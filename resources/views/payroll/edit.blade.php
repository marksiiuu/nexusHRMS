@extends('layouts.app')
@section('title','Edit Payroll')
@section('page-title','Edit Payroll')
@section('content')
<div class="page-header">
    <h1>Edit Payroll</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('payroll.index') }}">Payroll</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card mb-3">
    <div class="card-header">
        <h6><i class="bi bi-cash-stack me-2"></i>{{ $payroll->employee->full_name }} — {{ $payroll->month_name }} {{ $payroll->year }}
            @if($payroll->pay_period_type && $payroll->pay_period_type !== 'full')
            <span class="badge bg-light text-dark border ms-2">{{ $payroll->pay_period_type === 'first' ? '1st Half' : '2nd Half' }}</span>
            @endif
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('payroll.update',$payroll) }}">
        @csrf @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach(['draft'=>'Draft','processed'=>'Processed','paid'=>'Paid'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('status',$payroll->status)==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Draft → Processed → Paid (cannot go back once Paid)</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pay Date</label>
                <input type="date" name="pay_date" class="form-control" value="{{ old('pay_date',$payroll->pay_date?->format('Y-m-d')) }}">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Basic Salary (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="basic_salary" id="basicSalary" class="form-control" value="{{ old('basic_salary',$payroll->basic_salary) }}" min="0" step="0.01" required></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Overtime Pay (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="overtime_pay" id="overtimePay" class="form-control" value="{{ old('overtime_pay',$payroll->overtime_pay) }}" min="0" step="0.01"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Allowances (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="allowances" id="allowances" class="form-control" value="{{ old('allowances',$payroll->allowances) }}" min="0" step="0.01"></div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Tax (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="tax_deduction" id="taxDed" class="form-control" value="{{ old('tax_deduction',$payroll->tax_deduction) }}" min="0" step="0.01"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">SSS (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="sss_deduction" id="sssDed" class="form-control" value="{{ old('sss_deduction',$payroll->sss_deduction) }}" min="0" step="0.01"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">PhilHealth (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="philhealth_deduction" id="philDed" class="form-control" value="{{ old('philhealth_deduction',$payroll->philhealth_deduction) }}" min="0" step="0.01"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pag-IBIG (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="pagibig_deduction" id="pagibigDed" class="form-control" value="{{ old('pagibig_deduction',$payroll->pagibig_deduction) }}" min="0" step="0.01"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Other Deductions (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="other_deductions" id="otherDed" class="form-control" value="{{ old('other_deductions',$payroll->other_deductions) }}" min="0" step="0.01"></div>
            </div>
        </div>

        <div class="p-3 rounded mb-4" style="background:#E3EDF9;">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="small text-muted">Gross Pay</div>
                    <div class="fw-700" id="s_gross" style="color:#253D90;">—</div>
                </div>
                <div class="col-4">
                    <div class="small text-muted">Total Deductions</div>
                    <div class="fw-700 text-danger" id="s_ded">—</div>
                </div>
                <div class="col-4">
                    <div class="small text-muted">Net Pay</div>
                    <div class="fw-700" id="s_net" style="color:#253D90;font-size:1.1rem;">—</div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes',$payroll->notes) }}</textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
            <a href="{{ route('payroll.show',$payroll) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
const fmt=n=>'₱'+parseFloat(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
const v=id=>parseFloat(document.getElementById(id).value||0);
function recalc(){
    const gross=v('basicSalary')+v('overtimePay')+v('allowances');
    const ded=v('taxDed')+v('sssDed')+v('philDed')+v('pagibigDed')+v('otherDed');
    document.getElementById('s_gross').textContent=fmt(gross);
    document.getElementById('s_ded').textContent='-'+fmt(ded);
    document.getElementById('s_net').textContent=fmt(gross-ded);
}
['basicSalary','overtimePay','allowances','taxDed','sssDed','philDed','pagibigDed','otherDed'].forEach(id=>{
    document.getElementById(id).addEventListener('input',recalc);
});
recalc();
</script>
@endpush
