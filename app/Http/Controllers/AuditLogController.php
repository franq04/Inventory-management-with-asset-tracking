<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        if (session('role') !== 'custodian') {
            abort(403, 'Access denied: Custodians only');
        }

        $logs = AuditLog::with('account')
            ->orderBy('log_time', 'desc')
            ->limit(200)
            ->get();

        return view('custodian.audit_logs', compact('logs'));
    }
}