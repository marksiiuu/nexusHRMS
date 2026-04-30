@extends('layouts.app')
@section('title','Biometrics')
@section('page-title','Biometric Management')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Biometric Logs</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Biometrics</li>
        </ol></nav>
    </div>
    @if($unprocessed > 0)
    <form method="POST" action="{{ route('biometric.process') }}">
        @csrf
        <button type="submit" class="btn btn-warning">
            <i class="bi bi-arrow-repeat me-1"></i>Process {{ $unprocessed }} Pending Logs
        </button>
    </form>
    @endif
</div>

@if($unprocessed > 0)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-fingerprint fs-5"></i>
    <div><strong>{{ $unprocessed }} unprocessed biometric log(s)</strong> — Click "Process" to automatically convert them into attendance records.</div>
</div>
@endif

<div class="card mb-3">
    <div class="card-header"><h6><i class="bi bi-fingerprint me-2"></i>Simulate Biometric Tap (Demo)</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('biometric.tap') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_id }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Log Type</label>
                <select name="log_type" class="form-select">
                    <option value="time_in">Time In</option>
                    <option value="time_out">Time Out</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-fingerprint me-1"></i>Record Tap (PH Time)
                </button>
            </div>
        </form>
        <small class="text-muted d-block mt-2">
            <i class="bi bi-info-circle me-1"></i>Times are recorded in Philippine Standard Time (UTC+8). After tapping, click "Process Pending Logs" to convert to attendance records automatically.
        </small>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <select name="processed" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="0" {{ request('processed')==='0'?'selected':'' }}>Unprocessed</option>
                    <option value="1" {{ request('processed')==='1'?'selected':'' }}>Processed</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('biometric.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-list-ul me-2"></i>Biometric Records</h6>
        <span class="badge bg-secondary">{{ $logs->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Employee</th><th>Log Time (PHT)</th><th>Type</th><th>Device</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="small fw-600">{{ $log->employee->full_name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $log->employee->employee_id }}</div>
                    </td>
                    <td class="small">{{ \Carbon\Carbon::parse($log->log_time)->timezone('Asia/Manila')->format('M d, Y h:i:s A') }}</td>
                    <td><span class="badge {{ $log->log_type=='time_in' ? 'bg-success' : 'bg-danger' }}">{{ $log->log_type=='time_in' ? 'Time In' : 'Time Out' }}</span></td>
                    <td class="small text-muted">{{ $log->device_id ?? 'DEVICE-01' }}</td>
                    <td><span class="badge {{ $log->processed ? 'bg-success' : 'bg-warning text-dark' }}">{{ $log->processed ? 'Processed' : 'Pending' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-fingerprint display-6 d-block mb-2"></i>No biometric logs found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</small>
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
