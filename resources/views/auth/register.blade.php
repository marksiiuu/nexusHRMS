@extends('layouts.auth')
@section('title', 'Register')
@section('content')
<h2>Create Account</h2>
<p class="subtitle">Register as an HRMS user</p>

<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="Juan dela Cruz" required autofocus>
        </div>
        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="your@email.com" required>
        </div>
        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   placeholder="Min. 8 characters" required>
        </div>
        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="mb-4">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-auth">
        <i class="bi bi-person-plus me-2"></i>Create Account
    </button>
</form>
<div class="auth-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
@endsection
