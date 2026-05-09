@extends('layouts.app')
@section('title','Fingerprint Security')
@section('page-title','Fingerprint Security')
@section('content')
<div class="page-header">
    <h1><i class="bi bi-fingerprint me-2"></i>Fingerprint Security</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Fingerprint Security</li>
    </ol></nav>
</div>

{{-- Register New Fingerprint --}}
<div class="card mb-4" style="border-left:4px solid #253D90;">
    <div class="card-body">
        <h6 class="fw-700 mb-3"><i class="bi bi-plus-circle me-2" style="color:#253D90;"></i>Register New Fingerprint</h6>
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Device Name</label>
                <input type="text" id="deviceName" class="form-control form-control-sm" value="My Laptop" placeholder="e.g. Work Laptop, Phone">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary btn-sm" id="registerBtn" onclick="registerFingerprint()">
                    <i class="bi bi-fingerprint me-1"></i>Register Fingerprint
                </button>
            </div>
        </div>
        <div id="registerAlert" class="mt-3" style="display:none;"></div>
        <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>Your browser will prompt you to scan your fingerprint via Windows Hello, Touch ID, or your device's biometric reader.
        </small>
    </div>
</div>

{{-- WebAuthn Support Check --}}
<div id="webauthnUnsupported" class="alert alert-danger mb-4" style="display:none;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Not supported:</strong> Your browser or device does not support WebAuthn/fingerprint authentication.
    Please use a modern browser (Chrome, Edge, Firefox, Safari) on a device with a fingerprint scanner.
</div>

{{-- Registered Credentials --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0"><i class="bi bi-shield-lock me-2" style="color:#253D90;"></i>Registered Fingerprints</h6>
        <span class="badge bg-secondary">{{ $credentials->count() }} registered</span>
    </div>
    @if($credentials->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Device Name</th><th>Registered On</th><th>Last Used</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($credentials as $cred)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="width:32px;height:32px;font-size:.85rem;background:#E3EDF9;color:#253D90;">
                                <i class="bi bi-fingerprint"></i>
                            </div>
                            <div>
                                <div class="small fw-600">{{ $cred->device_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">ID: {{ Str::limit($cred->credential_id, 20) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="small">{{ $cred->created_at->format('M d, Y h:i A') }}</td>
                    <td class="small text-muted">{{ $cred->updated_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('webauthn.delete', $cred) }}" onsubmit="return confirm('Remove this fingerprint? You will need to re-register it.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>Remove
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-fingerprint display-4 d-block mb-3" style="opacity:.3;"></i>
        <p class="mb-1">No fingerprints registered yet.</p>
        <small>Click "Register Fingerprint" above to get started.</small>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Check WebAuthn support
if (!window.PublicKeyCredential) {
    document.getElementById('webauthnUnsupported').style.display = 'block';
    document.getElementById('registerBtn').disabled = true;
}

function showAlert(id, type, message) {
    const el = document.getElementById(id);
    el.innerHTML = `<div class="alert alert-${type} py-2 small mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'}"></i>
        ${message}
    </div>`;
    el.style.display = 'block';
}

// Base64URL helpers
function base64urlToBuffer(base64url) {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const pad = base64.length % 4 === 0 ? '' : '='.repeat(4 - (base64.length % 4));
    const binary = atob(base64 + pad);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
    return bytes.buffer;
}

function bufferToBase64url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

async function registerFingerprint() {
    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Waiting for fingerprint...';

    try {
        // 1. Get registration options from server
        const optRes = await fetch('{{ route("webauthn.register-options") }}');
        const options = await optRes.json();

        // 2. Convert base64url fields to ArrayBuffer
        options.challenge = base64urlToBuffer(options.challenge);
        options.user.id = base64urlToBuffer(options.user.id);
        if (options.excludeCredentials) {
            options.excludeCredentials = options.excludeCredentials.map(c => ({
                ...c, id: base64urlToBuffer(c.id)
            }));
        }

        // 3. Call WebAuthn API (triggers Windows Hello / fingerprint prompt)
        const credential = await navigator.credentials.create({ publicKey: options });

        // 4. Send response to server
        const verifyRes = await fetch('{{ route("webauthn.register-verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                id: bufferToBase64url(credential.rawId),
                response: {
                    clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                    attestationObject: bufferToBase64url(credential.response.attestationObject),
                },
                device_name: document.getElementById('deviceName').value || 'Fingerprint',
            }),
        });

        const result = await verifyRes.json();

        if (result.success) {
            showAlert('registerAlert', 'success', result.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('registerAlert', 'danger', result.message);
        }
    } catch (err) {
        if (err.name === 'NotAllowedError') {
            showAlert('registerAlert', 'warning', 'Registration cancelled or timed out.');
        } else {
            showAlert('registerAlert', 'danger', 'Error: ' + err.message);
        }
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-fingerprint me-1"></i>Register Fingerprint';
}
</script>
@endpush
