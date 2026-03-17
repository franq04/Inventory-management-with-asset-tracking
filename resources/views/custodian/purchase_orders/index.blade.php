                                                    View
                                                </a>
                                                <a href="{{ route('custodian.orders.create', ['pr' => $request->pr_no]) }}" class="inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90">
                                                    <i class="fas fa-file-signature"></i>
                                                    Create PO
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                            <i class="fa-solid fa-file-circle-question text-3xl text-gray-300 mb-3"></i>
                                            <p>No approved purchase requests awaiting ordering.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @elseif ($key === 'partial')
                    <div class="mt-4 space-y-6">
                        @forelse ($partialOrders as $order)
                            @php
                                $pendingItems = $order->items->filter(function ($item) {
                                    if ($item->fulfillment_status === 'ordered') {
                                        return false;
                                    }

                                    if ($item->fulfillment_status === 'unavailable') {
                                        $waitUntil = $item->employee_wait_until ? \Illuminate\Support\Carbon::parse($item->employee_wait_until) : null;
                                        return !($waitUntil && $waitUntil->isPast());
                                    }

                                    return true;
                                });
                            @endphp
                            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 shadow-sm">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-200 pb-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-amber-900">PO {{ $order->po_no }}</h4>
                                        <p class="text-sm text-amber-800">PR {{ $order->pr_no }} • {{ $order->supplier?->supplier_name ?? 'No supplier set' }}</p>
                                    </div>
                                    <div class="text-xs text-amber-700">
                                        Updated {{ optional($order->updated_at)->diffForHumans() ?? 'recently' }}
                                    </div>
                                </div>

                                <div class="mt-4 space-y-4">
                                    @foreach ($pendingItems as $item)
                                        @php
                                            $employeeDecision = $item->employee_decision;
                                            $decisionLabel = match ($employeeDecision) {
                                                'accept' => 'Employee accepted the alternative item.',
                                                'wait' => 'Employee prefers to wait for the original item.',
                                                default => null,
                                            };
                                        @endphp
                                        <div class="rounded-xl border border-amber-200 bg-white p-4 shadow-sm">
                                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                <div class="space-y-1">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $item->item_description }}</p>
                                                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                                                        <span>Qty: {{ $item->quantity }}</span>
                                                        <span>Unit: {{ $item->unit }}</span>
                                                        <span>Requested cost: ₱{{ number_format((float) $item->purchaseRequestItem?->estimated_unit_cost, 2) }}</span>
                                                        <span>Current cost: ₱{{ number_format((float) $item->unit_cost, 2) }}</span>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 font-semibold text-amber-700 capitalize">
                                                            <i class="fas fa-circle text-[8px]"></i>{{ $item->fulfillment_status }}
                                                        </span>
                                                        @if ($item->alternate_description)
                                                            <span class="text-amber-700">Alt: {{ $item->alternate_description }}</span>
                                                        @endif
                                                    </div>
                                                    @if ($decisionLabel)
                                                        <div class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-800">
                                                            <i class="fas fa-user-check mr-1"></i>{{ $decisionLabel }}
                                                        </div>
                                                    @endif
                                                    @php
                                                        $waitUntil = $item->employee_wait_until ? \Illuminate\Support\Carbon::parse($item->employee_wait_until) : null;
                                                        $waitExpired = $waitUntil ? $waitUntil->isPast() : false;
                                                    @endphp
                                                    @if ($item->fulfillment_status === 'unavailable')
                                                        <div class="rounded-lg border {{ $waitUntil ? ($waitExpired ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-800') : 'border-amber-200 bg-amber-50 text-amber-800' }} px-3 py-2 text-xs">
                                                            <i class="fas fa-hourglass-half mr-1"></i>
                                                            @if ($waitUntil)
                                                                @if ($waitExpired)
                                                                    Employee wait period lapsed on {{ $waitUntil->format('M d, Y') }}.
                                                                @else
                                                                    Employee will wait until {{ $waitUntil->format('M d, Y') }}.
                                                                @endif
                                                            @else
                                                                Awaiting employee wait timeframe.
                                                            @endif
                                                            @if ($item->employee_wait_note)
                                                                <div class="mt-1 text-[11px] text-gray-600">“{{ $item->employee_wait_note }}”</div>
                                                            @endif
                                                        </div>
                                                    @elseif ($item->fulfillment_status === 'alternative' && $item->employee_decision === 'wait' && $waitUntil)
                                                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                                            <i class="fas fa-hourglass-half mr-1"></i>
                                                            Employee will wait for the original item until {{ $waitUntil->format('M d, Y') }}.
                                                            @if ($item->employee_wait_note)
                                                                <div class="mt-1 text-[11px] text-gray-600">“{{ $item->employee_wait_note }}”</div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    @if ($item->fulfillment_status === 'alternative' && $item->employee_decision === 'wait')
                                                        <button type="button" class="js-retain-original inline-flex items-center gap-2 rounded-lg border border-amber-400 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-500 hover:text-white"
                                                            data-retain-url="{{ route('custodian.orders.items.retain', $item) }}">
                                                            <i class="fas fa-undo"></i>Retain Original
                                                        </button>
                                                    @endif
                                                    <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                                        data-show-url="{{ route('custodian.orders.show', $order) }}">
                                                        <i class="fas fa-eye"></i>View PO
                                                    </button>
                                                </div>
                                            </div>

                                            <form class="mt-4 space-y-3 rounded-lg border border-amber-100 bg-amber-50 px-3 py-3 js-update-po-item" data-update-url="{{ route('custodian.orders.items.update', $item) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_minmax(0,150px)_minmax(0,220px)]">
                                                    <div>
                                                        <label class="text-xs font-semibold uppercase tracking-wide text-amber-700">Item Status</label>
                                                        <select name="fulfillment_status" class="js-item-status mt-1 w-full rounded border border-amber-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none">
                                                            <option value="ordered" @selected($item->fulfillment_status === 'ordered')>Ordered</option>
                                                            <option value="unavailable" @selected($item->fulfillment_status === 'unavailable')>Unavailable</option>
                                                            <option value="alternative" @selected($item->fulfillment_status === 'alternative')>Alternative</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-semibold uppercase tracking-wide text-amber-700">Unit Cost</label>
                                                        <div class="relative mt-1">
                                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-amber-600">₱</span>
                                                            <input type="number" min="0" step="0.01" name="unit_cost" value="{{ $item->unit_cost }}" class="js-item-unit-cost w-full rounded border border-amber-300 px-3 py-2 pl-7 text-sm text-right focus:border-[#1a3a2d] focus:outline-none">
                                                        </div>
                                                    </div>
                                                    <div data-alt-wrapper class="{{ $item->fulfillment_status === 'alternative' ? '' : 'hidden' }}">
                                                        <label class="text-xs font-semibold uppercase tracking-wide text-amber-700">Alternative Description</label>
                                                        <input type="text" name="alternate_description" value="{{ $item->alternate_description }}" class="js-item-alt mt-1 w-full rounded border border-amber-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none" {{ $item->fulfillment_status === 'alternative' ? '' : 'disabled' }}>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90">
                                                        <i class="fas fa-floppy-disk"></i>
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-amber-300 bg-white px-6 py-10 text-center text-gray-500">
                                <i class="fa-solid fa-box-open text-3xl text-amber-200 mb-3"></i>
                                <p>No partial orders at the moment.</p>
                            </div>
                        @endforelse
                    </div>
                @elseif ($key === 'completed')
                    <div class="mt-4 overflow-x-auto rounded-xl border">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">PO No.</th>
                                    <th class="px-4 py-3 text-left">PR No.</th>
                                    <th class="px-4 py-3 text-left">Supplier</th>
                                    <th class="px-4 py-3 text-right">Order Date</th>
                                    <th class="px-4 py-3 text-right">Total Amount</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($completedOrders as $order)
                                    @php
                                        $total = $order->items->sum('total_cost');
                                    @endphp
                                    <tr class="border-b last:border-0 hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4 font-semibold text-gray-800">{{ $order->po_no }}</td>
                                        <td class="px-4 py-4 text-gray-600">{{ $order->pr_no }}</td>
                                        <td class="px-4 py-4 text-gray-700">{{ $order->supplier?->supplier_name ?? '—' }}</td>
                                        <td class="px-4 py-4 text-right text-gray-500">{{ optional($order->order_date)->format('M d, Y') }}</td>
                                        <td class="px-4 py-4 text-right font-semibold text-gray-900">₱{{ number_format($total, 2) }}</td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-2">
                                                <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                                    data-show-url="{{ route('custodian.orders.show', $order) }}">
                                                    <i class="fas fa-eye"></i>
                                                    View PO
                                                </button>
                                                <button type="button"
                                                    class="js-open-inspection inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90"
                                                    data-form-url="{{ route('custodian.inspection.form', $order) }}"
                                                    data-store-url="{{ route('custodian.inspection.store', $order) }}">
                                                    <i class="fas fa-clipboard-check"></i>
                                                    Prepare Inspection
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                            <i class="fa-solid fa-circle-check text-3xl text-gray-300 mb-3"></i>
                                            <p>No completed orders yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mt-4">
                        <form method="GET" class="mb-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-[minmax(0,1fr)_auto]">
                            <div>
                                <label for="poSearch" class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                                <input type="text" id="poSearch" name="search" value="{{ $search }}" placeholder="PO number, PR number or supplier" class="w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]">
                            </div>
                            <div class="flex items-end gap-2 justify-end">
                                <a href="{{ route('custodian.orders.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 font-semibold text-gray-700 transition hover:bg-gray-200">Reset</a>
                                <button type="submit" class="rounded-lg bg-[#1a3a2d] px-4 py-2 font-semibold text-white transition hover:bg-opacity-90">Apply</button>
                            </div>
                        </form>

                        <div class="overflow-x-auto rounded-xl border">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3 text-left">PO No.</th>
                                        <th class="px-4 py-3 text-left">PR No.</th>
                                        <th class="px-4 py-3 text-left">Supplier</th>
                                        <th class="px-4 py-3 text-left">Fulfillment</th>
                                        <th class="px-4 py-3 text-right">Order Date</th>
                                        <th class="px-4 py-3 text-right">Total Amount</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($purchaseOrders as $order)
                                        @php
                                            $isComplete = $order->items->isNotEmpty() && $order->items->every(fn ($item) => $item->fulfillment_status === 'ordered');
                                            $fulfillmentLabel = $isComplete ? 'Completed' : 'Partial';
                                            $fulfillmentBadge = $isComplete ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                                            $total = $order->items->sum('total_cost');
                                        @endphp
                                        <tr class="border-b last:border-0 hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-4 font-semibold text-gray-800">{{ $order->po_no }}</td>
                                            <td class="px-4 py-4 text-gray-600">{{ $order->pr_no }}</td>
                                            <td class="px-4 py-4 text-gray-700">{{ $order->supplier?->supplier_name ?? '—' }}</td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $fulfillmentBadge }}">
                                                    <i class="fas fa-circle text-[8px]"></i>{{ $fulfillmentLabel }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-right text-gray-500">{{ optional($order->order_date)->format('M d, Y') }}</td>
                                            <td class="px-4 py-4 text-right font-semibold text-gray-900">₱{{ number_format($total, 2) }}</td>
                                            <td class="px-4 py-4">
                                                <div class="flex justify-end gap-2">
                                                    <button class="js-view-po inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100"
                                                        data-show-url="{{ route('custodian.orders.show', $order) }}">
                                                        <i class="fas fa-eye"></i>
                                                        View
                                                    </button>
                                                    @php
                                                        $inspectableStatuses = [
                                                            \App\Models\Status::PO_PARTIALLY_DELIVERED,
                                                            \App\Models\Status::PO_DELIVERED_PENDING_INSPECTION,
                                                            \App\Models\Status::PO_CLOSED,
                                                        ];
                                                        $canInspect = in_array($order->status_id, $inspectableStatuses, true) || $order->inspectionReport;
                                                    @endphp
                                                    @if ($canInspect)
                                                        <button type="button"
                                                            class="js-open-inspection inline-flex items-center gap-2 rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-opacity-90"
                                                            data-form-url="{{ route('custodian.inspection.form', $order) }}"
                                                            data-store-url="{{ route('custodian.inspection.store', $order) }}">
                                                            <i class="fas fa-clipboard-check"></i>
                                                            Inspect
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                                <i class="fas fa-clipboard-list text-3xl text-gray-300 mb-3"></i>
                                                <p>No purchase orders found.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $purchaseOrders->links() }}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div id="poDetailsModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity duration-300" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] overflow-hidden transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex-shrink-0 z-10 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div class="flex-1">
                    <h3 class="text-2xl font-bold tracking-tight">Purchase Order Details</h3>
                    <p class="text-sm text-white/80 mt-1" id="poDetailsNumber"></p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="bg-white rounded-2xl border-2 border-gray-300 overflow-hidden">
                                                            <!-- PO Header (matching print format) -->
                    <div class="relative border-b-2 border-gray-400 px-6 pt-6 pb-4">
                        <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">APPENDIX 01</span>
                        <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                            <img src="/images/bpi-logo.png" alt="BPI Logo" class="h-16 w-auto object-contain" />
                            <div class="text-center">
                                <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                                <h2 class="mt-2 text-2xl font-black uppercase tracking-[0.35em] text-gray-900">PURCHASE ORDER</h2>
                                <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                                <p class="text-xs italic text-gray-500">(Agency)</p>
                            </div>
                            <img src="/images/pqslogo.png" alt="PQS Logo" class="h-16 w-auto object-contain" />
                        </div>
                    </div>

                    <!-- PO Details Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-b-2 border-gray-400 px-6 py-4 text-sm">
                        <!-- Left Column: Supplier -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-start gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Supplier :</label>
                                <span id="poDetailsSupplier" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block break-words"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-start gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Address :</label>
                                <span id="poDetailsAddress" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block break-words"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-start gap-x-2">
                                <label class="font-semibold whitespace-nowrap">TIN :</label>
                                <span id="poDetailsTin" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block break-words"></span>
                            </div>
                        </div>
                        <!-- Right Column: PO Info -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">P.O. No. :</label>
                                <span id="poDetailsPoNo" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block font-bold text-red-600"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">PR No. :</label>
                                <span id="poDetailsPr" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Date :</label>
                                <span id="poDetailsOrderDate" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Mode of Procurement :</label>
                                <span id="poDetailsMode" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Gentlemen Clause -->
                    <div class="px-6 py-3 text-sm italic text-gray-800 border-b border-gray-200">
                        Gentlemen: Please furnish this Office the following articles subject to the terms and conditions contained herein:
                    </div>

                    <!-- Delivery & Payment Terms -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-b-2 border-gray-400 px-6 py-4 text-sm">
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Place of Delivery <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsPlace" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Date of Delivery <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsDeliveryDate" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Delivery Term <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsDeliveryTerm" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                            <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                <label class="font-semibold whitespace-nowrap">Payment Term <span class="text-red-500">*</span>:</label>
                                <span id="poDetailsPaymentTerm" class="w-full border-b border-dotted border-gray-400 px-2 text-sm text-gray-900 min-h-[1.5rem] block"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b-2 border-gray-400 border-t-2 border-gray-400">
                                    <th class="border-r border-gray-300 px-4 py-2 text-center font-bold w-24">Item No.</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-center font-bold w-24">Quantity</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-center font-bold w-24">Unit</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-left font-bold">Description</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-left font-bold">Status</th>
                                    <th class="border-r border-gray-300 px-4 py-2 text-right font-bold w-32">Unit Cost</th>
                                    <th class="px-4 py-2 text-right font-bold w-32">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="poDetailsItems" class="align-top divide-y divide-dotted divide-gray-300">
                                <!-- Items will go here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Signatures & Fund Details -->
                    <div class="border-t-2 border-gray-400">
                        <div class="grid grid-cols-[auto,1fr] items-center gap-x-4 border-b border-gray-400 px-6 py-3 text-sm">
                            <label class="font-semibold text-gray-700">(Total Amount in Words)</label>
                            <div class="w-full border-b border-dotted border-gray-400 bg-transparent px-2 text-sm font-medium text-gray-900 h-5" id="poDetailsAmountWords"></div>
                        </div>

                        <div class="px-6 py-4 text-[11px] italic text-gray-600 border-b border-gray-400">
                            In case of failure to make the full delivery within the time specified above, a penalty of one-tenth (1/10) of one percent for every day of delay shall be imposed on the undelivered item/s.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 border-b border-gray-400 px-6 pt-4 pb-8 text-sm">
                            <div>
                                <p class="font-semibold mb-3 text-gray-800">Conforme:</p>
                                <div class="mb-6 mt-8">
                                    <div class="w-full border-b border-gray-500 text-center text-sm font-semibold h-6 text-gray-900" id="poDetailsConformeName"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Signature over Printed Name of Supplier</label>
                                </div>
                                <div class="mb-3">
                                    <div class="w-full border-b border-gray-500 text-center text-sm h-6 text-gray-900" id="poDetailsConformeDate"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Date</label>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold mb-3 text-gray-800">Very truly yours,</p>
                                <div class="mb-6 mt-8">
                                    <div class="w-full border-b border-gray-500 text-center text-sm font-semibold h-6 text-gray-900" id="poDetailsAuthorizedOfficial"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Signature over Printed Name of Authorized Official</label>
                                </div>
                                <div class="mb-3">
                                    <div class="w-full border-b border-gray-500 text-center text-sm font-semibold h-6 text-gray-900" id="poDetailsDesignation"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Designation</label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 text-sm bg-gray-50/50">
                            <div class="space-y-3 border-r border-gray-400 p-4">
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label class="font-semibold text-gray-700">Fund Cluster :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900" id="poDetailsFundCluster"></div>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label class="font-semibold text-gray-700">Funds Available :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900" id="poDetailsFundsAvailable"></div>
                                </div>
                                <div class="pt-8">
                                    <div class="w-full border-b border-gray-500 h-6"></div>
                                    <label class="mt-1 block text-center text-xs text-gray-600">Signature over Printed Name of Chief Accountant/Head of Accounting Division/Unit</label>
                                </div>
                            </div>
                            <div class="space-y-3 p-4">
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2 w-full">
                                    <label class="font-semibold text-gray-700 whitespace-nowrap">ORS/BURS No. :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900 min-w-[100px]" id="poDetailsOrsBursNo"></div>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2 w-full">
                                    <label class="font-semibold text-gray-700 whitespace-nowrap">Date of the ORS/BURS :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900 min-w-[100px]" id="poDetailsOrsBursDate"></div>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2 w-full">
                                    <label class="font-semibold text-gray-700 whitespace-nowrap">Amount :</label>
                                    <div class="w-full border-b border-dotted border-gray-400 text-sm h-5 font-semibold text-gray-900 min-w-[100px]" id="poDetailsOrsBursAmount"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status History Section -->
                <div class="mx-auto max-w-5xl mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h4 class="font-bold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-history text-indigo-500"></i>
                            Status History Log
                        </h4>
                    </div>
                    <div id="poStatusHistory" class="p-4 max-h-64 overflow-y-auto text-sm bg-white">
                        <!-- Status history items will be populated here -->  
                    </div>
                </div>
            <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                <button class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

@include('custodian.inspection._modal')
@endsection

@push('scripts')
@vite('resources/js/purchase-orders.js')
@vite('resources/js/inspection.js')
@endpush
