@extends('layouts.app')
@section('title','Edit Department')
@section('page-title','Edit Department')

@section('content')
<div class="page-header">
    <h1>Edit Department</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-diagram-3 me-2"></i>Department Details</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('departments.update',$department) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Department Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$department->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Department Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code',$department->code) }}" required>
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Manager</label>
            <select name="manager_id" class="form-select">
                <option value="">No Manager</option>
                @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ old('manager_id',$department->manager_id)==$emp->id?'selected':'' }}>{{ $emp->full_name }} — {{ $emp->position }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description',$department->description) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Positions / Roles</label>
            <textarea name="positions" class="form-control @error('positions') is-invalid @enderror" rows="3" placeholder="e.g. Manager, Senior Developer, Junior Developer">{{ old('positions', is_array($department->positions) ? implode(', ', $department->positions) : '') }}</textarea>
            <small class="text-muted">Separate multiple positions with a comma.</small>
            @error('positions')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $department->is_active?'checked':'' }}>
            <label class="form-check-label" for="is_active">Active Department</label>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
