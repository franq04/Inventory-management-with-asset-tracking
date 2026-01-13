@php
    $role = strtolower(session('role') ?? auth()->user()->role ?? '');

    $isManagementActive = request()->routeIs(['custodian.fund_allocations.*', 'accounts.*', 'categories.*', 'employees.*']);
    $isInventoryActive = request()->routeIs(['pqs.*', 'ics.index', 'par.index', 'custodian.inventory.*']);
    $isProcurementActive = request()->routeIs([
        'custodian.requests.*',
        'custodian.orders.*',
        'custodian.inspection.*'
    ]);
    $isInspectionActive = request()->routeIs(['custodian.inspection.*']);
    $isAssignmentActive = request()->routeIs(['custodian.inventory.*']);
    $isDivisionRequestsActive = request()->routeIs('division.requests.*');
    $isAuditActive = request()->routeIs(['custodian.audit_logs.*']);
    $isEmployeeRequestsActive = request()->routeIs('employee.purchase-requests.*');
@endphp

<div id="sidebar"
    class="fixed inset-y-0 left-0 bg-white shadow-xl w-64
    flex flex-col z-40
    transition-all duration-300 ease-in-out
    -translate-x-full md:translate-x-0">

    <div class="flex items-center justify-center p-5 border-b shrink-0">
        <img src="{{ asset('images/pqslogo.png') }}" alt="PQS Logo" class="h-10 w-auto">
        <span class="ml-3 text-2xl font-extrabold text-[var(--secondary-color)] [.w-20_&]:hidden">PQS</span>
    </div>

    <nav class="flex-1 p-4 overflow-y-auto hide-scrollbar">
        <ul class="space-y-2">
            <!-- Dashboard -->
            <li>
                @if($role === 'employee')
                    <a href="{{ route('employee.dashboard') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('employee.dashboard') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-user-tie text-lg w-6 text-center"></i>
                        <span class="ml-4 [.w-20_&]:hidden">My Dashboard</span>
                        <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">My Dashboard</span>
                    </a>
                @elseif($role === 'division_head')
                    <a href="{{ route('division.requests.index') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isDivisionRequestsActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-clipboard-check text-lg w-6 text-center"></i>
                        <span class="ml-4 [.w-20_&]:hidden">Recommendation Queue</span>
                        <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">Recommendation Queue</span>
                    </a>
                @elseif($role === 'iac')
                    <a href="{{ route('custodian.inspection.index') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isInspectionActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-clipboard-list text-lg w-6 text-center"></i>
                        <span class="ml-4 [.w-20_&]:hidden">Inspection Queue</span>
                        <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">Inspection Queue</span>
                    </a>
                @elseif($role === 'custodian')
                    <a href="{{ route('dashboard') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <i class="fas fa-chart-line text-lg w-6 text-center"></i>
                        <span class="ml-4 [.w-20_&]:hidden">Dashboard</span>
                        <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">Dashboard</span>
                    </a>
                @endif
            </li>

            <!-- My Purchase Requests (for custodian and employee) -->
            @if(in_array($role, ['custodian', 'employee']))
            <li>
                <a href="{{ route('employee.purchase-requests.index') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isEmployeeRequestsActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i class="fas fa-file-lines text-lg w-6 text-center"></i>
                    <span class="ml-4 [.w-20_&]:hidden">My Purchase Requests</span>
                    <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">My Requests</span>
                </a>
            </li>
            @endif

            <!-- Management (custodian only) -->
            @if($role === 'custodian')
            <li>
                <button class="dropdown-toggle group w-full flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isManagementActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i class="fas fa-cogs text-lg w-6 text-center"></i>
                    <span class="ml-4 text-left flex-1 [.w-20_&]:hidden">Management</span>
                    <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-300 {{ $isManagementActive ? 'rotate-180' : '' }} [.w-20_&]:hidden"></i>
                </button>
                <ul class="pl-7 space-y-1 mt-1 {{ $isManagementActive ? '' : 'hidden' }} [.w-20_&]:hidden">
                    @if (Route::has('custodian.fund_allocations.index'))
                        <li><a href="{{ route('custodian.fund_allocations.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('custodian.fund_allocations.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">Fund Allocations</a></li>
                    @endif
                    @if (Route::has('accounts.index'))
                        <li><a href="{{ route('accounts.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('accounts.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">Manage Accounts</a></li>
                    @endif
                    @if (Route::has('categories.index'))
                        <li><a href="{{ route('categories.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('categories.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">Manage Categories</a></li>
                    @endif
                    @if (Route::has('employees.index'))
                        <li><a href="{{ route('employees.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('employees.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">Manage Employees</a></li>
                    @endif
                </ul>
            </li>
            @endif

            <!-- Procurement -->
            @if($role === 'custodian')
            <li>
                <button class="dropdown-toggle group w-full flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isProcurementActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i class="fas fa-shopping-cart text-lg w-6 text-center"></i>
                    <span class="ml-4 text-left flex-1 [.w-20_&]:hidden">Procurement</span>
                    <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-300 {{ $isProcurementActive ? 'rotate-180' : '' }} [.w-20_&]:hidden"></i>
                </button>
                <ul class="pl-7 space-y-1 mt-1 {{ $isProcurementActive ? '' : 'hidden' }} [.w-20_&]:hidden">
                    <li>
                        <a href="{{ route('custodian.requests.index') }}" 
                           class="block p-2 rounded-md text-sm {{ request()->routeIs('custodian.requests.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                           All Purchase Requests
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('custodian.orders.index') }}" 
                           class="block p-2 rounded-md text-sm {{ request()->routeIs('custodian.orders.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                           Purchase Orders
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('custodian.inspection.index') }}" 
                           class="block p-2 rounded-md text-sm {{ $isInspectionActive ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">
                           Inspection & Acceptance
                        </a>
                    </li>
                </ul>
            </li>
            @elseif($role === 'bac')
            <li>
                <a href="{{ route('bac.requests.index') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ request()->routeIs('bac.requests.*') ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i class="fas fa-shopping-cart text-lg w-6 text-center"></i>
                    <span class="ml-4 [.w-20_&]:hidden">All Purchase Requests</span>
                    <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">All Purchase Requests</span>
                </a>
            </li>
            @endif

            <!-- Inventory (custodian only) -->
            @if($role === 'custodian')
            <li>
                <button class="dropdown-toggle group w-full flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isInventoryActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i class="fas fa-boxes-stacked text-lg w-6 text-center"></i>
                    <span class="ml-4 text-left flex-1 [.w-20_&]:hidden">Inventory</span>
                    <i class="fas fa-chevron-down text-xs ml-auto transition-transform duration-300 {{ $isInventoryActive ? 'rotate-180' : '' }} [.w-20_&]:hidden"></i>
                </button>
                <ul class="pl-7 space-y-1 mt-1 {{ $isInventoryActive ? '' : 'hidden' }} [.w-20_&]:hidden">
                    @if (Route::has('custodian.inventory.index'))
                        <li><a href="{{ route('custodian.inventory.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('custodian.inventory.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">Inventory Assignment</a></li>
                    @endif
                    @if (Route::has('pqs.index'))
                        <li><a href="{{ route('pqs.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('pqs.*') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">PQS</a></li>
                    @endif
                    @if (Route::has('ics.index'))
                        <li><a href="{{ route('ics.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('ics.index') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">ICS</a></li>
                    @endif
                    @if (Route::has('par.index'))
                        <li><a href="{{ route('par.index') }}" class="block p-2 rounded-md text-sm {{ request()->routeIs('par.index') ? 'text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900' }}">PAR</a></li>
                    @endif
                </ul>
            </li>
            @endif

            <!-- Reports link removed to avoid undefined route errors -->

            <!-- Audit Logs (custodian only) -->
            @if($role === 'custodian')
            <li>
                <a href="{{ route('custodian.audit_logs.index') }}" class="group relative flex items-center p-3 rounded-lg font-medium transition-colors duration-200 {{ $isAuditActive ? 'bg-[var(--secondary-color)] text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i class="fas fa-file-invoice text-lg w-6 text-center"></i>
                    <span class="ml-4 [.w-20_&]:hidden">Audit Logs</span>
                    <span class="absolute left-full ml-4 px-2 py-1 text-sm bg-gray-800 text-white rounded-md opacity-0 [.w-20_&]:group-hover:opacity-100 transition-opacity">Audit Logs</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>
</div>
