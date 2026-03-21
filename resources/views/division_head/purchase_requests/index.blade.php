@extends('layouts.app')

@section('title', 'Division Head - Purchase Requests')

@section('content')
<div id="divisionHeadQueuePage" class="space-y-6">
    {{-- Page Header --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#1a3a2d]">Division Head - Purchase Requests</h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Review and recommend purchase requests for your division.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Auto refresh</span>
                    <span class="text-emerald-500/70">in</span>
                    <span data-division-head-refresh-countdown>30s</span>
                    <span class="hidden sm:inline text-emerald-500/70">|</span>
                    <span class="hidden sm:inline text-emerald-600/80" data-division-head-refresh-status>updated just now</span>
                </span>
                <span class="hidden sm:inline-flex items-center gap-2 rounded-full bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white">
                    <i class="fas fa-user-tie"></i> Division Head Access
                </span>
            </div>
        </div>
    </div>

    @php
        $tabMeta = [
            'for_recommendation' => [
                'label' => 'Pending Recommendation',
                'color' => 'amber',
                'icon' => 'fa-clock',
            ],
            'recommended' => [
                'label' => 'Recommended',
                'color' => 'emerald',
                'icon' => 'fa-thumbs-up',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'color' => 'rose',
                'icon' => 'fa-ban',
            ],
        ];
    @endphp

    {{-- Status Tabs --}}
    <div class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 md:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach ($tabMeta as $key => $meta)
                <a href="?tab={{ $key }}"
                    data-division-head-tab-key="{{ $key }}"
                    class="group flex items-center justify-between rounded-xl border-2 p-4 transition-all duration-300
                           {{ $activeTab === $key 
                              ? 'border-' . $meta['color'] . '-500 bg-' . $meta['color'] . '-50 shadow-lg' 
                              : 'border-gray-200 bg-white hover:border-' . $meta['color'] . '-300 hover:bg-' . $meta['color'] . '-50/50' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full 
                                   bg-{{ $meta['color'] }}-100 text-{{ $meta['color'] }}-600">
                            <i class="fas {{ $meta['icon'] }} text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 truncate">
                                {{ $meta['label'] }}
                            </p>
                        </div>
                    </div>
                    <span data-division-head-tab-count="{{ $key }}" class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full 
                                 bg-{{ $meta['color'] }}-100 text-sm font-bold text-{{ $meta['color'] }}-700">
                        {{ $tabCounts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Purchase Requests Table --}}
    <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs sm:text-sm">
                <thead class="border-b">
                    <tr class="text-left text-gray-500 uppercase tracking-wider text-xs">
                        <th class="px-2 sm:px-4 py-3">PR Number</th>
                        <th class="px-2 sm:px-4 py-3">Requester</th>
                        <th class="px-2 sm:px-4 py-3 hidden md:table-cell">Status</th>
                        <th class="px-2 sm:px-4 py-3">Total Cost</th>
                        <th class="px-2 sm:px-4 py-3 hidden lg:table-cell">Submitted</th>
                        <th class="px-2 sm:px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $pr)
                        @php
                            $requesterName = $pr->requester?->employee 
                                ? trim($pr->requester->employee->first_name . ' ' . $pr->requester->employee->last_name)
                                : ($pr->requester?->username ?? 'Unknown');
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-2 sm:px-4 py-3 font-semibold text-xs sm:text-sm">{{ $pr->pr_no }}</td>
                            <td class="px-2 sm:px-4 py-3 text-xs sm:text-sm">{{ $requesterName }}</td>
                            <td class="px-2 sm:px-4 py-3 text-xs sm:text-sm hidden md:table-cell">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold
                                           {{ $pr->status?->status_name === 'Recommended' ? 'bg-emerald-100 text-emerald-700' : 
                                              ($pr->status?->status_name === 'Cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                    {{ $pr->status?->status_name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-xs sm:text-sm">₱{{ number_format($pr->total_estimated_cost ?? 0, 2) }}</td>
                            <td class="px-2 sm:px-4 py-3 text-xs sm:text-sm hidden lg:table-cell">{{ optional($pr->created_at)->format('M d, Y') }}</td>
                            <td class="px-2 sm:px-4 py-3 text-center">
                                <a href="{{ route('division_head.requests.show', $pr) }}" 
                                   class="inline-flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 rounded-md bg-[#1a3a2d] text-white text-xs sm:text-sm hover:bg-[#2d5a45] transition-colors">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 sm:px-4 py-8 sm:py-12 text-center text-gray-500">
                                <i class="fas fa-inbox fa-2x sm:fa-3x text-gray-300 mb-3"></i>
                                <p class="font-medium text-sm sm:text-base">No purchase requests found.</p>
                                <p class="text-xs mt-1">Requests awaiting your action will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseRequests->hasPages())
        <div class="mt-4 sm:mt-6">
            {{ $purchaseRequests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/division-head-purchase-requests.js')
@endpush
