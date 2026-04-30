@extends('layouts.app')
@section('title','Attendance')
@section('page-title','Attendance Management')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Attendance Records</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Attendance</li>
        </ol></nav>
    </div>
    @if(auth()->user()->hasHrAccess())
    <a href="{{ route('attendance.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Record Attendance</a>
    @endif
</div>

<!-- Filters -->
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
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}" placeholder="Date">
            </div>
            <div class="col-md-2">
                <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}" placeholder="Month">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['present','absent','late','half_day','on_leave'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ str_replace('_',' ',ucfirst($s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
            <div class="col-auto ms-auto">
                @if($showArchived)
                    <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calendar-check me-1"></i>Show Active</a>
                @else
                    <a href="{{ route('attendance.index') }}?archived=1" class="btn btn-outline-warning btn-sm"><i class="bi bi-archive me-1"></i>Archived ({{ $archivedCount }})</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($showArchived)
<div class="alert alert-warning mb-3"><i class="bi bi-archive me-2"></i>Showing <strong>archived attendance records</strong>. These records are hidden from standard reports.</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-clock-history me-2"></i>Attendance Log</h6>
        <span class="badge bg-secondary">{{ $attendances->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Notes</th>
                    @if(auth()->user()->hasHrAccess())<th class="text-end">Actions</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="emp-avatar" style="width:30px;height:30px;font-size:.7rem;">{{ strtoupper(substr($att->employee->first_name,0,1).substr($att->employee->last_name,0,1)) }}</div>
                            <div>
                                <div class="small fw-500">{{ $att->employee->full_name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $att->employee->department?->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="small">{{ $att->date->format('M d, Y') }}<br><span class="text-muted" style="font-size:.7rem;">{{ $att->date->format('l') }}</span></td>
                    <td class="small">{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i A') : '—' }}</td>
                    <td class="small">{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '—' }}</td>
                    <td class="small">{{ $att->hours_worked ? $att->hours_worked.'h' : '—' }}</td>
                    <td>
                        @php $cls=['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info','on_leave'=>'secondary'][$att->status]??'secondary'; @endphp
                        <span class="badge bg-{{ $cls }}">{{ str_replace('_',' ',ucfirst($att->status)) }}</span>
                    </td>
                    <td class="small text-muted">{{ Str::limit($att->notes,30) }}</td>
                    @if(auth()->user()->hasHrAccess())
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('attendance.edit',$att) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#delAtt{{ $att->id }}" title="Archive"><i class="bi bi-archive"></i></button>
                        </div>
                        <div class="modal fade" id="delAtt{{ $att->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm"><div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Archive Record</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body small">Archive attendance for <strong>{{ $att->employee->full_name }}</strong> on {{ $att->date->format('M d, Y') }}?</div>
                                <div class="modal-footer">
                                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST" action="{{ route('attendance.destroy',$att) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning">Archive</button>
                                    </form>
                                </div>
                            </div></div>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-clock display-6 d-block mb-2"></i>No {{ $showArchived ? 'archived' : '' }} attendance records found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($attendances->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} of {{ $attendances->total() }}</small>
        {{ $attendances->links() }}
    </div>
    @endif
</div>
@endsection
