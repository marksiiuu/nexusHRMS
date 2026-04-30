@extends('layouts.app')
@section('title','Record Attendance')
@section('page-title','Record Attendance')

@section('content')
<div class="page-header">
    <h1>Record Attendance</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
        <li class="breadcrumb-item active">Record</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-clock-history me-2"></i>Attendance Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('attendance.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('employee_id')==$emp->id?'selected':'' }}>
                    {{ $emp->full_name }} — {{ $emp->department?->name }}
                </option>
                @endforeach
            </select>
            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', today()->format('Y-m-d')) }}" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required id="statusSelect">
                    @foreach(['present'=>'Present','absent'=>'Absent','late'=>'Late','half_day'=>'Half Day','on_leave'=>'On Leave'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ old('status','present')==$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row g-3 mb-3" id="timeFields">
            <div class="col-md-6">
                <label class="form-label">Time In</label>
                <input type="time" name="time_in" class="form-control" value="{{ old('time_in','08:00') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Time Out</label>
                <input type="time" name="time_out" class="form-control" value="{{ old('time_out','17:00') }}">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes…">{{ old('notes') }}</textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Attendance</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('statusSelect').addEventListener('change', function() {
    document.getElementById('timeFields').style.display = ['absent','on_leave'].includes(this.value) ? 'none' : 'flex';
});
</script>
@endpush
