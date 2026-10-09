@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-5">
        <div class="card card-custom p-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-circle fs-1 text-primary"></i>
                <h3 class="fw-bold mt-2">Welcome Back</h3>
                <p class="text-muted">Sign in to your HRM account</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-gradient w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                </button>

                <div class="text-center mt-3">
                    <span class="text-muted">Don't have an account?</span>
                    <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-semibold">Register here</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
