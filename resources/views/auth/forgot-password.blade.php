@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<h2>Forgot Password?</h2>
<p class="subtitle">Enter your email and we'll send you a reset link.</p>
@if(session('status'))
    <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
@endif
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-4">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
        </div>
        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn btn-primary btn-auth"><i class="bi bi-send me-2"></i>Send Reset Link</button>
</form>
<div class="auth-footer"><a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Back to Login</a></div>
@endsection
