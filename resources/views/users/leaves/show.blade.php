@extends('layouts.app')
@section('title','Leave Request')
@section('page-title','Leave Request Details')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Leave Request</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($leave->status === 'pending' && auth()->user()->hasHrAccess())
        <form method="POST" action="{{ route('leaves.approve',$leave) }}">
            @csrf <button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>Approve</button>
        </form>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-circle me-1"></i>Reject</button>
        @endif
        @if($leave->status === 'pending' && auth()->user()->id === $leave->employee->user_id)
        <form method="POST" action="{{ route('leaves.cancel',$leave) }}">
            @csrf <button class="btn btn-warning" onclick="return confirm('Cancel this leave?')"><i class="bi bi-x-circle me-1"></i>Cancel</button>
        </form>
        @endif
        <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="emp-avatar" style="width:56px;height:56px;font-size:1.3rem;">
                    {{ strtoupper(substr($leave->employee->first_name,0,1).substr($leave->employee->last_name,0,1)) }}
                </div>
                <div>
                    <h5 class="mb-0 fw-700">{{ $leave->employee->full_name }}</h5>
                    <div class="text-muted small">{{ $leave->employee->position }} — {{ $leave->employee->department?->name }}</div>
                </div>
            </div>
            {!! $leave->status_badge !!}
        </div>
        <hr>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Leave Type</div>
                <div>{{ $leave->leaveType->name }} <span class="badge bg-light text-dark border ms-1">{{ $leave->leaveType->is_paid ? 'Paid' : 'Unpaid' }}</span></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Duration</div>
                <div class="fw-600" style="color:#253D90;">{{ $leave->total_days }} working day(s)</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Start Date</div>
                <div>{{ $leave->start_date->format('F d, Y') }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">End Date</div>
                <div>{{ $leave->end_date->format('F d, Y') }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted small fw-600 mb-1">Reason</div>
                <div class="p-3 rounded" style="background:#f8f9fa;">{{ $leave->reason }}</div>
            </div>
            @if($leave->status === 'rejected' && $leave->rejection_reason)
            <div class="col-12">
                <div class="text-muted small fw-600 mb-1">Rejection Reason</div>
                <div class="alert alert-danger mb-0">{{ $leave->rejection_reason }}</div>
            </div>
            @endif
            @if($leave->approver)
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Processed By</div>
                <div>{{ $leave->approver->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Processed At</div>
                <div>{{ $leave->approved_at?->format('M d, Y h:i A') ?? '—' }}</div>
            </div>
            @endif
            <div class="col-md-6">
                <div class="text-muted small fw-600 mb-1">Submitted</div>
                <div>{{ $leave->created_at->format('M d, Y h:i A') }}</div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<!-- Reject Modal -->
@if($leave->status === 'pending' && auth()->user()->hasHrAccess())
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Reject Leave</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('leaves.reject',$leave) }}">
            @csrf
            <div class="modal-body">
                <label class="form-label fw-600">Rejection Reason <span class="text-danger">*</span></label>
                <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Provide a clear reason…"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Leave</button>
            </div>
        </form>
    </div></div>
</div>
@endif
@endsection
