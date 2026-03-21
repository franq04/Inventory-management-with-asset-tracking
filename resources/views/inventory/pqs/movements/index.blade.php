@extends('layouts.app')

@section('title', 'Asset Movement Report')

@section('content')
<div class="space-y-6 animate-card">
    <div class="rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Custodian Tracking
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
        @endphp

        <form method="GET" action="{{ route('pqs.movements.report') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Search</label>
                    <input id="search" name="search" type="text" value="{{ $searchValue }}" placeholder="Property no., article, remarks..." class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('search') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                    @error('search')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="batch_reference" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Batch Reference</label>
                    <input id="batch_reference" name="batch_reference" type="text" value="{{ $batchReferenceValue }}" placeholder="Turnover batch UUID" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('batch_reference') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                    @error('batch_reference')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="movement_type" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Movement Type</label>
                    <select id="movement_type" name="movement_type" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('movement_type') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                        <option value="">All Types</option>
                        @foreach ($movementTypes as $type)
                            <option value="{{ $type }}" @selected((string) $movementTypeValue === (string) $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    @error('movement_type')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="division_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Division</label>
                    <select id="division_id" name="division_id" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('division_id') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                        <option value="">All Divisions</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->division_id }}" @selected((string) $divisionValue === (string) $division->division_id)>{{ $division->division_name }}</option>
                        @endforeach
                    </select>
                    @error('division_id')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="section_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Section</label>
                    <select id="section_id" name="section_id" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('section_id') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                        <option value="">All Sections</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}" @selected((string) $sectionValue === (string) $section->section_id) data-division-id="{{ $section->division_id }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                    @error('section_id')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="location_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Location</label>
                    <select id="location_id" name="location_id" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('location_id') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                        <option value="">All Locations</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->location_id }}" @selected((string) $locationValue === (string) $location->location_id)>{{ $location->location_name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="custodian_employee_id" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Custodian</label>
                    <select id="custodian_employee_id" name="custodian_employee_id" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('custodian_employee_id') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                        <option value="">All Custodians</option>
                        @foreach ($custodians as $custodian)
                            <option value="{{ $custodian->employee_id }}" @selected((string) $custodianValue === (string) $custodian->employee_id)>{{ trim($custodian->first_name . ' ' . $custodian->last_name) }}</option>
                        @endforeach
                    </select>
                    @error('custodian_employee_id')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_from" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Date From</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFromValue }}" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('date_from') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                    @error('date_from')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_to" class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Date To</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateToValue }}" class="mt-1 h-11 w-full rounded-xl border text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20 {{ $errors->has('date_to') ? 'border-rose-300 bg-rose-50/30' : 'border-gray-200' }}">
                    @error('date_to')
                        <p class="mt-1 text-xs font-medium text-rose-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-4">
                <div class="text-sm text-gray-500">{{ number_format($movements->total()) }} movement record(s) found</div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('pqs.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        <i class="fas fa-box"></i>
                        PQS Registry
                    </a>
                    <a href="{{ route('pqs.movements.report') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                        <i class="fas fa-undo"></i>
                        Reset
                    </a>
                    <a href="{{ route('pqs.movements.print.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                        <i class="fas fa-file-pdf text-rose-600"></i>
                        Print PDF
                    </a>
                    <a href="{{ route('pqs.movements.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                        <i class="fas fa-file-excel text-emerald-600"></i>
                        Export Excel
                    </a>
                    <a href="{{ route('pqs.movements.export.csv', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                        <i class="fas fa-file-csv text-sky-600"></i>
                        Export CSV
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#214f3d]">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>
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
                        <th class="px-4 py-3">Custodian</th>
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
                                <div><span class="font-semibold text-gray-600">From:</span> {{ $movement->fromLocation?->location_name ?: 'Unspecified' }}</div>
                                <div><span class="font-semibold text-gray-600">To:</span> {{ $movement->toLocation?->location_name ?: 'Unspecified' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs leading-relaxed text-gray-700">
                                <div><span class="font-semibold text-gray-600">From:</span> {{ $movement->fromCustodian?->full_name ?: 'Unspecified' }}</div>
                                <div><span class="font-semibold text-gray-600">To:</span> {{ $movement->toCustodian?->full_name ?: 'Unspecified' }}</div>
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
@endsection

@push('scripts')
<script>
(() => {
    const divisionSelect = document.getElementById('division_id');
    const sectionSelect = document.getElementById('section_id');

    if (!divisionSelect || !sectionSelect) {
        return;
    }

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

    divisionSelect.addEventListener('change', syncSections);
    syncSections();
})();
</script>
@endpush
