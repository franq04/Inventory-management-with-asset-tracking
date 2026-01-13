@extends('layouts.app')

@section('title', 'Inspector - Item Inspection')

@section('content')
{{-- Page Header --}}
<div class="animate-card mb-6 sm:mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1a3a2d]">Item Inspection</h2>
            <p class="mt-1 text-sm sm:text-base text-gray-500">Review and accept/reject delivered items for quality assurance.</p>
        </div>
        <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white">
            <i class="fas fa-search"></i> Inspector Access
        </span>
    </div>
</div>

@php
    $statusMeta = [
        'pending' => [
            'label' => 'Pending Inspection',
            'description' => 'Items awaiting your inspection.',
            'icon' => 'fa-clock',
            'color' => 'amber',
        ],
        'accepted' => [
            'label' => 'Accepted',
            'description' => 'Items you have accepted.',
            'icon' => 'fa-circle-check',
            'color' => 'emerald',
        ],
        'rejected' => [
            'label' => 'Rejected/Defective',
            'description' => 'Items marked as defective.',
            'icon' => 'fa-triangle-exclamation',
            'color' => 'rose',
        ],
        'recorded' => [
            'label' => 'Recorded in PQS',
            'description' => 'Items recorded in property ledger.',
            'icon' => 'fa-check-double',
            'color' => 'blue',
        ],
    ];
@endphp

{{-- Main Content Card --}}
<div class="animate-card rounded-2xl bg-white shadow-lg">
    
    {{-- Status Tabs --}}
    <div class="border-b border-gray-200 p-3 sm:p-4 md:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach ($statusMeta as $key => $meta)
                <a href="?tab={{ $key }}"
                    class="group flex flex-col rounded-xl border-2 p-4 text-left transition-all duration-300
                           {{ $activeTab === $key 
                              ? 'border-' . $meta['color'] . '-500 bg-' . $meta['color'] . '-50 shadow-lg' 
                              : 'border-gray-200 bg-white hover:border-' . $meta['color'] . '-300 hover:bg-' . $meta['color'] . '-50/50' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-full 
                                           bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-600">
                                    <i class="fas {{ $meta['icon'] }} text-sm sm:text-base"></i>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wide text-gray-500 truncate">
                                    {{ $meta['label'] }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 hidden sm:block">{{ $meta['description'] }}</p>
                        </div>
                        <span class="inline-flex h-7 w-7 sm:h-8 sm:w-8 flex-shrink-0 items-center justify-center rounded-full 
                                     bg-{{ $meta['color'] }}-100 text-xs sm:text-sm font-bold text-{{ $meta['color'] }}-700">
                            {{ $tabCounts[$key] ?? 0 }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Items Table --}}
    <div class="p-3 sm:p-4 md:p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs sm:text-sm">
                <thead class="border-b-2 border-gray-200">
                    <tr class="text-left text-gray-500 uppercase tracking-wider">
                        <th class="px-2 sm:px-4 py-3">Item ID</th>
                        <th class="px-2 sm:px-4 py-3">Description</th>
                        <th class="px-2 sm:px-4 py-3 hidden md:table-cell">PO Number</th>
                        <th class="px-2 sm:px-4 py-3 hidden lg:table-cell">Quantity</th>
                        <th class="px-2 sm:px-4 py-3">Status</th>
                        <th class="px-2 sm:px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $statusName = $item->status?->status_name ?? 'Pending';
                            $po = $item->report?->purchaseOrder;
                            $poItem = $item->purchaseOrderItem;
                        @endphp
                        <tr class="border-b hover:bg-gray-50 transition-colors">
                            <td class="px-2 sm:px-4 py-4 font-semibold text-gray-800">#{{ $item->ia_item_id }}</td>
                            <td class="px-2 sm:px-4 py-4">
                                <div class="font-medium text-gray-800 break-words">{{ $poItem?->item_description ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500 mt-1 hidden sm:block">{{ $poItem?->unit ?? '' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-4 font-medium text-gray-700 hidden md:table-cell">{{ $po?->po_no ?? 'N/A' }}</td>
                            <td class="px-2 sm:px-4 py-4 hidden lg:table-cell">
                                <span class="font-semibold">{{ $item->quantity_accepted ?? 0 }}</span>
                                <span class="text-gray-500">/ {{ $poItem?->quantity ?? 0 }}</span>
                            </td>
                            <td class="px-2 sm:px-4 py-4">
                                <span class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-full text-xs font-semibold
                                           {{ $statusName === 'Accepted' ? 'bg-emerald-100 text-emerald-700' : 
                                              ($statusName === 'Defective' ? 'bg-rose-100 text-rose-700' : 
                                              ($statusName === 'Recorded' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700')) }}">
                                    {{ $statusName }}
                                </span>
                            </td>
                            <td class="px-2 sm:px-4 py-4 text-center">
                                @if($activeTab === 'pending')
                                    <div class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2">
                                        <button onclick="acceptItem({{ $item->ia_item_id }})" 
                                                class="px-2 sm:px-3 py-1 rounded-md bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                                            Accept
                                        </button>
                                        <button onclick="rejectItem({{ $item->ia_item_id }})" 
                                                class="px-2 sm:px-3 py-1 rounded-md bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition-colors">
                                            Reject
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">—</span>
                                @endif

                                @if(!empty($po))
                                    <div class="mt-2">
                                        <button type="button"
                                            class="js-open-inspection inline-flex items-center gap-2 rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                            data-form-url="{{ route('inspector.inspection.form', $po) }}"
                                            data-store-url="{{ route('inspector.inspection.batch', $po) }}">
                                            <i class="fas fa-clipboard-list"></i>
                                            Inspect PO
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 sm:px-4 py-8 sm:py-12 text-center text-gray-500">
                                <i class="fas fa-inbox fa-2x sm:fa-3x text-gray-300 mb-3"></i>
                                <p class="font-medium text-sm sm:text-base">No items found for this status.</p>
                                <p class="text-xs mt-1">Items will appear here when they need inspection.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
        <div class="mt-4 sm:mt-6">
            {{ $items->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function acceptItem(itemId) {
    if (confirm('Accept this item?')) {
        // TODO: Implement accept item functionality
        console.log('Accepting item:', itemId);
    }
}

function rejectItem(itemId) {
    if (confirm('Reject this item as defective?')) {
        // TODO: Implement reject item functionality
        console.log('Rejecting item:', itemId);
    }
}
</script>
@endpush

@endsection
