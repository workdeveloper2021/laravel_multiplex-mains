@extends('layouts.app')

@section('content')
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #ff5722 50%, #e0e0e0 50%);
            height: 100vh;
            overflow: hidden; /* Stop scrolling */

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
            <h2><i class="fas fa-user"></i> Sign In</h2>
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autofocus>

                    @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password" required>

                    @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
                @if(!request()->is('admin/login'))
                <div class="text-center">
                    <a href="{{ route('register') }}" class="text-orange-600 hover:underline text-sm font-medium">
                        SignUp Here
                    </a>
                </div>
                @endif
                <button type="submit" class="btn btn-orange w-100 text-white">
                    Sign In
                </button>

                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        Forgot Password?
                    </a>
                @endif

                @if(!request()->is('admin/login'))
                    <div class="mt-3 text-center">
                        <a href="{{ route('auth.google') }}"
                        class="btn btn-outline-dark w-100">
                            <i class="fab fa-google me-2"></i> Login with Google
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
