<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/pqslogo.png') }}">
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --primary-color: #f9bf0f;
            --primary-color-glow: rgba(249, 191, 15, 0.4);
            --secondary-color: #1a3a2d;
            --input-bg-color: #ffffff;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            overflow: hidden;
        }

        #background-gradient {
            width: 200%;
            height: 200%;
            position: fixed;
            top: -50%;
            left: -50%;
            z-index: -1;
            background: linear-gradient(-45deg, #e0e7ff, #f0f4f8, #fef3c7, #d1fae5);
            background-size: 400% 400%;
            animation: gradientShift 20s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        .animate-card-enter {
            animation: cardEnter 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            opacity: 0;
            transform: scale(0.96);
        }

        @keyframes cardEnter {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .shimmer-btn {
            position: relative;
            overflow: hidden;
        }

        .shimmer-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 75%;
            height: 100%;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.3) 50%, rgba(255, 255, 255, 0) 100%);
            transition: left 0.6s ease-in-out;
        }

        .shimmer-btn:hover::before {
            left: 125%;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div id="background-gradient"></div>

    <div class="w-full max-w-lg rounded-2xl bg-white/70 backdrop-blur-xl shadow-2xl border border-white/40 p-8 animate-card-enter">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-[#1a3a2d]">Forgot Password</h1>
            <p class="mt-2 text-sm text-slate-500">Enter your username and recovery email to receive a reset link.</p>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" autocomplete="username" />
            </div>
            <div>
                <label for="email" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Recovery Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" autocomplete="email" />
            </div>
            <button id="forgotSubmitBtn" type="submit" data-no-global-loading="true" class="shimmer-btn group w-full rounded-xl bg-[var(--secondary-color)] py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 ease-in-out hover:bg-[#153024] active:scale-[0.98]">
                Send Reset Link
            </button>
        </form>

        <div class="mt-5 text-center text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Back to Sign In</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('forgotPasswordForm');
            const submitBtn = document.getElementById('forgotSubmitBtn');

            if (!form || !submitBtn) {
                return;
            }

            const defaultLabel = submitBtn.innerHTML;

            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i><span>Sending...</span>';
                form.setAttribute('aria-busy', 'true');

                window.setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = defaultLabel;
                    }
                }, 10000);
            });
        });
    </script>
</body>
</html>
