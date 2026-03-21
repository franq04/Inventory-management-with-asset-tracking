@extends('layouts.app')

@section('title', 'Inventory & Property Assignment')

@section('content')
<div id="inventoryAssignmentPage" class="relative space-y-8 transition-all duration-200 ease-out">
    <div class="animate-card rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10 overflow-hidden relative">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.18),_transparent_60%)]"></div>
        <div class="absolute -right-8 bottom-0 h-32 w-32 rounded-full border border-white/10 bg-white/5 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Inventory Assignment
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">Inventory &amp; Property Assignment</h1>
                    <p class="text-sm text-white/75 md:max-w-2xl">Finalize accepted items by creating PQS records and tracking custodial documents.</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[460px]">
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Total Accepted</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Ready for PQS</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($stats['readyForPqs']) }}</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Recorded in PQS</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($stats['recordedInPqs']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-4 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-6 space-y-5">
        <div class="flex flex-col gap-4 border-b border-gray-100 pb-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Assignment Queue</p>
                <h2 class="mt-1 text-xl font-bold text-[#1a3a2d]">Filter and process properties</h2>
                <p class="mt-1 text-sm text-gray-500">Jump between status segments, narrow by text or date, and generate PQS records.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @php
                    $tabs = [
                        'all' => ['label' => 'All Items', 'icon' => 'fa-list'],
                        'pending' => ['label' => 'Ready for PQS', 'icon' => 'fa-clock'],
                        'recorded' => ['label' => 'Recorded in PQS', 'icon' => 'fa-check-circle'],
                    ];
                    $activeTabClass = 'bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d] is-selected';
                    $inactiveTabClass = 'border-gray-200 bg-[#fbfcfb] text-gray-600 hover:bg-gray-100 hover:border-gray-300';
                @endphp

                <input type="hidden" id="inventoryState" value="all">

                @foreach ($tabs as $key => $meta)
                    <button type="button"
                        class="inventory-tab group inline-flex min-h-[44px] items-center gap-2.5 rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200 {{ $key === 'all' ? $activeTabClass : $inactiveTabClass }}"
                        data-inventory-tab="{{ $key }}"
                        data-active-class="bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d] is-selected"
                        data-inactive-class="border-gray-200 bg-[#fbfcfb] text-gray-600 hover:bg-gray-100 hover:border-gray-300">
                        <i class="fa-solid {{ $meta['icon'] }} text-xs"></i>
                        <span>{{ $meta['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-center">
                <div class="relative w-full flex-grow md:max-w-sm">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                    <input id="inventorySearch" type="search" class="w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-4 py-3 text-sm text-gray-700 shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10 transition" placeholder="Search item, IA number, or PO number..." aria-label="Search properties">
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <input id="date_from" type="date" class="rounded-2xl border border-emerald-950/10 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="Start Date" value="{{ request('date_from') }}">
                    <span class="text-gray-400">-</span>
                    <input id="date_to" type="date" class="rounded-2xl border border-emerald-950/10 bg-white px-3 py-2.5 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="End Date" value="{{ request('date_to') }}">
                    <button id="inventoryApplyDate" type="button" class="flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d] text-[#1a3a2d] shadow-sm transition-all hover:bg-[#1a3a2d] hover:text-white hover:shadow-md" title="Apply Date Filter">
                        <i class="fa-solid fa-filter text-sm"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="inventoryPrintPdfBtn" data-print-url="{{ route('custodian.inventory.print.pdf') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                    <i class="fa-solid fa-file-pdf text-rose-600"></i>
                    <span>Print PDF</span>
                </button>
                <button type="button" id="inventoryExportExcelBtn" data-excel-url="{{ route('custodian.inventory.export.excel') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                    <i class="fa-solid fa-file-excel text-green-600"></i>
                    <span>Export Excel</span>
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#fbfcfb] px-5 py-4">
                <div class="flex items-center gap-4 text-xs font-semibold">
                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700 shadow-sm" title="Pending">
                        <i class="fas fa-clock"></i>
                        <span id="inventoryPendingCount">0</span>
                        <span class="text-[11px] font-semibold uppercase tracking-wide">Pending</span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700 shadow-sm" title="Recorded">
                        <i class="fas fa-check-circle"></i>
                        <span id="inventoryRecordedCount">0</span>
                        <span class="text-[11px] font-semibold uppercase tracking-wide">Recorded</span>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto relative">
                <table class="min-w-full text-sm">
                    <thead class="bg-[#f5f8f6] text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-3 text-left">Item Description</th>
                            <th class="whitespace-nowrap px-5 py-3 text-left">Category</th>
                            <th class="whitespace-nowrap px-5 py-3 text-left">Sub-category</th>
                            <th class="whitespace-nowrap px-5 py-3 text-right">Qty</th>
                            <th class="whitespace-nowrap px-5 py-3 text-right">Unit Cost</th>
                            <th class="whitespace-nowrap px-5 py-3 text-right">Total Cost</th>
                            <th class="whitespace-nowrap px-5 py-3 text-left">Source</th>
                            <th class="whitespace-nowrap px-5 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryItemsBody" class="relative divide-y divide-gray-100/90">
                        {{-- Initial Loading State --}}
                        <tr>
                            <td colspan="8" class="px-5 py-20 text-center text-gray-500">
                                <i class="fas fa-spinner fa-pulse mb-4 text-3xl text-gray-300"></i>
                                <p class="font-medium text-lg">Loading accepted items...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="hidden border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <p id="pageInfo" class="text-sm font-medium text-gray-600">
                        Showing <span class="font-semibold text-gray-900">1</span> to <span class="font-semibold text-gray-900">10</span> of <span class="font-semibold text-gray-900">0</span> results
                    </p>
                    <div class="flex flex-wrap items-center justify-start gap-2 lg:justify-end">
                        <p id="pageMeta" class="text-sm font-medium text-gray-600"></p>
                        <nav id="pageLinks" class="flex flex-wrap items-center justify-start gap-2 lg:justify-end" aria-label="Pagination Navigation"></nav>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('custodian.inventory.partials._modals')
@endsection

@push('scripts')
@php
    $categoriesData = $categories->map(function ($parent) {
        return [
            'id' => $parent->cat_id,
            'name' => $parent->cat_name,
            'children' => $parent->children->map(function ($child) {
                return [
                    'id' => $child->cat_id,
                    'name' => $child->cat_name,
                ];
            })->values(),
        ];
    })->values();
@endphp
<script>
    window.inventoryAssignmentConfig = {
        routes: {
            items: @json(route('custodian.inventory.items')),
            show: @json(route('custodian.inventory.show', ['inspection_report_item' => '__ID__'])),
            store: @json(route('custodian.inventory.store', ['inspection_report_item' => '__ID__'])),
        },
        categories: @json($categoriesData),
        locations: @json($locations),
    };
</script>
@vite('resources/js/inventory-assignment.js')
@endpush