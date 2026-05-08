@php
    $role = strtolower(session('role') ?? auth()->user()->role ?? '');

    // Active state checks
    $isManagementActive = request()->routeIs(['custodian.fund_allocations.*', 'accounts.*', 'categories.*', 'employees.*']);
    $isInventoryActive = request()->routeIs(['pqs.*', 'custodian.inventory.*']);
    $isProcurementActive = request()->routeIs(['custodian.requests.*', 'custodian.orders.*', 'custodian.inspection.*']);
    $isInspectionActive = request()->routeIs(['custodian.inspection.*']);
    $isAssignmentActive = request()->routeIs(['custodian.inventory.*']);
    $isDivisionRequestsActive = request()->routeIs('division.requests.*');
    $isAuditActive = request()->routeIs(['custodian.audit_logs.*']);
    $isEmployeeRequestsActive = request()->routeIs('employee.purchase-requests.*');
@endphp

<div id="sidebar"
    class="fixed inset-y-0 left-0 bg-white border-r border-gray-100 w-64
    flex flex-col z-40
    transition-all duration-300 ease-in-out
    -translate-x-full md:translate-x-0" data-collapsed="false">

    <div class="flex items-center justify-center p-5 border-b border-gray-100 shrink-0">
        <img src="{{ asset('images/pqslogo.png') }}" alt="PQS Logo" class="h-10 w-auto">
        <span class="ml-3 text-2xl font-extrabold text-[#1a3a2d] [.w-20_&]:hidden">PQS</span>
    </div>

    <!-- MAIN NAV -->
    <nav id="sidebar-scroll" class="flex-1 p-4 overflow-y-auto hide-scrollbar space-y-6">
        
        <!-- SECTION: MAIN -->
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-wider text-gray-400 [.w-20_&]:hidden">
                Main
            </div>
            <ul class="space-y-1">
                @if($role === 'employee')
                    <li>
                        <a href="{{ route('employee.dashboard') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('employee.dashboard') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-chart-line text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Dashboard</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Dashboard</div>
                        </a>
                    </li>
                @elseif($role === 'division_head')
                    <li>
                        <a href="{{ route('division.requests.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ $isDivisionRequestsActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-clipboard-check text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Recommendation Queue</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Recommendation Queue</div>
                        </a>
                    </li>
                @elseif($role === 'iac')
                    <li>
                        <a href="{{ route('custodian.inspection.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ $isInspectionActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-clipboard-list text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Inspection Queue</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Inspection Queue</div>
                        </a>
                    </li>
                @elseif(in_array($role, ['custodian', 'admin'], true))
                    <li>
                        <a href="{{ route('dashboard') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-chart-line text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Dashboard</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Dashboard</div>
                        </a>
                    </li>
                @endif

                @if(in_array($role, ['custodian', 'employee', 'admin'], true))
                    <li>
                        <a href="{{ route('employee.purchase-requests.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ $isEmployeeRequestsActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-file-lines text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">My Requests</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">My Requests</div>
                        </a>
                    </li>
                @endif
            </ul>
        </div>

        <!-- SECTION: PROCUREMENT -->
        @if(in_array($role, ['custodian', 'bac', 'admin'], true))
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-wider text-gray-400 [.w-20_&]:hidden">
                Procurement
            </div>
            <ul class="space-y-1">
                @if(in_array($role, ['custodian', 'admin'], true))
                    <li>
                        <a href="{{ route('custodian.requests.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('custodian.requests.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-shopping-cart text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">All Requests</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">All Requests</div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('custodian.orders.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('custodian.orders.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-file-invoice-dollar text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Purchase Orders</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Purchase Orders</div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('custodian.inspection.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ $isInspectionActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-clipboard-list text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Inspection &amp; Acceptance</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Inspection &amp; Acceptance</div>
                        </a>
                    </li>
                @elseif($role === 'bac')
                    <li>
                        <a href="{{ route('bac.requests.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('bac.requests.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-shopping-cart text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">All Requests</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">All Requests</div>
                        </a>
                    </li>
                @endif
                @if($role === 'admin')
                    <li>
                        <a href="{{ route('division_head.requests.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('division_head.requests.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-clipboard-check text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Division Recommendation</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Division Recommendation</div>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('bac.requests.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('bac.requests.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-gavel text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">BAC Approval Queue</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">BAC Approval Queue</div>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        @endif

        <!-- SECTION: INVENTORY -->
        @if(in_array($role, ['custodian', 'admin'], true))
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-wider text-gray-400 [.w-20_&]:hidden">
                Inventory
            </div>
            <ul class="space-y-1">
                @if (Route::has('custodian.inventory.index'))
                    <li>
                        <a href="{{ route('custodian.inventory.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('custodian.inventory.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-boxes-stacked text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Assignment</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Assignment</div>
                        </a>
                    </li>
                @endif
                @if (Route::has('pqs.index'))
                    <li>
                        <a href="{{ route('pqs.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('pqs.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-file-signature text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">PQS Records</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">PQS Records</div>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        @endif

        <!-- SECTION: ADMINISTRATION -->
        @if(in_array($role, ['custodian', 'admin'], true))
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-wider text-gray-400 [.w-20_&]:hidden">
                Administration
            </div>
            <ul class="space-y-1">
                @if (Route::has('custodian.fund_allocations.index'))
                    <li>
                        <a href="{{ route('custodian.fund_allocations.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('custodian.fund_allocations.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-wallet text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Fund Allocations</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Fund Allocations</div>
                        </a>
                    </li>
                @endif
                @if (Route::has('accounts.index'))
                    <li>
                        <a href="{{ route('accounts.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('accounts.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-users-cog text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Accounts</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Accounts</div>
                        </a>
                    </li>
                @endif
                @if (Route::has('categories.index'))
                    <li>
                        <a href="{{ route('categories.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('categories.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-tags text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Categories</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Categories</div>
                        </a>
                    </li>
                @endif
                @if (Route::has('employees.index'))
                    <li>
                        <a href="{{ route('employees.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('employees.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                            <i class="fas fa-id-card text-lg w-6 text-center"></i>
                            <span class="ml-3 [.w-20_&]:hidden">Employees</span>
                            <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Employees</div>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        @endif

        <!-- SECTION: LOGS & REPORTS -->
        @if(in_array($role, ['custodian', 'admin'], true))
        <div>
            <div class="mb-2 px-3 text-[11px] font-extrabold uppercase tracking-wider text-gray-400 [.w-20_&]:hidden">
                Logs &amp; Reports
            </div>
            <ul class="space-y-1">
                <li>
                    <a href="{{ route('custodian.audit_logs.index') }}" class="group relative flex items-center px-3 py-2.5 rounded-lg font-medium transition-colors duration-200 {{ $isAuditActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-history text-lg w-6 text-center"></i>
                        <span class="ml-3 [.w-20_&]:hidden">Audit Logs</span>
                        <div class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none pointer-events-none">Audit Logs</div>
                    </a>
                </li>
            </ul>
        </div>
        @endif

    </nav>
</div>

