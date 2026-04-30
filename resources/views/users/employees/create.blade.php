@extends('layouts.app')
@section('title','Add Employee')
@section('page-title','Add Employee')

@section('content')
<div class="page-header">
    <h1>Add New Employee</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item active">Add Employee</li>
    </ol></nav>
</div>

<form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
@csrf
<div class="row g-3">
    <!-- Personal Info -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-person me-2"></i>Personal Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="firstName" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="lastName" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="text-danger">*</span>
                            <button type="button" class="btn btn-link p-0 ms-1" style="font-size:.72rem;text-decoration:none;" id="autoEmailBtn" title="Auto-generate from name">
                                <i class="bi bi-magic me-1"></i>Auto
                            </button>
                        </label>
                        <input type="email" name="email" id="emailField" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Auto-generated from name" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="phoneField" class="form-control" value="{{ old('phone', '+63-9') }}" placeholder="+63-9XX-XXX-XXXX">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <option value="male" {{ old('gender')=='male'?'selected':'' }}>Male</option>
                            <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
                            <option value="other" {{ old('gender')=='other'?'selected':'' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full address">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Info -->
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-briefcase me-2"></i>Employment Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id')==$dept->id?'selected':'' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Biometric ID</label>
                        <input type="text" name="biometric_id" class="form-control" value="{{ old('biometric_id', $nextBioId) }}">
                        <small class="text-muted"><i class="bi bi-magic me-1"></i>Auto-generated: {{ $nextBioId }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Position / Job Title <span class="text-danger">*</span></label>
                        <div class="position-relative" id="positionWrapper">
                            <input type="text" name="position" id="positionInput"
                                class="form-control @error('position') is-invalid @enderror"
                                value="{{ old('position') }}" placeholder="Select or type a position…"
                                autocomplete="off" required>
                            <div id="positionDropdown" style="display:none;position:absolute;z-index:9999;width:100%;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.1);max-height:200px;overflow-y:auto;top:calc(100% + 4px);"></div>
                        </div>
                        @error('position')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                        <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
                            <option value="full_time" {{ old('employment_type','full_time')=='full_time'?'selected':'' }}>Full Time</option>
                            <option value="part_time" {{ old('employment_type')=='part_time'?'selected':'' }}>Part Time</option>
                            <option value="contract" {{ old('employment_type')=='contract'?'selected':'' }}>Contract</option>
                            <option value="intern" {{ old('employment_type')=='intern'?'selected':'' }}>Intern</option>
                        </select>
                        @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hire Date <span class="text-danger">*</span></label>
                        <input type="date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror" value="{{ old('hire_date', date('Y-m-d')) }}" required>
                        @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted"><i class="bi bi-magic me-1"></i>Defaults to today</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Monthly Salary (₱) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', 18000) }}" min="0" step="1000" required>
                        </div>
                        @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-telephone-forward me-2"></i>Emergency Contact</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}" placeholder="+63-9XX-XXX-XXXX">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Avatar + Account -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="bi bi-image me-2"></i>Profile Photo</h6></div>
            <div class="card-body text-center">
                <div class="emp-avatar mx-auto mb-3" id="avatarPreview" style="width:80px;height:80px;font-size:1.8rem;">
                    <i class="bi bi-person"></i>
                </div>
                <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*" id="avatarInput">
                <small class="text-muted d-block mt-1">JPG, PNG. Max 2MB.</small>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6><i class="bi bi-key me-2"></i>Account Credentials</h6></div>
            <div class="card-body">
                <div class="alert alert-info mb-3 small p-2">
                    <i class="bi bi-lightning-fill me-1"></i><strong>Smart Auto-Fill:</strong> Email, Biometric ID, and Hire Date are auto-generated. Password (e.g. <code>juan1234</code>) will be shown after creation.
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Create Employee</button>
                    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
// ── Avatar Preview ────────────────────────────────────────────
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
            const prev = document.getElementById('avatarPreview');
            prev.innerHTML = `<img src="${ev.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:50%;">`;
        };
        reader.readAsDataURL(file);
    }
});

// ── Auto-Generate Email from First + Last Name ────────────────
const firstNameEl = document.getElementById('firstName');
const lastNameEl  = document.getElementById('lastName');
const emailEl     = document.getElementById('emailField');
let emailManuallyEdited = {{ old('email') ? 'true' : 'false' }};

function autoEmail() {
    if (emailManuallyEdited) return;
    const first = firstNameEl.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
    const last  = lastNameEl.value.trim().toLowerCase().replace(/[^a-z0-9]/g, '');
    if (first && last) {
        emailEl.value = `${first}.${last}@company.com`;
    } else if (first) {
        emailEl.value = `${first}@company.com`;
    } else {
        emailEl.value = '';
    }
}

[firstNameEl, lastNameEl].forEach(el => el.addEventListener('input', autoEmail));
emailEl.addEventListener('input', () => { emailManuallyEdited = true; });

document.getElementById('autoEmailBtn').addEventListener('click', () => {
    emailManuallyEdited = false;
    autoEmail();
});

// ── Custom Position Autocomplete ──────────────────────────────
const depts       = @json($departments);
const deptSelect  = document.querySelector('select[name="department_id"]');
const posInput    = document.getElementById('positionInput');
const posDropdown = document.getElementById('positionDropdown');

// Common positions that always show as suggestions
const defaultPositions = [
    'Manager', 'Assistant Manager', 'Supervisor', 'Team Lead',
    'Senior Developer', 'Junior Developer', 'Full Stack Developer', 'Frontend Developer', 'Backend Developer',
    'Software Engineer', 'QA Engineer', 'DevOps Engineer', 'System Administrator',
    'UI/UX Designer', 'Graphic Designer', 'Web Designer',
    'Project Manager', 'Product Manager', 'Scrum Master',
    'HR Manager', 'HR Officer', 'HR Assistant', 'Recruiter',
    'Accountant', 'Finance Officer', 'Payroll Officer', 'Bookkeeper',
    'Marketing Manager', 'Marketing Officer', 'Content Writer', 'SEO Specialist', 'Social Media Manager',
    'Sales Manager', 'Sales Representative', 'Account Executive', 'Business Development Officer',
    'Administrative Assistant', 'Office Administrator', 'Secretary', 'Receptionist', 'Clerk',
    'Customer Service Representative', 'Support Specialist', 'Technical Support',
    'IT Manager', 'IT Support', 'Network Engineer', 'Database Administrator',
    'Operations Manager', 'Logistics Coordinator', 'Warehouse Supervisor',
    'Legal Officer', 'Compliance Officer', 'Auditor',
    'Intern', 'Trainee', 'Consultant', 'Contractor',
    'Director', 'Vice President', 'Chief Executive Officer', 'Chief Technology Officer', 'Chief Financial Officer',
];
let currentDeptPositions = [];

function getSuggestions() {
    // Department-specific positions first, then defaults (de-duped)
    const combined = [...currentDeptPositions];
    defaultPositions.forEach(p => {
        if (!combined.some(c => c.toLowerCase() === p.toLowerCase())) combined.push(p);
    });
    return combined;
}

function buildDropdown(query) {
    posDropdown.innerHTML = '';
    const all = getSuggestions();
    const filtered = query
        ? all.filter(s => s.toLowerCase().includes(query.toLowerCase()))
        : all;

    if (!filtered.length) { posDropdown.style.display = 'none'; return; }

    // If dept positions exist, show them as a group header
    const deptCount = currentDeptPositions.filter(p => !query || p.toLowerCase().includes(query.toLowerCase())).length;

    filtered.forEach((pos, i) => {
        // Add section divider between dept and general positions
        if (deptCount > 0 && i === deptCount) {
            const divider = document.createElement('div');
            divider.style.cssText = 'padding:.3rem .85rem;font-size:.65rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;background:#f8fafc;border-bottom:1px solid #e2e8f0;';
            divider.textContent = 'Common Positions';
            posDropdown.appendChild(divider);
        }

        const item = document.createElement('div');
        const isDept = i < deptCount;
        item.style.cssText = 'padding:.5rem .85rem;cursor:pointer;font-size:.85rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.5rem;transition:background .12s;';
        item.innerHTML = `<i class="bi ${isDept ? 'bi-building' : 'bi-briefcase'}" style="color:${isDept ? '#16a34a' : '#253D90'};font-size:.75rem;"></i><span>${pos}</span>${isDept ? '<span class="badge bg-success bg-opacity-10 text-success ms-auto" style="font-size:.6rem;">Dept</span>' : ''}`;
        item.addEventListener('mouseenter', () => item.style.background = '#E3EDF9');
        item.addEventListener('mouseleave', () => item.style.background = '');
        item.addEventListener('mousedown', e => {
            e.preventDefault();
            posInput.value = pos;
            posDropdown.style.display = 'none';
        });
        posDropdown.appendChild(item);
    });
    posDropdown.style.display = 'block';
}

deptSelect.addEventListener('change', function() {
    const dept = depts.find(d => d.id == this.value);
    currentDeptPositions = (dept && Array.isArray(dept.positions)) ? dept.positions : [];
    if (document.activeElement === posInput) buildDropdown(posInput.value);
});

posInput.addEventListener('focus', () => buildDropdown(posInput.value));
posInput.addEventListener('input', () => buildDropdown(posInput.value));
posInput.addEventListener('blur',  () => setTimeout(() => { posDropdown.style.display = 'none'; }, 150));

// Keyboard navigation
posInput.addEventListener('keydown', e => {
    const items = posDropdown.querySelectorAll('div');
    let active = posDropdown.querySelector('.pos-active');
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!active) { items[0]?.classList.add('pos-active'); items[0] && (items[0].style.background='#E3EDF9'); }
        else { const next = active.nextElementSibling; if(next){active.classList.remove('pos-active');active.style.background='';next.classList.add('pos-active');next.style.background='#E3EDF9';} }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (active) { const prev = active.previousElementSibling; active.classList.remove('pos-active');active.style.background=''; if(prev){prev.classList.add('pos-active');prev.style.background='#E3EDF9';} }
    } else if (e.key === 'Enter' && active) {
        e.preventDefault();
        posInput.value = active.querySelector('span').textContent;
        posDropdown.style.display = 'none';
    } else if (e.key === 'Escape') {
        posDropdown.style.display = 'none';
    }
});

if (deptSelect.value) deptSelect.dispatchEvent(new Event('change'));
</script>
@endpush
