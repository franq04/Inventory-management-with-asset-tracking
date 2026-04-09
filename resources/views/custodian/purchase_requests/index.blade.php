@extends('layouts.app')

@section('title', 'All Requests')

@section('content')
@php
    $queueTotal = (int) $purchaseRequests->total();
    $visibleQueueCount = (int) $purchaseRequests->count();
    $visibleQueueValue = (float) $purchaseRequests->sum('total_estimated_cost');
@endphp
<div id="custodianQueuePage" class="space-y-8 transition-all duration-200 ease-out">
    <div class="animate-card rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10 overflow-hidden relative">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.18),_transparent_60%)]"></div>
        <div class="absolute -right-8 bottom-0 h-32 w-32 rounded-full border border-white/10 bg-white/5 blur-2xl"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Procurement Queue
                </div>
                <div class="space-y-1">
                    <h1 class="text-3xl font-extrabold tracking-tight text-white">{{ $pageTitle }}</h1>
                    <p class="text-sm text-white/75 md:max-w-2xl">{{ $pageDescription }}</p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[420px] lg:max-w-2xl">
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Queue Total</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($queueTotal) }}</p>
                    <p class="mt-1 text-xs text-white/65">All requests in this queue</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Visible Rows</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($visibleQueueCount) }}</p>
                    <p class="mt-1 text-xs text-white/65">Records on the current page</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Visible Value</p>
                    <p class="mt-2 text-2xl font-bold">&#8369;{{ number_format($visibleQueueValue, 2) }}</p>
                    <p class="mt-1 text-xs text-white/65">Current page total estimate</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-4 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-6 space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Queue Filters</p>
                <h2 class="mt-1 text-xl font-bold text-[#1a3a2d]">Filter and route procurement requests</h2>
                <p class="mt-1 text-sm text-gray-500">Review the queue by status, search for specific requests, and export the current view when needed.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach($tabs as $key => $label)
                    @php
                        $tabQuery = array_merge(request()->except(['page', 'tab']), ['tab' => $key]);
                        $isActive = $activeTab === $key;
                        $activeTabClasses = 'bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d]';
                        $inactiveTabClasses = 'border-gray-200 bg-[#fbfcfb] text-gray-600 hover:bg-gray-100 hover:border-gray-300';
                        $activeCountClasses = 'bg-white/20 text-white';
                        $inactiveCountClasses = 'bg-gray-200 text-gray-700 group-hover:bg-gray-300';
                        $tabStatusIds = $tabStatusMap[$key] ?? [];
                    @endphp
                    <button type="button"
                       class="po-tab inline-flex cursor-pointer items-center gap-2.5 rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200 group
                              @if($isActive) 
                                  {{ $activeTabClasses }} is-active
                              @else 
                                  {{ $inactiveTabClasses }}
                              @endif"
                       data-po-tab="{{ $key }}"
                       data-status-btn="{{ $key }}"
                       data-tab-url="{{ route($routes['index'], $tabQuery) }}"
                       data-status-ids="{{ implode(',', $tabStatusIds) }}"
                       data-inactive-class="{{ $inactiveTabClasses }}"
                       data-active-class="{{ $activeTabClasses }}">
                        
                        <span>{{ $label }}</span>

                        {{-- FIX: Conditional classes ensure text is always visible --}}
                        <span class="po-tab-count rounded-full px-2 py-0.5 text-xs font-bold 
                                   @if($isActive) 
                                       {{ $activeCountClasses }}
                                   @else 
                                       {{ $inactiveCountClasses }}
                                   @endif"
                              data-count-inactive-class="{{ $inactiveCountClasses }}"
                              data-count-active-class="{{ $activeCountClasses }}">
                            {{ $tabCounts[$key] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div class="grid w-full grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_170px_170px] xl:max-w-3xl">
                <div class="relative">
                <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                    <input type="search" id="purchaseRequestSearch" value="{{ $search ?? '' }}" class="w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-4 py-3 text-sm text-gray-700 shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10 transition" placeholder="Search PR no, requester, division, section, status..." aria-label="Search purchase requests">
                </div>
                <input type="date" id="purchaseRequestDateFrom" value="{{ $dateFrom ?? '' }}" class="rounded-2xl border border-emerald-950/10 bg-white px-3 py-3 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="Date from">
                <input type="date" id="purchaseRequestDateTo" value="{{ $dateTo ?? '' }}" class="rounded-2xl border border-emerald-950/10 bg-white px-3 py-3 text-sm shadow-sm transition focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/50" title="Date to">
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="purchaseRequestPrintPdf" data-print-url="{{ route('custodian.requests.print.pdf') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 bg-white hover:border-[#1a3a2d]/20 hover:text-[#1a3a2d] hover:bg-[#f7faf8] transition-all">
                    <i class="fas fa-file-pdf text-rose-600"></i> Print PDF
                </button>
                <button type="button" id="purchaseRequestExportExcel" data-excel-url="{{ route('custodian.requests.export.excel') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 bg-white hover:border-[#1a3a2d]/20 hover:text-[#1a3a2d] hover:bg-[#f7faf8] transition-all">
                    <i class="fas fa-file-excel text-emerald-600"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
        <div class="overflow-x-auto">
            <table id="custodianPurchaseRequestsTable" class="min-w-full text-sm">
                <thead class="bg-[#f5f8f6] text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                    <tr class="text-left">
                        <th class="px-4 py-2.5 font-semibold">PR Number</th>
                        <th class="px-4 py-2.5 font-semibold">Status</th>
                        <th class="px-4 py-2.5 font-semibold">Total Cost</th>
                        <th class="px-4 py-2.5 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseRequests as $request)
                        @php
                            $searchTokens = collect([
                                $request->pr_no,
                                $request->requester?->username,
                                $request->division?->division_name,
                                $request->section?->section_name,
                                $request->status?->status_name,
                                number_format((float) $request->total_estimated_cost, 2),
                                optional($request->created_at)->format('M d, Y h:i A'),
                            ])->filter()->implode(' ');
                        @endphp
                        <tr data-row data-status-id="{{ $request->status_id }}" data-created-at="{{ optional($request->created_at)->format('Y-m-d') }}" data-search="{{ e(strtolower($searchTokens)) }}" class="border-b border-gray-100/90 last:border-0 hover:bg-[#f6fbf8] transition-colors duration-200">
                            <td class="px-4 py-3 align-top">
                                <span class="block font-semibold text-[#1a3a2d]">{{ $request->pr_no }}</span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                {{-- NEW: Dynamic Status Colors --}}
                                @php
                                    $statusName = strtolower($request->status?->status_name ?? 'unknown');
                                    $statusColorClasses = 'bg-gray-100 text-gray-700 border-gray-200'; // Default
                                    if (in_array($statusName, ['approved', 'completed', 'ordered'])) {
                                        $statusColorClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    } elseif (in_array($statusName, ['cancelled', 'declined'])) {
                                        $statusColorClasses = 'bg-rose-50 text-rose-700 border-rose-200';
                                    } elseif (in_array($statusName, ['pending', 'for recommendation', 'for approval'])) {
                                        $statusColorClasses = 'bg-amber-50 text-amber-700 border-amber-200';
                                    } elseif (in_array($statusName, ['processing', 'under review'])) {
                                        $statusColorClasses = 'bg-sky-50 text-sky-700 border-sky-200';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold capitalize border {{ $statusColorClasses }}">
                                    <i class="fas fa-circle text-[8px]"></i>{{ $request->status?->status_name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <span class="inline-flex rounded-full bg-[#f2f7f4] px-2.5 py-1 text-xs font-semibold text-gray-800">₱{{ number_format((float) $request->total_estimated_cost, 2) }}</span>
                            </td>
                            <td class="px-4 py-3 align-top text-center">
                                <button class="js-view-custodian-pr inline-flex items-center gap-1.5 rounded-lg border border-[#1a3a2d]/10 bg-[#f4f8f5] px-3 py-2 text-[11px] font-semibold text-[#1a3a2d] transition-all hover:-translate-y-0.5 hover:border-[#1a3a2d]/20 hover:bg-[#eaf4ee] hover:shadow-md"
                                    data-show-url="{{ route($routes['show'], [$routeParam => $request]) }}"
                                    data-update-url="{{ route($routes['update'], [$routeParam => $request]) }}">
                                    <i class="fas fa-edit"></i>
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr data-static-row="empty">
                            <td colspan="4" class="px-4 py-16 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                                <p class="font-medium text-lg">No purchase requests to review.</p>
                                <p class="text-sm">New submissions will appear here automatically.</p>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="purchaseRequestsNoResults" class="hidden">
                        <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                            <i class="fas fa-search-minus text-3xl text-gray-300 mb-3"></i>
                            <p class="font-medium">No matching results.</p>
                            <p class="text-xs">Try adjusting your search terms.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
            {{ $purchaseRequests->onEachSide(1)->links('vendor.pagination.procurement') }}
        </div>
        </div>
    </div>
</div>

@include('custodian.purchase_requests.partials.review-modal', [
    'statuses' => $statuses,
    'canEditFunds' => $canEditFunds,
    'remarksRequiredStatuses' => $remarksRequiredStatuses,
    'workflowConfig' => $workflowConfig,
    'currentUserName' => $currentUserName,
    'workflowRole' => $workflowRole,
    'routes' => $routes,
    'routeParam' => $routeParam,
    'systemManagedStatuses' => $systemManagedStatuses,
])
@endsection