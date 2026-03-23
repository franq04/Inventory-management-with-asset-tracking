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

        // ✅ My monthly request trend (count + estimated value)
        $myRequestMonths = collect();
        $myRequestCounts = collect();
        $myRequestValues = collect();
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('purchase_request_items') && $account) {
            $monthlyRequests = DB::table('purchase_requests')
                ->leftJoin('purchase_request_items', 'purchase_requests.pr_no', '=', 'purchase_request_items.pr_no')
                ->where('purchase_requests.account_id', $account->account_id)
                ->selectRaw('DATE_FORMAT(purchase_requests.created_at, "%Y-%m") as month_key')
                ->selectRaw('DATE_FORMAT(purchase_requests.created_at, "%b %Y") as month_label')
                ->selectRaw('COUNT(DISTINCT purchase_requests.pr_no) as request_count')
                ->selectRaw('COALESCE(SUM(purchase_request_items.quantity * purchase_request_items.estimated_unit_cost), 0) as request_value')
                ->groupBy('month_key', 'month_label')
                ->orderBy('month_key')
                ->take(12)
                ->get();

            $myRequestMonths = $monthlyRequests->pluck('month_label')->values();
            $myRequestCounts = $monthlyRequests->pluck('request_count')->map(fn ($count) => (int) $count)->values();
            $myRequestValues = $monthlyRequests->pluck('request_value')->map(fn ($value) => round((float) $value, 2))->values();
        }

        // ✅ My top requested items
        $myTopItems = collect();
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('purchase_request_items') && $account) {
            $myTopItems = DB::table('purchase_request_items')
                ->join('purchase_requests', 'purchase_request_items.pr_no', '=', 'purchase_requests.pr_no')
                ->where('purchase_requests.account_id', $account->account_id)
                ->select('purchase_request_items.item_description', DB::raw('COUNT(*) as item_count'))
                ->groupBy('purchase_request_items.item_description')
                ->orderByDesc('item_count')
                ->take(8)
                ->get();
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
            'pendingActions',
            'myRequestMonths',
            'myRequestCounts',
            'myRequestValues',
            'myTopItems'
        ));
    }
}
