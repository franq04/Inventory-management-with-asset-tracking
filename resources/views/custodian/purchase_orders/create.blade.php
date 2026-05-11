@extends('layouts.app')

@section('title', 'Generate Purchase Order')

@section('content')
<div class="space-y-6 animate-card">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Generate Purchase Order</h2>
            <p class="mt-1 text-gray-500">Mirror the national template while pre-filling data from the approved request.</p>
        </div>
        <a href="{{ route('custodian.orders.index') }}" class="w-full sm:w-auto rounded-lg border border-[#1a3a2d] px-4 py-2 font-semibold text-[#1a3a2d] transition hover:bg-[#1a3a2d] hover:text-white">
            <i class="fas fa-arrow-left mr-2"></i>Back to Purchase Orders
        </a>
    </div>

    {{-- Main Form --}}
    <form id="purchaseOrderForm" class="space-y-6">
        @csrf
        <div id="poFormErrors" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

        @php
            $selectedPrNo = $initialPr;
        @endphp

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm px-6 py-5">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,280px)] lg:items-start">
                <div class="space-y-4">
                    <div class="grid gap-2">
                        <label for="poPrNo" class="text-sm font-semibold text-gray-700">Purchase Request No.</label>
                        <select id="poPrNo" name="pr_no" class="rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" @if($requests->isEmpty()) disabled @endif>
                            <option value="" disabled @if(!$selectedPrNo) selected @endif>
                                @if($requests->isEmpty())
                                    No convertible purchase requests available
                                @else
                                    Select purchase request...
                                @endif
                            </option>
                            @foreach ($requests as $request)
                                <option value="{{ $request->pr_no }}" @selected($selectedPrNo === $request->pr_no)>
                                    {{ $request->pr_no }} — {{ $request->status?->status_name ?? 'Status unknown' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <label for="poPurpose" class="text-sm font-semibold text-gray-700">Purpose / Justification</label>
                        <textarea id="poPurpose" name="purpose" rows="3" class="rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Purpose details will auto-fill after selecting a request."></textarea>
                    </div>
                </div>
                <div class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-4 shadow-inner">
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <span>Items Selected</span>
                        <span id="poSummaryItems" class="text-base font-bold text-gray-900">0</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <span>Estimated Total</span>
                        <span id="poSummaryTotal" class="text-base font-bold text-[#1a3a2d]">₱0.00</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- PO Document Container --}}
        <div class="overflow-hidden rounded-xl border-2 border-gray-400 bg-white shadow-lg">

            {{-- Form Header --}}
            <div class="relative border-b-2 border-gray-400 px-6 pt-6 pb-4">
                <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">Appendix 01</span>
                <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                    <img src="{{ asset('images/bpi-logo.png') }}" alt="BPI Logo" class="h-16 w-auto object-contain">
                    <div class="text-center">
                        <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                        <h3 class="mt-2 text-2xl font-black uppercase tracking-[0.35em] text-gray-900">Purchase Order</h3>
                        <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                        <p class="text-xs italic text-gray-500">(Agency)</p>
                    </div>
                    <img src="{{ asset('images/pqslogo.png') }}" alt="PQS Logo" class="h-16 w-auto object-contain">
                </div>
            </div>

            {{-- Supplier & PO Details Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-y-2 border-gray-400 px-6 py-4 text-sm">
                {{-- Left Column: Supplier --}}
                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-600">Supplier Source</span>
                        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                                <input type="radio" name="supplier_mode" id="poSupplierModeExisting" value="existing" class="h-4 w-4 text-[#1a3a2d] focus:ring-[#1a3a2d]" checked>
                                <span>Use saved supplier</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                                <input type="radio" name="supplier_mode" id="poSupplierModeNew" value="new" class="h-4 w-4 text-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <span>Add new supplier</span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Switch to “Add new supplier” when onboarding a supplier that is not yet in your list. Saved suppliers keep their history for future orders.</p>
                    </div>

                    <div id="poSupplierExistingWrapper" class="space-y-3">
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poSupplier" class="font-semibold">Supplier :</label>
                            <select id="poSupplier" name="supplier_id" class="w-full rounded-lg border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                                <option value="" disabled selected>Select supplier...</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->supplier_id }}"
                                            data-address="{{ $supplier->address }}"
                                            data-tin="{{ $supplier->tin }}"
                                            data-contact="{{ $supplier->contact_person }}"
                                            data-contact-number="{{ $supplier->contact_no }}"
                                            data-email="{{ $supplier->email }}">
                                        {{ $supplier->supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Only show supplier name, address and TIN for saved suppliers per request --}}
                    </div>

                    <div id="poSupplierNewWrapper" class="hidden space-y-3">
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poNewSupplierName" class="font-semibold">Supplier Name :</label>
                            <input type="text" id="poNewSupplierName" name="new_supplier[name]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Enter supplier name" data-new-supplier-field disabled>
                        </div>
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poNewSupplierAddress" class="font-semibold">Address :</label>
                            <input type="text" id="poNewSupplierAddress" name="new_supplier[address]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Complete address" data-new-supplier-field disabled>
                        </div>
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poNewSupplierTin" class="font-semibold">TIN :</label>
                            <input type="text" id="poNewSupplierTin" name="new_supplier[tin]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Tax Identification Number" data-new-supplier-field disabled>
                        </div>
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poNewSupplierContact" class="font-semibold">Contact Person :</label>
                            <input type="text" id="poNewSupplierContact" name="new_supplier[contact_person]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Primary contact" data-new-supplier-field disabled>
                        </div>
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poNewSupplierContactNo" class="font-semibold">Contact No. :</label>
                            <input type="text" id="poNewSupplierContactNo" name="new_supplier[contact_no]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Phone number" data-new-supplier-field disabled>
                        </div>
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                            <label for="poNewSupplierEmail" class="font-semibold">Email :</label>
                            <input type="email" id="poNewSupplierEmail" name="new_supplier[email]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="Email address" data-new-supplier-field disabled>
                        </div>
                    </div>
                </div>
                {{-- Right Column: PO Info --}}
                <div class="space-y-3">
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label class="font-semibold">P.O. No. :</label>
                        <span class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-500">Auto-generated</span>
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poOrderDate" class="font-semibold">Date :</label>
                        <input type="date" id="poOrderDate" name="order_date" value="{{ now()->toDateString() }}" class="w-full rounded border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poMode" class="font-semibold">Mode of Procurement :</label>
                        <input type="text" id="poMode" name="mode_of_procurement" class="w-full rounded border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" placeholder="e.g., Shopping">
                    </div>
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-x-6 pt-1">
                    <div id="poSupplierAddressRow" class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poSupplierAddress" class="font-semibold">Address :</label>
                        <input type="text" id="poSupplierAddress" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm text-gray-700 focus:ring-0" readonly placeholder="—">
                    </div>
                    <div id="poSupplierTinRow" class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poSupplierTin" class="font-semibold">TIN :</label>
                        <input type="text" id="poSupplierTin" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm text-gray-700 focus:ring-0" readonly placeholder="—">
                    </div>
                </div>
            </div>

            {{-- Gentlemen Clause --}}
            <div class="px-6 py-3 text-sm italic">
                Gentlemen: Please furnish this Office the following articles subject to the terms and conditions contained herein:
            </div>

            {{-- Delivery & Payment Terms --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-t-2 border-gray-400 px-6 py-4 text-sm">
                <div class="space-y-3">
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poPlace" class="font-semibold">Place of Delivery <span class="text-rose-500">*</span>:</label>
                        <input type="text" id="poPlace" name="place_of_delivery" value="{{ old('place_of_delivery', $poDefaults['place_of_delivery'] ?? '') }}" readonly class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm text-gray-600 focus:ring-0 cursor-not-allowed">
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poDeliveryDate" class="font-semibold">Date of Delivery <span class="text-rose-500">*</span>:</label>
                        <input type="date" id="poDeliveryDate" name="delivery_date" value="{{ old('delivery_date', $poDefaults['delivery_date'] ?? '') }}" class="w-full rounded border-gray-300 px-2 py-1 text-sm shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]">
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poDeliveryTerm" class="font-semibold">Delivery Term <span class="text-rose-500">*</span>:</label>
                        <input type="text" id="poDeliveryTerm" name="delivery_term" value="{{ old('delivery_term', $poDefaults['delivery_term'] ?? '') }}" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0">
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poPaymentTerm" class="font-semibold">Payment Term <span class="text-rose-500">*</span>:</label>
                        <input type="text" id="poPaymentTerm" name="payment_term" value="{{ old('payment_term', $poDefaults['payment_term'] ?? '') }}" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0">
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="px-6 py-2">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm" id="poItemsTable">
                        {{-- FIX: Restored the original table header as requested --}}
                        <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
                            <tr>
                                <th class="border border-gray-400 px-3 py-2 text-left">Stock / Property No.</th>
                                <th class="border border-gray-400 px-3 py-2 text-center">Unit</th>
                                <th class="border border-gray-400 px-3 py-2 text-left">Description</th>
                                <th class="border border-gray-400 px-3 py-2 text-right">Quantity</th>
                                <th class="border border-gray-400 px-3 py-2 text-right">Unit Cost</th>
                                <th class="border border-gray-400 px-3 py-2 text-right">Amount</th>
                                <th class="border border-gray-400 px-3 py-2 text-center">Fulfillment Status</th>
                                <th class="border border-gray-400 px-3 py-2 text-left">Requester Response</th>
                            </tr>
                        </thead>
                        <tbody id="poItemRows">
                            {{-- JS will populate this --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Total Amount in Words --}}
            <div class="grid grid-cols-[auto,1fr] items-center gap-x-4 border-y-2 border-gray-400 px-6 py-3 text-sm">
                <label for="poAmountWords" class="font-semibold">(Total Amount in Words)</label>
                <input type="text" id="poAmountWords" name="amount_in_words" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm font-medium focus:ring-0" placeholder="...">
            </div>

            {{-- Penalty Clause --}}
            <div class="px-6 py-4 text-xs italic">
                In case of failure to make the full delivery within the time specified above, a penalty of one-tenth (1/10) of one percent for every day of delay shall be imposed on the undelivered item/s.
            </div>

            {{-- Signatures Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-t-2 border-gray-400 px-6 pt-4 pb-8 text-sm">
                <div>
                    <p class="font-semibold mb-3">Conforme:</p>
                    <div class="mb-6">
                        <input type="text" id="poConformeName" name="conforme_name" class="w-full border-0 border-b border-gray-500 bg-transparent px-2 py-1 text-center text-sm font-semibold focus:ring-0" placeholder="Name of Supplier">
                        <label class="mt-1 block text-center text-xs">Signature over Printed Name of Supplier</label>
                    </div>
                    <div class="mb-3">
                        <input type="date" id="poConformeDate" name="conforme_date" class="w-full border-0 border-b border-gray-500 bg-transparent px-2 py-1 text-center text-sm focus:ring-0">
                        <label class="mt-1 block text-center text-xs">Date</label>
                    </div>
                </div>
                <div>
                    <p class="font-semibold mb-3">Very truly yours,</p>
                    <div class="mb-6">
                        <div class="w-full border-b border-gray-500 bg-transparent px-2 py-1 text-center text-sm font-semibold h-[1.75rem]">
                            {{-- Authorized Official Name Goes Here --}}
                        </div>
                        <label class="mt-1 block text-center text-xs">Signature over Printed Name of Authorized Official</label>
                    </div>
                    <div class="mb-3">
                        <div class="w-full border-b border-gray-500 bg-transparent px-2 py-1 text-center text-sm font-semibold h-[1.75rem]">
                            {{-- Designation Goes Here --}}
                        </div>
                        <label class="mt-1 block text-center text-xs">Designation</label>
                    </div>
                </div>
            </div>
            
            {{-- Funding Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 border-t-2 border-gray-400 text-sm">
                <div class="space-y-3 border-r-2 border-gray-400 p-4">
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poFundCluster" class="font-semibold">Fund Cluster :</label>
                        <input type="text" id="poFundCluster" name="fund_cluster" readonly class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm text-gray-600 focus:ring-0 cursor-not-allowed">
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poFundsAvailable" class="font-semibold">Funds Available :</label>
                        <input type="number" id="poFundsAvailable" name="funds_available" step="0.01" min="0" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0" readonly>
                    </div>
                    <div class="pt-8">
                        <div class="w-full border-b border-gray-500 bg-transparent px-2 text-center text-sm font-semibold pb-1"></div>
                        <label class="mt-1 block text-center text-xs">Signature over Printed Name of Chief Accountant/Head of Accounting Division/Unit</label>
                    </div>
                </div>
                <div class="space-y-3 p-4">
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poOrsNo" class="font-semibold">ORS/BURS No. :</label>
                        <input type="text" id="poOrsNo" name="ors_burs_no" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0">
                    </div>
                     <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poOrsDate" class="font-semibold">Date of the ORS/BURS :</label>
                        <input type="date" id="poOrsDate" name="ors_burs_date" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0">
                    </div>
                    <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                        <label for="poOrsAmount" class="font-semibold">Amount :</label>
                        <input type="number" id="poOrsAmount" name="ors_burs_amount" step="0.01" min="0" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('custodian.orders.index') }}" class="rounded-lg bg-gray-100 px-5 py-2.5 font-semibold text-gray-700 transition hover:bg-gray-200">Cancel</a>
            <button type="submit" data-loading-text="Generating purchase order..." class="rounded-lg bg-[#1a3a2d] px-5 py-2.5 font-semibold text-white transition hover:bg-opacity-90 shadow-md hover:shadow-lg">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Generate Purchase Order
            </button>
        </div>
    </form>
</div>
@endsection

{{-- All original PHP and Script sections remain unchanged --}}
@php
$purchaseRequestsData = $requests->mapWithKeys(function ($request) {
    return [$request->pr_no => [
        'purpose' => $request->purpose,
        'requested_delivery_date' => optional($request->requested_delivery_date)->toDateString(),
        'requested_delivery_term' => $request->requested_delivery_term,
        'requested_payment_term' => $request->requested_payment_term,
        'fund_cluster' => $request->fund_cluster ?: $request->fundAllocation?->fund_cluster,
        'funds_available' => $request->fundAllocation?->remaining_amount ?? $request->funds_available,
        'total_estimated_cost' => $request->total_estimated_cost,
        'items' => $request->items->map(function ($item) {
            return [
                'pri_id' => $item->pri_id,
                'item_description' => $item->item_description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'stock_number' => $item->stock_number,
                'estimated_unit_cost' => $item->estimated_unit_cost,
                'estimated_total_cost' => $item->estimated_total_cost,
                'remarks' => $item->remarks,
                'fulfillment_status' => $item->fulfillment_status,
                'alternate_description' => $item->alternate_description,
                'employee_decision' => $item->employee_decision,
                'employee_decided_at' => optional($item->employee_decided_at)->toDateTimeString(),
                'employee_wait_until' => optional($item->employee_wait_until)->toDateString(),
                'employee_wait_note' => $item->employee_wait_note,
            ];
        })->toArray(),
    ]];
})->toArray();

$suppliersData = $suppliers->mapWithKeys(function ($supplier) {
    return [$supplier->supplier_id => [
        'name' => $supplier->supplier_name,
        'address' => $supplier->address,
        'tin' => $supplier->tin,
        'contact_person' => $supplier->contact_person,
        'contact_no' => $supplier->contact_no,
        'email' => $supplier->email,
    ]];
})->toArray();
@endphp

@push('scripts')
<script>
    window.purchaseOrderConfig = {
        storeUrl: '{{ route('custodian.orders.store') }}',
        purchaseRequests: @json($purchaseRequestsData),
        suppliers: @json($suppliersData),
        initialPrNo: '{{ $initialPr }}',
        purchaseOrderDefaults: @json($poDefaults),
    };
</script>
@vite('resources/js/purchase-orders.js')
@endpush