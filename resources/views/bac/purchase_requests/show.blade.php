@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 px-4">
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <h2 class="text-xl sm:text-2xl font-bold text-[#1a3a2d]">Purchase Request {{ $purchaseRequest->pr_no }}</h2>
        <div class="mt-4 grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs sm:text-sm text-gray-500">Requester</p>
                <p class="font-medium text-sm sm:text-base">{{ $purchaseRequest->requester?->username ?? 'Unknown' }}</p>
            </div>
            <div>
                <p class="text-xs sm:text-sm text-gray-500">Status</p>
                <p class="font-medium text-sm sm:text-base">{{ $purchaseRequest->status?->status_name ?? '—' }}</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs sm:text-sm text-gray-500">Purpose</p>
                <p class="font-medium text-sm sm:text-base break-words">{{ $purchaseRequest->purpose ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-xs sm:text-sm text-gray-500 font-semibold">Items</h3>
            <ul class="mt-2 space-y-2">
                @foreach($purchaseRequest->items as $item)
                <li class="border rounded-md p-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm sm:text-base break-words">{{ $item->item_description }}</div>
                        <div class="text-xs text-gray-500 mt-1">Qty: {{ $item->quantity }} · Unit: {{ $item->unit }}</div>
                    </div>
                    <div class="text-sm sm:text-base font-semibold text-[#1a3a2d] flex-shrink-0">₱{{ number_format($item->estimated_total_cost, 2) }}</div>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-6 flex flex-wrap items-center gap-2 sm:gap-3">
            <form method="POST" action="{{ route('bac.requests.review', $purchaseRequest) }}">
                @csrf
                <button type="submit" class="px-3 sm:px-4 py-2 rounded-lg bg-amber-500 text-white font-semibold text-xs sm:text-sm hover:bg-amber-600 transition-colors">Move to Review</button>
            </form>

            <form method="POST" action="{{ route('bac.requests.approve', $purchaseRequest) }}" onsubmit="return confirm('Mark as Approved? This action is final.');">
                @csrf
                <button type="submit" class="px-3 sm:px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold text-xs sm:text-sm hover:bg-emerald-700 transition-colors">Approve</button>
            </form>

            <form method="POST" action="{{ route('bac.requests.cancel', $purchaseRequest) }}" onsubmit="return confirm('Cancel this purchase request?');">
                @csrf
                <input type="hidden" name="remarks" value="Cancelled by BAC">
                <button type="submit" class="px-3 sm:px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold text-xs sm:text-sm hover:bg-rose-700 transition-colors">Cancel</button>
            </form>

            <a href="{{ route('bac.requests.index') }}" class="text-xs sm:text-sm text-gray-500 hover:text-[#1a3a2d] font-medium">Back to queue</a>
        </div>
    </div>
</div>
@endsection
