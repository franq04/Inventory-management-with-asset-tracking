<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class UserManualController extends Controller
{
    public function index()
    {
        $manual = $this->buildManualData();

        return view('custodian.user_manual.index', $manual);
    }

    public function export()
    {
        $manual = $this->buildManualData();
        $html = view('custodian.user_manual.export', $manual)->render();

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="custodian-user-manual-' . now()->format('Y-m-d') . '.html"');
    }

    private function buildManualData(): array
    {
        $directory = public_path('user-manual-images/custodian');
        $files = [];

        if (File::exists($directory)) {
            $files = collect(File::files($directory))
                ->filter(function ($file) {
                    return in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'], true);
                })
                ->sortBy(function ($file) {
                    return strtolower($file->getFilename());
                }, SORT_NATURAL)
                ->values();
        }

        $sections = $this->manualSections();
        $steps = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $basename));
            $slug = trim($slug, '-');
            $title = $this->toTitle($basename);
            $sectionKey = $this->resolveSection($basename);

            $steps[] = [
                'id' => sprintf('step-%02d-%s', count($steps) + 1, $slug ?: (count($steps) + 1)),
                'filename' => $filename,
                'title' => $title,
                'description' => $this->descriptionFor($basename),
                'elements' => $this->elementCardsFor($basename),
                'section_key' => $sectionKey,
                'section_order' => $sections[$sectionKey]['order'] ?? 999,
                'step_order' => $this->resolveStepOrder($basename),
                'image_url' => asset('user-manual-images/custodian/' . rawurlencode($filename)),
            ];
        }

        usort($steps, function (array $left, array $right) {
            return [$left['section_order'], $left['step_order'], $left['title']] <=> [$right['section_order'], $right['step_order'], $right['title']];
        });

        $grouped = [];
        foreach ($sections as $sectionKey => $section) {
            $items = array_values(array_filter($steps, function (array $step) use ($sectionKey) {
                return $step['section_key'] === $sectionKey;
            }));

            if (!empty($items)) {
                foreach ($items as $index => $item) {
                    $items[$index]['number'] = $index + 1;
                }

                $grouped[] = [
                    'category' => $section['title'],
                    'intro' => $section['intro'],
                    'items' => $items,
                ];
            }
        }

        return [
            'manualTitle' => 'Custodian User Manual',
            'manualIntro' => 'This manual follows the sidebar flow from top to bottom. Each page is grouped with the screenshots that belong to it, with short instructions focused on what you should do on that screen.',
            'sections' => $grouped,
            'steps' => $steps,
        ];
    }

    private function manualSections(): array
    {
        return [
            'dashboard' => [
                'title' => 'Dashboard',
                'intro' => 'Start by opening the application URL, log in with your account, and confirm you are on the dashboard before moving to the next sidebar pages.',
                'order' => 1,
            ],
            'my-requests' => [
                'title' => 'My Requests',
                'intro' => 'Use this page to review the requests you created, start a new request, and complete the request form carefully.',
                'order' => 2,
            ],
            'all-requests' => [
                'title' => 'All Requests',
                'intro' => 'Use this page to view all requests submitted in the system, check their latest status, and open details for verification.',
                'order' => 3,
            ],
            'purchase-orders' => [
                'title' => 'Purchase Orders',
                'intro' => 'Use this page to find purchase orders, create a PO from an approved request, mark delivered orders as received, and follow the order pipeline.',
                'order' => 4,
            ],
            'pqs-records' => [
                'title' => 'PQS Records',
                'intro' => 'Use this page to view generated PQS records, search and filter the list, and print or export the current results.',
                'order' => 5,
            ],
            'assignment' => [
                'title' => 'Assignment',
                'intro' => 'Use this page to review asset assignments, find a specific assignment, and update turnover or movement-related actions.',
                'order' => 6,
            ],
            'fund-allocations' => [
                'title' => 'Fund Allocations',
                'intro' => 'Use this page to review budget allocations, create a new allocation, and save the correct account and division details.',
                'order' => 7,
            ],
            'accounts' => [
                'title' => 'Accounts',
                'intro' => 'Use this page to review accounts, search for the correct one, and update or create account records when needed.',
                'order' => 8,
            ],
            'categories' => [
                'title' => 'Categories',
                'intro' => 'Use this page to manage asset categories, search through the list, and add or edit category records.',
                'order' => 9,
            ],
            'employees' => [
                'title' => 'Employees',
                'intro' => 'Use this page to manage employee records, search for a person, view their details, and add a new employee when needed.',
                'order' => 10,
            ],
            'audit-logs' => [
                'title' => 'Audit Logs',
                'intro' => 'Use this page to check system activity, search for a specific action, and review what was changed and when.',
                'order' => 11,
            ],
            'other' => [
                'title' => 'Logout',
                'intro' => 'Use this section for profile menu actions such as opening your profile and logging out safely.',
                'order' => 99,
            ],
        ];
    }

    private function resolveSection(string $name): string
    {
        $key = strtolower(trim($name));

        $map = [
            // Dashboard
            'open-application' => 'dashboard',
            'login-credentials' => 'dashboard',
            'dashboard' => 'dashboard',

            // My Requests
            'my-request-page' => 'my-requests',
            'click-new-request-button' => 'my-requests',
            'fill-up-form' => 'my-requests',
            'view-request-details' => 'my-requests',

            // All Requests
            'all-request-page' => 'all-requests',
            'all-request status' => 'all-requests',
            'all-request-review-button' => 'all-requests',
            'filters-and-export' => 'all-requests',

            // Purchase Orders
            'purchase-order-page' => 'purchase-orders',
            'create-po-for-approved-pr' => 'purchase-orders',
            'generate-po' => 'purchase-orders',
            'order-pipeline-tab' => 'purchase-orders',
            'view-po-details' => 'purchase-orders',
            'mark-as-received-po' => 'purchase-orders',
            'po-status' => 'purchase-orders',

            // Assignment
            'assignment-page' => 'assignment',
            'assignment-filters-and-export' => 'assignment',
            'assignment-status' => 'assignment',
            'create-pqs-record' => 'assignment',
            'create-pqs-form' => 'assignment',
            
            // Assignment actions moved to related modules
            'action-buttons' => 'fund-allocations',
            'movement-report-button' => 'pqs-records',
            'bulk-turnover-button' => 'pqs-records',
            'bulk-turnover' => 'pqs-records',

            // PQS Records
            'pqs-records-page' => 'pqs-records',
            'pqs-records-search-and-filters' => 'pqs-records',
            'pqs-records-view-details' => 'pqs-records',
            'add-location-button' => 'pqs-records',
            'location-form' => 'pqs-records',

            // Fund Allocations
            'fund-allocation-page' => 'fund-allocations',
            'new-fund-allocation-button-form' => 'fund-allocations',
            'fund-allocation-form' => 'fund-allocations',

            // Accounts
            'accounts-page' => 'accounts',
            'accounts-search-filters' => 'accounts',
            'account-edit' => 'accounts',

            // Categories
            'categories-page' => 'categories',
            'categories-search-filters' => 'categories',
            'categories-action-buttons' => 'categories',
            'addcategory-button' => 'categories',
            'category-form' => 'categories',
            'subcategories' => 'categories',

            // Employees
            'employees-page' => 'employees',
            'employees-search-and-filters' => 'employees',
            'employees-action-buttons' => 'employees',
            'employees-view-details' => 'employees',
            'see-details' => 'employees',
            'employee-add-division' => 'employees',
            'employee-click-save-division' => 'employees',
            'employee-add-positions' => 'employees',
            'add-employees-button' => 'employees',
            'add-employee-first-step' => 'employees',
            'create-new-account' => 'employees',
            'save-employee' => 'employees',

            // Audit Logs
            'audit-logs-page' => 'audit-logs',
            'audit-logs-search-filters' => 'audit-logs',

            // Logout controls
            'profile-click' => 'other',
            'logout-button' => 'other',
        ];

        return $map[$key] ?? 'other';
    }

    private function resolveStepOrder(string $name): int
    {
        $key = strtolower(trim($name));

        $orders = [
            // Dashboard
            'open-application' => 1,
            'login-credentials' => 2,
            'dashboard' => 3,

            // My Requests
            'my-request-page' => 1,
            'click-new-request-button' => 2,
            'fill-up-form' => 3,
            'view-request-details' => 4,

            // All Requests
            'all-request-page' => 1,
            'all-request status' => 2,
            'all-request-review-button' => 3,
            'filters-and-export' => 4,

            // Purchase Orders
            'purchase-order-page' => 1,
            'create-po-for-approved-pr' => 2,
            'generate-po' => 3,
            'order-pipeline-tab' => 4,
            'view-po-details' => 5,
            'mark-as-received-po' => 6,
            'po-status' => 7,

            // Assignment
            'assignment-page' => 1,
            'assignment-filters-and-export' => 2,
            'assignment-status' => 3,
            'create-pqs-record' => 4,
            'create-pqs-form' => 5,

            // PQS Records
            'pqs-records-page' => 1,
            'pqs-records-search-and-filters' => 2,
            'pqs-records-view-details' => 3,
            'add-location-button' => 4,
            'location-form' => 5,
            'movement-report-button' => 6,
            'bulk-turnover-button' => 7,
            'bulk-turnover' => 8,

            // Fund Allocations
            'fund-allocation-page' => 1,
            'new-fund-allocation-button-form' => 2,
            'fund-allocation-form' => 3,
            'action-buttons' => 4,

            // Accounts
            'accounts-page' => 1,
            'accounts-search-filters' => 2,
            'account-edit' => 3,

            // Categories
            'categories-page' => 1,
            'categories-search-filters' => 2,
            'categories-action-buttons' => 3,
            'addcategory-button' => 4,
            'category-form' => 5,
            'subcategories' => 6,

            // Employees
            'employees-page' => 1,
            'employees-search-and-filters' => 2,
            'employees-action-buttons' => 3,
            'employees-view-details' => 4,
            'employee-add-division' => 5,
            'employee-click-save-division' => 6,
            'see-details' => 7,
            'employee-add-positions' => 8,
            'add-employees-button' => 9,
            'add-employee-first-step' => 10,
            'create-new-account' => 11,
            'save-employee' => 12,

            // Audit Logs
            'audit-logs-page' => 1,
            'audit-logs-search-filters' => 2,

            // Logout controls
            'profile-click' => 1,
            'logout-button' => 2,
        ];

        return $orders[$key] ?? 99;
    }

    private function toTitle(string $value): string
    {
        $normalized = str_replace(['_', '-'], ' ', strtolower($value));
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        if ($normalized === 'profile click') {
            return 'Open Profile Menu';
        }

        if ($normalized === 'logout button') {
            return 'Click Logout';
        }

        if ($normalized === 'see details') {
            return 'Click See Details';
        }

        if ($normalized === 'employee add division') {
            return 'Click Add Division';
        }

        if ($normalized === 'employee click save division') {
            return 'Save New Division';
        }

        if ($normalized === 'employee add positions') {
            return 'Click Add Position';
        }

        return ucwords($normalized);
    }

    private function descriptionFor(string $name): string
    {
        $value = strtolower($name);

        if (str_contains($value, 'login-credentials')) {
            return 'Type your username and password carefully, then click Login to enter the system.';
        }

        if (str_contains($value, 'open-application')) {
            return 'Open the application URL in your browser and wait for the login page to load fully before entering credentials.';
        }

        if (str_contains($value, 'dashboard')) {
            return 'After logging in, confirm you are on the dashboard and use the left sidebar to open the next module you need.';
        }

        if (str_contains($value, 'filters-and-export')) {
            return 'Use the filter and export controls on this page to narrow the visible records first, then print or export only the filtered result.';
        }

        if (str_contains($value, 'my-request-page')) {
            return 'Review your submitted requests, search for older entries, and open any request that needs follow-up.';
        }

        if (str_contains($value, 'click-new-request')) {
            return 'Click New Request to start a new request and begin entering the items you need.';
        }

        if (str_contains($value, 'fill-up-form')) {
            return 'Fill in the request form with the item details, quantity, and justification, then save or submit once all required fields are complete.';
        }

        if (str_contains($value, 'all-request-page')) {
            return 'Use this page to review all requests, filter the list, and open a request to view its details and current status.';
        }

        if (str_contains($value, 'all-request-status')) {
            return 'Check the status list to see what each request stage means and decide what to do next.';
        }

        if (str_contains($value, 'all-request-review')) {
            return 'Use the review button to open the request details modal and verify the request information and status.';
        }

        if (str_contains($value, 'view-request-details')) {
            return 'Check the full request details, compare the items and totals, and confirm the request is complete before moving it forward.';
        }

        if (str_contains($value, 'purchase-order-page')) {
            return 'Search for the purchase order you need, then open it to check the supplier, items, amounts, and current status.';
        }

        if (str_contains($value, 'create-po')) {
            return 'Select the approved request, verify the supplier and item details, then create the purchase order.';
        }

        if (str_contains($value, 'view-po-details')) {
            return 'Review the full purchase order, confirm supplier information and item totals, and check the status history before continuing.';
        }

        if (str_contains($value, 'mark-as-received')) {
            return 'Click this button to mark the purchase order as received once the items are delivered.';
        }

        if (str_contains($value, 'order-pipeline')) {
            return 'Move through the pipeline to track each purchase order from approval to delivery and closure.';
        }

        if (str_contains($value, 'pqs-records-search-and-filters')) {
            return 'Use the search and filter controls to narrow the PQS list. Refer to the element guide cards below this screenshot for each control and what it does.';
        }

        if (str_contains($value, 'pqs-records-page')) {
            return 'Start on the PQS Records page by reviewing the summary cards and total records found, then use filters before opening specific rows.';
        }

        if (str_contains($value, 'pqs-records-view-details')) {
            return 'Open the selected PQS record to check property details, assignment document reference, location, and condition history.';
        }

        if (str_contains($value, 'pqs-record')) {
            return 'Review the PQS records list, open a record when needed, and verify assignment and condition details.';
        }

        if (str_contains($value, 'assignment-page')) {
            return 'Use this page to see which employee or location currently holds each asset and open the assignment you want to review.';
        }

        if (str_contains($value, 'assignment-filters')) {
            return 'Filter the assignments to find the asset or employee you need, then export the results if you need a report.';
        }

        if (str_contains($value, 'assignment-status')) {
            return 'Check the status labels to see whether an asset is active, returned, or transferred before taking the next action.';
        }

        if (str_contains($value, 'add-location-button')) {
            return 'Use the Add Location button to register a new physical location that can be used in PQS and assignment records.';
        }

        if (str_contains($value, 'location-form')) {
            return 'Fill out the location form fields and save to add the new location to the available location list.';
        }

        if (str_contains($value, 'movement-report')) {
            return 'Click the report button to generate the asset movement record for transfers or location changes.';
        }

        if (str_contains($value, 'bulk-turnover-button')) {
            return 'Use the bulk turnover button when you need to process several returns or handovers at once.';
        }

        if (str_contains($value, 'bulk-turnover')) {
            return 'Review the selected assets carefully, confirm the turnover details, then complete the bulk turnover process.';
        }

        if (str_contains($value, 'fund-allocation-page')) {
            return 'Review the allocation list, check balances, and open a record if you need to verify or update funding.';
        }

        if (str_contains($value, 'new-fund-allocation')) {
            return 'Click the new allocation button to start adding a budget allocation for a department or account.';
        }

        if (str_contains($value, 'fund-allocation-form')) {
            return 'Fill in the allocation details, choose the account and division, enter the amount, and submit the form.';
        }

        if (str_contains($value, 'accounts-page')) {
            return 'Review the account list and open the account you want to check or update.';
        }

        if (str_contains($value, 'accounts-search-filters')) {
            return 'Use the filters to find the correct account by name, code, or status before editing it.';
        }

        if (str_contains($value, 'account-edit')) {
            return 'Update the account information carefully, then save the changes after confirming the values are correct.';
        }

        if (str_contains($value, 'create-new-account')) {
            return 'In the employee page flow, complete the account-related details before proceeding to the final save step.';
        }

        if (str_contains($value, 'categories-page')) {
            return 'Review the category list and open the category you want to manage.';
        }

        if (str_contains($value, 'categories-search-filters')) {
            return 'Use the search and filter tools to find the correct category quickly.';
        }

        if (str_contains($value, 'categories-action-buttons')) {
            return 'Use the action buttons to edit, view, or manage the selected category.';
        }

        if (str_contains($value, 'addcategory-button')) {
            return 'Click the add category button to create a new asset category.';
        }

        if (str_contains($value, 'category-form')) {
            return 'Fill in the category details clearly, then save the new category.';
        }

        if (str_contains($value, 'subcategories')) {
            return 'Use the subcategory screen to organize related asset types under the correct main category.';
        }

        if (str_contains($value, 'employees-page')) {
            return 'Review the employee list, then open the person you want to manage or assign assets to.';
        }

        if (str_contains($value, 'employees-search-and-filters')) {
            return 'Search or filter the employee list to locate the right person faster.';
        }

        if (str_contains($value, 'employees-action-buttons')) {
            return 'Use the employee action buttons to view details, edit the record, or manage asset links.';
        }

        if (str_contains($value, 'employees-view-details')) {
            return 'Open the employee record to confirm their position, department, and assigned assets.';
        }

        if (str_contains($value, 'see-details')) {
            return 'In the Divisions Overview panel, click See Details to open the selected division and view its sections and staffing summary.';
        }

        if (str_contains($value, 'employee-add-division')) {
            return 'Click Add Division in the Divisions Overview panel to open the division form and start adding a new division record.';
        }

        if (str_contains($value, 'employee-click-save-division')) {
            return 'Fill in Division Name and optional fields, add initial sections if needed, then click Save Division to create the entry.';
        }

        if (str_contains($value, 'employee-add-positions')) {
            return 'Inside the division details dialog, click Add Position beside the target section to register a position under that section.';
        }

        if (str_contains($value, 'add-employees-button')) {
            return 'Click the add employees button to begin creating a new employee record.';
        }

        if (str_contains($value, 'add-employee-first-step')) {
            return 'Start the employee creation form and enter the first required details carefully.';
        }

        if (str_contains($value, 'save-employee')) {
            return 'Review the employee information and click Save to register the new employee.';
        }

        if (str_contains($value, 'audit-logs-page')) {
            return 'Review the system activity log to see what changes were made and by whom.';
        }

        if (str_contains($value, 'audit-logs-search-filters')) {
            return 'Filter the logs by date, user, or action to find the record you need.';
        }

        if (str_contains($value, 'profile-click')) {
            return 'From the dashboard header, click your profile area in the top-right corner to open the account menu options.';
        }

        if (str_contains($value, 'logout-button')) {
            return 'After opening the profile menu, click Logout to securely end your current session and return to the login page.';
        }

        if (str_contains($value, 'action-buttons')) {
            return 'Use the action buttons on the list to open details, edit a record, or run the available action.';
        }

        if (str_contains($value, 'create-pqs-record')) {
            return 'From the Assignment list, click Create PQS in the Actions column for the selected item to open the PQS form.';
        }

        if (str_contains($value, 'create-pqs-form')) {
            return 'Complete the PQS form fields, review the record details, then click Save Record to generate and store the PQS entry.';
        }

        return 'Review the screen carefully and complete the available action before moving to the next step.';
    }

    private function elementCardsFor(string $name): array
    {
        $value = strtolower($name);

        if ($value === 'filters-and-export') {
            return [
                [
                    'title' => 'Search Bar',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search request no, requester, item...',
                    'description' => 'Type keywords to quickly locate a request by number, requester name, or item details.',
                ],
                [
                    'title' => 'Status Filter',
                    'icon' => 'fas fa-filter',
                    'chip' => 'Any status',
                    'description' => 'Filter the request list by status so you can focus on only the records you need to review.',
                ],
                [
                    'title' => 'Date Range',
                    'icon' => 'fas fa-calendar-days',
                    'chip' => 'From / To',
                    'description' => 'Set a start and end date to narrow records to a specific request period.',
                ],
                [
                    'title' => 'Print',
                    'icon' => 'fas fa-file-pdf',
                    'chip' => 'Print',
                    'description' => 'Create a printable report based on the active filters currently applied to the list.',
                ],
                [
                    'title' => 'Excel Export',
                    'icon' => 'fas fa-file-excel',
                    'chip' => 'Excel',
                    'description' => 'Export the currently filtered request list to Excel for reporting and submission.',
                ],
                [
                    'title' => 'Reset',
                    'icon' => 'fas fa-rotate-left',
                    'chip' => 'Reset',
                    'description' => 'Clear all active filters and reload the default all-request listing view.',
                ],
            ];
        }

        if ($value === 'assignment-filters-and-export') {
            return [
                [
                    'title' => 'Status Tabs',
                    'icon' => 'fas fa-layer-group',
                    'chip' => 'All Items | Ready for PQS | Recorded in PQS',
                    'description' => 'Switch between the three status tabs to quickly filter the assignment queue by processing stage.',
                ],
                [
                    'title' => 'Search Field',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search property no, item, IA/PO/PR, category...',
                    'description' => 'Type keywords to find assignment rows by property number, item details, references, category, officer, or supplier.',
                ],
                [
                    'title' => 'Date From',
                    'icon' => 'fas fa-calendar-day',
                    'chip' => 'YYYY-MM-DD',
                    'description' => 'Set the start date to include assignment rows from a specific period.',
                ],
                [
                    'title' => 'Date To',
                    'icon' => 'fas fa-calendar-check',
                    'chip' => 'YYYY-MM-DD',
                    'description' => 'Set the end date so results are limited to your selected date range.',
                ],
                [
                    'title' => 'Apply Date Filter',
                    'icon' => 'fas fa-filter',
                    'chip' => 'Filter button',
                    'description' => 'Click the filter button to apply the selected from/to dates to the queue list.',
                ],
            ];
        }

        if ($value === 'accounts-search-filters') {
            return [
                [
                    'title' => 'Search Account',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search username, employee, account ID...',
                    'description' => 'Use the main search box to filter the account list by username, linked employee, or account ID.',
                ],
                [
                    'title' => 'Role Dropdown',
                    'icon' => 'fas fa-user-shield',
                    'chip' => 'All Roles / specific role',
                    'description' => 'Select a role from the dropdown to show only accounts assigned to that role.',
                ],
                [
                    'title' => 'Role Search',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search prefix in role menu',
                    'description' => 'Inside the role dropdown, use the quick search input to narrow role options before selecting.',
                ],
                [
                    'title' => 'Reset',
                    'icon' => 'fas fa-rotate-left',
                    'chip' => 'Reset Filters',
                    'description' => 'Click reset to clear search and role selection, then return to the full account list.',
                ],
            ];
        }

        if ($value === 'categories-search-filters') {
            return [
                [
                    'title' => 'Search Category',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search category name, code, description...',
                    'description' => 'Use the search input to filter parent categories by category name, code, or description text.',
                ],
                [
                    'title' => 'Reset',
                    'icon' => 'fas fa-rotate-left',
                    'chip' => 'Reset Filters',
                    'description' => 'Use reset to clear the search term and reload the default categories view.',
                ],
            ];
        }

        if ($value === 'employees-search-and-filters') {
            return [
                [
                    'title' => 'Search Employee',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search name or employee no',
                    'description' => 'Use keyword search to locate employees by name, ID, or related assignment details.',
                ],
                [
                    'title' => 'Division',
                    'icon' => 'fas fa-building',
                    'chip' => 'All divisions',
                    'description' => 'Filter employees by division to review records from one office at a time.',
                ],
                [
                    'title' => 'Section',
                    'icon' => 'fas fa-sitemap',
                    'chip' => 'All sections',
                    'description' => 'Narrow the employee list to a selected section for faster record checks.',
                ],
                [
                    'title' => 'Position',
                    'icon' => 'fas fa-user-tie',
                    'chip' => 'All positions',
                    'description' => 'Use position filtering when validating role-based employee assignments.',
                ],
                [
                    'title' => 'Reset',
                    'icon' => 'fas fa-rotate-left',
                    'chip' => 'Reset',
                    'description' => 'Clear employee filters and show the full list again.',
                ],
            ];
        }

        if ($value === 'audit-logs-search-filters') {
            return [
                [
                    'title' => 'Search',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search user, action, or description...',
                    'description' => 'Use the search box to find audit entries by username, action text, or description details.',
                ],
                [
                    'title' => 'Action Type',
                    'icon' => 'fas fa-list-check',
                    'chip' => 'All Actions / selected action',
                    'description' => 'Use the Action Type dropdown to show only logs for one action category.',
                ],
                [
                    'title' => 'Date From',
                    'icon' => 'fas fa-calendar-day',
                    'chip' => 'YYYY-MM-DD',
                    'description' => 'Set the start date to include only audit records on or after that date.',
                ],
                [
                    'title' => 'Date To',
                    'icon' => 'fas fa-calendar-check',
                    'chip' => 'YYYY-MM-DD',
                    'description' => 'Set the end date to include only audit records on or before that date.',
                ],
                [
                    'title' => 'Apply',
                    'icon' => 'fas fa-filter',
                    'chip' => 'Apply date filter',
                    'description' => 'Click Apply after choosing dates to filter the audit log by the selected range.',
                ],
                [
                    'title' => 'Reset',
                    'icon' => 'fas fa-rotate-left',
                    'chip' => 'Reset',
                    'description' => 'Remove all active log filters and load the default audit log view.',
                ],
                [
                    'title' => 'Print PDF',
                    'icon' => 'fas fa-file-pdf',
                    'chip' => 'Print PDF',
                    'description' => 'Generate a printable PDF copy of the currently filtered audit logs.',
                ],
                [
                    'title' => 'Export Excel',
                    'icon' => 'fas fa-file-excel',
                    'chip' => 'Export Excel',
                    'description' => 'Export the current audit log result set to Excel for sharing or reporting.',
                ],
            ];
        }

        if (str_contains($value, 'pqs-records-search-and-filters')) {
            return [
                [
                    'title' => 'Search Bar',
                    'icon' => 'fas fa-magnifying-glass',
                    'chip' => 'Search property no, description, employee...',
                    'description' => 'Type keywords to find records by property number, item description, employee name, category, or location.',
                ],
                [
                    'title' => 'Category',
                    'icon' => 'fas fa-layer-group',
                    'chip' => 'All Categories',
                    'description' => 'Use the category dropdown to show only records that belong to a specific asset category.',
                ],
                [
                    'title' => 'Assignment',
                    'icon' => 'fas fa-id-card',
                    'chip' => 'Any | With ICS | With PAR | Unassigned',
                    'description' => 'Filter by assignment document status. Choose With ICS, With PAR, or Unassigned depending on what you need to review.',
                ],
                [
                    'title' => 'Condition',
                    'icon' => 'fas fa-circle-check',
                    'chip' => 'Any | Serviceable | Unserviceable',
                    'description' => 'Show only assets with a selected condition status for validation and reporting.',
                ],
                [
                    'title' => 'Date From',
                    'icon' => 'fas fa-calendar-day',
                    'chip' => 'YYYY-MM-DD',
                    'description' => 'Set the starting date to include only records on or after this acquisition date.',
                ],
                [
                    'title' => 'Date To',
                    'icon' => 'fas fa-calendar-check',
                    'chip' => 'YYYY-MM-DD',
                    'description' => 'Set the ending date to include only records on or before this acquisition date.',
                ],
                [
                    'title' => 'Print',
                    'icon' => 'fas fa-file-pdf',
                    'chip' => 'Print',
                    'description' => 'Generates a printable PDF based on the current filters so you can print or save the report.',
                ],
                [
                    'title' => 'Excel',
                    'icon' => 'fas fa-file-excel',
                    'chip' => 'Excel',
                    'description' => 'Exports the currently filtered records to an Excel file for spreadsheet review and submission.',
                ],
                [
                    'title' => 'Reset',
                    'icon' => 'fas fa-rotate-left',
                    'chip' => 'Reset',
                    'description' => 'Clears all active filters and reloads the default PQS record list view.',
                ],
            ];
        }

        return [];
    }
}
