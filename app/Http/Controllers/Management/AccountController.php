<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $roleFilter = $request->input('role');

        $query = Account::query()->with('employee.section');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('username', 'like', "%{$search}%")
                    ->orWhere('account_id', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('employee_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $accounts = $query
            ->orderBy('username')
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
                $builder->where('username', 'like', "%{$search}%")
                    ->orWhere('account_id', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('employee_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $accounts = $query->orderBy('username')->get();

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
                $builder->where('username', 'like', "%{$search}%")
                    ->orWhere('account_id', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('employee_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($roleFilter && $roleFilter !== 'all') {
            $query->where('role', $roleFilter);
        }

        $accounts = $query->orderBy('username')->get();

        $html = view('management.accounts.excel', ['accounts' => $accounts])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Accounts-' . date('Y-m-d') . '.xls"');
    }
}
