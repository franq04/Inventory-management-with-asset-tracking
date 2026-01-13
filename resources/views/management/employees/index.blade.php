@extends('layouts.app')

@section('title', 'Manage Employees')

@section('content')
<div class="space-y-8 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Manage Employees</h2>
            <p class="mt-1 text-gray-500">Oversee employee records, assignments, and system access.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center gap-2">
            <i class="fas fa-user-plus"></i>
            Add New Employee
        </a>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- Enhanced Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Total Employees', 'value' => $stats['total'], 'icon' => 'fa-users', 'color' => 'blue'],
                ['label' => 'With Accounts', 'value' => $stats['withAccount'], 'icon' => 'fa-user-check', 'color' => 'emerald'],
                ['label' => 'Without Accounts', 'value' => $stats['withoutAccount'], 'icon' => 'fa-user-times', 'color' => 'amber'],
                ['label' => 'Coverage Ratio', 'value' => ($stats['total'] > 0) ? number_format(($stats['withAccount'] / max($stats['total'], 1)) * 100, 1) . '%' : '0%', 'icon' => 'fa-percentage', 'color' => 'purple'],
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
                <form method="GET" class="space-y-4">
                    <div class="relative w-full">
                        <label for="search" class="text-xs font-semibold text-gray-500">Search</label>
                        <i class="fas fa-search absolute left-4 top-1/2 mt-2 -translate-y-1/2 text-gray-400"></i>
                        <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search by ID, name, email, or contact..."
                               class="w-full mt-1 rounded-xl border-gray-200 pl-11 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" data-auto-submit />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                             <label for="division" class="text-xs font-semibold text-gray-500">Division</label>
                            <select id="division" name="division" data-auto-submit class="mt-1 w-full rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <option value="" @selected(!$divisionFilter)>All Divisions</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->division_id }}" @selected($divisionFilter == $division->division_id)>
                                        {{ $division->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                         <div>
                            <label for="section" class="text-xs font-semibold text-gray-500">Section</label>
                            <select id="section" name="section" data-auto-submit class="mt-1 w-full rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <option value="" @selected(!$sectionFilter)>All Sections</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->section_id }}" @selected($sectionFilter == $section->section_id)>
                                        {{ $section->section_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                     <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                        <div class="w-full sm:w-56">
                            <label for="assignment" class="text-xs font-semibold text-gray-500">Account Assignment</label>
                            <select id="assignment" name="assignment" data-auto-submit class="mt-1 w-full rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <option value="" @selected(!$assignmentFilter)>Any</option>
                                <option value="with" @selected($assignmentFilter === 'with')>With account</option>
                                <option value="without" @selected($assignmentFilter === 'without')>Without account</option>
                            </select>
                        </div>
                         <div class="flex items-center gap-2">
                            <button type="button" id="employeesPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                                <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                            </button>
                            <button type="button" id="employeesExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-100">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Employees Table --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                     <h3 class="text-lg font-semibold text-gray-800">Employee Registry</h3>
                     <p class="text-sm text-gray-500" id="employeeCount">{{ number_format($employees->total()) }} records found</p>
                </div>
                <div class="overflow-x-auto" id="employeeTableContainer">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Contact</th>
                                <th class="px-6 py-3">Section / Division</th>
                                <th class="px-6 py-3 text-right">Account</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $employee->employee_id }}</div>
                                        @if ($employee->position)
                                            <div class="text-xs text-emerald-600 font-semibold mt-1">{{ $employee->position->position_title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>{{ $employee->email ?: '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->contact_no ?: '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ optional($employee->section)->section_name ?: 'Unassigned' }}</div>
                                        <div class="text-xs text-gray-500">{{ optional(optional($employee->section)->division)->division_name ?: '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($employee->account)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold uppercase tracking-wide">
                                                {{ $employee->account->username }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold uppercase tracking-wide">
                                                None
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border bg-white text-gray-600 hover:bg-gray-50 hover:text-[#1a3a2d] transition" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee record? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border bg-white text-red-500 hover:bg-red-50 hover:text-red-700 transition" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                                        <i class="fas fa-users-slash text-4xl text-gray-300"></i>
                                        <p class="mt-3 font-medium">No employees matched your filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-gray-100" id="employeePagination">
                    {{ $employees->onEachSide(1)->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Side Column --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Divisions Overview</h3>
                <div class="mt-4 space-y-4">
                    @php $maxEmployees = max(array_values($divisionEmployeeCounts->toArray())) ?: 1; @endphp
                    @foreach ($divisions as $division)
                        @php
                            $divisionCount = $divisionEmployeeCounts[$division->division_id] ?? 0;
                            $percentage = ($maxEmployees > 0) ? ($divisionCount / $maxEmployees) * 100 : 0;
                        @endphp
                        <div>
                             <div class="flex justify-between mb-1 text-sm">
                                <span class="font-medium text-gray-700">{{ $division->division_name }}</span>
                                <span class="text-gray-500 font-semibold">{{ number_format($divisionCount) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Recently Added</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentEmployees as $recent)
                        @php
                            $recentName = trim(($recent->first_name ?? '') . ' ' . ($recent->last_name ?? ''));
                            $initial = strtoupper(substr($recent->first_name, 0, 1) . substr($recent->last_name, 0, 1));
                        @endphp
                        <li class="flex items-center gap-4">
                             <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center bg-blue-100 text-blue-600 font-bold">
                                {{ $initial }}
                            </div>
                            <div class="flex-1 text-sm">
                                <p class="font-semibold text-gray-800">{{ $recentName }}</p>
                                <p class="text-xs text-gray-500">{{ $recent->employee_id }}</p>
                            </div>
                            <span class="text-xs font-semibold uppercase text-gray-400">{{ $recent->created_at ? $recent->created_at->diffForHumans() : 'N/A' }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 py-4 text-center">No recent employee records.</li>
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
        const tableWrapper = document.getElementById('employeeTableContainer');
        const totalSpan = document.getElementById('employeeCount');
        const paginationDiv = document.getElementById('employeePagination');

        document.querySelectorAll('[data-auto-submit]').forEach(function (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, function () {
                const form = element.closest('form');
                if (!form) return;
                const params = new URLSearchParams(new FormData(form));
                const url = `${form.action || window.location.pathname}?${params.toString()}`;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        if (tableWrapper && data.html) {
                            tableWrapper.innerHTML = data.html;
                        }
                        if (totalSpan && typeof data.total !== 'undefined') {
                            totalSpan.textContent = `${Number(data.total).toLocaleString()} records found`;
                        }
                        if (paginationDiv && data.pagination) {
                            paginationDiv.innerHTML = data.pagination;
                            bindPaginationLinks();
                        }
                    });
            });
        });

        function bindPaginationLinks() {
            if (!paginationDiv) return;
            paginationDiv.querySelectorAll('a').forEach(link => {
                if (!link.href || !link.href.includes('page=')) return;
                if (link.dataset.ajaxBound === 'true') return;
                link.dataset.ajaxBound = 'true';
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => res.json())
                        .then(data => {
                            if (tableWrapper && data.html) {
                                tableWrapper.innerHTML = data.html;
                            }
                            if (totalSpan && typeof data.total !== 'undefined') {
                                totalSpan.textContent = `${Number(data.total).toLocaleString()} records found`;
                            }
                            if (paginationDiv && data.pagination) {
                                paginationDiv.innerHTML = data.pagination;
                                bindPaginationLinks();
                            }
                        });
                });
            });
        }

        bindPaginationLinks();

        // PDF and Excel Export Handlers
        (function() {
            const pdfBtn = document.getElementById('employeesPrintPdfBtn');
            const excelBtn = document.getElementById('employeesExportExcelBtn');
            const form = document.querySelector('form');

            if (pdfBtn) {
                pdfBtn.addEventListener('click', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = '{{ route("employees.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
                    window.open(url, '_blank');
                });
            }

            if (excelBtn) {
                excelBtn.addEventListener('click', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = '{{ route("employees.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
                    window.location.href = url;
                });
            }
        })();
    });
</script>
@endpush