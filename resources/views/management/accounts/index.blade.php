@extends('layouts.app')

@section('title', 'Manage Accounts')

@section('content')
<div class="space-y-8 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Manage Accounts</h2>
            <p class="mt-1 text-gray-500">Oversee user roles, assignments, and system access.</p>
        </div>
    </div>

    {{-- Enhanced Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Total Accounts', 'value' => $stats['total'], 'icon' => 'fa-users', 'color' => 'blue'],
                ['label' => 'Linked To Employees', 'value' => $stats['withEmployee'], 'icon' => 'fa-link', 'color' => 'emerald'],
                ['label' => 'Unassigned Accounts', 'value' => $stats['withoutEmployee'], 'icon' => 'fa-user-slash', 'color' => 'amber'],
                ['label' => 'Roles Covered', 'value' => $roleOptions->count(), 'icon' => 'fa-tags', 'color' => 'purple'],
            ];
            $colors = [
                'blue' => 'bg-blue-100 text-blue-600',
                'emerald' => 'bg-emerald-100 text-emerald-600',
                'amber' => 'bg-amber-100 text-amber-600',
                'purple' => 'bg-purple-100 text-purple-600',
            ];
        @endphp
        @foreach ($statCards as $card)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex items-center gap-5 transition hover:shadow-xl hover:-translate-y-1">
            <div class="flex-shrink-0 w-14 h-14 rounded-full flex items-center justify-center {{ $colors[$card['color']] }}">
                <i class="fa-solid {{ $card['icon'] }} text-2xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content Column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Enhanced Filters & Actions --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4">
                <form method="GET" class="space-y-4">
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <div class="relative w-full flex-1">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search by username, employee name, or ID..."
                                   class="w-full rounded-xl border-gray-200 pl-11 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" />
                        </div>
                        <div class="flex items-center gap-2 w-full md:w-auto">
                             <select id="role" name="role" data-auto-submit class="w-full rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <option value="all" @selected($roleFilter === 'all' || !$roleFilter)>All Roles</option>
                                @foreach ($roleOptions as $role)
                                    <option value="{{ $role }}" @selected($roleFilter === $role)>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Export Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-3">
                    <span id="accountsTotalCount" class="text-sm text-gray-500">{{ number_format($accounts->total()) }} records found</span>
                         <div class="flex items-center gap-2">
                            <button type="button" id="accountsPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                                <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                            </button>
                            <button type="button" id="accountsExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-100">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Accounts Table (AJAX-updatable partial) --}}
            <div id="accountsTableWrapper">
                @include('management.accounts._table')
            </div>
        </div>

        {{-- Side Column --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Role Distribution</h3>
                <div class="mt-4 space-y-4">
                    @forelse ($stats['roles'] as $role => $count)
                        @php
                            $percentage = ($stats['total'] > 0) ? ($count / $stats['total']) * 100 : 0;
                            $roleColorClass = match(strtolower($role)) {
                                'custodian' => 'bg-blue-500',
                                'iac' => 'bg-purple-500',
                                'employee' => 'bg-emerald-500',
                                'division_head' => 'bg-indigo-500',
                                default => 'bg-gray-500',
                            };
                        @endphp
                        <div>
                            <div class="flex justify-between mb-1 text-sm">
                                <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                <span class="text-gray-500">{{ number_format($count) }} Users</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $roleColorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No role data available.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Recently Added</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentAccounts as $recent)
                        @php
                            $recentEmployee = $recent->employee;
                            $recentName = $recentEmployee ? trim(($recentEmployee->first_name ?? '') . ' ' . ($recentEmployee->last_name ?? '')) : 'Unassigned';
                            $initial = strtoupper(substr($recent->username, 0, 1));
                             $roleColor = match(strtolower($recent->role)) {
                                'custodian' => 'bg-blue-100 text-blue-700',
                                'iac' => 'bg-purple-100 text-purple-700',
                                'employee' => 'bg-emerald-100 text-emerald-700',
                                'division_head' => 'bg-indigo-100 text-indigo-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <li class="flex items-center gap-4">
                             <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center {{ $roleColor }} font-bold">
                                {{ $initial }}
                            </div>
                            <div class="flex-1 text-sm">
                                <p class="font-semibold text-gray-800">{{ $recent->username }}</p>
                                <p class="text-xs text-gray-500">{{ $recentName }}</p>
                            </div>
                            <span class="text-xs font-semibold uppercase text-gray-500" title="{{ optional($recent->created_at)->format('M d, Y H:i') }}">{{ optional($recent->created_at)->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 py-4 text-center">No recent accounts to show.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-auto-submit]').forEach(function (element) {
            element.addEventListener('change', function () {
                element.form.submit();
            });
        });
        // AJAX-powered search & pagination for accounts table
        (function () {
            const wrapper = document.getElementById('accountsTableWrapper');
            const form = document.querySelector('form');
            const searchInput = document.getElementById('search');
            const roleSelect = document.getElementById('role');

            const fetchTable = async (url) => {
                try {
                    const res = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) throw new Error('Network response was not ok');
                    const data = await res.json();
                    // replace the wrapper's innerHTML with returned partial
                    wrapper.innerHTML = data.html;
                    // update the records count in the filter area if present
                    const totalSpan = document.getElementById('accountsTotalCount');
                    if (totalSpan && typeof data.total !== 'undefined') {
                        totalSpan.textContent = `${Number(data.total).toLocaleString()} records found`;
                    }
                    // rebind pagination links
                    bindPaginationLinks();
                } catch (err) {
                    console.error('Failed to fetch accounts table:', err);
                }
            };

            const buildUrlFromForm = () => {
                const params = new URLSearchParams(new FormData(form));
                return `${window.location.pathname}?${params.toString()}`;
            };

            let debounceTimer = null;
            const scheduleFetch = (delay = 350) => {
                if (debounceTimer) clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetchTable(buildUrlFromForm()), delay);
            };

            // Intercept form submit to use AJAX
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                fetchTable(buildUrlFromForm());
            });

            // role select already had data-auto-submit; use AJAX instead
            if (roleSelect) {
                roleSelect.addEventListener('change', function () {
                    scheduleFetch(0);
                });
            }

            // search input: debounced AJAX fetch, submit on Enter immediately
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    scheduleFetch(350);
                });
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (debounceTimer) clearTimeout(debounceTimer);
                        fetchTable(buildUrlFromForm());
                    }
                });
            }

            const bindPaginationLinks = () => {
                const container = document.getElementById('accountsTableContainer');
                if (!container) return;
                container.querySelectorAll('a').forEach(a => {
                    // only bind pagination links (they usually include "page=" in the href)
                    if (!a.href || !a.href.includes('page=')) return;
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        fetchTable(a.href);
                        // update browser URL for back/forward support
                        window.history.pushState({}, '', a.href);
                    });
                });
            };

            // handle back/forward navigation
            window.addEventListener('popstate', function () {
                fetchTable(window.location.href);
            });

            // initial bind for pagination that may exist on first load
            bindPaginationLinks();
        })();

        // PDF and Excel Export Handlers
        (function() {
            const pdfBtn = document.getElementById('accountsPrintPdfBtn');
            const excelBtn = document.getElementById('accountsExportExcelBtn');
            const form = document.querySelector('form');

            if (pdfBtn) {
                pdfBtn.addEventListener('click', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = '{{ route("accounts.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
                    window.open(url, '_blank');
                });
            }

            if (excelBtn) {
                excelBtn.addEventListener('click', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = '{{ route("accounts.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
                    window.location.href = url;
                });
            }
        })();
    });
</script>
@endpush