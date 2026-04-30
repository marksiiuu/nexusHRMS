@extends('layouts.app')
@section('title','Add User')
@section('page-title','Add User')
@section('content')
<div class="page-header">
    <h1><i class="bi bi-person-plus me-2"></i>Add User</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item active">Add</li>
    </ol></nav>
</div>
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-person-plus me-2"></i>New User Account</h6></div>
    <div class="card-body">
        <div class="alert alert-info mb-4 small">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Auto Password:</strong> A default password will be auto-generated for this user (e.g. <code>juan1234</code>). It will be shown after creation. The user should change it on first login.
        </div>
        <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Select Role</option>
                @foreach(\App\Models\User::ROLES as $val=>$lbl)
                <option value="{{ $val }}" {{ old('role')==$val?'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <small class="text-muted">Admin can assign any role. The role determines what the user can access in the system.</small>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Create User</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
