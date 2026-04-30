@extends('layouts.app')
@section('title','Edit Leave Request')
@section('page-title','Edit Leave Request')

@section('content')
<div class="page-header">
    <h1>Edit Leave Request</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('leaves.index') }}">Leaves</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-calendar-event me-2"></i>Edit Leave Application</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('leaves.update', $leave) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
            <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                <option value="">Select Leave Type</option>
                @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}" {{ old('leave_type_id', $leave->leave_type_id) == $lt->id ? 'selected' : '' }}>
                    {{ $lt->name }} (max {{ $lt->max_days_per_year }} days/year)
                </option>
                @endforeach
            </select>
            @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="startDate"
                       class="form-control @error('start_date') is-invalid @enderror"
                       value="{{ old('start_date', $leave->start_date->format('Y-m-d')) }}" required>
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">End Date <span class="text-danger">*</span></label>
                <input type="date" name="end_date" id="endDate"
                       class="form-control @error('end_date') is-invalid @enderror"
                       value="{{ old('end_date', $leave->end_date->format('Y-m-d')) }}" required>
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mb-3" id="daysPreview">
            <div class="alert alert-info mb-0 py-2 small">
                <i class="bi bi-info-circle me-1"></i>Duration: <strong id="daysCount">{{ $leave->total_days }}</strong> working day(s)
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Reason <span class="text-danger">*</span></label>
            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                      rows="4" required>{{ old('reason', $leave->reason) }}</textarea>
            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Update Request</button>
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
    const endDate = new Date(end);
    while (cur <= endDate) {
        const d = cur.getDay();
        if (d !== 0 && d !== 6) count++;
        cur.setDate(cur.getDate() + 1);
    }
    return count;
}
function updateDays() {
    const s = document.getElementById('startDate').value;
    const e = document.getElementById('endDate').value;
    if (s && e && e >= s) {
        document.getElementById('daysCount').textContent = countWeekdays(new Date(s), new Date(e));
        document.getElementById('daysPreview').style.display = 'block';
    }
}
document.getElementById('startDate').addEventListener('change', updateDays);
document.getElementById('endDate').addEventListener('change', updateDays);
</script>
@endpush
