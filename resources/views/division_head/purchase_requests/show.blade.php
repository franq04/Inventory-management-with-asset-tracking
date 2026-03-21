@extends('layouts.app')

@section('title', 'Division Head - Review Purchase Request')

@section('content')
<div id="prReviewPage" data-pr-review-role="division_head" data-pr-review-pr-no="{{ $purchaseRequest->pr_no }}" data-pr-review-recommend-url="{{ route('division_head.requests.recommend', $purchaseRequest) }}" data-pr-review-cancel-url="{{ route('division_head.requests.cancel', $purchaseRequest) }}" data-pr-review-back-url="{{ route('division_head.requests.index') }}" class="max-w-4xl mx-auto space-y-6 px-4">
    <div id="prReviewToast" class="pointer-events-none fixed top-20 right-4 sm:right-6 z-[100] hidden min-w-[250px] max-w-md rounded-xl border border-[#2d5a4a] bg-[#1a3a2d] px-5 py-4 text-sm font-semibold text-white shadow-2xl opacity-0 transition-all duration-300"></div>
    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-[#1a3a2d]">Purchase Request {{ $purchaseRequest->pr_no }}</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Review and recommend for BAC approval</p>
            </div>
            <span data-pr-review-status-badge class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold
                       {{ $purchaseRequest->status?->status_name === 'Recommended' ? 'bg-emerald-100 text-emerald-700' : 
                          ($purchaseRequest->status?->status_name === 'Cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                {{ $purchaseRequest->status?->status_name ?? 'Unknown' }}
            </span>
        </div>

        {{-- Request Details --}}
        <div class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs sm:text-sm text-gray-500">Requester</p>
                <p class="font-medium text-sm sm:text-base">
                    @if($purchaseRequest->requester?->employee)
                        {{ trim($purchaseRequest->requester->employee->first_name . ' ' . $purchaseRequest->requester->employee->last_name) }}
                    @else
                        {{ $purchaseRequest->requester?->username ?? 'Unknown' }}
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">Division / Section</p>
                <p class="font-medium text-sm sm:text-base">
                    {{ $purchaseRequest->division?->div_name ?? 'N/A' }} / {{ $purchaseRequest->section?->sec_name ?? 'N/A' }}
                </p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs sm:text-sm text-gray-500">Purpose</p>
                <p class="font-medium text-sm sm:text-base break-words">{{ $purchaseRequest->purpose ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">Requested Date</p>
                <p class="font-medium text-sm sm:text-base">{{ optional($purchaseRequest->created_at)->format('M d, Y h:i A') ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">Total Estimated Cost</p>
                <p class="font-bold text-base sm:text-lg text-[#1a3a2d]">₱{{ number_format($purchaseRequest->total_estimated_cost ?? 0, 2) }}</p>
            </div>
        </div>
    </div>

    {{-- Items List --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Requested Items</h3>
        <div class="space-y-3">
            @forelse($purchaseRequest->items as $item)
                <div class="border border-gray-200 rounded-xl p-3 sm:p-4 hover:border-[#1a3a2d]/30 transition-all">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm sm:text-base text-gray-800 break-words">{{ $item->item_description }}</div>
                            <div class="text-xs text-gray-500 mt-1 space-y-1">
                                <div>Quantity: <span class="font-medium">{{ $item->quantity }} {{ $item->unit }}</span></div>
                                <div>Unit Cost: <span class="font-medium">₱{{ number_format($item->unit_cost, 2) }}</span></div>
                                @if($item->item_remarks)
                                    <div>Remarks: <span class="font-medium">{{ $item->item_remarks }}</span></div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-gray-500">Total Cost</p>
                            <p class="text-base sm:text-lg font-bold text-[#1a3a2d]">₱{{ number_format($item->estimated_total_cost, 2) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-box-open fa-2x text-gray-300 mb-3"></i>
                    <p class="text-sm">No items in this request.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Action Buttons --}}
    @if($purchaseRequest->status?->status_id === App\Models\Status::PR_FOR_RECOMMENDATION)
        <div data-pr-review-actions class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Division Head Actions</h3>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <form method="POST" action="{{ route('division_head.requests.recommend', $purchaseRequest) }}" data-pr-review-form data-pr-review-action="recommend" data-confirm-message="Recommend this purchase request to BAC?">
                    @csrf
                        <button type="submit" data-loading-text="Submitting recommendation..." 
                            class="px-3 sm:px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold text-xs sm:text-sm hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-thumbs-up mr-1"></i> Recommend
                    </button>
                </form>

                <form method="POST" action="{{ route('division_head.requests.cancel', $purchaseRequest) }}" data-pr-review-form data-pr-review-action="cancel" data-confirm-message="Cancel this purchase request? This action cannot be undone.">
                    @csrf
                    <input type="hidden" name="remarks" value="Cancelled by Division Head">
                            <button type="submit" data-loading-text="Canceling request..." 
                            class="px-3 sm:px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold text-xs sm:text-sm hover:bg-rose-700 transition-colors">
                        <i class="fas fa-ban mr-1"></i> Cancel
                    </button>
                </form>

                <a href="{{ route('division_head.requests.index') }}" 
                   class="text-xs sm:text-sm text-gray-500 hover:text-[#1a3a2d] font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>
    @else
        <div data-pr-review-actions class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                <p class="font-semibold text-gray-900">This request is read-only.</p>
                <p class="mt-1 text-xs text-gray-600">No further actions are available for this request status.</p>
            </div>
            <a href="{{ route('division_head.requests.index') }}" 
               class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-500 hover:text-[#1a3a2d] font-medium">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    @endif

    {{-- Status History --}}
    @if($purchaseRequest->statusHistory && $purchaseRequest->statusHistory->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Status History</h3>
            <div class="space-y-3">
                @foreach($purchaseRequest->statusHistory as $history)
                    <div class="border-l-4 border-blue-400 pl-4 py-2">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm text-gray-800">{{ $history->status?->status_name ?? 'Unknown' }}</p>
                                @if($history->remarks)
                                    <p class="text-xs text-gray-600 mt-1 break-words">{{ $history->remarks }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">
                                    Changed by: {{ $history->changedBy?->username ?? 'System' }}
                                </p>
                            </div>
                            <p class="text-xs text-gray-500 flex-shrink-0">{{ optional($history->changed_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
@vite('resources/js/purchase-request-review-page.js')
@endpush
@endsection
