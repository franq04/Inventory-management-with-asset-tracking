@extends('layouts.app')

@section('content')
<div class="space-y-8">
    {{-- Enhanced Header Card --}}
    <div class="animate-card rounded-2xl bg-white shadow-lg p-6">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-extrabold text-[#1a3a2d]">BAC Procurement Queue</h1>
                <p class="text-sm text-gray-600 md:max-w-2xl">Review, approve, and manage purchase requests.</p>
            </div>
            
            {{-- Tab Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                @foreach($tabs as $key => $label)
                    @php
                        $tabQuery = array_merge(request()->except(['page', 'tab']), ['tab' => $key]);
                        $isActive = $activeTab === $key;
                    @endphp
                    <a href="{{ route('bac.requests.index', $tabQuery) }}"
                       class="inline-flex items-center gap-2.5 rounded-full border px-4 py-2 text-sm font-semibold transition-all duration-200 group
                              @if($isActive) 
                                  bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30 border-[#1a3a2d]
                              @else 
                                  border-gray-300 text-gray-600 hover:bg-gray-100 hover:border-gray-400 transform hover:scale-105
                              @endif">
                        
                        <span>{{ $label }}</span>

                        <span class="rounded-full px-2 py-0.5 text-xs font-bold 
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
    </div>

    {{-- Enhanced Table Card --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <div class="relative w-full sm:max-w-xs">
                <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="search" id="bacPurchaseRequestSearch" class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all" placeholder="Search requests..." aria-label="Search purchase requests">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="bacPurchaseRequestsTable" class="min-w-full text-sm">
                <thead class="border-b-2 border-gray-200">
                    <tr class="text-left text-gray-500 uppercase tracking-wider text-xs">
                        <th class="px-4 py-3 font-semibold">PR Number</th>
                        <th class="px-4 py-3 font-semibold">Requested By</th>
                        <th class="px-4 py-3 font-semibold hidden md:table-cell">Status</th>
                        <th class="px-4 py-3 font-semibold">Total Cost</th>
                        <th class="px-4 py-3 font-semibold text-right">Submitted</th>
                        <th class="px-4 py-3 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $pr)
                    <tr class="border-b last:border-0 hover:bg-green-50/50 transition-colors duration-200">
                        <td class="px-4 py-4 font-semibold text-[#1a3a2d]">{{ $pr->pr_no }}</td>
                        <td class="px-4 py-4 text-gray-700 font-medium">{{ $pr->requester?->username ?? 'Unknown' }}</td>
                        <td class="px-4 py-4 hidden md:table-cell">
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
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold capitalize border {{ $statusColorClasses }}">
                                <i class="fas fa-circle text-[8px]"></i>{{ $pr->status?->status_name ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 font-semibold text-gray-800">₱{{ number_format((float) $pr->total_estimated_cost, 2) }}</td>
                        <td class="px-4 py-4 text-right text-gray-500 text-xs">{{ optional($pr->created_at)->format('M d, Y h:i A') }}</td>
                        <td class="px-4 py-4 text-center">
                            <button class="js-view-bac-pr inline-flex items-center gap-2 px-4 py-2 bg-[#1a3a2d] hover:bg-[#2d5a4a] text-white text-xs font-semibold rounded-lg transition-all transform hover:scale-105"
                                data-pr-no="{{ $pr->pr_no }}"
                                data-show-url="{{ route('bac.requests.show', $pr) }}">
                                <i class="fas fa-gavel"></i>
                                Review
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr data-static-row="empty">
                        <td colspan="6" class="px-4 py-16 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                            <p class="font-medium text-lg">No purchase requests for BAC at the moment.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseRequests->hasPages())
        <div class="mt-4">{{ $purchaseRequests->links() }}</div>
        @endif
    </div>
</div>

@include('bac.purchase_requests.partials.review-modal')
@endsection

@push('scripts')
@vite('resources/js/bac-purchase-requests.js')
@endpush
