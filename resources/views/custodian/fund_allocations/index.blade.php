@extends('layouts.app')

@section('title', 'Fund Allocations')

@section('content')
@php
    $kpis = $kpis ?? [
        'totalClusters' => $allocations->total(),
        'totalBudget' => 0,
        'totalAllocated' => 0,
        'totalRemaining' => 0,
        'utilizationRate' => 0,
    ];
@endphp

<style>
    .fund-refresh-prep {
        opacity: 0;
        transform: translateY(8px);
    }

    .fund-refresh-in {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 180ms ease, transform 180ms ease;
    }

    @keyframes fundShimmer {
        0% {
            background-position: -200% 0;
        }
        100% {
            background-position: 200% 0;
        }
    }

    .fund-skeleton-line {
        background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
        background-size: 200% 100%;
        animation: fundShimmer 1.1s ease-in-out infinite;
    }

    .fund-filter-row-two-wrap {
        overflow-x: auto;
    }

    .fund-filter-row-two {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.75rem;
        min-width: 960px;
    }

    .fund-filter-control {
        height: 3rem;
        width: 100%;
    }
</style>

<div class="space-y-7">
    <section class="animate-card relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Budget Control
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Fund Allocations</h2>
                <p class="text-sm text-white/75">Track and maintain funding clusters used in purchase request operations.</p>
            </div>

            <div id="fundAllocationKpis">
                @include('custodian.fund_allocations.partials.kpis', ['kpis' => $kpis])
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="animate-card rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
            <div class="flex items-center gap-2 text-sm font-semibold">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <section class="animate-card rounded-[26px] border border-emerald-950/8 bg-white/95 p-5 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
        <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-[#1a3a2d]">Budget Cluster Registry</h3>
                <p class="text-sm text-gray-500"><span id="fundAllocationCount" class="font-semibold text-gray-700">{{ number_format($allocations->total()) }}</span> records available</p>
            </div>
            <button id="openCreateModal" class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#1a3a2d] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg sm:w-auto">
                <i class="fas fa-plus-circle"></i>
                New Fund Allocation
            </button>
        </div>

        <div class="mb-4 rounded-2xl border border-emerald-950/8 bg-[#f8fbf9] p-4">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#1a3a2d]/75">Search and Filters</p>
                <p class="text-xs text-gray-500">Row 1: search. Row 2: utilization, dates, and actions.</p>
            </div>

            <div class="grid gap-3">
                <div class="relative">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                    <input type="search" id="fundAllocationSearch" value="{{ $search ?? '' }}" class="w-full rounded-2xl border border-emerald-950/10 bg-white py-3 pl-11 pr-4 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15" placeholder="Search cluster, creator, PO/PR no...">
                </div>

                <div class="fund-filter-row-two-wrap">
                    <div class="fund-filter-row-two">
                    <select id="fundAllocationUtilizationFilter" class="fund-filter-control rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15">
                        <option value="all" @selected(($utilizationFilter ?? 'all') === 'all')>All Utilization</option>
                        <option value="unused" @selected(($utilizationFilter ?? 'all') === 'unused')>Unused</option>
                        <option value="partial" @selected(($utilizationFilter ?? 'all') === 'partial')>Partially Utilized</option>
                        <option value="full" @selected(($utilizationFilter ?? 'all') === 'full')>Fully Utilized</option>
                        <option value="over" @selected(($utilizationFilter ?? 'all') === 'over')>Over Utilized</option>
                    </select>

                    <input type="date" id="fundAllocationDateFrom" value="{{ $dateFrom ?? '' }}" class="fund-filter-control rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15" title="Date from">

                    <input type="date" id="fundAllocationDateTo" value="{{ $dateTo ?? '' }}" class="fund-filter-control rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15" title="Date to">

                    <button type="button" id="fundAllocationPrintPdf" data-print-url="{{ route('custodian.fund_allocations.print.pdf') }}" class="fund-filter-control inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                        <i class="fas fa-file-pdf text-rose-600"></i>
                        <span>Print</span>
                    </button>

                    <button type="button" id="fundAllocationExportExcel" data-excel-url="{{ route('custodian.fund_allocations.export.excel') }}" class="fund-filter-control inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                        <i class="fas fa-file-excel text-emerald-600"></i>
                        <span>Excel</span>
                    </button>

                    <button type="button" id="fundAllocationResetFilters" class="fund-filter-control inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                        <i class="fas fa-rotate-left text-gray-500"></i>
                        <span>Reset</span>
                    </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="fundAllocationTable">
            @include('custodian.fund_allocations.partials.table', ['allocations' => $allocations])
        </div>
    </section>
</div>

<div id="fundAllocationToast" class="fixed right-6 top-6 z-[90] hidden rounded-xl bg-[#1a3a2d] px-5 py-3 text-sm font-semibold text-white shadow-lg"></div>

<div id="allocationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" aria-labelledby="modalTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="modal-panel relative flex w-full max-w-lg flex-col rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-6 py-4 text-white">
                <h3 class="text-xl font-bold" id="modalTitle">New Fund Allocation</h3>
                <button class="rounded-lg p-2 text-white/80 transition-all hover:bg-white/10 hover:text-white" data-close-modal data-focus>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="allocationForm" class="space-y-4 p-6">
                @csrf
                <input type="hidden" id="allocationId" name="allocation_id">
                <input type="hidden" id="formMethod" value="POST">

                <div id="formErrors" class="hidden rounded-lg border-l-4 border-red-500 bg-red-100 px-4 py-3 text-sm text-red-700">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                        <div class="flex-1" id="formErrorsContent"></div>
                    </div>
                </div>

                <div>
                    <label for="fundCluster" class="mb-2 block text-sm font-semibold text-gray-700">
                        Fund Cluster <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="fundCluster" name="fund_cluster"
                           class="w-full rounded-xl border border-emerald-950/15 bg-[#f8faf9] px-4 py-2.5 text-sm text-gray-800 focus:border-[#1a3a2d] focus:bg-white focus:ring-2 focus:ring-[#1a3a2d]/20"
                           placeholder="e.g., FY2025-GEN-001">
                    <p id="fundClusterError" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                </div>

                <div>
                    <label for="totalAmount" class="mb-2 block text-sm font-semibold text-gray-700">
                        Total Amount <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 font-semibold text-gray-500">₱</span>
                        <input type="number" id="totalAmount" name="total_amount" step="0.01" min="0"
                               class="w-full rounded-xl border border-emerald-950/15 bg-[#f8faf9] py-2.5 pl-8 pr-4 text-sm text-gray-800 focus:border-[#1a3a2d] focus:bg-white focus:ring-2 focus:ring-[#1a3a2d]/20"
                               placeholder="0.00">
                    </div>
                    <p id="totalAmountError" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                    <p class="mt-1 text-xs text-gray-500" id="allocatedNote"></p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50" data-close-modal>
                        Cancel
                    </button>
                    <button type="submit" data-loading-text="Saving allocation..." class="flex-1 rounded-xl bg-[#1a3a2d] px-4 py-2.5 font-semibold text-white transition hover:bg-[#204835]">
                        <span id="submitText">Create Allocation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteAllocationModal" class="fixed inset-0 z-[60] hidden opacity-0 transition-opacity duration-300" aria-labelledby="deleteAllocationTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-delete-modal></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="delete-modal-panel relative w-full max-w-md rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="border-b bg-gradient-to-r from-[#173628] to-[#2d5a4a] px-6 py-4 text-white">
                <h3 id="deleteAllocationTitle" class="text-lg font-bold tracking-tight">Delete Fund Allocation</h3>
                <p class="mt-1 text-sm text-white/80">This action cannot be undone.</p>
            </div>
            <div class="space-y-3 px-6 py-5">
                <p class="text-sm text-gray-600">You are about to delete this fund cluster:</p>
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" id="deleteAllocationClusterName">—</div>
            </div>
            <div class="flex gap-3 border-t border-gray-100 px-6 py-4">
                <button type="button" class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-50" data-close-delete-modal data-delete-focus>
                    Cancel
                </button>
                <button type="button" id="confirmDeleteAllocationBtn" class="flex-1 rounded-xl border border-red-300 bg-red-600 px-4 py-2.5 font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-70">
                    Delete Allocation
                </button>
            </div>
        </div>
    </div>
</div>

<div id="viewAllocationModal" class="fixed inset-0 z-[70] hidden opacity-0 transition-opacity duration-300" aria-labelledby="viewAllocationTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-view-modal></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="view-modal-panel relative flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between border-b bg-gradient-to-r from-[#173628] to-[#2d5a4a] px-6 py-4 text-white">
                <div>
                    <h3 id="viewAllocationTitle" class="text-lg font-bold tracking-tight">Fund Allocation Details</h3>
                    <p id="viewAllocationSubtitle" class="mt-1 text-xs text-white/80">Utilization summary and linked purchase orders</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-view-modal data-view-focus>
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="overflow-y-auto p-6">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Total Budget</p>
                        <p id="viewAllocationTotal" class="mt-1 text-lg font-bold text-gray-900">₱0.00</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Allocated</p>
                        <p id="viewAllocationAllocated" class="mt-1 text-lg font-bold text-amber-700">₱0.00</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Remaining</p>
                        <p id="viewAllocationRemaining" class="mt-1 text-lg font-bold text-emerald-700">₱0.00</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Utilization</p>
                        <p id="viewAllocationRate" class="mt-1 text-lg font-bold text-sky-700">0.0%</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 rounded-xl border border-gray-200 bg-[#f9fbfa] p-4 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Created By</p>
                        <p id="viewAllocationCreatedBy" class="mt-1 text-sm font-semibold text-gray-800">—</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Created At</p>
                        <p id="viewAllocationCreatedAt" class="mt-1 text-sm font-semibold text-gray-800">—</p>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-sm font-bold uppercase tracking-[0.14em] text-[#1a3a2d]">Utilization Details</h4>
                        <span id="viewAllocationRequestCount" class="rounded-full bg-[#e9f5ef] px-3 py-1 text-xs font-semibold text-[#1a3a2d]">0 linked PO</span>
                    </div>
                    <div id="viewAllocationRows" class="space-y-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const bootFundAllocationsPage = () => {
        const $ = window.jQuery || window.$;
        if (!$) {
            window.setTimeout(bootFundAllocationsPage, 50);
            return;
        }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const indexEndpoint = "{{ route('custodian.fund_allocations.index') }}";
    const $modal = $('#allocationModal');
    const $modalPanel = $modal.find('.modal-panel').first();
    const $deleteModal = $('#deleteAllocationModal');
    const $deleteModalPanel = $deleteModal.find('.delete-modal-panel').first();
    const $form = $('#allocationForm');
    const $errors = $('#formErrors');
    const $errorsContent = $('#formErrorsContent');
    const $toast = $('#fundAllocationToast');
    const $focusTarget = $modal.find('[data-focus]').first();
    const $kpiContainer = $('#fundAllocationKpis');
    const $tableContainer = $('#fundAllocationTable');
    const $recordCount = $('#fundAllocationCount');
    const $deleteClusterName = $('#deleteAllocationClusterName');
    const $confirmDeleteBtn = $('#confirmDeleteAllocationBtn');
    const $deleteFocusTarget = $deleteModal.find('[data-delete-focus]').first();
    const $viewModal = $('#viewAllocationModal');
    const $viewModalPanel = $viewModal.find('.view-modal-panel').first();
    const $viewFocusTarget = $viewModal.find('[data-view-focus]').first();
    const $viewSubtitle = $('#viewAllocationSubtitle');
    const $viewTotal = $('#viewAllocationTotal');
    const $viewAllocated = $('#viewAllocationAllocated');
    const $viewRemaining = $('#viewAllocationRemaining');
    const $viewRate = $('#viewAllocationRate');
    const $viewCreatedBy = $('#viewAllocationCreatedBy');
    const $viewCreatedAt = $('#viewAllocationCreatedAt');
    const $viewRows = $('#viewAllocationRows');
    const $viewRequestCount = $('#viewAllocationRequestCount');
    const $searchInput = $('#fundAllocationSearch');
    const $utilizationFilter = $('#fundAllocationUtilizationFilter');
    const $dateFromInput = $('#fundAllocationDateFrom');
    const $dateToInput = $('#fundAllocationDateTo');
    const $resetFiltersBtn = $('#fundAllocationResetFilters');
    const $printBtn = $('#fundAllocationPrintPdf');
    const $excelBtn = $('#fundAllocationExportExcel');
    const $fundCluster = $('#fundCluster');
    const $fundClusterError = $('#fundClusterError');
    const $totalAmount = $('#totalAmount');
    const $totalAmountError = $('#totalAmountError');
    const tableLoadingOverlayId = 'fundTableLoadingOverlay';
    let pendingDelete = null;
    let activeEditId = null;
    let filterDebounceTimer = null;

    const resolveFundUrl = (pathOrUrl = '') => {
        if (!pathOrUrl) {
            return indexEndpoint;
        }

        const raw = String(pathOrUrl).trim();
        if (/^https?:\/\//i.test(raw)) {
            return raw;
        }

        const normalizedPath = raw.startsWith('/') ? raw : `/${raw}`;
        const baseMatch = window.location.pathname.match(/^(.*?)(?=\/custodian(?:\/|$))/i);
        const basePath = baseMatch?.[1] ?? '';

        return `${window.location.origin}${basePath}${normalizedPath}`;
    };

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        timeout: 15000,
    });

    const showToast = (message, type = 'success') => {
        $toast
            .removeClass('hidden bg-[#1a3a2d] bg-red-600 bg-amber-600')
            .addClass(type === 'error' ? 'bg-red-600' : 'bg-[#1a3a2d]')
            .text(message);
        setTimeout(() => $toast.addClass('hidden'), 3000);
    };

    const getFilterParams = () => {
        const params = new URLSearchParams();

        const searchValue = String($searchInput.val() || '').trim();
        const utilizationValue = String($utilizationFilter.val() || 'all').trim();
        const dateFromValue = String($dateFromInput.val() || '').trim();
        const dateToValue = String($dateToInput.val() || '').trim();

        if (searchValue !== '') {
            params.set('search', searchValue);
        }

        if (utilizationValue !== '' && utilizationValue !== 'all') {
            params.set('utilization', utilizationValue);
        }

        if (dateFromValue !== '') {
            params.set('date_from', dateFromValue);
        }

        if (dateToValue !== '') {
            params.set('date_to', dateToValue);
        }

        return params;
    };

    const buildUrlWithFilters = (baseUrl) => {
        const target = new URL(resolveFundUrl(baseUrl || indexEndpoint));
        const params = getFilterParams();

        ['search', 'utilization', 'date_from', 'date_to'].forEach((key) => target.searchParams.delete(key));
        params.forEach((value, key) => target.searchParams.set(key, value));

        return target;
    };

    const buildTableLoadingOverlay = () => `
        <div id="${tableLoadingOverlayId}" class="absolute inset-0 z-20 bg-white/80 backdrop-blur-[1px] px-6 py-6">
            <div class="space-y-3 rounded-xl border border-gray-100 bg-white/95 p-4 shadow-sm">
                <div class="fund-skeleton-line h-3 w-36 rounded"></div>
                <div class="grid grid-cols-7 gap-3">
                    <div class="fund-skeleton-line h-3 rounded"></div>
                    <div class="fund-skeleton-line h-3 rounded"></div>
                    <div class="fund-skeleton-line h-3 rounded"></div>
                    <div class="fund-skeleton-line h-3 rounded"></div>
                    <div class="fund-skeleton-line h-3 rounded"></div>
                    <div class="fund-skeleton-line h-3 rounded"></div>
                    <div class="fund-skeleton-line h-3 rounded"></div>
                </div>
                <div class="space-y-2 pt-2">
                    <div class="fund-skeleton-line h-9 w-full rounded-lg"></div>
                    <div class="fund-skeleton-line h-9 w-full rounded-lg"></div>
                    <div class="fund-skeleton-line h-9 w-full rounded-lg"></div>
                    <div class="fund-skeleton-line h-9 w-full rounded-lg"></div>
                    <div class="fund-skeleton-line h-9 w-full rounded-lg"></div>
                </div>
            </div>
        </div>
    `;

    const setTableLoading = (isLoading) => {
        $tableContainer.toggleClass('opacity-60 relative', isLoading);

        if (isLoading) {
            if (!document.getElementById(tableLoadingOverlayId)) {
                $tableContainer.append(buildTableLoadingOverlay());
            }
            return;
        }

        $(`#${tableLoadingOverlayId}`).remove();
    };

    const animateRefresh = () => {
        const rowElements = $tableContainer.find('tr[data-fund-row]').toArray();
        rowElements.forEach((row, index) => {
            row.classList.add('fund-refresh-prep');
            setTimeout(() => {
                row.classList.add('fund-refresh-in');
                row.classList.remove('fund-refresh-prep');
            }, index * 35);
        });

        const kpiCards = $kpiContainer.children().first().children().toArray();
        kpiCards.forEach((card, index) => {
            card.classList.add('fund-refresh-prep');
            setTimeout(() => {
                card.classList.add('fund-refresh-in');
                card.classList.remove('fund-refresh-prep');
            }, index * 45);
        });
    };

    const fetchFundAllocations = (url = null) => {
        const target = new URL(resolveFundUrl(url || indexEndpoint));
        const activeFilters = getFilterParams();

        ['search', 'utilization', 'date_from', 'date_to'].forEach((key) => {
            target.searchParams.delete(key);
        });

        activeFilters.forEach((value, key) => {
            target.searchParams.set(key, value);
        });

        target.searchParams.set('ajax', '1');

        setTableLoading(true);

        $.ajax({
            method: 'GET',
            url: target.toString(),
            success: (response) => {
                if (response?.tableHtml) {
                    $tableContainer.html(response.tableHtml);
                }
                if (response?.kpisHtml) {
                    $kpiContainer.html(response.kpisHtml);
                }
                if (typeof response?.totalRecords === 'number') {
                    $recordCount.text(Number(response.totalRecords).toLocaleString('en-PH'));
                }

                animateRefresh();

                target.searchParams.delete('ajax');
                const nextQuery = target.searchParams.toString();
                const nextUrl = `${target.pathname}${nextQuery ? `?${nextQuery}` : ''}`;
                window.history.replaceState(null, '', nextUrl);
            },
            error: (xhr) => {
                const msg = xhr.responseJSON?.message || `Unable to refresh fund allocation data (HTTP ${xhr.status || 'ERR'}).`;
                showToast(msg, 'error');
            },
            complete: () => {
                setTableLoading(false);
                $tableContainer.removeClass('opacity-60');
                $(`#${tableLoadingOverlayId}`).remove();
            }
        });
    };

    const toggleModal = ($el, show) => {
        if (show) {
            $el.removeClass('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    $el.scrollTop(0);
                    if ($modalPanel.length) {
                        $modalPanel.scrollTop(0);
                    }
                    $el.removeClass('opacity-0').addClass('opacity-100');
                    $modalPanel.removeClass('opacity-0 scale-95 translate-y-2');
                });
            });
            setTimeout(() => {
                const focusEl = $focusTarget.get(0);
                if (focusEl) {
                    try {
                        focusEl.focus({ preventScroll: true });
                    } catch (error) {
                        focusEl.focus();
                    }
                }
            }, 300);
            document.body.classList.add('overflow-hidden');
        } else {
            $el.removeClass('opacity-100').addClass('opacity-0');
            $modalPanel.addClass('opacity-0 scale-95 translate-y-2');
            setTimeout(() => $el.addClass('hidden'), 300);
            document.body.classList.remove('overflow-hidden');
        }
    };

    const toggleDeleteModal = (show) => {
        if (show) {
            $deleteModal.removeClass('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    $deleteModal.removeClass('opacity-0').addClass('opacity-100');
                    $deleteModalPanel.removeClass('opacity-0 scale-95 translate-y-2');
                });
            });
            setTimeout(() => {
                const focusEl = $deleteFocusTarget.get(0);
                if (focusEl) {
                    try {
                        focusEl.focus({ preventScroll: true });
                    } catch (error) {
                        focusEl.focus();
                    }
                }
            }, 300);
            document.body.classList.add('overflow-hidden');
            return;
        }

        $deleteModal.removeClass('opacity-100').addClass('opacity-0');
        $deleteModalPanel.addClass('opacity-0 scale-95 translate-y-2');
        setTimeout(() => $deleteModal.addClass('hidden'), 300);
        pendingDelete = null;
        if ($modal.hasClass('hidden')) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    const toggleViewModal = (show) => {
        if (show) {
            $viewModal.removeClass('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    $viewModal.removeClass('opacity-0').addClass('opacity-100');
                    $viewModalPanel.removeClass('opacity-0 scale-95 translate-y-2');
                });
            });
            setTimeout(() => {
                const focusEl = $viewFocusTarget.get(0);
                if (focusEl) {
                    try {
                        focusEl.focus({ preventScroll: true });
                    } catch (error) {
                        focusEl.focus();
                    }
                }
            }, 280);
            document.body.classList.add('overflow-hidden');
            return;
        }

        $viewModal.removeClass('opacity-100').addClass('opacity-0');
        $viewModalPanel.addClass('opacity-0 scale-95 translate-y-2');
        setTimeout(() => $viewModal.addClass('hidden'), 300);
        if ($modal.hasClass('hidden') && $deleteModal.hasClass('hidden')) {
            document.body.classList.remove('overflow-hidden');
        }
    };

    const formatMoney = (value) => `₱${Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    const renderUtilizationRows = (rows = []) => {
        $viewRows.empty();

        if (!Array.isArray(rows) || rows.length === 0) {
            $viewRows.append('<div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-5 text-sm text-gray-500">No purchase orders currently utilizing this fund allocation.</div>');
            return;
        }

        rows.forEach((row) => {
            const properties = Array.isArray(row?.properties) ? row.properties : [];
            const propertiesHtml = properties.length
                ? properties.map((property) => {
                    const description = String(property?.description || '—');
                    const qty = Number(property?.quantity || 0);
                    const unit = String(property?.unit || '').trim();
                    const unitLabel = unit !== '' ? unit : 'unit';
                    const totalCost = Number(property?.ordered_total_cost || 0);
                    return `
                        <li class="rounded-lg border border-gray-200 bg-white px-3 py-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800">${description}</p>
                                <span class="text-xs font-semibold text-gray-600">${qty} ${unitLabel}</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Ordered total: ${formatMoney(totalCost)}</p>
                        </li>
                    `;
                }).join('')
                : '<li class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-500">No property/item details available.</li>';

            const card = `
                <article class="rounded-xl border border-emerald-950/10 bg-[#fbfdfc] p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-[#1a3a2d]">${String(row?.po_no || '—')}</p>
                            <p class="text-xs text-gray-500">PR: ${String(row?.pr_no || '—')} | Requester: ${String(row?.requester || '—')}</p>
                            <p class="text-xs text-gray-500">Supplier: ${String(row?.supplier || '—')} | Status: ${String(row?.status || 'Unknown')}</p>
                            <p class="text-xs text-gray-500">Ordered: ${String(row?.ordered_at || '—')}</p>
                        </div>
                        <p class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">${formatMoney(row?.ordered_amount || 0)}</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Ordered Properties/Items</p>
                        <ul class="mt-2 space-y-2">${propertiesHtml}</ul>
                    </div>
                </article>
            `;

            $viewRows.append(card);
        });
    };

    const clearFieldError = ($field, $error) => {
        if (!$field?.length || !$error?.length) {
            return;
        }

        $field.removeClass('border-red-400 ring-2 ring-red-100 focus:border-red-500 focus:ring-red-100');
        $field.attr('aria-invalid', 'false');
        $error.text('').addClass('hidden');
    };

    const setFieldError = ($field, $error, message) => {
        if (!$field?.length || !$error?.length) {
            return;
        }

        $field.addClass('border-red-400 ring-2 ring-red-100 focus:border-red-500 focus:ring-red-100');
        $field.attr('aria-invalid', 'true');
        $error.text(message || 'Invalid value.').removeClass('hidden');
    };

    const clearInlineErrors = () => {
        clearFieldError($fundCluster, $fundClusterError);
        clearFieldError($totalAmount, $totalAmountError);
    };

    const validateFundCluster = () => {
        const value = String($fundCluster.val() || '').trim();
        if (!value) {
            setFieldError($fundCluster, $fundClusterError, 'Fund Cluster is required.');
            return false;
        }
        if (value.length > 100) {
            setFieldError($fundCluster, $fundClusterError, 'Fund Cluster must not exceed 100 characters.');
            return false;
        }

        clearFieldError($fundCluster, $fundClusterError);
        return true;
    };

    const validateTotalAmount = () => {
        const rawValue = String($totalAmount.val() || '').trim();
        if (!rawValue) {
            setFieldError($totalAmount, $totalAmountError, 'Total Amount is required.');
            return false;
        }

        const numericValue = Number(rawValue);
        if (!Number.isFinite(numericValue) || numericValue < 0) {
            setFieldError($totalAmount, $totalAmountError, 'Total Amount must be a valid number greater than or equal to 0.');
            return false;
        }

        clearFieldError($totalAmount, $totalAmountError);
        return true;
    };

    const validateAllocationForm = () => {
        const clusterOk = validateFundCluster();
        const amountOk = validateTotalAmount();
        return clusterOk && amountOk;
    };

    const renderServerErrors = (messages = []) => {
        const safeMessages = Array.isArray(messages) ? messages : [];
        $errorsContent.empty();

        safeMessages.forEach((message) => {
            const line = $('<div/>', {
                class: 'mb-1',
                text: `• ${String(message ?? '')}`,
            });
            $errorsContent.append(line);
        });
    };

    $fundCluster.on('input blur', validateFundCluster);
    $totalAmount.on('input blur', validateTotalAmount);

    $(document)
        .off('click.fundCreate', '#openCreateModal')
        .on('click.fundCreate', '#openCreateModal', () => {
        $('#modalTitle').text('New Fund Allocation');
        $('#submitText').text('Create Allocation');
        $('#formMethod').val('POST');
        $('#allocationId').val('');
        $form.removeData('allocationId');
        activeEditId = null;
        $('#allocatedNote').text('');
        $form[0].reset();
        $errors.addClass('hidden');
        clearInlineErrors();

        // Fetch suggested fund cluster code and prefill
        $.ajax({
            method: 'GET',
            url: resolveFundUrl('/custodian/fund-allocations/suggest'),
            success: (resp) => {
                if (resp && resp.suggested_code) {
                    $('#fundCluster').val(resp.suggested_code);
                }
                toggleModal($modal, true);
            },
            error: () => {
                toggleModal($modal, true);
            }
        });
    });

    const scheduleFilterFetch = (delay = 280) => {
        if (filterDebounceTimer) {
            clearTimeout(filterDebounceTimer);
        }

        filterDebounceTimer = setTimeout(() => {
            fetchFundAllocations(indexEndpoint);
        }, delay);
    };

    $searchInput.on('input', () => scheduleFilterFetch(320));
    $searchInput.on('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        if (filterDebounceTimer) {
            clearTimeout(filterDebounceTimer);
        }
        fetchFundAllocations(indexEndpoint);
    });

    $utilizationFilter.on('change', () => fetchFundAllocations(indexEndpoint));
    $dateFromInput.on('change', () => fetchFundAllocations(indexEndpoint));
    $dateToInput.on('change', () => fetchFundAllocations(indexEndpoint));

    $resetFiltersBtn.on('click', () => {
        $searchInput.val('');
        $utilizationFilter.val('all');
        $dateFromInput.val('');
        $dateToInput.val('');
        fetchFundAllocations(indexEndpoint);
    });

    $printBtn.on('click', () => {
        const base = $printBtn.data('print-url');
        const target = buildUrlWithFilters(base);
        window.open(target.toString(), '_blank');
    });

    $excelBtn.on('click', () => {
        const base = $excelBtn.data('excel-url');
        const target = buildUrlWithFilters(base);
        window.location.href = target.toString();
    });

    $modal.on('click', '[data-close-modal]', () => toggleModal($modal, false));
    $deleteModal.on('click', '[data-close-delete-modal]', () => toggleDeleteModal(false));
    $viewModal.on('click', '[data-close-view-modal]', () => toggleViewModal(false));

    $(document)
        .off('click.fundView', '.view-allocation')
        .on('click.fundView', '.view-allocation', function () {
            const id = String($(this).data('id') || '').trim();
            if (!id) {
                showToast('Unable to open details: missing allocation reference.', 'error');
                return;
            }

            $viewSubtitle.text('Loading utilization details...');
            $viewTotal.text('₱0.00');
            $viewAllocated.text('₱0.00');
            $viewRemaining.text('₱0.00');
            $viewRate.text('0.0%');
            $viewCreatedBy.text('—');
            $viewCreatedAt.text('—');
            $viewRequestCount.text('0 linked PO');
            $viewRows.html('<div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-5 text-sm text-gray-500">Loading...</div>');
            toggleViewModal(true);

            $.ajax({
                method: 'GET',
                url: resolveFundUrl(`/custodian/fund-allocations/${id}`),
                success: (response) => {
                    const data = response?.data || {};
                    $viewSubtitle.text(`Fund Cluster: ${String(data.fund_cluster || '—')}`);
                    $viewTotal.text(formatMoney(data.total_amount || 0));
                    $viewAllocated.text(formatMoney(data.allocated_amount || 0));
                    $viewRemaining.text(formatMoney(data.remaining_amount || 0));
                    $viewRate.text(`${Number(data.utilization_rate || 0).toLocaleString('en-PH', { minimumFractionDigits: 1, maximumFractionDigits: 1 })}%`);
                    $viewCreatedBy.text(String(data.created_by || '—'));
                    $viewCreatedAt.text(String(data.created_at || '—'));
                    $viewRequestCount.text(`${Number(data.po_count || 0).toLocaleString('en-PH')} linked PO`);
                    renderUtilizationRows(data.utilization_rows || []);
                },
                error: (xhr) => {
                    toggleViewModal(false);
                    showToast(xhr.responseJSON?.message || 'Unable to load fund allocation details.', 'error');
                },
            });
        });

    $(document).on('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        if (!$viewModal.hasClass('hidden')) {
            toggleViewModal(false);
            return;
        }

        if (!$deleteModal.hasClass('hidden')) {
            toggleDeleteModal(false);
            return;
        }

        if (!$modal.hasClass('hidden')) {
            toggleModal($modal, false);
        }
    });

    $(document)
        .off('click.fundEdit', '.edit-allocation')
        .on('click.fundEdit', '.edit-allocation', function() {
        const id = $(this).data('id');
        const cluster = $(this).data('cluster');
        const total = $(this).data('total');
        const allocated = $(this).data('allocated');

        $('#modalTitle').text('Edit Fund Allocation');
        $('#submitText').text('Update Allocation');
        $('#formMethod').val('PUT');
        const normalizedId = String(id || '').trim();
        $('#allocationId').val(normalizedId);
        $form.data('allocationId', normalizedId);
        activeEditId = normalizedId;
        $('#fundCluster').val(cluster);
        $('#totalAmount').val(total);
        $('#allocatedNote').text(`Already allocated: ₱${parseFloat(allocated).toLocaleString('en-PH', {minimumFractionDigits: 2})}`);
        $errors.addClass('hidden');
        clearInlineErrors();
        toggleModal($modal, true);
    });

    $(document)
        .off('click.fundDelete', '.delete-allocation')
        .on('click.fundDelete', '.delete-allocation', function() {
        const id = $(this).data('id');
        const cluster = $(this).data('cluster');

        pendingDelete = { id, cluster };
        $deleteClusterName.text(cluster || 'Unknown Cluster');
        toggleDeleteModal(true);
    });

    $confirmDeleteBtn.on('click', () => {
        if (!pendingDelete?.id) {
            toggleDeleteModal(false);
            return;
        }

        const originalLabel = $confirmDeleteBtn.text();
        $confirmDeleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...');

        $.ajax({
            method: 'DELETE',
            url: resolveFundUrl(`/custodian/fund-allocations/${pendingDelete.id}`),
            success: (response) => {
                toggleDeleteModal(false);
                showToast(response.message ?? 'Fund allocation deleted successfully.');
                fetchFundAllocations(window.location.href);
            },
            error: (xhr) => {
                toggleDeleteModal(false);
                showToast(xhr.responseJSON?.message || 'Failed to delete allocation.', 'error');
            },
            complete: () => {
                $confirmDeleteBtn.prop('disabled', false).text(originalLabel);
            }
        });
    });

    $tableContainer.on('click', '[data-fund-pagination] a', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (!href) {
            return;
        }
        fetchFundAllocations(href);
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        $errors.addClass('hidden');
        clearInlineErrors();

        if (!validateAllocationForm()) {
            showToast('Please correct the highlighted fields before saving.', 'error');
            return;
        }

        const method = $('#formMethod').val();
        const id = String(activeEditId || $('#allocationId').val() || $form.data('allocationId') || '').trim();

        if (method === 'PUT' && !id) {
            showToast('Unable to update: missing allocation reference. Please reopen Edit and try again.', 'error');
            return;
        }

        const url = method === 'PUT'
            ? resolveFundUrl(`/custodian/fund-allocations/${id}`)
            : resolveFundUrl('/custodian/fund-allocations');

        const submitBtn = $form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');

        $.ajax({
            method: method,
            url: url,
            data: $form.serialize(),
            success: (response) => {
                toggleModal($modal, false);
                showToast(response.message ?? 'Fund allocation saved successfully.');
                fetchFundAllocations(window.location.href);
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const serverErrors = xhr.responseJSON.errors;
                    const messages = Object.values(serverErrors).flat();

                    if (serverErrors.fund_cluster?.[0]) {
                        setFieldError($fundCluster, $fundClusterError, serverErrors.fund_cluster[0]);
                    }
                    if (serverErrors.total_amount?.[0]) {
                        setFieldError($totalAmount, $totalAmountError, serverErrors.total_amount[0]);
                    }

                    renderServerErrors(messages);
                    $errors.removeClass('hidden');
                } else {
                    const message = xhr.responseJSON?.message || `An error occurred while saving allocation (HTTP ${xhr.status || 'ERR'}).`;
                    showToast(message, 'error');
                }
            },
            complete: () => {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootFundAllocationsPage, { once: true });
        return;
    }

    bootFundAllocationsPage();
})();
</script>
@endpush
@endsection
