@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6 animate-card">
    @php
        $selectedActionLabel = request('action')
            ? ucwords(str_replace('_', ' ', strtolower((string) request('action'))))
            : 'All Actions';
        $dateFrom = request('date_from') ?: 'Any';
        $dateTo = request('date_to') ?: 'Any';
    @endphp

    <div class="animate-card rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10 overflow-hidden relative">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.18),_transparent_60%)]"></div>
        <div class="absolute -right-8 bottom-0 h-32 w-32 rounded-full border border-white/10 bg-white/5 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Custodian Monitoring
                </div>
                <div class="space-y-1">
                    <h2 class="text-3xl font-extrabold tracking-tight text-white">Audit Trail</h2>
                    <p class="text-sm text-white/75 md:max-w-2xl">Track critical events, access actions, and data changes in one operational timeline.</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[420px] lg:max-w-2xl">
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Total Queue</p>
                    <p id="auditTotalKpi" class="mt-2 text-2xl font-bold">{{ number_format($logs->total()) }}</p>
                    <p class="mt-1 text-xs text-white/65">All audit records</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Visible Rows</p>
                    <p id="auditVisibleKpi" class="mt-2 text-2xl font-bold">{{ number_format($logs->count()) }}</p>
                    <p class="mt-1 text-xs text-white/65">Records on this page</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Active Status</p>
                    <p id="auditActionKpi" class="mt-2 text-xl font-bold">{{ $selectedActionLabel }}</p>
                    <p class="mt-1 text-xs text-white/65">Current filter segment</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-[0_14px_40px_-28px_rgba(15,60,45,0.45)]">
                <form id="filterForm" method="GET" action="{{ route('custodian.audit_logs.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                        <div class="relative lg:col-span-5">
                            <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Search</label>
                            <i class="fas fa-search absolute left-3 top-[38px] text-gray-400"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search user, action, or description..." class="mt-1 h-11 w-full rounded-xl border-gray-200 pl-10 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20" />
                        </div>

                        <div class="lg:col-span-3">
                            <label for="action" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Action Type</label>
                            <select name="action" id="action" class="mt-1 h-11 w-full rounded-xl border-gray-200 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20">
                                <option value="">All Actions</option>
                                @foreach($availableActions ?? [] as $act)
                                    @php
                                        $label = ucwords(str_replace('_', ' ', strtolower($act)));
                                    @endphp
                                    <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <label class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Date Range</label>
                            <div class="mt-1 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="h-11 rounded-xl border-gray-200 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20">
                                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="h-11 rounded-xl border-gray-200 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20">
                                <button type="button" id="applyDatesBtn" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#1a3a2d] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#214f3d]">
                                    <i class="fas fa-filter"></i>
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm text-gray-500" id="logCount">{{ number_format($logs->total()) }} logs found</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('custodian.audit_logs.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                <i class="fas fa-undo"></i>
                                Reset
                            </a>
                            <button type="button" id="auditLogsPrintPdfBtn" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                <i class="fas fa-file-pdf text-rose-600"></i>
                                Print PDF
                            </button>
                            <button type="button" id="auditLogsExportExcelBtn" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                <i class="fas fa-file-excel text-emerald-600"></i>
                                Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#fbfcfb] px-5 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-[#1a3a2d]">Audit Activity Stream</h3>
                        <p class="text-sm text-gray-500">System events listed by timestamp, actor, role, action, and target entity.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700" id="auditPanelCountBadge">
                        <i class="fa-solid fa-list-check"></i>
                        {{ number_format($logs->total()) }} record(s)
                    </span>
                </div>

                <div id="auditLogSkeleton" class="hidden animate-pulse space-y-3 bg-white p-4">
                    <div class="h-9 rounded-lg bg-emerald-50"></div>
                    <div class="h-12 rounded-lg bg-gray-100"></div>
                    <div class="h-12 rounded-lg bg-gray-100"></div>
                    <div class="h-12 rounded-lg bg-gray-100"></div>
                    <div class="h-12 rounded-lg bg-gray-100"></div>
                </div>

                <div id="auditLogContent">
                    @include('custodian.audit_logs._table', ['logs' => $logs])
                </div>
            </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-[0_12px_35px_-22px_rgba(15,60,45,0.45)]">
                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-[#1a3a2d]">Action Distribution</h3>
                <div class="mt-3 space-y-2 text-sm text-gray-700">
                    @php
                        $pageActionCounts = $logs->getCollection()->groupBy('action')->map->count()->sortDesc();
                    @endphp
                    @forelse ($pageActionCounts->take(5) as $action => $count)
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2">
                            <span class="font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', strtolower((string) $action))) }}</span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No action data on this page.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-[0_12px_35px_-22px_rgba(15,60,45,0.45)]">
                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-[#1a3a2d]">Recent Actors</h3>
                <div class="mt-3 space-y-2 text-sm">
                    @php
                        $recentActors = $logs->getCollection()->pluck('account.username')->filter()->unique()->take(6);
                    @endphp
                    @forelse ($recentActors as $actor)
                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-gray-700">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ strtoupper(substr((string) $actor, 0, 1)) }}</span>
                            <span class="truncate font-medium">{{ $actor }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No user activity on this page.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Use an Immediately Invoked Function Expression (IIFE) to avoid polluting the global scope
    (function($) {
        // Cache jQuery objects for performance
        const $contentWrapper = $('#auditLogContent');
        const $skeleton = $('#auditLogSkeleton');
        const $filterForm = $('#filterForm');
        const $searchInput = $('#search');
        const $actionSelect = $('#action');
        const $dateFromInput = $('#date_from');
        const $dateToInput = $('#date_to');
        const $applyDatesBtn = $('#applyDatesBtn');
        const $auditTotalKpi = $('#auditTotalKpi');
        const $auditVisibleKpi = $('#auditVisibleKpi');
        const $auditActionKpi = $('#auditActionKpi');
        const $auditPanelCountBadge = $('#auditPanelCountBadge');
        let debounceTimer;

        function animateRows() {
            const rows = $contentWrapper.find('[data-audit-row]');
            rows.each(function(index, row) {
                row.style.opacity = '0';
                row.style.transform = 'translateY(4px)';
                setTimeout(() => {
                    row.style.transition = 'opacity 180ms ease, transform 180ms ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 28);
            });
        }

        function syncKpis(total) {
            const visibleRows = $contentWrapper.find('[data-audit-row]').length;

            if ($auditTotalKpi.length && typeof total !== 'undefined') {
                $auditTotalKpi.text(Number(total).toLocaleString());
            }

            if ($auditVisibleKpi.length) {
                $auditVisibleKpi.text(Number(visibleRows).toLocaleString());
            }

            if ($auditActionKpi.length) {
                const actionLabel = $actionSelect.find(':selected').text() || 'All Actions';
                $auditActionKpi.text(actionLabel);
            }

            if ($auditPanelCountBadge.length && typeof total !== 'undefined') {
                $auditPanelCountBadge.html(`<i class="fa-solid fa-list-check"></i> ${Number(total).toLocaleString()} record(s)`);
            }
        }

        // The core function to fetch and update the log table
        function fetchLogs(url) {
            $contentWrapper.addClass('hidden');
            $skeleton.removeClass('hidden');
            
            $.ajax({
                url: url,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    if (data.html) {
                        $contentWrapper.html(data.html);
                        animateRows();
                    }
                    // Update the total count display
                    const logCountSpan = $('#logCount');
                    if (logCountSpan.length && typeof data.total !== 'undefined') {
                        logCountSpan.text(`${Number(data.total).toLocaleString()} logs found`);
                    }
                    syncKpis(data.total);
                },
                error: function() {
                    console.error('Failed to fetch audit logs.');
                    // You can add a user-facing error message here
                },
                complete: function() {
                    $skeleton.addClass('hidden');
                    $contentWrapper.removeClass('hidden');
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

        if (!window.__pqsAuditLogsAutoRefreshBound) {
            const handleAuditLogsAutoRefresh = (event) => {
                if (!$filterForm.length || !$contentWrapper.length) {
                    return;
                }

                event.preventDefault();
                fetchLogs(window.location.href);
            };

            document.addEventListener('pqs:auto-refresh', handleAuditLogsAutoRefresh);
            document.addEventListener('turbo:before-cache', () => {
                document.removeEventListener('pqs:auto-refresh', handleAuditLogsAutoRefresh);
                window.__pqsAuditLogsAutoRefreshBound = false;
            }, { once: true });

            window.__pqsAuditLogsAutoRefreshBound = true;
        }

        syncKpis({{ (int) $logs->total() }});
        animateRows();

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