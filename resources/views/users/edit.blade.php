@extends('layouts.app')
@section('title','Edit User')
@section('page-title','Edit User')
@section('content')
<div class="page-header">
    <h1><i class="bi bi-person-gear me-2"></i>Edit User — {{ $user->name }}</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
</div>
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-header"><h6><i class="bi bi-person-gear me-2"></i>Edit User</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('users.update',$user) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name',$user->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email',$user->email) }}" required>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select @error('role') is-invalid @enderror" required {{ $user->id===auth()->id()?'disabled':'' }}>
                @foreach(\App\Models\User::ROLES as $val=>$lbl)
                <option value="{{ $val }}" {{ old('role',$user->role)===$val?'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
            @if($user->id===auth()->id())
                <input type="hidden" name="role" value="{{ $user->role }}">
                <small class="text-muted">You cannot change your own role.</small>
            @endif
            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 characters">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control">
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                {{ old('is_active',$user->is_active)?'checked':'' }}
                {{ $user->id===auth()->id()?'disabled':'' }}>
            <label class="form-check-label" for="is_active">Active Account</label>
            @if($user->id===auth()->id())<input type="hidden" name="is_active" value="1">@endif
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Save Changes</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
