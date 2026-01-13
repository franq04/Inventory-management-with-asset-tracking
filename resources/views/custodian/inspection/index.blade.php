@extends('layouts.app')

@section('title', 'Inspection & Acceptance')

@section('content')
{{-- Page Header --}}
<div class="animate-card mb-8">
    <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Inspection & Acceptance Reports</h2>
    <p class="mt-1 text-gray-500">Review received items and track their acceptance status across various categories.</p>
</div>

@php
    $statusMeta = [
        'pending' => [
            'label' => 'Pending Inspection',
            'description' => 'Items delivered but awaiting final inspection results.',
            'icon' => 'fa-clock',
            'button_base' => 'border-amber-100 bg-white/95 text-amber-700 hover:border-amber-300 hover:bg-amber-50/80',
            'button_active' => '.active:border-amber-500 .active:bg-amber-50 .active:shadow-lg .active:shadow-amber-400/30',
            'icon_wrap' => 'bg-amber-100 text-amber-600 .active:bg-amber-200 .active:text-amber-800',
            'badge' => 'bg-amber-50 text-amber-700 .active:bg-amber-200 .active:text-amber-800',
            'description_class' => 'text-gray-500 .active:text-amber-800',
        ],
        'accepted' => [
            'label' => 'Accepted / Recorded',
            'description' => 'Items accepted or already recorded to property ledgers.',
            'icon' => 'fa-circle-check',
            'button_base' => 'border-emerald-100 bg-white/95 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-50/80',
            'button_active' => '.active:border-emerald-600 .active:bg-emerald-50 .active:shadow-lg .active:shadow-emerald-400/30',
            'icon_wrap' => 'bg-emerald-100 text-emerald-600 .active:bg-emerald-200 .active:text-emerald-800',
            'badge' => 'bg-emerald-50 text-emerald-700 .active:bg-emerald-200 .active:text-emerald-800',
            'description_class' => 'text-gray-500 .active:text-emerald-800',
        ],
        'defective' => [
            'label' => 'Defective',
            'description' => 'Items discovered with defects during inspection.',
            'icon' => 'fa-triangle-exclamation',
            'button_base' => 'border-rose-100 bg-white/95 text-rose-700 hover:border-rose-300 hover:bg-rose-50/80',
            'button_active' => '.active:border-rose-600 .active:bg-rose-50 .active:shadow-lg .active:shadow-rose-400/30',
            'icon_wrap' => 'bg-rose-100 text-rose-600 .active:bg-rose-200 .active:text-rose-800',
            'badge' => 'bg-rose-50 text-rose-700 .active:bg-rose-200 .active:text-rose-800',
            'description_class' => 'text-gray-500 .active:text-rose-800',
        ],
        'returned' => [
            'label' => 'Returned / Replacement',
            'description' => 'Items returned to supplier for action or replacement.',
            'icon' => 'fa-rotate-left',
            'button_base' => 'border-sky-100 bg-white/95 text-sky-700 hover:border-sky-300 hover:bg-sky-50/80',
            'button_active' => '.active:border-sky-600 .active:bg-sky-50 .active:shadow-lg .active:shadow-sky-400/30',
            'icon_wrap' => 'bg-sky-100 text-sky-600 .active:bg-sky-200 .active:text-sky-800',
            'badge' => 'bg-sky-50 text-sky-700 .active:bg-sky-200 .active:text-sky-800',
            'description_class' => 'text-gray-500 .active:text-sky-800',
        ],
    ];
    $viewerRole = auth()->user()->role ?? null;
@endphp

{{-- Main Content Card --}}
<div class="animate-card rounded-2xl bg-white shadow-lg">
    
    {{-- Status Tabs styled similar to purchase requests --}}
    <div class="border-b border-gray-200 p-4 sm:p-6">
        <div id="inspectionTabs" class="flex flex-wrap gap-3">
            <style>
                /* Selected inspection tab: subtle glow and elevated shadow without changing text color */
                .inspection-tab.is-selected {
                    border-color: #1a3a2d !important;
                    transform: translateY(-4px);
                    box-shadow: 0 10px 30px rgba(26,58,45,0.08);
                }

                /* Slight highlight for the icon background when selected */
                .inspection-tab.is-selected .inspection-label + .inspection-desc,
                .inspection-tab.is-selected .inspection-badge {
                    /* keep defaults; reserved for future adjustments */
                }
            </style>
            @foreach ($statusMeta as $key => $meta)
                <button type="button"
                    class="inspection-tab group flex min-w-[220px] flex-1 items-start justify-between gap-3 rounded-2xl border px-5 py-4 text-left text-sm font-semibold shadow-sm transition-all duration-300 {{ $meta['button_base'] }} {{ $meta['button_active'] }}"
                    data-inspection-tab="{{ $key }}">
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full text-base transition-colors {{ $meta['icon_wrap'] }}">
                        <i class="fa-solid {{ $meta['icon'] }}"></i>
                    </span>
                    <div class="flex flex-1 flex-col gap-1">
                        <span class="inspection-label text-sm transition-colors">{{ $meta['label'] }}</span>
                        <span class="inspection-desc text-xs font-normal leading-snug transition-colors {{ $meta['description_class'] }}">{{ $meta['description'] }}</span>
                    </div>
                    <span class="inspection-badge inline-flex min-w-[34px] items-center justify-center rounded-full px-3 py-1 text-xs font-bold transition-colors {{ $meta['badge'] }}">
                        {{ $itemsByStatus[$key]->total() }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="p-4 sm:p-6">
        {{-- ENHANCED: Filter and Action Bar --}}
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            {{-- Left Side: Search and Date Filters --}}
            <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
                <div class="relative w-full flex-grow md:max-w-xs">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input id="inspectionSearch" type="search" class="w-full rounded-lg border-gray-200 bg-gray-50/80 py-2.5 pl-11 pr-4 text-sm shadow-sm transition placeholder:text-gray-400 focus:border-[#1a3a2d] focus:bg-white focus:ring-2 focus:ring-[#1a3a2d]/50" placeholder="Search IAR, PO, Item..." aria-label="Search inspections">
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <input id="inspectionDateFrom" type="date" class="rounded-lg border-gray-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="Start Date">
                    <span class="text-gray-400">-</span>
                    <input id="inspectionDateTo" type="date" class="rounded-lg border-gray-200 bg-white px-3 py-2 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="End Date">
                    <button id="inspectionApplyDateFilter" type="button" class="flex h-10 w-10 items-center justify-center rounded-lg border border-[#1a3a2d] text-[#1a3a2d] shadow-sm transition-all hover:bg-[#1a3a2d] hover:text-white hover:shadow-md" title="Apply Date Filter">
                        <i class="fa-solid fa-filter text-sm"></i>
                    </button>
                </div>
            </div>

            {{-- Right Side: Export Buttons --}}
            <div class="flex items-center gap-2">
                <button id="inspectionPrintPdfBtn" type="button" class="flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm transition-all duration-300 hover:border-red-300 hover:bg-red-100 hover:shadow-md">
                    <i class="fa-solid fa-file-pdf text-red-500"></i>
                    <span>Print PDF</span>
                </button>
                <button id="inspectionPrintExcelBtn" type="button" class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-800 shadow-sm transition-all duration-300 hover:border-green-300 hover:bg-green-100 hover:shadow-md">
                    <i class="fa-solid fa-file-excel text-green-600"></i>
                    <span>Export Excel</span>
                </button>
            </div>
        </div>

        {{-- Tab Panels --}}
        @foreach ($statusMeta as $key => $meta)
            @php
                $panelItems = $itemsByStatus[$key];
                $panelTotal = $panelItems->total();
            @endphp
            <div class="inspection-panel hidden" data-inspection-panel="{{ $key }}">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $meta['label'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $meta['description'] }}</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $meta['badge'] }}">
                        <i class="fa-solid {{ $meta['icon'] }}"></i>
                        {{ $panelTotal }} item(s) found
                    </span>
                </div>

                {{-- ENHANCED: Table Design --}}
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-100">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100/70 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="whitespace-nowrap px-5 py-3 text-left">IAR No.</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left">PO No.</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left">Item Description</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left">Supplier</th>
                                <th class="whitespace-nowrap px-5 py-3 text-right">Delivered</th>
                                <th class="whitespace-nowrap px-5 py-3 text-right">Accepted</th>
                                <th class="whitespace-nowrap px-5 py-3 text-right">Rejected</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left">Warranty</th>
                                <th class="whitespace-nowrap px-5 py-3 text-left">Remarks</th>
                                <th class="whitespace-nowrap px-5 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($panelItems as $item)
                                @php
                                 $purchaseOrder = $item->purchaseOrder;
                                 $latestInspection = $item->latestInspectionItem;
                                @endphp
                                <tr class="transition-colors duration-200 hover:bg-emerald-50/70">
                                    <td class="px-5 py-4 align-top">
                                        <span class="block font-bold text-[#1a3a2d]">{{ $latestInspection?->ia_no ?? '—' }}</span>
                                        <span class="block text-xs text-gray-400">#{{ ($panelItems->currentPage() - 1) * $panelItems->perPage() + $loop->iteration }}</span>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <span class="font-semibold text-gray-700">{{ $purchaseOrder?->po_no ?? '—' }}</span>
                                        <span class="block text-xs text-gray-400">{{ optional($purchaseOrder?->created_at)->format('M d, Y') ?? 'No date' }}</span>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="max-w-xs truncate font-medium text-gray-800" title="{{ $item->item_description }}">{{ $item->item_description }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-wide text-gray-400">Unit: {{ $item->unit ?? '—' }}</div>
                                    </td>
                                    <td class="px-5 py-4 align-top">
                                        <div class="font-semibold text-gray-700">{{ $purchaseOrder?->supplier?->supplier_name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400">{{ $purchaseOrder?->supplier?->contact_person ?? '' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right font-mono font-semibold text-gray-800 align-top">{{ $latestInspection?->quantity_delivered ?? $item->quantity }}</td>
                                    <td class="px-5 py-4 text-right font-mono font-semibold text-emerald-600 align-top">{{ $latestInspection?->quantity_accepted ?? 0 }}</td>
                                    <td class="px-5 py-4 text-right font-mono font-semibold text-rose-600 align-top">{{ $latestInspection?->quantity_rejected ?? 0 }}</td>
                                    <td class="px-5 py-4 text-gray-600 align-top">{{ optional($latestInspection?->warranty_expiration)->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-600 align-top">
                                        <span class="block max-w-[150px] truncate" title="{{ $latestInspection?->inspection_remarks }}">{{ $latestInspection?->inspection_remarks ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center align-top">
                                        @if ($purchaseOrder && in_array($viewerRole, ['custodian', 'iac'], true))
                                            <button type="button"
                                                class="js-open-inspection flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2.5 font-semibold text-white shadow-md transition-all duration-300 hover:bg-opacity-90 hover:shadow-lg"
                                                data-form-url="{{ route('custodian.inspection.form', $purchaseOrder) }}"
                                                data-store-url="{{ route('custodian.inspection.store', $purchaseOrder) }}">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                <span class="text-xs">Inspect / Update</span>
                                            </button>
                                        @elseif ($purchaseOrder)
                                            <button type="button"
                                                class="js-open-inspection flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2.5 font-semibold text-gray-600 shadow-sm transition-all duration-300 hover:bg-gray-200"
                                                data-form-url="{{ route('custodian.inspection.form', $purchaseOrder) }}">
                                                <i class="fa-solid fa-eye"></i>
                                                <span class="text-xs">View Details</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-24 text-center">
                                        <div class="mx-auto max-w-xs">
                                            <i class="fa-regular fa-folder-open mb-4 text-5xl text-gray-300"></i>
                                            <h4 class="text-lg font-semibold text-gray-700">No Items Found</h4>
                                            <p class="text-sm text-gray-400">There are no items recorded under this status yet. Try adjusting your filters or checking other tabs.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- CORRECTED: Pagination Layout (Text Left, Links Right) --}}
                @if ($panelItems->hasPages())
                    <div class="mt-4 flex flex-col-reverse items-center justify-between gap-y-3 border-t border-gray-100 pt-4 sm:flex-row">
                        
                        {{-- Left Side: Results Text --}}
                        <div class="text-sm text-gray-500">
                            Showing <span class="font-semibold text-gray-700">{{ $panelItems->firstItem() }}</span>
                            to <span class="font-semibold text-gray-700">{{ $panelItems->lastItem() }}</span>
                            of <span class="font-semibold text-gray-700">{{ $panelTotal }}</span>
                            results
                        </div>
                        
                        {{-- Right Side: Pagination Links --}}
                        <div class="flex-shrink-0">
                            {{ $panelItems->onEachSide(1)->links() }}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

@include('custodian.inspection._modal')
@endsection

@push('scripts')
@vite('resources/js/inspection.js')
@endpush