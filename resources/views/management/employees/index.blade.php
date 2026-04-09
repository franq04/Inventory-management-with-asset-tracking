@extends('layouts.app')

@section('title', 'Manage Employees')

@section('content')
<div class="space-y-8 animate-card" id="employeesPage">
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Workforce Registry
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Manage Employees</h2>
                <p class="text-sm text-white/75">Oversee employee records, assignments, and system access.</p>
            </div>

            <div id="employeesKpiCards" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4 lg:min-w-[760px] 2xl:min-w-[840px]">
        @php
            $statCards = [
                ['label' => 'Total Employees', 'value' => $stats['total'], 'icon' => 'fa-users', 'color' => 'blue'],
                ['label' => 'With Accounts', 'value' => $stats['withAccount'], 'icon' => 'fa-user-check', 'color' => 'emerald'],
                ['label' => 'Without Accounts', 'value' => $stats['withoutAccount'], 'icon' => 'fa-user-times', 'color' => 'amber'],
                ['label' => 'Coverage Ratio', 'value' => ($stats['total'] > 0) ? number_format(($stats['withAccount'] / max($stats['total'], 1)) * 100, 1) . '%' : '0%', 'icon' => 'fa-percentage', 'color' => 'teal'],
            ];
            $colors = [
                'blue' => 'bg-blue-300/20 text-blue-100',
                'emerald' => 'bg-emerald-300/20 text-emerald-100',
                'amber' => 'bg-amber-300/20 text-amber-100',
                'teal' => 'bg-teal-300/20 text-teal-100',
            ];
        @endphp
        @foreach ($statCards as $card)
        <div class="min-h-[112px] rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
            <div class="flex items-center gap-2.5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $colors[$card['color']] }}">
                    <i class="fa-solid {{ $card['icon'] }} text-sm"></i>
                </div>
                <p class="min-h-[1.8rem] text-[10px] font-semibold uppercase leading-tight tracking-[0.16em] text-white/70">{{ $card['label'] }}</p>
            </div>
            <p class="mt-2 text-[1.75rem] font-bold leading-none tabular-nums">{{ $card['value'] }}</p>
        </div>
        @endforeach
            </div>
        </div>
    </div>

        @if (session('status'))
            <div id="employeesFlashStatus" class="hidden" data-message="{{ session('status') }}"></div>
        @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content Column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Enhanced Filters & Actions --}}
            <div class="rounded-[26px] border border-emerald-950/8 bg-white/95 p-5 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
                <form method="GET" class="space-y-4" id="employeesFiltersForm" action="{{ route('employees.index') }}">
                    <div class="w-full">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Search</label>
                        <div class="relative mt-1">
                            <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                            <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search by ID, name, email, or contact..."
                                   class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-3 text-sm shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:ring-4 focus:ring-[#1a3a2d]/10" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                             <label for="division" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Division</label>
                            <select id="division" name="division" class="mt-1 w-full rounded-2xl border border-emerald-950/10 bg-white px-3 py-[9px] text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50">
                                <option value="" @selected(!$divisionFilter)>All Divisions</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->division_id }}" @selected($divisionFilter == $division->division_id)>
                                        {{ $division->division_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                         <div>
                            <label for="section" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Section</label>
                            <select id="section" name="section" class="mt-1 w-full rounded-2xl border border-emerald-950/10 bg-white px-3 py-[9px] text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50">
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
                            <label for="assignment" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Account Assignment</label>
                            <select id="assignment" name="assignment" class="mt-1 w-full rounded-2xl border border-emerald-950/10 bg-white px-3 py-[9px] text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50">
                                <option value="" @selected(!$assignmentFilter)>Any</option>
                                <option value="with" @selected($assignmentFilter === 'with')>With account</option>
                                <option value="without" @selected($assignmentFilter === 'without')>Without account</option>
                            </select>
                        </div>
                         <div class="flex items-center gap-2">
                            <a id="employeesResetFilters" href="{{ route('employees.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                            <button type="button" id="employeesPrintPdfBtn" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                <i class="fas fa-file-pdf text-rose-600"></i> Print PDF
                            </button>
                            <button type="button" id="employeesExportExcelBtn" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Employees Table --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Employee Registry</h3>
                        <p class="text-sm text-gray-500" id="employeeCount">{{ number_format($employees->total()) }} records found</p>
                    </div>
                    <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg">
                        <i class="fas fa-user-plus"></i>
                        Add New Employee
                    </a>
                </div>
                <div class="overflow-x-auto" id="employeeTableContainer">
                    @include('management.employees._table', ['employees' => $employees])
                </div>

                @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-gray-100" id="employeePagination">
                    {{ $employees->onEachSide(1)->links('vendor.pagination.procurement') }}
                </div>
                @endif
            </div>
        </div>

        {{-- Side Column --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-800">Divisions Overview</h3>
                    <button type="button" id="openCreateDivisionModal" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        <i class="fas fa-plus"></i>
                        Add Division
                    </button>
                </div>
                <div class="mt-4 space-y-4">
                    @php
                        $maxEmployees = (int) collect($divisionEmployeeCounts)->max();
                        $maxEmployees = $maxEmployees > 0 ? $maxEmployees : 1;
                    @endphp
                    @foreach ($divisions as $division)
                        @php
                            $divisionCount = $divisionEmployeeCounts[$division->division_id] ?? 0;
                            $percentage = ($maxEmployees > 0) ? ($divisionCount / $maxEmployees) * 100 : 0;
                            $sectionsPayload = $division->sections->map(function ($section) use ($sectionEmployeeCounts) {
                                return [
                                    'id' => (int) $section->section_id,
                                    'name' => $section->section_name,
                                    'employees' => (int) ($sectionEmployeeCounts[$section->section_id] ?? 0),
                                    'positions' => collect($sectionPositionTitles[$section->section_id] ?? [])->values()->all(),
                                ];
                            })->values();
                        @endphp
                        <div class="rounded-xl border border-gray-100 p-3">
                             <div class="flex justify-between mb-1 text-sm">
                                <span class="font-medium text-gray-700">{{ $division->division_name }}</span>
                                <span class="text-gray-500 font-semibold">{{ number_format($divisionCount) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="mt-2 flex justify-end">
                                <button
                                    type="button"
                                    class="js-division-overview-details inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                    data-division-id="{{ $division->division_id }}"
                                    data-division-name="{{ $division->division_name }}"
                                    data-division-total="{{ (int) $divisionCount }}"
                                    data-division-sections='@json($sectionsPayload)'>
                                    <i class="fas fa-circle-info text-[11px]"></i>
                                    See details
                                </button>
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

<div id="createDivisionModal" class="fixed inset-0 z-[130] hidden opacity-0 transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="createDivisionTitle">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-create-division-modal></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-xl overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-5 py-4 text-white">
                <div>
                    <h3 id="createDivisionTitle" class="text-lg font-bold tracking-tight">Add Division</h3>
                    <p class="text-xs text-white/80">Create a division and define initial sections.</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-create-division-modal>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="createDivisionForm" class="space-y-4 p-5">
                <div>
                    <label for="createDivisionName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Division Name</label>
                    <input id="createDivisionName" name="division_name" type="text" maxlength="255" class="mt-1 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" required />
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="createDivisionCode" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Division Code (Optional)</label>
                        <input id="createDivisionCode" name="division_code" type="text" maxlength="50" class="mt-1 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" />
                    </div>
                    <div>
                        <label for="createDivisionDescription" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Description (Optional)</label>
                        <input id="createDivisionDescription" name="description" type="text" class="mt-1 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" />
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Initial Sections</label>
                        <button type="button" id="addInitialSectionRow" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                            <i class="fas fa-plus"></i>
                            Add Section
                        </button>
                    </div>

                    <div id="initialSectionsRows" class="mt-2 space-y-2">
                        <div class="flex items-center gap-2" data-section-row>
                            <input type="text" name="sections[]" maxlength="255" class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="e.g. Procurement Section" required />
                            <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100" data-remove-section-row>
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <p id="createDivisionError" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700"></p>

                <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                    <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100" data-close-create-division-modal>
                        Cancel
                    </button>
                    <button type="submit" id="createDivisionSubmitBtn" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#204835]">
                        <i class="fas fa-save"></i>
                        Save Division
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="divisionOverviewDetailsModal" class="fixed inset-0 z-[125] hidden opacity-0 transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="divisionOverviewDetailsTitle">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-division-overview-modal></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-5 py-4 text-white">
                <div>
                    <h3 id="divisionOverviewDetailsTitle" class="text-lg font-bold tracking-tight">Division Details</h3>
                    <p id="divisionOverviewDetailsMeta" class="text-xs text-white/80"></p>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-division-overview-modal>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4 p-5">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700/80">Total Employees</p>
                            <p id="divisionOverviewDetailsTotal" class="mt-1 text-xl font-bold text-[#1a3a2d]">0</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700/80">Total Sections</p>
                            <p id="divisionOverviewDetailsSectionCount" class="mt-1 text-xl font-bold text-[#1a3a2d]">0</p>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Sections</p>
                    <ul id="divisionOverviewDetailsSections" class="mt-2 space-y-2"></ul>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-4">
                <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100" data-close-division-overview-modal>
                    Close
                </button>
                <a id="divisionOverviewDetailsAddBtn" href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#204835]">
                    <i class="fas fa-user-plus"></i>
                    Add Employee
                </a>
            </div>
        </div>
    </div>
</div>

<div id="sectionPositionModal" class="fixed inset-0 z-[135] hidden opacity-0 transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="sectionPositionModalTitle">
    <div class="absolute inset-0 bg-black/60" data-close-section-position-modal></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-5 py-4 text-white">
                <div>
                    <h3 id="sectionPositionModalTitle" class="text-lg font-bold tracking-tight">Add Position</h3>
                    <p class="text-xs text-white/80">Create a position for <span id="sectionPositionModalSectionName" class="font-semibold">this section</span>.</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-section-position-modal>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="sectionPositionForm" class="space-y-4 p-5">
                <div>
                    <label for="sectionPositionTitleInput" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Position Title</label>
                    <input id="sectionPositionTitleInput" name="position_title" type="text" maxlength="255" class="mt-1 h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="e.g. Administrative Officer" required />
                </div>

                <p id="sectionPositionError" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700"></p>

                <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                    <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100" data-close-section-position-modal>
                        Cancel
                    </button>
                    <button type="submit" id="sectionPositionSubmitBtn" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#204835]">
                        <i class="fas fa-save"></i>
                        Save Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="employeesViewModal" class="fixed inset-0 z-[120] hidden opacity-0 transition-opacity duration-300" aria-labelledby="employeesViewTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-employee-view-modal></div>
    <div class="relative flex min-h-screen items-center justify-center p-3 sm:p-4">
        <div class="employee-view-panel relative flex h-[calc(100vh-1.5rem)] w-full max-w-[min(96vw,1700px)] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2 sm:h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-2rem)]">
            <div class="flex-shrink-0 flex items-center justify-between rounded-t-2xl bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-6 py-5 text-white sm:px-8 sm:py-6">
                <div>
                    <h3 id="employeesViewTitle" class="text-lg font-bold tracking-tight">Employee Details</h3>
                    <p class="text-xs text-white/80">Review profile, then edit or remove record.</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/80 transition-all hover:bg-white/10 hover:text-white" data-close-employee-view-modal data-employee-view-focus>
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8" id="employeeModalBody">
                <div id="employeeModalDetailsSection" class="space-y-5 transition-all duration-200 opacity-100 translate-y-0">
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                        <div class="flex items-start gap-4">
                            <img id="employeeViewAvatar" src="{{ asset('images/default-avatar.png') }}" alt="Employee profile" class="h-16 w-16 rounded-xl border border-emerald-200 object-cover shadow-sm" />
                            <div>
                                <p id="employeeViewName" class="text-lg font-bold text-[#1a3a2d]"></p>
                                <p id="employeeViewId" class="text-xs font-semibold uppercase tracking-[0.16em] text-[#1a3a2d]/70"></p>
                                <p id="employeeViewPosition" class="mt-1 text-sm font-medium text-emerald-700"></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Record ID</p>
                            <p id="employeeViewRecordId" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">First Name</p>
                            <p id="employeeViewFirstName" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Middle Name</p>
                            <p id="employeeViewMiddleName" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Last Name</p>
                            <p id="employeeViewLastName" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Suffix</p>
                            <p id="employeeViewSuffix" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Date of Birth</p>
                            <p id="employeeViewDateOfBirth" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Gender</p>
                            <p id="employeeViewGender" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Marital Status</p>
                            <p id="employeeViewMaritalStatus" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Email</p>
                            <p id="employeeViewEmail" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Contact</p>
                            <p id="employeeViewContact" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Division</p>
                            <p id="employeeViewDivision" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Section</p>
                            <p id="employeeViewSection" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Account Username</p>
                            <p id="employeeViewAccount" class="mt-1 text-sm font-semibold text-emerald-700"></p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-white p-3 sm:col-span-2 lg:col-span-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-500">Account Access</p>
                            <p id="employeeViewAccountAccess" class="mt-1 text-sm font-medium text-gray-800"></p>
                        </div>
                    </div>
                </div>

                <div id="employeeModalEditSection" class="hidden space-y-4 transition-all duration-200 opacity-0 translate-y-1 pointer-events-none">
                    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-white p-4">
                        <div class="flex items-start gap-3">
                            <img id="employeeEditAvatar" src="{{ asset('images/default-avatar.png') }}" alt="Employee avatar" class="h-12 w-12 rounded-xl border border-emerald-200 object-cover shadow-sm" />
                            <div>
                                <p class="text-base font-bold text-[#1a3a2d]">Edit Employee Record</p>
                                <p class="mt-1 text-sm text-emerald-900/80">Full-profile modal editor with assignment and access details. Save updates instantly without leaving this page.</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <input id="employeeEditProfileImage" name="profile_img" type="file" accept="image/*" class="hidden" />
                                    <label for="employeeEditProfileImage" class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-50">
                                        <i class="fas fa-camera"></i>
                                        Change Photo
                                    </label>
                                    <span id="employeeEditProfileImageName" class="text-xs text-emerald-800/80">No new file selected</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="employeeModalEditForm" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label for="employeeEditEmployeeId" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Employee ID</label>
                                <input id="employeeEditEmployeeId" name="employee_id" type="text" readonly class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-gray-700" />
                            </div>
                            <div>
                                <label for="employeeEditDateOfBirth" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Date of Birth</label>
                                <input id="employeeEditDateOfBirth" name="date_of_birth" type="date" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" max="{{ now()->toDateString() }}" autocomplete="bday" />
                            </div>
                            <div>
                                <label for="employeeEditSuffix" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Suffix</label>
                                <input id="employeeEditSuffix" name="suffix" type="text" maxlength="50" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="Jr., Sr., III" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="employeeEditFirstName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">First Name</label>
                                <input id="employeeEditFirstName" name="first_name" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="e.g. Juan" autocomplete="given-name" />
                            </div>
                            <div>
                                <label for="employeeEditMiddleName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Middle Name</label>
                                <input id="employeeEditMiddleName" name="middle_name" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="e.g. Santos" autocomplete="additional-name" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="employeeEditLastName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Last Name</label>
                                <input id="employeeEditLastName" name="last_name" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="e.g. Dela Cruz" autocomplete="family-name" />
                            </div>
                            <div>
                                <label for="employeeEditEmail" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Email</label>
                                <input id="employeeEditEmail" name="email" type="email" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="name@example.com" />
                            </div>
                            <div>
                                <label for="employeeEditContact" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Contact</label>
                                <div class="relative mt-1">
                                    <span class="pointer-events-none absolute inset-y-0 left-3 inline-flex items-center text-xs font-semibold text-gray-500">+63</span>
                                    <input id="employeeEditContact" name="contact_no" type="text" maxlength="10" inputmode="numeric" class="h-10 w-full rounded-xl border border-gray-200 bg-white pl-12 pr-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="9XXXXXXXXX" autocomplete="tel-national" />
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Enter 10-digit PH mobile number (example: 9171234567).</p>
                            </div>
                            <div>
                                <label for="employeeEditGender" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Gender</label>
                                <select id="employeeEditGender" name="gender" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Select gender</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender }}">{{ ucfirst($gender) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="employeeEditMaritalStatus" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Marital Status</label>
                                <select id="employeeEditMaritalStatus" name="marital_status" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Select status</option>
                                    @foreach ($maritalStatuses as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="employeeEditDivision" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Division</label>
                                <select id="employeeEditDivision" name="division_id" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Unassigned</option>
                                    @foreach ($divisions as $division)
                                        <option value="{{ $division->division_id }}">{{ $division->division_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="employeeEditSection" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Section</label>
                                <select id="employeeEditSection" name="section_id" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Unassigned</option>
                                </select>
                            </div>
                            <div>
                                <label for="employeeEditPosition" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Position</label>
                                <select id="employeeEditPosition" name="position_id" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Unassigned</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->position_id }}">{{ $position->position_title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="employeeEditAccount" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Linked Account</label>
                                <select id="employeeEditAccount" name="account_id" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Unassigned</option>
                                </select>
                            </div>
                            <div id="employeeModalCreateAccountWrap" class="hidden sm:col-span-2 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3">
                                <label class="inline-flex items-start gap-2 text-sm font-semibold text-emerald-900">
                                    <input id="employeeModalCreateAccountToggle" name="create_account" type="checkbox" value="1" class="mt-0.5 h-4 w-4 rounded border-emerald-300 text-emerald-700 focus:ring-emerald-500" />
                                    <span>Create a new account for this employee</span>
                                </label>

                                <div id="employeeModalCreateAccountFields" class="mt-3 hidden grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="employeeModalNewAccountUsername" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Username</label>
                                        <input id="employeeModalNewAccountUsername" name="new_account_username" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="letters, numbers, ., _, -" autocomplete="username" />
                                    </div>
                                    <div>
                                        <label for="employeeModalNewAccountRole" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Role</label>
                                        <select id="employeeModalNewAccountRole" name="new_account_role" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                            <option value="employee">Employee</option>
                                            <option value="custodian">Custodian</option>
                                            <option value="iac">Inspection and Acceptance Officer</option>
                                            <option value="division_head">Division head</option>
                                            <option value="bac">Bids and Awards Committee</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="employeeModalNewAccountPassword" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Password (Optional)</label>
                                        <input id="employeeModalNewAccountPassword" name="new_account_password" type="password" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="At least 6 characters (optional)" autocomplete="new-password" />
                                        <p class="mt-1 text-xs text-emerald-900/70">If blank, default password is <span class="font-semibold">password</span>.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p id="employeeModalEditError" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700"></p>
                        <div class="flex justify-end border-t border-gray-100 pt-3">
                            <button type="submit" id="employeeModalSaveBtn" data-loading-text="Saving employee changes..." class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#204835]">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex-shrink-0 border-t border-gray-100 bg-gray-50/70 px-6 py-4 sm:px-8">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button type="button" id="employeeModalDetailsAction" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white shadow">
                        <i class="fas fa-eye text-xs"></i>
                        Details
                    </button>
                    <button type="button" id="employeeModalEditAction" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                        <i class="fas fa-pen text-xs"></i>
                        Edit
                    </button>
                    <button type="button" id="employeeViewDeleteBtn" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                        <i class="fas fa-trash text-xs"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="employeeDeleteConfirmModal" class="fixed inset-0 z-[140] hidden opacity-0 transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="employeeDeleteConfirmTitle">
    <div class="absolute inset-0 bg-black/60" data-close-employee-delete-confirm></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl border border-red-100 bg-white p-5 shadow-2xl">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-triangle-exclamation"></i>
                </span>
                <div class="min-w-0">
                    <h4 id="employeeDeleteConfirmTitle" class="text-lg font-bold text-gray-900">Confirm Employee Deletion</h4>
                    <p class="mt-1 text-sm text-gray-600">You are about to delete <span id="employeeDeleteConfirmName" class="font-semibold text-gray-900"></span>. This action cannot be undone.</p>
                </div>
            </div>
            <div class="mt-5 flex items-center justify-end gap-2">
                <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" data-close-employee-delete-confirm>Cancel</button>
                <button type="button" id="employeeDeleteConfirmBtn" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                    <i class="fas fa-trash mr-1"></i>
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<div id="employeesToastStack" class="pointer-events-none fixed right-4 top-6 z-[130] w-[min(24rem,calc(100vw-2rem))] space-y-2"></div>
@endsection

@push('scripts')
@php
    $sectionsByDivisionData = $divisions->mapWithKeys(function ($division) {
        return [
            (string) $division->division_id => $division->sections->map(function ($section) {
                return [
                    'id' => (string) $section->section_id,
                    'name' => $section->section_name,
                ];
            })->values(),
        ];
    });

    $positionsBySectionData = $positions->groupBy('section_id')
        ->mapWithKeys(function ($sectionPositions, $sectionId) {
            if ($sectionId === null || $sectionId === '') {
                return [];
            }

            return [
                (string) $sectionId => $sectionPositions->map(function ($position) {
                    return [
                        'id' => (string) $position->position_id,
                        'title' => (string) $position->position_title,
                    ];
                })->values(),
            ];
        });

    $accountOptionsData = $accounts->map(function ($account) {
        $normalizedRole = strtolower((string) $account->role);
        $roleLabel = match ($normalizedRole) {
            'iac' => 'Inspection and Acceptance Officer',
            'bac' => 'Bids and Awards Committee',
            default => ucwords(str_replace('_', ' ', $normalizedRole)),
        };

        return [
            'id' => (string) $account->account_id,
            'label' => $account->username . ' (' . $roleLabel . ')',
            'employee_id' => (string) ($account->employee->employee_id ?? ''),
        ];
    })->values();
@endphp
<script>
    (function () {
        const form = document.getElementById('employeesFiltersForm');
        const tableWrapper = document.getElementById('employeeTableContainer');
        const totalSpan = document.getElementById('employeeCount');
        const paginationDiv = document.getElementById('employeePagination');
        const searchInput = document.getElementById('search');
        const divisionSelect = document.getElementById('division');
        const sectionSelect = document.getElementById('section');
        const assignmentSelect = document.getElementById('assignment');
        const resetLink = document.getElementById('employeesResetFilters');
        const pdfBtn = document.getElementById('employeesPrintPdfBtn');
        const excelBtn = document.getElementById('employeesExportExcelBtn');
        const viewModal = document.getElementById('employeesViewModal');
        const viewPanel = viewModal?.querySelector('.employee-view-panel') ?? null;
        const viewFocusTarget = viewModal?.querySelector('[data-employee-view-focus]') ?? null;
        const toastStackEl = document.getElementById('employeesToastStack');

        if (toastStackEl && toastStackEl.parentElement !== document.body) {
            document.body.appendChild(toastStackEl);
        }

        const viewName = document.getElementById('employeeViewName');
        const viewId = document.getElementById('employeeViewId');
        const viewPosition = document.getElementById('employeeViewPosition');
        const viewAvatar = document.getElementById('employeeViewAvatar');
        const viewRecordId = document.getElementById('employeeViewRecordId');
        const viewFirstName = document.getElementById('employeeViewFirstName');
        const viewMiddleName = document.getElementById('employeeViewMiddleName');
        const viewLastName = document.getElementById('employeeViewLastName');
        const viewSuffix = document.getElementById('employeeViewSuffix');
        const viewDateOfBirth = document.getElementById('employeeViewDateOfBirth');
        const viewGender = document.getElementById('employeeViewGender');
        const viewMaritalStatus = document.getElementById('employeeViewMaritalStatus');
        const viewEmail = document.getElementById('employeeViewEmail');
        const viewContact = document.getElementById('employeeViewContact');
        const viewSection = document.getElementById('employeeViewSection');
        const viewDivision = document.getElementById('employeeViewDivision');
        const viewAccount = document.getElementById('employeeViewAccount');
        const viewAccountAccess = document.getElementById('employeeViewAccountAccess');
        const detailsSection = document.getElementById('employeeModalDetailsSection');
        const editSectionPanel = document.getElementById('employeeModalEditSection');
        const detailsActionBtn = document.getElementById('employeeModalDetailsAction');
        const editActionBtn = document.getElementById('employeeModalEditAction');
        const editForm = document.getElementById('employeeModalEditForm');
        const saveBtn = document.getElementById('employeeModalSaveBtn');
        const editError = document.getElementById('employeeModalEditError');
        const editEmployeeId = document.getElementById('employeeEditEmployeeId');
        const editDateOfBirth = document.getElementById('employeeEditDateOfBirth');
        const editFirstName = document.getElementById('employeeEditFirstName');
        const editMiddleName = document.getElementById('employeeEditMiddleName');
        const editLastName = document.getElementById('employeeEditLastName');
        const editSuffix = document.getElementById('employeeEditSuffix');
        const editEmail = document.getElementById('employeeEditEmail');
        const editContact = document.getElementById('employeeEditContact');
        const editGender = document.getElementById('employeeEditGender');
        const editMaritalStatus = document.getElementById('employeeEditMaritalStatus');
        const editDivision = document.getElementById('employeeEditDivision');
        const editSectionField = document.getElementById('employeeEditSection');
        const editPosition = document.getElementById('employeeEditPosition');
        const editAccount = document.getElementById('employeeEditAccount');
        const editAvatar = document.getElementById('employeeEditAvatar');
        const editProfileImageInput = document.getElementById('employeeEditProfileImage');
        const editProfileImageName = document.getElementById('employeeEditProfileImageName');
        const createAccountWrap = document.getElementById('employeeModalCreateAccountWrap');
        const createAccountToggle = document.getElementById('employeeModalCreateAccountToggle');
        const createAccountFields = document.getElementById('employeeModalCreateAccountFields');
        const newAccountUsername = document.getElementById('employeeModalNewAccountUsername');
        const newAccountRole = document.getElementById('employeeModalNewAccountRole');
        const newAccountPassword = document.getElementById('employeeModalNewAccountPassword');
        const viewDeleteBtn = document.getElementById('employeeViewDeleteBtn');
        const deleteConfirmModal = document.getElementById('employeeDeleteConfirmModal');
        const deleteConfirmBtn = document.getElementById('employeeDeleteConfirmBtn');
        const deleteConfirmName = document.getElementById('employeeDeleteConfirmName');
        const divisionOverviewModal = document.getElementById('divisionOverviewDetailsModal');
        const divisionOverviewTitle = document.getElementById('divisionOverviewDetailsTitle');
        const divisionOverviewMeta = document.getElementById('divisionOverviewDetailsMeta');
        const divisionOverviewTotal = document.getElementById('divisionOverviewDetailsTotal');
        const divisionOverviewSectionCount = document.getElementById('divisionOverviewDetailsSectionCount');
        const divisionOverviewSections = document.getElementById('divisionOverviewDetailsSections');
        const divisionOverviewAddBtn = document.getElementById('divisionOverviewDetailsAddBtn');
        const sectionPositionModal = document.getElementById('sectionPositionModal');
        const sectionPositionForm = document.getElementById('sectionPositionForm');
        const sectionPositionInput = document.getElementById('sectionPositionTitleInput');
        const sectionPositionError = document.getElementById('sectionPositionError');
        const sectionPositionSubmitBtn = document.getElementById('sectionPositionSubmitBtn');
        const sectionPositionSectionName = document.getElementById('sectionPositionModalSectionName');
        const createDivisionModal = document.getElementById('createDivisionModal');
        const openCreateDivisionModalBtn = document.getElementById('openCreateDivisionModal');
        const createDivisionForm = document.getElementById('createDivisionForm');
        const createDivisionError = document.getElementById('createDivisionError');
        const createDivisionSubmitBtn = document.getElementById('createDivisionSubmitBtn');
        const addInitialSectionRowBtn = document.getElementById('addInitialSectionRow');
        const initialSectionsRows = document.getElementById('initialSectionsRows');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const sectionPositionStoreUrlTemplate = `{{ route('sections.positions.store', ['section' => '__SECTION__']) }}`;

        const sectionsByDivision = @json($sectionsByDivisionData);
        const positionsBySection = @json($positionsBySectionData);

        const allSections = Object.values(sectionsByDivision).flat();

        const accountOptions = @json($accountOptionsData);

        const buildProfileImageUrl = (path) => {
            if (!path) return `{{ asset('images/default-avatar.png') }}`;
            if (/^https?:\/\//i.test(path)) return path;
            return `{{ asset('') }}${String(path).replace(/^\//, '')}`;
        };

        const formatDateForView = (isoDate) => {
            if (!isoDate) return 'Not provided';
            const date = new Date(`${isoDate}T00:00:00`);
            if (Number.isNaN(date.getTime())) return isoDate;
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            }).format(date);
        };

        const normalizeNamePart = (value) => String(value || '').trim();

        const buildLegalName = (firstName, middleName, lastName, suffix) => {
            const first = normalizeNamePart(firstName);
            const middle = normalizeNamePart(middleName);
            const last = normalizeNamePart(lastName);
            const suffixPart = normalizeNamePart(suffix);

            const givenNames = [first, middle].filter(Boolean).join(' ').trim();
            let legal = '';

            if (last && givenNames) {
                legal = `${last}, ${givenNames}`;
            } else {
                legal = [last, givenNames].filter(Boolean).join(' ');
            }

            if (suffixPart) {
                legal = `${legal}${legal ? ' ' : ''}${suffixPart}`;
            }

            return legal || '—';
        };

        const formatPhoneForView = (value) => {
            const raw = String(value || '').trim();
            if (!raw || raw === 'Not provided' || raw === '—') {
                return '—';
            }

            const digits = raw.replace(/\D/g, '');
            if (!digits) {
                return raw;
            }

            if (digits.startsWith('63') && digits.length === 12) {
                return `+${digits}`;
            }

            if (digits.startsWith('9') && digits.length === 10) {
                return `+63${digits}`;
            }

            if (digits.startsWith('0') && digits.length === 11) {
                return `+63${digits.slice(1)}`;
            }

            return raw.startsWith('+63') ? raw : `+63${digits.slice(-10)}`;
        };

        if (!form || !tableWrapper) return;

        let debounceTimer = null;
        let activeController = null;
        let modalDeleteUrl = null;
        let modalUpdateUrl = null;
        let activeModalButton = null;
        let pendingDelete = false;
        let activeDivisionDetailsTrigger = null;
        let activeDivisionSections = [];
        let activeSectionIdForPositionCreate = null;
        let activeSectionAddButton = null;
        const activeToastKeys = new Set();

        const showToast = (message, type = 'success') => {
            if (!toastStackEl || !message) return;

            const key = `${type}:${message}`;
            if (activeToastKeys.has(key)) return;
            activeToastKeys.add(key);

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto rounded-xl border px-5 py-3 text-sm font-semibold text-white shadow-2xl opacity-0 translate-y-2 transition-all duration-200 ease-out';
            toast.textContent = message;

            if (type === 'error') {
                toast.classList.add('border-red-800', 'bg-red-700');
            } else {
                toast.classList.add('border-emerald-800', 'bg-[#1a3a2d]');
            }

            toastStackEl.appendChild(toast);

            while (toastStackEl.children.length > 3) {
                const first = toastStackEl.firstElementChild;
                if (first) first.remove();
            }

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
            });

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    toast.remove();
                    activeToastKeys.delete(key);
                }, 220);
            }, 3200);
        };

        const flashStatusEl = document.getElementById('employeesFlashStatus');
        const flashStatusMessage = flashStatusEl?.dataset?.message || '';
        if (flashStatusMessage) {
            setTimeout(() => showToast(flashStatusMessage, 'success'), 120);
        }

        const renderSectionsForModal = (divisionId, selectedSection = '') => {
            if (!editSectionField) return;

            const normalizedDivision = divisionId ? String(divisionId) : '';
            const options = normalizedDivision && sectionsByDivision[normalizedDivision]
                ? sectionsByDivision[normalizedDivision]
                : allSections;

            editSectionField.innerHTML = '<option value="">Unassigned</option>';
            options.forEach((section) => {
                const opt = document.createElement('option');
                opt.value = String(section.id);
                opt.textContent = section.name;
                if (selectedSection && String(selectedSection) === String(section.id)) {
                    opt.selected = true;
                }
                editSectionField.appendChild(opt);
            });
        };

        const renderPositionsForModal = (sectionId, selectedPosition = '') => {
            if (!editPosition) return;

            const normalizedSection = sectionId ? String(sectionId) : '';
            const options = normalizedSection && positionsBySection[normalizedSection]
                ? positionsBySection[normalizedSection]
                : [];

            editPosition.innerHTML = '<option value="">Unassigned</option>';
            options.forEach((position) => {
                const opt = document.createElement('option');
                opt.value = String(position.id);
                opt.textContent = position.title;
                if (selectedPosition && String(selectedPosition) === String(position.id)) {
                    opt.selected = true;
                }
                editPosition.appendChild(opt);
            });
        };

        const renderAccountsForModal = (currentEmployeeCode = '', selectedAccountId = '', currentLabel = '') => {
            if (!editAccount) return;

            editAccount.innerHTML = '<option value="">Unassigned</option>';

            if (selectedAccountId) {
                const current = accountOptions.find((account) => String(account.id) === String(selectedAccountId));

                const opt = document.createElement('option');
                opt.value = String(selectedAccountId);
                opt.textContent = current?.label || currentLabel || `Account #${selectedAccountId}`;
                opt.selected = true;
                editAccount.appendChild(opt);
            }

            if (selectedAccountId && !editAccount.querySelector(`option[value="${String(selectedAccountId)}"]`)) {
                const opt = document.createElement('option');
                opt.value = String(selectedAccountId);
                opt.textContent = currentLabel || `Account #${selectedAccountId}`;
                opt.selected = true;
                editAccount.appendChild(opt);
            }
        };

        const toggleDeleteConfirm = (show) => {
            if (!deleteConfirmModal) return;

            if (show) {
                deleteConfirmModal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    deleteConfirmModal.classList.remove('opacity-0');
                    deleteConfirmModal.classList.add('opacity-100');
                });
                return;
            }

            deleteConfirmModal.classList.remove('opacity-100');
            deleteConfirmModal.classList.add('opacity-0');
            setTimeout(() => {
                deleteConfirmModal.classList.add('hidden');
            }, 200);
        };

        const toggleDivisionOverviewModal = (show) => {
            if (!divisionOverviewModal) return;

            if (show) {
                divisionOverviewModal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    divisionOverviewModal.classList.remove('opacity-0');
                    divisionOverviewModal.classList.add('opacity-100');
                });
                return;
            }

            divisionOverviewModal.classList.remove('opacity-100');
            divisionOverviewModal.classList.add('opacity-0');
            setTimeout(() => {
                divisionOverviewModal.classList.add('hidden');
            }, 220);
        };

        const toggleCreateDivisionModal = (show) => {
            if (!createDivisionModal) return;

            if (show) {
                createDivisionModal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    createDivisionModal.classList.remove('opacity-0');
                    createDivisionModal.classList.add('opacity-100');
                });
                return;
            }

            createDivisionModal.classList.remove('opacity-100');
            createDivisionModal.classList.add('opacity-0');
            setTimeout(() => {
                createDivisionModal.classList.add('hidden');
            }, 220);
        };

        const toggleSectionPositionModal = (show) => {
            if (!sectionPositionModal) return;

            if (show) {
                sectionPositionModal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    sectionPositionModal.classList.remove('opacity-0');
                    sectionPositionModal.classList.add('opacity-100');
                });

                setTimeout(() => {
                    sectionPositionInput?.focus();
                    sectionPositionInput?.select();
                }, 120);
                return;
            }

            sectionPositionModal.classList.remove('opacity-100');
            sectionPositionModal.classList.add('opacity-0');
            setTimeout(() => {
                sectionPositionModal.classList.add('hidden');
            }, 200);
        };

        const resetSectionPositionForm = () => {
            sectionPositionForm?.reset();
            sectionPositionError?.classList.add('hidden');
            if (sectionPositionError) {
                sectionPositionError.textContent = '';
            }
            sectionPositionInput?.classList.remove('border-red-300', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:ring-red-100');
        };

        const normalizePositionTitle = (value) => String(value || '')
            .trim()
            .replace(/\s+/g, ' ')
            .toLowerCase();

        const getActiveSectionPositionTitles = () => {
            const sectionId = String(activeSectionIdForPositionCreate || '').trim();
            if (!sectionId) {
                return [];
            }

            const sectionEntry = activeDivisionSections.find((item) => String(item.id) === sectionId);
            return Array.isArray(sectionEntry?.positions) ? sectionEntry.positions : [];
        };

        const validateSectionPositionInput = (showErrorMessage = true) => {
            const rawValue = String(sectionPositionInput?.value || '');
            const normalized = normalizePositionTitle(rawValue);
            const existingNormalized = new Set(
                getActiveSectionPositionTitles()
                    .map((title) => normalizePositionTitle(title))
                    .filter(Boolean)
            );

            let message = '';
            if (!normalized) {
                message = 'Position title cannot be empty.';
            } else if (existingNormalized.has(normalized)) {
                message = 'Position already exists in this section (same spelling, different capitalization is not allowed).';
            }

            const isValid = message === '';

            if (sectionPositionSubmitBtn) {
                sectionPositionSubmitBtn.disabled = !isValid;
                sectionPositionSubmitBtn.classList.toggle('opacity-70', !isValid);
                sectionPositionSubmitBtn.classList.toggle('cursor-not-allowed', !isValid);
            }

            if (!isValid) {
                if (showErrorMessage && sectionPositionError) {
                    sectionPositionError.textContent = message;
                    sectionPositionError.classList.remove('hidden');
                }
                sectionPositionInput?.classList.add('border-red-300', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:ring-red-100');
            } else {
                sectionPositionError?.classList.add('hidden');
                if (sectionPositionError) {
                    sectionPositionError.textContent = '';
                }
                sectionPositionInput?.classList.remove('border-red-300', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:ring-red-100');
            }

            return isValid;
        };

        const addSectionInputRow = (value = '') => {
            if (!initialSectionsRows) return;

            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';
            row.setAttribute('data-section-row', '1');
            row.innerHTML = `
                <input type="text" name="sections[]" maxlength="255" class="h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40" placeholder="e.g. Procurement Section" required />
                <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100" data-remove-section-row>
                    Remove
                </button>
            `;

            const input = row.querySelector('input[name="sections[]"]');
            if (input) {
                input.value = value;
            }

            initialSectionsRows.appendChild(row);
        };

        const resetCreateDivisionForm = () => {
            if (!createDivisionForm || !initialSectionsRows) return;

            createDivisionForm.reset();
            createDivisionError?.classList.add('hidden');
            createDivisionError.textContent = '';

            initialSectionsRows.innerHTML = '';
            addSectionInputRow('');
        };

        const openDivisionOverviewDetails = (button) => {
            if (!button || !divisionOverviewSections) return;

            const divisionId = String(button.dataset.divisionId || '').trim();
            const divisionName = String(button.dataset.divisionName || 'Division').trim();
            const divisionTotal = Number(button.dataset.divisionTotal || 0);

            let sections = [];
            try {
                sections = JSON.parse(button.dataset.divisionSections || '[]');
            } catch (error) {
                sections = [];
            }

            activeDivisionDetailsTrigger = button;
            activeDivisionSections = Array.isArray(sections) ? sections : [];

            if (divisionOverviewTitle) {
                divisionOverviewTitle.textContent = `${divisionName} Details`;
            }

            if (divisionOverviewMeta) {
                divisionOverviewMeta.textContent = 'Review section staffing and their positions, then add a new employee in this division.';
            }

            if (divisionOverviewTotal) {
                divisionOverviewTotal.textContent = Number.isFinite(divisionTotal) ? divisionTotal.toLocaleString() : '0';
            }

            if (divisionOverviewSectionCount) {
                divisionOverviewSectionCount.textContent = sections.length.toLocaleString();
            }

            if (divisionOverviewAddBtn) {
                const baseUrl = `{{ route('employees.create') }}`;
                divisionOverviewAddBtn.setAttribute('href', divisionId ? `${baseUrl}?division_id=${encodeURIComponent(divisionId)}` : baseUrl);
            }

            divisionOverviewSections.innerHTML = '';

            if (!activeDivisionSections.length) {
                const empty = document.createElement('li');
                empty.className = 'rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-sm text-gray-500';
                empty.textContent = 'No sections found for this division.';
                divisionOverviewSections.appendChild(empty);
            } else {
                activeDivisionSections.forEach((section) => {
                    const li = document.createElement('li');
                    li.className = 'rounded-xl border border-gray-100 bg-white px-3 py-2';

                    const sectionName = String(section?.name || 'Unnamed section');
                    const sectionEmployees = Number(section?.employees || 0);
                    const sectionPositions = Array.isArray(section?.positions) ? section.positions.filter(Boolean) : [];
                    const positionsHtml = sectionPositions.length
                        ? `<p class="mt-1 text-xs text-gray-500">Positions: ${sectionPositions.join(', ')}</p>`
                        : '<p class="mt-1 text-xs text-gray-400">Positions: No assigned positions yet</p>';
                    const sectionId = Number(section?.id || 0);

                    li.innerHTML = `
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-gray-800">${sectionName}</span>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">${sectionEmployees.toLocaleString()} employee${sectionEmployees === 1 ? '' : 's'}</span>
                                <button type="button" class="js-add-section-position inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100" data-section-id="${sectionId}">
                                    <i class="fas fa-plus text-[10px]"></i>
                                    Add Position
                                </button>
                            </div>
                        </div>
                        ${positionsHtml}
                    `;

                    divisionOverviewSections.appendChild(li);
                });
            }

            toggleDivisionOverviewModal(true);
        };

        const fieldElements = {
            first_name: editFirstName,
            middle_name: editMiddleName,
            last_name: editLastName,
            suffix: editSuffix,
            date_of_birth: editDateOfBirth,
            gender: editGender,
            marital_status: editMaritalStatus,
            contact_no: editContact,
            email: editEmail,
            division_id: editDivision,
            section_id: editSectionField,
            position_id: editPosition,
            account_id: editAccount,
            new_account_username: newAccountUsername,
            new_account_role: newAccountRole,
            new_account_password: newAccountPassword,
            create_account: createAccountToggle,
        };

        const sanitizePhoneLocal = (value = '') => {
            let digits = String(value || '').replace(/\D/g, '');

            if (digits.startsWith('63')) {
                digits = digits.slice(2);
            }

            if (digits.startsWith('0')) {
                digits = digits.slice(1);
            }

            return digits.slice(0, 10);
        };

        const clientValidationRules = {
            first_name: (value) => {
                const v = String(value || '').trim();
                if (!v) return 'First Name is required.';
                if (!/^[A-Za-z .'-]+$/.test(v)) return 'First Name may only contain letters, spaces, apostrophe, period, and hyphen.';
                return '';
            },
            middle_name: (value) => {
                const v = String(value || '').trim();
                if (!v) return '';
                if (!/^[A-Za-z .'-]+$/.test(v)) return 'Middle Name may only contain letters, spaces, apostrophe, period, and hyphen.';
                return '';
            },
            last_name: (value) => {
                const v = String(value || '').trim();
                if (!v) return 'Last Name is required.';
                if (!/^[A-Za-z .'-]+$/.test(v)) return 'Last Name may only contain letters, spaces, apostrophe, period, and hyphen.';
                return '';
            },
            suffix: (value) => {
                const v = String(value || '').trim();
                if (!v) return '';
                if (!/^[A-Za-z0-9.,\- ]+$/.test(v)) return 'Suffix may only contain letters, numbers, comma, period, spaces, and hyphen.';
                if (v.length > 10) return 'Suffix must not exceed 10 characters.';
                return '';
            },
            date_of_birth: (value) => {
                const v = String(value || '').trim();
                if (!v) return 'Date of Birth is required.';
                const selected = new Date(v);
                if (Number.isNaN(selected.getTime())) return 'Please provide a valid Date of Birth.';
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (selected > today) return 'Date of Birth cannot be in the future.';
                return '';
            },
            gender: (value) => String(value || '').trim() ? '' : 'Gender is required.',
            marital_status: (value) => String(value || '').trim() ? '' : 'Marital Status is required.',
            contact_no: (value) => {
                const localDigits = sanitizePhoneLocal(value);
                if (!localDigits) return 'Contact Number is required.';
                if (!/^9\d{9}$/.test(localDigits)) return 'Enter a valid PH mobile number (example: +639171234567).';
                return '';
            },
            email: (value, field) => {
                const v = String(value || '').trim();
                if (!v) return 'Email Address is required.';
                if (field && typeof field.checkValidity === 'function' && !field.checkValidity()) {
                    return 'Enter a valid email address (example: name@example.com).';
                }
                return '';
            },
            division_id: (value) => String(value || '').trim() ? '' : 'Division is required.',
            section_id: (value) => String(value || '').trim() ? '' : 'Section is required.',
            position_id: (value) => String(value || '').trim() ? '' : 'Position is required.',
            new_account_username: (value) => {
                if (!createAccountToggle?.checked) return '';
                const v = String(value || '').trim();
                if (!v) return 'Username is required when account creation is enabled.';
                if (!/^[A-Za-z0-9._-]{3,255}$/.test(v)) return 'Username must be 3-255 characters and may only contain letters, numbers, dot, underscore, or hyphen.';
                return '';
            },
            new_account_role: (value) => {
                if (!createAccountToggle?.checked) return '';
                return String(value || '').trim() ? '' : 'Role is required when account creation is enabled.';
            },
            new_account_password: (value) => {
                if (!createAccountToggle?.checked) return '';
                const v = String(value || '');
                if (!v) return '';
                return v.length >= 6 ? '' : 'Password must be at least 6 characters.';
            },
        };

        const resetCreateAccountFields = () => {
            if (createAccountToggle) createAccountToggle.checked = false;
            if (newAccountUsername) newAccountUsername.value = '';
            if (newAccountRole) newAccountRole.value = 'employee';
            if (newAccountPassword) newAccountPassword.value = '';
            createAccountFields?.classList.add('hidden');
        };

        const syncCreateAccountVisibility = (hasLinkedAccount = false) => {
            if (!createAccountWrap) return;

            const accountChosen = Boolean(editAccount?.value);
            const canCreate = !hasLinkedAccount && !accountChosen;
            createAccountWrap.classList.toggle('hidden', !canCreate);

            if (!canCreate) {
                resetCreateAccountFields();
            }
        };

        const clearFieldError = (element) => {
            if (!element) return;
            const defaultBorderClass = element.dataset.defaultBorderClass || 'border-gray-200';
            element.classList.remove('border-red-300', 'border-red-400', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:border-red-500', 'focus:ring-red-100');
            element.classList.remove('border-gray-200', 'border-gray-300');
            element.classList.add(defaultBorderClass);
            element.removeAttribute('aria-invalid');

            const existing = element.parentElement?.querySelector('[data-modal-field-error]');
            if (existing) {
                existing.remove();
            }
        };

        const clearModalValidation = () => {
            Object.values(fieldElements).forEach((element) => clearFieldError(element));
        };

        const setFieldError = (element, message) => {
            if (!element) return;

            clearFieldError(element);
            element.classList.add('border-red-400', 'ring-2', 'ring-red-100', 'focus:border-red-500', 'focus:ring-red-100');
            element.setAttribute('aria-invalid', 'true');

            if (message) {
                const msg = document.createElement('p');
                msg.className = 'mt-1 text-xs font-medium text-red-600';
                msg.setAttribute('data-modal-field-error', '1');
                msg.textContent = message;
                element.parentElement?.appendChild(msg);
            }
        };

        const applyValidationErrors = (errors) => {
            clearModalValidation();
            const messages = [];

            Object.entries(errors || {}).forEach(([field, values]) => {
                const message = Array.isArray(values) ? String(values[0] || '') : String(values || '');
                if (message) {
                    messages.push(message);
                }

                if (fieldElements[field]) {
                    setFieldError(fieldElements[field], message || 'Invalid value.');
                }
            });

            if (editError && messages.length) {
                editError.textContent = messages[0];
                editError.classList.remove('hidden');
            }
        };

        const validateClientField = (fieldName) => {
            const element = fieldElements[fieldName];
            const rule = clientValidationRules[fieldName];

            if (!element || typeof rule !== 'function') {
                return true;
            }

            const message = rule(element.value, element);
            if (message) {
                setFieldError(element, message);
                return false;
            }

            clearFieldError(element);
            return true;
        };

        const validateClientForm = () => {
            const fieldsToValidate = [
                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'date_of_birth',
                'gender',
                'marital_status',
                'contact_no',
                'email',
                'division_id',
                'section_id',
                'position_id',
                'new_account_username',
                'new_account_role',
                'new_account_password',
            ];

            let firstInvalid = null;

            fieldsToValidate.forEach((fieldName) => {
                const valid = validateClientField(fieldName);
                if (!valid && !firstInvalid) {
                    firstInvalid = fieldElements[fieldName];
                }
            });

            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            return true;
        };

        Object.values(fieldElements).forEach((element) => {
            if (!element) return;
            element.dataset.defaultBorderClass = element.classList.contains('border-gray-300')
                ? 'border-gray-300'
                : 'border-gray-200';

            const fieldName = element.name;
            const eventName = element.tagName === 'SELECT' || element.type === 'file' || element.type === 'checkbox' ? 'change' : 'input';
            element.addEventListener(eventName, () => {
                if (fieldName === 'contact_no') {
                    element.value = sanitizePhoneLocal(element.value);
                }
                if (fieldName in clientValidationRules) {
                    validateClientField(fieldName);
                }
            });

            element.addEventListener('blur', () => {
                if (fieldName === 'contact_no') {
                    element.value = sanitizePhoneLocal(element.value);
                }
                if (fieldName in clientValidationRules) {
                    validateClientField(fieldName);
                }
            });
        });

        if (editContact) {
            editContact.value = sanitizePhoneLocal(editContact.value);
        }

        const animateSectionSwitch = (showEdit) => {
            if (!detailsSection || !editSectionPanel) return;

            const toShow = showEdit ? editSectionPanel : detailsSection;
            const toHide = showEdit ? detailsSection : editSectionPanel;

            toHide.classList.remove('opacity-100', 'translate-y-0');
            toHide.classList.add('opacity-0', '-translate-y-1', 'pointer-events-none');

            setTimeout(() => {
                toHide.classList.add('hidden');
                toHide.classList.remove('-translate-y-1');
            }, 180);

            toShow.classList.remove('hidden', 'pointer-events-none', 'opacity-0', 'translate-y-1');
            toShow.classList.add('opacity-0', 'translate-y-1');

            requestAnimationFrame(() => {
                toShow.classList.remove('opacity-0', 'translate-y-1');
                toShow.classList.add('opacity-100', 'translate-y-0');
            });
        };

        const toggleViewModal = (show) => {
            if (!viewModal || !viewPanel) return;

            if (show) {
                viewModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                requestAnimationFrame(() => {
                    viewModal.classList.remove('opacity-0');
                    viewModal.classList.add('opacity-100');
                    viewPanel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                    viewFocusTarget?.focus();
                });
                return;
            }

            viewModal.classList.remove('opacity-100');
            viewModal.classList.add('opacity-0');
            viewPanel.classList.add('opacity-0', 'scale-95', 'translate-y-2');

            setTimeout(() => {
                viewModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                modalDeleteUrl = null;
                modalUpdateUrl = null;
                activeModalButton = null;
                pendingDelete = false;
                toggleDeleteConfirm(false);
                editError?.classList.add('hidden');
            }, 300);
        };

        const openViewModal = (button) => {
            if (!viewName || !viewId || !viewPosition || !viewEmail || !viewContact || !viewSection || !viewDivision || !viewAccount) {
                return;
            }

            const legalName = buildLegalName(
                button.dataset.firstName || '',
                button.dataset.middleName || '',
                button.dataset.lastName || '',
                button.dataset.suffix || ''
            );

            viewName.textContent = legalName;
            viewId.textContent = button.dataset.employeeCode || '—';
            viewPosition.textContent = button.dataset.employeePosition || 'Unassigned';
            if (viewAvatar) {
                viewAvatar.src = buildProfileImageUrl(button.dataset.profileImg || '');
            }
            if (viewRecordId) viewRecordId.textContent = button.dataset.employeeId || '—';
            if (viewFirstName) viewFirstName.textContent = button.dataset.firstName || 'Not provided';
            if (viewMiddleName) viewMiddleName.textContent = button.dataset.middleName || 'Not provided';
            if (viewLastName) viewLastName.textContent = button.dataset.lastName || 'Not provided';
            if (viewSuffix) viewSuffix.textContent = button.dataset.suffix || 'None';
            if (viewDateOfBirth) viewDateOfBirth.textContent = formatDateForView(button.dataset.dateOfBirth || '');
            if (viewGender) viewGender.textContent = button.dataset.gender || 'Not provided';
            if (viewMaritalStatus) viewMaritalStatus.textContent = button.dataset.maritalStatus || 'Not provided';
            viewEmail.textContent = button.dataset.employeeEmail || '—';
            viewContact.textContent = formatPhoneForView(button.dataset.employeeContact || '—');
            viewSection.textContent = button.dataset.employeeSection || '—';
            viewDivision.textContent = button.dataset.employeeDivision || '—';
            viewAccount.textContent = button.dataset.employeeAccount || 'None';
            if (viewAccountAccess) {
                viewAccountAccess.textContent = button.dataset.accountLabel || 'No linked account';
            }

            if (editEmployeeId) editEmployeeId.value = button.dataset.employeeCode || '';
            if (editDateOfBirth) editDateOfBirth.value = button.dataset.dateOfBirth || '';
            if (editFirstName) editFirstName.value = button.dataset.firstName || '';
            if (editMiddleName) editMiddleName.value = button.dataset.middleName || '';
            if (editLastName) editLastName.value = button.dataset.lastName || '';
            if (editSuffix) editSuffix.value = button.dataset.suffix || '';
            if (editEmail) editEmail.value = button.dataset.emailRaw || '';
            if (editContact) editContact.value = sanitizePhoneLocal(button.dataset.contactRaw || '');
            if (editGender) editGender.value = button.dataset.gender || '';
            if (editMaritalStatus) editMaritalStatus.value = button.dataset.maritalStatus || '';
            if (editDivision) editDivision.value = button.dataset.divisionId || '';
            renderSectionsForModal(button.dataset.divisionId || '', button.dataset.sectionId || '');
            renderPositionsForModal(button.dataset.sectionId || '', button.dataset.positionId || '');
            renderAccountsForModal(button.dataset.employeeCode || '', button.dataset.accountId || '', button.dataset.accountLabel || '');

            if (editAvatar) {
                const profile = button.dataset.profileImg || '';
                editAvatar.src = buildProfileImageUrl(profile);
            }

            const hasLinkedAccount = Boolean(button.dataset.accountId);
            syncCreateAccountVisibility(hasLinkedAccount);
            resetCreateAccountFields();

            if (editProfileImageInput) {
                editProfileImageInput.value = '';
            }
            if (editProfileImageName) {
                editProfileImageName.textContent = 'No new file selected';
            }

            modalDeleteUrl = button.dataset.employeeDeleteUrl || null;
            modalUpdateUrl = button.dataset.modalUpdateUrl || null;
            activeModalButton = button;
            pendingDelete = false;
            if (deleteConfirmName) {
                deleteConfirmName.textContent = legalName !== '—' ? legalName : (button.dataset.employeeName || 'this employee');
            }
            editError?.classList.add('hidden');
            clearModalValidation();

            setModalView('details');

            toggleViewModal(true);
        };

        const setModalView = (view) => {
            const showEdit = view === 'edit';
            animateSectionSwitch(showEdit);

            if (detailsActionBtn) {
                detailsActionBtn.classList.toggle('bg-[#1a3a2d]', !showEdit);
                detailsActionBtn.classList.toggle('text-white', !showEdit);
                detailsActionBtn.classList.toggle('shadow', !showEdit);
                detailsActionBtn.classList.toggle('border', showEdit);
                detailsActionBtn.classList.toggle('border-gray-200', showEdit);
                detailsActionBtn.classList.toggle('bg-white', showEdit);
                detailsActionBtn.classList.toggle('text-gray-700', showEdit);
            }

            if (editActionBtn) {
                editActionBtn.classList.toggle('bg-[#1a3a2d]', showEdit);
                editActionBtn.classList.toggle('text-white', showEdit);
                editActionBtn.classList.toggle('shadow', showEdit);
                editActionBtn.classList.toggle('border', !showEdit);
                editActionBtn.classList.toggle('border-gray-200', !showEdit);
                editActionBtn.classList.toggle('bg-white', !showEdit);
                editActionBtn.classList.toggle('text-gray-700', !showEdit);
            }
        };

        const setLoading = (loading) => {
            tableWrapper.classList.toggle('opacity-60', loading);
            tableWrapper.classList.toggle('pointer-events-none', loading);
        };

        const buildUrlFromForm = () => {
            const params = new URLSearchParams(new FormData(form));
            return `${window.location.pathname}?${params.toString()}`;
        };

        const applyUrlToFilters = (url) => {
            const target = new URL(url, window.location.origin);
            if (searchInput) searchInput.value = target.searchParams.get('search') || '';
            if (divisionSelect) divisionSelect.value = target.searchParams.get('division') || '';
            if (sectionSelect) sectionSelect.value = target.searchParams.get('section') || '';
            if (assignmentSelect) assignmentSelect.value = target.searchParams.get('assignment') || '';
        };

        const fetchEmployees = async (url, push = false) => {
            const target = new URL(url, window.location.origin);
            target.searchParams.set('ajax', '1');

            if (activeController) {
                activeController.abort();
            }

            const controller = new AbortController();
            activeController = controller;
            setLoading(true);

            try {
                const res = await fetch(target.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: controller.signal,
                });

                if (!res.ok) {
                    throw new Error(`Employees fetch failed (status ${res.status})`);
                }

                const data = await res.json();
                if (typeof data.html !== 'undefined') {
                    tableWrapper.innerHTML = data.html;
                }

                if (totalSpan && typeof data.total !== 'undefined') {
                    totalSpan.textContent = `${Number(data.total).toLocaleString()} records found`;
                }

                if (paginationDiv) {
                    paginationDiv.innerHTML = data.pagination || '';
                    bindPaginationLinks();
                }

                target.searchParams.delete('ajax');
                const nextUrl = `${target.pathname}${target.search ? target.search : ''}`;
                if (push) {
                    window.history.pushState({}, '', nextUrl);
                } else {
                    window.history.replaceState({}, '', nextUrl);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Failed to fetch employee table:', err);
                }
            } finally {
                if (activeController === controller) {
                    activeController = null;
                }
                setLoading(false);
            }
        };

        const scheduleFetch = (delay = 320) => {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchEmployees(buildUrlFromForm(), false), delay);
        };

        function bindPaginationLinks() {
            if (!paginationDiv) return;
            paginationDiv.querySelectorAll('a[href]').forEach(link => {
                if (!link.href.includes('page=')) return;
                if (link.dataset.ajaxBound === 'true') return;
                link.dataset.ajaxBound = 'true';
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    fetchEmployees(link.href, true);
                });
            });
        }

        searchInput?.addEventListener('input', () => scheduleFetch(320));
        searchInput?.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchEmployees(buildUrlFromForm(), false);
        });

        divisionSelect?.addEventListener('change', () => scheduleFetch(0));
        sectionSelect?.addEventListener('change', () => scheduleFetch(0));
        assignmentSelect?.addEventListener('change', () => scheduleFetch(0));

        resetLink?.addEventListener('click', (event) => {
            event.preventDefault();
            if (searchInput) searchInput.value = '';
            if (divisionSelect) divisionSelect.value = '';
            if (sectionSelect) sectionSelect.value = '';
            if (assignmentSelect) assignmentSelect.value = '';
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchEmployees(resetLink.href, true);
        });

        tableWrapper.addEventListener('click', (event) => {
            const viewBtn = event.target instanceof Element ? event.target.closest('[data-view-employee-btn]') : null;
            if (!viewBtn) return;
            event.preventDefault();
            openViewModal(viewBtn);
        });

        openCreateDivisionModalBtn?.addEventListener('click', () => {
            resetCreateDivisionForm();
            toggleCreateDivisionModal(true);
        });

        document.addEventListener('click', (event) => {
            const detailsBtn = event.target instanceof Element ? event.target.closest('.js-division-overview-details') : null;
            if (!detailsBtn) return;
            event.preventDefault();
            openDivisionOverviewDetails(detailsBtn);
        });

        divisionOverviewSections?.addEventListener('click', async (event) => {
            const addBtn = event.target instanceof Element ? event.target.closest('.js-add-section-position') : null;
            if (!addBtn) return;

            event.preventDefault();

            const sectionId = String(addBtn.getAttribute('data-section-id') || '').trim();
            if (!sectionId) return;

            const sectionEntry = activeDivisionSections.find((item) => String(item.id) === sectionId);
            activeSectionIdForPositionCreate = sectionId;
            activeSectionAddButton = addBtn;
            resetSectionPositionForm();

            if (sectionPositionSectionName) {
                sectionPositionSectionName.textContent = String(sectionEntry?.name || 'this section');
            }

            validateSectionPositionInput(false);
            toggleSectionPositionModal(true);
        });

        sectionPositionModal?.addEventListener('click', (event) => {
            const closeEl = event.target instanceof Element ? event.target.closest('[data-close-section-position-modal]') : null;
            if (!closeEl) return;
            event.preventDefault();
            toggleSectionPositionModal(false);
        });

        sectionPositionInput?.addEventListener('input', () => {
            validateSectionPositionInput(true);
        });

        sectionPositionForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const sectionId = String(activeSectionIdForPositionCreate || '').trim();
            const positionTitle = String(sectionPositionInput?.value || '').replace(/\s+/g, ' ').trim();

            if (!sectionId) {
                showToast('Section context is missing. Please reopen the dialog.', 'error');
                return;
            }

            if (!validateSectionPositionInput(true)) {
                sectionPositionInput?.focus();
                return;
            }

            if (!csrfToken) {
                showToast('CSRF token is missing. Please refresh the page.', 'error');
                return;
            }

            const addBtn = activeSectionAddButton;
            const originalLabel = addBtn?.innerHTML || '';
            const originalSubmitLabel = sectionPositionSubmitBtn?.innerHTML || '';

            if (addBtn) {
                addBtn.setAttribute('disabled', 'disabled');
                addBtn.classList.add('opacity-70', 'cursor-not-allowed');
                addBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-[10px]"></i> Saving';
            }

            if (sectionPositionSubmitBtn) {
                sectionPositionSubmitBtn.setAttribute('disabled', 'disabled');
                sectionPositionSubmitBtn.classList.add('opacity-70', 'cursor-not-allowed');
                sectionPositionSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }

            try {
                const url = sectionPositionStoreUrlTemplate.replace('__SECTION__', encodeURIComponent(sectionId));
                const formData = new FormData();
                formData.append('position_title', positionTitle);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && payload?.errors) {
                        const firstMessage = String(Object.values(payload.errors).flat()[0] || 'Unable to add position.');
                        if (sectionPositionError) {
                            sectionPositionError.textContent = firstMessage;
                            sectionPositionError.classList.remove('hidden');
                        }
                        sectionPositionInput?.classList.add('border-red-300', 'ring-2', 'ring-red-100', 'focus:border-red-400', 'focus:ring-red-100');
                        return;
                    }

                    const fallback = payload?.message || 'Unable to add position.';
                    if (sectionPositionError) {
                        sectionPositionError.textContent = fallback;
                        sectionPositionError.classList.remove('hidden');
                    }
                    showToast(fallback, 'error');
                    return;
                }

                const sectionEntry = activeDivisionSections.find((item) => String(item.id) === sectionId);
                if (sectionEntry) {
                    const currentPositions = Array.isArray(sectionEntry.positions) ? sectionEntry.positions : [];
                    const normalizedCurrent = new Set(currentPositions.map((title) => normalizePositionTitle(title)));
                    if (!normalizedCurrent.has(normalizePositionTitle(positionTitle))) {
                        sectionEntry.positions = [...currentPositions, positionTitle].sort((a, b) => a.localeCompare(b));
                    }
                }

                toggleSectionPositionModal(false);
                showToast(payload?.message || 'Position added to section.', 'success');

                if (activeDivisionDetailsTrigger) {
                    activeDivisionDetailsTrigger.dataset.divisionSections = JSON.stringify(activeDivisionSections);
                    openDivisionOverviewDetails(activeDivisionDetailsTrigger);
                }
            } catch (error) {
                showToast('Unable to add position right now.', 'error');
            } finally {
                if (addBtn) {
                    addBtn.removeAttribute('disabled');
                    addBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                    addBtn.innerHTML = originalLabel;
                }

                if (sectionPositionSubmitBtn) {
                    sectionPositionSubmitBtn.removeAttribute('disabled');
                    sectionPositionSubmitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                    sectionPositionSubmitBtn.innerHTML = originalSubmitLabel;
                }
            }
        });

        viewModal?.addEventListener('click', (event) => {
            const closeEl = event.target instanceof Element ? event.target.closest('[data-close-employee-view-modal]') : null;
            if (!closeEl) return;
            event.preventDefault();
            toggleViewModal(false);
        });

        divisionOverviewModal?.addEventListener('click', (event) => {
            const closeEl = event.target instanceof Element ? event.target.closest('[data-close-division-overview-modal]') : null;
            if (!closeEl) return;
            event.preventDefault();
            toggleDivisionOverviewModal(false);
        });

        createDivisionModal?.addEventListener('click', (event) => {
            const closeEl = event.target instanceof Element ? event.target.closest('[data-close-create-division-modal]') : null;
            if (!closeEl) return;
            event.preventDefault();
            toggleCreateDivisionModal(false);
        });

        addInitialSectionRowBtn?.addEventListener('click', () => {
            addSectionInputRow('');
        });

        initialSectionsRows?.addEventListener('click', (event) => {
            const removeBtn = event.target instanceof Element ? event.target.closest('[data-remove-section-row]') : null;
            if (!removeBtn) return;

            const row = removeBtn.closest('[data-section-row]');
            if (!row) return;

            const rowCount = initialSectionsRows.querySelectorAll('[data-section-row]').length;
            if (rowCount <= 1) {
                const input = row.querySelector('input[name="sections[]"]');
                if (input) {
                    input.value = '';
                    input.focus();
                }
                return;
            }

            row.remove();
        });

        createDivisionForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!csrfToken) {
                return;
            }

            if (!createDivisionSubmitBtn) {
                return;
            }

            const formData = new FormData(createDivisionForm);
            createDivisionError?.classList.add('hidden');
            createDivisionError.textContent = '';

            const originalBtnHtml = createDivisionSubmitBtn.innerHTML;
            createDivisionSubmitBtn.disabled = true;
            createDivisionSubmitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            createDivisionSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            try {
                const response = await fetch(`{{ route('divisions.store') }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const payload = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && payload?.errors) {
                        const messages = Object.values(payload.errors).flat().filter(Boolean);
                        const firstMessage = messages.length ? String(messages[0]) : 'Please fix the highlighted fields and try again.';
                        if (createDivisionError) {
                            createDivisionError.textContent = firstMessage;
                            createDivisionError.classList.remove('hidden');
                        }
                    } else {
                        throw new Error(payload?.message || 'Unable to create division right now.');
                    }
                    return;
                }

                showToast(payload?.message || 'Division created successfully.', 'success');
                toggleCreateDivisionModal(false);
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } catch (error) {
                const fallback = error instanceof Error ? error.message : 'Unable to create division right now.';
                if (createDivisionError) {
                    createDivisionError.textContent = fallback;
                    createDivisionError.classList.remove('hidden');
                }
            } finally {
                createDivisionSubmitBtn.disabled = false;
                createDivisionSubmitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                createDivisionSubmitBtn.innerHTML = originalBtnHtml;
            }
        });

        detailsActionBtn?.addEventListener('click', () => setModalView('details'));
        editActionBtn?.addEventListener('click', () => setModalView('edit'));

        editDivision?.addEventListener('change', () => {
            renderSectionsForModal(editDivision.value || '', '');
            renderPositionsForModal('', '');
        });

        editSectionField?.addEventListener('change', () => {
            renderPositionsForModal(editSectionField.value || '', '');
        });

        editAccount?.addEventListener('change', () => {
            const hasLinkedAccount = Boolean(activeModalButton?.dataset.accountId);
            syncCreateAccountVisibility(hasLinkedAccount);
        });

        createAccountToggle?.addEventListener('change', () => {
            createAccountFields?.classList.toggle('hidden', !createAccountToggle.checked);
            if (!createAccountToggle.checked) {
                if (newAccountUsername) newAccountUsername.value = '';
                if (newAccountRole) newAccountRole.value = 'employee';
                if (newAccountPassword) newAccountPassword.value = '';
                clearFieldError(newAccountUsername);
                clearFieldError(newAccountRole);
                clearFieldError(newAccountPassword);
            } else {
                validateClientField('new_account_username');
                validateClientField('new_account_role');
                validateClientField('new_account_password');
            }
        });

        editProfileImageInput?.addEventListener('change', () => {
            const file = editProfileImageInput.files?.[0] || null;
            if (editProfileImageName) {
                editProfileImageName.textContent = file ? file.name : 'No new file selected';
            }

            if (!file || !editAvatar) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const result = event.target?.result;
                if (typeof result === 'string') {
                    editAvatar.src = result;
                }
            };
            reader.readAsDataURL(file);
        });

        editForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!modalUpdateUrl) {
                showToast('Update endpoint is unavailable.', 'error');
                return;
            }

            if (!validateClientForm()) {
                showToast('Please correct the highlighted fields before saving.', 'error');
                return;
            }

            const formData = new FormData(editForm);
            const normalizedContact = sanitizePhoneLocal(editContact?.value || '');
            formData.set('contact_no', normalizedContact ? `+63${normalizedContact}` : '');
            editError?.classList.add('hidden');
            clearModalValidation();
            saveBtn?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(modalUpdateUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: (() => {
                        formData.append('_method', 'PUT');
                        return formData;
                    })(),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    if (data?.errors) {
                        applyValidationErrors(data.errors);
                    }
                    throw new Error(data?.message || (data?.errors ? 'Please review highlighted fields.' : `Update failed (status ${response.status}).`));
                }

                const updated = data?.employee || {};
                const updatedName = buildLegalName(updated.first_name, updated.middle_name, updated.last_name, updated.suffix);
                viewName.textContent = updatedName || viewName.textContent;
                viewEmail.textContent = updated.email || 'Not provided';
                viewContact.textContent = formatPhoneForView(updated.contact_no || 'Not provided');
                viewPosition.textContent = activeModalButton?.dataset.employeePosition || 'Unassigned';
                viewSection.textContent = activeModalButton?.dataset.employeeSection || 'Unassigned';
                viewDivision.textContent = activeModalButton?.dataset.employeeDivision || '—';
                viewAccount.textContent = activeModalButton?.dataset.employeeAccount || 'None';

                if (activeModalButton) {
                    const selectedPositionLabel = editPosition?.selectedOptions?.[0]?.textContent || 'Unassigned';
                    const selectedSectionLabel = editSectionField?.selectedOptions?.[0]?.textContent || 'Unassigned';
                    const selectedDivisionLabel = editDivision?.selectedOptions?.[0]?.textContent || '—';
                    const selectedAccountLabel = editAccount?.selectedOptions?.[0]?.textContent || 'None';

                    activeModalButton.dataset.employeeName = updatedName || activeModalButton.dataset.employeeName || '';
                    activeModalButton.dataset.employeeCode = updated.employee_id || activeModalButton.dataset.employeeCode || '';
                    activeModalButton.dataset.firstName = updated.first_name || '';
                    activeModalButton.dataset.middleName = updated.middle_name || '';
                    activeModalButton.dataset.lastName = updated.last_name || '';
                    activeModalButton.dataset.suffix = updated.suffix || '';
                    activeModalButton.dataset.dateOfBirth = updated.date_of_birth || '';
                    activeModalButton.dataset.gender = updated.gender || '';
                    activeModalButton.dataset.maritalStatus = updated.marital_status || '';
                    activeModalButton.dataset.positionId = updated.position_id || '';
                    activeModalButton.dataset.sectionId = updated.section_id || '';
                    activeModalButton.dataset.divisionId = updated.division_id || '';
                    activeModalButton.dataset.accountId = updated.account_id || '';
                    activeModalButton.dataset.employeePosition = selectedPositionLabel;
                    activeModalButton.dataset.employeeSection = selectedSectionLabel;
                    activeModalButton.dataset.employeeDivision = selectedDivisionLabel;
                    activeModalButton.dataset.employeeAccount = selectedAccountLabel === 'Unassigned' ? 'None' : selectedAccountLabel;
                    activeModalButton.dataset.accountLabel = selectedAccountLabel === 'Unassigned' ? '' : selectedAccountLabel;
                    activeModalButton.dataset.profileImg = updated.profile_img || activeModalButton.dataset.profileImg || '';
                    activeModalButton.dataset.employeeEmail = updated.email || 'Not provided';
                    activeModalButton.dataset.emailRaw = updated.email || '';
                    activeModalButton.dataset.employeeContact = formatPhoneForView(updated.contact_no || 'Not provided');
                    activeModalButton.dataset.contactRaw = updated.contact_no || '';

                    if (deleteConfirmName) {
                        deleteConfirmName.textContent = updatedName || activeModalButton.dataset.employeeName || 'this employee';
                    }

                    viewPosition.textContent = activeModalButton.dataset.employeePosition || 'Unassigned';
                    viewSection.textContent = activeModalButton.dataset.employeeSection || 'Unassigned';
                    viewDivision.textContent = activeModalButton.dataset.employeeDivision || '—';
                    viewAccount.textContent = activeModalButton.dataset.employeeAccount || 'None';

                    const nowHasLinkedAccount = Boolean(activeModalButton.dataset.accountId);
                    syncCreateAccountVisibility(nowHasLinkedAccount);
                    resetCreateAccountFields();

                    if (editAvatar) {
                        editAvatar.src = buildProfileImageUrl(activeModalButton.dataset.profileImg || '');
                    }

                    const row = activeModalButton.closest('tr');
                    if (row) {
                        const nameCell = row.querySelector('td:nth-child(1) .font-semibold');
                        const emailCell = row.querySelector('td:nth-child(2) .text-sm');
                        if (nameCell && updatedName) nameCell.textContent = updatedName;
                        if (emailCell) emailCell.textContent = updated.email || 'N/A';
                    }
                }

                showToast(data?.message || 'Employee updated successfully.');
                setModalView('details');
                await fetchEmployees(buildUrlFromForm(), false);
            } catch (err) {
                console.error('Failed to update employee in modal:', err);
                if (editError) {
                    editError.textContent = err.message || 'Unable to save employee changes.';
                    editError.classList.remove('hidden');
                }
                showToast(err.message || 'Unable to save employee changes.', 'error');
            } finally {
                saveBtn?.removeAttribute('disabled');
            }
        });

        window.addEventListener('popstate', () => {
            applyUrlToFilters(window.location.href);
            fetchEmployees(window.location.href, false);
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && deleteConfirmModal && !deleteConfirmModal.classList.contains('hidden')) {
                pendingDelete = false;
                toggleDeleteConfirm(false);
                return;
            }

            if (event.key === 'Escape' && sectionPositionModal && !sectionPositionModal.classList.contains('hidden')) {
                toggleSectionPositionModal(false);
                return;
            }

            if (event.key === 'Escape' && viewModal && !viewModal.classList.contains('hidden')) {
                toggleViewModal(false);
            }
        });

        viewDeleteBtn?.addEventListener('click', () => {
            if (!modalDeleteUrl) {
                showToast('Delete endpoint is unavailable.', 'error');
                return;
            }

            pendingDelete = true;
            toggleDeleteConfirm(true);
        });

        deleteConfirmModal?.addEventListener('click', (event) => {
            const closeEl = event.target instanceof Element ? event.target.closest('[data-close-employee-delete-confirm]') : null;
            if (!closeEl) return;
            event.preventDefault();
            pendingDelete = false;
            toggleDeleteConfirm(false);
        });

        deleteConfirmBtn?.addEventListener('click', async () => {
            if (!modalDeleteUrl || !pendingDelete) return;

            deleteConfirmBtn.setAttribute('disabled', 'disabled');
            try {
                const response = await fetch(modalDeleteUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ _method: 'DELETE' }),
                });

                if (!response.ok) {
                    throw new Error(`Delete failed (status ${response.status})`);
                }

                const data = await response.json().catch(() => ({}));
                toggleDeleteConfirm(false);
                toggleViewModal(false);
                await fetchEmployees(buildUrlFromForm(), false);
                showToast(data?.message || 'Employee record removed.');
            } catch (err) {
                console.error('Failed to delete employee:', err);
                showToast(err.message || 'Failed to delete employee.', 'error');
            } finally {
                pendingDelete = false;
                deleteConfirmBtn.removeAttribute('disabled');
            }
        });

        bindPaginationLinks();

        pdfBtn?.addEventListener('click', function() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = '{{ route("employees.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
            window.open(url, '_blank');
        });

        excelBtn?.addEventListener('click', function() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = '{{ route("employees.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        });
    })();
</script>
@endpush