@extends('layouts.app')
@section('title','Apply for Leave')
@section('page-title','Apply for Leave')

@section('content')
<div class="page-header">
    <h1>Apply for Leave</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
        <li class="breadcrumb-item active">Apply</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-calendar-plus me-2"></i>Leave Application</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('leaves.store') }}">
        @csrf
        @if(auth()->user()->hasHrAccess())
        <div class="mb-3">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('employee_id', auth()->user()->employee?->id)==$emp->id?'selected':'' }}>{{ $emp->full_name }}</option>
                @endforeach
            </select>
            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        @else
        <input type="hidden" name="employee_id" value="{{ auth()->user()->employee?->id }}">
        @endif
        <div class="mb-3">
            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
            <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                <option value="">Select Leave Type</option>
                @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}" {{ old('leave_type_id')==$lt->id?'selected':'' }}>
                    {{ $lt->name }} (max {{ $lt->max_days_per_year }} days/year{{ $lt->is_paid ? '' : ' – unpaid' }})
                </option>
                @endforeach
            </select>
            @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}" min="{{ today()->format('Y-m-d') }}" required id="startDate">
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}" min="{{ today()->format('Y-m-d') }}" required id="endDate">
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3" id="daysPreview" style="display:none;">
            <div class="alert alert-info mb-0 py-2 small"><i class="bi bi-info-circle me-1"></i>Estimated: <strong id="daysCount">0</strong> working day(s)</div>
        </div>
        <div class="mb-4">
            <label class="form-label">Reason <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required placeholder="Please provide a reason for your leave request…">{{ old('reason') }}</textarea>
            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Application</button>
            <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function countWeekdays(start, end) {
    let count = 0, cur = new Date(start);
    while (cur <= end) { const d = cur.getDay(); if (d !== 0 && d !== 6) count++; cur.setDate(cur.getDate()+1); }
    return count;
}
function updateDays() {
    const s = document.getElementById('startDate').value;
    const e = document.getElementById('endDate').value;
    const preview = document.getElementById('daysPreview');
    if (s && e && e >= s) {
        document.getElementById('daysCount').textContent = countWeekdays(new Date(s), new Date(e));
        preview.style.display = 'block';
    } else { preview.style.display = 'none'; }
}
document.getElementById('startDate').addEventListener('change', updateDays);
document.getElementById('endDate').addEventListener('change', updateDays);
</script>
@endpush
