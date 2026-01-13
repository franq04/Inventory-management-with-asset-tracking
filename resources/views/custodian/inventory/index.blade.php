@extends('layouts.app')

@section('title', 'Inventory Assignment')

@section('content')
<div class="space-y-8 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Inventory &amp; Property Assignment</h2>
            <p class="mt-1 text-gray-500">Finalize accepted items by creating PQS records and tracking custodial documents.</p>
        </div>
    </div>

    {{-- Enhanced Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Total Items Accepted', 'value' => $stats['total'], 'icon' => 'fa-boxes-stacked', 'color' => 'blue'],
                ['label' => 'Items with ICS', 'value' => $stats['withIcs'], 'icon' => 'fa-file-signature', 'color' => 'emerald'],
                ['label' => 'Items with PAR', 'value' => $stats['withPar'], 'icon' => 'fa-file-contract', 'color' => 'purple'],
                ['label' => 'Awaiting PQS Record', 'value' => $stats['unassigned'], 'icon' => 'fa-hourglass-half', 'color' => 'amber'],
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
                <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Main Content Card with Filters and Table --}}
    <div class="bg-white mt-8 rounded-2xl shadow-lg">
        <div class="p-6">
            <form id="inventoryFilters" class="space-y-4">
                {{-- Top row: Search and Status --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                    <div class="relative lg:col-span-1">
                        <label for="inventorySearch" class="text-xs font-semibold text-gray-500">Search</label>
                        <i class="fas fa-search absolute left-4 top-1/2 mt-2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="inventorySearch" placeholder="Search item, IA number, or PO number..." class="w-full mt-1 rounded-xl border-gray-200 pl-11 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" />
                    </div>
                    <div>
                        <label for="inventoryState" class="text-xs font-semibold text-gray-500">Status</label>
                        <select id="inventoryState" class="w-full mt-1 rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                            <option value="all">All Items</option>
                            <option value="pending" selected>Ready for PQS</option>
                            <option value="recorded">Recorded in PQS</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="text-xs font-semibold text-gray-500">Date Range</label>
                        <div class="flex gap-2 mt-1 items-center">
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="flex-1 rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="flex-1 rounded-xl border-gray-200 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                            <button type="button" id="inventoryApplyDate" class="ml-2 inline-flex items-center gap-2 rounded-xl border border-[#1a3a2d] bg-[#1a3a2d] text-white px-4 py-2 text-sm font-semibold hover:opacity-95">Apply</button>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Click Apply to filter by the selected date range.</p>
                    </div>
                </div>
                {{-- Bottom row: Counts and Exports --}}
                 <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-4 text-xs font-semibold">
                         <span class="inline-flex items-center gap-2 text-amber-700">
                            <i class="fas fa-clock"></i>
                            <span id="inventoryPendingCount">0</span> Pending
                        </span>
                        <span class="inline-flex items-center gap-2 text-emerald-700">
                            <i class="fas fa-check-circle"></i>
                            <span id="inventoryRecordedCount">0</span> Recorded
                        </span>
                    </div>
                     <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                            <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                        </button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-100">
                            <i class="fas fa-file-excel text-green-600"></i> Export Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50/50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Item Description</th>
                        <th class="px-6 py-3 text-left">Category</th>
                        <th class="px-6 py-3 text-left">Sub-category</th>
                        <th class="px-6 py-3 text-right">Qty</th>
                        <th class="px-6 py-3 text-right">Unit Cost</th>
                        <th class="px-6 py-3 text-right">Total Cost</th>
                        <th class="px-6 py-3 text-left">Source</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="inventoryItemsBody" class="divide-y divide-gray-100">
                    {{-- Initial Loading State --}}
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                            <i class="fas fa-spinner fa-pulse text-3xl text-gray-300 mb-3"></i>
                            <p class="font-medium">Loading accepted items...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('custodian.inventory.partials._modals')
@endsection

@push('scripts')
@php
    $categoriesData = $categories->map(function ($parent) {
        return [
            'id' => $parent->cat_id,
            'name' => $parent->cat_name,
            'children' => $parent->children->map(function ($child) {
                return [
                    'id' => $child->cat_id,
                    'name' => $child->cat_name,
                ];
            })->values(),
        ];
    })->values();
@endphp
<script>
    window.inventoryAssignmentConfig = {
        routes: {
            items: @json(route('custodian.inventory.items')),
            show: @json(route('custodian.inventory.show', ['inspection_report_item' => '__ID__'])),
            store: @json(route('custodian.inventory.store', ['inspection_report_item' => '__ID__'])),
        },
        categories: @json($categoriesData),
    };
</script>
@vite('resources/js/inventory-assignment.js')
@endpush