@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
@php
    $formatRequesterName = function ($account) {
        if (! $account) {
            return '—';
        }

        $employee = $account->employee ?? null;

        if ($employee) {
            $name = collect([
                $employee->first_name ?? null,
                $employee->middle_name ?? null,
                $employee->last_name ?? null,
                $employee->suffix ?? null,
            ])->filter()->implode(' ');

            if ($name) {
                return $name;
            }
        }

        return $account->username ?? '—';
    };

    $stageAccents = [
        'awaiting_delivery' => 'border-sky-200 bg-sky-50/80',
        'receiving' => 'border-amber-200 bg-amber-50/70',
        'inspection' => 'border-emerald-200 bg-emerald-50/70',
        'issues' => 'border-rose-200 bg-rose-50/70',
        'completed' => 'border-slate-200 bg-slate-50/80',
    ];

    $tabIcons = [
        'awaiting_delivery' => 'fa-truck',
        'receiving' => 'fa-box-open',
        'inspection' => 'fa-clipboard-check',
        'issues' => 'fa-triangle-exclamation',
        'completed' => 'fa-circle-check',
    ];

    $tabBadgeColors = [
        'awaiting_delivery' => 'bg-sky-100 text-sky-700',
        'receiving' => 'bg-amber-100 text-amber-700',
        'inspection' => 'bg-emerald-100 text-emerald-700',
        'issues' => 'bg-rose-100 text-rose-700',
        'completed' => 'bg-slate-100 text-slate-700',
    ];

    $stageMetaByKey = collect($pipelineStages ?? [])->keyBy('key');
    $currentUserRole = auth()->user()->role ?? null;
    $totalPipelineOrders = collect($pipelineStages)->sum('count');
    $readyForPoCount = $approvedRequests->count();
    $overviewTabs = [
        'ready' => [
            'label' => 'Ready for PO',
            'count' => $readyForPoCount,
            'icon' => 'fa-file-circle-check',
        ],
        'pipeline' => [
            'label' => 'Order Pipeline',
            'count' => $totalPipelineOrders,
            'icon' => 'fa-diagram-project',
        ],
        'register' => [
            'label' => 'PO Register',
            'count' => $purchaseOrders->total(),
            'icon' => 'fa-table-list',
        ],
    ];
    $activeOverviewTab = request()->query('po_tab', 'ready');
    if (! array_key_exists($activeOverviewTab, $overviewTabs)) {
        $activeOverviewTab = 'ready';
    }
@endphp

{{-- Page Header --}}
<div class="animate-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Purchase Orders</h2>
        <p class="mt-1 text-gray-500">Monitor deliveries from supplier coordination through inspection and acceptance.</p>
    </div>
    <a href="{{ route('custodian.orders.create') }}" class="w-full sm:w-auto cursor-pointer bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
        <i class="fas fa-file-invoice-dollar"></i>
        Generate Purchase Order
    </a>
</div>

<div class="mt-8 space-y-8 transition-all duration-200 ease-out">
    {{-- Hero Summary Band --}}
    <div id="purchaseOrderOverviewSummaryBand" class="animate-card rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10 overflow-hidden relative">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.18),_transparent_60%)]"></div>
        <div class="absolute -right-8 bottom-0 h-32 w-32 rounded-full border border-white/10 bg-white/5 blur-2xl"></div>
        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Purchase Order Management
                </div>
                <div>
                    <h3 class="text-2xl font-bold tracking-tight sm:text-3xl">Purchase Orders Summary</h3>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75 sm:text-[15px]">Monitor request activity, review submission volume, and move quickly through procurement work without leaving the current theme or workflow.</p>
                </div>
            </div>

            <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-2xl">
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Ready for PO</p>
                    <p id="purchaseOrderReadyCount" class="mt-2 text-2xl font-bold">{{ number_format($readyForPoCount) }}</p>
                        <p class="mt-1 text-xs text-white/65">Approved requests</p>
                    </div>
                    <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">In Pipeline</p>
                    <p id="purchaseOrderPipelineCount" class="mt-2 text-2xl font-bold">{{ number_format($totalPipelineOrders) }}</p>
                        <p class="mt-1 text-xs text-white/65">Active orders tracked</p>
                    </div>
                    <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Total Orders</p>
                    <p id="purchaseOrderRegisterCount" class="mt-2 text-2xl font-bold">{{ number_format($purchaseOrders->total()) }}</p>
                        <p class="mt-1 text-xs text-white/65">All-time registered</p>
                    </div>
                </div>
            </div>
        </div>

    {{-- Section Tabs --}}
    <div id="purchaseOrderOverviewTabs" class="animate-card rounded-[26px] border border-emerald-950/8 bg-white/95 p-4 shadow-[0_16px_45px_-30px_rgba(15,23,42,0.35)] backdrop-blur sm:p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($overviewTabs as $key => $tab)
                    @php
                        $isOverviewActive = $activeOverviewTab === $key;
                    @endphp
                    <button
                        type="button"
                        data-po-overview-tab="{{ $key }}"
                        data-active-class="bg-[#1a3a2d] text-white border-[#1a3a2d] shadow-[0_10px_28px_-16px_rgba(26,58,45,0.85)]"
                        data-inactive-class="bg-[#f8fbf9] text-[#2d5a4a] border-emerald-950/10 hover:bg-white hover:border-[#1a3a2d]/30"
                        data-count-active-class="bg-white/20 text-white"
                        data-count-inactive-class="bg-emerald-100 text-emerald-700"
                        class="inline-flex items-center gap-2.5 rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $isOverviewActive ? 'bg-[#1a3a2d] text-white border-[#1a3a2d] shadow-[0_10px_28px_-16px_rgba(26,58,45,0.85)]' : 'bg-[#f8fbf9] text-[#2d5a4a] border-emerald-950/10 hover:bg-white hover:border-[#1a3a2d]/30' }}"
                    >
                        <i class="fas {{ $tab['icon'] }} text-xs"></i>
                        <span>{{ $tab['label'] }}</span>
                        <span class="po-overview-tab-count rounded-full px-2 py-0.5 text-xs font-bold {{ $isOverviewActive ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700' }}">{{ number_format($tab['count']) }}</span>
                    </button>
                @endforeach
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-950/10 bg-[#f8fbf9] px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-[#2d5a4a]/80">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Active View:
                <span data-po-overview-label class="text-[#1a3a2d]">{{ $overviewTabs[$activeOverviewTab]['label'] ?? 'Ready for PO' }}</span>
            </div>
        </div>
    </div>

    {{-- Ready for Purchase Ordering --}}
    <div id="purchaseOrderReadySection" data-po-overview-panel="ready" class="animate-card rounded-[28px] border border-emerald-950/8 bg-white/95 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur {{ $activeOverviewTab === 'ready' ? '' : 'hidden' }}">
        <div class="px-6 py-6 sm:px-8 lg:px-10">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between mb-6">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Approved Requests</p>
                    <h2 class="mt-1 text-xl font-bold text-[#1a3a2d]">Ready for Purchase Ordering</h2>
                    <p class="mt-1 text-sm text-gray-500">Approved requests with funding clearance and no associated purchase order yet.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-xs font-bold text-emerald-700">
                    <i class="fa-solid fa-file-circle-check"></i>
                    {{ $approvedRequests->total() }} request{{ $approvedRequests->total() === 1 ? '' : 's' }}
                </span>
            </div>
            @if ($approvedRequests->total() > 0)
          <div id="po-table-container" class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-[#f5f8f6] text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">PR No.</th>
                            <th class="px-5 py-3 text-left">Purpose</th>
                            <th class="px-5 py-3 text-left">Requested By</th>
                            <th class="px-5 py-3 text-left">Division / Section</th>
                            <th class="px-5 py-3 text-right">Submitted</th>
                            <th class="px-5 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvedRequests as $request)
                            <tr class="group border-b border-gray-100/90 last:border-0 hover:bg-[#f6fbf8] transition-colors duration-200">
                                <td class="px-5 py-4 align-top">
                                    <span class="block font-bold text-[#1a3a2d]">{{ $request->pr_no }}</span>
                                    <span class="mt-1 inline-flex rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-600">Approved</span>
                                </td>
                                <td class="px-5 py-4 align-top text-gray-600 max-w-xs">
                                    <span class="block line-clamp-2 leading-6" title="{{ $request->purpose }}">{{ \Illuminate\Support\Str::limit($request->purpose, 80) }}</span>
                                </td>
                                <td class="px-5 py-4 align-top text-gray-700 font-medium">{{ $formatRequesterName($request->requester) }}</td>
                                <td class="px-5 py-4 align-top">
                                    <div class="text-xs font-semibold leading-5">
                                        <span class="text-gray-800">{{ $request->division?->division_name ?? '—' }}</span>
                                        <span class="block text-gray-500 font-normal">{{ $request->section?->section_name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top text-right text-gray-500 text-xs leading-5">{{ optional($request->created_at)->format('M d, Y') }}</td>
                                <td class="px-5 py-4 align-top text-center">
                                    <div class="flex items-center justify-center">
                                        <a href="{{ route('custodian.orders.create', ['pr' => $request->pr_no]) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg">
                                            <i class="fas fa-file-signature"></i>
                                            Create PO
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                {{ $approvedRequests->onEachSide(1)->links('vendor.pagination.procurement') }}
            </div>
        </div>
            @else
            <div class="rounded-xl border-2 border-dashed border-emerald-200 bg-emerald-50/40 px-6 py-12 text-center">
                <i class="fa-solid fa-file-circle-check text-4xl text-emerald-300 mb-3"></i>
                <p class="font-medium text-gray-700">No approved requests are waiting for PO creation.</p>
                <p class="text-sm text-gray-500 mt-1">New approved purchase requests will appear here automatically.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Order Pipeline --}}
    <div id="purchaseOrderPipelineSection" data-po-overview-panel="pipeline" class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-6 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-8 lg:p-10 space-y-6 {{ $activeOverviewTab === 'pipeline' ? '' : 'hidden' }}">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Delivery Tracking</p>
                <h2 class="mt-1 text-xl font-bold text-[#1a3a2d]">Order Pipeline</h2>
                <p class="mt-1 text-sm text-gray-500">Track orders from supplier coordination through inspection and acceptance.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach ($pipelineStages as $idx => $stage)
                    @php
                        $tabIcon = $tabIcons[$stage['key']] ?? 'fa-folder';
                        $isActive = $idx === 0;
                        $badgeColor = $tabBadgeColors[$stage['key']] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <button type="button"
                        class="pipeline-tab inline-flex cursor-pointer items-center gap-2.5 rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200 group
                            {{ $isActive ? 'bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d]' : 'border-gray-200 bg-[#fbfcfb] text-gray-600 hover:bg-gray-100 hover:border-gray-300' }}"
                        data-tab="{{ $stage['key'] }}">
                        <i class="fas {{ $tabIcon }} text-xs"></i>
                        <span class="hidden sm:inline">{{ $stage['label'] }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold
                            {{ $isActive ? 'bg-white/20 text-white' : $badgeColor }}">
                            {{ $stage['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="mt-2">
            @foreach ($pipelineStages as $idx => $stage)
                @php
                    $accent = $stageAccents[$stage['key']] ?? 'border-gray-100 bg-gray-50';
                    $overdueCount = $stage['orders']->filter(fn($s) => !empty($s['timing']['overdue']))->count();
                    $earlyCount = $stage['orders']->filter(fn($s) => !empty($s['timing']['arrived_early']))->count();
                    $isActive = $idx === 0;
                @endphp
                <div class="pipeline-panel {{ $isActive ? '' : 'hidden' }}" data-panel="{{ $stage['key'] }}">
                    {{-- Stage Stats Bar --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full {{ $stage['badge'] }} flex items-center justify-center">
                                <i class="fas {{ $tabIcons[$stage['key']] ?? 'fa-folder' }}"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $stage['label'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $stage['description'] }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($overdueCount)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700">
                                    <i class="fas fa-clock"></i> {{ $overdueCount }} Overdue
                                </span>
                            @endif
                            @if ($earlyCount)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                    <i class="fas fa-truck-fast"></i> {{ $earlyCount }} Early
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Orders List --}}
                    @if ($stage['orders']->isEmpty())
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center">
                            <i class="fas {{ $tabIcons[$stage['key']] ?? 'fa-folder-open' }} text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No purchase orders in this stage.</p>
                        </div>
                    @else
                        <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                            @foreach ($stage['orders'] as $summary)
                                @php
                                    $order = $summary['order'];
                                    $totals = $summary['totals'];
                                    $deliveries = $summary['deliveries'];
                                    $issueBreakdown = $summary['issue_breakdown'];
                                    $hasIssues = $summary['has_issues'];
                                    $latestReport = $deliveries['latest_report'];
                                    $receivedCount = $totals['received'] ?? 0;
                                    $awaitingReceipt = $totals['awaiting_receipt'] ?? max(($totals['items'] ?? 0) - $receivedCount, 0);
                                    $timing = $summary['timing'];
                                    $expectedDelivery = $timing['expected'];
                                    $firstDeliveryDate = $timing['first_delivery'];
                                    $earlyArrival = !empty($timing['arrived_early']);
                                    $earlyDays = $timing['early_days'] ?? 0;
                                    $isOverdue = !empty($timing['overdue']);
                                    $overdueDays = $timing['overdue_days'] ?? 0;

                                    $inspectableStatuses = [
                                        \App\Models\Status::PO_PARTIALLY_DELIVERED,
                                        \App\Models\Status::PO_DELIVERED_PENDING_INSPECTION,
                                        \App\Models\Status::PO_CLOSED,
                                    ];
                                    $canInspect = in_array($order->status_id, $inspectableStatuses, true) || $order->inspectionReports->isNotEmpty();

                                    $pendingFulfillment = $order->items->filter(function ($item) {
                                        if ($item->fulfillment_status === 'ordered') return false;
                                        if ($item->fulfillment_status === 'unavailable') {
                                            $waitUntil = $item->employee_wait_until;
                                            return !($waitUntil && $waitUntil->isPast());
                                        }
                                        return true;
                                    });
                                @endphp

                                <article class="rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition-shadow">
                                    {{-- Order Header --}}
                                    <div class="flex flex-wrap items-start justify-between gap-3 px-4 py-3 border-b border-gray-100 bg-white">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h5 class="text-sm font-bold text-gray-900">{{ $order->po_no }}</h5>
                                                @if ($isOverdue)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                                                        <i class="fas fa-clock"></i> Overdue
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-600">
                                                <span class="font-medium">PR {{ $order->pr_no }}</span>
                                                <span class="text-gray-300 mx-1">•</span>
                                                {{ $order->supplier?->supplier_name ?? 'No supplier' }}
                                                <span class="text-gray-300 mx-1">•</span>
                                                Ordered {{ optional($order->order_date)->format('M d, Y') ?? '—' }}
                                            </p>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-md bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700 border border-gray-200">
                                                {{ $order->status?->status_name ?? 'Unknown' }}
                                            </span>
                                            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold">
                                                <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-1.5 py-0.5 text-emerald-700 border border-emerald-100">
                                                    Accept {{ $totals['accepted'] }}/{{ $totals['items'] }}
                                                </span>
                                                @if ($totals['pending'] > 0)
                                                    <span class="inline-flex items-center gap-1 rounded bg-slate-50 px-1.5 py-0.5 text-slate-700 border border-slate-200">
                                                        Pend {{ $totals['pending'] }}
                                                    </span>
                                                @endif
                                                @if ($receivedCount > 0)
                                                    <span class="inline-flex items-center gap-1 rounded bg-sky-50 px-1.5 py-0.5 text-sky-700 border border-sky-100">
                                                        Rec {{ $receivedCount }}/{{ $totals['items'] }}
                                                    </span>
                                                @endif
                                                @if ($totals['issues'] > 0)
                                                    <span class="inline-flex items-center gap-1 rounded bg-rose-50 px-1.5 py-0.5 text-rose-700 border border-rose-100">
                                                        Issue {{ $totals['issues'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Alerts Section --}}
                                    @if ($isOverdue || $earlyArrival || ((int) $order->status_id === \App\Models\Status::PO_SENT_TO_SUPPLIER && $stage['key'] === 'awaiting_delivery'))
                                        <div class="px-5 py-3 space-y-2 border-t border-gray-100 bg-gray-50/20">
                                            @if ($isOverdue && $expectedDelivery)
                                                <div class="flex items-center gap-2 rounded-lg bg-rose-50 border border-rose-100 px-3 py-2 text-xs text-rose-700">
                                                    <i class="fas fa-triangle-exclamation"></i>
                                                    <span>Delivery target {{ $expectedDelivery->format('M d, Y') }} exceeded by {{ $overdueDays }} {{ \Illuminate\Support\Str::plural('day', $overdueDays) }}.</span>
                                                </div>
                                            @endif
                                            @if ($earlyArrival && $firstDeliveryDate)
                                                <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-2 text-xs text-emerald-700">
                                                    <i class="fas fa-truck-fast"></i>
                                                    <span>Items arrived {{ $earlyDays }} {{ \Illuminate\Support\Str::plural('day', $earlyDays) }} early on {{ $firstDeliveryDate->format('M d, Y') }}.</span>
                                                </div>
                                            @endif
                                            @if ((int) $order->status_id === \App\Models\Status::PO_SENT_TO_SUPPLIER && $stage['key'] === 'awaiting_delivery')
                                                <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-2 text-xs text-emerald-700">
                                                    <i class="fas fa-check-double"></i>
                                                    <span>Order accepted and sent to supplier. Awaiting delivery.</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Receiving Progress (for delivery/receiving stages) --}}
                                    @if (in_array($stage['key'], ['awaiting_delivery', 'receiving'], true) && $order->items->isNotEmpty())
                                        <div class="px-5 py-3 border-t border-gray-100">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                                    <i class="fas fa-box-open mr-1"></i> Receiving Progress
                                                </span>
                                                <span class="text-[10px] font-medium text-gray-400">{{ $receivedCount }} of {{ $totals['items'] }} items</span>
                                            </div>
                                            <div class="space-y-2">
                                                @foreach ($order->items as $trackingItem)
                                                    @php
                                                        $trackingReceived = !empty($trackingItem->received_at);
                                                        $trackingReceivable = $trackingItem->fulfillment_status !== 'unavailable';
                                                        $receivedStamp = $trackingItem->received_at ? optional($trackingItem->received_at)->format('M d, Y') : null;
                                                        $receiverName = $trackingItem->receivedBy?->employee
                                                            ? collect([$trackingItem->receivedBy->employee->first_name, $trackingItem->receivedBy->employee->last_name])->filter()->implode(' ')
                                                            : ($trackingItem->receivedBy?->username ?? null);
                                                    @endphp
                                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 bg-gray-50/50 px-3 py-2">
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $trackingItem->item_description }}</p>
                                                            <p class="text-xs text-gray-500">Qty {{ $trackingItem->quantity }} • {{ $trackingItem->unit }} • ₱{{ number_format((float) $trackingItem->unit_cost, 2) }}</p>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            @if ($trackingReceived)
                                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                                                    <i class="fas fa-check"></i> Received
                                                                </span>
                                                            @elseif ($trackingReceivable)
                                                                <form class="js-receive-item" data-receive-url="{{ route('custodian.orders.items.receive', $trackingItem) }}">
                                                                    @csrf
                                                                    <button type="submit" data-no-global-loading="true" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1a3a2d] px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-opacity-90 transition cursor-pointer ">
                                                                        <i class="fas fa-box-open"></i> Mark Received
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <span class="text-[11px] font-medium text-amber-600">Unavailable</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Inspection Info --}}
                                    <div class="px-5 py-3 bg-gray-50/50 border-t border-gray-100">
                                        <div class="flex items-center justify-between text-xs text-gray-600">
                                            <span>
                                                @if ($deliveries['count'] > 0)
                                                    <i class="fas fa-file-circle-check mr-1 text-emerald-600"></i>
                                                    {{ $deliveries['count'] }} IAR{{ $deliveries['count'] === 1 ? '' : 's' }}
                                                    @if ($latestReport)
                                                        • Latest {{ optional($latestReport->inspection_date)->format('M d, Y') }}
                                                    @endif
                                                @else
                                                    <i class="fas fa-circle-dot mr-1 text-gray-400"></i>
                                                    No inspections recorded
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Fulfillment Follow-ups (if applicable) --}}
                                    @if ($stage['key'] === 'awaiting_delivery' && $pendingFulfillment->isNotEmpty())
                                        <details class="border-t border-gray-100">
                                            <summary class="flex cursor-pointer items-center gap-2 px-5 py-3 text-sm font-semibold text-amber-700 bg-amber-50/50 hover:bg-amber-50 transition">
                                                <i class="fas fa-clipboard-list"></i>
                                                Fulfillment follow-ups ({{ $pendingFulfillment->count() }})
                                                <i class="fas fa-chevron-down ml-auto text-xs transition-transform"></i>
                                            </summary>
                                            <div class="px-5 py-4 space-y-3 bg-amber-50/30">
                                                @foreach ($pendingFulfillment as $item)
                                                    @php
                                                        $decisionLabel = match ($item->employee_decision) {
                                                            'accept' => 'Employee accepted the alternative.',
                                                            'wait' => 'Employee opted to wait.',
                                                            default => null,
                                                        };
                                                        $waitUntil = $item->employee_wait_until ? \Illuminate\Support\Carbon::parse($item->employee_wait_until) : null;
                                                        $waitExpired = $waitUntil ? $waitUntil->isPast() : false;
                                                    @endphp
                                                    <div class="rounded-lg border border-amber-200 bg-white p-3 shadow-sm">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div>
                                                                <p class="text-sm font-semibold text-gray-900">{{ $item->item_description }}</p>
                                                                <p class="text-xs text-gray-500 mt-0.5">Qty {{ $item->quantity }} • {{ $item->unit }} • ₱{{ number_format((float) $item->unit_cost, 2) }}</p>
                                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 capitalize">
                                                                        {{ $item->fulfillment_status }}
                                                                    </span>
                                                                    @if ($decisionLabel)
                                                                        <span class="text-[11px] text-sky-700">{{ $decisionLabel }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if ($item->fulfillment_status === 'alternative' && $item->employee_decision === 'wait')
                                                                <button type="button" class="js-retain-original flex-shrink-0 inline-flex items-center gap-1 rounded-lg border border-amber-400 px-2 py-1 text-[11px] font-semibold text-amber-700 hover:bg-amber-500 hover:text-white transition cursor-pointer "
                                                                    data-retain-url="{{ route('custodian.orders.items.retain', $item) }}">
                                                                    <i class="fas fa-undo"></i> Retain
                                                                </button>
                                                            @endif
                                                        </div>
                                                        {{-- Quick update form --}}
                                                        <form class="mt-3 grid gap-2 sm:grid-cols-3 js-update-po-item" data-update-url="{{ route('custodian.orders.items.update', $item) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <select name="fulfillment_status" class="js-item-status rounded-lg border border-gray-200 px-2 py-1.5 text-xs focus:border-[#1a3a2d] focus:outline-none">
                                                                <option value="ordered" @selected($item->fulfillment_status === 'ordered')>Ordered</option>
                                                                <option value="unavailable" @selected($item->fulfillment_status === 'unavailable')>Unavailable</option>
                                                                <option value="alternative" @selected($item->fulfillment_status === 'alternative')>Alternative</option>
                                                            </select>
                                                            <div class="relative">
                                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">₱</span>
                                                                <input type="number" min="0" step="0.01" name="unit_cost" value="{{ $item->unit_cost }}" class="js-item-unit-cost w-full rounded-lg border border-gray-200 pl-5 pr-2 py-1.5 text-xs text-right focus:border-[#1a3a2d] focus:outline-none">
                                                            </div>
                                                            <button type="submit" data-no-global-loading="true" class="rounded-lg bg-[#1a3a2d] px-3 py-1.5 text-xs font-semibold text-white hover:bg-opacity-90 transition cursor-pointer ">
                                                                <i class="fas fa-save mr-1"></i> Save
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="flex flex-wrap items-center justify-end gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50/30">
                                        @if ($stage['key'] === 'awaiting_delivery' && (int) $order->status_id === \App\Models\Status::PO_CREATED)
                                            <button type="button" class="js-accept-po inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-emerald-700 transition cursor-pointer "
                                                data-accept-url="{{ route('custodian.orders.accept', $order) }}">
                                                <i class="fas fa-check"></i> Accept & Send
                                            </button>
                                        @endif
                                        <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer "
                                            data-show-url="{{ route('custodian.orders.show', $order) }}">
                                            <i class="fas fa-eye"></i> View PO
                                        </button>
                                        @if ($canInspect)
                                            <button type="button"
                                                class="js-open-inspection inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90 transition"
                                                data-form-url="{{ route('custodian.inspection.form', $order) }}"
                                                data-store-url="{{ route('custodian.inspection.store', $order) }}">
                                                <i class="fas fa-clipboard-check"></i> Inspect
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Purchase Order Register --}}
    <div id="purchaseOrderRegisterSection" data-po-overview-panel="register" class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-6 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-8 lg:p-10 space-y-6 {{ $activeOverviewTab === 'register' ? '' : 'hidden' }}">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Full Listing</p>
                <h2 class="mt-1 text-xl font-bold text-[#1a3a2d]">Purchase Order Register</h2>
                <p class="mt-1 text-sm text-gray-500">Complete listing with inspection activity snapshots and quick actions.</p>
            </div>
        </div>

        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <form method="GET" class="relative w-full xl:max-w-sm">
                <input type="hidden" name="po_tab" value="register">
                <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search PO number, PR number or supplier..." class="w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-4 py-3 text-sm text-gray-700 shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10 transition" aria-label="Search purchase orders">
            </form>
            <div class="flex flex-wrap items-center gap-2">
                @if ($search)
                    <a href="{{ route('custodian.orders.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 bg-white hover:border-[#1a3a2d]/20 hover:text-[#1a3a2d] hover:bg-[#f7faf8] transition-all">
                        <i class="fas fa-rotate-right text-sm"></i> Reset
                    </a>
                @endif
            </div>
        </div>
        <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#f5f8f6] text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                        <tr>
                            <th class="px-5 py-3 text-left">PO / PR</th>
                            <th class="px-5 py-3 text-left">Supplier</th>
                            <th class="px-5 py-3 text-left">Stage</th>
                            <th class="px-5 py-3 text-left">Items Snapshot</th>
                            <th class="px-5 py-3 text-left">Inspection Summary</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseOrders as $order)
                            @php
                                $registerSummary = $registerSummaries[$order->po_no] ?? [
                                    'accepted_count' => 0,
                                    'issue_count' => 0,
                                    'pending_count' => $order->items->count(),
                                    'deliveries_count' => 0,
                                    'latest_inspection_date' => null,
                                    'can_inspect' => false,
                                ];

                                $stageKey = match ((int) $order->status_id) {
                                    \App\Models\Status::PO_PARTIALLY_DELIVERED => 'receiving',
                                    \App\Models\Status::PO_DELIVERED_PENDING_INSPECTION => 'inspection',
                                    \App\Models\Status::PO_CLOSED => 'completed',
                                    \App\Models\Status::PO_CANCELLED => 'issues',
                                    default => 'awaiting_delivery',
                                };

                                $stageMeta = $stageMetaByKey[$stageKey] ?? null;
                                $stageLabel = $stageMeta['label'] ?? 'Awaiting Delivery';
                                $stageBadge = $stageMeta['badge'] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <tr class="group border-b border-gray-100/90 last:border-0 hover:bg-[#f6fbf8] transition-colors duration-200">
                                <td class="px-5 py-4 align-top">
                                    <span class="block font-bold text-[#1a3a2d]">{{ $order->po_no }}</span>
                                    <span class="mt-1 block text-xs text-gray-500">PR {{ $order->pr_no }}</span>
                                </td>
                                <td class="px-5 py-4 align-top text-gray-700 font-medium">{{ $order->supplier?->supplier_name ?? '—' }}</td>
                                <td class="px-5 py-4 align-top">
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $stageBadge }}">
                                        <i class="fas fa-circle text-[8px]"></i>{{ $stageLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="flex flex-wrap gap-1.5 text-[11px] font-semibold">
                                        <span class="rounded-full bg-emerald-50 border border-emerald-200 px-2.5 py-1 text-emerald-700">Accepted {{ $registerSummary['accepted_count'] }}</span>
                                        <span class="rounded-full bg-slate-50 border border-slate-200 px-2.5 py-1 text-slate-700">Pending {{ $registerSummary['pending_count'] }}</span>
                                        @if ($registerSummary['issue_count'] > 0)
                                            <span class="rounded-full bg-rose-50 border border-rose-200 px-2.5 py-1 text-rose-700">Issues {{ $registerSummary['issue_count'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="text-xs text-gray-600 leading-5">
                                        <div>{{ $registerSummary['deliveries_count'] }} batch{{ $registerSummary['deliveries_count'] === 1 ? '' : 'es' }}</div>
                                        <div class="text-gray-400">Latest {{ $registerSummary['latest_inspection_date'] ?? '—' }}</div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    @php
                                        $statusName = strtolower($order->status?->status_name ?? 'unknown');
                                        $statusColorClasses = 'bg-gray-100 text-gray-700 border-gray-200';
                                        if (str_contains($statusName, 'delivered') || str_contains($statusName, 'closed') || str_contains($statusName, 'complete')) {
                                            $statusColorClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        } elseif (str_contains($statusName, 'cancel')) {
                                            $statusColorClasses = 'bg-rose-50 text-rose-700 border-rose-200';
                                        } elseif (str_contains($statusName, 'partial') || str_contains($statusName, 'pending') || str_contains($statusName, 'inspection')) {
                                            $statusColorClasses = 'bg-amber-50 text-amber-700 border-amber-200';
                                        } elseif (str_contains($statusName, 'sent') || str_contains($statusName, 'created') || str_contains($statusName, 'awaiting')) {
                                            $statusColorClasses = 'bg-sky-50 text-sky-700 border-sky-200';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold capitalize border {{ $statusColorClasses }}">
                                        <i class="fas fa-circle text-[8px]"></i>{{ $order->status?->status_name ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 align-top text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button class="js-view-po inline-flex items-center gap-2 rounded-xl border border-[#1a3a2d]/10 bg-[#f4f8f5] px-4 py-2.5 text-xs font-semibold text-[#1a3a2d] transition-all hover:-translate-y-0.5 hover:border-[#1a3a2d]/20 hover:bg-[#eaf4ee] hover:shadow-md cursor-pointer "
                                            data-show-url="{{ route('custodian.orders.show', $order) }}">
                                            <i class="fas fa-eye"></i>
                                            View
                                        </button>
                                        @if ($registerSummary['can_inspect'])
                                            <button type="button"
                                                class="js-open-inspection inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg"
                                                data-form-url="{{ route('custodian.inspection.form', $order) }}"
                                                data-store-url="{{ route('custodian.inspection.store', $order) }}">
                                                <i class="fas fa-clipboard-check"></i>
                                                Inspect
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center text-gray-500">
                                    <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                                    <p class="font-medium text-lg">No purchase orders matched your filters.</p>
                                    <p class="text-sm">Try adjusting your search terms.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                {{ $purchaseOrders->onEachSide(1)->links('vendor.pagination.procurement') }}
            </div>
        </div>
    </div>
</div>

{{-- PO Details Modal --}}
<div id="poDetailsModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-300" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative bg-white w-full max-w-6xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] overflow-hidden transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex-shrink-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold tracking-tight">Purchase Order Details</h3>
                    <p class="text-sm text-white/80 mt-1" id="poDetailsNumber"></p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all cursor-pointer " data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="bg-white rounded-2xl border-2 border-gray-300 overflow-hidden">
                                                            <!-- PO Header (matching print format) -->
                    <div class="relative border-b-2 border-gray-400 px-6 pt-6 pb-4">
                        <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">APPENDIX 01</span>
                        <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                            <img src="/images/bpi-logo.png" alt="BPI Logo" class="h-16 w-auto object-contain" />
                            <div class="text-center">
                                <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                                <h2 class="mt-2 text-2xl font-black uppercase tracking-[0.35em] text-gray-900">PURCHASE ORDER</h2>
                                <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                                <p class="text-xs italic text-gray-500">(Agency)</p>
                            </div>
                            <img src="/images/pqslogo.png" alt="PQS Logo" class="h-16 w-auto object-contain" />
                        </div>
                    </div>

                    <!-- PO Details Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-b-2 border-gray-400 px-6 py-4 text-sm">
                        <!-- Left Column: Supplier -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-start gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Supplier :</label>
                                <span id="poDetailsSupplier" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block break-words"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-start gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Address :</label>
                                <span id="poDetailsAddress" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block break-words"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-start gap-x-2">
                                <label class="font-semibold whitespace-nowrap">TIN :</label>
                                <span id="poDetailsTin" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block break-words"></span>
                            </div>
                        </div>
                        <!-- Right Column: PO Info -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">P.O. No. :</label>
                                <span id="poDetailsPoNo" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block font-bold text-red-600"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">PR No. :</label>
                                <span id="poDetailsPr" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Date :</label>
                                <span id="poDetailsOrderDate" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Mode of Procurement :</label>
                                <span id="poDetailsMode" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Gentlemen Clause -->
                    <div class="px-6 py-3 text-sm italic text-gray-800 border-b border-gray-200">
                        Gentlemen: Please furnish this Office the following articles subject to the terms and conditions contained herein:
                    </div>

                    <!-- Delivery & Payment Terms -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-b-2 border-gray-400 px-6 py-4 text-sm">
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Place of Delivery <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsPlace" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Date of Delivery <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsDeliveryDate" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Delivery Term <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsDeliveryTerm" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Payment Term <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsPaymentTerm" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-gray-400 border-t-2 border-gray-400">
                                    <th class="border-r border-gray-300 px-4 py-2 text-center font-bold w-24">Item No.</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-center font-bold w-24">Quantity</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-center font-bold w-24">Unit</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-left font-bold">Description</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-left font-bold">Status</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-right font-bold w-32">Unit Cost</th>
                                    <th class="px-4 py-2 text-right font-bold w-32">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="poDetailsItems" class="align-top divide-y divide-dotted divide-gray-300">
                                <!-- Items will go here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Signatures & Fund Details -->
                    <div class="border-t-2 border-gray-400">
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-4 border-b border-gray-400 px-6 py-3 text-sm">
                            <label class="font-semibold text-gray-700">(Total Amount in Words)</label>
                            <div class="w-full border-b border-dotted border-gray-400 bg-transparent px-2 text-sm font-medium text-gray-900 h-5" id="poDetailsAmountWords"></div>
                        </div>

                        <div class="px-6 py-4 text-[11px] italic text-gray-600 border-b border-gray-400">
                            In case of failure to make the full delivery within the time specified above, a penalty of one-tenth (1/10) of one percent for every day of delay shall be imposed on the undelivered item/s.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-b border-gray-400 px-6 pt-4 pb-8 text-sm">
                            <div>
                                <p class="font-semibold mb-3 text-gray-800">Conforme:</p>
                                <div class="mb-6 mt-8">
                                    <div class="w-full border-b border-gray-500 text-center text-sm font-semibold h-6 text-gray-900" id="poDetailsConformeName"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Signature over Printed Name of Supplier</label>
                                </div>
                                <div class="mb-3">
                                    <div class="w-full border-b border-gray-500 text-center text-sm h-6 text-gray-900" id="poDetailsConformeDate"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Date</label>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold mb-3 text-gray-800">Very truly yours,</p>
                                <div class="mb-6 mt-8">
                                    <div class="w-full border-b border-gray-500 text-center text-sm font-semibold h-6 text-gray-900" id="poDetailsAuthorizedOfficial"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Signature over Printed Name of Authorized Official</label>
                                </div>
                                <div class="mb-3">
                                    <div class="w-full border-b border-gray-500 text-center text-sm font-semibold h-6 text-gray-900" id="poDetailsDesignation"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Designation</label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 text-sm bg-gray-50/50">
                            <div class="space-y-3 border-r border-gray-400 p-4">
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label class="font-semibold text-gray-700">Fund Cluster :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900" id="poDetailsFundCluster"></div>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label class="font-semibold text-gray-700">Funds Available :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900" id="poDetailsFundsAvailable"></div>
                                </div>
                                <div class="pt-8">
                                    <div class="w-full border-b border-gray-500 h-6"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Signature over Printed Name of Chief Accountant/Head of Accounting Division/Unit</label>
                                </div>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2 w-full">
                                    <label class="font-semibold text-gray-700 whitespace-nowrap">ORS/BURS No. :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900 min-w-[100px]" id="poDetailsOrsBursNo"></div>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2 w-full">
                                    <label class="font-semibold text-gray-700 whitespace-nowrap">Date of the ORS/BURS :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900 min-w-[100px]" id="poDetailsOrsBursDate"></div>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2 w-full">
                                    <label class="font-semibold text-gray-700 whitespace-nowrap">Amount :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900 min-w-[100px]" id="poDetailsOrsBursAmount"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status History Section -->
                <div class="mx-auto max-w-5xl mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h4 class="font-bold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i>
                            Status History Log
                        </h4>
                    </div>
                    <div id="poStatusHistory" class="p-4 max-h-64 overflow-y-auto text-sm bg-white">
                        <!-- Status history items will be populated here -->  
                    </div>
                </div>
            <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                <button class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm cursor-pointer " data-close-modal>
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

@include('custodian.purchase_requests.partials.review-modal', [
    'statuses' => $statuses,
    'canEditFunds' => $canEditFunds,
    'remarksRequiredStatuses' => $remarksRequiredStatuses,
    'workflowConfig' => $workflowConfig,
    'currentUserName' => $currentUserName,
    'workflowRole' => $workflowRole,
    'routes' => $routes,
    'routeParam' => $routeParam,
    'systemManagedStatuses' => $systemManagedStatuses,
])

@include('custodian.inspection._modal')
@endsection

@push('scripts')
@vite('resources/js/custodian-purchase-requests.js')
@vite('resources/js/purchase-orders.js')
@vite('resources/js/inspection.js')
<script>
(() => {
    const queryKey = 'po_tab';
    const tabs = Array.from(document.querySelectorAll('[data-po-overview-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-po-overview-panel]'));
    const label = document.querySelector('[data-po-overview-label]');

    if (!tabs.length || !panels.length) {
        return;
    }

    const availablePanels = new Set(panels.map((panel) => String(panel.dataset.poOverviewPanel || '').trim()).filter(Boolean));

    const getTabFromUrl = () => {
        const params = new URLSearchParams(window.location.search);
        const fromQuery = String(params.get(queryKey) || '').trim();
        if (fromQuery && availablePanels.has(fromQuery)) {
            return fromQuery;
        }

        const currentActive = tabs.find((tab) => tab.classList.contains('bg-[#1a3a2d]'));
        const fallback = String(currentActive?.dataset.poOverviewTab || '').trim();
        if (fallback && availablePanels.has(fallback)) {
            return fallback;
        }

        return availablePanels.has('ready') ? 'ready' : (Array.from(availablePanels)[0] || null);
    };

    const animatePanel = (panel, mode = 'show') => {
        panel.getAnimations().forEach((animation) => animation.cancel());

        if (mode === 'show') {
            panel.classList.remove('hidden');
            panel.classList.remove('pointer-events-none');
            panel.animate(
                [
                    { opacity: 0, transform: 'translateY(6px)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ],
                { duration: 190, easing: 'ease-out', fill: 'both' }
            );
            return;
        }

        panel.classList.add('pointer-events-none');
        const animation = panel.animate(
            [
                { opacity: 1, transform: 'translateY(0)' },
                { opacity: 0, transform: 'translateY(4px)' },
            ],
            { duration: 140, easing: 'ease-in', fill: 'both' }
        );

        animation.onfinish = () => {
            panel.classList.add('hidden');
            panel.classList.remove('pointer-events-none');
            panel.style.opacity = '';
            panel.style.transform = '';
        };
    };

    const activateOverviewTab = (tabKey, options = {}) => {
        if (!tabKey || !availablePanels.has(tabKey)) {
            return;
        }

        const { syncUrl = false, replaceState = false } = options;
        tabs.forEach((tab) => {
            const isActive = String(tab.dataset.poOverviewTab) === String(tabKey);
            const activeClass = String(tab.dataset.activeClass || '').trim();
            const inactiveClass = String(tab.dataset.inactiveClass || '').trim();
            const activeCountClass = String(tab.dataset.countActiveClass || '').trim();
            const inactiveCountClass = String(tab.dataset.countInactiveClass || '').trim();
            const count = tab.querySelector('.po-overview-tab-count');

            tab.classList.remove('is-selected');
            if (activeClass) tab.classList.remove(...activeClass.split(/\s+/));
            if (inactiveClass) tab.classList.remove(...inactiveClass.split(/\s+/));

            if (count) {
                if (activeCountClass) count.classList.remove(...activeCountClass.split(/\s+/));
                if (inactiveCountClass) count.classList.remove(...inactiveCountClass.split(/\s+/));
            }

            if (isActive) {
                tab.classList.add('is-selected');
                if (activeClass) tab.classList.add(...activeClass.split(/\s+/));
                if (count && activeCountClass) count.classList.add(...activeCountClass.split(/\s+/));
                if (label) {
                    const tabText = tab.querySelector('span')?.textContent?.trim();
                    if (tabText) {
                        label.textContent = tabText;
                    }
                }
            } else {
                if (inactiveClass) tab.classList.add(...inactiveClass.split(/\s+/));
                if (count && inactiveCountClass) count.classList.add(...inactiveCountClass.split(/\s+/));
            }
        });

        panels.forEach((panel) => {
            const isActivePanel = String(panel.dataset.poOverviewPanel) === String(tabKey);
            if (isActivePanel) {
                animatePanel(panel, 'show');
            } else if (!panel.classList.contains('hidden')) {
                animatePanel(panel, 'hide');
            }
        });

        if (syncUrl) {
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set(queryKey, tabKey);

            if (replaceState) {
                window.history.replaceState({ poTab: tabKey }, '', nextUrl);
            } else {
                window.history.pushState({ poTab: tabKey }, '', nextUrl);
            }
        }
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            activateOverviewTab(String(tab.dataset.poOverviewTab || '').trim(), { syncUrl: true });
        });
    });

    activateOverviewTab(getTabFromUrl(), { syncUrl: true, replaceState: true });

    if (!window.__purchaseOrdersOverviewTabPopstateBound) {
        window.addEventListener('popstate', () => {
            activateOverviewTab(getTabFromUrl(), { syncUrl: false });
        });
        window.__purchaseOrdersOverviewTabPopstateBound = true;
    }
})();

(() => {
    // Tab switching logic for pill-style pipeline tabs
    const queryKey = 'po_stage';
    const tabs = Array.from(document.querySelectorAll('.pipeline-tab'));
    const panels = Array.from(document.querySelectorAll('.pipeline-panel'));

    if (!tabs.length || !panels.length) {
        return;
    }

    const activeClasses = ['bg-[#1a3a2d]', 'text-white', 'shadow-lg', 'shadow-[#1a3a2d]/30', 'border-[#1a3a2d]'];
    const inactiveClasses = ['border-gray-200', 'bg-[#fbfcfb]', 'text-gray-600', 'hover:bg-gray-100', 'hover:border-gray-300'];
    const knownStages = new Set(tabs.map((tab) => String(tab.dataset.tab || '').trim()).filter(Boolean));

    const getStageFromUrl = () => {
        const params = new URLSearchParams(window.location.search);
        const stageFromQuery = String(params.get(queryKey) || '').trim();
        if (stageFromQuery && knownStages.has(stageFromQuery)) {
            return stageFromQuery;
        }

        const current = tabs.find((tab) => tab.classList.contains('bg-[#1a3a2d]'));
        const fallback = String(current?.dataset.tab || '').trim();
        if (fallback && knownStages.has(fallback)) {
            return fallback;
        }

        return tabs[0] ? String(tabs[0].dataset.tab || '').trim() : null;
    };

    const animatePanel = (panel, mode = 'show') => {
        panel.getAnimations().forEach((animation) => animation.cancel());

        if (mode === 'show') {
            panel.classList.remove('hidden');
            panel.classList.remove('pointer-events-none');
            panel.animate(
                [
                    { opacity: 0, transform: 'translateY(5px)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ],
                { duration: 180, easing: 'ease-out', fill: 'both' }
            );
            return;
        }

        panel.classList.add('pointer-events-none');
        const animation = panel.animate(
            [
                { opacity: 1, transform: 'translateY(0)' },
                { opacity: 0, transform: 'translateY(4px)' },
            ],
            { duration: 130, easing: 'ease-in', fill: 'both' }
        );

        animation.onfinish = () => {
            panel.classList.add('hidden');
            panel.classList.remove('pointer-events-none');
            panel.style.opacity = '';
            panel.style.transform = '';
        };
    };

    const activatePipelineStage = (stageKey, options = {}) => {
        if (!stageKey || !knownStages.has(stageKey)) {
            return;
        }

        const { syncUrl = false, replaceState = false } = options;

        tabs.forEach((tab) => {
            const isActive = String(tab.dataset.tab) === String(stageKey);

            tab.classList.remove(...activeClasses);
            tab.classList.add(...inactiveClasses);
            const badge = tab.querySelector('span:last-child');
            if (badge) {
                badge.classList.remove('bg-white/20', 'text-white');
            }

            if (isActive) {
                tab.classList.remove(...inactiveClasses);
                tab.classList.add(...activeClasses);
                if (badge) {
                    badge.classList.add('bg-white/20', 'text-white');
                }
            }
        });

        panels.forEach((panel) => {
            if (panel.dataset.panel === stageKey) {
                animatePanel(panel, 'show');
            } else if (!panel.classList.contains('hidden')) {
                animatePanel(panel, 'hide');
            }
        });

        if (syncUrl) {
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set(queryKey, stageKey);
            if (replaceState) {
                window.history.replaceState({ poStage: stageKey }, '', nextUrl);
            } else {
                window.history.pushState({ poStage: stageKey }, '', nextUrl);
            }
        }
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', function () {
            activatePipelineStage(String(this.dataset.tab || '').trim(), { syncUrl: true });
        });
    });

    activatePipelineStage(getStageFromUrl(), { syncUrl: true, replaceState: true });

    if (!window.__purchaseOrdersPipelineStagePopstateBound) {
        window.addEventListener('popstate', () => {
            activatePipelineStage(getStageFromUrl(), { syncUrl: false });
        });
        window.__purchaseOrdersPipelineStagePopstateBound = true;
    }
})();

(() => {
    const modal = document.getElementById('custodianPrModal');
    if (!modal) {
        return;
    }

    const panel = modal.querySelector('.modal-panel');
    const formatCurrency = (value) => {
        const number = Number(value ?? 0);
        return `₱${number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };
    const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[char] || char));
    const formatDateTime = (value) => {
        if (!value) {
            return '—';
        }
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const openModal = () => {
        modal.classList.remove('hidden', 'opacity-0');
        requestAnimationFrame(() => {
            panel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
        });
    };

    const closeModal = () => {
        modal.classList.add('opacity-0');
        panel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    modal.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });

    const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) {
            el.textContent = value ?? '';
        }
    };

    const setValue = (id, value) => {
        const el = document.getElementById(id);
        if (el) {
            el.value = value ?? '';
        }
    };

    const showRequestInModal = async (button) => {
        const showUrl = button?.getAttribute('data-show-url');
        if (!showUrl) {
            return;
        }

        openModal();
        setText('custodianPrNumber', 'Loading purchase request...');
        const itemsTable = document.getElementById('custodianPrItemsTable');
        if (itemsTable) {
            itemsTable.innerHTML = '<tr><td colspan="8" class="border border-gray-500 px-4 py-8 text-center text-gray-500">Loading...</td></tr>';
        }

        try {
            const response = await fetch(showUrl, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Request failed (${response.status})`);
            }

            const payload = await response.json();
            const data = payload?.data || {};

            setText('custodianPrNumber', data.pr_no || 'Purchase Request Review');
            setText('custodianPrNo', data.pr_no || '—');
            setText('custodianPrDate', formatDateTime(data.created_at));
            setText('custodianPrDivisionInput', data.division || '—');
            setText('custodianPrSectionInput', data.section || '—');
            setText('custodianPrSaiInput', data.sai_no || '—');
            setText('custodianPrAlobsInput', data.alobs_no || '—');
            setText('custodianPrFundClusterInput', data.fund_cluster || '—');
            setText('custodianPrFundsAvailableInput', data.funds_available ? formatCurrency(data.funds_available) : '—');
            setValue('custodianPrPurposeTextarea', data.purpose || '');
            setText('custodianPrRequestedPrintedName', data.requester || '');
            setText('custodianPrApprovedPrintedName', data.approved_by || '');

            const recommendedBlock = document.getElementById('custodianPrRecommendedBlock');
            if (data.recommended_by_name) {
                setText('custodianPrRecommendedPrintedName', data.recommended_by_name);
                setText('custodianPrRecommendedDate', formatDateTime(data.recommended_at));
                setText('custodianPrRecommendationRemarks', data.recommendation_remarks || '—');
                recommendedBlock?.classList.remove('hidden');
            } else {
                setText('custodianPrRecommendedPrintedName', '');
                setText('custodianPrRecommendedDate', '');
                setText('custodianPrRecommendationRemarks', '');
                recommendedBlock?.classList.add('hidden');
            }

            const items = Array.isArray(data.items) ? data.items : [];
            if (itemsTable) {
                if (!items.length) {
                    itemsTable.innerHTML = '<tr><td colspan="8" class="border border-gray-500 px-4 py-8 text-center text-gray-500">No items found.</td></tr>';
                } else {
                    itemsTable.innerHTML = items.map((item) => `
                        <tr>
                            <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.quantity ?? '')}</td>
                            <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.unit ?? '')}</td>
                            <td class="border border-gray-500 px-3 py-2">${escapeHtml(item.item_description ?? '')}</td>
                            <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.stock_number ?? '')}</td>
                            <td class="border border-gray-500 px-3 py-2 text-right">${formatCurrency(item.estimated_unit_cost ?? 0)}</td>
                            <td class="border border-gray-500 px-3 py-2 text-right">${formatCurrency(item.estimated_total_cost ?? 0)}</td>
                            <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.fulfillment_status ?? 'pending')}</td>
                            <td class="border border-gray-500 px-3 py-2 text-center">—</td>
                        </tr>
                    `).join('');
                }
            }

            setText('custodianPrGrandTotal', formatCurrency(data.total_estimated_cost || 0));
        } catch (error) {
            if (itemsTable) {
                itemsTable.innerHTML = `<tr><td colspan="8" class="border border-gray-500 px-4 py-8 text-center text-rose-600">${escapeHtml(error?.message || 'Unable to load purchase request details.')}</td></tr>`;
            }
        }
    };

    window.__openCustodianRequestModal = showRequestInModal;

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.js-open-pr-modal');
        if (!trigger) {
            return;
        }
        event.preventDefault();
        showRequestInModal(trigger);
    });
})();
</script>
@endpush
