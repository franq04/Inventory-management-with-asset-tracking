@extends('layouts.app')

@section('title', 'PQS Records')

@section('content')
@php
    $completion = ($stats['total'] > 0) ? (($stats['total'] - $stats['unassigned']) / max($stats['total'], 1)) * 100 : 0;
@endphp
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                        <div class="relative lg:col-span-1">
                            <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Search</label>
                            <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 mt-2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                            <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Property no., article, officer..." class="w-full mt-1 rounded-2xl border border-emerald-950/10 bg-[#f7faf8] py-[9px] pl-11 pr-3 text-sm shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:ring-4 focus:ring-[#1a3a2d]/10" />
                        </div>
                        <div id="categoryDropdown" class="relative z-20">
                            <label for="category" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Category</label>

                            <input type="hidden" name="category" id="category" value="{{ $categoryFilter ?? '' }}" data-auto-submit>

                            <button id="categoryDropdownToggle" type="button" class="mt-1 flex w-full py-[9px] items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50" aria-haspopup="listbox" aria-expanded="false">
                                <span id="categoryDropdownLabel" class="block truncate text-gray-700 text-sm">
                                    {{ $categoryFilter ? ($categories->where('cat_id', $categoryFilter)->first()->cat_name ?? 'All Categories') : 'All Categories' }}
                                </span>
                                <i id="categoryDropdownChevron" class="fas fa-chevron-down text-[#2d5a4a]/45 text-xs transition-transform duration-200"></i>
                            </button>

                            <div id="categoryDropdownMenu" class="absolute z-50 mt-2 hidden w-full origin-top-right rounded-2xl border border-gray-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.12)] ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="px-3 pt-3 pb-2 border-b border-gray-50">
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input id="categoryDropdownSearch" type="text" placeholder="Search prefix..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500" autocomplete="off">
                                    </div>
                                </div>

                                <ul id="categoryDropdownOptions" class="max-h-56 overflow-y-auto py-1 custom-scrollbar" role="listbox">
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

                                    <li id="categoryDropdownEmpty" class="hidden py-3 px-4 text-center text-sm text-gray-500 italic">
                                        No categories found
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div>
                             <label for="assignment" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Assignment Status</label>
                            <select id="assignment" name="assignment" data-auto-submit class="mt-1 w-full rounded-2xl border border-emerald-950/10 bg-white shadow-sm py-[9px] px-3 text-sm focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50">
                                <option value="" @selected(!$assignmentFilter)>Any</option>
                                <option value="ics" @selected($assignmentFilter === 'ics')>With ICS</option>
                                <option value="par" @selected($assignmentFilter === 'par')>With PAR</option>
                                <option value="unassigned" @selected($assignmentFilter === 'unassigned')>Unassigned</option>
                            </select>
                        </div>
                    </div>
                     <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                         <span class="text-sm text-gray-500"><span id="pqs-record-count">{{ number_format($records->total()) }}</span> records found</span>
                         <div class="flex flex-wrap items-center gap-2">
                            <a id="pqsResetFilters" href="{{ route('pqs.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white" title="Reset Filters">
                               <i class="fas fa-undo"></i>
                           </a>
                           <button type="button" id="pqsPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm transition-all duration-300 hover:border-red-300 hover:bg-red-100 hover:shadow-md">
                                <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                            </button>
                            <button type="button" id="pqsExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-700 shadow-sm transition-all duration-300 hover:border-green-300 hover:bg-green-100 hover:shadow-md">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
                    </div>
                     <button type="submit" class="sr-only">Submit</button>
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
        setLineField(parFields.receivedBy, '');
        setLineField(parFields.receivedByPosition, '');
        setLineField(parFields.receivedByDate, formatDate(acquiredDateText));
        setLineField(parFields.receivedFrom, '');
        setLineField(parFields.receivedFromPosition, '');
        setLineField(parFields.receivedFromDate, formatDate(acquiredDateText));

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
        setLineField(icsFields.receivedFrom, '');
        setLineField(icsFields.receivedFromPosition, '');
        setLineField(icsFields.receivedFromDate, '');
        setLineField(icsFields.receivedBy, '');
        setLineField(icsFields.receivedByPosition, '');
        setLineField(icsFields.receivedByDate, '');

        setBadgeState('pending');

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

        const officerName = baseRecord.accountable_officer?.name || '';
        const officerPosition = baseRecord.accountable_officer?.position || '';
        setLineField(parFields.receivedBy, officerName);
        setLineField(parFields.receivedByPosition, officerPosition);
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

        const officerName = baseRecord.accountable_officer?.name || '';
        const officerPosition = baseRecord.accountable_officer?.position || '';
    setLineField(icsFields.receivedFrom, '');
    setLineField(icsFields.receivedFromPosition, '');
        setLineField(icsFields.receivedFromDate, formatDate(acquiredDateText));
        setLineField(icsFields.receivedBy, officerName);
        setLineField(icsFields.receivedByPosition, officerPosition);
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

    resultsContainer.addEventListener('click', async (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const trigger = target ? target.closest('.js-pqs-view') : null;
        if (!trigger) {
            return;
        }

        const url = trigger.getAttribute('data-show-url');
        if (!url) {
            return;
        }

        event.preventDefault();

        try {
            toggleLoadingState(trigger, true);
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

            const payload = await response.json();
            if (!payload?.data) {
                throw new Error('Incomplete response received from server.');
            }

            populateModal(payload.data);
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
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
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

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (assignment) params.append('assignment', assignment);

            const url = '{{ route("pqs.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
            window.open(url, '_blank');
        });
    }

    if (excelBtn) {
        excelBtn.addEventListener('click', function() {
            const search = document.getElementById('search')?.value || '';
            const category = document.getElementById('category')?.value || '';
            const assignment = document.getElementById('assignment')?.value || '';

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (assignment) params.append('assignment', assignment);

            const url = '{{ route("pqs.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        });
    }
})();
</script>
@endpush