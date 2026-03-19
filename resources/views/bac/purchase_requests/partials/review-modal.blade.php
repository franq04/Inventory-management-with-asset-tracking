<div id="bacPrToast" class="fixed bottom-6 right-6 hidden px-5 py-3 rounded-xl shadow-lg text-white bg-[#1a3a2d] text-sm font-semibold"></div>

<div id="bacPrModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" aria-labelledby="bacPrModalTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative flex flex-col bg-white w-full max-w-5xl rounded-2xl shadow-2xl max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div class="flex-1">
                    <h3 id="bacPrModalTitle" class="text-2xl font-bold tracking-tight">BAC Purchase Request Review</h3>
                    <p class="text-sm text-white/90 mt-1" id="bacPrNumber"></p>
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
                                <span id="bacPrDivisionInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">PR No.:</span>
                                <span id="bacPrNo" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                <span id="bacPrDate" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            {{-- Row 2: Section | SAI No. | Date --}}
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Section :</span>
                                <span id="bacPrSectionInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">SAI No.:</span>
                                <span id="bacPrSaiInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                                <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                            </div>
                            {{-- Row 3: (empty) | ALOBS No. | Date --}}
                            <div></div>
                            <div class="flex items-end gap-2">
                                <span class="font-bold uppercase tracking-wider whitespace-nowrap">ALOBS No.:</span>
                                <span id="bacPrAlobsInput" class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900"></span>
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
                                        <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Unit Cost</th>
                                        <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Total Cost</th>
                                        <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="bacPrItemsTableBody">
                                    {{-- Items will be dynamically populated --}}
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="6" class="border border-gray-500 px-3 py-2 text-right font-bold text-gray-900 uppercase tracking-wide text-sm">Total Cost:</td>
                                        <td class="border border-gray-500 px-3 py-2 text-center font-bold text-gray-900 text-sm" id="bacPrTotalCost">₱0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="border-b border-gray-400 px-6 py-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-700">Purpose:</label>
                        <textarea readonly id="bacPrPurposeTextarea" rows="3" class="mt-2 w-full border border-gray-500 rounded-md px-3 py-2 text-sm text-gray-900 bg-gray-50"></textarea>
                    </div>

                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 text-xs">
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Requested by:</span>
                                <div class="mt-6 space-y-3 text-xs uppercase tracking-wide text-gray-600">
                                    <div class="border-b border-gray-500 pb-2">
                                        <div class="flex min-h-10 items-end gap-2">
                                            <span class="w-20 shrink-0">Signature:</span>
                                            <span id="bacPrRequestedSignature" class="block flex-1 text-center"></span>
                                        </div>
                                    </div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Printed Name: <span id="bacPrRequestedPrintedName"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Designation: <span id="bacPrRequestedDesignation"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Date: <span id="bacPrRequestedDate"></span></div>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Recommending Approval:</span>
                                <div class="mt-6 space-y-3 text-xs uppercase tracking-wide text-gray-600">
                                    <div class="border-b border-gray-500 pb-2">
                                        <div class="flex min-h-10 items-end gap-2">
                                            <span class="w-20 shrink-0">Signature:</span>
                                            <span id="bacPrRecommendedSignature" class="block flex-1 text-center"></span>
                                        </div>
                                    </div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Printed Name: <span id="bacPrRecommendedPrintedName"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Designation: <span id="bacPrRecommendedDesignation"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Date: <span id="bacPrRecommendedDate"></span></div>
                                </div>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Approved by (BAC):</span>
                                <div class="mt-6 space-y-3 text-xs uppercase tracking-wide text-gray-600">
                                    <div class="border-b border-gray-500 pb-2">
                                        <div class="flex min-h-10 items-end gap-2">
                                            <span class="w-20 shrink-0">Signature:</span>
                                            <span id="bacPrApprovedSignature" class="block flex-1 text-center"></span>
                                        </div>
                                    </div>
                                    <div class="border-b border-gray-500 pb-2 whitespace-nowrap">Printed Name: <span id="bacPrApprovedPrintedName"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Designation: <span id="bacPrApprovedDesignation"></span></div>
                                    <div class="border-b border-gray-500 pb-2">Date: <span id="bacPrApprovedDate"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BAC Action Buttons --}}
                <div class="rounded-xl border-2 border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <h4 class="text-lg font-bold text-amber-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-gavel"></i>
                        BAC Actions
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="bacFundCluster" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                                <i class="fas fa-coins mr-1"></i>Fund Cluster
                            </label>
                            <input type="text" id="bacFundCluster" name="fund_cluster" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all" placeholder="e.g., 101" />
                        </div>
                        <div>
                            <label for="bacFundsAvailable" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                                <i class="fas fa-peso-sign mr-1"></i>Funds Available
                            </label>
                            <input type="number" min="0" step="0.01" id="bacFundsAvailable" name="funds_available" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all" placeholder="0.00" />
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bacRemarks" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            <i class="fas fa-comment mr-1"></i>Remarks <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        <textarea id="bacRemarks" rows="3" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#1a3a2d] focus:border-transparent transition-all resize-y" placeholder="Optional notes..."></textarea>
                    </div>

                    {{-- Error Display Area --}}
                    <div id="bacModalError" class="hidden mb-4 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-lg">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-rose-500 text-lg mt-0.5"></i>
                            <div class="flex-1">
                                <p class="font-semibold text-rose-800 text-sm mb-1">Validation Error</p>
                                <p id="bacModalErrorMessage" class="text-rose-700 text-sm"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" id="bacBtnMoveToReview" data-loading-on-click="true" data-loading-text="Moving to approval..." class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-amber-500 text-white font-semibold text-sm hover:bg-amber-600 transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-clipboard-check"></i>
                            Move to For Approval
                        </button>
                        
                        <button type="button" id="bacBtnApprove" data-loading-on-click="true" data-loading-text="Approving..." class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-check-circle"></i>
                            Approve
                        </button>
                        
                        <button type="button" id="bacBtnCancel" data-loading-on-click="true" data-loading-text="Canceling request..." class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-rose-600 text-white font-semibold text-sm hover:bg-rose-700 transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-times-circle"></i>
                            Cancel Request
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50 shadow-inner">
                <button type="button" class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
                    <i class="fas fa-times mr-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Alternative Suggestion Modal --}}
<div id="bacAlternativeModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="bacAlternativeModalTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" data-close-alternative-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md rounded-t-2xl">
                <div>
                    <h3 id="bacAlternativeModalTitle" class="text-xl font-bold">Suggest Alternative Item</h3>
                    <p class="text-sm text-white/90 mt-1">The requester will be notified to accept or wait</p>
                </div>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-alternative-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="bacAlternativeForm" class="p-6 space-y-4">
                <input type="hidden" id="alternativePriId" name="pri_id" value="">
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                        <i class="fas fa-box mr-1"></i>Original Item
                    </label>
                    <div id="alternativeOriginalItem" class="p-3 bg-gray-100 rounded-lg text-sm text-gray-700 border border-gray-200"></div>
                </div>

                <div>
                    <label for="alternateDescription" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                        <i class="fas fa-exchange-alt mr-1"></i>Alternative Item Description <span class="text-rose-500">*</span>
                    </label>
                    <textarea id="alternateDescription" name="alternate_description" rows="3"
                        class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-y"
                        placeholder="Enter the alternative item description..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="alternativeUnitCost" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            <i class="fas fa-peso-sign mr-1"></i>Unit Cost <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="alternativeUnitCost" name="unit_cost" min="0" step="0.01"
                            class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                            <i class="fas fa-calculator mr-1"></i>Total Cost
                        </label>
                        <div id="alternativeTotalCost" class="w-full border-2 border-gray-200 bg-gray-50 rounded-lg px-3 py-2.5 text-right text-gray-700 font-medium">
                            ₱0.00
                        </div>
                    </div>
                </div>

                <div>
                    <label for="alternativeRemarks" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 mb-2">
                        <i class="fas fa-comment mr-1"></i>Remarks <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <textarea id="alternativeRemarks" name="remarks" rows="2"
                        class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-y"
                        placeholder="Additional notes for the requester..."></textarea>
                </div>

                <div id="alternativeModalError" class="hidden p-4 bg-rose-50 border-l-4 border-rose-500 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-rose-500 text-lg mt-0.5"></i>
                        <div class="flex-1">
                            <p class="font-semibold text-rose-800 text-sm mb-1">Error</p>
                            <p id="alternativeModalErrorMessage" class="text-rose-700 text-sm"></p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" class="flex-1 px-4 py-3 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition-all" data-close-alternative-modal>
                        Cancel
                    </button>
                    <button type="submit" id="bacBtnSubmitAlternative" data-loading-text="Sending suggestion..." class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-all shadow-md">
                        <i class="fas fa-paper-plane"></i>
                        Send Suggestion
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
