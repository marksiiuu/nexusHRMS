@extends('layouts.app')
@section('title','Edit Job Posting')
@section('page-title','Edit Job Posting')
@section('content')
<div class="page-header">
    <h1>Edit Job Posting</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('recruitment.index') }}">Recruitment</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>
<div class="row justify-content-center">
<div class="col-lg-8">
<form method="POST" action="{{ route('recruitment.update',$jobPosting) }}">
@csrf @method('PUT')
<div class="card mb-3">
    <div class="card-header"><h6><i class="bi bi-briefcase me-2"></i>Job Details</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Job Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title',$jobPosting->title) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">Select Department</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id',$jobPosting->department_id)==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                <select name="employment_type" class="form-select" required>
                    @foreach(['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('employment_type',$jobPosting->employment_type)==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Slots</label>
                <input type="number" name="slots" class="form-control" value="{{ old('slots',$jobPosting->slots) }}" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Min Salary (₱)</label>
                <input type="number" name="salary_min" class="form-control" value="{{ old('salary_min',$jobPosting->salary_min) }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max Salary (₱)</label>
                <input type="number" name="salary_max" class="form-control" value="{{ old('salary_max',$jobPosting->salary_max) }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Deadline</label>
                <input type="date" name="deadline" class="form-control" value="{{ old('deadline',$jobPosting->deadline?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    @foreach(['draft'=>'Draft','open'=>'Open','closed'=>'Closed'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('status',$jobPosting->status)==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Job Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description',$jobPosting->description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Requirements</label>
                <textarea name="requirements" class="form-control" rows="4">{{ old('requirements',$jobPosting->requirements) }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
    <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div>
</div>
@endsection
