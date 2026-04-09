@extends('layouts.app')

@section('title', 'Asset Movement Report')

@section('content')
<div class="space-y-6 animate-card">
    <div class="rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Employee Tracking
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Asset Movement Report</h2>
                <p class="text-sm text-white/75">Monitor relocation and custody changes across all property records.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 lg:min-w-[620px]">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Total Moves</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($stats['total'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Today</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($stats['today'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Transfers</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($stats['transfer'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Maintenance</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($stats['maintenance'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-[0_14px_40px_-28px_rgba(15,60,45,0.45)]">
        @php
            $searchValue = old('search', request('search'));
            $movementTypeValue = old('movement_type', request('movement_type'));
            $divisionValue = old('division_id', request('division_id'));
            $sectionValue = old('section_id', request('section_id'));
            $locationValue = old('location_id', request('location_id'));
            $custodianValue = old('custodian_employee_id', request('custodian_employee_id'));
            $batchReferenceValue = old('batch_reference', request('batch_reference'));
            $dateFromValue = old('date_from', request('date_from'));
            $dateToValue = old('date_to', request('date_to'));
            $showAdvancedFilters = ($batchReferenceValue !== null && trim((string) $batchReferenceValue) !== '') || $errors->has('batch_reference');
        @endphp

        <form method="GET" action="{{ route('pqs.movements.report') }}" class="space-y-5">
            <div class="space-y-5">
                <div class="relative">
                    <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Search</label>
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 mt-2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                    <input id="search" name="search" type="text" value="{{ $searchValue }}" placeholder="Search property no., article, movement remarks, reason code..." class="mt-1 h-11 w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-3 text-sm shadow-inner shadow-emerald-950/5 transition focus:border-[#1a3a2d] focus:bg-white focus:ring-4 focus:ring-[#1a3a2d]/10 {{ $errors->has('search') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                    @error('search')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="movement_type" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Movement Type</label>
                        <select id="movement_type" name="movement_type" class="mt-1 h-11 w-full rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50 {{ $errors->has('movement_type') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                            <option value="">All Types</option>
                            @foreach ($movementTypes as $type)
                                <option value="{{ $type }}" @selected((string) $movementTypeValue === (string) $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                        @error('movement_type')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="movementDivisionDropdown" class="relative">
                        <label for="division_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Division</label>
                        <button id="movementDivisionToggle" type="button" class="mt-1 flex h-11 w-full items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm transition focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50">
                            <span id="movementDivisionLabel" class="truncate">All Divisions</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#2d5a4a]/45"></i>
                        </button>
                        <div id="movementDivisionPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                            <input id="movementDivisionSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search division...">
                            <ul id="movementDivisionList" class="mt-2 max-h-[210px] overflow-y-auto rounded-lg border border-gray-100"></ul>
                        </div>
                        <select id="division_id" name="division_id" class="hidden {{ $errors->has('division_id') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                            <option value="">All Divisions</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->division_id }}" @selected((string) $divisionValue === (string) $division->division_id)>{{ $division->division_name }}</option>
                            @endforeach
                        </select>
                        @error('division_id')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="movementSectionDropdown" class="relative">
                        <label for="section_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Section</label>
                        <button id="movementSectionToggle" type="button" class="mt-1 flex h-11 w-full items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm transition focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50">
                            <span id="movementSectionLabel" class="truncate">All Sections</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#2d5a4a]/45"></i>
                        </button>
                        <div id="movementSectionPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                            <input id="movementSectionSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search section...">
                            <ul id="movementSectionList" class="mt-2 max-h-[210px] overflow-y-auto rounded-lg border border-gray-100"></ul>
                        </div>
                        <select id="section_id" name="section_id" class="hidden {{ $errors->has('section_id') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                            <option value="">All Sections</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->section_id }}" @selected((string) $sectionValue === (string) $section->section_id) data-division-id="{{ $section->division_id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="movementLocationDropdown" class="relative">
                        <label for="location_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Location</label>
                        <button id="movementLocationToggle" type="button" class="mt-1 flex h-11 w-full items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm transition focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50">
                            <span id="movementLocationLabel" class="truncate">All Locations</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#2d5a4a]/45"></i>
                        </button>
                        <div id="movementLocationPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                            <input id="movementLocationSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search location...">
                            <ul id="movementLocationList" class="mt-2 max-h-[210px] overflow-y-auto rounded-lg border border-gray-100"></ul>
                        </div>
                        <select id="location_id" name="location_id" class="hidden {{ $errors->has('location_id') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                            <option value="">All Locations</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->location_id }}" @selected((string) $locationValue === (string) $location->location_id)>{{ $location->location_name }}</option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="movementEmployeeDropdown" class="relative">
                        <label for="custodian_employee_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Employee</label>
                        <button id="movementEmployeeToggle" type="button" class="mt-1 flex h-11 w-full items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm text-gray-700 shadow-sm transition focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50">
                            <span id="movementEmployeeLabel" class="truncate">All Employees</span>
                            <i class="fas fa-chevron-down text-[11px] text-[#2d5a4a]/45"></i>
                        </button>
                        <div id="movementEmployeePanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                            <input id="movementEmployeeSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search employee...">
                            <ul id="movementEmployeeList" class="mt-2 max-h-[210px] overflow-y-auto rounded-lg border border-gray-100"></ul>
                        </div>
                        <select id="custodian_employee_id" name="custodian_employee_id" class="hidden {{ $errors->has('custodian_employee_id') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                            <option value="">All Employees</option>
                            @foreach ($custodians as $custodian)
                                <option value="{{ $custodian->employee_id }}" @selected((string) $custodianValue === (string) $custodian->employee_id)>{{ trim($custodian->first_name . ' ' . $custodian->last_name) }}</option>
                            @endforeach
                        </select>
                        @error('custodian_employee_id')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_from" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Date From</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $dateFromValue }}" class="mt-1 h-11 w-full rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50 {{ $errors->has('date_from') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                        @error('date_from')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_to" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Date To</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $dateToValue }}" class="mt-1 h-11 w-full rounded-2xl border border-emerald-950/10 bg-white px-3 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50 {{ $errors->has('date_to') ? 'border-rose-300 bg-rose-50/30' : '' }}">
                        @error('date_to')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div id="movementAdvancedFilters" class="{{ $showAdvancedFilters ? '' : 'hidden' }} rounded-2xl border border-gray-200 bg-gray-50/70 p-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="batch_reference" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Batch Reference</label>
                        <input id="batch_reference" name="batch_reference" type="text" value="{{ $batchReferenceValue }}" placeholder="Turnover batch UUID" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('batch_reference') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                        <p class="mt-1 text-[11px] text-gray-500">Optional: use only when tracing a specific turnover batch.</p>
                        @error('batch_reference')
                            <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 border-t border-gray-100 pt-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex w-full items-center justify-between lg:w-auto lg:min-w-[220px] lg:justify-start">
                    <span class="text-sm font-medium text-gray-500"><span>{{ number_format($movements->total()) }}</span> movement record(s) found</span>
                    <a href="{{ route('pqs.movements.report') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white lg:hidden" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>

                <div class="flex w-full flex-col gap-3 lg:w-auto lg:items-end">
                    <a href="{{ route('pqs.movements.report') }}" class="hidden lg:inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>

                    <div class="flex w-full flex-col gap-3 xl:flex-row xl:items-center xl:gap-3">
                        <div class="flex w-full flex-wrap items-stretch gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/45 p-1.5 sm:items-center xl:w-auto">
                            <a href="{{ route('pqs.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
                                <i class="fas fa-box"></i> PQS Registry
                            </a>
                            <button type="button" data-open-location-registry-modal class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto">
                                <i class="fas fa-plus"></i> Add Location
                            </button>
                            <button id="movementAdvancedToggle" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition-all hover:border-emerald-300 hover:bg-emerald-100 sm:w-auto" aria-controls="movementAdvancedFilters" aria-expanded="{{ $showAdvancedFilters ? 'true' : 'false' }}">
                                <i class="fas fa-sliders"></i>
                                <span>Advanced Filters</span>
                                <i id="movementAdvancedChevron" class="fas fa-chevron-down text-[11px] transition-transform {{ $showAdvancedFilters ? 'rotate-180' : '' }}"></i>
                            </button>
                        </div>

                        <div class="hidden h-8 w-px bg-gray-200 xl:block"></div>

                        <div class="flex w-full flex-wrap items-stretch gap-2 rounded-2xl border border-gray-200 bg-gray-50/65 p-1.5 sm:items-center xl:w-auto">
                            <a href="{{ route('pqs.movements.print.pdf', request()->query()) }}" target="_blank" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d] sm:w-auto">
                                <i class="fas fa-file-pdf text-rose-600"></i> Print PDF
                            </a>
                            <a href="{{ route('pqs.movements.export.excel', request()->query()) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d] sm:w-auto">
                                <i class="fas fa-file-excel text-emerald-600"></i> Export Excel
                            </a>
                            <a href="{{ route('pqs.movements.export.csv', request()->query()) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d] sm:w-auto">
                                <i class="fas fa-file-csv text-sky-600"></i> Export CSV
                            </a>
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1a3a2d] px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#214f3d] sm:w-auto">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-[#f5f8f6] text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Property</th>
                        <th class="px-4 py-3">Movement</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Recorded By</th>
                        <th class="px-4 py-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white text-sm text-gray-700">
                    @forelse ($movements as $movement)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ optional($movement->effective_at)->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ optional($movement->effective_at)->format('h:i A') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $movement->property_no }}</div>
                                <div class="text-xs text-gray-500">{{ $movement->property?->article ?: 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ ucwords(str_replace('_', ' ', (string) $movement->movement_type)) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs leading-relaxed text-gray-700">
                                <div><span class="font-semibold text-gray-600">From:</span> {{ $movement->from_endpoint_display ?: 'Unspecified' }}</div>
                                <div><span class="font-semibold text-gray-600">To:</span> {{ $movement->to_endpoint_display ?: 'Unspecified' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs leading-relaxed text-gray-700">
                                <div><span class="font-semibold text-gray-600">From:</span> {{ $movement->from_custodian_display ?: 'Unspecified' }}</div>
                                <div><span class="font-semibold text-gray-600">To:</span> {{ $movement->to_custodian_display ?: 'Unspecified' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $movement->movedByAccount?->username ?: 'System' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $movement->remarks ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-sm text-gray-500">
                                <i class="fas fa-route text-4xl text-gray-300"></i>
                                <p class="mt-3 font-medium">No movement records matched your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $movements->onEachSide(1)->links('vendor.pagination.procurement') }}
            </div>
        @endif
    </div>
</div>

@include('inventory.locations.partials._add-modal', [
    'locationRegistryStoreRoute' => route('physical_locations.store'),
    'locationRegistryParents' => $locations,
])
@endsection

@push('scripts')
<script>
(() => {
    const advancedToggle = document.getElementById('movementAdvancedToggle');
    const advancedFilters = document.getElementById('movementAdvancedFilters');
    const advancedChevron = document.getElementById('movementAdvancedChevron');

    if (advancedToggle && advancedFilters && advancedChevron) {
        advancedToggle.addEventListener('click', () => {
            const willOpen = advancedFilters.classList.contains('hidden');
            advancedFilters.classList.toggle('hidden', !willOpen);
            advancedChevron.classList.toggle('rotate-180', willOpen);
            advancedToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    }

    const divisionSelect = document.getElementById('division_id');
    const sectionSelect = document.getElementById('section_id');
    const locationSelect = document.getElementById('location_id');
    const employeeSelect = document.getElementById('custodian_employee_id');

    const controls = [
        {
            select: divisionSelect,
            toggle: document.getElementById('movementDivisionToggle'),
            panel: document.getElementById('movementDivisionPanel'),
            search: document.getElementById('movementDivisionSearch'),
            list: document.getElementById('movementDivisionList'),
            label: document.getElementById('movementDivisionLabel'),
        },
        {
            select: sectionSelect,
            toggle: document.getElementById('movementSectionToggle'),
            panel: document.getElementById('movementSectionPanel'),
            search: document.getElementById('movementSectionSearch'),
            list: document.getElementById('movementSectionList'),
            label: document.getElementById('movementSectionLabel'),
        },
        {
            select: locationSelect,
            toggle: document.getElementById('movementLocationToggle'),
            panel: document.getElementById('movementLocationPanel'),
            search: document.getElementById('movementLocationSearch'),
            list: document.getElementById('movementLocationList'),
            label: document.getElementById('movementLocationLabel'),
        },
        {
            select: employeeSelect,
            toggle: document.getElementById('movementEmployeeToggle'),
            panel: document.getElementById('movementEmployeePanel'),
            search: document.getElementById('movementEmployeeSearch'),
            list: document.getElementById('movementEmployeeList'),
            label: document.getElementById('movementEmployeeLabel'),
        },
    ];

    if (controls.some((control) => !control.select || !control.toggle || !control.panel || !control.search || !control.list || !control.label)) {
        return;
    }

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const closeAllPanels = () => {
        controls.forEach((control) => control.panel.classList.add('hidden'));
    };

    const setLabel = (control) => {
        const selected = control.select.selectedOptions[0];
        const text = selected ? String(selected.textContent || '').trim() : '';
        control.label.textContent = text || 'All';
    };

    const syncSections = () => {
        const selectedDivision = divisionSelect.value;

        Array.from(sectionSelect.options).forEach((option) => {
            const optionDivision = option.dataset.divisionId || '';
            const keepVisible = option.value === '' || selectedDivision === '' || optionDivision === selectedDivision;
            option.hidden = !keepVisible;

            if (!keepVisible && option.selected) {
                sectionSelect.value = '';
            }
        });
    };

    const renderOptions = (control) => {
        const query = String(control.search.value || '').trim().toLowerCase();
        const options = Array.from(control.select.options).filter((option) => {
            if (option.hidden) {
                return false;
            }

            const label = String(option.textContent || '').toLowerCase();
            return query === '' || label.includes(query);
        });

        if (options.length === 0) {
            control.list.innerHTML = '<li class="px-3 py-2 text-xs text-gray-500">No matches found.</li>';
            return;
        }

        control.list.innerHTML = options.map((option) => {
            const isSelected = String(control.select.value) === String(option.value);
            return `
                <li>
                    <button type="button" data-value="${escapeHtml(option.value)}" class="w-full px-3 py-2 text-left text-sm ${isSelected ? 'bg-emerald-50 text-[#1a3a2d]' : 'text-gray-700 hover:bg-gray-50'}">${escapeHtml(String(option.textContent || '').trim())}</button>
                </li>
            `;
        }).join('');

        Array.from(control.list.querySelectorAll('button[data-value]')).forEach((button) => {
            button.addEventListener('click', () => {
                const value = String(button.dataset.value || '');
                control.select.value = value;
                control.select.dispatchEvent(new Event('change', { bubbles: true }));
                closeAllPanels();
            });
        });
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

    const upsertLocationOption = (location) => {
        if (!locationSelect || !location) {
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

        let option = Array.from(locationSelect.options).find((entry) => String(entry.value) === optionValue);
        if (!option) {
            option = document.createElement('option');
            option.value = optionValue;
            locationSelect.appendChild(option);
        }

        option.textContent = optionLabel || `Location ${optionValue}`;
        option.dataset.divisionId = divisionId;
        option.dataset.sectionId = sectionId;

        return true;
    };

    controls.forEach((control) => {
        control.toggle.addEventListener('click', () => {
            const willOpen = control.panel.classList.contains('hidden');
            closeAllPanels();
            if (!willOpen) {
                return;
            }
            control.panel.classList.remove('hidden');
            renderOptions(control);
            control.search.focus();
        });

        control.search.addEventListener('input', () => renderOptions(control));
        control.search.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllPanels();
            }
        });

        control.select.addEventListener('change', () => {
            if (control.select === divisionSelect) {
                syncSections();
                renderOptions(controls[1]);
                setLabel(controls[1]);
            }

            setLabel(control);
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) {
            return;
        }

        const insideControl = controls.some((control) => control.toggle.contains(target) || control.panel.contains(target));
        if (!insideControl) {
            closeAllPanels();
        }
    });

    document.addEventListener('location-registry:created', (event) => {
        const location = event?.detail?.location;
        if (!location) {
            return;
        }

        const updated = upsertLocationOption(location);
        if (!updated) {
            return;
        }

        locationSelect.value = String(location.location_id);
        locationSelect.dispatchEvent(new Event('change', { bubbles: true }));

        const locationControl = controls.find((control) => control.select === locationSelect);
        if (locationControl && !locationControl.panel.classList.contains('hidden')) {
            renderOptions(locationControl);
        }

        notifySuccess(event?.detail?.message || 'Location list updated.');
    });

    syncSections();
    controls.forEach((control) => setLabel(control));
})();
</script>
@endpush
