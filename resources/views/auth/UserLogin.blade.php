@extends('layouts.app')

@section('content')
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #ff5722 50%, #e0e0e0 50%);
            height: 100vh;
            overflow: hidden;
            /* Stop scrolling */

            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 1.5rem 1rem;
                border-radius: 8px;
            }

            h2 {
                font-size: 1.5rem;
            }

            .btn {
                font-size: 1rem;
            }
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .form-control {
            height: 45px;
            border-radius: 5px;
        }

        .btn-orange {
            background-color: #ff5722;
            border: none;
        }

        .btn-orange:hover {
            background-color: #e64a19;
        }

        .btn-outline-orange {
            border-color: #ff5722;
            color: #ff5722;
        }

        .btn-outline-orange:hover,
        .btn-outline-orange.active {
            background-color: #ff5722;
            border-color: #ff5722;
            color: white;
        }

        .logo {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #fff;
            letter-spacing: 2px;
        }

        .forgot-link {
            text-align: right;
            display: block;
            margin-top: 10px;
        }
    </style>

    <div class="logo mt-5">MULTIPLEX PLAY</div>

    <div class="login-container">
        <div class="login-box">
            <h2><i class="fas fa-user"></i> User Login</h2>

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Login Toggle Buttons -->
            <div class="btn-group w-100 mb-3" role="group">
                <button type="button" id="emailLoginBtn" class="btn btn-outline-orange active">Email Login</button>
                <button type="button" id="otpLoginBtn" class="btn btn-outline-orange">OTP Login</button>
            </div>

            <!-- Email/Password Login Form -->
            <form method="POST" action="{{ route('user-login.post') }}" id="emailLoginForm">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" required autofocus>

                    @error('email')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                        name="password" required>

                    @error('password')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-orange w-100 text-white">
                    Login
                </button>
            </form>
            <!-- OTP Login Form -->
            <form method="POST" action="{{ route('user-send-otp') }}" id="otpLoginForm" style="display: none;">
                @csrf

                {{--  <div class="mb-3">
                    <label for="email_otp" class="form-label">Email</label>
                    <input id="email_otp" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}">

                    @error('email')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>  --}}

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror"
                        name="phone" value="{{ old('phone') }}">

                    @error('phone')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-orange w-100 text-white">
                    Send OTP
                </button>
            </form>

            <div class="mt-3 text-center">
                <a href="{{ route('auth.google') }}" class="btn btn-outline-dark w-100">
                    <i class="fab fa-google me-2"></i> Login with Google
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailLoginBtn = document.getElementById('emailLoginBtn');
            const otpLoginBtn = document.getElementById('otpLoginBtn');
            const emailLoginForm = document.getElementById('emailLoginForm');
            const otpLoginForm = document.getElementById('otpLoginForm');

            emailLoginBtn.addEventListener('click', function() {
                emailLoginBtn.classList.add('active');
                otpLoginBtn.classList.remove('active');
                emailLoginForm.style.display = 'block';
                otpLoginForm.style.display = 'none';
            });

            otpLoginBtn.addEventListener('click', function() {
                otpLoginBtn.classList.add('active');
                emailLoginBtn.classList.remove('active');
                otpLoginForm.style.display = 'block';
                emailLoginForm.style.display = 'none';
            });
        });
    </script>
@endsection
