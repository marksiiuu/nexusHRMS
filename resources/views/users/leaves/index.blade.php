@extends('layouts.app')
@section('title','Leave Requests')
@section('page-title','Leave Management')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Leave Requests</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Leaves</li>
        </ol></nav>
    </div>
    <a href="{{ route('leaves.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Apply for Leave</a>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(auth()->user()->hasHrAccess())
            <div class="col-md-3">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id')==$emp->id?'selected':'' }}>{{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <select name="leave_type_id" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}" {{ request('leave_type_id')==$lt->id?'selected':'' }}>{{ $lt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['pending','approved','rejected','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-calendar-x me-2"></i>Leave Requests</h6>
        <span class="badge bg-secondary">{{ $leaves->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="width:30px;height:30px;font-size:.7rem;">{{ strtoupper(substr($leave->employee->first_name,0,1).substr($leave->employee->last_name,0,1)) }}</div>
                            <div>
                                <div class="small fw-500">{{ $leave->employee->full_name }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ $leave->employee->department?->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $leave->leaveType->name }}</span></td>
                    <td class="small">{{ $leave->start_date->format('M d, Y') }}</td>
                    <td class="small">{{ $leave->end_date->format('M d, Y') }}</td>
                    <td class="small fw-500">{{ $leave->total_days }}d</td>
                    <td class="small text-muted">{{ Str::limit($leave->reason,40) }}</td>
                    <td>{!! $leave->status_badge !!}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end flex-wrap align-items-center">
                            <a href="{{ route('employees.show',$leave->employee) }}" class="btn btn-sm btn-outline-primary" title="View Profile"><i class="bi bi-person"></i></a>
                            <a href="{{ route('leaves.show',$leave) }}" class="btn btn-sm btn-outline-secondary" title="View Leave Details"><i class="bi bi-info-circle me-1"></i>Details</a>
                            @if($leave->status === 'pending')
                                @if(auth()->user()->hasHrAccess())
                                <form method="POST" action="{{ route('leaves.approve',$leave) }}" class="m-0">
                                    @csrf <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check2"></i></button>
                                </form>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}" title="Reject"><i class="bi bi-x"></i></button>
                                @endif
                                @if(auth()->user()->id === $leave->employee->user_id)
                                <form method="POST" action="{{ route('leaves.cancel',$leave) }}" class="m-0">
                                    @csrf <button type="submit" class="btn btn-sm btn-warning" title="Cancel" onclick="return confirm('Cancel this leave request?')"><i class="bi bi-x-circle"></i></button>
                                </form>
                                @endif
                            @endif
                        </div>

                        <!-- Reject Modal -->
                        @if($leave->status === 'pending' && auth()->user()->hasHrAccess())
                        <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Reject Leave Request</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <form method="POST" action="{{ route('leaves.reject',$leave) }}">
                                    @csrf
                                    <div class="modal-body">
                                        <p class="small text-muted">Provide a reason for rejecting <strong>{{ $leave->employee->full_name }}'s</strong> leave request.</p>
                                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Reason for rejection…"></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger btn-sm">Reject Leave</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-calendar-x display-6 d-block mb-2"></i>No leave requests found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $leaves->firstItem() }}–{{ $leaves->lastItem() }} of {{ $leaves->total() }}</small>
        {{ $leaves->links() }}
    </div>
    @endif
</div>
@endsection
