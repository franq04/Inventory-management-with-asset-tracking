<div id="inspectionModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-6xl rounded-2xl shadow-2xl flex flex-col max-h-[calc(100vh-2rem)] overflow-hidden">
            
            <form id="inspectionForm" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                {{-- Header --}}
                <div class="flex-shrink-0 z-10 px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold uppercase tracking-tight">Inspection &amp; Acceptance Report</h3>
                            <p class="text-sm text-white/80 mt-1">Purchase Order <span id="inspectionPoNumber" class="font-semibold">&mdash;</span></p>
                        </div>
                        <button type="button" class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                {{-- Scrollable Content --}}
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6 bg-gray-50">
                    <div id="inspectionModalErrors" class="hidden mb-4 px-4 py-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm"></div>

                    {{-- Main IAR Document Form --}}
                    <div class="border-2 border-gray-400 bg-white shadow-sm">
                        <div class="relative border-b-2 border-gray-400 px-6 pt-6 pb-4">
                            <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">Appendix 62</span>
                            <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                                <img src="{{ asset('images/bpi-logo.png') }}" alt="BPI Logo" class="h-16 w-auto object-contain">
                                <div class="text-center">
                                    <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                                    <h4 class="mt-2 text-lg font-black uppercase tracking-[0.35em] text-gray-900">Inspection &amp; Acceptance Report</h4>
                                    <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                                    <p class="text-xs italic text-gray-500">(Agency)</p>
                                </div>
                                <img src="{{ asset('images/pqslogo.png') }}" alt="PQS Logo" class="h-16 w-auto object-contain">
                            </div>
                        </div>

                        {{-- Top Information Section --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 border-y-2 border-gray-400 text-sm">
                            {{-- Left Column --}}
                            <div class="space-y-2 border-b-2 md:border-b-0 md:border-r-2 border-gray-400 p-4">
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">Entity Name :</label>
                                    <span class="border-b border-dotted border-gray-400">BPI-NPQSD</span>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">Supplier :</label>
                                    <span id="inspectionSupplier" class="border-b border-dotted border-gray-400">&mdash;</span>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">PO No./Date :</label>
                                    <span class="border-b border-dotted border-gray-400">
                                        <span id="inspectionPoNumberDisplay">&mdash;</span> / <span id="inspectionOrderDate">&mdash;</span>
                                    </span>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">Requisitioning Office/Dept. :</label>
                                    <span id="inspectionPrNumber" class="border-b border-dotted border-gray-400">&mdash;</span>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">Responsibility Center Code :</label>
                                    <span class="border-b border-dotted border-gray-400"></span>
                                </div>
                            </div>
                            {{-- Right Column --}}
                            <div class="space-y-2 p-4">
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">Fund Cluster :</label>
                                    <span class="border-b border-dotted border-gray-400">156</span>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-baseline gap-x-2">
                                    <label class="font-semibold">IAR No. :</label>
                                    <span id="inspectionIaNumber" class="border-b border-dotted border-gray-400">&mdash;</span>
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label for="invoiceNo" class="font-semibold">Invoice No. :</label>
                                    <input type="text" id="invoiceNo" name="invoice_no" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0" placeholder="...">
                                </div>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label for="invoiceDate" class="font-semibold">Date :</label>
                                    <input type="date" id="invoiceDate" name="invoice_date" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0">
                                </div>
                            </div>
                        </div>

                        {{-- Items Table (Interactive Version) --}}
                        <div class="border-b-2 border-gray-400">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs border-collapse">
                                    <thead>
                                        <tr class="border-b-2 border-gray-400 bg-gray-50">
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 border-x-2 border-gray-400">Item</th>
                                            <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-700 border-r-2 border-gray-400">Delivered</th>
                                            <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-700 border-r-2 border-gray-400">Accepted</th>
                                            <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider text-gray-700 border-r-2 border-gray-400">Rejected</th>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 border-r-2 border-gray-400">Status</th>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 border-r-2 border-gray-400">Warranty</th>
                                            <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 border-r-2 border-gray-400">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inspectionItems" class="bg-white divide-y divide-gray-200">
                                        {{-- JS will populate this --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Bottom Section (Inspection & Acceptance) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 text-sm">
                            {{-- Inspection Column --}}
                            <div class="space-y-4 border-r-2 border-gray-400 p-4">
                                <h4 class="text-center font-bold">INSPECTION</h4>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label for="inspectionDate" class="font-semibold">Date Inspected :</label>
                                    <input type="date" id="inspectionDate" name="inspection_date" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0" required>
                                </div>
                                <div class="pt-4 flex items-start gap-3">
                                    <div class="w-5 h-5 border border-gray-400 mt-0.5"></div>
                                    <p class="flex-1 text-xs">Inspected, verified and found in order as to quantity and specifications</p>
                                </div>
                                <div class="pt-12 text-center">
                                    <div class="w-full border-b border-gray-500"></div>
                                    <label class="mt-1 block text-xs">Inspection Officer/Inspection Committee</label>
                                </div>
                            </div>
                            {{-- Acceptance Column --}}
                            <div class="space-y-4 p-4">
                                <h4 class="text-center font-bold">ACCEPTANCE</h4>
                                <div class="grid grid-cols-[auto,1fr] items-center gap-x-2">
                                    <label for="acceptedDate" class="font-semibold">Date Received :</label>
                                    <input type="date" id="acceptedDate" name="accepted_date" class="w-full border-0 border-b border-dotted border-gray-400 bg-transparent px-2 text-sm focus:ring-0">
                                </div>
                                <div class="pt-4 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 border border-gray-400"></div>
                                        <label class="text-xs">Complete</label>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 border border-gray-400"></div>
                                        <label class="text-xs">Partial (pls. specify quantity)</label>
                                    </div>
                                </div>
                                <div class="pt-12 text-center">
                                    <div class="w-full border-b border-gray-500"></div>
                                    <label class="mt-1 block text-xs">Supply and/or Property Custodian</label>
                                </div>
                            </div>
                        </div>

                         {{-- Overall Remarks --}}
                        <div class="border-t-2 border-gray-400 p-4">
                            <label for="inspectionRemarks" class="text-xs font-bold uppercase tracking-wider text-gray-700">Overall Remarks</label>
                            <textarea id="inspectionRemarks" name="remarks" class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 resize-y" rows="2" placeholder="Optional summary or observations"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Sticky Footer --}}
                <div class="flex-shrink-0 flex items-center justify-between gap-3 px-6 py-4 border-t bg-gray-50">
                    <p class="text-xs text-gray-600">* Quantities cannot exceed delivered amounts</p>
                    <div class="flex items-center gap-3">
                        <button type="button" class="px-5 py-3 rounded-lg bg-white border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all text-sm shadow-sm" data-close-modal>Cancel</button>
                        <button type="submit" class="px-6 py-3 rounded-lg bg-[#1a3a2d] text-white font-semibold shadow-md hover:bg-[#1a3a2d]/90 transition-all text-sm">
                            <i class="fas fa-save mr-2"></i>Save Inspection
                        </button>
                    </div>
                </div>
            </form>

            <div id="inspectionModalLoader" class="pointer-events-none absolute inset-0 hidden items-center justify-center bg-white/90">
                <div class="flex flex-col items-center gap-3 text-sm font-semibold text-gray-600">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                    Loading inspection data...
                </div>
            </div>
        </div>
    </div>
</div>