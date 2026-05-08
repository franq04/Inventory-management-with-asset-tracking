@extends('layouts.app')

@section('title', 'Inspection & Acceptance')

@section('content')
@php
    $statusMeta = [
        'pending' => [
            'label' => 'Pending Inspection',
            'description' => 'Items delivered but awaiting final inspection results.',
            'icon' => 'fa-clock',
            'icon_wrap' => 'bg-amber-100 text-amber-600',
            'badge' => 'bg-amber-50 text-amber-700',
            'row_badge' => 'bg-amber-50 text-amber-700 border-amber-200',
        ],
        'accepted' => [
            'label' => 'Accepted / Recorded',
            'description' => 'Items accepted or already recorded to property ledgers.',
            'icon' => 'fa-circle-check',
            'icon_wrap' => 'bg-emerald-100 text-emerald-600',
            'badge' => 'bg-emerald-50 text-emerald-700',
            'row_badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        'defective' => [
            'label' => 'Defective',
            'description' => 'Items discovered with defects during inspection.',
            'icon' => 'fa-triangle-exclamation',
            'icon_wrap' => 'bg-rose-100 text-rose-600',
            'badge' => 'bg-rose-50 text-rose-700',
            'row_badge' => 'bg-rose-50 text-rose-700 border-rose-200',
        ],
        'returned' => [
            'label' => 'Returned / Replacement',
            'description' => 'Items returned to supplier for action or replacement.',
            'icon' => 'fa-rotate-left',
            'icon_wrap' => 'bg-sky-100 text-sky-600',
            'badge' => 'bg-sky-50 text-sky-700',
            'row_badge' => 'bg-sky-50 text-sky-700 border-sky-200',
        ],
    ];
    $viewerRole = auth()->user()->role ?? null;
    $totalItems = collect($itemsByStatus)->sum(fn ($set) => $set->total());
    $visibleItems = collect($itemsByStatus)->sum(fn ($set) => $set->count());
    $activeTabKey = collect(array_keys($statusMeta))->first(function ($key) {
        return request()->has($key.'_page');
    }) ?? 'pending';
@endphp
<div id="inspectionAcceptancePage" class="relative space-y-8 transition-all duration-200 ease-out">
    <div class="animate-card rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10 overflow-hidden relative">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.18),_transparent_60%)]"></div>
        <div class="absolute -right-8 bottom-0 h-32 w-32 rounded-full border border-white/10 bg-white/5 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Inspection Queue
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Inspection & Acceptance Reports</h1>
                    <p class="text-sm text-white/75 md:max-w-2xl">Review received items and track their acceptance status across categories with queue-first visibility.</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[420px] lg:max-w-2xl">
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Total Queue</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($totalItems) }}</p>
                    <p class="mt-1 text-xs text-white/65">All inspection records</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Visible Rows</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($visibleItems) }}</p>
                    <p class="mt-1 text-xs text-white/65">Records on this page</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Active Status</p>
                    <p class="mt-2 text-xl font-bold" data-active-status-label>{{ $statusMeta[$activeTabKey]['label'] ?? 'Pending Inspection' }}</p>
                    <p class="mt-1 text-xs text-white/65">Current review segment</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-4 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-6 space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Queue Filters</p>
                <h2 class="mt-1 text-xl font-bold text-[#1a3a2d]">Filter and process inspection records</h2>
                <p class="mt-1 text-sm text-gray-500">Jump between status segments, narrow by text or date, and continue with inspection actions from one unified table view.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach ($statusMeta as $key => $meta)
                    @php
                        $isActive = $activeTabKey === $key;
                        $activeTabClasses = 'bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d]';
                        $inactiveTabClasses = 'border-gray-200 bg-[#fbfcfb] text-gray-600 hover:bg-gray-100 hover:border-gray-300';
                        $activeCountClasses = 'bg-white/20 text-white';
                        $inactiveCountClasses = 'bg-gray-200 text-gray-700 group-hover:bg-gray-300';
                    @endphp
                    <button type="button"
                        class="inspection-tab group inline-flex min-h-[44px] items-center gap-2.5 rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200
                            {{ $isActive ? $activeTabClasses.' is-selected' : $inactiveTabClasses }}"
                        data-inspection-tab="{{ $key }}"
                        data-active-class="{{ $activeTabClasses }}"
                        data-inactive-class="{{ $inactiveTabClasses }}"
                        data-count-active-class="{{ $activeCountClasses }}"
                        data-count-inactive-class="{{ $inactiveCountClasses }}">
                        <i class="fa-solid {{ $meta['icon'] }} text-xs"></i>
                        <span>{{ $meta['label'] }}</span>
                        <span class="inspection-tab-count rounded-full px-2 py-0.5 text-xs font-bold {{ $isActive ? $activeCountClasses : $inactiveCountClasses }}">
                            {{ $itemsByStatus[$key]->total() }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
                <div class="relative w-full flex-grow md:max-w-sm">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                    <input id="inspectionSearch" type="search" class="w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-4 py-3 text-sm text-gray-700 shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10 transition" placeholder="Search IAR, PO, Item..." aria-label="Search inspections">
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <input id="inspectionDateFrom" type="date" class="rounded-2xl border border-emerald-950/10 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="Start Date">
                    <span class="text-gray-400">-</span>
                    <input id="inspectionDateTo" type="date" class="rounded-2xl border border-emerald-950/10 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="End Date">
                    <button id="inspectionApplyDateFilter" type="button" class="flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d] text-[#1a3a2d] shadow-sm transition-all hover:bg-[#1a3a2d] hover:text-white hover:shadow-md" title="Apply Date Filter">
                        <i class="fa-solid fa-filter text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button id="inspectionPrintPdfBtn" data-print-url="{{ route('custodian.inspection.print.pdf') }}" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                    <i class="fa-solid fa-file-pdf text-rose-600"></i>
                    <span>Print PDF</span>
                </button>
                <button id="inspectionPrintExcelBtn" data-excel-url="{{ route('custodian.inspection.export.excel') }}" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                    <i class="fa-solid fa-file-excel text-green-600"></i>
                    <span>Export Excel</span>
                </button>
            </div>
        </div>

        @foreach ($statusMeta as $key => $meta)
            @php
                $panelItems = $itemsByStatus[$key];
                $panelTotal = $panelItems->total();
            @endphp
            <div class="inspection-panel {{ $activeTabKey === $key ? '' : 'hidden' }}" data-inspection-panel="{{ $key }}">
                <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#fbfcfb] px-5 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-[#1a3a2d]">{{ $meta['label'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $meta['description'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold border {{ $meta['row_badge'] }}">
                            <i class="fa-solid {{ $meta['icon'] }}"></i>
                            {{ $panelTotal }} item(s) found
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-[#f5f8f6] text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                                <tr>
                                    <th class="whitespace-nowrap px-5 py-3 text-left">IAR No.</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-left">PO No.</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-left">Item Description</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-left">Supplier</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-right">Delivered</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-right">Accepted</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-right">Rejected</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-left">Remarks</th>
                                    <th class="whitespace-nowrap px-5 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($panelItems as $item)
                                    @php
                                        $purchaseOrder = $item->purchaseOrder;
                                        $latestInspection = $item->latestInspectionItem;
                                    @endphp
                                    <tr class="border-b border-gray-100/90 last:border-0 hover:bg-[#f6fbf8] transition-colors duration-200">
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
                                        <td class="px-5 py-4 align-top text-right font-mono font-semibold text-gray-800">{{ $latestInspection?->quantity_delivered ?? $item->quantity }}</td>
                                        <td class="px-5 py-4 align-top text-right font-mono font-semibold text-emerald-600">{{ $latestInspection?->quantity_accepted ?? 0 }}</td>
                                        <td class="px-5 py-4 align-top text-right font-mono font-semibold text-rose-600">{{ $latestInspection?->quantity_rejected ?? 0 }}</td>
                                        <td class="px-5 py-4 align-top text-gray-600">
                                            <span class="block max-w-[160px] truncate" title="{{ $latestInspection?->inspection_remarks }}">{{ $latestInspection?->inspection_remarks ?? '—' }}</span>
                                        </td>
                                        <td class="px-5 py-4 align-top text-center">
                                            @if ($purchaseOrder && in_array($viewerRole, ['custodian', 'iac', 'admin'], true))
                                                <button type="button"
                                                    class="js-open-inspection inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg"
                                                    data-form-url="{{ route('custodian.inspection.form', $purchaseOrder) }}"
                                                    data-store-url="{{ route('custodian.inspection.store', $purchaseOrder) }}">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                    Inspect / Update
                                                </button>
                                            @elseif ($purchaseOrder)
                                                <button type="button"
                                                    class="js-open-inspection inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-100"
                                                    data-form-url="{{ route('custodian.inspection.form', $purchaseOrder) }}">
                                                    <i class="fa-solid fa-eye"></i>
                                                    View Details
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-5 py-20 text-center text-gray-500">
                                            <i class="fa-regular fa-folder-open mb-4 text-5xl text-gray-300"></i>
                                            <p class="font-medium text-lg">No inspection items found.</p>
                                            <p class="text-sm">Try adjusting your filters or switch to another status tab.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-100 bg-[#fbfcfb] px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500">
                            Showing
                            <span class="font-semibold text-gray-700">{{ number_format($panelItems->firstItem() ?? 0) }}</span>
                            to
                            <span class="font-semibold text-gray-700">{{ number_format($panelItems->lastItem() ?? 0) }}</span>
                            of
                            <span class="font-semibold text-gray-700">{{ number_format($panelTotal) }}</span>
                            results
                        </p>
                        <div>
                            {{ $panelItems->onEachSide(1)->links('vendor.pagination.procurement') }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('custodian.inspection._modal')
@endsection

@push('scripts')
@vite('resources/js/inspection.js')
@endpush