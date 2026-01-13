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
        'awaiting_delivery' => 'border-sky-100 bg-sky-50/80',
        'receiving' => 'border-amber-100 bg-amber-50/70',
        'inspection' => 'border-emerald-100 bg-emerald-50/70',
        'issues' => 'border-rose-100 bg-rose-50/70',
        'completed' => 'border-slate-100 bg-slate-50/80',
    ];

    $stageMetaByKey = collect($pipelineStages ?? [])->keyBy('key');
    $currentUserRole = auth()->user()->role ?? null;
@endphp

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

<section class="mt-8">
    <div class="rounded-2xl bg-white shadow-lg">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 pt-6 pb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Delivery → Receiving → Inspection Pipeline</h3>
                <p class="text-xs text-gray-500">Visualise every purchase order’s progress from supplier coordination to IAR completion and highlight pending issues.</p>
            </div>
        </div>
        <div class="px-6 pb-6">
            <div class="relative rounded-2xl border border-gray-100 bg-gray-50/60 p-4">
                <div class="pointer-events-none absolute inset-x-6 top-4 h-6 rounded-full bg-gradient-to-b from-gray-50 via-gray-50/80 to-transparent z-10"></div>
                <div class="pointer-events-none absolute inset-x-6 bottom-4 h-8 rounded-full bg-gradient-to-t from-gray-50 via-gray-50/80 to-transparent z-10"></div>
                <div id="purchaseOrderPipeline" class="relative z-0 grid max-h-[70vh] gap-4 overflow-y-auto overflow-x-hidden pr-3 lg:grid-cols-2 xl:grid-cols-3">
                    @foreach ($pipelineStages as $stage)
                    @php
                        $accent = $stageAccents[$stage['key']] ?? 'border-gray-100 bg-gray-50';
                        $overdueCount = $stage['orders']->filter(function ($summary) {
                            return !empty($summary['timing']['overdue']);
                        })->count();
                        $earlyCount = $stage['orders']->filter(function ($summary) {
                            return !empty($summary['timing']['arrived_early']);
                        })->count();
                    @endphp
                    <div class="rounded-2xl border {{ $accent }} p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3 border-b border-white/60 pb-3">
                            <div>
                                <h4 class="text-base font-semibold text-gray-900">{{ $stage['label'] }}</h4>
                                <p class="text-xs text-gray-600 leading-relaxed">{{ $stage['description'] }}</p>
                            </div>
                            <span class="inline-flex min-w-[44px] items-center justify-center rounded-full px-3 py-1 text-xs font-bold {{ $stage['badge'] }}">
                                {{ $stage['count'] }}
                            </span>
                        </div>
                        @if ($overdueCount || $earlyCount)
                            <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold">
                                @if ($overdueCount)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-rose-700">
                                        <i class="fas fa-clock"></i>Overdue {{ $overdueCount }}
                                    </span>
                                @endif
                                @if ($earlyCount)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-emerald-700">
                                        <i class="fas fa-truck-fast"></i>Arrived early {{ $earlyCount }}
                                    </span>
                                @endif
                            </div>
                        @endif
                        <div class="mt-4 space-y-4 @if(in_array($stage['key'], ['awaiting_delivery','receiving','inspection','completed'], true)) max-h-[48vh] overflow-y-auto pr-3 @endif">
                            @forelse ($stage['orders'] as $summary)
                                @php
                                    /** @var \App\Models\PurchaseOrder $order */
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
                                    $inspectLabel = $deliveries['count'] > 0 ? 'Update Inspection' : 'Record Inspection';

                                    $pendingFulfillment = $order->items->filter(function ($item) {
                                        if ($item->fulfillment_status === 'ordered') {
                                            return false;
                                        }

                                        if ($item->fulfillment_status === 'unavailable') {
                                            $waitUntil = $item->employee_wait_until;
                                            return !($waitUntil && $waitUntil->isPast());
                                        }

                                        return true;
                                    });
                                @endphp
                                <article class="rounded-xl border border-gray-200/80 bg-white px-4 py-4 shadow-sm transition hover:shadow-md">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h5 class="text-lg font-semibold text-gray-900">PO {{ $order->po_no }}</h5>
                                            <div class="mt-1 text-xs text-gray-600">
                                                <span class="font-semibold text-gray-700">PR {{ $order->pr_no }}</span>
                                                • {{ $order->supplier?->supplier_name ?? 'No supplier recorded' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Ordered {{ optional($order->order_date)->format('M d, Y') ?? '—' }}
                                                @if ($order->delivery_date)
                                                    • Delivery target {{ optional($order->delivery_date)->format('M d, Y') }}
                                                @endif
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                            <i class="fas fa-circle text-[8px] text-[#1a3a2d]"></i>
                                            {{ $order->status?->status_name ?? 'Status unknown' }}
                                        </span>
                                    </div>

                                    @if ($isOverdue && $expectedDelivery)
                                        <div class="mt-3 flex items-start gap-2 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                                            <i class="fas fa-triangle-exclamation mt-0.5"></i>
                                            <span>
                                                Delivery target {{ $expectedDelivery->format('M d, Y') }} has been exceeded
                                                @if ($overdueDays > 0)
                                                    by {{ $overdueDays }} {{ \Illuminate\Support\Str::plural('day', $overdueDays) }}
                                                @endif
                                                @if (! $firstDeliveryDate)
                                                    . Awaiting supplier update.
                                                @else
                                                    . Actual receipt was recorded on {{ optional($firstDeliveryDate)->format('M d, Y') }}.
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if ($earlyArrival && $expectedDelivery && $firstDeliveryDate)
                                        <div class="mt-3 flex items-start gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                                            <i class="fas fa-truck-fast mt-0.5"></i>
                                            <span>
                                                Items began arriving on {{ $firstDeliveryDate->format('M d, Y') }}
                                                @if ($earlyDays > 0)
                                                    , {{ $earlyDays }} {{ \Illuminate\Support\Str::plural('day', $earlyDays) }} ahead of the {{ $expectedDelivery->format('M d, Y') }} target.
                                                @else
                                                    , ahead of schedule.
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if ($stage['key'] === 'awaiting_delivery' && (int) $order->status_id === \App\Models\Status::PO_SENT_TO_SUPPLIER)
                                        <div class="mt-3 flex items-start gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                                            <i class="fas fa-check-double mt-0.5"></i>
                                            <span>Custodian/BAC already accepted this purchase order and endorsed it to the supplier. Awaiting delivery updates.</span>
                                        </div>
                                    @endif

                                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-emerald-700">
                                            <i class="fas fa-check-circle text-[10px]"></i>
                                            Accepted {{ $totals['accepted'] }} / {{ $totals['items'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                            <i class="fas fa-layer-group text-[10px]"></i>
                                            Pending {{ $totals['pending'] }}
                                        </span>
                                        @if ($receivedCount > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-3 py-1 text-sky-700">
                                                <i class="fas fa-box-open text-[10px]"></i>
                                                Received {{ $receivedCount }} / {{ $totals['items'] }}
                                            </span>
                                        @endif
                                        @if (in_array($stage['key'], ['awaiting_delivery', 'receiving'], true) && $awaitingReceipt > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-amber-700">
                                                <i class="fas fa-truck-loading text-[10px]"></i>
                                                Awaiting {{ $awaitingReceipt }}
                                            </span>
                                        @endif
                                        @if ($totals['issues'] > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-3 py-1 text-rose-700">
                                                <i class="fas fa-triangle-exclamation text-[10px]"></i>
                                                Issues {{ $totals['issues'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if ($hasIssues && $issueBreakdown->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach ($issueBreakdown as $issue)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-3 py-1 text-[11px] font-semibold text-rose-700">
                                                    {{ $issue['label'] }} ({{ $issue['count'] }})
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (in_array($stage['key'], ['awaiting_delivery', 'receiving'], true) && $order->items->isNotEmpty())
                                        <div class="mt-3 rounded-lg border border-sky-100 bg-sky-50/70 px-3 py-3 text-xs text-gray-700">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <span class="font-semibold uppercase tracking-wide text-gray-800">Receiving Progress</span>
                                                <span class="text-[11px] text-gray-500">{{ $receivedCount }} of {{ $totals['items'] }} item{{ $totals['items'] === 1 ? '' : 's' }} received</span>
                                            </div>
                                            <div class="mt-3 space-y-3">
                                                @foreach ($order->items as $trackingItem)
                                                    @php
                                                        $trackingReceived = !empty($trackingItem->received_at);
                                                        $trackingReceivable = $trackingItem->fulfillment_status !== 'unavailable';
                                                        $receivedStamp = $trackingItem->received_at ? optional($trackingItem->received_at)->format('M d, Y') : null;
                                                        $receiverName = $trackingItem->receivedBy?->employee
                                                            ? collect([
                                                                $trackingItem->receivedBy->employee->first_name ?? null,
                                                                $trackingItem->receivedBy->employee->last_name ?? null,
                                                            ])->filter()->implode(' ')
                                                            : ($trackingItem->receivedBy?->username ?? null);
                                                    @endphp
                                                    <div class="rounded-md border border-white bg-white px-3 py-2 shadow-sm">
                                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                                            <div>
                                                                <p class="text-sm font-semibold text-gray-900">{{ $trackingItem->item_description }}</p>
                                                                <div class="mt-1 flex flex-wrap gap-3 text-[11px] text-gray-600">
                                                                    <span>Qty {{ $trackingItem->quantity }}</span>
                                                                    <span>Unit {{ $trackingItem->unit }}</span>
                                                                    <span>Cost ₱{{ number_format((float) $trackingItem->unit_cost, 2) }}</span>
                                                                </div>
                                                                @if ($trackingItem->receiving_note)
                                                                    <div class="mt-2 text-[11px] italic text-gray-500">“{{ $trackingItem->receiving_note }}”</div>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                @if ($trackingReceived)
                                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                                                        <i class="fas fa-circle-check text-[9px]"></i>
                                                                        Received {{ $receivedStamp ?? '—' }}
                                                                    </span>
                                                                    @if ($receiverName)
                                                                        <span class="text-[11px] text-gray-500">by {{ $receiverName }}</span>
                                                                    @endif
                                                                @elseif ($trackingReceivable)
                                                                    <form class="js-receive-item" data-receive-url="{{ route('custodian.orders.items.receive', $trackingItem) }}">
                                                                        @csrf
                                                                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-[#1a3a2d] px-3 py-1.5 text-[11px] font-semibold text-white shadow hover:bg-opacity-90">
                                                                            <i class="fas fa-box-open"></i>
                                                                            Mark as Received
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <span class="text-[11px] font-semibold text-amber-700">Unavailable – awaiting sourcing</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">
                                        @if ($deliveries['count'] > 0)
                                            <i class="fas fa-file-circle-check mr-1 text-gray-500"></i>
                                            {{ $deliveries['count'] }} IAR{{ $deliveries['count'] === 1 ? '' : 's' }} recorded
                                            @if ($latestReport)
                                                • Latest {{ optional($latestReport->inspection_date)->format('M d, Y') ?? '—' }}
                                            @endif
                                        @else
                                            <i class="fas fa-circle-dot mr-1 text-gray-400"></i>
                                            No inspections logged yet
                                        @endif
                                    </div>

                                    @if ($stage['key'] === 'awaiting_delivery' && $pendingFulfillment->isNotEmpty())
                                        <details class="mt-4 rounded-lg border border-amber-200 bg-amber-50/80">
                                            <summary class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm font-semibold text-amber-800">
                                                <i class="fas fa-clipboard-list"></i>
                                                Fulfillment follow-ups ({{ $pendingFulfillment->count() }})
                                            </summary>
                                            <div class="space-y-4 border-t border-amber-200 px-3 py-3 text-sm text-gray-700">
                                                @foreach ($pendingFulfillment as $item)
                                                    @php
                                                        $decisionLabel = match ($item->employee_decision) {
                                                            'accept' => 'Employee accepted the proposed alternative.',
                                                            'wait' => 'Employee opted to wait for the original item.',
                                                            default => null,
                                                        };
                                                        $waitUntil = $item->employee_wait_until ? \Illuminate\Support\Carbon::parse($item->employee_wait_until) : null;
                                                        $waitExpired = $waitUntil ? $waitUntil->isPast() : false;
                                                    @endphp
                                                    <div class="rounded-lg border border-amber-200 bg-white p-3 shadow-sm">
                                                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                                            <div class="space-y-1 text-xs">
                                                                <p class="text-sm font-semibold text-gray-900">{{ $item->item_description }}</p>
                                                                <div class="flex flex-wrap items-center gap-2 text-gray-600">
                                                                    <span>Qty {{ $item->quantity }}</span>
                                                                    <span>Unit {{ $item->unit }}</span>
                                                                    <span>Cost ₱{{ number_format((float) $item->unit_cost, 2) }}</span>
                                                                </div>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700 capitalize">
                                                                        <i class="fas fa-circle text-[8px]"></i>{{ $item->fulfillment_status }}
                                                                    </span>
                                                                    @if ($item->alternate_description)
                                                                        <span class="text-[11px] text-amber-700">Alt: {{ $item->alternate_description }}</span>
                                                                    @endif
                                                                </div>
                                                                @if ($decisionLabel)
                                                                    <div class="rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-[11px] text-sky-800">
                                                                        <i class="fas fa-user-check mr-1"></i>{{ $decisionLabel }}
                                                                    </div>
                                                                @endif
                                                                @if ($item->fulfillment_status === 'unavailable')
                                                                    <div class="rounded-md border {{ $waitUntil ? ($waitExpired ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-800') : 'border-amber-200 bg-amber-50 text-amber-800' }} px-2.5 py-1.5 text-[11px]">
                                                                        <i class="fas fa-hourglass-half mr-1"></i>
                                                                        @if ($waitUntil)
                                                                            {{ $waitExpired ? 'Wait period lapsed on '.$waitUntil->format('M d, Y') : 'Waiting until '.$waitUntil->format('M d, Y') }}
                                                                        @else
                                                                            Awaiting employee wait timeframe.
                                                                        @endif
                                                                        @if ($item->employee_wait_note)
                                                                            <div class="mt-1 italic text-gray-600">“{{ $item->employee_wait_note }}”</div>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="flex flex-col items-end gap-2">
                                                                <button class="js-view-po inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                                                    data-show-url="{{ route('custodian.orders.show', $order) }}">
                                                                    <i class="fas fa-eye"></i>View PO
                                                                </button>
                                                                @if ($item->fulfillment_status === 'alternative' && $item->employee_decision === 'wait')
                                                                    <button type="button" class="js-retain-original inline-flex items-center gap-2 rounded-md border border-amber-400 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-500 hover:text-white"
                                                                        data-retain-url="{{ route('custodian.orders.items.retain', $item) }}">
                                                                        <i class="fas fa-undo"></i>Retain Original
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <form class="mt-3 grid gap-3 rounded-lg border border-amber-100 bg-amber-50 px-3 py-3 text-xs md:grid-cols-[minmax(0,1fr)_minmax(0,150px)_minmax(0,220px)] js-update-po-item"
                                                            data-update-url="{{ route('custodian.orders.items.update', $item) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="space-y-1">
                                                                <label class="font-semibold uppercase tracking-wide text-amber-700">Item Status</label>
                                                                <select name="fulfillment_status" class="js-item-status w-full rounded-md border border-amber-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none">
                                                                    <option value="ordered" @selected($item->fulfillment_status === 'ordered')>Ordered</option>
                                                                    <option value="unavailable" @selected($item->fulfillment_status === 'unavailable')>Unavailable</option>
                                                                    <option value="alternative" @selected($item->fulfillment_status === 'alternative')>Alternative</option>
                                                                </select>
                                                            </div>
                                                            <div class="space-y-1">
                                                                <label class="font-semibold uppercase tracking-wide text-amber-700">Unit Cost</label>
                                                                <div class="relative">
                                                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-amber-600">₱</span>
                                                                    <input type="number" min="0" step="0.01" name="unit_cost" value="{{ $item->unit_cost }}"
                                                                        class="js-item-unit-cost w-full rounded-md border border-amber-300 px-3 py-2 pl-7 text-sm text-right focus:border-[#1a3a2d] focus:outline-none">
                                                                </div>
                                                            </div>
                                                            <div data-alt-wrapper class="space-y-1 {{ $item->fulfillment_status === 'alternative' ? '' : 'hidden' }}">
                                                                <label class="font-semibold uppercase tracking-wide text-amber-700">Alternative Description</label>
                                                                <input type="text" name="alternate_description" value="{{ $item->alternate_description }}"
                                                                    class="js-item-alt w-full rounded-md border border-amber-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none"
                                                                    {{ $item->fulfillment_status === 'alternative' ? '' : 'disabled' }}>
                                                            </div>
                                                            <div class="md:col-span-3 flex justify-end">
                                                                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90">
                                                                    <i class="fas fa-floppy-disk"></i>Save Changes
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif

                                    @if ($stage['key'] === 'awaiting_delivery' && (int) $order->status_id === \App\Models\Status::PO_CREATED)
                                        <div class="mt-4 flex justify-end">
                                            <button type="button" class="js-accept-po inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90"
                                                data-accept-url="{{ route('custodian.orders.accept', $order) }}">
                                                <i class="fas fa-check"></i>
                                                Accept & Send to Supplier
                                            </button>
                                        </div>
                                    @endif

                                    <div class="mt-4 flex flex-wrap justify-end gap-2">
                                        <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                            data-show-url="{{ route('custodian.orders.show', $order) }}">
                                            <i class="fas fa-eye"></i>
                                            View PO
                                        </button>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-200 bg-white px-4 py-6 text-center text-sm text-gray-500">
                                    <i class="fa-regular fa-folder-open text-2xl text-gray-300"></i>
                                    <p class="mt-2">No purchase orders currently fall under this stage.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

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
                                $latestInspectionItems = $order->inspectionReports
                                    ->sortBy('inspection_date')
                                    ->flatMap(function ($report) {
                                        return $report->items;
                                    })
                                    ->keyBy('po_item_id');

                                $issueStatusIds = [
                                    \App\Models\Status::ITEM_DEFECTIVE,
                                    \App\Models\Status::ITEM_RETURNED,
                                    \App\Models\Status::ITEM_REPLACED,
                                ];
                                $acceptedStatusIds = [
                                    \App\Models\Status::ITEM_ACCEPTED,
                                    \App\Models\Status::ITEM_RECORDED,
                                ];

                                $issueCount = $latestInspectionItems->filter(fn ($item) => in_array((int) $item->inspection_status_id, $issueStatusIds, true))->count();
                                $acceptedCount = $latestInspectionItems->filter(fn ($item) => in_array((int) $item->inspection_status_id, $acceptedStatusIds, true))->count();
                                $pendingCount = max($order->items->count() - $acceptedCount - $issueCount, 0);

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

                                $deliveriesCount = $order->inspectionReports->count();
                                $latestInspectionDate = optional($order->inspectionReports->sortByDesc('inspection_date')->first()?->inspection_date)->format('M d, Y');

                                $inspectableStatuses = [
                                    \App\Models\Status::PO_PARTIALLY_DELIVERED,
                                    \App\Models\Status::PO_DELIVERED_PENDING_INSPECTION,
                                    \App\Models\Status::PO_CLOSED,
                                ];
                                $canInspect = in_array($order->status_id, $inspectableStatuses, true) || $order->inspectionReports->isNotEmpty();
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
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700">Accepted {{ $acceptedCount }}</span>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">Pending {{ $pendingCount }}</span>
                                        @if ($issueCount > 0)
                                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-rose-700">Issues {{ $issueCount }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-gray-700">
                                    <div class="text-xs text-gray-600">
                                        <div>{{ $deliveriesCount }} batch{{ $deliveriesCount === 1 ? '' : 'es' }}</div>
                                        <div>Latest {{ $latestInspectionDate ?? '—' }}</div>
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
                                        @if ($canInspect)
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
@endpush
