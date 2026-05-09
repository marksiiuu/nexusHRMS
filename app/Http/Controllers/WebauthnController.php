<?php

namespace App\Http\Controllers;

use App\Models\WebauthnCredential;
use App\Models\Attendance;
use App\Models\BiometricLog;
use App\Services\WebAuthnService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WebauthnController extends Controller
{
    protected WebAuthnService $webauthn;

    public function __construct(WebAuthnService $webauthn)
    {
        $this->webauthn = $webauthn;
    }

    /**
     * Get the RP ID (domain) for WebAuthn.
     * WebAuthn requires a valid domain — IP addresses are NOT allowed.
     */
    private function getRpId(Request $request): string
    {
        $host = $request->getHost();

        // IP addresses are not valid RP IDs — use "localhost" as fallback
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return 'localhost';
        }

        // Strip port if somehow included
        $host = strtolower(explode(':', $host)[0]);

        return $host;
    }

    // ── Registration ──────────────────────────────────────────────────

    /**
     * Show credential management page.
     */
    public function manage()
    {
        $credentials = auth()->user()->webauthnCredentials()->latest()->get();
        return view('webauthn.manage', compact('credentials'));
    }

    /**
     * Generate registration options (called via AJAX).
     */
    public function registerOptions(Request $request)
    {
        $options = $this->webauthn->getRegistrationOptions(
            auth()->user(),
            $this->getRpId($request),
            config('app.name', 'Nexus HR')
        );

        return response()->json($options);
    }

    /**
     * Verify registration and store credential (called via AJAX).
     */
    public function registerVerify(Request $request)
    {
        $request->validate([
            'id'                            => 'required|string',
            'response.clientDataJSON'       => 'required|string',
            'response.attestationObject'    => 'required|string',
            'device_name'                   => 'nullable|string|max:100',
        ]);

        try {
            $result = $this->webauthn->verifyRegistration(
                $request->all(),
                $this->getRpId($request)
            );

            WebauthnCredential::create([
                'user_id'       => auth()->id(),
                'credential_id' => $result['credential_id'],
                'public_key'    => $result['public_key'],
                'sign_count'    => $result['sign_count'],
                'device_name'   => $request->input('device_name', 'Fingerprint'),
            ]);

            return response()->json(['success' => true, 'message' => 'Fingerprint registered successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete a credential.
     */
    public function deleteCredential(WebauthnCredential $credential)
    {
        if ($credential->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized.');
        }

        $credential->delete();
        return back()->with('success', 'Fingerprint credential removed.');
    }

    // ── Authentication (Clock In/Out) ─────────────────────────────────

    /**
     * Generate authentication options (called via AJAX).
     */
    public function authenticateOptions(Request $request)
    {
        $user = auth()->user();

        if ($user->webauthnCredentials()->count() === 0) {
            return response()->json(['error' => 'No fingerprint registered. Please register one first.'], 400);
        }

        $options = $this->webauthn->getAuthenticationOptions(
            $user,
            $this->getRpId($request)
        );

        return response()->json($options);
    }

    /**
     * Verify authentication and record attendance (called via AJAX).
     */
    public function authenticateVerify(Request $request)
    {
        $request->validate([
            'id'                           => 'required|string',
            'response.clientDataJSON'      => 'required|string',
            'response.authenticatorData'   => 'required|string',
            'response.signature'           => 'required|string',
        ]);

        try {
            $credentialId = $request->input('id');

            // Find the credential
            $credential = WebauthnCredential::where('credential_id', $credentialId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$credential) {
                return response()->json(['success' => false, 'message' => 'Credential not found.'], 404);
            }

            $result = $this->webauthn->verifyAuthentication(
                $request->all(),
                $credential,
                $this->getRpId($request)
            );

            // Update sign count
            $credential->update(['sign_count' => $result['new_sign_count']]);

            // Record attendance (same logic as AttendanceController@clockIn)
            $employee = auth()->user()->employee;
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'No employee profile linked.'], 400);
            }

            $phNow   = Carbon::now('Asia/Manila');
            $today   = $phNow->format('Y-m-d');
            $timeNow = $phNow->format('H:i');

            $existing = Attendance::where('employee_id', $employee->id)->where('date', $today)->first();

            if ($existing) {
                if ($existing->time_out) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already clocked out today!',
                    ]);
                }

                // Clock out
                $inTime = Carbon::parse($existing->time_in, 'Asia/Manila');
                $hours  = round($phNow->diffInMinutes($inTime) / 60, 2);

                $existing->update(['time_out' => $timeNow, 'hours_worked' => $hours]);

                // Log biometric
                BiometricLog::create([
                    'employee_id' => $employee->id,
                    'log_time'    => $phNow,
                    'log_type'    => 'time_out',
                    'device_id'   => 'WEBAUTHN-' . strtoupper(substr($credential->device_name, 0, 8)),
                    'processed'   => true,
                ]);

                return response()->json([
                    'success' => true,
                    'action'  => 'clock_out',
                    'message' => 'Clock-out recorded at ' . $phNow->format('h:i A') . ' PHT',
                    'time'    => $phNow->format('h:i A'),
                ]);
            }

            // Clock in
            $status = ($phNow->hour > 8 || ($phNow->hour === 8 && $phNow->minute > 15)) ? 'late' : 'present';

            Attendance::create([
                'employee_id' => $employee->id,
                'date'        => $today,
                'time_in'     => $timeNow,
                'status'      => $status,
            ]);

            // Log biometric
            BiometricLog::create([
                'employee_id' => $employee->id,
                'log_time'    => $phNow,
                'log_type'    => 'time_in',
                'device_id'   => 'WEBAUTHN-' . strtoupper(substr($credential->device_name, 0, 8)),
                'processed'   => true,
            ]);

            return response()->json([
                'success' => true,
                'action'  => 'clock_in',
                'message' => 'Clock-in recorded at ' . $phNow->format('h:i A') . ' PHT' . ($status === 'late' ? ' — Late' : ''),
                'time'    => $phNow->format('h:i A'),
                'status'  => $status,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Check if user has registered fingerprints (called via AJAX).
     */
    public function hasCredentials()
    {
        return response()->json([
            'has_credentials' => auth()->user()->webauthnCredentials()->count() > 0,
            'count'           => auth()->user()->webauthnCredentials()->count(),
        ]);
    }
}
