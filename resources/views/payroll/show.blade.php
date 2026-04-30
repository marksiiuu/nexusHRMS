@extends('layouts.app')
@section('title','Payslip')
@section('page-title','Payslip')
@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>Payslip</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">
                <a href="{{ auth()->user()->hasPayrollAccess() ? route('payroll.index') : route('payroll.my') }}">
                    {{ auth()->user()->hasPayrollAccess() ? 'Payroll' : 'My Payslips' }}
                </a>
            </li>
            <li class="breadcrumb-item active">Payslip</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        @if($payroll->status !== 'paid')
        <a href="{{ route('payroll.edit',$payroll) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</button>
        <a href="{{ auth()->user()->hasPayrollAccess() ? route('payroll.index') : route('payroll.my') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card" id="payslipCard">
    <div class="card-body p-4">
        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h4 class="fw-800 mb-0" style="color:#253D90;">Nexus HR</h4>
                <div class="text-muted small">Official Payslip</div>
            </div>
            <div class="text-end">
                <div class="fw-600">{{ $payroll->month_name }} {{ $payroll->year }}</div>
                @if($payroll->pay_period_type && $payroll->pay_period_type !== 'full')
                <div class="text-muted small">{{ $payroll->pay_period_type === 'first' ? '1st Half: 1–15' : '2nd Half: 16–End' }}</div>
                @endif
                <div class="mt-1">{!! $payroll->status_badge !!}
                    <span class="badge bg-light text-dark border ms-1" style="font-size:.65rem;">{{ $payroll->pay_period === 'semi_monthly' ? 'Semi-Monthly' : 'Monthly' }}</span>
                </div>
            </div>
        </div>

        {{-- Employee Info --}}
        <div class="row g-3 mb-4 p-3 rounded" style="background:#E3EDF9;">
            <div class="col-md-6">
                <div class="small text-muted fw-600 mb-1">Employee</div>
                <div class="fw-700">{{ $payroll->employee->full_name }}</div>
                <div class="small text-muted">{{ $payroll->employee->employee_id }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted fw-600 mb-1">Position / Department</div>
                <div class="fw-500">{{ $payroll->employee->position }}</div>
                <div class="small text-muted">{{ $payroll->employee->department?->name ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted fw-600 mb-1">Days Worked</div>
                <div class="fw-500 text-success">{{ $payroll->days_worked }} days</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted fw-600 mb-1">Days Absent</div>
                <div class="fw-500 text-danger">{{ $payroll->days_absent }} days</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted fw-600 mb-1">Pay Date</div>
                <div class="fw-500">{{ $payroll->pay_date?->format('M d, Y') ?? '—' }}</div>
            </div>
        </div>

        {{-- Earnings & Deductions --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <h6 class="fw-700 mb-3" style="color:#253D90;"><i class="bi bi-plus-circle me-1"></i>Earnings</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted small ps-0">Basic Salary</td><td class="text-end fw-500">₱{{ number_format($payroll->basic_salary,2) }}</td></tr>
                    <tr><td class="text-muted small ps-0">Overtime Pay</td><td class="text-end fw-500">₱{{ number_format($payroll->overtime_pay,2) }}</td></tr>
                    <tr><td class="text-muted small ps-0">Allowances</td><td class="text-end fw-500">₱{{ number_format($payroll->allowances,2) }}</td></tr>
                    <tr class="border-top"><td class="fw-700 ps-0">Gross Pay</td><td class="text-end fw-700" style="color:#253D90;">₱{{ number_format($payroll->gross_salary,2) }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-700 mb-3 text-danger"><i class="bi bi-dash-circle me-1"></i>Deductions</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted small ps-0">Withholding Tax</td><td class="text-end text-danger">-₱{{ number_format($payroll->tax_deduction,2) }}</td></tr>
                    <tr><td class="text-muted small ps-0">SSS</td><td class="text-end text-danger">-₱{{ number_format($payroll->sss_deduction,2) }}</td></tr>
                    <tr><td class="text-muted small ps-0">PhilHealth</td><td class="text-end text-danger">-₱{{ number_format($payroll->philhealth_deduction,2) }}</td></tr>
                    <tr><td class="text-muted small ps-0">Pag-IBIG</td><td class="text-end text-danger">-₱{{ number_format($payroll->pagibig_deduction,2) }}</td></tr>
                    @if($payroll->other_deductions > 0)
                    <tr><td class="text-muted small ps-0">Other</td><td class="text-end text-danger">-₱{{ number_format($payroll->other_deductions,2) }}</td></tr>
                    @endif
                    <tr class="border-top"><td class="fw-700 ps-0">Total Deductions</td><td class="text-end fw-700 text-danger">-₱{{ number_format($payroll->total_deductions,2) }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Net Pay --}}
        <div class="p-3 rounded text-center" style="background:#253D90;color:#fff;">
            <div class="small mb-1 opacity-75">NET PAY — {{ strtoupper($payroll->month_name.' '.$payroll->year) }}</div>
            <div style="font-size:2.2rem;font-weight:800;letter-spacing:-1px;">₱{{ number_format($payroll->net_salary,2) }}</div>
        </div>

        @if($payroll->notes)
        <div class="mt-3 small text-muted"><strong>Notes:</strong> {{ $payroll->notes }}</div>
        @endif
        @if($payroll->processor)
        <div class="mt-2 small text-muted">Processed by: {{ $payroll->processor->name }}</div>
        @endif
    </div>
</div>
</div>
</div>
@endsection

@push('styles')
<style>
@media print {
    #sidebar,#topbar,.page-header .btn,.btn-group,.dropdown{display:none!important;}
    #main-content{margin:0!important;padding:0!important;}
    #payslipCard{box-shadow:none!important;border:1px solid #ccc!important;}
    .no-print{display:none!important;}
}
</style>
@endpush
