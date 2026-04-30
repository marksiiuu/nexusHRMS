@extends('layouts.app')
@section('title','Payroll')
@section('page-title','Payroll Management')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Payroll Records</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Payroll</li>
        </ol></nav>
    </div>
    <a href="{{ route('payroll.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Generate Payroll</a>
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
                <select name="year" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ request('year')==$y?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-select form-select-sm">
                    <option value="">All Months</option>
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ request('month')==$m?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <select name="pay_period" class="form-select form-select-sm">
                    <option value="">All Schedules</option>
                    <option value="semi_monthly" {{ request('pay_period')=='semi_monthly'?'selected':'' }}>Semi-Monthly</option>
                    <option value="monthly" {{ request('pay_period')=='monthly'?'selected':'' }}>Monthly</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['draft','processed','paid'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
            </div>
            <div class="col-auto ms-auto">
                @if($showArchived)
                    <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-cash-stack me-1"></i>Show Active</a>
                @else
                    <a href="{{ route('payroll.index') }}?archived=1" class="btn btn-outline-warning btn-sm"><i class="bi bi-archive me-1"></i>Archived ({{ $archivedCount }})</a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($showArchived)
<div class="alert alert-warning mb-3"><i class="bi bi-archive me-2"></i>Showing <strong>archived payroll records</strong>. These records are hidden from financial reports.</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-cash-stack me-2"></i>Payroll Records</h6>
        <span class="badge bg-secondary">{{ $payrolls->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Period</th>
                    <th>Schedule</th>
                    <th>Basic</th>
                    <th>Gross</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $pay)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($pay->employee->full_name) }}&background=253D90&color=fff&size=34&bold=true"
                                 class="rounded-circle" style="width:34px;height:34px;">
                            <div>
                                <div class="small fw-600">{{ $pay->employee->full_name }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $pay->employee->department?->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="small">
                        {{ $pay->month_name }} {{ $pay->year }}
                        @if($pay->pay_period_type && $pay->pay_period_type !== 'full')
                        <div class="text-muted" style="font-size:.7rem;">{{ $pay->pay_period_type === 'first' ? '1st Half (1–15)' : '2nd Half (16–End)' }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border" style="font-size:.68rem;">
                            {{ $pay->pay_period === 'semi_monthly' ? 'Semi-Monthly' : 'Monthly' }}
                        </span>
                    </td>
                    <td class="small">₱{{ number_format($pay->basic_salary,2) }}</td>
                    <td class="small">₱{{ number_format($pay->gross_salary,2) }}</td>
                    <td class="small text-danger">-₱{{ number_format($pay->total_deductions,2) }}</td>
                    <td class="small fw-700" style="color:#253D90;">₱{{ number_format($pay->net_salary,2) }}</td>
                    <td>{!! $pay->status_badge !!}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('payroll.show',$pay) }}" class="btn btn-outline-secondary" title="View Payslip"><i class="bi bi-receipt"></i></a>
                            @if($pay->status !== 'paid')
                            <a href="{{ route('payroll.edit',$pay) }}" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#delPay{{ $pay->id }}" title="Archive"><i class="bi bi-archive"></i></button>
                            @endif
                        </div>
                        <!-- Archive Modal -->
                        <div class="modal fade" id="delPay{{ $pay->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm"><div class="modal-content">
                                <div class="modal-header"><h6 class="modal-title">Archive Payroll</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                <div class="modal-body small">Archive payroll for <strong>{{ $pay->employee->full_name }}</strong> ({{ $pay->month_name }} {{ $pay->year }})?</div>
                                <div class="modal-footer">
                                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <form method="POST" action="{{ route('payroll.destroy',$pay) }}">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-warning">Archive</button></form>
                                </div>
                            </div></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-cash display-6 d-block mb-2"></i>No {{ $showArchived ? 'archived' : '' }} payroll records found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Showing {{ $payrolls->firstItem() }}–{{ $payrolls->lastItem() }} of {{ $payrolls->total() }}</small>
        {{ $payrolls->links() }}
    </div>
    @endif
</div>
@endsection
