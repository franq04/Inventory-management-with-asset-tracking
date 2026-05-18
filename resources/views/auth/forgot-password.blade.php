<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200 p-8">
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

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" autocomplete="username" />
            </div>
            <div>
                <label for="email" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Recovery Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" autocomplete="email" />
            </div>
            <button type="submit" class="w-full rounded-xl bg-[#1a3a2d] py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#153024]">Send Reset Link</button>
        </form>

        <div class="mt-5 text-center text-sm">
            <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Back to Sign In</a>
        </div>
    </div>
</body>
</html>
