<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('account')->orderByDesc('log_time');

        if ($request->filled('action')) {
            // Normalize action: allow either "STATUS UPDATE" or "STATUS_UPDATE" from UI
            $action = strtoupper(str_replace(' ', '_', trim($request->input('action'))));
            $query->where('action', $action);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_time', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_time', '<=', $request->date('date_to'));
        }

        $logs = $query->paginate(5)->withQueryString();

        if ($request->ajax()) {
            $html = view('custodian.audit_logs._table', [
                'logs' => $logs,
            ])->render();

            $pagination = $logs->hasPages() ? $logs->links()->toHtml() : '';

            return response()->json([
                'html' => $html,
                'total' => $logs->total(),
                'pagination' => $pagination,
            ]);
        }

        $availableActions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('custodian.audit_logs.index', compact('logs', 'availableActions'));
    }

    public function printPdf(Request $request): View
    {
        $query = AuditLog::with('account')->orderByDesc('log_time');

        if ($request->filled('action')) {
            $action = strtoupper(str_replace(' ', '_', trim($request->input('action'))));
            $query->where('action', $action);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_time', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_time', '<=', $request->date('date_to'));
        }

        $logs = $query->get();

        return view('custodian.audit_logs.print', [
            'logs' => $logs,
            'search' => $request->input('search'),
            'dateFrom' => $request->input('date_from'),
            'dateTo' => $request->input('date_to'),
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $query = AuditLog::with('account')->orderByDesc('log_time');

        if ($request->filled('action')) {
            $action = strtoupper(str_replace(' ', '_', trim($request->input('action'))));
            $query->where('action', $action);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('description', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($accountQuery) use ($search) {
                        $accountQuery->where('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('log_time', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('log_time', '<=', $request->date('date_to'));
        }

        $logs = $query->get();

        $html = view('custodian.audit_logs.excel', ['logs' => $logs])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Audit-Logs-' . date('Y-m-d') . '.xls"');
    }
}
