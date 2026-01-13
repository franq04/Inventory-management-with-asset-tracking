<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Import Auth facade
use Illuminate\Support\Facades\Schema;
use App\Models\InspectionReportItem;
use App\Models\PqsRecord;
use App\Models\Status;

class DashboardController extends Controller
{
    public function index()
    {
        // Use Auth::user() to get the currently authenticated user.
        // This is guaranteed to work because of the 'auth.session' middleware.
        $account = Auth::user() ?? Account::find(session('account_id'));

        // ✅ Summary counts (guarded if tables don't exist yet)
        $totalRequests = Schema::hasTable('purchase_requests') ? DB::table('purchase_requests')->count() : 0;
        $totalOrders = Schema::hasTable('purchase_orders') ? DB::table('purchase_orders')->count() : 0;
        $totalAssets = Schema::hasTable('pqs') ? DB::table('pqs')->count() : 0;

        // Calculate total expense from purchase order items (quantity * unit_cost)
        $totalExpense = 0;
        if (Schema::hasTable('purchase_orders') && Schema::hasTable('purchase_order_items')) {
            $totalExpense = DB::table('purchase_order_items')
                ->select(DB::raw('SUM(quantity * unit_cost) as total'))
                ->value('total') ?? 0;
        } elseif (Schema::hasTable('purchase_orders')) {
            // Fallback: if items table is missing, try an approximate total from a known PO field if available
            $totalExpense = DB::table('purchase_orders')
                ->select(DB::raw('SUM(ors_burs_amount) as total'))
                ->value('total') ?? 0;
        }

        $totalIcs = Schema::hasTable('ics') ? DB::table('ics')->sum('quantity') : 0;

        // ✅ Inventory assignment pipeline stats
        $inventoryStats = [
            'accepted' => 0,
            'ready' => 0,
            'recorded' => 0,
            'withIcs' => 0,
            'withPar' => 0,
            'awaiting' => 0,
        ];

        if (Schema::hasTable('inspection_report_items')) {
            $assignmentQuery = InspectionReportItem::query()
                ->where('quantity_accepted', '>', 0);

            if (Schema::hasTable('statuses')) {
                $assignmentQuery->whereIn('inspection_status_id', [Status::ITEM_ACCEPTED, Status::ITEM_RECORDED]);
            }

            $inventoryStats['accepted'] = (clone $assignmentQuery)->count();
            $inventoryStats['ready'] = (clone $assignmentQuery)->whereNull('property_no')->count();
            $inventoryStats['recorded'] = max(0, $inventoryStats['accepted'] - $inventoryStats['ready']);
        }

        if (Schema::hasTable('pqs')) {
            if (Schema::hasTable('ics')) {
                $inventoryStats['withIcs'] = PqsRecord::whereHas('icsRecord')->count();
            }

            if (Schema::hasTable('par')) {
                $inventoryStats['withPar'] = PqsRecord::whereHas('parRecord')->count();
            }

            if (Schema::hasTable('ics') && Schema::hasTable('par')) {
                $inventoryStats['awaiting'] = PqsRecord::doesntHave('icsRecord')
                    ->whereDoesntHave('parRecord')
                    ->count();
            } else {
                $inventoryStats['awaiting'] = max(0, DB::table('pqs')->count() - $inventoryStats['withIcs'] - $inventoryStats['withPar']);
            }
        }

        // ✅ Monthly expenses (for chart)
        $monthlyExpenses = collect();
        if (Schema::hasTable('purchase_orders') && Schema::hasTable('purchase_order_items')) {
            $monthlyExpenses = DB::table('purchase_orders')
                ->join('purchase_order_items', 'purchase_orders.po_no', '=', 'purchase_order_items.po_no')
                ->selectRaw('DATE_FORMAT(purchase_orders.order_date, "%Y-%m") as month_key, DATE_FORMAT(purchase_orders.order_date, "%b %Y") as month_label, SUM(purchase_order_items.quantity * purchase_order_items.unit_cost) as total')
                ->groupBy('month_key', 'month_label')
                ->orderBy('month_key')
                ->take(12)
                ->get();
        }

        $months = $monthlyExpenses->pluck('month_label');
        $totals = $monthlyExpenses->pluck('total');

        // ✅ Monthly custodial records (for assets trend)
        $monthlyPqs = collect();
        if (Schema::hasTable('pqs')) {
            $monthlyPqs = DB::table('pqs')
                ->selectRaw('DATE_FORMAT(date_acquired, "%Y-%m") as month_key, DATE_FORMAT(date_acquired, "%b %Y") as month_label, COUNT(*) as total_assets, SUM(total_value) as total_value')
                ->groupBy('month_key', 'month_label')
                ->orderBy('month_key')
                ->take(12)
                ->get();
        }

        $inventoryMonths = $monthlyPqs->pluck('month_label');
        $inventoryCounts = $monthlyPqs->pluck('total_assets');
        $inventoryValues = $monthlyPqs->pluck('total_value');

        // ✅ Recent activity (with user)
        $recentLogs = Schema::hasTable('audit_logs')
            ? AuditLog::with('account')->latest('log_time')->take(5)->get()
            : collect();

        // ✅ Most requested items report (from purchase_request_items)
        $topRequests = collect();
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('purchase_request_items')) {
            $topRequests = DB::table('purchase_request_items')
                ->select('item_description', DB::raw('COUNT(*) as count'))
                ->groupBy('item_description')
                ->orderByDesc('count')
                ->take(5)
                ->get();
        }

        // ✅ Most expensive purchase orders
        $topExpenses = collect();
        if (Schema::hasTable('purchase_orders') && Schema::hasTable('purchase_order_items')) {
            $topExpenses = DB::table('purchase_orders')
                ->join('purchase_order_items', 'purchase_orders.po_no', '=', 'purchase_order_items.po_no')
                ->select(
                    'purchase_orders.po_no',
                    DB::raw('GROUP_CONCAT(purchase_order_items.item_description SEPARATOR "; ") as description'),
                    DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_cost) as total')
                )
                ->groupBy('purchase_orders.po_no')
                ->orderByDesc('total')
                ->take(5)
                ->get();
        }

        // ✅ Top asset categories (PQS breakdown)
        $topCategories = collect();
        if (Schema::hasTable('pqs') && Schema::hasTable('categories')) {
            $topCategories = DB::table('pqs')
                ->join('categories', 'pqs.cat_id', '=', 'categories.cat_id')
                ->select(
                    'categories.cat_name',
                    DB::raw('COUNT(*) as total_assets'),
                    DB::raw('SUM(pqs.total_value) as total_value')
                )
                ->groupBy('categories.cat_name')
                ->orderByDesc('total_assets')
                ->take(5)
                ->get();
        }

        // ✅ Status summary for PRs
        $statusSummary = collect();
        if (Schema::hasTable('purchase_requests') && Schema::hasTable('statuses')) {
            $statusSummary = DB::table('purchase_requests')
                ->join('statuses', 'purchase_requests.status_id', '=', 'statuses.status_id')
                ->select('statuses.status_name', DB::raw('COUNT(*) as count'))
                ->groupBy('statuses.status_name')
                ->pluck('count', 'statuses.status_name');
        }

        // ✅ Latest custodial records
        $recentPqs = Schema::hasTable('pqs')
            ? PqsRecord::with(['category.parent', 'icsRecord', 'parRecord'])
                ->orderByDesc('date_acquired')
                ->take(5)
                ->get()
            : collect();

        // Normalize chart collections for the frontend
        $months = $months->values();
        $totals = $totals->map(fn ($value) => round((float) $value, 2))->values();
        $inventoryMonths = $inventoryMonths->values();
        $inventoryCounts = $inventoryCounts->map(fn ($value) => (int) $value)->values();
        $inventoryValues = $inventoryValues->map(fn ($value) => round((float) $value, 2))->values();

        // ✅ Fetch unread notifications for the logged-in user
        $unreadNotifications = (Schema::hasTable('notifications') && $account)
            ? $account->unreadNotifications
            : collect();

        return view('custodian.dashboard', compact(
            'account',
            'unreadNotifications',
            'totalRequests',
            'totalOrders',
            'totalAssets',
            'totalExpense',
            'totalIcs',
            'months',
            'totals',
            'inventoryStats',
            'inventoryMonths',
            'inventoryCounts',
            'inventoryValues',
            'recentLogs',
            'topRequests',
            'topExpenses',
            'topCategories',
            'recentPqs',
            'statusSummary'
        ));
    }
}