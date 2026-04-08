<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
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
}