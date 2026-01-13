<div id="icsViewModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200 ease-out">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" data-close-modal></div>

    {{-- Outer wrapper centers the modal and provides safe padding --}}
    <div class="relative flex min-h-screen items-center justify-center p-4">

        {{-- Main modal panel uses flexbox and max-height to ensure it fits the screen --}}
        <div class="relative flex w-full max-w-5xl flex-col rounded-2xl bg-white shadow-2xl max-h-[calc(100vh-2rem)]">

            {{-- Modal Header (Flex item, does not shrink) --}}
            <div class="flex-shrink-0 flex flex-wrap items-center justify-between gap-4 bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-8 py-6 text-white">
                <div class="space-y-3">
                    <span class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Inventory Custodian Slip</span>
                    <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-white/90">
                        <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-1.5">
                            <i class="fas fa-barcode"></i>
                            <span id="icsViewPropertyNo">—</span>
                        </div>
                        <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-1.5">
                            <i class="fas fa-file-signature"></i>
                            <span id="icsViewNumber">—</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="#" id="icsPrintButton" target="_blank" class="inline-flex items-center gap-2 rounded-full border border-white/40 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/60">
                        <i class="fas fa-print"></i>
                        Print Record
                    </a>
                    <button type="button" class="rounded-full p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-modal>
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Scrollable content area --}}
            <div class="flex-1 overflow-y-auto bg-gray-50/50 px-6 py-8 sm:px-8">
                <div class="mx-auto w-full max-w-5xl space-y-8">
                    <section>
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Property Record Snapshot</h4>
                        <div class="mt-4 grid grid-cols-1 gap-x-8 gap-y-4 text-sm text-gray-800 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Article</p>
                                <p id="icsViewArticle" class="mt-1 font-semibold text-gray-900">—</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Property Number</p>
                                <p id="icsViewPropertyNoSnapshot" class="mt-1 font-semibold text-gray-900">—</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">ICS Number</p>
                                <p id="icsViewNumberSnapshot" class="mt-1 font-semibold text-gray-900">—</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Accountable Officer</p>
                                <p id="icsViewOfficer" class="mt-1 font-semibold text-gray-900">—</p>
                                <p class="text-xs text-gray-500">Employee ID: <span id="icsViewOfficerId">—</span></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Quantity &amp; Unit</p>
                                <p id="icsViewQuantitySnapshot" class="mt-1 font-semibold text-gray-900">—</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estimated Useful Life</p>
                                <p id="icsViewUsefulLife" class="mt-1 font-semibold text-gray-900">—</p>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Valuation Summary</h4>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Category</p>
                                <p id="icsViewCategory" class="mt-2 text-base font-semibold text-gray-900">—</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Unit</p>
                                <p id="icsViewUnit" class="mt-2 text-base font-semibold text-gray-900">—</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Quantity</p>
                                <p id="icsViewQuantity" class="mt-2 text-base font-semibold text-gray-900">—</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Unit Cost</p>
                                <p id="icsViewUnitCost" class="mt-2 text-base font-semibold text-gray-900">—</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Cost</p>
                                <p id="icsViewTotalCost" class="mt-2 text-base font-semibold text-gray-900">—</p>
                            </div>
                        </div>
                    </section>

                    <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Property Description</h4>
                            <p id="icsViewDescription" class="mt-3 text-sm leading-relaxed text-gray-700">—</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Serial Numbers</h4>
                            <ul id="icsViewSerials" class="mt-3 space-y-2 text-sm text-gray-700">
                                <li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks</h4>
                            <p id="icsViewRemarks" class="mt-3 text-sm text-gray-700">No additional remarks recorded.</p>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Modal Footer (Flex item, does not shrink, always visible) --}}
            <div class="flex-shrink-0 flex items-center justify-end gap-3 border-t bg-gray-50 px-8 py-4">
                <button type="button" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100" data-close-modal>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
