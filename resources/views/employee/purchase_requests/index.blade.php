@extends('layouts.app')

@section('title', 'Purchase Requests')

@section('content')
{{-- Page Header --}}
<div class="animate-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-[#1a3a2d]">My Purchase Requests</h2>
        <p class="mt-1 text-gray-500">Track submissions and file new procurement requests.</p>
    </div>
    <button id="openCreateRequest" class="w-full sm:w-auto cursor-pointer bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
        <i class="fas fa-file-circle-plus"></i>
        New Purchase Request
    </button>
</div>

{{-- Purchase Requests Table --}}
@php
    $statusCounts = collect($statusSummary ?? [])->sortBy('name');
    $totalRequests = $totalRequests ?? $purchaseRequests->total();
    $approvedCount = (int) $statusCounts
        ->filter(fn ($statusData) => str_contains(strtolower($statusData['name']), 'approve'))
        ->sum('count');
    $latestRequest = $purchaseRequests->sortByDesc('created_at')->first();
    $latestSubmission = optional($latestRequest?->created_at)->format('M d, Y h:i A') ?? 'No submissions yet';

    $resolveStatusDesign = function (string $statusName) {
        $statusLower = strtolower($statusName);
        $preset = 'neutral';

        if (str_contains($statusLower, 'approve') || str_contains($statusLower, 'release') || str_contains($statusLower, 'complete')) {
            $preset = 'success';
        } elseif (str_contains($statusLower, 'cancel') || str_contains($statusLower, 'decline') || str_contains($statusLower, 'reject')) {
            $preset = 'danger';
        } elseif (str_contains($statusLower, 'recommend') || str_contains($statusLower, 'pending') || str_contains($statusLower, 'process') || str_contains($statusLower, 'review')) {
            $preset = 'warning';
        } elseif (str_contains($statusLower, 'queue') || str_contains($statusLower, 'progress') || str_contains($statusLower, 'waiting') || str_contains($statusLower, 'order')) {
            $preset = 'info';
        }

        return match ($preset) {
            'success' => [
                'badge' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                'dot' => 'bg-emerald-500',
                'count' => 'bg-emerald-100 text-emerald-700',
                'icon' => 'fa-circle-check',
                'button_inactive' => 'border border-emerald-200 bg-emerald-50/70 text-emerald-700 hover:bg-emerald-100',
                'button_active' => 'border border-emerald-600 bg-emerald-600 text-white shadow-lg shadow-emerald-500/30',
            ],
            'danger' => [
                'badge' => 'bg-rose-50 text-rose-700 border border-rose-200',
                'dot' => 'bg-rose-500',
                'count' => 'bg-rose-100 text-rose-700',
                'icon' => 'fa-triangle-exclamation',
                'button_inactive' => 'border border-rose-200 bg-rose-50/70 text-rose-700 hover:bg-rose-100',
                'button_active' => 'border border-rose-600 bg-rose-600 text-white shadow-lg shadow-rose-500/30',
            ],
            'warning' => [
                'badge' => 'bg-amber-50 text-amber-700 border border-amber-200',
                'dot' => 'bg-amber-500',
                'count' => 'bg-amber-100 text-amber-700',
                'icon' => 'fa-clock',
                'button_inactive' => 'border border-amber-200 bg-amber-50/70 text-amber-700 hover:bg-amber-100',
                'button_active' => 'border border-amber-500 bg-amber-500 text-white shadow-lg shadow-amber-400/30',
            ],
            'info' => [
                'badge' => 'bg-sky-50 text-sky-700 border border-sky-200',
                'dot' => 'bg-sky-500',
                'count' => 'bg-sky-100 text-sky-700',
                'icon' => 'fa-arrows-rotate',
                'button_inactive' => 'border border-sky-200 bg-sky-50/70 text-sky-700 hover:bg-sky-100',
                'button_active' => 'border border-sky-600 bg-sky-600 text-white shadow-lg shadow-sky-500/30',
            ],
            default => [
                'badge' => 'bg-gray-100 text-gray-700 border border-gray-200',
                'dot' => 'bg-gray-400',
                'count' => 'bg-gray-200 text-gray-700',
                'icon' => 'fa-circle',
                'button_inactive' => 'border border-gray-200 bg-gray-50/70 text-gray-600 hover:bg-gray-100',
                'button_active' => 'border border-[#1a3a2d] bg-[#1a3a2d] text-white shadow-lg shadow-[#1a3a2d]/30',
            ],
        };
    };
@endphp
<div class="mt-8 space-y-6">
    <section class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-6 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.22),_transparent_58%)]"></div>
        <div class="absolute -right-10 bottom-0 h-36 w-36 rounded-full border border-white/10 bg-white/5 blur-2xl"></div>
        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-4">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/75">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Procurement Overview
                </div>
                <div>
                    <h3 class="text-2xl font-bold tracking-tight sm:text-3xl">Purchase Requests Summary</h3>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/75 sm:text-[15px]">Monitor request activity, review submission volume, and move quickly through procurement work without leaving the current theme or workflow.</p>
                </div>
            </div>
            <div class="grid w-full gap-3 sm:grid-cols-3 lg:max-w-2xl">
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Total Requests</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($totalRequests) }}</p>
                    <p class="mt-1 text-xs text-white/65">All tracked submissions</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Approved</p>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($approvedCount) }}</p>
                    <p class="mt-1 text-xs text-white/65">Cleared procurement requests</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 px-4 py-4 backdrop-blur-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">Latest Submission</p>
                    <p class="mt-2 text-sm font-semibold leading-6">{{ $latestSubmission }}</p>
                    <p class="mt-1 text-xs text-white/65">Most recent recorded activity</p>
                </div>
            </div>
        </div>
    </section>

    <div class="rounded-[28px] border border-emerald-950/8 bg-white/95 p-4 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur sm:p-6 space-y-5">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-[#2d5a4a]/70">Request Directory</p>
                <h3 class="mt-1 text-xl font-bold text-[#1a3a2d]">Filter and review requests</h3>
                <p class="mt-1 text-sm text-gray-500">Search instantly, narrow by status, and scan the list with clearer spacing and stronger contrast.</p>
            </div>
            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-end xl:w-auto">
                <div class="relative w-full sm:w-80">
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                    <input type="search" id="employeePrSearch" class="w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-4 py-3 text-sm text-gray-700 shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10 transition" placeholder="Search by PR number, purpose, or keyword..." autocomplete="off">
                </div>
                <button type="button" id="employeePrResetFilters" class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-gray-200 px-4 py-3 text-xs font-semibold text-gray-600 bg-white hover:border-[#1a3a2d]/20 hover:text-[#1a3a2d] hover:bg-[#f7faf8] transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-rotate-right text-sm"></i>Reset
                </button>
            </div>
        </div>
        <div class="rounded-[24px] border border-gray-100 bg-gradient-to-b from-[#fcfdfc] to-white p-4 sm:p-5">
            <div class="flex flex-wrap items-center gap-2" id="employeePrStatusTabs">
                @php
                    $allDesign = $resolveStatusDesign('All');
                @endphp
                <button type="button"
                    class="employee-status-btn group inline-flex cursor-pointer items-center gap-2 rounded-full px-4 py-2.5 text-xs font-semibold transition {{ $allDesign['button_inactive'] }}"
                    data-status-btn=""
                    data-inactive-class="{{ $allDesign['button_inactive'] }}"
                    data-active-class="{{ $allDesign['button_active'] }}">
                    <span class="flex h-2.5 w-2.5 rounded-full {{ $allDesign['dot'] }} group-[.is-active]:bg-white"></span>
                    <span class="uppercase tracking-wide group-[.is-active]:text-white">All</span>
                    <span class="inline-flex min-w-[28px] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-bold {{ $allDesign['count'] }} group-[.is-active]:bg-white/20 group-[.is-active]:text-white">{{ $totalRequests }}</span>
                </button>
                @foreach ($statusCounts as $statusSlug => $statusData)
                    @php
                        $design = $resolveStatusDesign($statusData['name']);
                    @endphp
                    <button type="button"
                        class="employee-status-btn group inline-flex cursor-pointer items-center gap-2 rounded-full px-4 py-2.5 text-xs font-semibold transition {{ $design['button_inactive'] }}"
                        data-status-btn="{{ $statusSlug }}"
                        data-inactive-class="{{ $design['button_inactive'] }}"
                        data-active-class="{{ $design['button_active'] }}">
                        <i class="fa-solid {{ $design['icon'] }} text-sm group-[.is-active]:text-white/90"></i>
                        <span class="uppercase tracking-wide group-[.is-active]:text-white">{{ $statusData['name'] }}</span>
                        <span class="inline-flex min-w-[28px] items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-bold {{ $design['count'] }} group-[.is-active]:bg-white/20 group-[.is-active]:text-white">{{ $statusData['count'] }}</span>
                    </button>
                @endforeach
            </div>
            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span>Showing <span id="employeePrResultCount">{{ $purchaseRequests->count() }}</span> of <span id="employeePrTotalCount">{{ $totalRequests }}</span> requests</span>
                </span>
                <span class="text-xs font-medium uppercase tracking-[0.18em] text-gray-400">Filters update instantly as you type</span>
            </div>
        </div>
        <div class="overflow-hidden rounded-[24px] border border-emerald-950/8 bg-white">
            <div class="overflow-x-auto">
                <table id="employeePurchaseRequestsTable" class="min-w-full text-sm">
                    <thead class="bg-[#f5f8f6] text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                <tr>
                    <th class="px-5 py-3 text-left">PR Number</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Purpose</th>
                    <th class="px-5 py-3 text-left">Total Cost</th>
                    <th class="px-5 py-3 text-right">Submitted</th>
                    <th class="px-5 py-3 text-center">Actions</th>
                </tr>
            </thead>
                    <tbody>
                @forelse ($purchaseRequests as $request)
                    @php
                        $statusName = $request->status?->status_name ?? 'Unknown';
                        $statusSlug = \Illuminate\Support\Str::slug($statusName, '_');
                        $design = $resolveStatusDesign($statusName);
                        $searchTokens = collect([
                            $request->pr_no,
                            $statusName,
                            $request->purpose,
                            number_format((float) $request->total_estimated_cost, 2),
                            optional($request->created_at)->format('M d, Y'),
                        ])->filter()->implode(' ');
                        $searchIndex = strtolower($searchTokens);
                    @endphp
                    <tr data-row data-status="{{ $statusSlug }}" data-search="{{ e($searchIndex) }}" class="group border-b border-gray-100/90 last:border-0 hover:bg-[#f6fbf8] transition-colors duration-200">
                        <td class="px-5 py-4 align-top">
                            <span class="block font-bold text-[#1a3a2d] group-hover:text-[#18472f] transition">{{ $request->pr_no }}</span>
                            <span class="mt-1 inline-flex rounded-full bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-500">Ref. {{ $loop->iteration }}</span>
                        </td>
                        <td class="px-5 py-4 align-top">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $design['badge'] }}">
                                    <i class="fa-solid {{ $design['icon'] }} text-[10px]"></i>{{ $statusName }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top text-gray-600 max-w-md">
                            <span class="block line-clamp-2 leading-6" title="{{ $request->purpose }}">{{ $request->purpose }}</span>
                        </td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex rounded-full bg-[#f2f7f4] px-3 py-1.5 text-sm font-semibold text-gray-800">
                                ₱{{ number_format((float) $request->total_estimated_cost, 2) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top text-right text-gray-500 text-xs leading-5">
                            {{ optional($request->created_at)->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-5 py-4 align-top text-center">
                            <button class="js-view-employee-pr inline-flex cursor-pointer items-center gap-2 rounded-xl border border-[#1a3a2d]/10 bg-[#f4f8f5] px-4 py-2.5 text-xs font-semibold text-[#1a3a2d] transition-all hover:-translate-y-0.5 hover:border-[#1a3a2d]/20 hover:bg-[#eaf4ee] hover:shadow-md" data-show-url="{{ route('employee.purchase-requests.show', $request) }}">
                                <i class="fas fa-eye"></i>
                                View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-state>
                        <td colspan="6" class="px-5 py-16 text-center text-gray-500">
                            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                            <p class="font-medium text-lg">No purchase requests found.</p>
                            <p class="text-sm">Use the button above to file your first request.</p>
                        </td>
                    </tr>
                @endforelse
                    <tr id="employeePrNoResults" class="hidden">
                        <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                        <i class="fas fa-search-minus text-4xl text-gray-300 mb-3"></i>
                        <p class="font-semibold">No matching purchase requests.</p>
                        <p class="text-xs">Clear or adjust your filters to see more results.</p>
                    </td>
                </tr>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                <div class="flex justify-center">
                    {{ $purchaseRequests->onEachSide(1)->links('vendor.pagination.procurement') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="employeePrToast" class="pointer-events-none fixed top-20 right-4 sm:right-6 z-[100] hidden min-w-[250px] max-w-md rounded-xl border border-[#2d5a4a] bg-[#1a3a2d] px-5 py-4 text-sm font-semibold text-white shadow-2xl animate-card"></div>

{{-- Create Purchase Request Modal --}}
<div id="createRequestModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            {{-- Modal Header: Kept in place by flexbox, not sticky--}}
            <div class="flex-shrink-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight">New Purchase Request</h3>
                    <p class="text-sm text-white/90 mt-1">Fill out the details of your procurement requirement</p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Form takes up remaining space and handles internal scrolling --}}
            <form id="purchaseRequestForm" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @php
                    $canSubmit = $defaultDivision && $defaultSection;
                    $user = auth()->user();
                    $requesterName = collect([
                        $user?->first_name ?? null,
                        $user?->middle_name ?? null,
                        $user?->last_name ?? null,
                    ])->filter()->implode(' ');
                    $requesterName = $requesterName ?: ($user?->name ?? '');
                @endphp

                @if($canSubmit)
                    <input type="hidden" name="division_id" value="{{ $defaultDivision }}">
                    <input type="hidden" name="section_id" value="{{ $defaultSection }}">
                @endif

                {{-- Scrollable Content Area --}}
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

                    @unless($canSubmit)
                        <div class="rounded-xl border-2 border-amber-400 bg-amber-50 px-5 py-4 text-sm text-amber-900 shadow-sm">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5 mr-3 text-lg"></i>
                                <div>
                                    <p class="font-semibold mb-1">Profile Incomplete</p>
                                    <p class="text-amber-800">Your employee profile has no assigned division/section yet. Please contact the administrator before submitting a purchase request.</p>
                                </div>
                            </div>
                        </div>
                    @endunless

                    {{-- Original Form Structure --}}
                    <div class="rounded-2xl border-2 border-gray-400 bg-white shadow-sm overflow-hidden">
                        <div class="relative border-b border-gray-400 px-5 pt-4 pb-3">
                            <span class="absolute top-3 right-5 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">Annex G-6</span>
                            <div class="flex flex-col items-center gap-3 md:flex-row md:items-center md:justify-between">
                                <img src="{{ asset('images/bpi-logo.png') }}" alt="BPI Logo" class="h-12 w-auto object-contain">
                                <div class="text-center">
                                    <p class="text-[10px] uppercase tracking-[0.28em] text-gray-600">Department of Agriculture</p>
                                    <h2 class="mt-1.5 text-xl font-black uppercase tracking-[0.28em] text-gray-900">Purchase Request</h2>
                                    <p class="mt-1 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                                    <p class="text-xs italic text-gray-500">(Agency)</p>
                                </div>
                                <img src="{{ asset('images/pqslogo.png') }}" alt="PQS Logo" class="h-12 w-auto object-contain">
                            </div>
                        </div>

                        <div class="border-b border-gray-400 px-5 py-2.5">
                            <input type="hidden" name="division_id" value="{{ $defaultDivision }}">
                            <input type="hidden" name="section_id" value="{{ $defaultSection }}">
                            <div class="grid grid-cols-1 gap-x-5 gap-y-1.5 text-xs text-gray-700 md:grid-cols-[1fr_1.1fr_0.7fr]">
                                {{-- Row 1: Division | PR No. | Date --}}
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Division :</span>
                                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate">{{ $divisionName }}</span>
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">PR No.:</span>
                                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">Auto-generated</span>
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">{{ now()->format('M d, Y') }}</span>
                                </div>
                                {{-- Row 2: Section | SAI No. | Date --}}
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Section :</span>
                                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate">{{ $sectionName }}</span>
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">SAI No.:</span>
                                    <input type="text" id="saiNo" name="sai_no" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 bg-transparent focus:border-[#1a3a2d] focus:ring-0" placeholder="Optional">
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                                </div>
                                {{-- Row 3: (empty) | ALOBS No. | Date --}}
                                <div></div>
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">ALOBS No.:</span>
                                    <input type="text" id="alobsNo" name="alobs_no" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 bg-transparent focus:border-[#1a3a2d] focus:ring-0" placeholder="Optional">
                                </div>
                                <div class="flex items-end gap-2">
                                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-b border-gray-400 px-4 py-6">
                            <div class="overflow-x-auto">
                                <table class="w-full border border-gray-500 text-sm border-collapse" id="prItemsTable">
                                    <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-700">
                                        <tr>
                                            <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Quantity</th>
                                            <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Unit of Issue</th>
                                            <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Item Description</th>
                                            <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Stock No.</th>
                                            <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Estimated Unit Cost</th>
                                            <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Estimated Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemRows"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="border border-gray-500 px-3 py-3 text-right font-semibold uppercase tracking-wide" colspan="5">Total Estimated Cost</td>
                                            <td class="border border-gray-500 px-3 py-3 text-right text-lg font-bold">
                                                <span id="prGrandTotal">₱0.00</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-3 text-right text-sm text-gray-700">
                                <span id="selectedFundLabel" class="mr-4">Selected Cluster: <span class="font-semibold">—</span></span>
                                <span id="selectedFundRemaining">Remaining: <span class="font-bold">₱0.00</span></span>
                            </div>
                            <button type="button" id="addItemRow" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-[#1a3a2d] px-4 py-2 text-sm font-semibold text-[#1a3a2d] transition hover:bg-[#1a3a2d] hover:text-white">
                                <i class="fas fa-plus-circle text-base"></i>
                                Add Item Row
                            </button>
                        </div>

                        <div class="border-b border-gray-400 px-6 py-6 space-y-6">
                            <div>
                                <label for="fundAllocation" class="mb-2 block text-sm font-semibold uppercase tracking-wide text-gray-700">
                                    Fund Cluster <span class="text-red-600">*</span>
                                </label>
                                <select id="fundAllocation" name="fund_allocation_id" class="w-full border border-gray-500 px-3 py-2 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-0">
                                    <option value="">Select Fund Cluster</option>
                                    @foreach($fundAllocations ?? [] as $allocation)
                                        <option value="{{ $allocation->id }}" 
                                                data-remaining="{{ $allocation->remaining_amount }}"
                                                data-total="{{ $allocation->total_amount }}">
                                            {{ $allocation->fund_cluster }} - Available: ₱{{ number_format($allocation->remaining_amount, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="fundAllocationWarning" class="hidden mt-2 rounded-lg border-l-4 border-amber-500 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                    <div class="flex items-start">
                                        <i class="fas fa-exclamation-triangle mt-0.5 mr-2 text-amber-600"></i>
                                        <div id="fundAllocationWarningText"></div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="recommendingOfficer" class="mb-2 block text-sm font-semibold uppercase tracking-wide text-gray-700">Recommending Approval :</label>
                                <input type="text" id="recommendingOfficer" name="recommending_officer_id" class="w-full border border-gray-500 px-3 py-2 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-0" placeholder="Optional">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold uppercase tracking-wide text-gray-700" for="purpose">Purpose :</label>
                                <textarea id="purpose" name="purpose" rows="3" data-focus class="w-full border border-gray-500 px-3 py-2 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-0" placeholder="Describe the purpose and justification for this purchase request..."></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-6">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Requested by:</span>
                                    <span class="mt-2 block border-b border-gray-500 pb-2 text-sm font-medium text-gray-900">{{ $requesterName ?: '____________________' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Approved by:</span>
                                    <span class="mt-2 block border-b border-gray-500 pb-2 text-sm text-gray-900">____________________</span>
                                </div>
                            </div>
                            <div class="mt-6 grid gap-6 md:grid-cols-2 text-xs uppercase tracking-wide text-gray-600">
                                <div class="space-y-3">
                                    <div class="border-b border-gray-500 pb-2">Signature:</div>
                                    <div class="border-b border-gray-500 pb-2">Printed Name:</div>
                                    <div class="border-b border-gray-500 pb-2">Designation:</div>
                                </div>
                                <div class="space-y-3">
                                    <div class="border-b border-gray-500 pb-2">Signature:</div>
                                    <div class="border-b border-gray-500 pb-2">Printed Name:</div>
                                    <div class="border-b border-gray-500 pb-2">Designation:</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer: Also kept in place by flexbox --}}
                <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                    <button type="button" class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" data-loading-text="Submitting request..." class="px-6 py-3 rounded-lg bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white font-bold shadow-md hover:shadow-lg hover:scale-105 transition-all @unless($canSubmit) opacity-50 cursor-not-allowed @endunless" @unless($canSubmit) disabled @endunless>
                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('employee.purchase_requests.partials.details-modal')

{{-- Wait Timeframe Modal --}}
<div id="employeeWaitModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-5 py-4 text-white">
                <div>
                    <h3 class="text-lg font-bold tracking-tight">Waiting Timeframe</h3>
                    <p id="employeeWaitContext" class="text-xs text-white/80 mt-1">Let us know how long you can wait.</p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="employeeWaitForm" class="space-y-5 px-5 py-5">
                <div id="employeeWaitErrors" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700"></div>
                <div class="space-y-2">
                    <label for="employeeWaitDate" class="text-xs font-semibold uppercase tracking-wide text-gray-600">Wait Until</label>
                    <input type="date" id="employeeWaitDate" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none" data-focus>
                </div>
                <div class="space-y-2">
                    <label for="employeeWaitNote" class="text-xs font-semibold uppercase tracking-wide text-gray-600">Optional Notes</label>
                    <textarea id="employeeWaitNote" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none" rows="3" placeholder="Add additional context (optional)"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100" data-close-modal>Cancel</button>
                    <button type="submit" data-loading-text="Saving timeframe..." class="rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white transition hover:bg-opacity-90">Save Timeframe</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // JavaScript configuration for the employee purchase request screens
    window.employeePrConfig = {
        storeUrl: '{{ route('employee.purchase-requests.store') }}',
        showUrlTemplate: '{{ route('employee.purchase-requests.show', ['purchase_request' => '__PR__']) }}',
        decisionUrlTemplate: '{{ route('employee.purchase-requests.items.decision', ['purchase_request_item' => '__PRI__']) }}',
        canSubmit: {{ ($defaultDivision && $defaultSection) ? 'true' : 'false' }},
        statuses: {
            pending: {{ \App\Models\Status::PR_FOR_RECOMMENDATION }},
            forApproval: {{ \App\Models\Status::PR_FOR_APPROVAL }},
            approved: {{ \App\Models\Status::PR_APPROVED }},
            declined: {{ \App\Models\Status::PR_CANCELLED }},
        },
    };
</script>
@vite('resources/js/employee-purchase-requests.js')
@endpush