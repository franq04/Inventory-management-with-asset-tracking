<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl border border-slate-200 p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-[#1a3a2d]">Reset Password</h1>
            <p class="mt-2 text-sm text-slate-500">Create a new password for {{ $username }}.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <input type="hidden" name="username" value="{{ $username }}">

            <div>
                <label for="password" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">New Password</label>
                <input id="password" name="password" type="password" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" autocomplete="new-password" />
            </div>
            <div>
                <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15" autocomplete="new-password" />
            </div>

            <button type="submit" class="w-full rounded-xl bg-[#1a3a2d] py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#153024]">Update Password</button>
        </form>
    </div>
</body>
</html>
