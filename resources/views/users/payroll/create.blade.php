@extends('layouts.app')
@section('title','Generate Payroll')
@section('page-title','Generate Payroll')
@section('content')
<div class="page-header">
    <h1>Generate Payroll</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('payroll.index') }}">Payroll</a></li>
        <li class="breadcrumb-item active">Generate</li>
    </ol></nav>
</div>

{{-- Bulk Generate Card --}}
<div class="card mb-3" style="border-left:4px solid #253D90;">
    <div class="card-body">
        <h6 class="fw-700 mb-3"><i class="bi bi-lightning-fill me-2" style="color:#253D90;"></i>Bulk Generate — By Department</h6>
        <form method="POST" action="{{ route('payroll.generate-bulk') }}" class="row g-2 align-items-end" id="bulkForm">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select name="department_id" class="form-select form-select-sm" id="bulkDepartment" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y=date('Y');$y>=date('Y')-2;$y--)
                    <option value="{{ $y }}" {{ date('Y')==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select form-select-sm">
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ date('n')==$m?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Pay Schedule</label>
                <select name="pay_period" class="form-select form-select-sm" id="bulkPayPeriod">
                    <option value="semi_monthly">Semi-Monthly (Every 2 weeks)</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Period</label>
                <select name="pay_period_type" class="form-select form-select-sm" id="bulkPeriodType">
                    <option value="first">1st Half (1–15)</option>
                    <option value="second">2nd Half (16–End)</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-lightning me-1"></i>Bulk Generate</button>
            </div>
        </form>
        <div id="bulkAlert" class="mt-3" style="display:none;"></div>
        <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Select a department first. Salary is auto-halved for semi-monthly. Attendance days are auto-fetched. Existing records are skipped.</small>
    </div>
</div>

<div class="row">
<div class="col-lg-8">
<form method="POST" action="{{ route('payroll.store') }}" id="payrollForm">
@csrf
<div class="card mb-3">
    <div class="card-header"><h6><i class="bi bi-person me-2"></i>Employee & Pay Period</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Employee <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required id="empSelect">
                    <option value="">Select Employee</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" data-salary="{{ $emp->salary }}" {{ old('employee_id')==$emp->id?'selected':'' }}>
                        {{ $emp->full_name }} — {{ $emp->department?->name }}
                    </option>
                    @endforeach
                </select>
                @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Year <span class="text-danger">*</span></label>
                <select name="year" class="form-select" required>
                    @for($y=date('Y');$y>=date('Y')-3;$y--)
                    <option value="{{ $y }}" {{ old('year',date('Y'))==$y?'selected':'' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month <span class="text-danger">*</span></label>
                <select name="month" class="form-select" required>
                    @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ old('month',date('n'))==$m?'selected':'' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pay Schedule <span class="text-danger">*</span></label>
                <select name="pay_period" class="form-select" id="payPeriodSel" required>
                    <option value="semi_monthly" {{ old('pay_period','semi_monthly')=='semi_monthly'?'selected':'' }}>Semi-Monthly (Every 2 Weeks)</option>
                    <option value="monthly" {{ old('pay_period')=='monthly'?'selected':'' }}>Monthly</option>
                </select>
            </div>
            <div class="col-md-4" id="periodTypeWrap">
                <label class="form-label">Pay Period <span class="text-danger">*</span></label>
                <select name="pay_period_type" class="form-select" required>
                    <option value="first" {{ old('pay_period_type','first')=='first'?'selected':'' }}>1st Half (1–15)</option>
                    <option value="second" {{ old('pay_period_type')=='second'?'selected':'' }}>2nd Half (16–End)</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h6><i class="bi bi-cash-coin me-2"></i>Earnings</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Basic Salary (₱) <span class="text-danger">*</span></label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="basic_salary" id="basicSalary" class="form-control" value="{{ old('basic_salary') }}" min="0" step="0.01" required></div>
                <small class="text-muted" id="salaryHint"></small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Overtime Pay (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="overtime_pay" id="overtimePay" class="form-control" value="{{ old('overtime_pay',0) }}" min="0" step="0.01"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Allowances (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span>
                <input type="number" name="allowances" id="allowances" class="form-control" value="{{ old('allowances',3000) }}" min="0" step="0.01"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6><i class="bi bi-dash-circle me-2"></i>Deductions</h6>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="autoDeductions()"><i class="bi bi-magic me-1"></i>Auto-Compute</button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Withholding Tax (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span><input type="number" name="tax_deduction" id="taxDed" class="form-control" value="{{ old('tax_deduction',0) }}" min="0" step="0.01"></div></div>
            <div class="col-md-4"><label class="form-label">SSS (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span><input type="number" name="sss_deduction" id="sssDed" class="form-control" value="{{ old('sss_deduction',1125) }}" min="0" step="0.01"></div></div>
            <div class="col-md-4"><label class="form-label">PhilHealth (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span><input type="number" name="philhealth_deduction" id="philDed" class="form-control" value="{{ old('philhealth_deduction',0) }}" min="0" step="0.01"></div></div>
            <div class="col-md-4"><label class="form-label">Pag-IBIG (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span><input type="number" name="pagibig_deduction" id="pagibigDed" class="form-control" value="{{ old('pagibig_deduction',200) }}" min="0" step="0.01"></div></div>
            <div class="col-md-4"><label class="form-label">Other Deductions (₱)</label>
                <div class="input-group"><span class="input-group-text">₱</span><input type="number" name="other_deductions" id="otherDed" class="form-control" value="{{ old('other_deductions',0) }}" min="0" step="0.01"></div></div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Pay Date</label>
                <input type="date" name="pay_date" class="form-control" value="{{ old('pay_date') }}"></div>
            <div class="col-12"><label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
        </div>
    </div>
</div>
<div class="d-flex gap-2 align-items-center flex-wrap">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Payroll</button>
    <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <div class="ms-auto form-check form-switch mb-0 d-flex align-items-center gap-2">
        <input class="form-check-input" type="checkbox" name="immediate_release" id="immediateRelease" style="width:2.5em;height:1.3em;">
        <label class="form-check-label small fw-600 text-success" for="immediateRelease">
            <i class="bi bi-lightning-fill me-1"></i>Release Immediately (Mark as Paid)
        </label>
    </div>
</div>
</form>
</div>

{{-- Live Summary --}}
<div class="col-lg-4">
    <div class="card sticky-top" style="top:76px;">
        <div class="card-header"><h6><i class="bi bi-calculator me-2"></i>Live Pay Summary</h6></div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Basic Salary</span><span id="s_basic">₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Overtime</span><span id="s_ot">₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Allowances</span><span id="s_allow">₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small fw-600 border-top pt-2"><span>Gross Pay</span><span id="s_gross">₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Tax</span><span id="s_tax" class="text-danger">-₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">SSS</span><span id="s_sss" class="text-danger">-₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">PhilHealth</span><span id="s_phil" class="text-danger">-₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Pag-IBIG</span><span id="s_pag" class="text-danger">-₱0.00</span></div>
            <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Other</span><span id="s_other" class="text-danger">-₱0.00</span></div>
            <div class="d-flex justify-content-between mb-3 small fw-600 border-top pt-2"><span>Total Deductions</span><span id="s_deductions" class="text-danger">-₱0.00</span></div>
            <div class="p-2 rounded text-center" style="background:#E3EDF9;">
                <div class="small text-muted mb-1">NET PAY</div>
                <div id="s_net" style="font-size:1.5rem;font-weight:800;color:#253D90;">₱0.00</div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
const fmt=n=>'₱'+parseFloat(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
const v=id=>parseFloat(document.getElementById(id).value||0);

// Auto-fill salary when employee selected
document.getElementById('empSelect').addEventListener('change',function(){
    const sal = this.options[this.selectedIndex].dataset.salary;
    if (!sal) return;
    const isPeriod = document.getElementById('payPeriodSel').value === 'semi_monthly';
    const computed = isPeriod ? (parseFloat(sal)/2).toFixed(2) : parseFloat(sal).toFixed(2);
    document.getElementById('basicSalary').value = computed;
    document.getElementById('salaryHint').textContent = isPeriod
        ? `Semi-monthly: ₱${parseFloat(sal).toLocaleString()} ÷ 2 = ₱${parseFloat(computed).toLocaleString()}`
        : `Monthly salary: ₱${parseFloat(sal).toLocaleString()}`;
    autoDeductions(); recalc();
});

// Update salary when pay period changes
document.getElementById('payPeriodSel').addEventListener('change',function(){
    const isSemi = this.value === 'semi_monthly';
    document.getElementById('periodTypeWrap').style.display = isSemi ? '' : 'none';
    const opt = document.getElementById('empSelect').options[document.getElementById('empSelect').selectedIndex];
    if (opt?.dataset.salary) {
        const sal = parseFloat(opt.dataset.salary);
        document.getElementById('basicSalary').value = isSemi ? (sal/2).toFixed(2) : sal.toFixed(2);
        recalc();
    }
});

function autoDeductions(){
    const gross = v('basicSalary')+v('overtimePay')+v('allowances');
    document.getElementById('taxDed').value    = (gross*0.10).toFixed(2);
    document.getElementById('sssDed').value    = '1125.00';
    document.getElementById('philDed').value   = (gross*0.025).toFixed(2);
    document.getElementById('pagibigDed').value= '200.00';
    recalc();
}

function recalc(){
    const basic=v('basicSalary'),ot=v('overtimePay'),allow=v('allowances');
    const gross=basic+ot+allow;
    const tax=v('taxDed'),sss=v('sssDed'),phil=v('philDed'),pag=v('pagibigDed'),other=v('otherDed');
    const totalDed=tax+sss+phil+pag+other;
    document.getElementById('s_basic').textContent=fmt(basic);
    document.getElementById('s_ot').textContent=fmt(ot);
    document.getElementById('s_allow').textContent=fmt(allow);
    document.getElementById('s_gross').textContent=fmt(gross);
    document.getElementById('s_tax').textContent='-'+fmt(tax);
    document.getElementById('s_sss').textContent='-'+fmt(sss);
    document.getElementById('s_phil').textContent='-'+fmt(phil);
    document.getElementById('s_pag').textContent='-'+fmt(pag);
    document.getElementById('s_other').textContent='-'+fmt(other);
    document.getElementById('s_deductions').textContent='-'+fmt(totalDed);
    document.getElementById('s_net').textContent=fmt(gross-totalDed);
}
['basicSalary','overtimePay','allowances','taxDed','sssDed','philDed','pagibigDed','otherDed'].forEach(id=>{
    document.getElementById(id).addEventListener('input',recalc);
});
recalc();

// Bulk check logic
const bulkDepartment = document.getElementById('bulkDepartment');
const bulkYear = document.querySelector('#bulkForm select[name="year"]');
const bulkMonth = document.querySelector('#bulkForm select[name="month"]');
const bulkPeriodType = document.getElementById('bulkPeriodType');
const bulkAlertDiv = document.getElementById('bulkAlert');

// Confirmation on submit
document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const deptSelect = bulkDepartment;
    if (!deptSelect.value) { e.preventDefault(); return; }
    const deptName = deptSelect.options[deptSelect.selectedIndex].text;
    if (!confirm(`Generate payroll for all active employees in "${deptName}" for this period?`)) {
        e.preventDefault();
    }
});

async function checkExistingBulk() {
    const deptId = bulkDepartment.value;
    if (!deptId) {
        bulkAlertDiv.style.display = 'none';
        return;
    }
    const year = bulkYear.value;
    const month = bulkMonth.value;
    const periodType = bulkPeriodType.value;

    try {
        const res = await fetch(`{{ route('payroll.check-bulk') }}?year=${year}&month=${month}&pay_period_type=${periodType}&department_id=${deptId}`);
        const data = await res.json();

        const deptName = bulkDepartment.options[bulkDepartment.selectedIndex].text;
        let msg = '';

        if (data.total_employees === 0) {
            msg = `<div class="alert alert-info py-2 small mb-0">
                <i class="bi bi-info-circle-fill me-2"></i>
                No active employees found in <strong>${deptName}</strong>.
            </div>`;
        } else if (data.exists) {
            msg = `<div class="alert alert-warning py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>${deptName}:</strong> ${data.count} of ${data.total_employees} employees already have payroll for this period.
                ${data.remaining > 0 ? `<strong>${data.remaining}</strong> remaining will be generated.` : 'All employees already have records — nothing to generate.'}
            `;
            if (data.processed) {
                msg += `<br><span class="text-danger"><i class="bi bi-shield-lock-fill me-2"></i><strong>Warning:</strong> ${data.processed_count} records have already been <b>processed/paid</b>.</span>`;
            }
            msg += `</div>`;
        } else {
            msg = `<div class="alert alert-success py-2 small mb-0">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>${deptName}:</strong> ${data.total_employees} active employees ready for payroll generation.
            </div>`;
        }

        bulkAlertDiv.innerHTML = msg;
        bulkAlertDiv.style.display = 'block';
    } catch (e) {
        console.error('Error checking bulk payroll:', e);
    }
}

[bulkDepartment, bulkYear, bulkMonth, bulkPeriodType].forEach(el => {
    el.addEventListener('change', checkExistingBulk);
});
</script>
@endpush
