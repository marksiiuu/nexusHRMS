<?php

namespace App\Http\Controllers;

use App\Models\BiometricLog;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BiometricController extends Controller
{
    public function index(Request $request)
    {
        $query = BiometricLog::with('employee')->latest('log_time');
        if ($request->date)        $query->whereDate('log_time', $request->date);
        if ($request->employee_id) $query->where('employee_id', $request->employee_id);
        if ($request->processed !== null) $query->where('processed', $request->processed);

        $logs      = $query->paginate(20)->withQueryString();
        $employees = Employee::whereNull('archived_at')->where('status','active')->get();
        $unprocessed = BiometricLog::where('processed',false)->count();

        return view('biometric.index', compact('logs','employees','unprocessed'));
    }

    // Process all unprocessed biometric logs into attendance records
    public function processLogs()
    {
        $logs = BiometricLog::where('processed', false)
            ->orderBy('log_time')
            ->get()
            ->groupBy(function($log) {
                return $log->employee_id.'_'.Carbon::parse($log->log_time)->format('Y-m-d');
            });

        $processed = 0;
        foreach ($logs as $key => $dayLogs) {
            $empId = $dayLogs->first()->employee_id;
            $date  = Carbon::parse($dayLogs->first()->log_time)->format('Y-m-d');

            $timeIns  = $dayLogs->where('log_type','time_in')->sortBy('log_time');
            $timeOuts = $dayLogs->where('log_type','time_out')->sortByDesc('log_time');

            $timeIn  = $timeIns->first()  ? Carbon::parse($timeIns->first()->log_time)->format('H:i') : null;
            $timeOut = $timeOuts->first() ? Carbon::parse($timeOuts->first()->log_time)->format('H:i') : null;

            $hours = null;
            if ($timeIn && $timeOut) {
                $hours = round((strtotime($timeOut) - strtotime($timeIn)) / 3600, 2);
            }

            // Determine status
            $status = 'present';
            if ($timeIn) {
                $scheduled = strtotime($date.' 08:00:00');
                $actual    = strtotime($date.' '.$timeIn);
                if ($actual > $scheduled + 900) $status = 'late'; // 15 min grace
            }

            // Upsert attendance
            Attendance::updateOrCreate(
                ['employee_id' => $empId, 'date' => $date],
                ['time_in' => $timeIn, 'time_out' => $timeOut, 'hours_worked' => $hours, 'status' => $status]
            );

            // Mark logs as processed
            $dayLogs->each(fn($log) => $log->update(['processed' => true]));
            $processed++;
        }

        return back()->with('success',"Processed {$processed} attendance record(s) from biometric logs!");
    }

    // Simulate biometric tap (for demo)
    public function tap(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'log_type'    => 'required|in:time_in,time_out',
        ]);

        $now = Carbon::now('Asia/Manila');
        BiometricLog::create([
            'employee_id' => $request->employee_id,
            'log_time'    => $now,
            'log_type'    => $request->log_type,
            'device_id'   => 'DEVICE-01',
            'processed'   => false,
        ]);

        return back()->with('success','Biometric tap recorded at '.$now->format('h:i A').' PH time!');
    }
}
