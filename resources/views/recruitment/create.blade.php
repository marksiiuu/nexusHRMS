@extends('layouts.app')
@section('title','New Job Posting')
@section('page-title','New Job Posting')
@section('content')
<div class="page-header">
    <h1>Create Job Posting</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('recruitment.index') }}">Recruitment</a></li>
        <li class="breadcrumb-item active">Create</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<form method="POST" action="{{ route('recruitment.store') }}">
@csrf
<div class="card mb-3">
    <div class="card-header"><h6><i class="bi bi-briefcase me-2"></i>Job Details</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Job Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">Select Department</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Employment Type <span class="text-danger">*</span></label>
                <select name="employment_type" class="form-select" required>
                    @foreach(['full_time'=>'Full Time','part_time'=>'Part Time','contract'=>'Contract','intern'=>'Intern'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('employment_type','full_time')==$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">No. of Slots <span class="text-danger">*</span></label>
                <input type="number" name="slots" class="form-control" value="{{ old('slots',1) }}" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Min Salary (₱)</label>
                <input type="number" name="salary_min" class="form-control" value="{{ old('salary_min') }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max Salary (₱)</label>
                <input type="number" name="salary_max" class="form-control" value="{{ old('salary_max') }}" min="0" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Application Deadline</label>
                <input type="date" name="deadline" class="form-control" value="{{ old('deadline') }}" min="{{ today()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="draft" {{ old('status','draft')=='draft'?'selected':'' }}>Draft</option>
                    <option value="open" {{ old('status')=='open'?'selected':'' }}>Open</option>
                    <option value="closed" {{ old('status')=='closed'?'selected':'' }}>Closed</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Job Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Requirements</label>
                <textarea name="requirements" class="form-control" rows="4" placeholder="Qualifications, skills, experience…">{{ old('requirements') }}</textarea>
            </div>
        </div>
    </div>
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Create Posting</button>
    <a href="{{ route('recruitment.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
</div>
</div>
@endsection
