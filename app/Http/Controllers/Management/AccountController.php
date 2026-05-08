<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Allowed system roles used across route middleware and gates.
     */
    private const ALLOWED_ROLES = [
        'custodian',
        'bac',
        'employee',
        'division_head',
        'iac',
        'admin',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $roleFilter = $request->input('role');

        $query = Account::query()->with('employee.section');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $prefixSearch = $search.'%';
                $builder->where('username', 'like', $prefixSearch)
                    ->orWhere('account_id', 'like', $prefixSearch)
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $prefixSearch = $search.'%';
                        $employeeQuery->where('employee_id', 'like', $prefixSearch)
                            ->orWhere('first_name', 'like', $prefixSearch)
                            ->orWhere('last_name', 'like', $prefixSearch)
                            ->orWhere('middle_name', 'like', $prefixSearch);
                    });
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $accounts = $query
            ->orderByDesc('account_id')
            ->paginate(5)
            ->withQueryString();

        $roleOptions = Account::query()
            ->select('role')
            ->distinct()
            ->orderBy('role')
            ->pluck('role');

        $roleBreakdown = Account::query()
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->orderBy('role')
            ->pluck('total', 'role');

        $stats = [
            'total' => Account::count(),
            'withEmployee' => Account::has('employee')->count(),
            'withoutEmployee' => Account::doesntHave('employee')->count(),
            'roles' => $roleBreakdown,
        ];

        $recentAccounts = Account::query()
            ->with('employee')
            ->orderByDesc('account_id')
            ->limit(5)
            ->get();

        // If this is an AJAX request, return the table partial only
        if ($request->ajax()) {
            $html = view('management.accounts._table', [
                'accounts' => $accounts,
            ])->render();

            return response()->json([
                'html' => $html,
                'total' => $accounts->total(),
            ]);
        }

        return view('management.accounts.index', [
            'accounts' => $accounts,
            'stats' => $stats,
            'roleOptions' => $roleOptions,
            'roleFilter' => $roleFilter,
            'search' => $search,
            'recentAccounts' => $recentAccounts,
        ]);
    }

    public function printPdf(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $roleFilter = $request->input('role');

        $query = Account::query()->with('employee.section');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $prefixSearch = $search.'%';
                $builder->where('username', 'like', $prefixSearch)
                    ->orWhere('account_id', 'like', $prefixSearch)
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $prefixSearch = $search.'%';
                        $employeeQuery->where('employee_id', 'like', $prefixSearch)
                            ->orWhere('first_name', 'like', $prefixSearch)
                            ->orWhere('last_name', 'like', $prefixSearch)
                            ->orWhere('middle_name', 'like', $prefixSearch);
                    });
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $accounts = $query->orderByDesc('account_id')->get();

        return view('management.accounts.print', [
            'accounts' => $accounts,
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $roleFilter = $request->input('role');

        $query = Account::query()->with('employee.section');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $prefixSearch = $search.'%';
                $builder->where('username', 'like', $prefixSearch)
                    ->orWhere('account_id', 'like', $prefixSearch)
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $prefixSearch = $search.'%';
                        $employeeQuery->where('employee_id', 'like', $prefixSearch)
                            ->orWhere('first_name', 'like', $prefixSearch)
                            ->orWhere('last_name', 'like', $prefixSearch)
                            ->orWhere('middle_name', 'like', $prefixSearch);
                    });
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $accounts = $query->orderByDesc('account_id')->get();

        $html = view('management.accounts.excel', ['accounts' => $accounts])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Accounts-' . date('Y-m-d') . '.xls"');
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(self::ALLOWED_ROLES)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $newRole = strtolower((string) $validated['role']);
        $currentRole = strtolower((string) $account->role);
        $hasPasswordUpdate = filled($validated['password'] ?? null);

        $sessionAccountId = (int) session('account_id');
        if ($sessionAccountId === (int) $account->account_id && $currentRole !== $newRole) {
            return response()->json([
                'message' => 'You cannot change your own role while logged in.',
                'errors' => [
                    'role' => ['Self role change is not allowed for safety.'],
                ],
            ], 422);
        }

        if ($currentRole === $newRole && ! $hasPasswordUpdate) {
            return response()->json([
                'message' => 'No changes detected.',
                'account' => [
                    'account_id' => $account->account_id,
                    'username' => $account->username,
                    'role' => $account->role,
                ],
            ]);
        }

        $updatePayload = [
            'role' => $newRole,
        ];

        if ($hasPasswordUpdate) {
            $updatePayload['password'] = Hash::make((string) $validated['password']);
        }

        $account->update($updatePayload);

        $message = $hasPasswordUpdate
            ? ($currentRole === $newRole ? 'Account password updated successfully.' : 'Account role and password updated successfully.')
            : 'Account role updated successfully.';

        return response()->json([
            'message' => $message,
            'account' => [
                'account_id' => $account->account_id,
                'username' => $account->username,
                'role' => $account->role,
            ],
        ]);
    }
}
