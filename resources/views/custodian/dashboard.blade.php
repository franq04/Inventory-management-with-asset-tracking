@extends('layouts.app')

@section('title', 'Custodian Dashboard')

@php
$statusStyles = [
        'pending' => ['icon' => 'fas fa-clock', 'color' => 'amber'],
        'approved' => ['icon' => 'fas fa-check-circle', 'color' => 'green'],
        'rejected' => ['icon' => 'fas fa-times-circle', 'color' => 'red'],
        'completed' => ['icon' => 'fas fa-award', 'color' => 'blue'],
        'default' => ['icon' => 'fas fa-question-circle', 'color' => 'gray'],
];

$logActionStyles = [
        'create' => 'green',
        'update' => 'blue',
        'delete' => 'red',
        'login' => 'purple',
        'logout' => 'gray',
        'default' => 'gray',
];

$pipelineCards = [
        ['label' => 'Accepted Items', 'value' => $inventoryStats['accepted'], 'icon' => 'fa-clipboard-check', 'accent' => 'blue'],
        ['label' => 'Ready for PQS', 'value' => $inventoryStats['ready'], 'icon' => 'fa-hourglass-half', 'accent' => 'amber'],
        ['label' => 'Recorded in PQS', 'value' => $inventoryStats['recorded'], 'icon' => 'fa-barcode', 'accent' => 'emerald'],
        ['label' => 'With ICS', 'value' => $inventoryStats['withIcs'], 'icon' => 'fa-file-signature', 'accent' => 'teal'],
        ['label' => 'With PAR', 'value' => $inventoryStats['withPar'], 'icon' => 'fa-file-contract', 'accent' => 'purple'],
        ['label' => 'Awaiting Documents', 'value' => $inventoryStats['awaiting'], 'icon' => 'fa-clipboard-list', 'accent' => 'slate'],
];

$pipelineColors = [
        'blue' => 'bg-blue-100 text-blue-600',
        'emerald' => 'bg-emerald-100 text-emerald-600',
        'amber' => 'bg-amber-100 text-amber-600',
        'teal' => 'bg-teal-100 text-teal-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'slate' => 'bg-slate-100 text-slate-600',
];
@endphp

@section('content')
<div class="space-y-10">
        {{-- =============================================================== --}}
        {{-- 1. Enhanced Header with Call-to-Action --}}
        {{-- =============================================================== --}}
        <div class="animate-card flex flex-wrap items-center justify-between gap-4" style="animation-delay: 100ms;">
                <div>
                        <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Custodian Dashboard</h2>
                        <p class="mt-1 text-gray-500">Welcome back, <span class="font-semibold text-gray-700">{{ session('username') }}</span>! Here's your real-time system overview.</p>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-500">
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 font-semibold text-emerald-700">
                                <i class="fas fa-wifi"></i> Live Monitoring
                        </span>
                        <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-[#1a3a2d] px-4 py-2 font-semibold text-white">
                                <i class="fas fa-shield-alt"></i> Custodian Access
                        </span>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 2. Dynamic & Responsive Stat Cards --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border border-blue-100 flex items-center gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="animation-delay: 200ms;">
                        <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500 truncate">Purchase Requests</p>
                                <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-800 break-words">{{ number_format($totalRequests) }}</p>
                        </div>
                        <div class="bg-blue-100 text-blue-600 rounded-full h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-file-alt text-xl"></i>
                        </div>
                </div>
                <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border border-amber-100 flex items-center gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="animation-delay: 250ms;">
                        <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500 truncate">Total ICS Quantity</p>
                                <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-800 break-words">{{ number_format($totalIcs) }}</p>
                        </div>
                        <div class="bg-amber-100 text-amber-600 rounded-full h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-box text-xl"></i>
                        </div>
                </div>
                <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border border-green-100 flex items-center gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="animation-delay: 300ms;">
                        <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500 truncate">Purchase Orders</p>
                                <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-800 break-words">{{ number_format($totalOrders) }}</p>
                        </div>
                        <div class="bg-green-100 text-green-600 rounded-full h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                </div>
                <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border border-purple-100 flex items-center gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="animation-delay: 350ms;">
                        <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Assets</p>
                                <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-800 break-words">{{ number_format($totalAssets) }}</p>
                        </div>
                        <div class="bg-purple-100 text-purple-600 rounded-full h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-archive text-xl"></i>
                        </div>
                </div>
                <div class="animate-card bg-white p-6 rounded-2xl shadow-lg border border-red-100 flex items-center gap-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="animation-delay: 400ms;">
                        <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Expenses</p>
                                <p class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-800 break-words">₱{{ number_format($totalExpense, 2) }}</p>
                        </div>
                        <div class="bg-red-100 text-red-600 rounded-full h-12 w-12 flex-shrink-0 flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-xl"></i>
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 2.1 Inventory Pipeline Snapshot --}}
        {{-- =============================================================== --}}
        <div class="animate-card bg-white rounded-2xl shadow-lg border border-gray-100 p-6" style="animation-delay: 450ms;">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                                <h3 class="text-xl font-bold text-gray-800">Inventory Pipeline Snapshot</h3>
                                <p class="text-sm text-gray-500">Monitor how accepted items progress into PQS and custodial documents.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                <i class="fas fa-signal"></i> Live Metrics
                        </span>
                </div>
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach ($pipelineCards as $card)
                                        <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-5 shadow-sm hover:shadow-md transition">
                                                <div class="flex items-center gap-4">
                                                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ $pipelineColors[$card['accent']] ?? 'bg-gray-100 text-gray-600' }}">
                                                                <i class="fas {{ $card['icon'] }} text-lg"></i>
                                                        </div>
                                                        <div>
                                                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $card['label'] }}</p>
                                                                <p class="text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                                                        </div>
                                                </div>
                                        </div>
                                @endforeach
                        </div>
                        <div class="lg:col-span-2">
                                <div class="flex items-center justify-between gap-3">
                                        <div>
                                                <h4 class="text-lg font-semibold text-gray-800">Custodial Progress</h4>
                                                <p class="text-sm text-gray-500">Compare items still waiting for PQS versus those already issued documents.</p>
                                        </div>
                                </div>
                                <div class="relative mt-4 h-56">
                                        <canvas id="assignmentBreakdownChart"></canvas>
                                </div>
                                <p class="mt-3 text-xs text-gray-400">Includes PQS records awaiting ICS/PAR plus completed custodial documents.</p>
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 3. Main Content Grid (Chart, Status, Top Items) --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="animate-card bg-white rounded-2xl shadow-lg p-6 lg:col-span-2" style="animation-delay: 500ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Monthly Expense Trend</h3>
                        <p class="text-sm text-gray-500 mb-4">Visualizing spending patterns over the recent months.</p>
                        <div class="relative h-48">
                                <canvas id="expenseChart" class="h-full w-full"></canvas>
                        </div>
                </div>

                <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 600ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Request Status</h3>
                        <div class="space-y-2">
                                @forelse($statusSummary as $status => $count)
                                        @php
                                                $style = $statusStyles[strtolower($status)] ?? $statusStyles['default'];
                                        @endphp
                                        <div class="flex items-center p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                                <div class="bg-{{ $style['color'] }}-100 text-{{ $style['color'] }}-600 h-10 w-10 flex items-center justify-center rounded-lg mr-4">
                                                        <i class="{{ $style['icon'] }}"></i>
                                                </div>
                                                <div class="flex-1">
                                                        <p class="font-semibold text-gray-700">{{ ucfirst($status) }}</p>
                                                </div>
                                                <p class="text-xl font-bold text-gray-800">{{ number_format($count) }}</p>
                                        </div>
                                @empty
                                        <div class="text-center text-gray-500 py-8">
                                                <i class="fas fa-chart-pie fa-2x text-gray-300 mb-2"></i>
                                                <p>No status data available.</p>
                                        </div>
                                @endforelse
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 3.1 Custodial Trend & Recent Records --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="animate-card bg-white rounded-2xl shadow-lg p-6 xl:col-span-2" style="animation-delay: 650ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">Custodial Records Trend</h3>
                        <p class="text-sm text-gray-500 mb-4">Monthly PQS creation volume paired with total asset value captured.</p>
                        <div class="relative h-64">
                                <canvas id="pqsTrendChart"></canvas>
                        </div>
                        <div class="mt-5 flex flex-wrap items-center gap-4 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <span class="inline-flex items-center gap-2"><i class="fas fa-circle text-[#1a3a2d]"></i> Assets Recorded</span>
                                <span class="inline-flex items-center gap-2"><i class="fas fa-circle text-blue-400"></i> Asset Value (₱)</span>
                        </div>
                </div>

                <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 700ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Custodial Records</h3>
                        <div class="space-y-4">
                                @forelse ($recentPqs as $record)
                                        @php
                                                $documentBadge = ['label' => 'Awaiting Document', 'classes' => 'bg-amber-100 text-amber-700'];
                                                if ($record->icsRecord) {
                                                        $documentBadge = ['label' => 'ICS Issued', 'classes' => 'bg-emerald-100 text-emerald-700'];
                                                } elseif ($record->parRecord) {
                                                        $documentBadge = ['label' => 'PAR Issued', 'classes' => 'bg-purple-100 text-purple-700'];
                                                }
                                        @endphp
                                        <div class="rounded-2xl border border-gray-100 p-4 shadow-sm hover:border-[#1a3a2d]/30 transition">
                                                <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                                <p class="text-sm font-semibold text-gray-800">{{ $record->property_no }}</p>
                                                                <p class="text-xs text-gray-500">
                                                                        {{ optional($record->category?->parent)->cat_name ?? 'General' }} &middot;
                                                                        {{ $record->category?->cat_name ?? 'Uncategorised' }}
                                                                </p>
                                                        </div>
                                                        <span class="text-sm font-bold text-[#1a3a2d]">₱{{ number_format($record->total_value ?? 0, 2) }}</span>
                                                </div>
                                                <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                                                        <span class="inline-flex items-center gap-2">
                                                                <i class="fas fa-calendar-alt text-[11px]"></i>
                                                                {{ optional($record->date_acquired)->format('M d, Y') ?? 'No date' }}
                                                        </span>
                                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 font-semibold {{ $documentBadge['classes'] }}">
                                                                <i class="fas fa-file-circle-check text-[11px]"></i>
                                                                {{ $documentBadge['label'] }}
                                                        </span>
                                                </div>
                                        </div>
                                @empty
                                        <div class="text-center text-gray-500 py-10">
                                                <i class="fas fa-clipboard-list fa-2x text-gray-300 mb-3"></i>
                                                <p class="font-medium">No custodial records found.</p>
                                                <p class="text-xs">Once PQS records are generated they will appear here.</p>
                                        </div>
                                @endforelse
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 4. Data Tables Grid with Zebra Striping & Better Empty States --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 750ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Top 5 Requested Items</h3>
                        <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                        <thead class="border-b-2 border-gray-200">
                                                <tr>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Item Description</th>
                                                        <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Requests</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                @forelse($topRequests as $req)
                                                        <tr class="even:bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                                                <td class="px-4 py-4 font-medium text-gray-800">{{ $req->item_description }}</td>
                                                                <td class="px-4 py-4 text-right font-bold text-lg text-[#1a3a2d]">{{ number_format($req->count) }}</td>
                                                        </tr>
                                                @empty
                                                        <tr>
                                                                <td colspan="2" class="p-8 text-center text-gray-500">
                                                                        <i class="fas fa-box-open fa-3x text-gray-300 mb-4"></i>
                                                                        <p class="font-medium">No Request Data Found</p>
                                                                        <p class="text-xs">When new requests are made, they will appear here.</p>
                                                                </td>
                                                        </tr>
                                                @endforelse
                                        </tbody>
                                </table>
                        </div>
                </div>

                <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 825ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Top 5 Purchase Orders by Cost</h3>
                        <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                        <thead class="border-b-2 border-gray-200">
                                                <tr>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">PO Number</th>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                                        <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Total Cost</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                @forelse($topExpenses as $po)
                                                        <tr class="even:bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                                                <td class="px-4 py-4 font-medium text-gray-800">{{ $po->po_no }}</td>
                                                                <td class="px-4 py-4 text-gray-600">{{ Str::limit($po->description, 40) }}</td>
                                                                <td class="px-4 py-4 text-right font-bold text-lg text-[#1a3a2d]">₱{{ number_format($po->total, 2) }}</td>
                                                        </tr>
                                                @empty
                                                        <tr>
                                                                <td colspan="3" class="p-8 text-center text-gray-500">
                                                                        <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-4"></i>
                                                                        <p class="font-medium">No Purchase Order Data</p>
                                                                        <p class="text-xs">When new purchase orders are created, they will show up here.</p>
                                                                </td>
                                                        </tr>
                                                @endforelse
                                        </tbody>
                                </table>
                        </div>
                </div>

                <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 900ms;">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Top Asset Categories</h3>
                        <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                        <thead class="border-b-2 border-gray-200">
                                                <tr>
                                                        <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                                        <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Assets</th>
                                                        <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Total Value</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                @forelse ($topCategories as $category)
                                                        <tr class="even:bg-gray-50/50 hover:bg-gray-50 transition-colors">
                                                                <td class="px-4 py-4 font-medium text-gray-800">{{ $category->cat_name }}</td>
                                                                <td class="px-4 py-4 text-right font-bold text-lg text-[#1a3a2d]">{{ number_format($category->total_assets) }}</td>
                                                                <td class="px-4 py-4 text-right font-semibold text-gray-700">₱{{ number_format($category->total_value ?? 0, 2) }}</td>
                                                        </tr>
                                                @empty
                                                        <tr>
                                                                <td colspan="3" class="p-8 text-center text-gray-500">
                                                                        <i class="fas fa-layer-group fa-3x text-gray-300 mb-4"></i>
                                                                        <p class="font-medium">No Asset Data Available</p>
                                                                        <p class="text-xs">New PQS records will update this breakdown automatically.</p>
                                                                </td>
                                                        </tr>
                                                @endforelse
                                        </tbody>
                                </table>
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 5. Recent Activity Log --}}
        {{-- =============================================================== --}}
        <div class="animate-card bg-white rounded-2xl shadow-lg p-6" style="animation-delay: 950ms;">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Activity Logs</h3>
                <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                                <thead class="border-b-2 border-gray-100">
                                        <tr>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                                <th class="px-4 py-3 text-right font-semibold text-gray-500 uppercase tracking-wider">Time</th>
                                        </tr>
                                </thead>
                                <tbody>
                                        @forelse($recentLogs as $log)
                                                @php
                                                        $actionColor = $logActionStyles[strtolower($log->action)] ?? $logActionStyles['default'];
                                                @endphp
                                                <tr class="even:bg-gray-50/50 hover:bg-gray-50/50 transition-colors">
                                                        <td class="px-4 py-4 font-medium text-gray-800">{{ $log->account->username ?? 'System' }}</td>
                                                        <td class="px-4 py-4">
                                                                <span class="px-3 py-1 text-xs font-bold uppercase text-{{ $actionColor }}-800 bg-{{ $actionColor }}-100 rounded-full">{{ $log->action }}</span>
                                                        </td>
                                                        <td class="px-4 py-4 text-gray-600">{{ $log->description }}</td>
                                                        <td class="px-4 py-4 text-gray-500 text-right">{{ \Carbon\Carbon::parse($log->log_time)->diffForHumans() }}</td>
                                                </tr>
                                        @empty
                                                <tr>
                                                        <td colspan="4" class="p-8 text-center text-gray-500">
                                                                <i class="fas fa-history fa-3x text-gray-300 mb-4"></i>
                                                                <p class="font-medium">No Recent Activity Found</p>
                                                                <p class="text-xs">System and user actions will be logged here.</p>
                                                        </td>
                                                </tr>
                                        @endforelse
                                </tbody>
                        </table>
                </div>
        </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
        window.dashboardData = {
                months: @json($months),
                totals: @json($totals),
                expenseMonths: @json($months),
                expenseTotals: @json($totals),
                pqsMonths: @json($inventoryMonths),
                pqsCounts: @json($inventoryCounts),
                pqsValues: @json($inventoryValues),
                assignment: @json($inventoryStats),
        };
</script>
@vite('resources/js/dashboard.js')
@endpush
