@extends('layouts.app')

@section('title', 'Purchase Request')

@section('content')
@php
    $requesterSignature = $purchaseRequest->requester?->employee?->signature
        ? 'data:image/png;base64,' . base64_encode($purchaseRequest->requester->employee->signature)
        : null;
    $approverSignature = $purchaseRequest->approver?->employee?->signature
        ? 'data:image/png;base64,' . base64_encode($purchaseRequest->approver->employee->signature)
        : null;
@endphp
<div class="max-w-5xl mx-auto px-4 pb-8">
    <div class="bg-white rounded-2xl border-2 border-gray-300 overflow-hidden mt-6">
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
            <div class="grid grid-cols-1 gap-x-5 gap-y-1.5 text-xs text-gray-700 md:grid-cols-[1fr_1.1fr_0.7fr]">
                {{-- Row 1: Division | PR No. | Date --}}
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Division :</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate">{{ $purchaseRequest->division?->division_name ?? '—' }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">PR No.:</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">{{ $purchaseRequest->pr_no }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">{{ optional($purchaseRequest->created_at)->format('M d, Y') }}</span>
                </div>
                {{-- Row 2: Section | SAI No. | Date --}}
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Section :</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate">{{ $purchaseRequest->section?->section_name ?? '—' }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">SAI No.:</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">{{ $purchaseRequest->sai_no ?? '—' }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                </div>
                {{-- Row 3: (empty) | ALOBS No. | Date --}}
                <div></div>
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">ALOBS No.:</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">{{ $purchaseRequest->alobs_no ?? '—' }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                    <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                </div>
            </div>
        </div>

        <div class="border-b border-gray-400 px-4 py-6">
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-500 text-sm border-collapse">
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
                    <tbody>
                        @foreach($purchaseRequest->items as $item)
                        <tr>
                            <td class="border border-gray-500 px-3 py-2 text-center">{{ $item->quantity }}</td>
                            <td class="border border-gray-500 px-3 py-2 text-center">{{ $item->unit }}</td>
                            <td class="border border-gray-500 px-3 py-2">{{ $item->item_description }}</td>
                            <td class="border border-gray-500 px-3 py-2 text-center">{{ $item->stock_number ?? '—' }}</td>
                            <td class="border border-gray-500 px-3 py-2 text-right">₱{{ number_format($item->estimated_unit_cost ?? $item->unit_cost ?? 0, 2) }}</td>
                            <td class="border border-gray-500 px-3 py-2 text-right">₱{{ number_format($item->estimated_total_cost ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="border border-gray-500 px-3 py-3 text-right font-semibold uppercase tracking-wide" colspan="5">Total Estimated Cost</td>
                            <td class="border border-gray-500 px-3 py-3 text-right text-lg font-bold">₱{{ number_format($purchaseRequest->total_estimated_cost ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3 text-right text-sm text-gray-700">
                <span class="mr-4">Selected Cluster: <span class="font-semibold">{{ $purchaseRequest->fundAllocation?->fund_cluster ?? '—' }}</span></span>
                <span>Remaining: <span class="font-bold">₱{{ number_format($purchaseRequest->fundAllocation?->remaining_amount ?? ($purchaseRequest->funds_available ?? 0), 2) }}</span></span>
            </div>
        </div>

        <div class="border-b border-gray-400 px-6 py-6 space-y-6">
            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-3">Purpose</span>
                <p class="text-sm text-gray-800 leading-relaxed">{{ $purchaseRequest->purpose ?? '—' }}</p>
            </div>
        </div>

        <div class="px-6 py-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Requested by:</span>
                    <span class="mt-2 block border-b border-gray-500 pb-2 text-sm font-medium text-gray-900">
                        @if($purchaseRequest->requester?->employee)
                            {{ trim($purchaseRequest->requester->employee->first_name . ' ' . $purchaseRequest->requester->employee->last_name) }}
                        @else
                            {{ $purchaseRequest->requester?->username ?? '—' }}
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Approved by:</span>
                    <span class="mt-2 block border-b border-gray-500 pb-2 text-sm font-medium text-gray-900">{{ $purchaseRequest->approver?->username ?? '____________________' }}</span>
                </div>
            </div>
            <div class="mt-6 grid gap-6 md:grid-cols-2 text-xs uppercase tracking-wide text-gray-600">
                <div class="space-y-3">
                    <div class="border-b border-gray-500 pb-2">
                        <div class="flex min-h-10 items-end justify-between gap-2">
                            <span>Signature:</span>
                            @if ($requesterSignature)
                                <img src="{{ $requesterSignature }}" alt="Requested by signature" class="max-h-10 w-auto object-contain" />
                            @endif
                        </div>
                    </div>
                    <div class="border-b border-gray-500 pb-2">Printed Name:</div>
                    <div class="border-b border-gray-500 pb-2">Designation:</div>
                </div>
                <div class="space-y-3">
                    <div class="border-b border-gray-500 pb-2">
                        <div class="flex min-h-10 items-end justify-between gap-2">
                            <span>Signature:</span>
                            @if ($approverSignature)
                                <img src="{{ $approverSignature }}" alt="Approved by signature" class="max-h-10 w-auto object-contain" />
                            @endif
                        </div>
                    </div>
                    <div class="border-b border-gray-500 pb-2">Printed Name:</div>
                    <div class="border-b border-gray-500 pb-2">Designation:</div>
                </div>
            </div>
        </div>
    </div>

    @if($latestHistory)
    <div class="mt-6 bg-white rounded-2xl shadow-sm p-4">
        <h3 class="text-base font-bold mb-2">Latest Status</h3>
        <p class="text-sm text-gray-700">{{ $latestHistory->remarks ?? 'No remarks' }}</p>
        <p class="text-xs text-gray-500 mt-2">Changed at: {{ optional($latestHistory->changed_at)->format('M d, Y h:i A') }}</p>
    </div>
    @endif
</div>
@endsection
