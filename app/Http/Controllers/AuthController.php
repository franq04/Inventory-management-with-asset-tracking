<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Mail\PasswordResetLink;
use App\Models\Account;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $account = Account::where('username', $request->username)->first();

        $valid = false;
        if ($account) {
            // Support bcrypt (Hash::check) and legacy SHA-256 hex hashes from the seed data
            $valid = Hash::check($request->password, $account->password)
                || hash('sha256', $request->password) === $account->password;
        }

        if ($account && $valid) {
            $employee = $account->employee;
            $sessionProfilePath = $employee?->profile_img
                ? preg_replace('/^storage\//', '', ltrim($employee->profile_img, '/'))
                : null;

            // Log in using Laravel guard (for Auth::user()) and set custom session
            Auth::login($account);
            // Set session
            Session::put('account_id', $account->account_id);
            Session::put('username', $account->username);
            Session::put('role', $account->role);
            Session::put('profile_img', $sessionProfilePath);

            // Log action
            AuditLog::create([
                'account_id' => $account->account_id,
                'table_name' => 'accounts',
                'action' => 'LOGIN',
                'description' => "User {$account->username} logged in to the system",
                'log_time' => now(),
            ]);

            // Determine redirect based on role
            $role = strtolower($account->role);

            // Decide redirect per role. BAC should be routed to the BAC purchase requests queue
            $redirectUrl = match ($role) {
                'custodian' => route('dashboard'),
                'admin' => route('dashboard'),
                'bac' => route('bac.requests.index'),
                'employee' => route('employee.dashboard'),
                'division_head' => route('division.requests.index'),
                'iac' => route('custodian.inspection.index'),
                default => route('dashboard'),
            };

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect()->to($redirectUrl);
        }

        $errorBag = [
            'username' => 'Invalid username or password.',
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid username or password.',
                'errors' => $errorBag,
            ], 422);
        }

        return back()->withErrors($errorBag)->withInput($request->only('username'));
    }


    public function logout()
    {
        if (Session::has('account_id')) {
            AuditLog::create([
                'account_id' => Session::get('account_id'),
                'table_name' => 'accounts',
                'action' => 'LOGOUT',
                'description' => "User " . Session::get('username') . " logged out",
            ]);
        }
        Auth::logout();
        Session::flush();
        return redirect()->route('login')->with('toast', [
            'message' => 'You have been successfully logged out.',
            'type' => 'success',
        ]);
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $account = Account::where('username', $validated['username'])->first();
        $employeeEmail = $account?->employee?->email;

        if (! $account || ! $employeeEmail || strcasecmp($employeeEmail, $validated['email']) !== 0) {
            return back()
                ->withErrors(['username' => 'We could not find a matching account for the provided email.'])
                ->withInput($request->only('username', 'email'));
        }

        $table = config('auth.passwords.users.table', 'password_reset_tokens');
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        DB::table($table)
            ->where('email', $employeeEmail)
            ->where('username', $account->username)
            ->delete();

        DB::table($table)->insert([
            'email' => $employeeEmail,
            'username' => $account->username,
            'token' => $tokenHash,
            'created_at' => now(),
        ]);

        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $employeeEmail,
            'username' => $account->username,
        ]);

        Mail::to($employeeEmail)->send(new PasswordResetLink($account->username, $resetUrl));

        return back()->with('status', 'A reset link has been sent to your recovery email.');
    }

    public function showResetForm(Request $request)
    {
        $token = (string) $request->query('token');
        $email = (string) $request->query('email');
        $username = (string) $request->query('username');

        if ($token === '' || $email === '' || $username === '') {
            return redirect()->route('password.request')->withErrors([
                'username' => 'The password reset link is invalid or incomplete.',
            ]);
        }

        if (! $this->isResetTokenValid($email, $username, $token)) {
            return redirect()->route('password.request')->withErrors([
                'username' => 'The password reset link is invalid or has expired.',
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
            'username' => $username,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $this->isResetTokenValid($validated['email'], $validated['username'], $validated['token'])) {
            return back()->withErrors([
                'token' => 'The password reset link is invalid or has expired.',
            ]);
        }

        $updated = Account::where('username', $validated['username'])->update([
            'password' => Hash::make($validated['password']),
        ]);

        $table = config('auth.passwords.users.table', 'password_reset_tokens');
        DB::table($table)
            ->where('email', $validated['email'])
            ->where('username', $validated['username'])
            ->delete();

        if (! $updated) {
            return back()->withErrors([
                'username' => 'Unable to reset password for the provided account.',
            ]);
        }

        return redirect()->route('login')->with('toast', [
            'message' => 'Password updated. You can sign in now.',
            'type' => 'success',
        ]);
    }

    private function isResetTokenValid(string $email, string $username, string $token): bool
    {
        $table = config('auth.passwords.users.table', 'password_reset_tokens');
        $record = DB::table($table)
            ->where('email', $email)
            ->where('username', $username)
            ->first();

        if (! $record) {
            return false;
        }

        $tokenHash = hash('sha256', $token);
        if (! hash_equals((string) $record->token, $tokenHash)) {
            return false;
        }

        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at) : null;

        if (! $createdAt) {
            return false;
        }

        return $createdAt->addMinutes($expiresInMinutes)->isFuture();
    }
}