<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Account;
use App\Models\PurchaseRequest;
use App\Models\InspectionReport;
use App\Models\PqsRecord;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\InspectionReportPolicy;
use App\Policies\PqsRecordPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model policies
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(InspectionReport::class, InspectionReportPolicy::class);
        Gate::policy(PqsRecord::class, PqsRecordPolicy::class);

        // Define gates for role-based access
        
        /**
         * Gate: BAC Approval
         * Only BAC role can access final approval actions.
         * This is the strictest gate - prevents any other role from approving.
         */
        Gate::define('bac-approve', function (Account $user) {
            return $user->role === 'bac';
        });

        /**
         * Gate: Division Head Recommendation
         * Only Division Heads can provide recommendations on PRs.
         */
        Gate::define('division-head-recommend', function (Account $user) {
            return $user->role === 'division_head';
        });

        /**
         * Gate: Inspector Actions
         * Only IAC (Inspection and Acceptance Committee) can inspect items.
         */
        Gate::define('inspect-items', function (Account $user) {
            return in_array($user->role, ['iac', 'custodian'], true);
        });

        /**
         * Gate: Custodian PQS Creation
         * Only Custodians can create PQS records from accepted items.
         */
        Gate::define('create-pqs', function (Account $user) {
            return $user->role === 'custodian';
        });

        /**
         * Gate: View Procurement Dashboard
         * BAC and Custodians can access the main procurement dashboard.
         */
        Gate::define('view-procurement-dashboard', function (Account $user) {
            return in_array($user->role, ['bac', 'custodian'], true);
        });

        /**
         * Gate: Manage Purchase Orders
         * BAC and Custodians can manage purchase orders.
         */
        Gate::define('manage-purchase-orders', function (Account $user) {
            return in_array($user->role, ['bac', 'custodian'], true);
        });
    }
}
