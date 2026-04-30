@extends('layouts.app')
@section('title', $employee->full_name)
@section('page-title','Employee Profile')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Employee Profile</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            @if(auth()->user()->hasHrAccess())
            <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
            @endif
            <li class="breadcrumb-item active">{{ $employee->full_name }}</li>
        </ol></nav>
    </div>
    @if(auth()->user()->hasHrAccess())
    <div class="d-flex gap-2">
        <a href="{{ route('employees.edit',$employee) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @if(!$employee->isArchived())
        <form method="POST" action="{{ route('employees.archive',$employee) }}" onsubmit="return confirm('Archive {{ addslashes($employee->full_name) }}? Data is preserved.')">
            @csrf <button class="btn btn-outline-warning"><i class="bi bi-archive me-1"></i>Archive</button>
        </form>
        @else
        <form method="POST" action="{{ route('employees.restore',$employee) }}">
            @csrf <button class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
        </form>
        @endif
    </div>
    @endif
</div>

<div class="row g-3">
    {{-- Profile Card --}}
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body text-center py-4">
                <img src="{{ $employee->avatar_url }}" alt="{{ $employee->full_name }}"
                     class="rounded-circle mb-3" style="width:90px;height:90px;object-fit:cover;border:3px solid #E3EDF9;"
                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($employee->full_name) }}&background=253D90&color=fff&size=90&bold=true'">
                <h5 class="fw-700 mb-1">{{ $employee->full_name }}</h5>
                <div class="text-muted small mb-2">{{ $employee->position }}</div>
                {!! $employee->status_badge !!}
                @if($employee->isArchived())
                <span class="badge bg-warning text-dark ms-1">Archived</span>
                @endif
                <hr>
                <div class="text-start">
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Employee ID</span><code>{{ $employee->employee_id }}</code></div>
                    @if($employee->biometric_id)
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Biometric ID</span><code>{{ $employee->biometric_id }}</code></div>
                    @endif
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Department</span><span class="fw-500">{{ $employee->department?->name ?? '—' }}</span></div>
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Type</span><span>{{ str_replace('_',' ',ucwords($employee->employment_type,'_')) }}</span></div>
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Hire Date</span><span>{{ $employee->hire_date->format('M d, Y') }}</span></div>
                    <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Tenure</span><span>{{ $employee->hire_date->diffForHumans(null,true) }}</span></div>
                    @if(auth()->user()->hasPayrollAccess())
                    <div class="d-flex justify-content-between small"><span class="text-muted">Salary</span><span class="fw-600" style="color:#253D90;">₱{{ number_format($employee->salary,2) }}</span></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Contact</h6></div>
            <div class="card-body">
                <div class="mb-2 d-flex align-items-start gap-2"><i class="bi bi-envelope text-muted mt-1"></i><a href="mailto:{{ $employee->email }}" class="small text-break">{{ $employee->email }}</a></div>
                <div class="mb-2 d-flex align-items-center gap-2"><i class="bi bi-telephone text-muted"></i><span class="small">{{ $employee->phone ?? '—' }}</span></div>
                <div class="d-flex align-items-start gap-2"><i class="bi bi-geo-alt text-muted mt-1"></i><span class="small">{{ $employee->address ?? '—' }}</span></div>
                @if($employee->emergency_contact_name)
                <hr>
                <div class="small fw-600 mb-1 text-muted">Emergency Contact</div>
                <div class="small">{{ $employee->emergency_contact_name }}</div>
                <div class="small text-muted">{{ $employee->emergency_contact_phone }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#attendance">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#leaves">Leaves</a></li>
                    @if(auth()->user()->hasPayrollAccess())
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payroll">Payroll</a></li>
                    @endif
                    @if(auth()->user()->id === $employee->user_id || auth()->user()->hasHrAccess())
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#security">Security</a></li>
                    @endif
                </ul>
            </div>
            <div class="card-body tab-content p-0">
                <div class="tab-pane fade show active p-0" id="attendance">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Hours</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($employee->attendances as $att)
                                <tr>
                                    <td class="small">{{ $att->date->format('M d, Y') }}</td>
                                    <td class="small">{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i A') : '—' }}</td>
                                    <td class="small">{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '—' }}</td>
                                    <td class="small">{{ $att->hours_worked ? $att->hours_worked.'h' : '—' }}</td>
                                    <td><span class="badge bg-{{ ['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info','on_leave'=>'secondary'][$att->status]??'secondary' }}">{{ str_replace('_',' ',ucfirst($att->status)) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-3 small">No attendance records</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade p-0" id="leaves">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($employee->leaves as $leave)
                                <tr>
                                    <td class="small">{{ $leave->leaveType->name }}</td>
                                    <td class="small">{{ $leave->start_date->format('M d, Y') }}</td>
                                    <td class="small">{{ $leave->end_date->format('M d, Y') }}</td>
                                    <td class="small">{{ $leave->total_days }}d</td>
                                    <td>{!! $leave->status_badge !!}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-3 small">No leave records</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(auth()->user()->hasPayrollAccess())
                <div class="tab-pane fade p-0" id="payroll">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead><tr><th>Period</th><th>Schedule</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($employee->payrolls as $pay)
                                <tr>
                                    <td class="small">{{ $pay->month_name }} {{ $pay->year }}
                                        @if($pay->pay_period_type && $pay->pay_period_type !== 'full')
                                        <div class="text-muted" style="font-size:.68rem;">{{ $pay->pay_period_type === 'first' ? '1st Half' : '2nd Half' }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark border" style="font-size:.65rem;">{{ $pay->pay_period === 'semi_monthly' ? 'Semi' : 'Monthly' }}</span></td>
                                    <td class="small">₱{{ number_format($pay->gross_salary,2) }}</td>
                                    <td class="small text-danger">-₱{{ number_format($pay->total_deductions,2) }}</td>
                                    <td class="small fw-600" style="color:#253D90;">₱{{ number_format($pay->net_salary,2) }}</td>
                                    <td>{!! $pay->status_badge !!}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-3 small">No payroll records</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                @if(auth()->user()->id === $employee->user_id || auth()->user()->hasHrAccess())
                <div class="tab-pane fade p-3" id="security">
                    <h6 class="mb-3">Change Password</h6>
                    <form method="POST" action="{{ route('employees.update-password', $employee) }}" class="col-md-6">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Update Password</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
