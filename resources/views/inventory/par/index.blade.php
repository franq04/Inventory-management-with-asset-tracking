@extends('layouts.app')

@section('title', 'PAR Registry')

@section('content')
<div class="space-y-8 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">PAR Registry</h2>
            <p class="mt-1 text-gray-500">Property Acknowledgement Receipts for accountable assets.</p>
        </div>
        {{-- In the future, an "Add PAR" button could go here --}}
    </div>

    {{-- Enhanced Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Total PAR Records', 'value' => number_format($stats['total']), 'icon' => 'fa-file-contract', 'color' => 'blue'],
                ['label' => 'Total Inventory Value', 'value' => '₱' . number_format($stats['totalAmount'], 2), 'icon' => 'fa-coins', 'color' => 'emerald'],
                ['label' => 'Avg. Useful Life', 'value' => ($stats['averageUsefulLife'] ? number_format($stats['averageUsefulLife'], 1) : '0.0') . ' yrs', 'icon' => 'fa-hourglass-half', 'color' => 'purple'],
                ['label' => 'Unassigned Items', 'value' => number_format($stats['unassigned']), 'icon' => 'fa-question-circle', 'color' => 'amber'],
            ];
            $colors = [
                'blue' => 'bg-blue-100 text-blue-600',
                'emerald' => 'bg-emerald-100 text-emerald-600',
                'purple' => 'bg-purple-100 text-purple-600',
                'amber' => 'bg-amber-100 text-amber-600',
            ];
        @endphp
        @foreach ($statCards as $card)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex items-center gap-5 transition hover:shadow-xl hover:-translate-y-1">
            <div class="flex-shrink-0 w-14 h-14 rounded-full flex items-center justify-center {{ $colors[$card['color']] }}">
                <i class="fa-solid {{ $card['icon'] }} text-2xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ $card['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content Column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Enhanced Filters & Actions --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4">
                <form id="parFiltersForm" method="GET" action="{{ route('par.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1fr,auto,auto] gap-4 items-end">
                        <div class="relative w-full">
                            <label for="search" class="text-xs font-semibold text-gray-500">Search</label>
                            <i class="fas fa-search absolute left-4 top-1/2 mt-2 -translate-y-1/2 text-gray-400"></i>
                            <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search by PAR no., property no., or article..." class="w-full mt-1 rounded-xl border-gray-200 pl-11 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" />
                        </div>
                        <div>
                            <label for="categories" class="text-xs font-semibold text-gray-500">Category</label>
                            <select id="categories" name="categories[]" data-auto-submit class="mt-1 w-full rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->cat_id }}" @selected(in_array($category->cat_id, $categoryFilters ?? []))>{{ $category->cat_name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div>
                            <label for="assignment" class="text-xs font-semibold text-gray-500">Assignment</label>
                            <select id="assignment" name="assignment" data-auto-submit class="mt-1 w-full rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                               <option value="" @selected(!$assignmentFilter)>Any</option>
                               <option value="assigned" @selected($assignmentFilter === 'assigned')>Assigned</option>
                               <option value="unassigned" @selected($assignmentFilter === 'unassigned')>Unassigned</option>
                            </select>
                        </div>
                    </div>
                     <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                         <span class="text-sm text-gray-500"><span id="par-record-count">{{ number_format($records->total()) }}</span> records found</span>
                         <div class="flex items-center gap-2 flex-wrap">
                            <a id="parResetFilters" href="{{ route('par.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-100 transition">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                             <button type="submit" class="bg-[#1a3a2d] text-white font-semibold px-5 py-2 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 flex items-center gap-2">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                            <button type="button" id="parPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                                <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                            </button>
                            <button type="button" id="parExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-100">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Container for AJAX Results --}}
            <div id="parResults">
                @include('inventory.par.partials.records-table', ['records' => $records])
            </div>
        </div>

        {{-- Side Column --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Inventory Snapshot</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex justify-between mb-1 text-sm">
                            <span class="font-medium text-gray-700">Total Inventory Value</span>
                            <span class="text-gray-800 font-bold">₱{{ number_format($stats['totalAmount'], 2) }}</span>
                        </div>
                    </div>
                     <div>
                        @php $completion = ($stats['total'] > 0) ? (($stats['total'] - $stats['unassigned']) / max($stats['total'], 1)) * 100 : 0; @endphp
                        <div class="flex justify-between mb-1 text-sm">
                            <span class="font-medium text-gray-700">Assignment Completion</span>
                            <span class="text-gray-500 font-semibold">{{ number_format($completion, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $completion }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Recent PAR Slips</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentPar as $recent)
                        <li class="flex items-center gap-4">
                             <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-purple-100 text-purple-600 font-bold">
                                <i class="fas fa-file-contract"></i>
                            </div>
                            <div class="flex-1 text-sm">
                                <p class="font-semibold text-gray-800">PAR {{ $recent->par_no }}</p>
                                <p class="text-xs text-gray-500">{{ optional(optional($recent->pqsRecord)->category)->cat_name ?? '—' }}</p>
                            </div>
                            <span class="text-xs font-semibold text-emerald-700">₱{{ number_format((float) $recent->amount, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 text-center py-4">No recent PAR records.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@include('inventory.par.partials._view-modal')
@endsection

@push('scripts')
{{-- The original script is preserved and adapted for PAR --}}
<script>
(() => {
    const endpoint = "{{ route('par.index') }}";
    const form = document.getElementById('parFiltersForm');
    if (!form) { return; }

    const searchInput = document.getElementById('search');
    const selects = Array.from(form.querySelectorAll('[data-auto-submit]'));
    const resultsContainer = document.getElementById('parResults');
    const recordCount = document.getElementById('par-record-count');
    const resetLink = document.getElementById('parResetFilters');

    let debounceTimer = null;
    let activeRequest = null;

    const setLoading = (isLoading) => {
        resultsContainer.classList.toggle('opacity-50', isLoading);
        resultsContainer.classList.toggle('pointer-events-none', isLoading);
    };

    const buildParams = (extra = {}) => {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        if (extra.page) { params.set('page', extra.page); }
        return params;
    };

    const fetchRecords = async (extra = {}) => {
        const params = buildParams(extra);
        const ajaxParams = new URLSearchParams(params);
        ajaxParams.set('ajax', '1');
        if (activeRequest) { activeRequest.abort(); }
        const controller = new AbortController();
        activeRequest = controller;
        setLoading(true);
        try {
            const response = await fetch(`${endpoint}?${ajaxParams.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) { throw new Error(`Unable to fetch PAR records (status ${response.status})`); }
            const payload = await response.json();
            resultsContainer.innerHTML = payload.html;
            if (typeof payload.total === 'number' && recordCount) { recordCount.textContent = payload.total.toLocaleString(); }
            const nextUrl = params.toString() ? `${endpoint}?${params.toString()}` : endpoint;
            window.history.pushState(null, '', nextUrl);
        } catch (error) {
            if (error.name !== 'AbortError') { console.error(error); }
        } finally {
            if (activeRequest === controller) { activeRequest = null; }
            setLoading(false);
        }
    };

    const debouncedFetch = () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchRecords(), 400);
    };

    searchInput?.addEventListener('input', debouncedFetch);
    selects.forEach((select) => { select.addEventListener('change', () => fetchRecords()); });
    form.addEventListener('submit', (event) => { event.preventDefault(); fetchRecords(); });

    resetLink?.addEventListener('click', (event) => {
        event.preventDefault();
        form.reset();
        fetchRecords();
    });

    resultsContainer.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('a') : null;
        if (!target || !target.href || !target.href.includes('page=')) { return; }
        event.preventDefault();
        const url = new URL(target.href);
        fetchRecords({ page: url.searchParams.get('page') });
    });
})();

(() => {
    const modal = document.getElementById('parViewModal');
    const resultsContainer = document.getElementById('parResults');
    if (!modal || !resultsContainer) { return; }

    const numberEl = document.getElementById('parViewNumber');
    const propertyNoEl = document.getElementById('parViewPropertyNo');
    const descriptionEl = document.getElementById('parViewDescription');
    const quantityEl = document.getElementById('parViewQuantity');
    const unitEl = document.getElementById('parViewUnit');
    const dateAcquiredEl = document.getElementById('parViewDateAcquired');
    const unitValueEl = document.getElementById('parViewUnitValue');
    const amountEl = document.getElementById('parViewAmount');
    const printButton = document.getElementById('parPrintButton');

    const articleEl = document.getElementById('parViewArticle');
    const propertyNoSnapshotEl = document.getElementById('parViewPropertyNoSnapshot');
    const dateSnapshotEl = document.getElementById('parViewDateSnapshot');
    const dateAcquiredChipEl = document.getElementById('parViewDateAcquiredChip');
    const officerEl = document.getElementById('parViewOfficer');
    const officerIdEl = document.getElementById('parViewOfficerId');
    const quantitySnapshotEl = document.getElementById('parViewQuantitySnapshot');
    const categoryEl = document.getElementById('parViewCategory');
    const serialsEl = document.getElementById('parViewSerials');
    const remarksEl = document.getElementById('parViewRemarks');

    const setText = (el, value, fallback = '—') => {
        if (!el) return;
        const rawValue = value ?? '';
        const stringValue = typeof rawValue === 'number' ? rawValue.toString() : String(rawValue);
        el.textContent = stringValue.trim() !== '' ? stringValue : fallback;
    };

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const formatCurrency = (value) => {
        if (value === null || value === undefined || Number.isNaN(Number(value))) return '—';
        return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const formatDate = (value) => {
        if (!value) return '—';
        const parsed = new Date(value);
        if (Number.isNaN(parsed.valueOf())) return String(value);
        return parsed.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    };

    const openModal = () => {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.remove('opacity-0'));
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        modal.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
        document.body.classList.remove('overflow-hidden');
    };

    modal.addEventListener('click', (event) => {
        if (event.target.closest('[data-close-modal]')) {
            closeModal();
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    resultsContainer.addEventListener('click', async (event) => {
        const trigger = event.target.closest('.js-par-view');
        if (!trigger) return;

        const url = trigger.dataset.showUrl;
        if (!url) return;

        try {
            const originalHtml = trigger.innerHTML;
            trigger.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            trigger.disabled = true;

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Failed to fetch');

            const { data } = await response.json();
            const pqsRecord = data.pqs_record || {};

            setText(numberEl, data.par_no);
            setText(propertyNoEl, pqsRecord.property_no);
            setText(propertyNoSnapshotEl, pqsRecord.property_no);
            setText(descriptionEl, data.article_desc || pqsRecord.description);
            setText(quantityEl, Number(data.quantity).toLocaleString());
            setText(unitEl, data.unit);
            setText(dateAcquiredEl, data.date_acquired);
            setText(unitValueEl, formatCurrency(data.unit_value));
            setText(amountEl, formatCurrency(data.amount));

            const dateFormatted = formatDate(pqsRecord.date_acquired);
            setText(dateSnapshotEl, dateFormatted);
            setText(dateAcquiredChipEl, dateFormatted);

            const articleValue = pqsRecord.article || pqsRecord.description || '—';
            setText(articleEl, articleValue);

            const categoryParts = [];
            if (pqsRecord.category_parent) categoryParts.push(pqsRecord.category_parent);
            if (pqsRecord.category) categoryParts.push(pqsRecord.category);
            const categoryText = categoryParts.length ? categoryParts.join(' › ') : '—';
            setText(categoryEl, categoryText);

            let quantityText = '—';
            if (data.quantity !== null && data.quantity !== undefined && data.quantity !== '') {
                const quantityNumber = Number(data.quantity);
                if (!Number.isNaN(quantityNumber)) {
                    const unitSuffix = data.unit ? ` ${String(data.unit).trim()}` : '';
                    quantityText = `${quantityNumber.toLocaleString('en-PH')}${unitSuffix}`;
                }
            }
            setText(quantitySnapshotEl, quantityText);

            const accountableOfficer = pqsRecord.accountable_officer;
            setText(officerEl, accountableOfficer?.name, 'Unassigned');
            setText(officerIdEl, accountableOfficer?.id);

            if (serialsEl) {
                if (Array.isArray(pqsRecord.serial_numbers) && pqsRecord.serial_numbers.length) {
                    const serialItems = pqsRecord.serial_numbers.map((serial, index) => `
                        <li class="flex items-center gap-3 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">#${index + 1}</span>
                            <span class="font-mono">${escapeHtml(serial)}</span>
                        </li>
                    `);
                    serialsEl.innerHTML = serialItems.join('');
                } else {
                    serialsEl.innerHTML = '<li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>';
                }
            }

            setText(remarksEl, pqsRecord.remarks, 'No additional remarks recorded.');

            if (printButton) {
                printButton.href = data.print_url;
            }

            openModal();
        } catch (error) {
            console.error(error);
            alert('Unable to load details.');
        } finally {
            trigger.innerHTML = originalHtml;
            trigger.disabled = false;
        }
    });
})();

// PDF and Excel Export Handlers
(function() {
    const pdfBtn = document.getElementById('parPrintPdfBtn');
    const excelBtn = document.getElementById('parExportExcelBtn');
    const form = document.getElementById('parFiltersForm');

    if (pdfBtn) {
        pdfBtn.addEventListener('click', function() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = '{{ route("par.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
            window.open(url, '_blank');
        });
    }

    if (excelBtn) {
        excelBtn.addEventListener('click', function() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = '{{ route("par.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        });
    }
})();
</script>
@endpush