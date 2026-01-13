<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use App\Models\PurchaseRequest;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $account = Auth::user() ?? Account::find(session('account_id'));

        // ✅ My Purchase Requests Stats
        $myRequests = Schema::hasTable('purchase_requests') && $account
            ? DB::table('purchase_requests')->where('account_id', $account->account_id)->count()
            : 0;

        // ✅ My Request Status Breakdown
        $myRequestsByStatus = [];
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('statuses') && $account) {
            $myRequestsByStatus = DB::table('purchase_requests')
                ->join('statuses', 'purchase_requests.status_id', '=', 'statuses.status_id')
                ->where('purchase_requests.account_id', $account->account_id)
                ->select('statuses.status_name', DB::raw('COUNT(*) as count'))
            ->groupBy('statuses.status_name')
            ->pluck('count', 'statuses.status_name')
            ->toArray();
        }

        // ✅ My Recent Requests
        $myRecentRequests = collect();
        if (Schema::hasTable('purchase_requests') && $account) {
            $myRecentRequests = PurchaseRequest::with('status')
                ->where('account_id', $account->account_id)
                ->orderByDesc('created_at')
                ->take(5)
                ->get();
        }

        // ✅ Total Items Requested
        $myTotalItems = 0;
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('purchase_request_items') && $account) {
            $myTotalItems = DB::table('purchase_request_items')
                ->whereIn('pr_no', function($query) use ($account) {
                    $query->select('pr_no')
                        ->from('purchase_requests')
                        ->where('account_id', $account->account_id);
                })
                ->sum('quantity');
        }

        // ✅ Total Value of My Requests
        $myTotalValue = 0;
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('purchase_request_items') && $account) {
            $myTotalValue = DB::table('purchase_request_items')
                ->whereIn('pr_no', function($query) use ($account) {
                    $query->select('pr_no')
                        ->from('purchase_requests')
                        ->where('account_id', $account->account_id);
                })
                ->select(DB::raw('SUM(quantity * estimated_unit_cost) as total'))
                ->value('total') ?? 0;
        }

        // ✅ Notifications (Unread)
        $unreadNotifications = collect();
        if (Schema::hasTable('notifications') && $account) {
            $unreadNotifications = $account->unreadNotifications ?? collect();
        }

        // ✅ My Recent Activity (from audit logs)
        $myRecentActivity = collect();
        if (Schema::hasTable('audit_logs') && $account) {
            $myRecentActivity = AuditLog::where('account_id', $account->account_id)
                ->latest('log_time')
                ->take(10)
                ->get();
        }

        // ✅ Quick Actions needed
        $pendingActions = 0;
        if (Schema::hasTable('purchase_requests') && $account) {
            // Count requests in draft status that need submission
            $pendingActions = DB::table('purchase_requests')
                ->where('account_id', $account->account_id)
                ->where('status_id', 1) // Draft status
                ->count();
        }

        // Ensure scalar defaults for numeric values
        $myRequests = (int) ($myRequests ?? 0);
        $myTotalItems = (int) ($myTotalItems ?? 0);
        $myTotalValue = (float) ($myTotalValue ?? 0);
        $pendingActions = (int) ($pendingActions ?? 0);

        return view('employee.dashboard', compact(
            'account',
            'myRequests',
            'myRequestsByStatus',
            'myRecentRequests',
            'myTotalItems',
            'myTotalValue',
            'unreadNotifications',
            'myRecentActivity',
            'pendingActions'
        ));
    }
}
