@extends('layouts.app')
@section('title','My Payslips')
@section('page-title','My Payslips')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1>My Payslips</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">My Payslips</li>
        </ol></nav>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="year" class="form-select form-select-sm">
            <option value="">All Years</option>
            @foreach($years as $y)<option value="{{ $y }}" {{ request('year')==$y?'selected':'' }}>{{ $y }}</option>@endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </form>
</div>

<div class="row g-3">
    @forelse($payrolls as $pay)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0 fw-700">{{ $pay->month_name }} {{ $pay->year }}</h6>
                    {!! $pay->status_badge !!}
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Gross Pay</span>
                    <span class="fw-500">₱{{ number_format($pay->gross_salary,2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Deductions</span>
                    <span class="text-danger">-₱{{ number_format($pay->total_deductions,2) }}</span>
                </div>
                <div class="border-top pt-2 d-flex justify-content-between">
                    <span class="fw-600">Net Pay</span>
                    <span class="fw-700" style="color:#253D90;font-size:1.05rem;">₱{{ number_format($pay->net_salary,2) }}</span>
                </div>
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">{{ $pay->days_worked }}d worked</span>
                    <a href="{{ route('payroll.show',$pay) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View Payslip</a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="bi bi-wallet2 display-4 d-block mb-3"></i>No payslips found.
        </div></div>
    </div>
    @endforelse
</div>

@if($payrolls->hasPages())
<div class="mt-3 d-flex justify-content-center">{{ $payrolls->links() }}</div>
@endif
@endsection
