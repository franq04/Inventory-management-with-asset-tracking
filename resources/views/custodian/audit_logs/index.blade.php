@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-8 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Audit Trail</h2>
            <p class="mt-1 text-gray-500">Monitor significant actions performed across the system.</p>
        </div>
    </div>

    {{-- Enhanced Filter Card --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg p-4">
        <form id="filterForm" method="GET" action="{{ route('custodian.audit_logs.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                {{-- Search (AJAX on input) --}}
                <div class="relative">
                    <label for="search" class="text-xs font-semibold text-gray-500">Search</label>
                    <i class="fas fa-search absolute left-3 top-1/2 mt-2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search by user, action, or description..." class="w-full mt-1 rounded-xl border-gray-200 pl-10 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" />
                </div>
                
                {{-- Action Type Dropdown --}}
                <div>
                    <label for="action" class="text-xs font-semibold text-gray-500">Action Type</label>
                    <select name="action" id="action" class="w-full mt-1 rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                        <option value="">All Actions</option>
                        @foreach($availableActions ?? [] as $act)
                            @php
                                // Display friendly label by replacing underscores with space and capitalizing
                                $label = ucwords(str_replace('_', ' ', strtolower($act)));
                            @endphp
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Date Range (Manual Apply) --}}
                <div>
                    <label class="text-xs font-semibold text-gray-500">Filter by Date Range</label>
                    <div class="flex gap-2 mt-1">
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="flex-1 rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="flex-1 rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                        <button type="button" id="applyDatesBtn" class="bg-[#1a3a2d] text-white font-semibold px-4 py-2 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                    </div>
                </div>
            </div>
            
            {{-- Action Buttons & Export --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                 <span class="text-sm text-gray-500" id="logCount">{{ number_format($logs->total()) }} logs found</span>
                 <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('custodian.audit_logs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <button type="button" id="auditLogsPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                        <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                    </button>
                    <button type="button" id="auditLogsExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-100">
                        <i class="fas fa-file-excel text-green-600"></i> Export Excel
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Container for AJAX content --}}
    <div id="auditLogContent">
        @include('custodian.audit_logs._table', ['logs' => $logs])
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Use an Immediately Invoked Function Expression (IIFE) to avoid polluting the global scope
    (function($) {
        // Cache jQuery objects for performance
        const $contentWrapper = $('#auditLogContent');
        const $filterForm = $('#filterForm');
        const $searchInput = $('#search');
        const $actionSelect = $('#action');
        const $applyDatesBtn = $('#applyDatesBtn');
        let debounceTimer;

        // The core function to fetch and update the log table
        function fetchLogs(url) {
            $contentWrapper.css('opacity', 0.5); // Visual feedback for loading
            
            $.ajax({
                url: url,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    if (data.html) {
                        $contentWrapper.html(data.html);
                    }
                    // Update the total count display
                    const logCountSpan = $('#logCount');
                    if (logCountSpan.length && typeof data.total !== 'undefined') {
                        logCountSpan.text(`${Number(data.total).toLocaleString()} logs found`);
                    }
                },
                error: function() {
                    console.error('Failed to fetch audit logs.');
                    // You can add a user-facing error message here
                },
                complete: function() {
                    $contentWrapper.css('opacity', 1);
                }
            });
        }

        // Build the URL from the current form state
        function buildUrl() {
            const params = new URLSearchParams(new FormData($filterForm[0]));
            params.delete('page'); // Always reset to page 1 on new filter
            const queryString = params.toString();
            return `${$filterForm.attr('action')}?${queryString}`;
        }

        // Live search on input with debounce to prevent excessive requests
        $searchInput.on('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const url = buildUrl();
                fetchLogs(url);
                window.history.pushState({ path: url }, '', url);
            }, 350); // 350ms delay after user stops typing
        });

        // Action type filter - instant AJAX on change
        $actionSelect.on('change', function() {
            const url = buildUrl();
            fetchLogs(url);
            window.history.pushState({ path: url }, '', url);
        });

        // Manual "Apply" button for date filters
        $applyDatesBtn.on('click', function() {
            const url = buildUrl();
            fetchLogs(url);
            window.history.pushState({ path: url }, '', url);
        });
        
        // Event delegation for handling clicks on pagination links
        $contentWrapper.on('click', '.pagination a', function(event) {
            event.preventDefault();
            const url = $(this).attr('href');
            fetchLogs(url);
            window.history.pushState({ path: url }, '', url);
        });

        // Handle browser back/forward navigation
        $(window).on('popstate', function() {
            fetchLogs(window.location.href);
        });

    })(jQuery);

    // PDF and Excel Export Handlers
    (function() {
        const pdfBtn = document.getElementById('auditLogsPrintPdfBtn');
        const excelBtn = document.getElementById('auditLogsExportExcelBtn');
        const form = document.getElementById('filterForm');

        if (pdfBtn) {
            pdfBtn.addEventListener('click', function() {
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                const url = '{{ route("custodian.audit_logs.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
                window.open(url, '_blank');
            });
        }

        if (excelBtn) {
            excelBtn.addEventListener('click', function() {
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                const url = '{{ route("custodian.audit_logs.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
                window.location.href = url;
            });
        }
    })();
</script>
@endpush