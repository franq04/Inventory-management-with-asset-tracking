<div id="custodianPrToast" class="fixed bottom-6 right-6 hidden px-5 py-3 rounded-xl shadow-lg text-white bg-[#1a3a2d] text-sm font-semibold"></div>

<div id="custodianPrModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" aria-labelledby="custodianPrModalTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative flex flex-col bg-white w-full max-w-5xl rounded-2xl shadow-2xl max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div class="flex-1">
                    <h3 id="custodianPrModalTitle" class="text-2xl font-bold tracking-tight">Purchase Request Review</h3>
                    <p class="text-sm text-white/90 mt-1" id="custodianPrNumber"></p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <div class="flex-grow overflow-y-auto px-6 py-6 space-y-6">
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
                        <div class="grid grid-cols-1 gap-x-5 gap-y-1.5 text-xs text-gray-700 md:grid-cols-[1fr_1.1fr_0.7fr]">
                            {{-- Row 1: Division | PR No. | Date --}}
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Division :</span>
                                <span id="custodianPrDivisionInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">PR No.:</span>
                                <span id="custodianPrNo" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                <span id="custodianPrDate" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            {{-- Row 2: Section | SAI No. | Date --}}
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Section :</span>
                                <span id="custodianPrSectionInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">SAI No.:</span>
                                <span id="custodianPrSaiInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                            </div>
                            {{-- Row 3: (empty) | ALOBS No. | Date --}}
                            <div></div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">ALOBS No.:</span>
                                <span id="custodianPrAlobsInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                            </div>
                            {{-- Row 4: Fund Cluster | Funds Available --}}
                            <div></div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Fund Cluster:</span>
                                <span id="custodianPrFundClusterInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Funds Avail.:</span>
                                <span id="custodianPrFundsAvailableInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
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
                                        <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Fulfillment</th>
                                        <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Item Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="custodianPrItemsTable"></tbody>
                                <tfoot>
                                    <tr>
                                        <td class="border border-gray-500 px-3 py-3 text-right font-semibold uppercase tracking-wide" colspan="5">Total Estimated Cost</td>
                                        <td class="border border-gray-500 px-3 py-3 text-right text-lg font-bold">
                                            <span id="custodianPrGrandTotal">₱0.00</span>
                                        </td>
                                        <td class="border border-gray-500 px-3 py-3" colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="border-b border-gray-400 px-6 py-6 space-y-6">
                        <div>
                            <label class="mb-2 block text-sm font-semibold uppercase tracking-wide text-gray-700" for="custodianPrPurpose">Purpose :</label>
                            <textarea readonly id="custodianPrPurposeTextarea" rows="3" class="w-full border border-gray-500 px-3 py-2 text-sm text-gray-800 bg-transparent"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        <div class="grid gap-6 md:grid-cols-3">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Requested by:</span>
                                <div class="mt-6 space-y-3 text-xs uppercase tracking-wide text-gray-600">
                                    <div class="border-b border-gray-500 pb-2">
                                        <div class="flex min-h-10 items-end justify-between gap-2">
                                            <span>Signature:</span>
                                            <span id="custodianPrRequestedSignature"></span>
                                        </div>
                                    </div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Printed Name: <span id="custodianPrRequestedPrintedName"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Designation:</div>
                                </div>
                            </div>
                            <div id="custodianPrRecommendedBlock" class="hidden">
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Recommending Approval Officer:</span>
                                <div class="mt-6 space-y-3 text-xs uppercase tracking-wide text-gray-600">
                                    <div class="border-b border-gray-500 pb-2">
                                        <div class="flex min-h-10 items-end justify-between gap-2">
                                            <span>Signature:</span>
                                            <span id="custodianPrRecommendedSignature"></span>
                                        </div>
                                    </div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Printed Name: <span id="custodianPrRecommendedPrintedName"></span></div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Date: <span id="custodianPrRecommendedDate"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Remarks: <span id="custodianPrRecommendationRemarks"></span></div>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Approved by:</span>
                                <div class="mt-6 space-y-3 text-xs uppercase tracking-wide text-gray-600">
                                    <div class="border-b border-gray-500 pb-2">
                                        <div class="flex min-h-10 items-end justify-between gap-2">
                                            <span>Signature:</span>
                                            <span id="custodianPrApprovedSignature"></span>
                                        </div>
                                    </div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Printed Name: <span id="custodianPrApprovedPrintedName"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Designation:</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($statuses->isNotEmpty())
                <div class="rounded-xl border-2 border-gray-300 p-6 bg-gradient-to-br from-gray-50 to-white shadow-sm">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-edit mr-2"></i>Update Status
                    </h4>
                    <div id="custodianStatusErrors" class="hidden mb-4 rounded-lg bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 text-sm shadow-sm">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                            <div class="flex-1" id="custodianStatusErrorsContent"></div>
                        </div>
                    </div>
                    @php
                        $gridColumns = ($canEditFunds ?? false) ? 'md:grid-cols-3' : 'md:grid-cols-2';
                        $remarksColSpan = ($canEditFunds ?? false) ? 'md:col-span-3' : 'md:col-span-2';
                        $remarksRequiredLabels = ($statuses ?? collect())
                            ->whereIn('status_id', $remarksRequiredStatuses ?? [])
                            ->pluck('status_name')
                            ->filter()
                            ->implode(', ');
                        $systemManaged = collect($systemManagedStatuses ?? [])->map(fn ($id) => (int) $id)->all();
                    @endphp
                    <form id="custodianStatusForm" class="grid grid-cols-1 gap-5 {{ $gridColumns }}">
                        @csrf
                        <div class="md:col-span-1">
                            <label for="custodianStatusSelect" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">
                                <i class="fas fa-check-circle mr-1"></i>Status
                            </label>
                            <select id="custodianStatusSelect" name="status_id" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all">
                                @foreach ($statuses as $status)
                                    @php
                                        $isManaged = in_array($status->status_id, $systemManaged, true);
                                    @endphp
                                    <option value="{{ $status->status_id }}" @if($isManaged) disabled data-system-managed="true" @endif>
                                        {{ $status->status_name }}
                                        @if($isManaged)
                                            (System)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <div id="custodianSystemStatusNotice" class="mt-3 hidden rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-[11px] font-semibold text-sky-700">
                                This status is updated automatically based on purchase order progress.
                            </div>
                        </div>
                        @if ($canEditFunds ?? false)
                            <div class="md:col-span-1">
                                <label for="custodianFundCluster" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">
                                    <i class="fas fa-coins mr-1"></i>Fund Cluster
                                </label>
                                <input type="text" id="custodianFundCluster" name="fund_cluster" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all" placeholder="e.g., 101" />
                            </div>
                            <div class="md:col-span-1">
                                <label for="custodianFundsAvailable" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">
                                    <i class="fas fa-peso-sign mr-1"></i>Funds Available
                                </label>
                                <input type="number" min="0" step="0.01" id="custodianFundsAvailable" name="funds_available" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all" placeholder="0.00" />
                            </div>
                        @endif
                        <div class="{{ $remarksColSpan }}">
                            <label for="custodianStatusRemarks" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">
                                <i class="fas fa-comment mr-1"></i>Remarks
                                @if (!empty($remarksRequiredLabels))
                                    <span class="ml-1 text-[10px] font-normal uppercase tracking-tight text-rose-600">Required when selecting: {{ $remarksRequiredLabels }}</span>
                                @else
                                    <span class="ml-1 text-[10px] font-normal uppercase tracking-tight text-gray-400">Optional</span>
                                @endif
                            </label>
                            <textarea id="custodianStatusRemarks" name="remarks" rows="2" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all resize-y" placeholder="Optional notes..."></textarea>
                        </div>
                    </form>
                </div>
                @else
                <div class="rounded-xl border-2 border-blue-200 bg-blue-50 p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-info-circle text-blue-600 text-2xl"></i>
                        <div>
                            <h4 class="text-sm font-bold text-blue-900 mb-1">View-Only Access</h4>
                            <p class="text-xs text-blue-700">You can view purchase request details, but status updates are handled by the recommending officer and BAC.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50 shadow-inner">
                <button type="button" class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
                    <i class="fas fa-times mr-2"></i>Close
                </button>
                @if($statuses->isNotEmpty())
                <button type="submit" data-loading-text="Saving status updates..." form="custodianStatusForm" class="px-6 py-3 rounded-lg bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white font-bold shadow-md hover:shadow-lg hover:scale-105 transition-all">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.purchaseRequestWorkflowConfig = @json($workflowConfig);
    window.custodianPrConfig = {
        statuses: @json(($statuses ?? collect())->map(fn ($status) => [
            'id' => $status->status_id,
            'name' => $status->status_name,
        ])),
        currentUserName: @json($currentUserName ?? ''),
        showUrlTemplate: @json(route($routes['show'], [$routeParam => '__PR__'])),
        updateUrlTemplate: @json(route($routes['update'], [$routeParam => '__PR__'])),
        approvedStatusId: @json($workflowConfig['approvedStatusId'] ?? null),
        declinedStatusId: {{ \App\Models\Status::PR_CANCELLED }},
        workflowRole: @json($workflowRole ?? ''),
    };
</script>
@vite('resources/js/custodian-purchase-requests.js')
@endpush