@extends('layouts.app')

@section('content')
@php
    $statusName = strtolower($purchaseRequest->status?->status_name ?? 'unknown');
    $statusId = (int) ($purchaseRequest->status_id ?? 0);
    $statusBadgeClasses = 'bg-gray-100 text-gray-700 border-gray-200';

    $isRecommended = $statusId === \App\Models\Status::PR_RECOMMENDED;
    $isForApproval = $statusId === \App\Models\Status::PR_FOR_APPROVAL;
    $isTerminal = in_array($statusId, [\App\Models\Status::PR_APPROVED, \App\Models\Status::PR_CANCELLED], true);

    if (in_array($statusName, ['approved', 'completed'], true)) {
        $statusBadgeClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200';
    } elseif (in_array($statusName, ['cancelled', 'declined'], true)) {
        $statusBadgeClasses = 'bg-rose-50 text-rose-700 border-rose-200';
    } elseif (in_array($statusName, ['pending', 'for approval', 'recommended'], true)) {
        $statusBadgeClasses = 'bg-amber-50 text-amber-700 border-amber-200';
    }

    $itemCount = $purchaseRequest->items->count();
    $totalCost = (float) ($purchaseRequest->items->sum('estimated_total_cost') ?: $purchaseRequest->total_estimated_cost);
@endphp

<div id="prReviewPage" data-pr-review-role="bac" data-pr-review-pr-no="{{ $purchaseRequest->pr_no }}" data-pr-review-review-url="{{ route('bac.requests.review', $purchaseRequest) }}" data-pr-review-approve-url="{{ route('bac.requests.approve', $purchaseRequest) }}" data-pr-review-cancel-url="{{ route('bac.requests.cancel', $purchaseRequest) }}" data-pr-review-back-url="{{ route('bac.requests.index') }}" class="space-y-8 animate-card">
    <div id="prReviewToast" class="pointer-events-none fixed top-20 right-4 sm:right-6 z-[100] hidden min-w-[250px] max-w-md rounded-xl border border-[#2d5a4a] bg-[#1a3a2d] px-5 py-4 text-sm font-semibold text-white shadow-2xl opacity-0 transition-all duration-300"></div>
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    BAC Detail Review
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight">Purchase Request {{ $purchaseRequest->pr_no }}</h1>
                <p class="text-sm text-white/75">Inspect request details, validate budget alignment, and execute BAC actions with complete traceability.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[520px]">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Requester</p>
                    <p class="mt-2 text-lg font-bold truncate">{{ $purchaseRequest->requester?->username ?? 'Unknown' }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Items</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($itemCount) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Estimated Cost</p>
                    <p class="mt-2 text-2xl font-bold">₱{{ number_format($totalCost, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="rounded-[24px] border border-emerald-950/8 bg-white p-5 shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)]">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-[#1a3a2d]">Request Profile</h2>
                    <span data-pr-review-status-badge class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold capitalize border {{ $statusBadgeClasses }}">
                        <i class="fas fa-circle text-[8px]"></i>{{ $purchaseRequest->status?->status_name ?? 'Unknown' }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">PR Number</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $purchaseRequest->pr_no }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Date Submitted</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ optional($purchaseRequest->created_at)->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Division</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $purchaseRequest->division?->division_name ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Section</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $purchaseRequest->section?->section_name ?? '—' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Purpose</p>
                    <p class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $purchaseRequest->purpose ?? '—' }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-emerald-950/10 bg-white shadow-[0_20px_50px_-38px_rgba(15,23,42,0.75)]">
                <div class="border-b border-gray-100 bg-[#fbfcfb] px-5 py-4">
                    <h3 class="text-base font-bold text-[#1a3a2d]">Requested Items</h3>
                    <p class="text-xs text-gray-500">Line-level view of requested quantities and estimates.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-[#f5f8f6] text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.14em]">
                            <tr>
                                <th class="px-5 py-3">Description</th>
                                <th class="px-5 py-3">Qty</th>
                                <th class="px-5 py-3">Unit</th>
                                <th class="px-5 py-3 text-right">Unit Cost</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-sm text-gray-700">
                            @forelse($purchaseRequest->items as $item)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-gray-900">{{ $item->item_description }}</p>
                                        <p class="mt-1 text-xs text-gray-500">Stock No: {{ $item->stock_property_no ?: '—' }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-medium text-gray-900">{{ number_format((float) $item->quantity, 2) }}</td>
                                    <td class="px-5 py-4">{{ $item->unit ?: '—' }}</td>
                                    <td class="px-5 py-4 text-right font-medium">₱{{ number_format((float) $item->estimated_unit_cost, 2) }}</td>
                                    <td class="px-5 py-4 text-right font-semibold text-[#1a3a2d]">₱{{ number_format((float) $item->estimated_total_cost, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">
                                        <i class="fas fa-box-open text-3xl text-gray-300"></i>
                                        <p class="mt-2 font-medium">No line items found for this request.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-[#fbfcfb]">
                            <tr>
                                <td colspan="4" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Grand Total</td>
                                <td class="px-5 py-3 text-right text-sm font-bold text-[#1a3a2d]">₱{{ number_format($totalCost, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div data-pr-review-actions class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-[0_20px_45px_-35px_rgba(146,64,14,0.35)]">
                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-amber-900">BAC Actions</h3>
                @if ($isTerminal)
                    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                        <p class="font-semibold text-gray-900">This request is read-only.</p>
                        <p class="mt-1 text-xs text-gray-600">No further BAC actions are available for {{ strtolower($purchaseRequest->status?->status_name ?? 'this status') }} requests.</p>
                    </div>
                @else
                    <p class="mt-1 text-xs text-amber-800">Use actions below to progress or close this request.</p>

                    <div class="mt-4 space-y-2">
                        @if ($isRecommended)
                            <form method="POST" action="{{ route('bac.requests.review', $purchaseRequest) }}" data-pr-review-form data-pr-review-action="review" data-confirm-message="Move this purchase request to BAC review?">
                                @csrf
                                <button type="submit" data-loading-text="Moving to review..." class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
                                    <i class="fas fa-clipboard-check"></i>
                                    Move to Review
                                </button>
                            </form>
                        @endif

                        @if ($isForApproval)
                            <form method="POST" action="{{ route('bac.requests.approve', $purchaseRequest) }}" data-pr-review-form data-pr-review-action="approve" data-confirm-message="Mark as Approved? This action is final.">
                                @csrf
                                <button type="submit" data-loading-text="Approving..." class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    <i class="fas fa-check-circle"></i>
                                    Approve
                                </button>
                            </form>
                        @endif

                        @if ($isRecommended || $isForApproval)
                            <form method="POST" action="{{ route('bac.requests.cancel', $purchaseRequest) }}" data-pr-review-form data-pr-review-action="cancel" data-confirm-message="Cancel this purchase request?">
                                @csrf
                                <input type="hidden" name="remarks" value="Cancelled by BAC">
                                <button type="submit" data-loading-text="Canceling request..." class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                    <i class="fas fa-times-circle"></i>
                                    Cancel Request
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)]">
                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-[#1a3a2d]">Navigation</h3>
                <p class="mt-1 text-xs text-gray-500">Return to queue after taking action.</p>
                <a href="{{ route('bac.requests.index') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    <i class="fas fa-arrow-left"></i>
                    Back to Queue
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/purchase-request-review-page.js')
@endpush
@endsection
