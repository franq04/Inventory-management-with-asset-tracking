@extends('layouts.app')

@section('title', 'PQS Records')

@section('content')
@php
    $completion = ($stats['total'] > 0) ? (($stats['total'] - $stats['unassigned']) / max($stats['total'], 1)) * 100 : 0;
@endphp
<style>
    .pqs-filter-row-two-wrap {
        overflow: visible;
    }

    .pqs-filter-row-two {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.75rem;
        min-width: 0;
    }

    .pqs-filter-control {
        height: 3rem;
        width: 100%;
    }

    .pqs-filter-label {
        display: block;
        min-height: 1rem;
        white-space: nowrap;
    }

    .pqs-filter-actions-row {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 0.75rem;
    }

    @media (min-width: 768px) {
        .pqs-filter-row-two {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .pqs-filter-actions-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1280px) {
        .pqs-filter-row-two {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .pqs-filter-actions-row {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
</style>
<div id="pqsRegistryPage" class="space-y-8 animate-card">
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Inventory Records
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">PQS Records</h2>
                <p class="text-sm text-white/75">View and monitor generated PQS records. New PQS creation is handled in Inventory Assignment.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 lg:min-w-[620px]">
        @php
            $statCards = [
                ['label' => 'Total Items', 'value' => $stats['total']],
                ['label' => 'With ICS', 'value' => $stats['withIcs']],
                ['label' => 'With PAR', 'value' => $stats['withPar']],
                ['label' => 'Awaiting Assignment', 'value' => $stats['unassigned']],
            ];
        @endphp
        @foreach ($statCards as $card)
        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">{{ $card['label'] }}</p>
            <p class="mt-2 text-3xl font-bold">{{ number_format($card['value']) }}</p>
        </div>
        @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content Column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Enhanced Filters & Actions --}}
            <div class="rounded-[26px] border border-emerald-950/8 bg-white/95 p-5 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
                <form id="pqsFiltersForm" method="GET" class="space-y-4" action="{{ route('pqs.index') }}">
                    <div class="rounded-2xl border border-emerald-950/8 bg-[#f8fbf9] p-4">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#1a3a2d]/75">Search and Filters</p>
                            <p class="text-xs text-gray-500">Row 1: search. Row 2: category, assignment, condition, dates, and actions.</p>
                        </div>

                        <div class="grid gap-3">
                            <div class="relative">
                                <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search property no, description, employee, category, location..." class="w-full rounded-2xl border border-emerald-950/10 bg-white py-3 pl-11 pr-4 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15" />
                            </div>

                            <div class="pqs-filter-row-two-wrap">
                                <div class="pqs-filter-row-two">
                                    <div id="categoryDropdown" class="relative z-20">
                                        <label for="category" class="pqs-filter-label text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Category</label>

                                        <input type="hidden" name="category" id="category" value="{{ $categoryFilter ?? '' }}" data-auto-submit>

                                        <button id="categoryDropdownToggle" type="button" class="pqs-filter-control mt-1 flex items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50" aria-haspopup="listbox" aria-expanded="false">
                                            <span id="categoryDropdownLabel" class="block truncate text-sm text-gray-700">
                                                {{ $categoryFilter ? ($categories->where('cat_id', $categoryFilter)->first()->cat_name ?? 'All Categories') : 'All Categories' }}
                                            </span>
                                            <i id="categoryDropdownChevron" class="fas fa-chevron-down text-xs text-[#2d5a4a]/45 transition-transform duration-200"></i>
                                        </button>

                                        <div id="categoryDropdownMenu" class="absolute z-50 mt-2 hidden w-full origin-top-right rounded-2xl border border-gray-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.12)] ring-1 ring-black ring-opacity-5 focus:outline-none">
                                            <div class="border-b border-gray-50 px-3 pb-2 pt-3">
                                                <div class="relative">
                                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                                    <input id="categoryDropdownSearch" type="text" placeholder="Search prefix..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500" autocomplete="off">
                                                </div>
                                            </div>

                                            <ul id="categoryDropdownOptions" class="custom-scrollbar max-h-56 overflow-y-auto py-1" role="listbox">
                                                <li class="category-option relative cursor-pointer select-none py-2 pl-4 pr-9 text-sm text-gray-700 transition-colors hover:bg-emerald-50/80 {{ !$categoryFilter ? 'bg-emerald-50/50 font-medium text-[#1a3a2d]' : '' }}" data-value="" data-name="All Categories" role="option" aria-selected="{{ !$categoryFilter ? 'true' : 'false' }}">
                                                    <span class="block truncate">All Categories</span>
                                                    <span class="category-check absolute inset-y-0 right-0 flex items-center pr-4 text-[#1a3a2d] {{ !$categoryFilter ? '' : 'hidden' }}">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </span>
                                                </li>
                                                @foreach ($categories as $category)
                                                    <li class="category-option relative cursor-pointer select-none py-2 pl-4 pr-9 text-sm text-gray-700 transition-colors hover:bg-emerald-50/80 {{ (string) $categoryFilter === (string) $category->cat_id ? 'bg-emerald-50/50 font-medium text-[#1a3a2d]' : '' }}" data-value="{{ $category->cat_id }}" data-name="{{ $category->cat_name }}" role="option" aria-selected="{{ (string) $categoryFilter === (string) $category->cat_id ? 'true' : 'false' }}">
                                                        <span class="block truncate">{{ $category->cat_name }}</span>
                                                        <span class="category-check absolute inset-y-0 right-0 flex items-center pr-4 text-[#1a3a2d] {{ (string) $categoryFilter === (string) $category->cat_id ? '' : 'hidden' }}">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </span>
                                                    </li>
                                                @endforeach

                                                <li id="categoryDropdownEmpty" class="hidden px-4 py-3 text-center text-sm italic text-gray-500">
                                                    No categories found
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="assignment" class="pqs-filter-label text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Assignment</label>
                                        <select id="assignment" name="assignment" data-auto-submit class="pqs-filter-control mt-1 rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15">
                                            <option value="" @selected(!$assignmentFilter)>Any</option>
                                            <option value="ics" @selected($assignmentFilter === 'ics')>With ICS</option>
                                            <option value="par" @selected($assignmentFilter === 'par')>With PAR</option>
                                            <option value="unassigned" @selected($assignmentFilter === 'unassigned')>Unassigned</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="condition" class="pqs-filter-label text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Condition</label>
                                        <select id="condition" name="condition" data-auto-submit class="pqs-filter-control mt-1 rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15">
                                            <option value="" @selected(!$conditionFilter)>Any</option>
                                            <option value="serviceable" @selected($conditionFilter === 'serviceable')>Serviceable</option>
                                            <option value="unserviceable" @selected($conditionFilter === 'unserviceable')>Unserviceable</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="date_from" class="pqs-filter-label text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Date From</label>
                                        <input id="date_from" name="date_from" type="date" value="{{ $dateFrom ?? '' }}" class="pqs-filter-control mt-1 rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15" />
                                    </div>

                                    <div>
                                        <label for="date_to" class="pqs-filter-label text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Date To</label>
                                        <input id="date_to" name="date_to" type="date" value="{{ $dateTo ?? '' }}" class="pqs-filter-control mt-1 rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/15" />
                                    </div>
                                </div>

                                <div class="pqs-filter-actions-row mt-3">
                                    <div>
                                        <button type="button" id="pqsPrintPdfBtn" class="pqs-filter-control inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                            <i class="fas fa-file-pdf text-rose-600"></i>
                                            <span>Print</span>
                                        </button>
                                    </div>

                                    <div>
                                        <button type="button" id="pqsExportExcelBtn" class="pqs-filter-control inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                            <i class="fas fa-file-excel text-emerald-600"></i>
                                            <span>Excel</span>
                                        </button>
                                    </div>

                                    <div>
                                        <a id="pqsResetFilters" href="{{ route('pqs.index') }}" class="pqs-filter-control inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-3 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50" title="Reset Filters">
                                            <i class="fas fa-rotate-left text-gray-500"></i>
                                            <span>Reset</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 border-t border-gray-100 pt-5 lg:flex-row lg:items-start lg:justify-between">
                        <span class="text-sm font-medium text-gray-500 lg:pt-2"><span id="pqs-record-count">{{ number_format($records->total()) }}</span> records found</span>
                        <div class="flex w-full flex-wrap items-stretch gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/45 p-1.5 sm:items-center lg:w-auto">
                            <a href="{{ route('pqs.movements.report') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
                                <i class="fas fa-timeline"></i> Movement Report
                            </a>
                            <button type="button" data-open-location-registry-modal class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
                                <i class="fas fa-plus"></i> Add Location
                            </button>
                            <button type="button" id="pqsBulkTurnoverOpen" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
                                <i class="fas fa-people-arrows-left-right"></i> Bulk Turnover
                            </button>
                        </div>
                    </div>

                    <button type="submit" data-no-global-loading="true" class="sr-only">Submit</button>
                </form>
            </div>

            {{-- Property Ledger Table --}}
            <div id="pqsResults">
                @include('inventory.pqs.partials.records-table', ['records' => $records])
            </div>
        </div>

        {{-- Side Column --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)]">
                <h3 class="text-lg font-semibold text-[#1a3a2d]">Inventory Snapshot</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex justify-between mb-1 text-sm">
                            <span class="font-medium text-gray-700">Total Inventory Value</span>
                            <span class="text-gray-800 font-bold">₱{{ number_format($stats['totalValue'], 2) }}</span>
                        </div>
                    </div>
                     <div>
                        <div class="flex justify-between mb-1 text-sm">
                            <span class="font-medium text-gray-700">Assignment Completion</span>
                            <span class="text-gray-500 font-semibold">{{ number_format($completion, 1) }}%</span>
                        </div>
                        <div class="w-full bg-emerald-100 rounded-full h-2.5">
                            <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $completion }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)]">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-[#1a3a2d]">Reconciliation Summary</h3>
                    <button type="button" id="pqsReconcileRefresh" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-[#1a3a2d] hover:bg-[#f7faf8]">
                        <i class="fas fa-rotate-right text-xs"></i>
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Assets</p>
                        <p id="pqsRecAssets" class="mt-1 text-lg font-bold text-gray-900">—</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Active</p>
                        <p id="pqsRecActive" class="mt-1 text-lg font-bold text-emerald-700">—</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">For Repair</p>
                        <p id="pqsRecRepair" class="mt-1 text-lg font-bold text-amber-700">—</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Unlocated</p>
                        <p id="pqsRecUnlocated" class="mt-1 text-lg font-bold text-rose-700">—</p>
                    </div>
                </div>
                <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Stale Inventory</p>
                    <p id="pqsRecStale" class="mt-1 text-base font-semibold text-gray-900">—</p>
                </div>
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Top Locations</p>
                    <ul id="pqsRecTopLocations" class="mt-2 space-y-2 text-sm text-gray-700">
                        <li class="text-gray-400 italic">Loading summary...</li>
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)]">
                <h3 class="text-lg font-semibold text-[#1a3a2d]">Recently Acquired</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentAssets as $recent)
                         <li class="flex items-center gap-4">
                             <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-emerald-100 text-emerald-600 font-bold">
                                <i class="fas fa-cube"></i>
                            </div>
                            <div class="flex-1 text-sm">
                                <p class="font-semibold text-gray-800">{{ $recent->article }}</p>
                                <p class="text-xs text-gray-500 flex justify-between">
                                    <span>{{ optional($recent->category)->cat_name ?: 'Uncategorised' }}</span>
                                    <span>{{ optional($recent->date_acquired)->format('M d, Y') }}</span>
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 py-4 text-center">No recent assets found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@include('inventory.pqs.partials._view-modal')

<div id="pqsBulkTurnoverModal" class="fixed inset-0 z-[55] hidden bg-black/45 px-4 py-6 opacity-0 transition duration-300" role="dialog" aria-modal="true" aria-labelledby="pqsBulkTurnoverTitle">
    <div class="mx-auto flex h-full max-w-3xl items-center justify-center">
        <div class="modal-panel w-full max-h-full overflow-hidden rounded-2xl bg-white opacity-0 scale-95 translate-y-2 shadow-2xl transition duration-300">
            <div class="flex items-start justify-between border-b px-6 py-4">
                <div>
                    <h3 id="pqsBulkTurnoverTitle" class="text-lg font-semibold text-[#1a3a2d]">Bulk Employee Turnover</h3>
                    <p class="mt-1 text-sm text-gray-600">This action can move multiple assets. Review and confirm before processing.</p>
                </div>
                <button type="button" data-close-bulk-turnover class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <strong>Scope warning:</strong> this applies to all transferable assets under the selected employee, not only the currently viewed item.
                </div>

                <div id="pqsTurnoverPreview" class="mb-4 hidden rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-700">
                    <p id="pqsTurnoverPreviewSummary" class="font-semibold text-gray-800"></p>
                    <p id="pqsTurnoverPreviewHint" class="mt-1 text-gray-600"></p>
                    <ul id="pqsTurnoverPreviewAssets" class="mt-2 list-disc space-y-1 pl-4 text-gray-700"></ul>
                </div>

                <form id="pqsTurnoverForm" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label for="pqsTurnoverEmployee" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Resigning Employee</label>
                        <div class="relative mt-1">
                            <button id="pqsTurnoverEmployeeToggle" type="button" class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                <span id="pqsTurnoverEmployeeLabel">Select employee</span>
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>
                            <div id="pqsTurnoverEmployeePanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                                <input id="pqsTurnoverEmployeeSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search employee...">
                                <ul id="pqsTurnoverEmployeeList" class="mt-2 max-h-44 overflow-y-auto rounded-lg border border-gray-100"></ul>
                            </div>
                        </div>
                        <select id="pqsTurnoverEmployee" name="employee_id" class="hidden">
                            <option value="">Select employee</option>
                            @foreach($turnoverEmployees as $employee)
                                <option value="{{ $employee->employee_id }}">
                                    {{ $employee->full_name }} ({{ $employee->employee_id }})
                                </option>
                            @endforeach
                        </select>
                        <p data-turnover-error-for="employee_id" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                    </div>

                    <div>
                        <label for="pqsTurnoverLocation" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Stockroom Location</label>
                        <div class="relative mt-1">
                            <button id="pqsTurnoverLocationToggle" type="button" class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                <span id="pqsTurnoverLocationLabel">Select stockroom</span>
                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                            </button>
                            <div id="pqsTurnoverLocationPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                                <input id="pqsTurnoverLocationSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search stockroom...">
                                <ul id="pqsTurnoverLocationList" class="mt-2 max-h-44 overflow-y-auto rounded-lg border border-gray-100"></ul>
                            </div>
                        </div>
                        <select id="pqsTurnoverLocation" name="stockroom_location_id" class="hidden">
                            <option value="">Select stockroom</option>
                            @foreach($turnoverLocations as $location)
                                <option value="{{ $location->location_id }}">
                                    {{ $location->location_name }}{{ $location->location_code ? ' ('.$location->location_code.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button
                            id="pqsTurnoverAddStockroom"
                            type="button"
                            data-open-location-registry-modal
                            data-location-registry-type="storage"
                            data-location-registry-lock-type="1"
                            class="mt-2 inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                        >
                            <i class="fas fa-plus text-[10px]"></i>
                            Add Stockroom
                        </button>
                        @if($turnoverLocations->isEmpty())
                            <p id="pqsTurnoverStockroomHint" class="mt-1 text-xs font-medium text-amber-700">No active stockroom locations are available yet. Create one first in location management.</p>
                        @endif
                        <p data-turnover-error-for="stockroom_location_id" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                    </div>

                    <div>
                        <label for="pqsTurnoverEffectiveAt" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Effective Date</label>
                        <input id="pqsTurnoverEffectiveAt" name="effective_at" type="date" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                        <p data-turnover-error-for="effective_at" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                    </div>

                    <div>
                        <label for="pqsTurnoverRemarks" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks</label>
                        <textarea id="pqsTurnoverRemarks" name="remarks" rows="3" maxlength="1000" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Optional turnover notes"></textarea>
                        <p data-turnover-error-for="remarks" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="pqsTurnoverConfirm" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Type CONFIRM to proceed</label>
                        <input id="pqsTurnoverConfirm" type="text" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm uppercase" placeholder="CONFIRM">
                    </div>

                    <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                        <button id="pqsTurnoverSubmit" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            <i class="fas fa-people-arrows"></i>
                            Process Turnover
                        </button>
                        <button type="button" data-close-bulk-turnover class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="pqsConditionConfirmModal" class="fixed inset-0 z-[120] hidden opacity-0 transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="pqsConditionConfirmTitle">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-condition-confirm-close></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="condition-confirm-panel w-full max-w-md rounded-2xl border border-emerald-950/10 bg-white shadow-2xl opacity-0 scale-95 translate-y-2 transition-all duration-300 ease-out">
            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <div id="pqsConditionConfirmIcon" class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-100 text-rose-700">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h4 id="pqsConditionConfirmTitle" class="text-base font-bold text-[#1a3a2d]">Mark Asset Unserviceable</h4>
                    <p class="text-xs text-gray-500">Please review before proceeding.</p>
                </div>
            </div>
            <div class="px-5 py-4">
                <p id="pqsConditionConfirmMessage" class="text-sm text-gray-700 whitespace-pre-line">Are you sure you want to continue?</p>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                <button type="button" id="pqsConditionConfirmNo" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" data-condition-confirm-close>
                    Cancel
                </button>
                <button type="button" id="pqsConditionConfirmYes" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                    Mark Unserviceable
                </button>
            </div>
        </div>
    </div>
</div>

@include('inventory.locations.partials._add-modal', [
    'locationRegistryStoreRoute' => route('physical_locations.store'),
    'locationRegistryParents' => $transferLocations,
])
@endsection

@push('scripts')
<script>
(() => {
    const endpoint = "{{ route('pqs.index') }}";
    const form = document.getElementById('pqsFiltersForm');
    if (!form) {
        return;
    }

    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const assignmentSelect = document.getElementById('assignment');
    const conditionSelect = document.getElementById('condition');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const categoryDropdown = document.getElementById('categoryDropdown');
    const categoryToggle = document.getElementById('categoryDropdownToggle');
    const categoryMenu = document.getElementById('categoryDropdownMenu');
    const categoryLabel = document.getElementById('categoryDropdownLabel');
    const categorySearchInput = document.getElementById('categoryDropdownSearch');
    const categoryChevron = document.getElementById('categoryDropdownChevron');
    const categoryEmpty = document.getElementById('categoryDropdownEmpty');
    const categoryOptions = Array.from(document.querySelectorAll('.category-option'));
    const selects = Array.from(form.querySelectorAll('[data-auto-submit]'));
    const resultsContainer = document.getElementById('pqsResults');
    const recordCount = document.getElementById('pqs-record-count');
    const resetLink = document.getElementById('pqsResetFilters');

    let debounceTimer = null;
    let activeRequest = null;

    const setLoading = (isLoading) => {
        resultsContainer.classList.toggle('opacity-50', isLoading);
        resultsContainer.classList.toggle('pointer-events-none', isLoading);
    };

    const buildParams = (extra = {}) => {
        const params = new URLSearchParams();
        const searchValue = searchInput?.value.trim();
        if (searchValue) {
            params.set('search', searchValue);
        }

        const categoryValue = categorySelect?.value;
        if (categoryValue) {
            params.set('category', categoryValue);
        }

        const assignmentValue = assignmentSelect?.value;
        if (assignmentValue) {
            params.set('assignment', assignmentValue);
        }

        const conditionValue = conditionSelect?.value;
        if (conditionValue) {
            params.set('condition', conditionValue);
        }

        const dateFromValue = dateFromInput?.value;
        const dateToValue = dateToInput?.value;
        if (dateFromValue) {
            params.set('date_from', dateFromValue);
        }
        if (dateToValue) {
            params.set('date_to', dateToValue);
        }

        if (extra.page) {
            params.set('page', extra.page);
        }

        return params;
    };

    const fetchRecords = async (extra = {}) => {
        const params = buildParams(extra);
        const ajaxParams = new URLSearchParams(params);
        ajaxParams.set('ajax', '1');

        if (activeRequest) {
            activeRequest.abort();
        }

        const controller = new AbortController();
        activeRequest = controller;

        setLoading(true);

        try {
            const response = await fetch(`${endpoint}?${ajaxParams.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`Unable to fetch PQS records (status ${response.status})`);
            }

            const payload = await response.json();
            resultsContainer.innerHTML = payload.html;

            if (typeof payload.total === 'number' && recordCount) {
                recordCount.textContent = payload.total.toLocaleString();
            }

            const nextUrl = params.toString() ? `${endpoint}?${params.toString()}` : endpoint;
            window.history.replaceState(null, '', nextUrl);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            if (activeRequest === controller) {
                activeRequest = null;
            }
            setLoading(false);
        }
    };

    // Expose a safe refresh hook for other page scripts (e.g., modal actions).
    window.pqsFetchRecords = fetchRecords;

    const debouncedFetch = () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchRecords(), 400);
    };

    const closeCategoryDropdown = () => {
        if (!categoryMenu || !categoryToggle || !categoryChevron) {
            return;
        }

        categoryMenu.classList.add('hidden');
        categoryToggle.setAttribute('aria-expanded', 'false');
        categoryChevron.classList.remove('rotate-180');
    };

    const openCategoryDropdown = () => {
        if (!categoryMenu || !categoryToggle || !categoryChevron) {
            return;
        }

        categoryMenu.classList.remove('hidden');
        categoryToggle.setAttribute('aria-expanded', 'true');
        categoryChevron.classList.add('rotate-180');
        categorySearchInput?.focus();
    };

    const filterCategoryOptions = () => {
        if (!categorySearchInput) {
            return;
        }

        const query = categorySearchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        categoryOptions.forEach((option) => {
            const name = (option.dataset.name || '').toLowerCase();
            const isVisible = query === '' || name.startsWith(query);
            option.classList.toggle('hidden', !isVisible);

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (categoryEmpty) {
            categoryEmpty.classList.toggle('hidden', visibleCount !== 0);
        }
    };

    const setSelectedCategory = (option) => {
        if (!categorySelect || !categoryLabel) {
            return;
        }

        const value = option.dataset.value ?? '';
        const name = option.dataset.name ?? 'All Categories';

        categorySelect.value = value;
        categoryLabel.textContent = name;

        categoryOptions.forEach((item) => {
            const selected = item === option;
            item.classList.toggle('bg-emerald-50/50', selected);
            item.classList.toggle('font-medium', selected);
            item.classList.toggle('text-[#1a3a2d]', selected);
            item.setAttribute('aria-selected', selected ? 'true' : 'false');

            const check = item.querySelector('.category-check');
            if (check) {
                check.classList.toggle('hidden', !selected);
            }
        });

        closeCategoryDropdown();

        if (categorySearchInput) {
            categorySearchInput.value = '';
            filterCategoryOptions();
        }

        fetchRecords();
    };

    categoryToggle?.addEventListener('click', () => {
        if (categoryMenu?.classList.contains('hidden')) {
            openCategoryDropdown();
            filterCategoryOptions();
            return;
        }

        closeCategoryDropdown();
    });

    categorySearchInput?.addEventListener('input', filterCategoryOptions);
    categorySearchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCategoryDropdown();
        }
    });

    categoryOptions.forEach((option) => {
        option.addEventListener('click', () => setSelectedCategory(option));
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Node) || !categoryDropdown) {
            return;
        }

        if (!categoryDropdown.contains(target)) {
            closeCategoryDropdown();
        }
    });

    searchInput?.addEventListener('input', debouncedFetch);
    dateFromInput?.addEventListener('change', () => fetchRecords());
    dateToInput?.addEventListener('change', () => fetchRecords());

    selects.forEach((select) => {
        select.addEventListener('change', () => fetchRecords());
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchRecords();
    });

    resetLink?.addEventListener('click', (event) => {
        event.preventDefault();
        if (searchInput) {
            searchInput.value = '';
        }
        selects.forEach((select) => {
            select.value = '';
        });
        if (dateFromInput) {
            dateFromInput.value = '';
        }
        if (dateToInput) {
            dateToInput.value = '';
        }

        if (categoryLabel) {
            categoryLabel.textContent = 'All Categories';
        }

        if (categorySearchInput) {
            categorySearchInput.value = '';
            filterCategoryOptions();
        }

        closeCategoryDropdown();

        categoryOptions.forEach((item) => {
            const isAll = (item.dataset.value ?? '') === '';
            item.classList.toggle('bg-emerald-50/50', isAll);
            item.classList.toggle('font-medium', isAll);
            item.classList.toggle('text-[#1a3a2d]', isAll);
            item.setAttribute('aria-selected', isAll ? 'true' : 'false');

            const check = item.querySelector('.category-check');
            if (check) {
                check.classList.toggle('hidden', !isAll);
            }
        });

        fetchRecords();
    });

    resultsContainer.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const anchor = target ? target.closest('a') : null;
        if (!anchor) {
            return;
        }

        const url = new URL(anchor.href, window.location.origin);
        if (!url.searchParams.has('page')) {
            return;
        }

        event.preventDefault();
        const page = url.searchParams.get('page');
        fetchRecords({ page });
    });
})();

(() => {
    const modal = document.getElementById('pqsViewModal');
    const modalPanel = modal ? modal.querySelector('.modal-panel') : null;
    const modalBody = modalPanel ? modalPanel.querySelector('.flex-1') : null;
    const modalFocusTarget = modal ? modal.querySelector('[data-focus]') : null;
    const resultsContainer = document.getElementById('pqsResults');
    if (!modal || !resultsContainer) {
        return;
    }

    const articleEl = document.getElementById('pqsViewArticle');
    const propertyNoEl = document.getElementById('pqsViewPropertyNo');
    const dateAcquiredEl = document.getElementById('pqsViewDateAcquired');
    const categoryEl = document.getElementById('pqsViewCategory');
    const quantityEl = document.getElementById('pqsViewQuantity');
    const unitCostEl = document.getElementById('pqsViewUnitCost');
    const totalCostEl = document.getElementById('pqsViewTotalCost');
    const officerEl = document.getElementById('pqsViewOfficer');
    const officerIdEl = document.getElementById('pqsViewOfficerId');
    const descriptionEl = document.getElementById('pqsViewDescription');
    const serialsEl = document.getElementById('pqsViewSerials');
    const remarksEl = document.getElementById('pqsViewRemarks');
    const documentBadge = document.getElementById('pqsViewDocumentBadge');
    const documentMeta = document.getElementById('pqsViewDocumentMeta');
    const documentExtra = document.getElementById('pqsViewDocumentExtra');
    const currentLocationEl = document.getElementById('pqsViewCurrentLocation');
    const currentCustodianEl = document.getElementById('pqsViewCurrentCustodian');
    const currentDivisionEl = document.getElementById('pqsViewCurrentDivision');
    const currentSectionEl = document.getElementById('pqsViewCurrentSection');
    const currentConditionEl = document.getElementById('pqsViewCurrentCondition');
    const lastMovementEl = document.getElementById('pqsViewLastMovement');
    const conditionChipEl = document.getElementById('pqsViewConditionChip');
    const movementTimelineEl = document.getElementById('pqsMovementTimeline');
    const movementTimelinePager = document.getElementById('pqsMovementTimelinePager');
    const timelinePrevBtn = document.getElementById('pqsTimelinePrev');
    const timelineNextBtn = document.getElementById('pqsTimelineNext');
    const timelinePageInfo = document.getElementById('pqsTimelinePageInfo');
    const transferForm = document.getElementById('pqsTransferForm');
    const transferSubmit = document.getElementById('pqsTransferSubmit');
    const transferError = document.getElementById('pqsTransferError');
    const transferBlockedNotice = document.getElementById('pqsTransferBlockedNotice');
    const transferLocation = document.getElementById('pqsTransferLocation');
    const transferLocationToggle = document.getElementById('pqsTransferLocationToggle');
    const transferLocationLabel = document.getElementById('pqsTransferLocationLabel');
    const transferLocationPanel = document.getElementById('pqsTransferLocationPanel');
    const transferLocationSearch = document.getElementById('pqsTransferLocationSearch');
    const transferLocationList = document.getElementById('pqsTransferLocationList');
    const transferCustodian = document.getElementById('pqsTransferCustodian');
    const transferCustodianToggle = document.getElementById('pqsTransferCustodianToggle');
    const transferCustodianLabel = document.getElementById('pqsTransferCustodianLabel');
    const transferCustodianPanel = document.getElementById('pqsTransferCustodianPanel');
    const transferCustodianSearch = document.getElementById('pqsTransferCustodianSearch');
    const transferCustodianList = document.getElementById('pqsTransferCustodianList');
    const transferDivision = document.getElementById('pqsTransferDivision');
    const transferSection = document.getElementById('pqsTransferSection');
    const transferReason = document.getElementById('pqsTransferReason');
    const transferRemarks = document.getElementById('pqsTransferRemarks');
    const transferMovementType = document.getElementById('pqsTransferMovementType');
    const transferFieldErrors = Array.from(document.querySelectorAll('[data-transfer-error-for]'));
    const transferInputMap = {
        movement_type: transferMovementType,
        to_location_id: transferLocationToggle,
        to_custodian_employee_id: transferCustodianToggle,
        to_division_id: transferDivision,
        to_section_id: transferSection,
        reason_code: transferReason,
        remarks: transferRemarks,
    };
    const turnoverForm = document.getElementById('pqsTurnoverForm');
    const turnoverSubmit = document.getElementById('pqsTurnoverSubmit');
    const turnoverEmployee = document.getElementById('pqsTurnoverEmployee');
    const turnoverEmployeeToggle = document.getElementById('pqsTurnoverEmployeeToggle');
    const turnoverEmployeeLabel = document.getElementById('pqsTurnoverEmployeeLabel');
    const turnoverEmployeePanel = document.getElementById('pqsTurnoverEmployeePanel');
    const turnoverEmployeeSearch = document.getElementById('pqsTurnoverEmployeeSearch');
    const turnoverEmployeeList = document.getElementById('pqsTurnoverEmployeeList');
    const turnoverLocationToggle = document.getElementById('pqsTurnoverLocationToggle');
    const turnoverLocationLabel = document.getElementById('pqsTurnoverLocationLabel');
    const turnoverLocationPanel = document.getElementById('pqsTurnoverLocationPanel');
    const turnoverLocationSearch = document.getElementById('pqsTurnoverLocationSearch');
    const turnoverLocationList = document.getElementById('pqsTurnoverLocationList');
    const turnoverLocation = document.getElementById('pqsTurnoverLocation');
    const turnoverAddStockroomBtn = document.getElementById('pqsTurnoverAddStockroom');
    const turnoverStockroomHint = document.getElementById('pqsTurnoverStockroomHint');
    const turnoverEffectiveAt = document.getElementById('pqsTurnoverEffectiveAt');
    const turnoverRemarks = document.getElementById('pqsTurnoverRemarks');
    const turnoverConfirm = document.getElementById('pqsTurnoverConfirm');
    const bulkTurnoverOpenBtn = document.getElementById('pqsBulkTurnoverOpen');
    const bulkTurnoverModal = document.getElementById('pqsBulkTurnoverModal');
    const bulkTurnoverModalPanel = bulkTurnoverModal ? bulkTurnoverModal.querySelector('.modal-panel') : null;
    const bulkTurnoverCloseEls = bulkTurnoverModal ? Array.from(bulkTurnoverModal.querySelectorAll('[data-close-bulk-turnover]')) : [];
    const conditionConfirmModal = document.getElementById('pqsConditionConfirmModal');
    const conditionConfirmPanel = conditionConfirmModal ? conditionConfirmModal.querySelector('.condition-confirm-panel') : null;
    const conditionConfirmMessage = document.getElementById('pqsConditionConfirmMessage');
    const conditionConfirmYesBtn = document.getElementById('pqsConditionConfirmYes');
    const conditionConfirmCloseEls = conditionConfirmModal ? Array.from(conditionConfirmModal.querySelectorAll('[data-condition-confirm-close]')) : [];
    const turnoverPreview = document.getElementById('pqsTurnoverPreview');
    const turnoverPreviewSummary = document.getElementById('pqsTurnoverPreviewSummary');
    const turnoverPreviewHint = document.getElementById('pqsTurnoverPreviewHint');
    const turnoverPreviewAssets = document.getElementById('pqsTurnoverPreviewAssets');
    let restoreBulkTurnoverBodyOverflowOnClose = false;
    let restoreConditionConfirmBodyOverflowOnClose = false;
    let conditionConfirmResolver = null;
    let pendingTurnoverStockroomSelection = false;
    const actionTabButtons = Array.from(document.querySelectorAll('[data-pqs-action-tab]'));
    const detailSections = Array.from(document.querySelectorAll('.pqs-detail-section'));
    const actionWorkspace = document.getElementById('pqsActionWorkspace');
    const actionTransferPane = document.getElementById('pqsActionTransferPane');
    const actionTurnoverPane = document.getElementById('pqsActionTurnoverPane');
    const actionConditionPane = document.getElementById('pqsActionConditionPane');
    const conditionForm = document.getElementById('pqsConditionForm');
    const conditionSuccess = document.getElementById('pqsConditionSuccess');
    const conditionError = document.getElementById('pqsConditionError');
    const conditionEffectiveAt = document.getElementById('pqsConditionEffectiveAt');
    const conditionReasonCode = document.getElementById('pqsConditionReasonCode');
    const conditionReasonHelp = document.getElementById('pqsConditionReasonHelp');
    const conditionReasonError = document.getElementById('pqsConditionReasonError');
    const conditionExpectedFixDateWrap = document.getElementById('pqsConditionExpectedFixDateWrap');
    const conditionExpectedFixDate = document.getElementById('pqsConditionExpectedFixDate');
    const conditionExpectedFixDateError = document.getElementById('pqsConditionExpectedFixDateError');
    const conditionHistoryEl = document.getElementById('pqsConditionHistory');
    const conditionRemarks = document.getElementById('pqsConditionRemarks');
    const conditionRemarksError = document.getElementById('pqsConditionRemarksError');
    const conditionUnserviceableBtn = document.getElementById('pqsConditionUnserviceable');
    const allConditionReasonOptions = conditionReasonCode
        ? Array.from(conditionReasonCode.options).map((option) => ({
            value: String(option.value || ''),
            label: String(option.textContent || ''),
            condition: String(option.dataset.condition || 'both'),
        }))
        : [];
    const turnoverFieldErrors = Array.from(document.querySelectorAll('[data-turnover-error-for]'));
    const turnoverInputMap = {
        employee_id: turnoverEmployeeToggle,
        stockroom_location_id: turnoverLocationToggle,
        effective_at: turnoverEffectiveAt,
        remarks: turnoverRemarks,
    };
    const dateAcquiredChip = document.getElementById('pqsViewDateAcquiredChip');
    const snapshotArticleEl = document.getElementById('pqsViewSnapshotArticle');
    const propertyNoSnapshotEl = document.getElementById('pqsViewPropertyNoSnapshot');
    const snapshotDateEl = document.getElementById('pqsViewSnapshotDate');
    const snapshotDocumentEl = document.getElementById('pqsViewSnapshotDocument');
    const snapshotQuantityEl = document.getElementById('pqsViewSnapshotQuantity');
    const unitEl = document.getElementById('pqsViewUnit');
    const parTemplate = document.getElementById('pqsParTemplate');
    const icsTemplate = document.getElementById('pqsIcsTemplate');
    const defaultTemplate = document.getElementById('pqsDefaultTemplate');
    const parFields = {
        entity: document.getElementById('pqsParEntity'),
        fundCluster: document.getElementById('pqsParFundCluster'),
        number: document.getElementById('pqsParNumber'),
        quantity: document.getElementById('pqsParQuantity'),
        unit: document.getElementById('pqsParUnit'),
        description: document.getElementById('pqsParDescription'),
        propertyNo: document.getElementById('pqsParPropertyNo'),
        dateAcquired: document.getElementById('pqsParDateAcquired'),
        amount: document.getElementById('pqsParAmount'),
        receivedBySignature: document.getElementById('pqsParReceivedBySignature'),
        receivedBy: document.getElementById('pqsParReceivedBy'),
        receivedByPosition: document.getElementById('pqsParReceivedByPosition'),
        receivedByDate: document.getElementById('pqsParReceivedByDate'),
        receivedFrom: document.getElementById('pqsParReceivedFrom'),
        receivedFromPosition: document.getElementById('pqsParReceivedFromPosition'),
        receivedFromDate: document.getElementById('pqsParReceivedFromDate'),
    };
    const icsFields = {
        entity: document.getElementById('pqsIcsEntity'),
        fundCluster: document.getElementById('pqsIcsFundCluster'),
        number: document.getElementById('pqsIcsNumber'),
        quantity: document.getElementById('pqsIcsQuantity'),
        unit: document.getElementById('pqsIcsUnit'),
        unitCost: document.getElementById('pqsIcsUnitCost'),
        totalCost: document.getElementById('pqsIcsTotalCost'),
        description: document.getElementById('pqsIcsDescription'),
        inventoryNo: document.getElementById('pqsIcsInventoryNo'),
        usefulLife: document.getElementById('pqsIcsUsefulLife'),
        receivedFrom: document.getElementById('pqsIcsReceivedFrom'),
        receivedFromPosition: document.getElementById('pqsIcsReceivedFromPosition'),
        receivedFromDate: document.getElementById('pqsIcsReceivedFromDate'),
        receivedBySignature: document.getElementById('pqsIcsReceivedBySignature'),
        receivedBy: document.getElementById('pqsIcsReceivedBy'),
        receivedByPosition: document.getElementById('pqsIcsReceivedByPosition'),
        receivedByDate: document.getElementById('pqsIcsReceivedByDate'),
    };

    const defaultSerialHtml = '<li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>';
    const defaultRemarksText = 'No additional remarks recorded.';
    const defaultDocumentMeta = 'No custodial document generated yet.';
    const defaultDocumentExtra = 'Generate PQS record to create custodial documents automatically.';
    const defaultEntityName = 'Department of Agriculture - Bureau of Plant Industry';
    const defaultFundCluster = '01';
    const statusActive = '{{ \App\Models\PqsRecord::STATUS_ACTIVE }}';
    const statusForRepair = '{{ \App\Models\PqsRecord::STATUS_FOR_REPAIR }}';
    const statusDisposed = '{{ \App\Models\PqsRecord::STATUS_DISPOSED }}';
    const statusLost = '{{ \App\Models\PqsRecord::STATUS_LOST }}';
    let currentRecord = null;
    let currentShowUrl = null;

    const normalizeAssetStatus = (status) => String(status || '').trim() || statusActive;

    const humanizeReasonCode = (reasonCode) => String(reasonCode || '')
        .trim()
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

    const getConditionPresentation = (status) => {
        const normalizedStatus = normalizeAssetStatus(status);

        if (normalizedStatus === statusForRepair) {
            return {
                label: 'Unserviceable',
                icon: 'fa-triangle-exclamation',
                chipClass: 'border-rose-200 bg-rose-50 text-rose-700',
            };
        }

        if (normalizedStatus === statusDisposed) {
            return {
                label: 'Unserviceable',
                icon: 'fa-triangle-exclamation',
                chipClass: 'border-rose-200 bg-rose-50 text-rose-700',
            };
        }

        if (normalizedStatus === statusLost) {
            return {
                label: 'Unserviceable',
                icon: 'fa-triangle-exclamation',
                chipClass: 'border-rose-200 bg-rose-50 text-rose-700',
            };
        }

        return {
            label: 'Serviceable',
            icon: 'fa-circle-check',
            chipClass: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        };
    };

    const getTransferBlockedMessage = (status) => {
        const normalizedStatus = normalizeAssetStatus(status);

        if (normalizedStatus === statusForRepair) {
            return 'Transfer is disabled while this asset is unserviceable.';
        }

        if (normalizedStatus === statusDisposed) {
            return 'Transfer is disabled because this asset is already disposed.';
        }

        if (normalizedStatus === statusLost) {
            return 'Transfer is disabled because this asset is marked as lost.';
        }

        return '';
    };

    const updateTransferActionAvailability = (record) => {
        const transferTabButton = actionTabButtons.find((button) => button.dataset.pqsActionTab === 'transfer');
        const blockedMessage = getTransferBlockedMessage(record?.asset_status);
        const isBlocked = blockedMessage !== '';

        if (transferTabButton) {
            transferTabButton.disabled = isBlocked;
            transferTabButton.classList.toggle('opacity-60', isBlocked);
            transferTabButton.classList.toggle('cursor-not-allowed', isBlocked);
            transferTabButton.title = isBlocked ? blockedMessage : 'Transfer';
        }

        if (transferSubmit) {
            transferSubmit.disabled = isBlocked;
            transferSubmit.classList.toggle('opacity-60', isBlocked);
            transferSubmit.classList.toggle('cursor-not-allowed', isBlocked);
            transferSubmit.title = isBlocked ? blockedMessage : 'Save Movement';
        }

        if (transferBlockedNotice) {
            transferBlockedNotice.classList.toggle('hidden', !isBlocked);
            transferBlockedNotice.textContent = isBlocked ? blockedMessage : '';
        }

        if (isBlocked && transferError) {
            transferError.classList.add('hidden');
            transferError.textContent = '';
        }
    };

    const renderConditionChip = (status) => {
        const presentation = getConditionPresentation(status);

        if (currentConditionEl) {
            currentConditionEl.textContent = presentation.label;
        }

        if (!conditionChipEl) {
            return;
        }

        conditionChipEl.className = `inline-flex items-center gap-2 rounded-full border px-4 py-1.5 text-xs font-semibold uppercase tracking-wide ${presentation.chipClass}`;
        conditionChipEl.innerHTML = `<i class="fas ${presentation.icon}"></i>${escapeHtml(presentation.label)}`;
    };

    const recAssets = document.getElementById('pqsRecAssets');
    const recActive = document.getElementById('pqsRecActive');
    const recRepair = document.getElementById('pqsRecRepair');
    const recUnlocated = document.getElementById('pqsRecUnlocated');
    const recStale = document.getElementById('pqsRecStale');
    const recTopLocations = document.getElementById('pqsRecTopLocations');
    const recRefresh = document.getElementById('pqsReconcileRefresh');
    const reconcileEndpoint = "{{ route('pqs.reconciliation.summary') }}";
    const turnoverPreviewEndpoint = "{{ route('pqs.movements.turnover.preview') }}";
    const timelineEndpointTemplate = "{{ route('pqs.movements.timeline', ['pqsRecord' => '__PROPERTY__']) }}";
    const timelinePageSize = 3;
    let timelineRequestToken = 0;
    let timelineCurrentPage = 1;
    let timelineLastPage = 1;
    let timelineEntriesCache = [];

    const setText = (element, value, fallback = '—') => {
        if (!element) {
            return;
        }
        const rawValue = value ?? '';
        const stringValue = typeof rawValue === 'number' ? rawValue.toString() : String(rawValue);
        element.textContent = stringValue.trim() !== '' ? stringValue : fallback;
    };

    const setLineField = (element, value) => {
        if (!element) {
            return;
        }
        const rawValue = value ?? '';
        const stringValue = typeof rawValue === 'number' ? rawValue.toString() : String(rawValue);
        element.textContent = stringValue.trim() !== '' ? stringValue : '____________________';
    };

    const trimSignatureDataUri = (dataUri) => new Promise((resolve) => {
        const source = String(dataUri || '').trim();
        if (!source || !source.startsWith('data:image/')) {
            resolve(source);
            return;
        }

        const image = new Image();
        image.onload = () => {
            try {
                const canvas = document.createElement('canvas');
                canvas.width = image.width;
                canvas.height = image.height;
                const context = canvas.getContext('2d');
                if (!context) {
                    resolve(source);
                    return;
                }

                context.drawImage(image, 0, 0);
                const { data, width, height } = context.getImageData(0, 0, canvas.width, canvas.height);

                let minX = width;
                let minY = height;
                let maxX = -1;
                let maxY = -1;

                for (let y = 0; y < height; y += 1) {
                    for (let x = 0; x < width; x += 1) {
                        const alpha = data[(y * width + x) * 4 + 3];
                        if (alpha > 0) {
                            if (x < minX) minX = x;
                            if (y < minY) minY = y;
                            if (x > maxX) maxX = x;
                            if (y > maxY) maxY = y;
                        }
                    }
                }

                if (maxX < minX || maxY < minY) {
                    resolve(source);
                    return;
                }

                const padding = 4;
                const cropX = Math.max(0, minX - padding);
                const cropY = Math.max(0, minY - padding);
                const cropWidth = Math.min(width - cropX, maxX - minX + 1 + padding * 2);
                const cropHeight = Math.min(height - cropY, maxY - minY + 1 + padding * 2);

                const output = document.createElement('canvas');
                output.width = cropWidth;
                output.height = cropHeight;
                const outputContext = output.getContext('2d');
                if (!outputContext) {
                    resolve(source);
                    return;
                }

                outputContext.drawImage(canvas, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
                resolve(output.toDataURL('image/png'));
            } catch (error) {
                resolve(source);
            }
        };

        image.onerror = () => resolve(source);
        image.src = source;
    });

    const setSignatureField = (element, signatureDataUri, nameLineElement = null) => {
        if (!element) {
            return;
        }

        const value = String(signatureDataUri || '').trim();
        if (!value) {
            element.setAttribute('src', '');
            element.style.left = '';
            element.classList.add('hidden');
            return;
        }

        trimSignatureDataUri(value).then((trimmed) => {
            const finalValue = String(trimmed || value).trim();
            element.setAttribute('src', finalValue);
            element.classList.toggle('hidden', finalValue === '');

            if (finalValue !== '' && nameLineElement) {
                // Keep signature anchored to the exact center of the printed-name wrapper.
                element.style.left = '50%';
                element.style.transform = 'translateX(-50%)';
            }
        });
    };

    const setTransferError = (message = '') => {
        if (!transferError) {
            return;
        }

        const hasMessage = String(message || '').trim() !== '';
        transferError.classList.toggle('hidden', !hasMessage);
        transferError.textContent = hasMessage ? message : '';
    };

    const clearTransferFieldErrors = () => {
        transferFieldErrors.forEach((fieldError) => {
            fieldError.classList.add('hidden');
            fieldError.textContent = '';
        });

        Object.values(transferInputMap).forEach((field) => {
            if (!field) {
                return;
            }

            field.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');
            field.classList.add('border-gray-200');
        });
    };

    const clearTransferFieldError = (fieldName) => {
        const fieldError = transferFieldErrors.find((item) => item.dataset.transferErrorFor === fieldName);
        if (fieldError) {
            fieldError.classList.add('hidden');
            fieldError.textContent = '';
        }

        const field = transferInputMap[fieldName];
        if (field) {
            field.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');
            field.classList.add('border-gray-200');
        }
    };

    const setTransferFieldErrors = (errors = {}) => {
        clearTransferFieldErrors();

        Object.entries(errors).forEach(([fieldName, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : messages;
            const fieldError = transferFieldErrors.find((item) => item.dataset.transferErrorFor === fieldName);

            if (fieldError && message) {
                fieldError.textContent = String(message);
                fieldError.classList.remove('hidden');
            }

            const field = transferInputMap[fieldName];
            if (field) {
                field.classList.remove('border-gray-200');
                field.classList.add('border-rose-400', 'ring-2', 'ring-rose-200');
            }
        });
    };

    const setTransferLoading = (isLoading) => {
        if (!transferSubmit) {
            return;
        }

        if (isLoading) {
            transferSubmit.dataset.originalHtml = transferSubmit.innerHTML;
            transferSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            transferSubmit.disabled = true;
            return;
        }

        if (transferSubmit.dataset.originalHtml) {
            transferSubmit.innerHTML = transferSubmit.dataset.originalHtml;
        }
        transferSubmit.disabled = false;
    };

    const notifySuccess = (message) => {
        const text = String(message || '').trim();
        if (!text) {
            return;
        }

        if (typeof window.pqsShowToast === 'function') {
            window.pqsShowToast(text, 'success');
            return;
        }

        if (window.alertify && typeof window.alertify.success === 'function') {
            window.alertify.success(text);
        }
    };

    const notifyError = (message) => {
        const text = String(message || '').trim();
        if (!text) {
            return;
        }

        if (typeof window.pqsShowToast === 'function') {
            window.pqsShowToast(text, 'error');
            return;
        }

        if (window.alertify && typeof window.alertify.error === 'function') {
            window.alertify.error(text);
        }
    };

    const clearTurnoverFieldErrors = () => {
        turnoverFieldErrors.forEach((fieldError) => {
            fieldError.classList.add('hidden');
            fieldError.textContent = '';
        });

        Object.values(turnoverInputMap).forEach((field) => {
            if (!field) {
                return;
            }

            field.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');
            field.classList.add('border-gray-200');
        });
    };

    const clearTurnoverFieldError = (fieldName) => {
        const fieldError = turnoverFieldErrors.find((item) => item.dataset.turnoverErrorFor === fieldName);
        if (fieldError) {
            fieldError.classList.add('hidden');
            fieldError.textContent = '';
        }

        const field = turnoverInputMap[fieldName];
        if (field) {
            field.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');
            field.classList.add('border-gray-200');
        }
    };

    const setTurnoverFieldErrors = (errors = {}) => {
        clearTurnoverFieldErrors();

        Object.entries(errors).forEach(([fieldName, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : messages;
            const fieldError = turnoverFieldErrors.find((item) => item.dataset.turnoverErrorFor === fieldName);

            if (fieldError && message) {
                fieldError.textContent = String(message);
                fieldError.classList.remove('hidden');
            }

            const field = turnoverInputMap[fieldName];
            if (field) {
                field.classList.remove('border-gray-200');
                field.classList.add('border-rose-400', 'ring-2', 'ring-rose-200');
            }
        });
    };

    const setTurnoverLoading = (isLoading) => {
        if (!turnoverSubmit) {
            return;
        }

        if (isLoading) {
            turnoverSubmit.dataset.originalHtml = turnoverSubmit.innerHTML;
            turnoverSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            turnoverSubmit.disabled = true;
            return;
        }

        if (turnoverSubmit.dataset.originalHtml) {
            turnoverSubmit.innerHTML = turnoverSubmit.dataset.originalHtml;
        }
        turnoverSubmit.disabled = false;
    };

    const setTurnoverPreview = (payload = null) => {
        if (!turnoverPreview || !turnoverPreviewSummary || !turnoverPreviewHint || !turnoverPreviewAssets) {
            return;
        }

        if (!payload) {
            turnoverPreview.classList.add('hidden');
            turnoverPreviewSummary.textContent = '';
            turnoverPreviewHint.textContent = '';
            turnoverPreviewAssets.innerHTML = '';
            return;
        }

        const totalAssets = Number(payload.total_assets || 0);
        const eligibleAssets = Number(payload.eligible_assets || 0);
        const skippedAssets = Number(payload.skipped_assets || 0);
        const items = Array.isArray(payload.sample_assets) ? payload.sample_assets : [];

        turnoverPreview.classList.remove('hidden');
        turnoverPreviewSummary.textContent = `${eligibleAssets.toLocaleString('en-PH')} eligible of ${totalAssets.toLocaleString('en-PH')} total assets`;
        turnoverPreviewHint.textContent = skippedAssets > 0
            ? `${skippedAssets.toLocaleString('en-PH')} item(s) are non-transferable and will be skipped.`
            : 'All listed assets are transferable.';

        if (items.length === 0) {
            turnoverPreviewAssets.innerHTML = '<li class="list-none text-gray-500 italic">No asset preview available.</li>';
            return;
        }

        turnoverPreviewAssets.innerHTML = items
            .map((item) => `<li>${escapeHtml(item.property_no || 'Unknown Property')}</li>`)
            .join('');
    };

    const loadTurnoverPreview = async () => {
        const employeeId = turnoverEmployee?.value || '';
        if (!employeeId) {
            setTurnoverPreview(null);
            return;
        }

        try {
            const response = await fetch(turnoverPreviewEndpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ employee_id: employeeId }),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                setTurnoverPreview(null);
                return;
            }

            setTurnoverPreview(data?.data?.summary || null);
        } catch (error) {
            setTurnoverPreview(null);
        }
    };

    const openBulkTurnoverModal = () => {
        if (!bulkTurnoverModal) {
            return;
        }

        if (!bulkTurnoverModal.classList.contains('hidden')) {
            return;
        }

        restoreBulkTurnoverBodyOverflowOnClose = !document.body.classList.contains('overflow-hidden');
        bulkTurnoverModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                bulkTurnoverModal.scrollTop = 0;
                if (bulkTurnoverModalPanel) {
                    bulkTurnoverModalPanel.scrollTop = 0;
                    bulkTurnoverModalPanel.querySelectorAll('.overflow-y-auto, .overflow-y-scroll').forEach((element) => {
                        element.scrollTop = 0;
                    });
                }

                bulkTurnoverModal.classList.remove('opacity-0');
                bulkTurnoverModalPanel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
            });
        });
        document.body.classList.add('overflow-hidden');

        clearTurnoverFieldErrors();
        setTurnoverPreview(null);
        if (turnoverConfirm) {
            turnoverConfirm.value = '';
            turnoverConfirm.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');
            turnoverConfirm.classList.add('border-gray-200');
        }
        if (turnoverEmployeeSearch) {
            turnoverEmployeeSearch.value = '';
        }
        if (turnoverEffectiveAt && String(turnoverEffectiveAt.value || '').trim() === '') {
            turnoverEffectiveAt.value = todayInputValue();
        }
        closeTurnoverSearchPanels();
        setSelectLabel(turnoverEmployee, turnoverEmployeeLabel);
        setSelectLabel(turnoverLocation, turnoverLocationLabel);

        setTimeout(() => {
            const focusTarget = turnoverEmployeeToggle || turnoverEmployee || turnoverLocation;
            if (focusTarget) {
                try {
                    focusTarget.focus({ preventScroll: true });
                } catch (error) {
                    focusTarget.focus();
                }
            }
        }, 300);
    };

    const closeBulkTurnoverModal = () => {
        if (!bulkTurnoverModal) {
            return;
        }

        if (bulkTurnoverModal.classList.contains('hidden')) {
            return;
        }

        bulkTurnoverModal.classList.add('opacity-0');
        bulkTurnoverModalPanel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        setTimeout(() => {
            bulkTurnoverModal.classList.add('hidden');
            if (restoreBulkTurnoverBodyOverflowOnClose) {
                document.body.classList.remove('overflow-hidden');
            }
            restoreBulkTurnoverBodyOverflowOnClose = false;
        }, 300);
        closeTurnoverSearchPanels();
    };

    const closeConditionConfirmModal = (confirmed = false) => {
        if (!conditionConfirmModal || conditionConfirmModal.classList.contains('hidden')) {
            if (conditionConfirmResolver) {
                const resolve = conditionConfirmResolver;
                conditionConfirmResolver = null;
                resolve(Boolean(confirmed));
            }
            return;
        }

        conditionConfirmModal.classList.add('opacity-0');
        conditionConfirmPanel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        setTimeout(() => {
            conditionConfirmModal.classList.add('hidden');
            if (restoreConditionConfirmBodyOverflowOnClose) {
                document.body.classList.remove('overflow-hidden');
            }
            restoreConditionConfirmBodyOverflowOnClose = false;
        }, 220);

        if (conditionConfirmResolver) {
            const resolve = conditionConfirmResolver;
            conditionConfirmResolver = null;
            resolve(Boolean(confirmed));
        }
    };

    const openConditionConfirmModal = (message) => new Promise((resolve) => {
        if (!conditionConfirmModal || !conditionConfirmMessage) {
            resolve(false);
            return;
        }

        if (conditionConfirmResolver) {
            const pendingResolve = conditionConfirmResolver;
            conditionConfirmResolver = null;
            pendingResolve(false);
        }

        conditionConfirmResolver = resolve;
        conditionConfirmMessage.textContent = String(message || 'Are you sure you want to continue?');
        restoreConditionConfirmBodyOverflowOnClose = !document.body.classList.contains('overflow-hidden');

        conditionConfirmModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                conditionConfirmModal.classList.remove('opacity-0');
                conditionConfirmPanel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
            });
        });

        document.body.classList.add('overflow-hidden');

        setTimeout(() => {
            if (!conditionConfirmYesBtn) {
                return;
            }

            try {
                conditionConfirmYesBtn.focus({ preventScroll: true });
            } catch (error) {
                conditionConfirmYesBtn.focus();
            }
        }, 230);
    });

    const setActionTab = (tabName = 'details') => {
        const paneMap = {
            transfer: actionTransferPane,
            turnover: actionTurnoverPane,
            condition: actionConditionPane,
        };

        if (tabName === 'details') {
            if (actionWorkspace) {
                actionWorkspace.classList.add('hidden');
            }

            detailSections.forEach((section) => section.classList.remove('hidden'));
        } else {
            if (actionWorkspace) {
                actionWorkspace.classList.remove('hidden');
            }

            detailSections.forEach((section) => section.classList.add('hidden'));

            Object.entries(paneMap).forEach(([name, pane]) => {
                if (!pane) {
                    return;
                }
                pane.classList.toggle('hidden', name !== tabName);
            });
        }

        actionTabButtons.forEach((button) => {
            const isActive = button.dataset.pqsActionTab === tabName;
            if (isActive) {
                button.classList.remove('border', 'border-gray-200', 'bg-white', 'text-gray-700');
                button.classList.add('bg-[#1a3a2d]', 'text-white');
            } else {
                button.classList.remove('bg-[#1a3a2d]', 'text-white');
                button.classList.add('border', 'border-gray-200', 'bg-white', 'text-gray-700');
            }
        });

        if (modalBody) {
            modalBody.scrollTop = 0;
        }
    };

    const setConditionMessage = (target, message = '', kind = 'error') => {
        if (!target) {
            return;
        }

        const hasMessage = String(message || '').trim() !== '';
        target.classList.toggle('hidden', !hasMessage);
        target.textContent = hasMessage ? message : '';

        if (kind === 'success') {
            target.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-700');
            target.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
        } else {
            target.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            target.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
        }
    };

    const setConditionLoading = (isLoading) => {
        if (!conditionUnserviceableBtn) {
            return;
        }

        if (isLoading) {
            conditionUnserviceableBtn.dataset.originalHtml = conditionUnserviceableBtn.innerHTML;
            conditionUnserviceableBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            conditionUnserviceableBtn.disabled = true;
            return;
        }

        if (conditionUnserviceableBtn.dataset.originalHtml) {
            conditionUnserviceableBtn.innerHTML = conditionUnserviceableBtn.dataset.originalHtml;
        }

        conditionUnserviceableBtn.disabled = false;
    };

    const setConditionReasonError = (message = '') => {
        if (!conditionReasonError || !conditionReasonCode) {
            return;
        }

        const hasMessage = String(message || '').trim() !== '';
        conditionReasonError.classList.toggle('hidden', !hasMessage);
        conditionReasonError.textContent = hasMessage ? message : '';
        conditionReasonCode.classList.toggle('border-rose-300', hasMessage);
        conditionReasonCode.classList.toggle('bg-rose-50/30', hasMessage);
    };

    const setConditionRemarksError = (message = '') => {
        if (!conditionRemarks || !conditionRemarksError) {
            return;
        }

        const hasMessage = String(message || '').trim() !== '';
        conditionRemarksError.classList.toggle('hidden', !hasMessage);
        conditionRemarksError.textContent = hasMessage ? message : '';
        conditionRemarks.classList.toggle('border-rose-300', hasMessage);
        conditionRemarks.classList.toggle('bg-rose-50/30', hasMessage);
    };

    const setConditionReasonHelpText = (conditionValue) => {
        if (!conditionReasonHelp) {
            return;
        }

        if (conditionValue === 'serviceable') {
            conditionReasonHelp.textContent = 'Recommended when marking as serviceable.';
            return;
        }

        conditionReasonHelp.textContent = 'Required when marking as unserviceable.';
    };

    const setConditionExpectedFixDateError = (message = '') => {
        if (!conditionExpectedFixDate || !conditionExpectedFixDateError) {
            return;
        }

        const hasMessage = String(message || '').trim() !== '';
        conditionExpectedFixDateError.classList.toggle('hidden', !hasMessage);
        conditionExpectedFixDateError.textContent = hasMessage ? message : '';
        conditionExpectedFixDate.classList.toggle('border-rose-300', hasMessage);
        conditionExpectedFixDate.classList.toggle('bg-rose-50/30', hasMessage);
    };

    const syncExpectedFixDateVisibility = (conditionValue = 'unserviceable') => {
        if (!conditionExpectedFixDateWrap || !conditionExpectedFixDate) {
            return;
        }

        const reasonValue = String(conditionReasonCode?.value || '').trim();
        const showExpectedFixDate = conditionValue === 'unserviceable' && reasonValue === 'for_repair';

        conditionExpectedFixDateWrap.classList.toggle('hidden', !showExpectedFixDate);
        if (!showExpectedFixDate) {
            conditionExpectedFixDate.value = '';
            setConditionExpectedFixDateError('');
        }
    };

    const applyConditionReasonOptions = (conditionValue = 'unserviceable') => {
        if (!conditionReasonCode || allConditionReasonOptions.length === 0) {
            return;
        }

        const selectedValue = String(conditionReasonCode.value || '');
        const allowed = allConditionReasonOptions.filter((entry) => {
            if (entry.value === '') {
                return true;
            }
            if (entry.condition === 'both') {
                return true;
            }
            return entry.condition === conditionValue;
        });

        conditionReasonCode.innerHTML = allowed.map((entry) => {
            const selectedAttr = entry.value === selectedValue ? ' selected' : '';
            return `<option value="${escapeHtml(entry.value)}"${selectedAttr}>${escapeHtml(entry.label)}</option>`;
        }).join('');

        const stillSelected = allowed.some((entry) => entry.value === selectedValue);
        if (!stillSelected) {
            conditionReasonCode.value = '';
        }

        setConditionReasonHelpText(conditionValue);
        syncExpectedFixDateVisibility(conditionValue);
    };

    const updateConditionButtonsState = (record) => {
        const isUnserviceable = normalizeAssetStatus(record?.asset_status) === statusForRepair;

        if (!conditionUnserviceableBtn) {
            return;
        }

        conditionUnserviceableBtn.disabled = isUnserviceable;
        conditionUnserviceableBtn.classList.toggle('opacity-60', isUnserviceable);
        conditionUnserviceableBtn.classList.toggle('cursor-not-allowed', isUnserviceable);
        conditionUnserviceableBtn.title = isUnserviceable
            ? 'Asset is already marked as unserviceable.'
            : 'Mark Unserviceable';
    };

    const renderConditionHistory = (movements = []) => {
        if (!conditionHistoryEl) {
            return;
        }

        const conditionMovements = Array.isArray(movements)
            ? movements.filter((movement) => ['maintenance_out', 'maintenance_in'].includes(String(movement?.movement_type || '').trim()))
            : [];

        if (conditionMovements.length === 0) {
            conditionHistoryEl.innerHTML = '<p class="text-gray-400 italic">No condition history yet.</p>';
            return;
        }

        conditionHistoryEl.innerHTML = conditionMovements.map((movement) => {
            const when = movement?.effective_at ? formatDate(movement.effective_at) : '—';
            const isUnserviceable = String(movement?.movement_type || '') === 'maintenance_out';
            const stateLabel = isUnserviceable ? 'Marked Unserviceable' : 'Marked Serviceable';
            const stateClass = isUnserviceable
                ? 'bg-rose-100 text-rose-700 border-rose-200'
                : 'bg-emerald-100 text-emerald-700 border-emerald-200';
            const byText = movement?.moved_by ? `By ${movement.moved_by}` : 'By System';
            const reasonCode = humanizeReasonCode(movement?.reason_code || '');
            const reasonHtml = reasonCode !== ''
                ? `<p class="mt-1 text-[11px] text-gray-600">Reason: <span class="font-medium text-gray-700">${escapeHtml(reasonCode)}</span></p>`
                : '';
            const remarks = String(movement?.remarks || '').trim();
            const remarksHtml = remarks !== ''
                ? `<p class="mt-1 text-[11px] text-gray-500">${escapeHtml(remarks)}</p>`
                : '';

            return `
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold ${stateClass}">${escapeHtml(stateLabel)}</span>
                        <span class="text-[11px] text-gray-500">${escapeHtml(when)}</span>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-600">${escapeHtml(byText)}</p>
                    ${reasonHtml}
                    ${remarksHtml}
                </div>
            `;
        }).join('');
    };

    const setTimelinePager = (currentPage = 1, lastPage = 1) => {
        timelineCurrentPage = currentPage;
        timelineLastPage = lastPage;

        if (!movementTimelinePager || !timelinePrevBtn || !timelineNextBtn || !timelinePageInfo) {
            return;
        }

        const showPager = lastPage > 1;
        movementTimelinePager.classList.toggle('hidden', !showPager);
        movementTimelinePager.classList.toggle('flex', showPager);

        timelinePrevBtn.disabled = currentPage <= 1;
        timelineNextBtn.disabled = currentPage >= lastPage;
        timelinePageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
    };

    const employeeName = (employee) => {
        if (!employee || typeof employee !== 'object') {
            return '';
        }

        if (typeof employee.full_name === 'string' && employee.full_name.trim() !== '') {
            return employee.full_name.trim();
        }

        if (typeof employee.name === 'string' && employee.name.trim() !== '') {
            return employee.name.trim();
        }

        const parts = [employee.first_name, employee.middle_name, employee.last_name, employee.suffix]
            .map((part) => String(part || '').trim())
            .filter((part) => part !== '');

        return parts.join(' ');
    };

    const buildEndpointDisplay = (locationName, custodianName, currentOwnerName) => {
        const location = String(locationName || '').trim();
        const custodian = String(custodianName || '').trim();
        const owner = String(currentOwnerName || '').trim();

        if (custodian && location) {
            return `${custodian} @ ${location}`;
        }

        if (custodian) {
            return custodian;
        }

        if (location && owner) {
            return `${location} (Owner: ${owner})`;
        }

        if (location) {
            return location;
        }

        if (owner) {
            return owner;
        }

        return 'Unspecified';
    };

    const mapTimelineItem = (movement, currentOwnerName = '') => {
        const fromLocation = movement?.from_location?.location_name
            || movement?.fromLocation?.location_name
            || movement?.from_location
            || '';
        const toLocation = movement?.to_location?.location_name
            || movement?.toLocation?.location_name
            || movement?.to_location
            || '';
        const fromCustodian = movement?.from_custodian_display
            || movement?.from_custodian?.full_name
            || movement?.fromCustodian?.full_name
            || '';
        const toCustodian = movement?.to_custodian_display
            || movement?.to_custodian?.full_name
            || movement?.toCustodian?.full_name
            || '';
        const ownerName = movement?.current_owner_display || currentOwnerName || toCustodian || fromCustodian || '';
        const fromDisplay = movement?.from_endpoint_display
            || buildEndpointDisplay(fromLocation, fromCustodian, ownerName);
        const toDisplay = movement?.to_endpoint_display
            || buildEndpointDisplay(toLocation, toCustodian, ownerName);
        const movedBy = movement?.moved_by
            || movement?.moved_by_account?.username
            || movement?.movedByAccount?.username
            || 'System';

        return {
            movement_type: movement?.movement_type || 'movement',
            reason_code: movement?.reason_code || '',
            from_display: fromDisplay,
            to_display: toDisplay,
            moved_by: movedBy,
            effective_at: movement?.effective_at || null,
            remarks: movement?.remarks || '',
        };
    };

    const renderMovementTimeline = (movements = [], currentPage = 1, lastPage = 1) => {
        if (!movementTimelineEl) {
            return;
        }

        if (!Array.isArray(movements) || movements.length === 0) {
            timelineEntriesCache = [];
            movementTimelineEl.innerHTML = '<p class="text-gray-400 italic">No movement records yet.</p>';
            renderConditionHistory([]);
            setTimelinePager(1, 1);
            return;
        }

        timelineEntriesCache = movements;
        renderConditionHistory(timelineEntriesCache);

        movementTimelineEl.innerHTML = movements.map((movement) => {
            const fromDisplay = movement.from_display || 'Unspecified';
            const toDisplay = movement.to_display || 'Unspecified';
            const movedBy = movement.moved_by || 'System';
            const when = movement.effective_at ? formatDate(movement.effective_at) : '—';
            const remarks = movement.remarks ? `<p class="mt-1 text-xs text-gray-500">${escapeHtml(movement.remarks)}</p>` : '';
            const movementType = String(movement.movement_type || 'movement').replace(/_/g, ' ');

            return `
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold text-gray-800">${escapeHtml(movementType)}</p>
                        <span class="text-xs text-gray-500">${escapeHtml(when)}</span>
                    </div>
                    <p class="mt-1 line-clamp-2 text-sm text-gray-700">${escapeHtml(fromDisplay)} → ${escapeHtml(toDisplay)}</p>
                    <p class="text-xs text-gray-500">By ${escapeHtml(movedBy)}</p>
                    ${remarks}
                </div>
            `;
        }).join('');

        setTimelinePager(currentPage, lastPage);
    };

    const loadMovementTimelinePage = async (propertyNo, page = 1) => {
        const normalizedPropertyNo = String(propertyNo || '').trim();
        if (!normalizedPropertyNo) {
            renderMovementTimeline([], 1, 1);
            return;
        }

        const requestToken = ++timelineRequestToken;

        try {
            const timelineUrl = timelineEndpointTemplate.replace('__PROPERTY__', encodeURIComponent(normalizedPropertyNo));
            const params = new URLSearchParams({ per_page: String(timelinePageSize), page: String(Math.max(1, page)) });
            const response = await fetch(`${timelineUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`Unable to fetch movement timeline (status ${response.status}).`);
            }

            const payload = await response.json().catch(() => ({}));
            if (requestToken !== timelineRequestToken) {
                return;
            }

            const pager = payload?.data?.movements || {};
            const currentOwnerName = employeeName(payload?.data?.asset?.currentCustodian)
                || employeeName(payload?.data?.asset?.current_custodian)
                || employeeName(payload?.data?.asset?.accountableOfficer)
                || employeeName(payload?.data?.asset?.accountable_officer)
                || '';
            const entries = Array.isArray(pager?.data)
                ? pager.data.map((item) => mapTimelineItem(item, currentOwnerName))
                : [];
            const currentPage = Number(pager?.current_page || 1);
            const lastPage = Number(pager?.last_page || 1);

            renderMovementTimeline(entries, currentPage, lastPage);
        } catch (error) {
            if (requestToken !== timelineRequestToken) {
                return;
            }

            renderMovementTimeline([], 1, 1);
        }
    };

    const syncSectionOptions = () => {
        if (!transferDivision || !transferSection) {
            return;
        }

        const selectedDivision = transferDivision.value;
        Array.from(transferSection.options).forEach((option) => {
            const divisionId = option.dataset.divisionId || '';
            const keepOption = option.value === '';
            const visible = keepOption || selectedDivision === '' || selectedDivision === divisionId;
            option.hidden = !visible;
            if (!visible && option.selected) {
                transferSection.value = '';
            }
        });
    };

    const applyLocationDefaults = () => {
        if (!transferLocation || !transferDivision || !transferSection) {
            return;
        }

        const selected = transferLocation.selectedOptions[0];
        if (!selected) {
            return;
        }

        const divisionId = selected.dataset.divisionId || '';
        const sectionId = selected.dataset.sectionId || '';

        if (divisionId) {
            transferDivision.value = divisionId;
        }

        syncSectionOptions();

        if (sectionId) {
            transferSection.value = sectionId;
        }
    };

    const setSelectLabel = (selectField, labelField) => {
        if (!selectField || !labelField) {
            return;
        }

        const selected = selectField.selectedOptions[0];
        const label = selected ? String(selected.textContent || '').trim() : 'Keep current';
        labelField.textContent = label || 'Keep current';
    };

    const setKeepCurrentOptionLabel = (selectField, labelText) => {
        if (!selectField) {
            return;
        }

        const keepCurrentOption = Array.from(selectField.options).find((option) => option.value === '');
        if (!keepCurrentOption) {
            return;
        }

        keepCurrentOption.textContent = labelText;
    };

    const syncTransferKeepCurrentLabels = (record) => {
        if (!record) {
            return;
        }

        const currentOwnerName = String(
            record.current_custodian?.name
            || record.accountable_officer?.name
            || ''
        ).trim();

        const currentLocationName = String(record.current_location?.name || '').trim();

        const ownerLabel = `Keep current: ${currentOwnerName || 'Unassigned owner'}`;
        const locationLabel = `Keep current: ${currentLocationName || 'Unassigned location'}`;

        setKeepCurrentOptionLabel(transferCustodian, ownerLabel);
        setKeepCurrentOptionLabel(transferLocation, locationLabel);
    };

    const closeTransferSearchPanels = () => {
        transferLocationPanel?.classList.add('hidden');
        transferCustodianPanel?.classList.add('hidden');
    };

    const closeTurnoverSearchPanels = () => {
        turnoverEmployeePanel?.classList.add('hidden');
        turnoverLocationPanel?.classList.add('hidden');
    };

    const renderSearchableList = ({ selectField, listField, queryText, onSelect }) => {
        if (!selectField || !listField) {
            return;
        }

        const normalizedQuery = String(queryText || '').trim().toLowerCase();
        const excludedValue = String(selectField.dataset.excludeValue || '').trim();

        if (excludedValue !== '' && String(selectField.value || '') === excludedValue) {
            selectField.value = '';
        }

        const options = Array.from(selectField.options).filter((option) => {
            if (option.value !== '' && excludedValue !== '' && String(option.value) === excludedValue) {
                return false;
            }

            const label = String(option.textContent || '').toLowerCase();
            return normalizedQuery === '' || label.includes(normalizedQuery);
        });

        if (options.length === 0) {
            listField.innerHTML = '<li class="px-3 py-2 text-xs text-gray-500">No matches found.</li>';
            return;
        }

        listField.innerHTML = options.map((option) => {
            const isSelected = selectField.value === option.value;
            const itemClass = isSelected
                ? 'bg-emerald-50 text-[#1a3a2d]'
                : 'text-gray-700 hover:bg-gray-50';

            return `<li><button type="button" data-option-value="${escapeHtml(option.value)}" class="w-full px-3 py-2 text-left text-sm ${itemClass}">${escapeHtml(String(option.textContent || '').trim())}</button></li>`;
        }).join('');

        Array.from(listField.querySelectorAll('button[data-option-value]')).forEach((button) => {
            button.addEventListener('click', () => {
                const value = button.getAttribute('data-option-value') || '';
                selectField.value = value;
                selectField.dispatchEvent(new Event('change', { bubbles: true }));
                if (onSelect) {
                    onSelect();
                }
            });
        });
    };

    const setupSearchableControl = ({
        selectField,
        toggleField,
        panelField,
        searchField,
        listField,
        labelField,
        onSelect,
    }) => {
        if (!selectField || !toggleField || !panelField || !searchField || !listField || !labelField) {
            return;
        }

        let activeIndex = -1;

        const getOptionButtons = () => Array.from(listField.querySelectorAll('button[data-option-value]'));

        const setActiveButton = (targetIndex) => {
            const buttons = getOptionButtons();
            if (buttons.length === 0) {
                activeIndex = -1;
                return;
            }

            const clampedIndex = Math.max(0, Math.min(targetIndex, buttons.length - 1));
            activeIndex = clampedIndex;

            buttons.forEach((button, index) => {
                const isActive = index === clampedIndex;
                button.classList.toggle('bg-emerald-100', isActive);
                button.classList.toggle('text-[#1a3a2d]', isActive);
                button.classList.toggle('ring-1', isActive);
                button.classList.toggle('ring-emerald-200', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            buttons[clampedIndex].scrollIntoView({ block: 'nearest' });
        };

        const render = () => {
            renderSearchableList({
                selectField,
                listField,
                queryText: searchField.value,
                onSelect: () => {
                    panelField.classList.add('hidden');
                    searchField.value = '';
                    setSelectLabel(selectField, labelField);
                    render();
                },
            });

            const buttons = getOptionButtons();
            if (buttons.length === 0) {
                activeIndex = -1;
                return;
            }

            const selectedIndex = buttons.findIndex((button) => button.getAttribute('data-option-value') === selectField.value);
            setActiveButton(selectedIndex >= 0 ? selectedIndex : 0);
        };

        toggleField.addEventListener('click', () => {
            const willOpen = panelField.classList.contains('hidden');
            closeTransferSearchPanels();
            closeTurnoverSearchPanels();
            if (willOpen) {
                panelField.classList.remove('hidden');
                render();
                searchField.focus();
            }
        });

        searchField.addEventListener('input', render);
        searchField.addEventListener('keydown', (event) => {
            const key = event.key;

            if (key === 'Escape') {
                panelField.classList.add('hidden');
                return;
            }

            if (key === 'Tab') {
                const buttons = getOptionButtons();
                if (!panelField.classList.contains('hidden') && activeIndex >= 0 && buttons[activeIndex]) {
                    buttons[activeIndex].click();
                }
                return;
            }

            if (!['ArrowDown', 'ArrowUp', 'Enter'].includes(key)) {
                return;
            }

            if (panelField.classList.contains('hidden')) {
                panelField.classList.remove('hidden');
                render();
            }

            const buttons = getOptionButtons();
            if (buttons.length === 0) {
                return;
            }

            if (key === 'ArrowDown') {
                event.preventDefault();
                const nextIndex = activeIndex < 0 ? 0 : Math.min(activeIndex + 1, buttons.length - 1);
                setActiveButton(nextIndex);
                return;
            }

            if (key === 'ArrowUp') {
                event.preventDefault();
                const prevIndex = activeIndex < 0 ? 0 : Math.max(activeIndex - 1, 0);
                setActiveButton(prevIndex);
                return;
            }

            if (key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                buttons[activeIndex].click();
            }
        });

        toggleField.addEventListener('keydown', (event) => {
            if (!['Enter', ' ', 'ArrowDown'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            const willOpen = panelField.classList.contains('hidden');
            closeTransferSearchPanels();
            closeTurnoverSearchPanels();
            if (willOpen) {
                panelField.classList.remove('hidden');
                render();
                searchField.focus();
            }
        });

        selectField.addEventListener('change', () => {
            setSelectLabel(selectField, labelField);
            if (onSelect) {
                onSelect();
            }
            render();
        });

        setSelectLabel(selectField, labelField);
        render();
    };

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const formatCurrency = (value) => {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '—';
        }
        const number = Number(value);
        return `₱${number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    const formatCurrencyPlain = (value) => {
        if (value === null || value === undefined || Number.isNaN(Number(value))) {
            return '';
        }
        return Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const formatDate = (value) => {
        if (!value) {
            return '—';
        }
        const parsed = new Date(value);
        if (Number.isNaN(parsed.valueOf())) {
            return String(value);
        }
        return parsed.toLocaleDateString('en-PH', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    const todayInputValue = () => {
        const now = new Date();
        const timezoneOffsetMs = now.getTimezoneOffset() * 60000;
        return new Date(now.getTime() - timezoneOffsetMs).toISOString().slice(0, 10);
    };

    const upsertLocationOption = (selectField, location, includePredicate = null) => {
        if (!selectField || !location) {
            return false;
        }

        if (typeof includePredicate === 'function' && !includePredicate(location)) {
            return false;
        }

        const optionValue = String(location.location_id || '').trim();
        if (optionValue === '') {
            return false;
        }

        const optionLabel = String(location.display_name || location.location_name || '').trim();
        const divisionId = location.division_id !== null && location.division_id !== undefined
            ? String(location.division_id)
            : '';
        const sectionId = location.section_id !== null && location.section_id !== undefined
            ? String(location.section_id)
            : '';

        let option = Array.from(selectField.options).find((entry) => String(entry.value) === optionValue);
        if (!option) {
            option = document.createElement('option');
            option.value = optionValue;
            selectField.appendChild(option);
        }

        option.textContent = optionLabel || `Location ${optionValue}`;
        if ('divisionId' in option.dataset) {
            option.dataset.divisionId = divisionId;
        } else if (divisionId !== '') {
            option.dataset.divisionId = divisionId;
        }

        if ('sectionId' in option.dataset) {
            option.dataset.sectionId = sectionId;
        } else if (sectionId !== '') {
            option.dataset.sectionId = sectionId;
        }

        return true;
    };

    const setBadgeState = (state) => {
        if (!documentBadge) {
            return;
        }

        const presets = {
            pending: {
                classes: 'bg-amber-100 text-amber-700',
                icon: 'fa-hourglass-half',
                label: 'Pending Document',
            },
            ics: {
                classes: 'bg-emerald-100 text-emerald-700',
                icon: 'fa-file-signature',
                label: 'ICS Document Issued',
            },
            par: {
                classes: 'bg-purple-100 text-purple-700',
                icon: 'fa-file-contract',
                label: 'PAR Document Issued',
            },
            both: {
                classes: 'bg-sky-100 text-sky-700',
                icon: 'fa-layer-group',
                label: 'ICS & PAR Documents',
            },
        };

        const preset = presets[state] ?? presets.pending;
        documentBadge.className = `inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-sm ${preset.classes}`;
        documentBadge.innerHTML = `<i class="fas ${preset.icon}"></i>${preset.label}`;
    };

    const setActivePrintLayout = (layout) => {
        if (parTemplate) {
            parTemplate.classList.toggle('hidden', layout !== 'par');
        }
        if (icsTemplate) {
            icsTemplate.classList.toggle('hidden', layout !== 'ics');
        }
        if (defaultTemplate) {
            defaultTemplate.classList.toggle('hidden', layout !== 'default');
        }
        // Print button removed; no action required for print control.
    };

    const closeModal = () => {
        closeConditionConfirmModal(false);
        modal.classList.add('opacity-0');
        if (modalPanel) {
            modalPanel.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        }
        setTimeout(() => modal.classList.add('hidden'), 300);
        document.body.classList.remove('overflow-hidden');
        document.body.classList.remove('pqs-printing');
    };

    const openModal = () => {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                modal.scrollTop = 0;
                if (modalPanel) {
                    modalPanel.scrollTop = 0;
                    modalPanel.querySelectorAll('.overflow-y-auto, .overflow-y-scroll').forEach((el) => {
                        el.scrollTop = 0;
                    });
                }

                modal.classList.remove('opacity-0');
                if (modalPanel) {
                    modalPanel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                }
            });
        });

        setTimeout(() => {
            if (modalFocusTarget) {
                try {
                    modalFocusTarget.focus({ preventScroll: true });
                } catch (error) {
                    modalFocusTarget.focus();
                }
            }

            modal.scrollTop = 0;
            if (modalPanel) {
                modalPanel.scrollTop = 0;
                modalPanel.querySelectorAll('.overflow-y-auto, .overflow-y-scroll').forEach((el) => {
                    el.scrollTop = 0;
                });
            }

        }, 300);

        document.body.classList.add('overflow-hidden');
        document.body.classList.remove('pqs-printing');
    };

    const resetModal = () => {
        currentRecord = null;
        setText(articleEl);
        setText(propertyNoEl);
        setText(dateAcquiredEl);
        setText(categoryEl);
        setText(quantityEl);
        setText(unitCostEl);
        setText(totalCostEl);
        setText(officerEl, '', 'Unassigned');
        setText(officerIdEl);
        setText(descriptionEl);
        setText(remarksEl, '', defaultRemarksText);
        setText(dateAcquiredChip);
        setText(snapshotArticleEl);
        setText(propertyNoSnapshotEl);
        setText(snapshotDateEl);
        setText(snapshotDocumentEl, '', defaultDocumentMeta);
        setText(snapshotQuantityEl);
        setText(unitEl);

        if (serialsEl) {
            serialsEl.innerHTML = defaultSerialHtml;
        }

        setActivePrintLayout('default');

            const acquiredDateText = null;

        setLineField(parFields.entity, defaultEntityName);
        setLineField(parFields.fundCluster, defaultFundCluster);
        setLineField(parFields.number, '');
        setText(parFields.quantity, '—');
        setText(parFields.unit, '—');
        setText(parFields.description, '—');
        setText(parFields.propertyNo, '—');
        setText(parFields.dateAcquired, '—');
        setText(parFields.amount, '—');
        setSignatureField(parFields.receivedBySignature, '', parFields.receivedBy);
        setLineField(parFields.receivedBy, '');
        setLineField(parFields.receivedByPosition, '');
        setLineField(parFields.receivedByDate, '');
        setLineField(parFields.receivedFrom, '');
        setLineField(parFields.receivedFromPosition, '');
        setLineField(parFields.receivedFromDate, '');

        setLineField(icsFields.entity, defaultEntityName);
        setLineField(icsFields.fundCluster, defaultFundCluster);
        setLineField(icsFields.number, '');
        setText(icsFields.quantity, '—');
        setText(icsFields.unit, '—');
        setText(icsFields.unitCost, '—');
        setText(icsFields.totalCost, '—');
        setText(icsFields.description, '—');
        setText(icsFields.inventoryNo, '—');
        setText(icsFields.usefulLife, '—');
        setSignatureField(icsFields.receivedBySignature, '', icsFields.receivedBy);
        setLineField(icsFields.receivedFrom, '');
        setLineField(icsFields.receivedFromPosition, '');
        setLineField(icsFields.receivedFromDate, '');
        setLineField(icsFields.receivedBy, '');
        setLineField(icsFields.receivedByPosition, '');
        setLineField(icsFields.receivedByDate, '');

        setBadgeState('pending');
        setText(currentLocationEl, '', 'Unassigned');
        setText(currentCustodianEl, '', 'Unassigned');
        setText(currentDivisionEl, '', 'Unassigned');
        setText(currentSectionEl, '', 'Unassigned');
        setText(currentConditionEl, 'Serviceable', 'Serviceable');
        setText(lastMovementEl, '', '—');
        renderConditionChip(statusActive);
        renderMovementTimeline([]);
        timelineRequestToken += 1;

        if (transferForm) {
            transferForm.reset();
            setTransferError('');
            clearTransferFieldErrors();
            if (transferLocationSearch) {
                transferLocationSearch.value = '';
            }
            if (transferCustodianSearch) {
                transferCustodianSearch.value = '';
            }
            closeTransferSearchPanels();
            setSelectLabel(transferLocation, transferLocationLabel);
            setSelectLabel(transferCustodian, transferCustodianLabel);
            syncSectionOptions();
        }

        if (turnoverForm) {
            turnoverForm.reset();
            clearTurnoverFieldErrors();
            if (turnoverEmployeeSearch) {
                turnoverEmployeeSearch.value = '';
            }
            closeTurnoverSearchPanels();
            setSelectLabel(turnoverEmployee, turnoverEmployeeLabel);
            setSelectLabel(turnoverLocation, turnoverLocationLabel);
        }

        if (conditionForm) {
            conditionForm.reset();
            if (conditionEffectiveAt) {
                conditionEffectiveAt.value = todayInputValue();
            }
            setConditionMessage(conditionSuccess, '', 'success');
            setConditionMessage(conditionError, '', 'error');
            setConditionReasonError('');
            setConditionExpectedFixDateError('');
            setConditionRemarksError('');
            syncExpectedFixDateVisibility('unserviceable');
        }

        if (conditionHistoryEl) {
            conditionHistoryEl.innerHTML = '<p class="text-gray-400 italic">No condition history yet.</p>';
        }

        updateConditionButtonsState(null);
        updateTransferActionAvailability(null);

        setActionTab('details');

        if (documentMeta) {
            documentMeta.textContent = defaultDocumentMeta;
        }
        if (documentExtra) {
            documentExtra.textContent = defaultDocumentExtra;
        }
    };

    const populateParTemplate = (parRecord, baseRecord) => {
        setActivePrintLayout('par');

        setLineField(parFields.entity, defaultEntityName);
        setLineField(parFields.fundCluster, defaultFundCluster);
        setLineField(parFields.number, parRecord?.par_no || '');

        const quantityValue = parRecord?.quantity ?? baseRecord.quantity;
        const quantityDisplay = quantityValue !== undefined && quantityValue !== null && quantityValue !== ''
            ? Number(quantityValue).toLocaleString('en-PH')
            : '—';
        setText(parFields.quantity, quantityDisplay);
        setText(parFields.unit, baseRecord.unit || '—');

        const description = parRecord?.article_desc || baseRecord.description || baseRecord.article || '—';
        setText(parFields.description, description);
        setText(parFields.propertyNo, baseRecord.property_no || '—');

        const acquiredDateText = parRecord?.date_acquired || baseRecord.date_acquired;
        setText(parFields.dateAcquired, formatDate(acquiredDateText));

        const amountValue = parRecord?.amount ?? parRecord?.total_cost ?? baseRecord.total_value;
        const amountPlain = formatCurrencyPlain(amountValue);
        setText(parFields.amount, amountPlain || '—');

        const receiver = baseRecord.received_by || baseRecord.current_custodian || baseRecord.accountable_officer || null;
        const receiverName = receiver?.name || '';
        const receiverOffice = receiver?.office || receiver?.position || '';
        setSignatureField(parFields.receivedBySignature, receiver?.signature || '', parFields.receivedBy);
        setLineField(parFields.receivedBy, receiverName);
        setLineField(parFields.receivedByPosition, receiverOffice);
        setLineField(parFields.receivedByDate, '');

        setLineField(parFields.receivedFrom, '');
        setLineField(parFields.receivedFromPosition, '');
        setLineField(parFields.receivedFromDate, '');
    };

    const populateIcsTemplate = (icsRecord, baseRecord) => {
        setActivePrintLayout('ics');

        setLineField(icsFields.entity, defaultEntityName);
        setLineField(icsFields.fundCluster, defaultFundCluster);
        setLineField(icsFields.number, icsRecord?.ics_no || '');

        const quantityValue = icsRecord?.quantity ?? baseRecord.quantity;
        const quantityDisplay = quantityValue !== undefined && quantityValue !== null && quantityValue !== ''
            ? Number(quantityValue).toLocaleString('en-PH')
            : '—';
        setText(icsFields.quantity, quantityDisplay);
        setText(icsFields.unit, baseRecord.unit || '—');

        const unitCostValue = icsRecord?.unit_cost ?? baseRecord.unit_value;
        const totalCostValue = icsRecord?.total_cost ?? baseRecord.total_value;
        const unitCostPlain = formatCurrencyPlain(unitCostValue);
        const totalCostPlain = formatCurrencyPlain(totalCostValue);
        setText(icsFields.unitCost, unitCostPlain || '—');
        setText(icsFields.totalCost, totalCostPlain || '—');

        const description = icsRecord?.description || baseRecord.description || baseRecord.article || '—';
        setText(icsFields.description, description);
        setText(icsFields.inventoryNo, baseRecord.property_no || '—');
        setText(icsFields.usefulLife, icsRecord?.estimated_useful_life || '—');
        const acquiredDateText = icsRecord?.date_acquired || baseRecord.date_acquired;

        const receiver = baseRecord.received_by || baseRecord.current_custodian || baseRecord.accountable_officer || null;
        const receiverName = receiver?.name || '';
        const receiverOffice = receiver?.office || receiver?.position || '';
        setLineField(icsFields.receivedFrom, '');
        setLineField(icsFields.receivedFromPosition, '');
        setLineField(icsFields.receivedFromDate, '');
        setSignatureField(icsFields.receivedBySignature, receiver?.signature || '', icsFields.receivedBy);
        setLineField(icsFields.receivedBy, receiverName);
        setLineField(icsFields.receivedByPosition, receiverOffice);
        setLineField(icsFields.receivedByDate, formatDate(acquiredDateText));
    };

    const populateModal = (record) => {
        if (!record) {
            return;
        }

        resetModal();

        const articleValue = record.article || record.description || '—';
        setText(articleEl, articleValue);
        setText(snapshotArticleEl, articleValue);

        const propertyNumber = record.property_no || '—';
        currentRecord = record;
        setText(propertyNoEl, propertyNumber);
        setText(propertyNoSnapshotEl, propertyNumber);

        const formattedDate = formatDate(record.date_acquired);
        setText(dateAcquiredEl, formattedDate);
        setText(dateAcquiredChip, formattedDate);
        setText(snapshotDateEl, formattedDate);

        const categoryParts = [];
        if (record.category_parent) {
            categoryParts.push(record.category_parent);
        }
        if (record.category) {
            categoryParts.push(record.category);
        }
        const categoryText = categoryParts.length ? categoryParts.join(' › ') : '—';
        setText(categoryEl, categoryText);

        setText(unitEl, record.unit);

        let quantityText = '—';
        if (record.quantity !== null && record.quantity !== undefined && record.quantity !== '') {
            const quantityNumber = Number(record.quantity);
            if (!Number.isNaN(quantityNumber)) {
                const unitSuffix = record.unit ? ` ${String(record.unit).trim()}` : '';
                quantityText = `${quantityNumber.toLocaleString('en-PH')}${unitSuffix}`;
            }
        }
        setText(quantityEl, quantityText);
        setText(snapshotQuantityEl, quantityText);

        const unitCostText = formatCurrency(record.unit_value);
        setText(unitCostEl, unitCostText);

        const totalCostText = formatCurrency(record.total_value);
        setText(totalCostEl, totalCostText);

        setText(officerEl, record.accountable_officer?.name, 'Unassigned');
        setText(officerIdEl, record.accountable_officer?.id);

        setText(descriptionEl, record.description);

        if (serialsEl) {
            if (Array.isArray(record.serial_numbers) && record.serial_numbers.length) {
                const serialItems = record.serial_numbers.map((serial, index) => `
                    <li class="flex items-center gap-3 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">#${index + 1}</span>
                        <span class="font-mono">${escapeHtml(serial)}</span>
                    </li>
                `);
                serialsEl.innerHTML = serialItems.join('');
            } else {
                serialsEl.innerHTML = defaultSerialHtml;
            }
        }

        setText(remarksEl, record.remarks, defaultRemarksText);
        setText(currentLocationEl, record.current_location?.name || '', 'Unassigned');
        setText(currentCustodianEl, record.current_custodian?.name || '', 'Unassigned');
        setText(currentDivisionEl, record.assigned_division || '', 'Unassigned');
        setText(currentSectionEl, record.assigned_section || '', 'Unassigned');
        renderConditionChip(record.asset_status);
        setText(lastMovementEl, formatDate(record.last_movement_at), '—');

        updateConditionButtonsState(record);
        updateTransferActionAvailability(record);
        setConditionReasonError('');
        setConditionExpectedFixDateError('');
        setConditionRemarksError('');

        if (conditionReasonCode) {
            conditionReasonCode.value = '';
        }
        if (conditionEffectiveAt && String(conditionEffectiveAt.value || '').trim() === '') {
            conditionEffectiveAt.value = todayInputValue();
        }
        applyConditionReasonOptions('unserviceable');

        const recentConditionEntries = Array.isArray(record.recent_movements)
            ? record.recent_movements.map((entry) => mapTimelineItem(entry))
            : [];
        renderConditionHistory(recentConditionEntries);

        syncTransferKeepCurrentLabels(record);

        loadMovementTimelinePage(record.property_no, 1);

        const currentOwnerId = String(
            record.current_custodian?.id
            || record.accountable_officer?.id
            || ''
        ).trim();
        const currentLocationId = String(record.current_location_id || '').trim();

        if (transferLocation) {
            transferLocation.dataset.excludeValue = currentLocationId;
            transferLocation.value = '';
        }
        if (transferCustodian) {
            transferCustodian.dataset.excludeValue = currentOwnerId;
            transferCustodian.value = '';
        }
        setSelectLabel(transferCustodian, transferCustodianLabel);
        setSelectLabel(transferLocation, transferLocationLabel);
        if (transferDivision) {
            transferDivision.value = record.assigned_division_id || '';
        }
        syncSectionOptions();
        if (transferSection) {
            transferSection.value = record.assigned_section_id || '';
        }
        if (transferMovementType) {
            transferMovementType.value = 'transfer';
        }
        if (transferReason) {
            transferReason.value = '';
        }
        if (transferRemarks) {
            transferRemarks.value = '';
        }
        setTransferError('');

        const { ics_record: icsRecord, par_record: parRecord } = record;

        let badgeState = 'pending';
        let snapshotDocumentText = defaultDocumentMeta;

        if (icsRecord && parRecord) {
            badgeState = 'both';
            const icsLabel = icsRecord.ics_no ? `ICS ${icsRecord.ics_no}` : 'ICS Issued';
            const parLabel = parRecord.par_no ? `PAR ${parRecord.par_no}` : 'PAR Issued';
            snapshotDocumentText = `${icsLabel} • ${parLabel}`;
        } else if (icsRecord) {
            badgeState = 'ics';
            snapshotDocumentText = icsRecord.ics_no ? `ICS ${icsRecord.ics_no}` : 'ICS Issued';
        } else if (parRecord) {
            badgeState = 'par';
            snapshotDocumentText = parRecord.par_no ? `PAR ${parRecord.par_no}` : 'PAR Issued';
        }

        setBadgeState(badgeState);
        setText(snapshotDocumentEl, snapshotDocumentText, defaultDocumentMeta);

        if (documentMeta && documentExtra) {
            if (icsRecord && parRecord) {
                documentMeta.textContent = snapshotDocumentText;
                const details = [];
                if (icsRecord.quantity !== undefined && icsRecord.quantity !== null) {
                    details.push(`ICS ${Number(icsRecord.quantity).toLocaleString('en-PH')} pcs`);
                }
                if (icsRecord.unit_cost !== undefined && icsRecord.unit_cost !== null) {
                    details.push(`ICS unit ${formatCurrency(icsRecord.unit_cost)}`);
                }
                if (icsRecord.total_cost !== undefined && icsRecord.total_cost !== null) {
                    details.push(`ICS total ${formatCurrency(icsRecord.total_cost)}`);
                }
                if (icsRecord.estimated_useful_life) {
                    details.push(`ICS useful life ${icsRecord.estimated_useful_life}`);
                }
                if (parRecord.quantity !== undefined && parRecord.quantity !== null) {
                    details.push(`PAR ${Number(parRecord.quantity).toLocaleString('en-PH')} pcs`);
                }
                if (parRecord.unit_value !== undefined && parRecord.unit_value !== null) {
                    details.push(`PAR unit ${formatCurrency(parRecord.unit_value)}`);
                }
                if (parRecord.amount !== undefined && parRecord.amount !== null) {
                    details.push(`PAR total ${formatCurrency(parRecord.amount)}`);
                }
                if (parRecord.date_acquired) {
                    details.push(`PAR acquired ${formatDate(parRecord.date_acquired)}`);
                }
                documentExtra.textContent = details.length ? details.join(' • ') : 'ICS & PAR custodial documents recorded.';
            } else if (icsRecord) {
                documentMeta.textContent = snapshotDocumentText;
                const details = [];
                if (icsRecord.quantity !== undefined && icsRecord.quantity !== null) {
                    details.push(`${Number(icsRecord.quantity).toLocaleString('en-PH')} pcs`);
                }
                if (icsRecord.unit_cost !== undefined && icsRecord.unit_cost !== null) {
                    details.push(`${formatCurrency(icsRecord.unit_cost)} unit cost`);
                }
                if (icsRecord.total_cost !== undefined && icsRecord.total_cost !== null) {
                    details.push(`${formatCurrency(icsRecord.total_cost)} total`);
                }
                if (icsRecord.estimated_useful_life) {
                    details.push(`${icsRecord.estimated_useful_life} useful life`);
                }
                documentExtra.textContent = details.length ? details.join(' • ') : 'Issued via Inventory Custodian Slip';
            } else if (parRecord) {
                documentMeta.textContent = snapshotDocumentText;
                const details = [];
                if (parRecord.quantity !== undefined && parRecord.quantity !== null) {
                    details.push(`${Number(parRecord.quantity).toLocaleString('en-PH')} pcs`);
                }
                if (parRecord.unit_value !== undefined && parRecord.unit_value !== null) {
                    details.push(`${formatCurrency(parRecord.unit_value)} unit value`);
                }
                if (parRecord.amount !== undefined && parRecord.amount !== null) {
                    details.push(`${formatCurrency(parRecord.amount)} total`);
                }
                if (parRecord.date_acquired) {
                    details.push(`Acquired ${formatDate(parRecord.date_acquired)}`);
                }
                documentExtra.textContent = details.length ? details.join(' • ') : 'Issued via Property Acknowledgement Receipt';
            } else {
                documentMeta.textContent = defaultDocumentMeta;
                documentExtra.textContent = defaultDocumentExtra;
            }
        }

        if (parRecord) {
            populateParTemplate(parRecord, record);
        } else if (icsRecord) {
            populateIcsTemplate(icsRecord, record);
        } else {
            setActivePrintLayout('default');
        }
    };

    const toggleLoadingState = (button, isLoading) => {
        if (!button) {
            return;
        }

        if (isLoading) {
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading';
            button.disabled = true;
        } else {
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
            button.disabled = false;
            delete button.dataset.originalHtml;
        }
    };

    const fetchAndPopulateRecord = async (url) => {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            const text = await response.text().catch(() => '<no response body>');
            throw new Error(`Unable to fetch PQS record (status ${response.status}): ${text}`);
        }

        const contentType = String(response.headers.get('content-type') || '').toLowerCase();
        if (!contentType.includes('application/json')) {
            const text = await response.text().catch(() => '<no response body>');
            throw new Error(`Expected JSON but received ${contentType || 'unknown content type'}: ${text.slice(0, 180)}`);
        }

        const payload = await response.json();
        if (!payload?.data) {
            throw new Error('Incomplete response received from server.');
        }

        populateModal(payload.data);
    };

    resultsContainer.addEventListener('click', async (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const trigger = target ? target.closest('.js-pqs-view') : null;
        if (!trigger) {
            return;
        }

        const propertyNo = String(trigger.getAttribute('data-property-no') || '').trim();
        const prebuiltDetailUrl = String(trigger.getAttribute('data-detail-url') || '').trim();
        const fallbackUrl = trigger.getAttribute('data-show-url');
        if (!propertyNo && !fallbackUrl && !prebuiltDetailUrl) {
            return;
        }

        const detailUrl = prebuiltDetailUrl
            || (propertyNo ? `{{ route('pqs.show.by-property') }}?property_no=${encodeURIComponent(propertyNo)}` : '')
            || fallbackUrl;

        event.preventDefault();

        try {
            toggleLoadingState(trigger, true);
            currentShowUrl = detailUrl;
            await fetchAndPopulateRecord(detailUrl);
            openModal();
        } catch (error) {
            console.error(error);
            const message = error?.message || 'Unable to load PQS record details right now. Please try again shortly.';
            alert('Unable to load PQS record details: ' + message);
        } finally {
            toggleLoadingState(trigger, false);
        }
    });

    // Attach click handlers directly to any element with [data-close-modal]
    // inside the modal so the Close/X controls always work.
    try {
        const closeEls = modal.querySelectorAll('[data-close-modal]');
        closeEls.forEach((el) => el.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        }));
    } catch (e) {
        // ignore if modal not ready
    }

    modal.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-close-modal]') : null;
        if (target) {
            closeModal();
        }
    });

    // Fallback: listen at document level so close controls work even
    // if the modal listener isn't reachable (e.g. DOM replacement).
    document.addEventListener('click', (event) => {
        if (!modal || modal.classList.contains('hidden')) {
            return;
        }
        try {
            const clicked = event.target instanceof Element ? event.target.closest('[data-close-modal]') : null;
            if (clicked) {
                closeModal();
            }
        } catch (e) {
            // ignore
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        if (conditionConfirmModal && !conditionConfirmModal.classList.contains('hidden')) {
            closeConditionConfirmModal(false);
            return;
        }

        if (bulkTurnoverModal && !bulkTurnoverModal.classList.contains('hidden')) {
            closeBulkTurnoverModal();
            return;
        }

        if (!modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    timelinePrevBtn?.addEventListener('click', () => {
        if (!currentRecord?.property_no || timelineCurrentPage <= 1) {
            return;
        }

        loadMovementTimelinePage(currentRecord.property_no, timelineCurrentPage - 1);
    });

    timelineNextBtn?.addEventListener('click', () => {
        if (!currentRecord?.property_no || timelineCurrentPage >= timelineLastPage) {
            return;
        }

        loadMovementTimelinePage(currentRecord.property_no, timelineCurrentPage + 1);
    });

    transferLocation?.addEventListener('change', () => {
        applyLocationDefaults();
        clearTransferFieldError('to_location_id');
    });
    transferCustodian?.addEventListener('change', () => clearTransferFieldError('to_custodian_employee_id'));

    actionTabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (button.disabled) {
                return;
            }
            const tabName = button.dataset.pqsActionTab || 'transfer';
            setActionTab(tabName);
        });
    });

    transferDivision?.addEventListener('change', () => {
        syncSectionOptions();
        clearTransferFieldError('to_division_id');
    });

    transferSection?.addEventListener('change', () => clearTransferFieldError('to_section_id'));
    transferMovementType?.addEventListener('change', () => clearTransferFieldError('movement_type'));
    transferReason?.addEventListener('input', () => clearTransferFieldError('reason_code'));
    transferRemarks?.addEventListener('input', () => clearTransferFieldError('remarks'));

    turnoverEmployee?.addEventListener('change', () => clearTurnoverFieldError('employee_id'));
    turnoverEmployee?.addEventListener('change', loadTurnoverPreview);
    turnoverLocation?.addEventListener('change', () => clearTurnoverFieldError('stockroom_location_id'));
    turnoverEffectiveAt?.addEventListener('change', () => clearTurnoverFieldError('effective_at'));
    turnoverRemarks?.addEventListener('input', () => clearTurnoverFieldError('remarks'));
    turnoverConfirm?.addEventListener('input', () => {
        turnoverConfirm.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');
        turnoverConfirm.classList.add('border-gray-200');
    });

    bulkTurnoverOpenBtn?.addEventListener('click', openBulkTurnoverModal);
    bulkTurnoverCloseEls.forEach((el) => el.addEventListener('click', closeBulkTurnoverModal));
    bulkTurnoverModal?.addEventListener('click', (event) => {
        if (event.target === bulkTurnoverModal) {
            closeBulkTurnoverModal();
        }
    });

    setupSearchableControl({
        selectField: transferLocation,
        toggleField: transferLocationToggle,
        panelField: transferLocationPanel,
        searchField: transferLocationSearch,
        listField: transferLocationList,
        labelField: transferLocationLabel,
        onSelect: () => {
            applyLocationDefaults();
            clearTransferFieldError('to_location_id');
        },
    });

    setupSearchableControl({
        selectField: transferCustodian,
        toggleField: transferCustodianToggle,
        panelField: transferCustodianPanel,
        searchField: transferCustodianSearch,
        listField: transferCustodianList,
        labelField: transferCustodianLabel,
        onSelect: () => {
            clearTransferFieldError('to_custodian_employee_id');
        },
    });

    setupSearchableControl({
        selectField: turnoverEmployee,
        toggleField: turnoverEmployeeToggle,
        panelField: turnoverEmployeePanel,
        searchField: turnoverEmployeeSearch,
        listField: turnoverEmployeeList,
        labelField: turnoverEmployeeLabel,
        onSelect: () => {
            clearTurnoverFieldError('employee_id');
        },
    });

    setupSearchableControl({
        selectField: turnoverLocation,
        toggleField: turnoverLocationToggle,
        panelField: turnoverLocationPanel,
        searchField: turnoverLocationSearch,
        listField: turnoverLocationList,
        labelField: turnoverLocationLabel,
        onSelect: () => {
            clearTurnoverFieldError('stockroom_location_id');
        },
    });

    document.addEventListener('location-registry:created', (event) => {
        const location = event?.detail?.location;
        if (!location) {
            return;
        }

        const transferUpdated = upsertLocationOption(
            transferLocation,
            location,
            (entry) => !Boolean(entry.is_storage)
        );
        if (transferUpdated && transferLocation) {
            transferLocation.value = String(location.location_id);
            transferLocation.dispatchEvent(new Event('change', { bubbles: true }));
            setSelectLabel(transferLocation, transferLocationLabel);
            if (!transferLocationPanel?.classList.contains('hidden')) {
                transferLocationSearch?.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        const turnoverUpdated = upsertLocationOption(
            turnoverLocation,
            location,
            (entry) => Boolean(entry.is_storage)
        );

        const shouldPreferCreatedStockroom = pendingTurnoverStockroomSelection
            || Boolean(bulkTurnoverModal && !bulkTurnoverModal.classList.contains('hidden'));

        if (turnoverUpdated && turnoverLocation && (shouldPreferCreatedStockroom || !turnoverLocation.value)) {
            turnoverLocation.value = String(location.location_id);
            turnoverLocation.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (turnoverUpdated && !turnoverLocationPanel?.classList.contains('hidden')) {
            turnoverLocationSearch?.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (turnoverUpdated && turnoverStockroomHint) {
            turnoverStockroomHint.classList.add('hidden');
        }

        pendingTurnoverStockroomSelection = false;
    });

    turnoverAddStockroomBtn?.addEventListener('click', () => {
        pendingTurnoverStockroomSelection = true;
        clearTurnoverFieldError('stockroom_location_id');
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) {
            return;
        }

        const clickedInLocation = target.closest('#pqsTransferLocationPanel') || target.closest('#pqsTransferLocationToggle');
        const clickedInCustodian = target.closest('#pqsTransferCustodianPanel') || target.closest('#pqsTransferCustodianToggle');
        const clickedInTurnoverEmployee = target.closest('#pqsTurnoverEmployeePanel') || target.closest('#pqsTurnoverEmployeeToggle');
        const clickedInTurnoverLocation = target.closest('#pqsTurnoverLocationPanel') || target.closest('#pqsTurnoverLocationToggle');
        if (!clickedInLocation && !clickedInCustodian) {
            closeTransferSearchPanels();
        }
        if (!clickedInTurnoverEmployee && !clickedInTurnoverLocation) {
            closeTurnoverSearchPanels();
        }
    });

    transferForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const blockedMessage = getTransferBlockedMessage(currentRecord?.asset_status);
        if (blockedMessage !== '') {
            clearTransferFieldErrors();
            setTransferError(blockedMessage);
            notifyError(blockedMessage);
            return;
        }

        if (!currentRecord?.property_no) {
            clearTransferFieldErrors();
            setTransferError('Open a record first before submitting movement.');
            return;
        }

        clearTransferFieldErrors();
        setTransferError('');
        setTransferLoading(true);

        try {
            const transferUrl = "{{ route('pqs.movements.transfer', ['pqsRecord' => '__PROPERTY__']) }}".replace('__PROPERTY__', encodeURIComponent(currentRecord.property_no));

            const payload = {
                movement_type: transferMovementType?.value || 'transfer',
                to_location_id: transferLocation?.value || null,
                to_custodian_employee_id: transferCustodian?.value || null,
                to_division_id: transferDivision?.value || null,
                to_section_id: transferSection?.value || null,
                reason_code: transferReason?.value?.trim() || null,
                remarks: transferRemarks?.value?.trim() || null,
            };

            const response = await fetch(transferUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            const responseContentType = String(response.headers.get('content-type') || '').toLowerCase();
            const data = responseContentType.includes('application/json')
                ? await response.json().catch(() => ({}))
                : {};
            if (!response.ok) {
                if (data?.errors) {
                    setTransferFieldErrors(data.errors);
                }
                const fallbackMessage = !responseContentType.includes('application/json')
                    ? `Request failed with status ${response.status}.`
                    : '';
                const validationMessage = data?.message || Object.values(data?.errors || {}).flat().join(' ') || fallbackMessage || 'Failed to save movement.';
                throw new Error(validationMessage);
            }

            if (currentShowUrl) {
                await fetchAndPopulateRecord(currentShowUrl);
            }

            await window.pqsFetchRecords?.();
            const successMessage = data?.message || 'Asset movement has been recorded successfully.';
            notifySuccess(successMessage);
            closeModal();
        } catch (error) {
            const message = error?.message || 'Unable to save movement right now.';
            setTransferError(message);
            notifyError(message);
        } finally {
            setTransferLoading(false);
        }
    });

    turnoverForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        clearTurnoverFieldErrors();

        if (turnoverEffectiveAt && String(turnoverEffectiveAt.value || '').trim() === '') {
            turnoverEffectiveAt.value = todayInputValue();
        }

        if ((turnoverConfirm?.value || '').trim().toUpperCase() !== 'CONFIRM') {
            if (turnoverConfirm) {
                turnoverConfirm.classList.remove('border-gray-200');
                turnoverConfirm.classList.add('border-rose-400', 'ring-2', 'ring-rose-200');
            }
            notifyError('Type CONFIRM before processing bulk turnover.');
            return;
        }

        setTurnoverLoading(true);

        try {
            const turnoverUrl = "{{ route('pqs.movements.turnover') }}";

            const payload = {
                employee_id: turnoverEmployee?.value || null,
                stockroom_location_id: turnoverLocation?.value || null,
                effective_at: turnoverEffectiveAt?.value || null,
                remarks: turnoverRemarks?.value?.trim() || null,
            };

            const response = await fetch(turnoverUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (data?.errors) {
                    setTurnoverFieldErrors(data.errors);
                }
                const validationMessage = data?.message || Object.values(data?.errors || {}).flat().join(' ') || 'Failed to process turnover.';
                throw new Error(validationMessage);
            }

            const baseSuccessMessage = data?.message || 'Employee asset turnover completed successfully.';
            const batchReference = String(data?.data?.batch_reference || '').trim();
            const successMessage = batchReference
                ? `${baseSuccessMessage} Batch Reference: ${batchReference}`
                : baseSuccessMessage;
            notifySuccess(successMessage);
            turnoverForm?.reset();
            setSelectLabel(turnoverEmployee, turnoverEmployeeLabel);
            setSelectLabel(turnoverLocation, turnoverLocationLabel);
            if (turnoverConfirm) {
                turnoverConfirm.value = '';
            }
            setTurnoverPreview(null);

            if (currentShowUrl) {
                await fetchAndPopulateRecord(currentShowUrl);
            }

            await window.pqsFetchRecords?.();
            closeBulkTurnoverModal();
        } catch (error) {
            const errorMessage = error?.message || 'Unable to process turnover right now.';
            notifyError(errorMessage);
        } finally {
            setTurnoverLoading(false);
        }
    });

    const submitConditionUpdate = async (conditionValue) => {
        if (!currentRecord?.property_no || !conditionValue) {
            setConditionMessage(conditionError, 'Open a record first before updating condition.', 'error');
            return;
        }

        setConditionMessage(conditionError, '', 'error');
        setConditionMessage(conditionSuccess, '', 'success');
        setConditionRemarksError('');

        if (conditionEffectiveAt && String(conditionEffectiveAt.value || '').trim() === '') {
            conditionEffectiveAt.value = todayInputValue();
        }

        const remarksValue = String(conditionRemarks?.value || '').trim();
        if (remarksValue === '') {
            setConditionRemarksError('Remarks is required when marking an asset as unserviceable.');
            setConditionMessage(conditionError, 'Provide remarks first.', 'error');
            return;
        }

        const effectiveDateLabel = String(conditionEffectiveAt?.value || '').trim() || todayInputValue();
        const confirmMessage = [
            'You are about to mark this asset as UNSERVICEABLE.',
            `Property No: ${currentRecord.property_no}`,
            `Effective Date: ${effectiveDateLabel}`,
            '',
            'This action cannot be reverted from this screen.',
            'Press Mark Unserviceable to continue or Cancel to review first.'
        ].join('\n');

        const isConfirmed = await openConditionConfirmModal(confirmMessage);
        if (!isConfirmed) {
            return;
        }

        setConditionLoading(true);

        try {
            const conditionUrl = "{{ route('pqs.movements.condition', ['pqsRecord' => '__PROPERTY__']) }}".replace('__PROPERTY__', encodeURIComponent(currentRecord.property_no));

            const payload = {
                condition: conditionValue,
                effective_at: conditionEffectiveAt?.value || null,
                remarks: remarksValue,
            };

            const response = await fetch(conditionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (data?.errors?.remarks?.length) {
                    setConditionRemarksError(String(data.errors.remarks[0] || ''));
                }
                const message = data?.message || Object.values(data?.errors || {}).flat().join(' ') || 'Failed to update asset condition.';
                throw new Error(message);
            }

            const successMessage = data?.message || 'Asset condition updated successfully.';
            setConditionMessage(conditionSuccess, successMessage, 'success');
            notifySuccess(successMessage);

            if (currentShowUrl) {
                await fetchAndPopulateRecord(currentShowUrl);
            }

            await window.pqsFetchRecords?.();
            closeModal();
        } catch (error) {
            const errorMessage = error?.message || 'Unable to update condition right now.';
            setConditionMessage(conditionError, errorMessage, 'error');
            notifyError(errorMessage);
        } finally {
            setConditionLoading(false);
        }
    };

    conditionUnserviceableBtn?.addEventListener('click', () => submitConditionUpdate('unserviceable'));
    conditionReasonCode?.addEventListener('change', () => {
        setConditionReasonError('');
        setConditionExpectedFixDateError('');

        const selectedOption = conditionReasonCode.selectedOptions?.[0] || null;
        const optionCondition = String(selectedOption?.dataset?.condition || 'unserviceable');
        const inferredCondition = optionCondition === 'serviceable' ? 'serviceable' : 'unserviceable';
        syncExpectedFixDateVisibility(inferredCondition);
    });
    conditionExpectedFixDate?.addEventListener('change', () => setConditionExpectedFixDateError(''));
    conditionRemarks?.addEventListener('input', () => setConditionRemarksError(''));
    conditionConfirmCloseEls.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            closeConditionConfirmModal(false);
        });
    });
    conditionConfirmYesBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        closeConditionConfirmModal(true);
    });

    const loadReconciliationSummary = async () => {
        if (!reconcileEndpoint) {
            return;
        }

        try {
            recTopLocations && (recTopLocations.innerHTML = '<li class="text-gray-400 italic">Loading summary...</li>');

            const response = await fetch(reconcileEndpoint, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load reconciliation summary.');
            }

            const payload = await response.json();
            const totals = payload?.data?.totals || {};
            const byLocation = payload?.data?.breakdown?.by_location || [];

            recAssets && (recAssets.textContent = Number(totals.assets || 0).toLocaleString('en-PH'));
            recActive && (recActive.textContent = Number(totals.active || 0).toLocaleString('en-PH'));
            recRepair && (recRepair.textContent = Number(totals.for_repair || 0).toLocaleString('en-PH'));
            recUnlocated && (recUnlocated.textContent = Number(totals.unlocated || 0).toLocaleString('en-PH'));
            recStale && (recStale.textContent = Number(totals.stale_inventory || 0).toLocaleString('en-PH'));

            if (recTopLocations) {
                if (!Array.isArray(byLocation) || byLocation.length === 0) {
                    recTopLocations.innerHTML = '<li class="text-gray-400 italic">No location distribution yet.</li>';
                } else {
                    recTopLocations.innerHTML = byLocation.slice(0, 4).map((item) => {
                        const label = item.location_name || 'Unassigned';
                        const total = Number(item.total || 0).toLocaleString('en-PH');
                        return `<li class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"><span class="truncate pr-3">${escapeHtml(label)}</span><span class="font-semibold text-[#1a3a2d]">${total}</span></li>`;
                    }).join('');
                }
            }
        } catch (error) {
            recTopLocations && (recTopLocations.innerHTML = '<li class="text-rose-600 text-xs">Unable to load summary.</li>');
        }
    };

    recRefresh?.addEventListener('click', loadReconciliationSummary);
    loadReconciliationSummary();
})();

// PDF and Excel Export Handlers
(function() {
    const pdfBtn = document.getElementById('pqsPrintPdfBtn');
    const excelBtn = document.getElementById('pqsExportExcelBtn');
    const form = document.getElementById('pqsFiltersForm');

    if (pdfBtn) {
        pdfBtn.addEventListener('click', function() {
            const search = document.getElementById('search')?.value || '';
            const category = document.getElementById('category')?.value || '';
            const assignment = document.getElementById('assignment')?.value || '';
            const condition = document.getElementById('condition')?.value || '';
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (assignment) params.append('assignment', assignment);
            if (condition) params.append('condition', condition);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const url = '{{ route("pqs.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
            window.open(url, '_blank');
        });
    }

    if (excelBtn) {
        excelBtn.addEventListener('click', function() {
            const search = document.getElementById('search')?.value || '';
            const category = document.getElementById('category')?.value || '';
            const assignment = document.getElementById('assignment')?.value || '';
            const condition = document.getElementById('condition')?.value || '';
            const dateFrom = document.getElementById('date_from')?.value || '';
            const dateTo = document.getElementById('date_to')?.value || '';

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (assignment) params.append('assignment', assignment);
            if (condition) params.append('condition', condition);
            if (dateFrom) params.append('date_from', dateFrom);
            if (dateTo) params.append('date_to', dateTo);

            const url = '{{ route("pqs.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        });
    }
})();
</script>
@endpush