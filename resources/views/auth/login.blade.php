@extends('layouts.auth')
@section('title','Sign In')
@section('content')
<h2>Welcome back</h2>
<p class="sub">Sign in to Nexus HR</p>


@if(session('status'))
<div class="alert alert-success mb-3">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('login') }}">
@csrf
<div class="mb-3">
    <label class="form-label">Email Address</label>
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
    </div>
    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Password</label>
    <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Enter your password" required>
    </div>
    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label small" for="remember">Remember me</label>
    </div>
    @if(Route::has('password.request'))
    <a href="{{ route('password.request') }}" class="small" style="color:#253D90;">Forgot password?</a>
    @endif
</div>
<button type="submit" class="btn btn-primary btn-auth"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
</form>
@endsection
