@extends('layouts.app')
@section('title','Edit Attendance')
@section('page-title','Edit Attendance')

@section('content')
<div class="page-header">
    <h1>Edit Attendance</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('attendance.index') }}">Attendance</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header">
        <h6><i class="bi bi-clock-history me-2"></i>{{ $attendance->employee->full_name }} — {{ $attendance->date->format('F d, Y') }}</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('attendance.update',$attendance) }}">
        @csrf @method('PUT')
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    @foreach(['present'=>'Present','absent'=>'Absent','late'=>'Late','half_day'=>'Half Day','on_leave'=>'On Leave'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ old('status',$attendance->status)==$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Time In</label>
                <input type="time" name="time_in" class="form-control" value="{{ old('time_in',$attendance->time_in) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Time Out</label>
                <input type="time" name="time_out" class="form-control" value="{{ old('time_out',$attendance->time_out) }}">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes',$attendance->notes) }}</textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Update</button>
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
