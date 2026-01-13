<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Custodian\AuditLogController;
use App\Http\Controllers\Custodian\InspectionController;
use App\Http\Controllers\Custodian\InventoryAssignmentController;
use App\Http\Controllers\Custodian\PurchaseOrderController;
use App\Http\Controllers\Custodian\PurchaseRequestController as CustodianPurchaseRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Employee\PurchaseRequestController as EmployeePurchaseRequestController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Management\AccountController;
use App\Http\Controllers\Management\CategoryController;
use App\Http\Controllers\Management\EmployeeController;
use App\Http\Controllers\Inventory\IcsController;
use App\Http\Controllers\Inventory\ParController;
use App\Http\Controllers\Inventory\PqsController;

Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================
// 🔐 AUTHENTICATION ROUTES
// ==========================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth.session'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

    Route::middleware(['auth.session', 'role:division_head'])->group(function () {
        Route::prefix('division')->name('division.')->group(function () {
            Route::get('purchase-requests', [CustodianPurchaseRequestController::class, 'index'])
                ->name('requests.index');

            Route::get('purchase-requests/{purchase_request}', [CustodianPurchaseRequestController::class, 'show'])
                ->name('requests.show');

            Route::post('purchase-requests/{purchase_request}/status', [CustodianPurchaseRequestController::class, 'updateStatus'])
                ->name('requests.update-status');
        });
    });

// ==========================
// 🧾 DASHBOARD (Custodian)
// ==========================
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth.session', 'role:custodian'])
    ->name('dashboard');

Route::prefix('custodian')->name('custodian.')->middleware(['auth.session'])->group(function () {
Route::middleware('role:custodian')->group(function () {
        Route::get('fund-allocations', [App\Http\Controllers\Custodian\FundAllocationController::class, 'index'])
            ->name('fund_allocations.index');
        Route::get('fund-allocations/suggest', [App\Http\Controllers\Custodian\FundAllocationController::class, 'suggest'])
            ->name('fund_allocations.suggest');
        Route::post('fund-allocations', [App\Http\Controllers\Custodian\FundAllocationController::class, 'store'])
            ->name('fund_allocations.store');
        Route::put('fund-allocations/{fundAllocation}', [App\Http\Controllers\Custodian\FundAllocationController::class, 'update'])
            ->name('fund_allocations.update');
        Route::delete('fund-allocations/{fundAllocation}', [App\Http\Controllers\Custodian\FundAllocationController::class, 'destroy'])
            ->name('fund_allocations.destroy');

        Route::get('purchase-requests', [CustodianPurchaseRequestController::class, 'index'])
            ->name('requests.index');

        Route::get('purchase-requests/{purchase_request}', [CustodianPurchaseRequestController::class, 'show'])
            ->name('requests.show');

        Route::post('purchase-requests/{purchase_request}/status', [CustodianPurchaseRequestController::class, 'updateStatus'])
            ->name('requests.update-status');

        Route::patch('purchase-requests/items/{purchase_request_item}', [CustodianPurchaseRequestController::class, 'updateItem'])
            ->name('requests.items.update');

        Route::get('purchase-orders', [
            PurchaseOrderController::class,
            'index',
        ])->name('orders.index');

        Route::get('purchase-orders/create', [
            PurchaseOrderController::class,
            'create',
        ])->name('orders.create');

        Route::post('purchase-orders', [
            PurchaseOrderController::class,
            'store',
        ])->name('orders.store');

        Route::patch('purchase-orders/items/{purchase_order_item}', [
            PurchaseOrderController::class,
            'updateItem',
        ])->name('orders.items.update');

        Route::post('purchase-orders/items/{purchase_order_item}/retain', [
            PurchaseOrderController::class,
            'retainOriginal',
        ])->name('orders.items.retain');

        Route::get('purchase-orders/{purchase_order}', [
            PurchaseOrderController::class,
            'show',
        ])->name('orders.show');

        Route::post('purchase-orders/{purchase_order}/accept', [
            PurchaseOrderController::class,
            'acceptOrder',
        ])->name('orders.accept');

        Route::post('purchase-orders/items/{purchase_order_item}/receive', [
            PurchaseOrderController::class,
            'receiveItem',
        ])->name('orders.items.receive');

        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit_logs.index');
        Route::get('audit-logs/export/pdf', [AuditLogController::class, 'printPdf'])
            ->name('audit_logs.print.pdf');
        Route::get('audit-logs/export/excel', [AuditLogController::class, 'exportExcel'])
            ->name('audit_logs.export.excel');
    });

    Route::middleware('role:custodian,iac')->group(function () {
        Route::get('inspection-acceptance', [
            InspectionController::class,
            'index',
        ])->name('inspection.index');

        Route::get('purchase-orders/{purchase_order}/inspection', [
            InspectionController::class,
            'form',
        ])->name('inspection.form');

        Route::post('purchase-orders/{purchase_order}/inspection', [
            InspectionController::class,
            'store',
        ])->name('inspection.store');

        Route::get('inventory-assignment', [
            InventoryAssignmentController::class,
            'index',
        ])->name('inventory.index');

        Route::get('inventory-assignment/items', [
            InventoryAssignmentController::class,
            'items',
        ])->name('inventory.items');

        Route::get('inventory-assignment/items/{inspection_report_item}', [
            InventoryAssignmentController::class,
            'show',
        ])->name('inventory.show');

        Route::post('inventory-assignment/items/{inspection_report_item}/pqs', [
            InventoryAssignmentController::class,
            'store',
        ])->name('inventory.store');
    });
});

// Lightweight management placeholders so sidebar links render even if full modules
Route::middleware(['auth.session', 'role:custodian'])->group(function () {
    Route::prefix('management')->group(function () {
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('accounts/export/pdf', [AccountController::class, 'printPdf'])->name('accounts.print.pdf');
    Route::get('accounts/export/excel', [AccountController::class, 'exportExcel'])->name('accounts.export.excel');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/export/pdf', [CategoryController::class, 'printPdf'])->name('categories.print.pdf');
    Route::get('categories/export/excel', [CategoryController::class, 'exportExcel'])->name('categories.export.excel');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('categories/{category}/children', [CategoryController::class, 'storeChild'])->name('categories.children.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::resource('employees', EmployeeController::class)
            ->except(['show'])
            ->names('employees');
        Route::get('employees-export/pdf', [EmployeeController::class, 'printPdf'])->name('employees.print.pdf');
        Route::get('employees-export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    });

    Route::prefix('inventory')->group(function () {
        Route::get('pqs', [PqsController::class, 'index'])->name('pqs.index');
        Route::get('pqs/export/pdf', [PqsController::class, 'printPdf'])->name('pqs.print.pdf');
        Route::get('pqs/export/excel', [PqsController::class, 'exportExcel'])->name('pqs.export.excel');
        Route::get('pqs/{pqsRecord}', [PqsController::class, 'show'])->name('pqs.show');
        Route::get('ics', [IcsController::class, 'index'])->name('ics.index');
        Route::get('ics/export/pdf', [IcsController::class, 'printPdf'])->name('ics.print.pdf');
        Route::get('ics/export/excel', [IcsController::class, 'exportExcel'])->name('ics.export.excel');
        Route::get('ics/{icsRecord}', [IcsController::class, 'show'])->name('ics.show');
        Route::get('ics/{icsRecord}/print', [IcsController::class, 'print'])->name('ics.print');
        Route::get('par', [ParController::class, 'index'])->name('par.index');
        Route::get('par/export/pdf', [ParController::class, 'printPdf'])->name('par.print.pdf');
        Route::get('par/export/excel', [ParController::class, 'exportExcel'])->name('par.export.excel');
        Route::get('par/{parRecord}', [ParController::class, 'show'])->name('par.show');
        Route::get('par/{parRecord}/print', [ParController::class, 'print'])->name('par.print');
    });
});

// ==========================
// 🏛️ BAC (Bids and Awards Committee) ROUTES
// ==========================
// STRICT: Only BAC role can access these routes
Route::middleware(['auth.session', 'role:bac'])->prefix('bac')->name('bac.')->group(function () {
    // BAC Purchase Request Management - Final Approval Authority
    Route::get('purchase-requests', [App\Http\Controllers\BAC\PurchaseRequestController::class, 'index'])
        ->name('requests.index');

    Route::get('purchase-requests/{purchase_request}', [App\Http\Controllers\BAC\PurchaseRequestController::class, 'show'])
        ->name('requests.show');

    // BAC Exclusive Actions
    Route::post('purchase-requests/{purchase_request}/review', [App\Http\Controllers\BAC\PurchaseRequestController::class, 'moveToReview'])
        ->name('requests.review');

    Route::post('purchase-requests/{purchase_request}/approve', [App\Http\Controllers\BAC\PurchaseRequestController::class, 'approve'])
        ->name('requests.approve');

    Route::post('purchase-requests/{purchase_request}/cancel', [App\Http\Controllers\BAC\PurchaseRequestController::class, 'cancel'])
        ->name('requests.cancel');
});

// ==========================
// 🏢 DIVISION HEAD ROUTES
// ==========================
Route::middleware(['auth.session', 'role:division_head'])->prefix('division-head')->name('division_head.')->group(function () {
    // Division Head Purchase Request Recommendation
    Route::get('purchase-requests', [App\Http\Controllers\DivisionHead\PurchaseRequestController::class, 'index'])
        ->name('requests.index');

    Route::get('purchase-requests/{purchase_request}', [App\Http\Controllers\DivisionHead\PurchaseRequestController::class, 'show'])
        ->name('requests.show');

    Route::post('purchase-requests/{purchase_request}/recommend', [App\Http\Controllers\DivisionHead\PurchaseRequestController::class, 'recommend'])
        ->name('requests.recommend');

    Route::post('purchase-requests/{purchase_request}/cancel', [App\Http\Controllers\DivisionHead\PurchaseRequestController::class, 'cancel'])
        ->name('requests.cancel');
});

// ==========================
// 🔍 INSPECTOR (IAC) ROUTES
// ==========================
Route::middleware(['auth.session', 'role:iac,custodian'])->prefix('inspector')->name('inspector.')->group(function () {
    Route::get('inspections', [App\Http\Controllers\Inspector\InspectionController::class, 'index'])
        ->name('inspection.index');

    Route::get('purchase-orders/{purchase_order}/inspection', [App\Http\Controllers\Inspector\InspectionController::class, 'form'])
        ->name('inspection.form');

    // Inspector Accept/Reject Actions
    Route::post('inspection-items/{inspection_report_item}/accept', [App\Http\Controllers\Inspector\InspectionController::class, 'acceptItem'])
        ->name('inspection.accept');

    Route::post('inspection-items/{inspection_report_item}/reject', [App\Http\Controllers\Inspector\InspectionController::class, 'rejectItem'])
        ->name('inspection.reject');

    Route::post('purchase-orders/{purchase_order}/batch-inspect', [App\Http\Controllers\Inspector\InspectionController::class, 'batchInspect'])
        ->name('inspection.batch');
});

// ==========================
// 👤 DASHBOARD (Employee)
// ==========================
Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])
    ->middleware(['auth.session', 'role:employee'])
    ->name('employee.dashboard');

Route::middleware(['auth.session', 'role:employee,custodian'])->group(function () {
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('purchase-requests', [EmployeePurchaseRequestController::class, 'index'])
            ->name('purchase-requests.index');

        Route::post('purchase-requests', [EmployeePurchaseRequestController::class, 'store'])
            ->name('purchase-requests.store');

        Route::get('purchase-requests/{purchase_request}', [EmployeePurchaseRequestController::class, 'show'])
            ->name('purchase-requests.show');

        Route::post('purchase-requests/items/{purchase_request_item}/decision', [EmployeePurchaseRequestController::class, 'respondToItem'])
            ->name('purchase-requests.items.decision');
    });
});

// Fund allocation lookup used by frontend to fetch live remaining_amount
Route::middleware(['auth.session', 'role:employee,custodian'])->group(function () {
    Route::get('employee/fund-allocations/{id}', [App\Http\Controllers\FundAllocationController::class, 'show'])
        ->name('employee.fund-allocations.show');
});

