<div id="inventoryCreateModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="modal-panel relative bg-white w-full max-w-6xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex-shrink-0 flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight uppercase">Generate Property Acknowledgement Receipt</h3>
                    <p class="text-sm text-white/90 mt-1">Finalize the custodial record for this accepted item.</p>
                </div>
                <button type="button" class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="inventoryCreateForm" class="flex flex-col flex-1 overflow-hidden">
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    <div id="inventoryCreateErrors" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg text-sm shadow-sm"></div>

                    <input type="hidden" name="ia_item_id" id="inventoryIaItemId">

                    <div class="rounded-2xl border-2 border-gray-400 bg-white shadow-sm overflow-hidden">
                        <div class="relative border-b border-gray-400 px-6 pt-6 pb-4">
                            <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">Annex B-1</span>
                            <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                                <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-auto object-contain">
                                <div class="text-center">
                                    <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                                    <h2 class="mt-2 text-2xl font-black uppercase tracking-[0.35em] text-gray-900">Property Acknowledgement Receipt</h2>
                                    <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                                    <p class="text-xs italic text-gray-500">(Agency)</p>
                                </div>
                                <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-auto object-contain">
                            </div>
                        </div>

                        <div class="border-b border-gray-400 bg-gray-50 px-5 py-5">
                            <div class="text-xs font-bold uppercase tracking-wide text-gray-700 mb-4">Inventory Item Snapshot</div>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm text-gray-800">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Description</dt>
                                    <dd id="inventoryItemDescription" class="mt-1 font-medium text-gray-900">—</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Reference</dt>
                                    <dd id="inventoryItemSource" class="mt-1 font-medium text-gray-900">—</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Accepted Quantity</dt>
                                    <dd id="inventoryItemAccepted" class="mt-1 font-medium text-gray-900">—</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Supplier</dt>
                                    <dd id="inventoryItemSupplier" class="mt-1 font-medium text-gray-900">—</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Accountable Officer</dt>
                                    <dd id="inventoryAccountableOfficer" class="mt-1 font-medium text-gray-900">—</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="px-5 py-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Category *</label>
                                    <select name="category_id" id="inventoryCategory" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"></select>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Sub-category *</label>
                                    <select name="sub_category_id" id="inventorySubCategory" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400"></select>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Property Description *</label>
                                <textarea name="property_description" id="inventoryPropertyDescription" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 resize-y"></textarea>
                            </div>

                            <div class="flex flex-col gap-1.5" id="inventorySerialsSection">
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Serial Numbers</label>
                                <div class="rounded border border-dashed border-gray-300 bg-gray-50 px-3 py-3">
                                    <div id="inventorySerialsContainer" class="flex flex-col gap-2"></div>
                                </div>
                                <p class="text-xs text-gray-500">Provide one serial number per unit. Leave blank if a unit does not have a serial.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Unit</label>
                                    <input type="text" name="unit" id="inventoryUnit" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Quantity</label>
                                    <input type="number" min="1" name="quantity" id="inventoryQuantity" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Unit Cost</label>
                                    <input type="number" min="0" step="0.01" name="unit_cost" id="inventoryUnitCost" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Total Cost</label>
                                    <input type="number" min="0" step="0.01" id="inventoryTotalCost" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Date Acquired *</label>
                                    <input type="date" name="date_acquired" id="inventoryDateAcquired" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400">
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Estimated Useful Life</label>
                                    <input type="text" name="estimated_useful_life" id="inventoryUsefulLife" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="e.g. 5 years">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Initial Location</label>
                                    <input type="hidden" name="initial_location_id" id="inventoryInitialLocationId">
                                    <div class="relative">
                                        <input type="text" id="inventoryInitialLocationInput" list="inventoryInitialLocationList" class="w-full rounded-xl border border-emerald-900/15 bg-white px-3 py-2.5 pr-10 text-sm shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500/25 focus:border-emerald-500" placeholder="Search and select location...">
                                        <button type="button" id="inventoryClearLocationBtn" class="absolute right-2.5 top-1/2 hidden -translate-y-1/2 rounded-md px-2 py-1 text-[11px] font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                                            Clear
                                        </button>
                                    </div>
                                    <datalist id="inventoryInitialLocationList"></datalist>
                                    <div id="inventoryLocationSuggestions" class="hidden rounded-xl border border-emerald-900/10 bg-emerald-50/40 p-2.5">
                                        <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/60">Matching Locations</div>
                                        <div id="inventoryLocationSuggestionsList" class="flex flex-wrap gap-2"></div>
                                    </div>
                                    <button type="button" id="inventoryToggleNewLocationBtn" class="inline-flex w-fit items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                        <i class="fas fa-plus text-[10px]"></i>
                                        Add New Location
                                    </button>
                                    <div id="inventoryNewLocationForm" class="hidden rounded-lg border border-emerald-200 bg-emerald-50/40 p-3 space-y-2">
                                        <div class="flex flex-col gap-1">
                                            <label for="inventoryInitialLocationName" class="text-[11px] font-semibold uppercase tracking-wider text-emerald-800">New Location Name</label>
                                            <input type="text" name="initial_location_name" id="inventoryInitialLocationName" class="w-full border border-emerald-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" placeholder="e.g. Building 1 - 2nd Floor">
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label for="inventoryInitialLocationType" class="text-[11px] font-semibold uppercase tracking-wider text-emerald-800">Location Type</label>
                                            <select name="initial_location_type" id="inventoryInitialLocationType" class="w-full border border-emerald-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                                <option value="room">Room</option>
                                                <option value="storage">Storage</option>
                                                <option value="floor">Floor</option>
                                                <option value="building">Building</option>
                                                <option value="other" selected>Other</option>
                                            </select>
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <label for="inventoryInitialLocationParent" class="text-[11px] font-semibold uppercase tracking-wider text-emerald-800">Parent Location</label>
                                            <select name="initial_location_parent_id" id="inventoryInitialLocationParent" class="w-full border border-emerald-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                                                <option value="">No parent location</option>
                                            </select>
                                        </div>
                                        <button type="button" id="inventoryAddLocationBtn" class="inline-flex w-fit items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-800">
                                            <i class="fas fa-plus"></i>
                                            Add Location
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500">Auto-selected from the initial employee section/division when mapped; you may still override if needed.</p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Initial Employee</label>
                                    <input type="text" id="inventoryInitialCustodian" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                                    <p class="text-xs text-gray-500">Automatically assigned from the accountable officer in the source purchase request.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50">
                    <button type="button" class="px-6 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all shadow-sm" data-close-modal>
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" data-loading-text="Saving record..." class="px-6 py-3 rounded-lg bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white font-bold shadow-md hover:shadow-lg hover:scale-105 transition-all">
                        <i class="fas fa-save mr-2"></i>Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="inventoryViewModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" data-close-modal></div>
    
    {{-- This outer wrapper centers the modal and provides safe padding --}}
    <div class="relative flex min-h-screen items-center justify-center p-4">
        
        {{-- FIX: This is the main modal panel, now using a flexbox column layout --}}
        <div class="modal-panel relative flex flex-col w-full max-w-6xl rounded-2xl bg-white shadow-2xl max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            
            {{-- Modal Header (Flex item, does not shrink) --}}
            <div class="flex-shrink-0 bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-8 py-6 text-white">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-3">
                        <span class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Custodial Record</span>
                        <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold">
                            <i class="fas fa-barcode text-white/90"></i>
                            <span id="inventoryViewPropertyNo">—</span>
                        </div>
                        <p class="text-sm text-white/80" id="inventoryViewSource">—</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span id="inventoryViewDocumentBadge" class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm">
                            <i class="fas fa-hourglass-half"></i>
                            Awaiting Document
                        </span>
                        <button type="button" class="rounded-full p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-modal>
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- FIX: This is the scrollable content area that grows to fill available space --}}
            <div class="flex-1 overflow-y-auto px-8 py-8 space-y-8 bg-gray-50/50">
                <section>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Summary</h4>
                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Date Acquired</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewDateAcquired">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Category</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewCategory">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Sub-category</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewSubCategory">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Quantity</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewQuantity">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Cost</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewUnitCost">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Cost</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewTotalCost">—</p>
                        </div>
                    </div>
                </section>

                <section>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Reference Details</h4>
                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">PO No.</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewPoNo">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">IA No.</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewIaNo">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">PR No.</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewPrNo">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Supplier</p>
                            <p class="mt-2 text-base font-semibold text-gray-900" id="inventoryViewSupplier">—</p>
                        </div>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Custodial Document</h4>
                        <p class="mt-3 text-sm font-semibold text-gray-900" id="inventoryViewDocumentMeta">—</p>
                        <p class="mt-2 text-sm text-gray-500" id="inventoryViewDocumentExtra">Generate PQS record to create custodial documents automatically.</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Accountable Officer</h4>
                        <p class="mt-3 text-sm font-semibold text-gray-900" id="inventoryViewOfficer">—</p>
                        <p class="mt-2 text-sm text-gray-500">Useful life: <span id="inventoryViewUsefulLife">—</span></p>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Property Description</h4>
                        <p class="mt-3 text-sm leading-relaxed text-gray-700" id="inventoryViewDescription">—</p>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Serial Numbers</h4>
                        <ul class="mt-3 space-y-2 text-sm text-gray-700" id="inventoryViewSerials">
                            <li class="text-gray-400 italic">No serial numbers recorded.</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">Remarks</h4>
                        <p class="mt-3 text-sm text-gray-700" id="inventoryViewRemarks">No additional remarks recorded.</p>
                    </div>
                </section>
            </div>

            {{-- FIX: Modal Footer (Flex item, does not shrink, always visible) --}}
            <div class="flex-shrink-0 flex items-center justify-end gap-3 border-t bg-gray-50 px-8 py-4">
                <button type="button" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100" data-close-modal>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="inventorySuccessModal" class="fixed inset-0 z-[60] hidden opacity-0 transition-opacity duration-300" aria-labelledby="inventorySuccessTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="modal-panel w-full max-w-md rounded-2xl border border-emerald-950/10 bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h4 id="inventorySuccessTitle" class="text-base font-bold text-[#1a3a2d]">PQS Record Created</h4>
                    <p class="text-xs text-gray-500">The item has been successfully recorded.</p>
                </div>
            </div>

            <div class="px-5 py-4">
                <p id="inventorySuccessMessage" class="text-sm text-gray-700">PQS record created successfully.</p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                <button type="button" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700" data-close-modal>
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<div id="inventoryFeedbackModal" class="fixed inset-0 z-[65] hidden opacity-0 transition-opacity duration-300" aria-labelledby="inventoryFeedbackTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="modal-panel w-full max-w-md rounded-2xl border border-emerald-950/10 bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                <div id="inventoryFeedbackIcon" class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <i class="fas fa-circle-exclamation"></i>
                </div>
                <div>
                    <h4 id="inventoryFeedbackTitle" class="text-base font-bold text-[#1a3a2d]">Notice</h4>
                    <p id="inventoryFeedbackSubtitle" class="text-xs text-gray-500">Please review this message.</p>
                </div>
            </div>

            <div class="px-5 py-4">
                <p id="inventoryFeedbackMessage" class="text-sm text-gray-700">Something needs your attention.</p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                <button type="button" id="inventoryFeedbackClose" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600" data-close-modal>
                    OK
                </button>
            </div>
        </div>
    </div>
</div>