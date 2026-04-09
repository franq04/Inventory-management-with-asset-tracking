@extends('layouts.app')

@section('content')
<div class="space-y-8 animate-card">
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.18),_transparent_60%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2 max-w-2xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    BAC Oversight
                </span>
                <h1 class="text-3xl font-extrabold tracking-tight">BAC Procurement Queue</h1>
                <p class="text-sm text-white/75">Review procurement requests, move qualified entries to final approval, and maintain transparent decision records.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 lg:min-w-[620px]">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Awaiting Review</p>
                    <p class="mt-2 text-3xl font-bold" data-bac-count-card="recommended">{{ number_format($tabCounts['recommended'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">For Approval</p>
                    <p class="mt-2 text-3xl font-bold" data-bac-count-card="for_approval">{{ number_format($tabCounts['for_approval'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Approved</p>
                    <p class="mt-2 text-3xl font-bold" data-bac-count-card="approved">{{ number_format($tabCounts['approved'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">Cancelled</p>
                    <p class="mt-2 text-3xl font-bold" data-bac-count-card="cancelled">{{ number_format($tabCounts['cancelled'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div id="bacQueuePage" class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-4 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-6 space-y-5 transition-all duration-200">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="relative w-full lg:max-w-sm">
                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="search" id="bacPurchaseRequestSearch" class="w-full rounded-xl border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/20 focus:border-[#1a3a2d] transition-all" placeholder="Search by PR no., requester, or status" aria-label="Search purchase requests">
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach($tabs as $key => $label)
                    @php
                        $tabQuery = array_merge(request()->except(['page', 'tab']), ['tab' => $key]);
                        $isActive = $activeTab === $key;
                    @endphp
                    <a href="{{ route('bac.requests.index', $tabQuery) }}"
                       data-bac-tab="{{ $key }}"
                       class="inline-flex items-center gap-2.5 rounded-full border px-4 py-2 text-sm font-semibold transition-all duration-200 group
                              @if($isActive)
                                  bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d]
                              @else
                                  border-gray-300 text-gray-600 hover:bg-gray-100 hover:border-gray-400
                              @endif">
                        <span>{{ $label }}</span>
                        <span data-bac-tab-count="{{ $key }}" class="rounded-full px-2 py-0.5 text-xs font-bold
                                   @if($isActive)
                                       bg-white/20 text-white
                                   @else
                                       bg-gray-200 text-gray-700 group-hover:bg-gray-300
                                   @endif">
                            {{ $tabCounts[$key] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-emerald-950/10 bg-white shadow-[0_20px_50px_-38px_rgba(15,23,42,0.75)]">
            <div class="overflow-x-auto">
                <table id="bacPurchaseRequestsTable" class="min-w-full text-sm">
                    <thead class="bg-[#f5f8f6] text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                        <tr>
                            <th class="px-4 py-2.5">PR Number</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5">Total Cost</th>
                            <th class="px-4 py-2.5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($purchaseRequests as $pr)
                        <tr class="hover:bg-gray-50/70 transition-colors" data-pr-no="{{ $pr->pr_no }}" data-status-id="{{ (int) $pr->status_id }}">
                            <td class="px-4 py-3 font-semibold text-[#1a3a2d]">{{ $pr->pr_no }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $statusName = strtolower($pr->status?->status_name ?? 'unknown');
                                    $statusColorClasses = 'bg-gray-100 text-gray-700 border-gray-200';
                                    if (in_array($statusName, ['approved', 'completed'])) {
                                        $statusColorClasses = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    } elseif (in_array($statusName, ['cancelled', 'declined'])) {
                                        $statusColorClasses = 'bg-rose-50 text-rose-700 border-rose-200';
                                    } elseif (in_array($statusName, ['pending', 'for approval', 'recommended'])) {
                                        $statusColorClasses = 'bg-amber-50 text-amber-700 border-amber-200';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold capitalize border {{ $statusColorClasses }}">
                                    <i class="fas fa-circle text-[8px]"></i>{{ $pr->status?->status_name ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">₱{{ number_format((float) $pr->total_estimated_cost, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <button class="js-view-bac-pr inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#1a3a2d] hover:bg-[#2d5a4a] text-white text-[11px] font-semibold rounded-md transition-all"
                                    data-pr-no="{{ $pr->pr_no }}"
                                    data-show-url="{{ route('bac.requests.show', $pr) }}">
                                    <i class="fas fa-gavel"></i>
                                    Review
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr data-static-row="empty">
                            <td colspan="4" class="px-6 py-16 text-center text-gray-500">
                                <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                                <p class="font-medium text-lg">No purchase requests for BAC at the moment.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseRequests->hasPages())
            <div class="border-t border-gray-100 px-6 py-4">{{ $purchaseRequests->links() }}</div>
            @endif
        </div>
    </div>
</div>

@include('bac.purchase_requests.partials.review-modal')
@endsection

@push('scripts')
@vite('resources/js/bac-purchase-requests.js')
@endpush
