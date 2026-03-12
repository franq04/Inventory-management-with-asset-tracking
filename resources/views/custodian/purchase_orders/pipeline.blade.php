@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="animate-card flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Purchase Orders</h2>
        <p class="mt-1 text-gray-500">Monitor deliveries from supplier coordination through inspection and acceptance.</p>
    </div>
    <a href="{{ route('custodian.orders.create') }}" class="bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center gap-2">
        <i class="fas fa-file-invoice-dollar"></i>
        Generate Purchase Order
    </a>
</div>

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

    $stageMetaByKey = collect($pipelineStages ?? [])->keyBy('key');
    $currentUserRole = auth()->user()->role ?? null;
@endphp

{{-- Ready for PO Section --}}
@if ($approvedRequests->isNotEmpty())
<section class="mt-8">
    <div class="rounded-2xl bg-white shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 pt-6 pb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Ready for Purchase Ordering</h3>
                <p class="text-xs text-gray-500">Approved requests with funding clearance and no associated purchase order yet.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                <i class="fa-solid fa-file-circle-check"></i>
                {{ $approvedRequests->count() }} request{{ $approvedRequests->count() === 1 ? '' : 's' }}
            </span>
        </div>
        <div class="overflow-x-auto px-6 pb-6">
            <table class="min-w-full rounded-xl border text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-left">PR No.</th>
                        <th class="px-4 py-3 text-left">Purpose</th>
                        <th class="px-4 py-3 text-left">Requested By</th>
                        <th class="px-4 py-3 text-left">Division / Section</th>
                        <th class="px-4 py-3 text-right">Submitted</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($approvedRequests as $request)
                        <tr class="border-b last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 font-semibold text-gray-900">{{ $request->pr_no }}</td>
                            <td class="px-4 py-4 text-gray-600">{{ \Illuminate\Support\Str::limit($request->purpose, 80) }}</td>
                            <td class="px-4 py-4 text-gray-700">{{ $formatRequesterName($request->requester) }}</td>
                            <td class="px-4 py-4 text-gray-600">
                                {{ $request->division?->division_name ?? '—' }}<br>
                                <span class="text-xs text-gray-500">{{ $request->section?->section_name ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-4 text-right text-gray-500">{{ optional($request->created_at)->format('M d, Y') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="js-view-custodian-pr inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                        data-show-url="{{ route('custodian.requests.show', ['purchase_request' => $request]) }}"
                                        data-update-url="{{ route('custodian.requests.update-status', ['purchase_request' => $request]) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </button>
                                    <a href="{{ route('custodian.orders.create', ['pr' => $request->pr_no]) }}" class="inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90">
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
    </div>
</section>
@endif

{{-- Pipeline Section with Tabs --}}
<section class="mt-8">
    <div class="rounded-2xl bg-white shadow-lg overflow-hidden">
        {{-- Tab Navigation --}}
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Pipeline</h3>
            <div class="flex flex-wrap gap-1 border-b border-gray-200">
                @foreach ($pipelineStages as $idx => $stage)
                    @php
                        $tabIcon = $tabIcons[$stage['key']] ?? 'fa-folder';
                        $isActive = $idx === 0;
                    @endphp
                    <button type="button"
                        class="pipeline-tab group relative flex items-center gap-2 px-4 py-3 text-sm font-semibold transition-all rounded-t-lg -mb-px {{ $isActive ? 'border-2 border-b-white border-gray-200 bg-white text-[#1a3a2d]' : 'border-2 border-transparent text-gray-500 hover:text-gray-700 hover:bg-white/50' }}"
                        data-tab="{{ $stage['key'] }}">
                        <i class="fas {{ $tabIcon }} text-xs"></i>
                        <span class="hidden sm:inline">{{ $stage['label'] }}</span>
                        <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] rounded-full text-xs font-bold {{ $stage['badge'] }}">
                            {{ $stage['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="px-6 py-6">
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
                                    <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-4 border-b border-gray-100">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h5 class="text-lg font-bold text-gray-900">{{ $order->po_no }}</h5>
                                                @if ($isOverdue)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                                                        <i class="fas fa-clock"></i> Overdue
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 mt-0.5">
                                                <span class="font-medium">PR {{ $order->pr_no }}</span>
                                                <span class="text-gray-400 mx-1">•</span>
                                                {{ $order->supplier?->supplier_name ?? 'No supplier' }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Ordered {{ optional($order->order_date)->format('M d, Y') ?? '—' }}
                                                @if ($order->delivery_date)
                                                    <span class="text-gray-400 mx-1">•</span>
                                                    Target {{ optional($order->delivery_date)->format('M d, Y') }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                            {{ $order->status?->status_name ?? 'Unknown' }}
                                        </span>
                                    </div>

                                    {{-- Order Stats --}}
                                    <div class="px-5 py-3 bg-gray-50/50">
                                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">
                                                <i class="fas fa-check-circle text-[10px]"></i>
                                                Accepted {{ $totals['accepted'] }}/{{ $totals['items'] }}
                                            </span>
                                            @if ($totals['pending'] > 0)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                                    <i class="fas fa-hourglass-half text-[10px]"></i>
                                                    Pending {{ $totals['pending'] }}
                                                </span>
                                            @endif
                                            @if ($receivedCount > 0)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 px-3 py-1 text-sky-700">
                                                    <i class="fas fa-box-open text-[10px]"></i>
                                                    Received {{ $receivedCount }}/{{ $totals['items'] }}
                                                </span>
                                            @endif
                                            @if ($totals['issues'] > 0)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-3 py-1 text-rose-700">
                                                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                                    Issues {{ $totals['issues'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Alerts Section --}}
                                    @if ($isOverdue || $earlyArrival || ((int) $order->status_id === \App\Models\Status::PO_SENT_TO_SUPPLIER && $stage['key'] === 'awaiting_delivery'))
                                        <div class="px-5 py-3 space-y-2 border-t border-gray-100">
                                            @if ($isOverdue && $expectedDelivery)
                                                <div class="flex items-start gap-2 rounded-lg bg-rose-50 border border-rose-200 px-3 py-2 text-xs text-rose-700">
                                                    <i class="fas fa-triangle-exclamation mt-0.5"></i>
                                                    <span>Delivery target {{ $expectedDelivery->format('M d, Y') }} exceeded by {{ $overdueDays }} {{ \Illuminate\Support\Str::plural('day', $overdueDays) }}.</span>
                                                </div>
                                            @endif
                                            @if ($earlyArrival && $firstDeliveryDate)
                                                <div class="flex items-start gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs text-emerald-700">
                                                    <i class="fas fa-truck-fast mt-0.5"></i>
                                                    <span>Items arrived {{ $earlyDays }} {{ \Illuminate\Support\Str::plural('day', $earlyDays) }} early on {{ $firstDeliveryDate->format('M d, Y') }}.</span>
                                                </div>
                                            @endif
                                            @if ((int) $order->status_id === \App\Models\Status::PO_SENT_TO_SUPPLIER && $stage['key'] === 'awaiting_delivery')
                                                <div class="flex items-start gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs text-emerald-700">
                                                    <i class="fas fa-check-double mt-0.5"></i>
                                                    <span>Order accepted and sent to supplier. Awaiting delivery.</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Receiving Progress (for delivery/receiving stages) --}}
                                    @if (in_array($stage['key'], ['awaiting_delivery', 'receiving'], true) && $order->items->isNotEmpty())
                                        <div class="px-5 py-4 border-t border-gray-100">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                                                    <i class="fas fa-box-open mr-1 text-[#1a3a2d]"></i> Receiving Progress
                                                </span>
                                                <span class="text-xs text-gray-500">{{ $receivedCount }} of {{ $totals['items'] }} items</span>
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
                                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-[#1a3a2d] px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-opacity-90 transition">
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
                                                                <button type="button" class="js-retain-original flex-shrink-0 inline-flex items-center gap-1 rounded-lg border border-amber-400 px-2 py-1 text-[11px] font-semibold text-amber-700 hover:bg-amber-500 hover:text-white transition"
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
                                                            <button type="submit" class="rounded-lg bg-[#1a3a2d] px-3 py-1.5 text-xs font-semibold text-white hover:bg-opacity-90 transition">
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
                                            <button type="button" class="js-accept-po inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-emerald-700 transition"
                                                data-accept-url="{{ route('custodian.orders.accept', $order) }}">
                                                <i class="fas fa-check"></i> Accept & Send
                                            </button>
                                        @endif
                                        <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
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
</section>

{{-- Purchase Order Register --}}
<section class="mt-8">
    <div class="rounded-2xl bg-white shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 pt-6 pb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Purchase Order Register</h3>
                <p class="text-xs text-gray-500">Complete listing with inspection activity snapshots and quick actions.</p>
            </div>
        </div>
        <div class="px-6 pb-6">
            <form method="GET" class="mb-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-[minmax(0,1fr)_auto]">
                <div>
                    <label for="poSearch" class="mb-1 block text-xs font-semibold text-gray-600">Search</label>
                    <input type="text" id="poSearch" name="search" value="{{ $search }}" placeholder="PO number, PR number or supplier" class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]">
                </div>
                <div class="flex items-end justify-end gap-2">
                    <a href="{{ route('custodian.orders.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-200">Reset</a>
                    <button type="submit" class="rounded-lg bg-[#1a3a2d] px-4 py-2 font-semibold text-white transition hover:bg-opacity-90">Apply</button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">PO / PR</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-left">Stage</th>
                            <th class="px-4 py-3 text-left">Items Snapshot</th>
                            <th class="px-4 py-3 text-left">Inspection Summary</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
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
                            <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-900">{{ $order->po_no }}</div>
                                    <div class="text-xs text-gray-500">PR {{ $order->pr_no }}</div>
                                </td>
                                <td class="px-4 py-4 text-gray-700">{{ $order->supplier?->supplier_name ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $stageBadge }}">
                                        <i class="fas fa-circle text-[8px]"></i>{{ $stageLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-gray-700">
                                    <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700">Accepted {{ $registerSummary['accepted_count'] }}</span>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">Pending {{ $registerSummary['pending_count'] }}</span>
                                        @if ($registerSummary['issue_count'] > 0)
                                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-rose-700">Issues {{ $registerSummary['issue_count'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-700">
                                    <div class="text-xs text-gray-600">
                                        <div>{{ $registerSummary['deliveries_count'] }} batch{{ $registerSummary['deliveries_count'] === 1 ? '' : 'es' }}</div>
                                        <div>Latest {{ $registerSummary['latest_inspection_date'] ?? '—' }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-700">{{ $order->status?->status_name ?? '—' }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                            data-show-url="{{ route('custodian.orders.show', $order) }}">
                                            <i class="fas fa-eye"></i>
                                            View
                                        </button>
                                        @if ($registerSummary['can_inspect'])
                                            <button type="button"
                                                class="js-open-inspection inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-3 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90"
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
                                <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                    <i class="fas fa-clipboard-list text-3xl text-gray-300 mb-3"></i>
                                    <p>No purchase orders matched your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>
</section>

{{-- PO Details Modal --}}
<div id="poDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] overflow-hidden">
            <div class="flex-shrink-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold tracking-tight">Purchase Order Details</h3>
                    <p class="text-sm text-white/80 mt-1" id="poDetailsNumber"></p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border-2 border-gray-200 bg-white px-4 py-3 shadow-sm">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">PR Reference</span>
                        <span id="poDetailsPr" class="block text-sm font-bold text-gray-900 border-b border-dotted border-gray-300 pb-1"></span>
                    </div>
                    <div class="rounded-xl border-2 border-gray-200 bg-white px-4 py-3 shadow-sm">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">Order Date</span>
                        <span id="poDetailsOrderDate" class="block text-sm font-bold text-gray-900 border-b border-dotted border-gray-300 pb-1"></span>
                    </div>
                    <div class="rounded-xl border-2 border-gray-200 bg-white px-4 py-3 shadow-sm">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">Supplier</span>
                        <span id="poDetailsSupplier" class="block text-sm font-bold text-gray-900 border-b border-dotted border-gray-300 pb-1"></span>
                    </div>
                    <div class="rounded-xl border-2 border-gray-200 bg-white px-4 py-3 shadow-sm">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">Delivery Date</span>
                        <span id="poDetailsDeliveryDate" class="block text-sm font-bold text-gray-900 border-b border-dotted border-gray-300 pb-1"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border-2 border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-100 to-gray-200 px-5 py-3 border-b-2 border-gray-300">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            Order Items
                        </h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 text-xs uppercase text-gray-600 border-b-2 border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold">Description</th>
                                    <th class="px-4 py-3 text-center font-bold">Quantity</th>
                                    <th class="px-4 py-3 text-center font-bold">Unit</th>
                                    <th class="px-4 py-3 text-left font-bold">Status</th>
                                    <th class="px-4 py-3 text-left font-bold">Alternate Item</th>
                                    <th class="px-4 py-3 text-right font-bold">Unit Cost</th>
                                    <th class="px-4 py-3 text-right font-bold">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody id="poDetailsItems" class="divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Status History Section -->
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-gray-100 to-gray-50 border-b border-gray-200">
                        <h4 class="font-bold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i>
                            Status History Log
                        </h4>
                    </div>
                    <div id="poStatusHistory" class="p-4 max-h-64 overflow-y-auto">
                        <!-- Status history items will be populated here -->
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                <button class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
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
@vite('resources/js/purchase-orders.js')
@vite('resources/js/inspection.js')
<script>
(() => {
    // Tab switching logic
    const tabs = document.querySelectorAll('.pipeline-tab');
    const panels = document.querySelectorAll('.pipeline-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetPanel = this.dataset.tab;

            // Update tab states
            tabs.forEach(t => {
                t.classList.remove('border-b-white', 'border-gray-200', 'bg-white', 'text-[#1a3a2d]');
                t.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-b-white', 'border-gray-200', 'bg-white', 'text-[#1a3a2d]');

            // Update panel visibility
            panels.forEach(p => {
                if (p.dataset.panel === targetPanel) {
                    p.classList.remove('hidden');
                } else {
                    p.classList.add('hidden');
                }
            });
        });
    });
})();
</script>
@endpush
