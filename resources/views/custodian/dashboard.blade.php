@extends('layouts.app')

@section('title', 'Custodian Dashboard')

@php
$statusStyles = [
        'pending'   => ['icon' => 'fas fa-clock',          'color' => 'amber'],
        'approved'  => ['icon' => 'fas fa-check-circle',   'color' => 'green'],
        'rejected'  => ['icon' => 'fas fa-times-circle',   'color' => 'red'],
        'completed' => ['icon' => 'fas fa-award',          'color' => 'blue'],
        'default'   => ['icon' => 'fas fa-question-circle','color' => 'gray'],
];

$logActionStyles = [
        'create'  => 'green',
        'update'  => 'blue',
        'delete'  => 'red',
        'login'   => 'purple',
        'logout'  => 'gray',
        'default' => 'gray',
];

$pipelineCards = [
        ['label' => 'Accepted Items',     'value' => $inventoryStats['accepted'], 'icon' => 'fa-clipboard-check', 'accent' => 'blue'],
        ['label' => 'Ready for PQS',      'value' => $inventoryStats['ready'],    'icon' => 'fa-hourglass-half',  'accent' => 'amber'],
        ['label' => 'Recorded in PQS',    'value' => $inventoryStats['recorded'], 'icon' => 'fa-barcode',         'accent' => 'emerald'],
        ['label' => 'With ICS',           'value' => $inventoryStats['withIcs'],  'icon' => 'fa-file-signature',  'accent' => 'teal'],
        ['label' => 'With PAR',           'value' => $inventoryStats['withPar'],  'icon' => 'fa-file-contract',   'accent' => 'purple'],
        ['label' => 'Awaiting Documents', 'value' => $inventoryStats['awaiting'], 'icon' => 'fa-clipboard-list',  'accent' => 'slate'],
];

$pipelineColors = [
        'blue'    => 'bg-blue-50 text-blue-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber'   => 'bg-amber-50 text-amber-600',
        'teal'    => 'bg-teal-50 text-teal-600',
        'purple'  => 'bg-purple-50 text-purple-600',
        'slate'   => 'bg-slate-100 text-slate-600',
];

$statCards = [
        ['label' => 'Purchase Requests', 'value' => number_format($totalRequests),           'icon' => 'fa-file-alt',            'border' => 'border-blue-200/60',   'bg' => 'bg-blue-50',   'text' => 'text-blue-600'],
        ['label' => 'ICS Quantity',      'value' => number_format($totalIcs),                 'icon' => 'fa-box',                 'border' => 'border-amber-200/60',  'bg' => 'bg-amber-50',  'text' => 'text-amber-600'],
        ['label' => 'Purchase Orders',   'value' => number_format($totalOrders),              'icon' => 'fa-file-invoice-dollar', 'border' => 'border-green-200/60',  'bg' => 'bg-green-50',  'text' => 'text-green-600'],
        ['label' => 'Total Assets',      'value' => number_format($totalAssets),              'icon' => 'fa-archive',             'border' => 'border-purple-200/60', 'bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
        ['label' => 'Total Expenses',    'value' => '₱' . number_format($totalExpense, 2),   'icon' => 'fa-money-bill-wave',     'border' => 'border-red-200/60',    'bg' => 'bg-red-50',    'text' => 'text-red-600'],
];
@endphp

@section('content')
<div class="space-y-8">

        {{-- =============================================================== --}}
        {{-- 1. Page Header --}}
        {{-- =============================================================== --}}
        <div class="animate-card" style="animation-delay: 80ms;">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                        <div>
                                <h1 class="text-3xl font-extrabold tracking-tight text-[#1a3a2d]">Dashboard</h1>
                                <p class="mt-1 text-base text-gray-500">Welcome back, <span class="font-semibold text-gray-700">{{ session('username') }}</span></p>
                        </div>
                        <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200/60">
                                        <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span></span>
                                        Live
                                </span>
                                <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-[#1a3a2d]/5 px-4 py-2 text-sm font-semibold text-[#1a3a2d] ring-1 ring-[#1a3a2d]/10">
                                        <i class="fas fa-shield-alt text-xs"></i> Custodian
                                </span>
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 2. KPI Stat Cards --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
                @foreach ($statCards as $i => $card)
                <div class="animate-card group relative bg-white rounded-2xl border {{ $card['border'] }} p-6 transition-all duration-200 hover:shadow-lg hover:-translate-y-1" style="animation-delay: {{ 120 + $i * 40 }}ms;">
                        <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $card['bg'] }} {{ $card['text'] }} transition-transform duration-200 group-hover:scale-110">
                                                <i class="fas {{ $card['icon'] }} text-xl"></i>
                                        </div>
                                </div>
                                <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $card['label'] }}</p>
                                        <p class="mt-1 text-3xl font-extrabold text-gray-900 leading-none truncate">{{ $card['value'] }}</p>
                                </div>
                        </div>
                </div>
                @endforeach
        </div>

        {{-- =============================================================== --}}
        {{-- 3. Inventory Pipeline Snapshot --}}
        {{-- =============================================================== --}}
        <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md" style="animation-delay: 340ms;">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-8 pt-8 pb-4">
                        <div>
                                <h2 class="text-xl font-bold text-gray-900">Inventory Pipeline</h2>
                                <p class="text-sm text-gray-500 mt-1">Track item progression from acceptance through custodial documentation.</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-emerald-700">
                                <i class="fas fa-signal text-[10px]"></i> Live
                        </span>
                </div>

                <div class="px-8 pb-8 pt-4 grid grid-cols-1 lg:grid-cols-5 gap-8">
                        {{-- Pipeline metric cards --}}
                        <div class="lg:col-span-3 grid grid-cols-2 md:grid-cols-3 gap-5">
                                @foreach ($pipelineCards as $card)
                                <div class="rounded-2xl bg-gray-50/80 border border-gray-100 p-5 transition hover:shadow-md hover:bg-white">
                                        <div class="flex items-center gap-4">
                                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl {{ $pipelineColors[$card['accent']] ?? 'bg-gray-100 text-gray-600' }}">
                                                        <i class="fas {{ $card['icon'] }} text-lg"></i>
                                                </div>
                                                <div class="min-w-0">
                                                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 truncate">{{ $card['label'] }}</p>
                                                        <p class="text-2xl font-extrabold text-gray-900 leading-tight mt-1">{{ number_format($card['value']) }}</p>
                                                </div>
                                        </div>
                                </div>
                                @endforeach
                        </div>

                        {{-- Doughnut chart --}}
                        <div class="lg:col-span-2 flex flex-col">
                                <h3 class="text-base font-semibold text-gray-800">Custodial Progress</h3>
                                <p class="text-sm text-gray-500 mt-1">Document issuance breakdown for PQS records.</p>
                                <div class="relative mt-4 flex-1 min-h-[250px]">
                                        <canvas id="assignmentBreakdownChart"></canvas>
                                </div>
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 4. Charts + Request Status --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Monthly Expense Trend --}}
                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md lg:col-span-2" style="animation-delay: 400ms;">
                        <div class="px-8 pt-8 pb-4">
                                <h2 class="text-xl font-bold text-gray-900">Monthly Expense Trend</h2>
                                <p class="text-sm text-gray-500 mt-1">Spending patterns across recent months.</p>
                        </div>
                        <div class="px-8 pb-8 pt-2">
                                <div class="relative h-72">
                                        <canvas id="expenseChart" class="h-full w-full"></canvas>
                                </div>
                        </div>
                </div>

                {{-- Request Status --}}
                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md flex flex-col h-full" style="animation-delay: 460ms;">
                        <div class="px-8 pt-8 pb-4 flex-shrink-0">
                                <h2 class="text-xl font-bold text-gray-900">Request Status</h2>
                                <p class="text-sm text-gray-500 mt-1">Breakdown of current request pipeline.</p>
                        </div>
                        <div class="px-6 pb-8 pt-2 space-y-2 overflow-y-auto flex-1 max-h-[328px] !pr-3">
                                @forelse($statusSummary as $status => $count)
                                        @php $style = $statusStyles[strtolower($status)] ?? $statusStyles['default']; @endphp
                                        <div class="flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-gray-50/80 transition-colors">
                                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-{{ $style['color'] }}-50 text-{{ $style['color'] }}-600">
                                                        <i class="{{ $style['icon'] }} text-lg"></i>
                                                </div>
                                                <span class="flex-1 text-base font-semibold text-gray-700">{{ ucfirst($status) }}</span>
                                                <span class="text-2xl font-extrabold text-gray-900 tabular-nums">{{ number_format($count) }}</span>
                                        </div>
                                @empty
                                        <div class="text-center text-gray-400 py-12">
                                                <i class="fas fa-chart-pie fa-3x mb-3 text-gray-300"></i>
                                                <p class="text-base font-medium">No status data yet</p>
                                        </div>
                                @endforelse
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 5. Custodial Records Trend + Recent PQS --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md xl:col-span-2" style="animation-delay: 520ms;">
                        <div class="px-8 pt-8 pb-4">
                                <h2 class="text-xl font-bold text-gray-900">Custodial Records Trend</h2>
                                <p class="text-sm text-gray-500 mt-1">Monthly PQS volume paired with total asset value.</p>
                        </div>
                        <div class="px-8 pb-8 pt-2">
                                <div class="relative h-80">
                                        <canvas id="pqsTrendChart"></canvas>
                                </div>
                                <div class="mt-6 flex flex-wrap items-center gap-6 text-xs font-bold uppercase tracking-widest text-gray-500">
                                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-[#1a3a2d]"></span> Assets Recorded</span>
                                        <span class="inline-flex items-center gap-2"><span class="h-3 w-3 rounded-full bg-blue-400"></span> Asset Value (₱)</span>
                                </div>
                        </div>
                </div>

                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md flex flex-col h-full" style="animation-delay: 580ms;">
                        <div class="px-8 pt-8 pb-4 flex-shrink-0">
                                <h2 class="text-xl font-bold text-gray-900">Recent Records</h2>
                                <p class="text-sm text-gray-500 mt-1">Latest documentation updates and PQS entries.</p>
                        </div>
                        <div class="px-6 pb-8 pt-2 space-y-4 overflow-y-auto flex-1 max-h-[400px] !pr-3">
                                @forelse ($recentPqs as $record)
                                        @php
                                                $documentBadge = ['label' => 'Awaiting', 'classes' => 'bg-amber-50 text-amber-700 ring-amber-200/60'];
                                                if ($record->icsRecord) {
                                                        $documentBadge = ['label' => 'ICS Issued', 'classes' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/60'];
                                                } elseif ($record->parRecord) {
                                                        $documentBadge = ['label' => 'PAR Issued', 'classes' => 'bg-purple-50 text-purple-700 ring-purple-200/60'];
                                                }
                                        @endphp
                                        <div class="rounded-xl border border-gray-100 p-4 hover:border-[#1a3a2d]/20 transition-colors bg-gray-50/50 hover:bg-white shadow-sm">
                                                <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                                <p class="text-base font-bold text-gray-800 truncate">{{ $record->property_no }}</p>
                                                                <p class="text-xs text-gray-500 truncate mt-0.5">
                                                                        {{ optional($record->category?->parent)->cat_name ?? 'General' }} &middot;
                                                                        {{ $record->category?->cat_name ?? 'Uncategorised' }}
                                                                </p>
                                                        </div>
                                                        <span class="flex-shrink-0 text-base font-extrabold text-[#1a3a2d]">₱{{ number_format($record->total_value ?? 0, 2) }}</span>
                                                </div>
                                                <div class="mt-4 flex items-center justify-between">
                                                        <span class="text-xs font-medium text-gray-500">
                                                                <i class="far fa-calendar text-gray-400 mr-1.5"></i>{{ optional($record->date_acquired)->format('M d, Y') ?? '—' }}
                                                        </span>
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider ring-1 {{ $documentBadge['classes'] }}">
                                                                {{ $documentBadge['label'] }}
                                                        </span>
                                                </div>
                                        </div>
                                @empty
                                        <div class="text-center text-gray-400 py-12">
                                                <i class="fas fa-clipboard-list fa-3x mb-3 text-gray-300"></i>
                                                <p class="text-base font-medium">No records yet</p>
                                                <p class="text-sm mt-1">PQS records will appear here once generated.</p>
                                        </div>
                                @endforelse
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 6. Top-5 Data Tables --}}
        {{-- =============================================================== --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Top Requested Items --}}
                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md" style="animation-delay: 640ms;">
                        <div class="px-8 pt-8 pb-4">
                                <h2 class="text-xl font-bold text-gray-900">Top Requested Items</h2>
                        </div>
                        <div class="px-3 pb-6">
                                <table class="w-full text-base">
                                        <thead>
                                                <tr class="border-b border-gray-200">
                                                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500">Item</th>
                                                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-500">Count</th>
                                                </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                                @forelse($topRequests as $req)
                                                <tr class="hover:bg-gray-50/60 transition-colors">
                                                        <td class="px-5 py-4 font-medium text-gray-800">{{ Str::limit($req->item_description, 36) }}</td>
                                                        <td class="px-5 py-4 text-right font-extrabold text-[#1a3a2d] tabular-nums">{{ number_format($req->count) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                        <td colspan="2" class="px-5 py-12 text-center text-gray-400">
                                                                <i class="fas fa-box-open fa-3x mb-3 text-gray-300"></i>
                                                                <p class="text-base font-medium">No data yet</p>
                                                        </td>
                                                </tr>
                                                @endforelse
                                        </tbody>
                                </table>
                        </div>
                </div>

                {{-- Top POs by Cost --}}
                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md" style="animation-delay: 700ms;">
                        <div class="px-8 pt-8 pb-4">
                                <h2 class="text-xl font-bold text-gray-900">Top POs by Cost</h2>
                        </div>
                        <div class="px-3 pb-6 overflow-x-auto">
                                <table class="w-full text-base whitespace-nowrap min-w-[350px]">
                                        <thead>
                                                <tr class="border-b border-gray-200">
                                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500">PO #</th>
                                                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500 w-full">Description</th>
                                                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-500">Cost</th>
                                                </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                                @forelse($topExpenses as $po)
                                                <tr class="hover:bg-gray-50/60 transition-colors">
                                                        <td class="px-4 py-4 font-semibold text-gray-800">{{ $po->po_no }}</td>
                                                        <td class="px-4 py-4 text-gray-500 truncate max-w-[150px]" title="{{ $po->description }}">{{ Str::limit($po->description, 25) }}</td>
                                                        <td class="px-4 py-4 text-right font-extrabold text-[#1a3a2d] tabular-nums">₱{{ number_format($po->total, 2) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                        <td colspan="3" class="px-5 py-12 text-center text-gray-400">
                                                                <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-gray-300"></i>
                                                                <p class="text-base font-medium">No data yet</p>
                                                        </td>
                                                </tr>
                                                @endforelse
                                        </tbody>
                                </table>
                        </div>
                </div>

                {{-- Top Asset Categories --}}
                <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md" style="animation-delay: 760ms;">
                        <div class="px-8 pt-8 pb-4">
                                <h2 class="text-xl font-bold text-gray-900">Top Asset Categories</h2>
                        </div>
                        <div class="px-3 pb-6">
                                <table class="w-full text-base">
                                        <thead>
                                                <tr class="border-b border-gray-200">
                                                        <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500">Category</th>
                                                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-500">Assets</th>
                                                        <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-500">Value</th>
                                                </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                                @forelse ($topCategories as $category)
                                                <tr class="hover:bg-gray-50/60 transition-colors">
                                                        <td class="px-5 py-4 font-medium text-gray-800">{{ $category->cat_name }}</td>
                                                        <td class="px-5 py-4 text-right font-extrabold text-[#1a3a2d] tabular-nums">{{ number_format($category->total_assets) }}</td>
                                                        <td class="px-5 py-4 text-right font-semibold text-gray-500 tabular-nums whitespace-nowrap">₱{{ number_format($category->total_value ?? 0, 2) }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                        <td colspan="3" class="px-5 py-12 text-center text-gray-400">
                                                                <i class="fas fa-layer-group fa-3x mb-3 text-gray-300"></i>
                                                                <p class="text-base font-medium">No data yet</p>
                                                        </td>
                                                </tr>
                                                @endforelse
                                        </tbody>
                                </table>
                        </div>
                </div>
        </div>

        {{-- =============================================================== --}}
        {{-- 7. Recent Activity Log --}}
        {{-- =============================================================== --}}
        <div class="animate-card bg-white rounded-2xl border border-gray-200/60 shadow-md" style="animation-delay: 820ms;">
                <div class="px-8 pt-8 pb-4">
                        <h2 class="text-xl font-bold text-gray-900">Recent Activity</h2>
                </div>
                <div class="px-3 pb-6 overflow-x-auto">
                        <table class="w-full text-base">
                                <thead>
                                        <tr class="border-b border-gray-200">
                                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500">User</th>
                                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500">Action</th>
                                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-500">Description</th>
                                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-500">Time</th>
                                        </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                        @forelse($recentLogs as $log)
                                                @php $actionColor = $logActionStyles[strtolower($log->action)] ?? $logActionStyles['default']; @endphp
                                                <tr class="hover:bg-gray-50/60 transition-colors">
                                                        <td class="px-5 py-4 font-medium text-gray-800 whitespace-nowrap">{{ $log->account->username ?? 'System' }}</td>
                                                        <td class="px-5 py-4">
                                                                <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold uppercase bg-{{ $actionColor }}-50 text-{{ $actionColor }}-700 ring-1 ring-{{ $actionColor }}-200/60">{{ $log->action }}</span>
                                                        </td>
                                                        <td class="px-5 py-4 text-gray-600">{{ Str::limit($log->description, 80) }}</td>
                                                        <td class="px-5 py-4 text-right text-gray-500 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($log->log_time)->diffForHumans() }}</td>
                                                </tr>
                                        @empty
                                                <tr>
                                                        <td colspan="4" class="px-5 py-12 text-center text-gray-400">
                                                                <i class="fas fa-history fa-3x mb-3 text-gray-300"></i>
                                                                <p class="text-base font-medium">No activity yet</p>
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
