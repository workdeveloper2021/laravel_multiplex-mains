@extends('layouts.app')

@section('content')
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(to bottom, #ff5722 50%, #e0e0e0 50%);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 1.8rem;
        }

        .otp-inputs {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .otp-box {
            width: 45px;
            height: 55px;
            font-size: 24px;
            text-align: center;
            border: 2px solid #ccc;
            border-radius: 10px;
            outline: none;
            transition: 0.2s;
        }

        .otp-box:focus {
            border-color: #ff5722;
            box-shadow: 0 0 5px #ff5722;
        }

        .btn-orange {
            background-color: #ff5722;
            border: none;
        }

        .btn-orange:hover {
            background-color: #e64a19;
        }

        .logo {
            position: absolute;
            top: 30px;
            font-size: 28px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 2px;
        }

        @media (max-width: 576px) {
            .login-box {
                padding: 30px 20px;
            }

            .otp-box {
                width: 100%;
                height: 50px;
                font-size: 20px;
            }

            .otp-inputs {
                gap: 8px;
            }

            .logo {
                font-size: 22px;
                top: 20px;
            }
        }
    </style>

    <div class="logo">MULTIPLEX PLAY</div>

    <div class="login-container">
        <div class="login-box">
            <h2><i class="fas fa-lock"></i> Enter OTP</h2>

            <form method="POST" action="{{ route('user-verify-otp') }}" autocomplete="one-time-code">
                @csrf

                <input type="hidden" name="user_id" value="{{ session('user_id') }}">

                <div class="otp-inputs mb-4">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text" name="otp[]" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" required>
                    @endfor
                </div>

                <button type="submit" class="btn btn-orange w-100 text-white mb-2">Verify OTP</button>
            </form>


            <!-- Resend OTP Button -->
            <form method="POST" action="{{ route('user-send-otp') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">
                <input type="hidden" name="phone" value="{{ session('phone') }}">
                <button type="submit" class="btn btn-link w-100 text-center" style="text-decoration: underline; color: #ff5722;">
                    Resend OTP
                </button>
            </form>
            @if(session('success'))
                <div class="alert alert-danger mt-3">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mt-3">
                    {{ session('error') }}
                </div>
            @endif
        </div>

    <script>
        const boxes = document.querySelectorAll('.otp-box');

        boxes.forEach((box, idx) => {
            box.addEventListener('input', () => {
                if (box.value.length === 1 && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                }
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !box.value && idx > 0) {
                    boxes[idx - 1].focus();
                }
            });
        });

        // Autofocus first box on page load
        window.addEventListener('load', () => boxes[0].focus());
    </script>
@endsection
