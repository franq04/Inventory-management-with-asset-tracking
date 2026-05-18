<div id="pqsViewModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">

    <style>
        .pqs-template {
            position: relative;
            width: 100%;
            /* On-screen view should not force a large print height. */
            min-height: auto;
            background-repeat: no-repeat;
            background-position: center top;
            background-size: 100% 100%;
        }

        .pqs-template-par {
            background-image: url("{{ asset('images/PAR.jpg') }}");
        }

        .pqs-template-ics {
            background-image: url("{{ asset('images/ICS.jpg') }}");
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }
            body.pqs-printing {
                background: #fff;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            body.pqs-printing #sidebar,
            body.pqs-printing header,
            body.pqs-printing #main-content > *:not(main) {
                display: none !important;
            }
            body.pqs-printing #main-content > main > *:not(#pqsViewModal) {
                display: none !important;
            }
            body.pqs-printing #pqsViewModal {
                position: static !important;
                inset: auto !important;
                display: block !important;
                opacity: 1 !important;
                background: transparent !important;
            }
            body.pqs-printing #pqsViewModal .pqs-modal-overlay {
                display: none !important;
            }
            body.pqs-printing #pqsViewModal .pqs-modal-shell {
                max-height: none !important;
                max-width: none !important;
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                border: none !important;
                height: auto !important;
            }
            body.pqs-printing #pqsViewModal .pqs-modal-shell > *:not(.flex-1) {
                display: none !important;
            }
            body.pqs-printing #pqsViewModal .flex-1 {
                overflow: visible !important;
                background: #fff !important;
                padding: 0 !important;
                height: auto !important;
            }
            body.pqs-printing #pqsViewModal .flex-1 > :not(.pqs-print-wrapper) {
                display: none !important;
            }
            body.pqs-printing #pqsViewModal .pqs-print-wrapper {
                display: block !important;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body.pqs-printing #pqsViewModal .pqs-print-wrapper > *:not(#pqsPrintArea) {
                display: none !important;
            }
            body.pqs-printing #pqsPrintArea {
                display: block !important;
                border-radius: 0 !important;
                border: none !important;
                box-shadow: none !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: none !important;
                min-height: 100vh !important;
                overflow: visible !important;
            }
            body.pqs-printing #pqsParTemplate,
            body.pqs-printing #pqsIcsTemplate {
                width: 8.5in !important;
                min-height: 13in !important;
                margin: 0 auto !important;
                padding: 0.75in 0.85in 1in !important;
            }
            body.pqs-printing #pqsPrintArea table,
            body.pqs-printing #pqsPrintArea th,
            body.pqs-printing #pqsPrintArea td {
                background: transparent !important;
            }
            body.pqs-printing #pqsViewModal button,
            body.pqs-printing #pqsViewModal [data-close-modal] {
                display: none !important;
            }
        }
    </style>

    <div class="pqs-modal-overlay absolute inset-0 bg-slate-950/60 backdrop-blur-sm" data-close-modal></div>

    {{-- FIX: Outer wrapper now centers the modal and provides safe padding --}}
    <div class="relative flex min-h-screen items-center justify-center p-3 sm:p-4">

        {{-- FIX: Main modal panel now uses flexbox and max-height to ensure it fits the screen --}}
        <div class="pqs-modal-shell modal-panel relative flex h-[calc(100vh-1.5rem)] w-full max-w-[min(96vw,1700px)] flex-col rounded-2xl bg-white shadow-2xl max-h-[calc(100vh-1.5rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2 sm:h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-2rem)]">

            {{-- Modal Header (Flex item, does not shrink) --}}
            <div class="flex-shrink-0 flex flex-wrap items-center justify-between gap-4 bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-6 py-5 text-white sm:px-8 sm:py-6">
                <div class="space-y-3">
                    <span class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70">Property Custodial Record</span>
                    {{-- REMOVED: Redundant h2 element as requested --}}
                    <div class="flex flex-wrap items-center gap-2 text-sm font-semibold text-white/90">
                        <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-1.5">
                            <i class="fas fa-barcode"></i>
                            <span id="pqsViewPropertyNo">—</span>
                        </div>
                        <div class="inline-flex items-center gap-3 rounded-full bg-white/15 px-4 py-1.5">
                            <i class="fas fa-calendar-day"></i>
                            <span id="pqsViewDateAcquiredChip">—</span>
                        </div>
                        <div id="pqsViewConditionChip" class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-white/95">
                            <i class="fas fa-circle-check"></i>
                            Serviceable
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span id="pqsViewDocumentBadge" class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700 shadow-sm">
                        <i class="fas fa-hourglass-half"></i>
                        Pending Document
                    </span>
                    {{-- Print button removed per request --}}
                    <button type="button" class="rounded-full p-2 text-white/80 transition hover:bg-white/10 hover:text-white" data-close-modal data-focus>
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Scrollable content area: includes print-ready template and on-screen summary --}}
            <div class="flex-1 overflow-y-auto bg-gray-50/50 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto w-full max-w-none space-y-6 pqs-print-wrapper">
                    <div id="pqsPrintArea" class="pqs-detail-section rounded-2xl border-2 border-gray-400 bg-white shadow-sm" style="min-height:auto;">
                        <div id="pqsParTemplate" class="hidden px-6 py-7 text-gray-900 pqs-template pqs-template-par">
                            <div class="relative mb-6 flex items-center justify-center">
                                <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="absolute left-0 top-0 h-16 w-16 object-contain">
                                <div class="text-center">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600">Property Acknowledgment Receipt</p>
                                    <p class="text-[11px] uppercase tracking-[0.2em] text-gray-500">Department of Agriculture • Bureau of Plant Industry</p>
                                    <p class="text-[11px] uppercase tracking-[0.2em] text-gray-500">Plant Quarantine Service</p>
                                </div>
                                <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="absolute right-0 top-0 h-16 w-16 object-contain">
                            </div>

                            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase">Entity Name:</span>
                                    <span id="pqsParEntity" class="flex-1 border-b border-gray-500 pb-0.5 text-sm">Department of Agriculture - Bureau of Plant Industry</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase">Fund Cluster:</span>
                                    <span id="pqsParFundCluster" class="flex-1 border-b border-gray-500 pb-0.5 text-sm">01</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-sm">
                                <span class="text-xs font-semibold uppercase">PAR No.:</span>
                                <span id="pqsParNumber" class="flex-1 border-b border-gray-500 pb-0.5 text-sm">____________________</span>
                            </div>

                            <div class="mt-5 overflow-hidden rounded border border-gray-600">
                                <table class="w-full text-xs text-gray-900">
                                    <thead class="bg-gray-100 uppercase">
                                        <tr>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Quantity</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Unit</th>
                                            <th class="border border-gray-600 px-3 py-2 text-left">Description</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Property Number</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Date Acquired</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="pqsParQuantity" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsParUnit" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsParDescription" class="border border-gray-600 px-3 py-2">—</td>
                                            <td id="pqsParPropertyNo" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsParDateAcquired" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsParAmount" class="border border-gray-600 px-3 py-2 text-right">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-8 text-xs md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <p class="font-semibold uppercase">Received by:</p>
                                    <div class="w-[78%] mx-auto relative">
                                        <img id="pqsParReceivedBySignature" src="" alt="Received by signature" class="hidden absolute left-1/2 -translate-x-1/2 bottom-full mb-0.5 h-8 w-auto max-w-[220px] object-contain">
                                        <span id="pqsParReceivedBy" class="block border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    </div>
                                    <span class="block text-[11px] text-gray-600 text-center">Signature over Printed Name</span>
                                    <span id="pqsParReceivedByPosition" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Position/Office</span>
                                    <span id="pqsParReceivedByDate" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Date</span>
                                </div>
                                <div class="space-y-1.5">
                                    <p class="font-semibold uppercase">Received from:</p>
                                    <span id="pqsParReceivedFrom" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Signature over Printed Name</span>
                                    <span id="pqsParReceivedFromPosition" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Position/Office</span>
                                    <span id="pqsParReceivedFromDate" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Date</span>
                                </div>
                            </div>
                        </div>

                        <div id="pqsIcsTemplate" class="hidden px-6 py-7 text-gray-900 pqs-template pqs-template-ics">
                            <div class="relative mb-6 flex items-center justify-center">
                                <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="absolute left-0 top-0 h-16 w-16 object-contain">
                                <div class="text-center">
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-600">Inventory Custodian Slip</p>
                                    <p class="text-[11px] uppercase tracking-[0.2em] text-gray-500">Department of Agriculture • Bureau of Plant Industry</p>
                                    <p class="text-[11px] uppercase tracking-[0.2em] text-gray-500">Plant Quarantine Service</p>
                                </div>
                                <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="absolute right-0 top-0 h-16 w-16 object-contain">
                            </div>

                            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase">Entity Name:</span>
                                    <span id="pqsIcsEntity" class="flex-1 border-b border-gray-500 pb-0.5 text-sm">Department of Agriculture - Bureau of Plant Industry</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold uppercase">Fund Cluster:</span>
                                    <span id="pqsIcsFundCluster" class="flex-1 border-b border-gray-500 pb-0.5 text-sm">01</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2 text-sm">
                                <span class="text-xs font-semibold uppercase">ICS No.:</span>
                                <span id="pqsIcsNumber" class="flex-1 border-b border-gray-500 pb-0.5 text-sm">____________________</span>
                            </div>

                            <div class="mt-5 overflow-hidden rounded border border-gray-600">
                                <table class="w-full text-xs text-gray-900">
                                    <thead class="bg-gray-100 uppercase">
                                        <tr>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Quantity</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Unit</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Unit Cost</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Total Cost</th>
                                            <th class="border border-gray-600 px-3 py-2 text-left">Description</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Inventory Item No.</th>
                                            <th class="border border-gray-600 px-3 py-2 text-center">Estimated Useful Life</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td id="pqsIcsQuantity" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsIcsUnit" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsIcsUnitCost" class="border border-gray-600 px-3 py-2 text-right">—</td>
                                            <td id="pqsIcsTotalCost" class="border border-gray-600 px-3 py-2 text-right">—</td>
                                            <td id="pqsIcsDescription" class="border border-gray-600 px-3 py-2">—</td>
                                            <td id="pqsIcsInventoryNo" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                            <td id="pqsIcsUsefulLife" class="border border-gray-600 px-3 py-2 text-center">—</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-8 text-xs md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <p class="font-semibold uppercase">Received from:</p>
                                    <span id="pqsIcsReceivedFrom" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Signature over Printed Name</span>
                                    <span id="pqsIcsReceivedFromPosition" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Position/Office</span>
                                    <span id="pqsIcsReceivedFromDate" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Date</span>
                                </div>
                                <div class="space-y-1.5">
                                    <p class="font-semibold uppercase">Received by:</p>
                                    <div class="w-[78%] mx-auto relative">
                                        <img id="pqsIcsReceivedBySignature" src="" alt="Received by signature" class="hidden absolute left-1/2 -translate-x-1/2 bottom-full mb-0.5 h-8 w-auto max-w-[220px] object-contain">
                                        <span id="pqsIcsReceivedBy" class="block border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    </div>
                                    <span class="block text-[11px] text-gray-600 text-center">Signature over Printed Name</span>
                                    <span id="pqsIcsReceivedByPosition" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Position/Office</span>
                                    <span id="pqsIcsReceivedByDate" class="block w-[78%] mx-auto border-b border-gray-500 pb-0.5 text-sm text-center">____________________</span>
                                    <span class="block text-[11px] text-gray-600 text-center">Date</span>
                                </div>
                            </div>
                        </div>

                        <div id="pqsDefaultTemplate" class="flex h-64 items-center justify-center px-6 text-center text-sm text-gray-500">
                            This record does not have a PAR or ICS document yet. Generate a custodial document to preview the official form.
                        </div>
                    </div>

                    <div class="space-y-6">
                        <section class="pqs-detail-section">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Property Record Snapshot</h4>
                            <div class="mt-4 grid grid-cols-1 gap-x-8 gap-y-4 text-sm text-gray-800 md:grid-cols-2">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Article</p>
                                    <p id="pqsViewSnapshotArticle" class="mt-1 font-semibold text-gray-900">—</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Property Number</p>
                                    <p id="pqsViewPropertyNoSnapshot" class="mt-1 font-semibold text-gray-900">—</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Acquisition Date</p>
                                    <p id="pqsViewSnapshotDate" class="mt-1 font-semibold text-gray-900">—</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Assignment Document</p>
                                    <p id="pqsViewSnapshotDocument" class="mt-1 font-semibold text-gray-900">—</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Accountable Officer</p>
                                    <p id="pqsViewOfficer" class="mt-1 font-semibold text-gray-900">—</p>
                                    <p class="text-xs text-gray-500">Employee ID: <span id="pqsViewOfficerId">—</span></p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Quantity &amp; Unit</p>
                                    <p id="pqsViewSnapshotQuantity" class="mt-1 font-semibold text-gray-900">—</p>
                                </div>
                            </div>
                        </section>

                        <section class="pqs-detail-section">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Valuation Summary</h4>
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Date Acquired</p>
                                    <p id="pqsViewDateAcquired" class="mt-2 text-base font-semibold text-gray-900">—</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Category</p>
                                    <p id="pqsViewCategory" class="mt-2 text-base font-semibold text-gray-900">—</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Unit</p>
                                    <p id="pqsViewUnit" class="mt-2 text-base font-semibold text-gray-900">—</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Quantity</p>
                                    <p id="pqsViewQuantity" class="mt-2 text-base font-semibold text-gray-900">—</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Unit Cost</p>
                                    <p id="pqsViewUnitCost" class="mt-2 text-base font-semibold text-gray-900">—</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Value</p>
                                    <p id="pqsViewTotalCost" class="mt-2 text-base font-semibold text-gray-900">—</p>
                                </div>
                            </div>
                        </section>

                        <section class="pqs-detail-section grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Custodial Documentation</h4>
                                <p id="pqsViewDocumentMeta" class="mt-3 text-sm font-semibold text-gray-900">No custodial document generated yet.</p>
                                <p id="pqsViewDocumentExtra" class="mt-2 text-sm text-gray-500">Generate PQS record to create custodial documents automatically.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Property Description</h4>
                                <p id="pqsViewDescription" class="mt-3 text-sm leading-relaxed text-gray-700">—</p>
                            </div>
                        </section>

                        <section class="pqs-detail-section grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Serial Numbers</h4>
                                <ul id="pqsViewSerials" class="mt-3 space-y-2 text-sm text-gray-700">
                                    <li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>
                                </ul>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks</h4>
                                <p id="pqsViewRemarks" class="mt-3 text-sm text-gray-700">No additional remarks recorded.</p>
                            </div>
                        </section>

                        <section class="pqs-detail-section rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Current Assignment</h4>
                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-gray-700 sm:grid-cols-2 lg:grid-cols-3">
                                <p><span class="font-semibold text-gray-500">Location:</span> <span id="pqsViewCurrentLocation">Unassigned</span></p>
                                <p><span class="font-semibold text-gray-500">Current Employee:</span> <span id="pqsViewCurrentCustodian">Unassigned</span></p>
                                <p><span class="font-semibold text-gray-500">Division:</span> <span id="pqsViewCurrentDivision">Unassigned</span></p>
                                <p><span class="font-semibold text-gray-500">Section:</span> <span id="pqsViewCurrentSection">Unassigned</span></p>
                                <p><span class="font-semibold text-gray-500">Last Movement:</span> <span id="pqsViewLastMovement">—</span></p>
                                <p><span class="font-semibold text-gray-500">Condition:</span> <span id="pqsViewCurrentCondition">Serviceable</span></p>
                            </div>
                        </section>

                        <section id="pqsActionWorkspace" class="hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

                            <div id="pqsActionTransferPane" class="mt-1">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Movement</h4>
                                <form id="pqsTransferForm" class="mt-3 space-y-3">
                                    <div>
                                        <label for="pqsTransferMovementType" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Movement Type</label>
                                        <select id="pqsTransferMovementType" name="movement_type" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                                            <option value="transfer">Transfer</option>
                                        </select>
                                        <p data-transfer-error-for="movement_type" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>

                                    <div>
                                        <label for="pqsTransferCustodian" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Target Employee (Optional)</label>
                                        <div class="relative mt-1">
                                            <button id="pqsTransferCustodianToggle" type="button" class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                                <span id="pqsTransferCustodianLabel">Keep current: Unassigned owner</span>
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </button>
                                            <div id="pqsTransferCustodianPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                                                <input id="pqsTransferCustodianSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search employee...">
                                                <ul id="pqsTransferCustodianList" class="mt-2 max-h-44 overflow-y-auto rounded-lg border border-gray-100"></ul>
                                            </div>
                                        </div>
                                        <select id="pqsTransferCustodian" name="to_custodian_employee_id" class="hidden">
                                            <option value="">Keep current: Unassigned owner</option>
                                            @foreach($transferCustodians as $employee)
                                                <option value="{{ $employee->employee_id }}">
                                                    {{ $employee->full_name }} ({{ $employee->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">Assign only when custody should move to another employee.</p>
                                        <p data-transfer-error-for="to_custodian_employee_id" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>

                                    <div>
                                        <label for="pqsTransferLocation" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Target Location</label>
                                        <div class="relative mt-1">
                                            <button id="pqsTransferLocationToggle" type="button" class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                                <span id="pqsTransferLocationLabel">Keep current: Unassigned location</span>
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </button>
                                            <div id="pqsTransferLocationPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                                                <input id="pqsTransferLocationSearch" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Search location...">
                                                <ul id="pqsTransferLocationList" class="mt-2 max-h-44 overflow-y-auto rounded-lg border border-gray-100"></ul>
                                            </div>
                                        </div>
                                        <select id="pqsTransferLocation" name="to_location_id" class="hidden">
                                            <option value="">Keep current: Unassigned location</option>
                                            @foreach($transferLocations as $location)
                                                <option value="{{ $location->location_id }}" data-division-id="{{ $location->division_id }}" data-section-id="{{ $location->section_id }}">
                                                    {{ $location->location_name }}{{ $location->location_code ? ' ('.$location->location_code.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p data-transfer-error-for="to_location_id" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>

                                    <div>
                                        <label for="pqsTransferReason" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Reason Code</label>
                                        <input id="pqsTransferReason" name="reason_code" type="text" maxlength="100" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="optional reason code">
                                        <p data-transfer-error-for="reason_code" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>

                                    <div>
                                        <label for="pqsTransferRemarks" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks</label>
                                        <textarea id="pqsTransferRemarks" name="remarks" rows="2" maxlength="1000" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="optional notes"></textarea>
                                        <p data-transfer-error-for="remarks" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>

                                    <div id="pqsTransferError" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>
                                    <div id="pqsTransferBlockedNotice" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800"></div>

                                    <button id="pqsTransferSubmit" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#285641]">
                                        <i class="fas fa-random"></i>
                                        Save Movement
                                    </button>
                                </form>
                            </div>

                            <div id="pqsActionConditionPane" class="mt-4 hidden rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-50 to-white p-4 shadow-sm">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-rose-700">Asset Serviceability</h4>
                                <p class="mt-2 text-sm text-gray-600">Use this to mark the current asset as serviceable, under maintenance, or unserviceable while preserving movement history.</p>

                                <div id="pqsConditionSuccess" class="hidden mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700"></div>
                                <div id="pqsConditionError" class="hidden mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700"></div>

                                <form id="pqsConditionForm" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label for="pqsConditionSelect" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Condition</label>
                                        <div class="relative mt-1">
                                            <button id="pqsConditionSelectToggle" type="button" class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                                <span id="pqsConditionSelectLabel" class="flex items-center gap-2">Select condition</span>
                                                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                            </button>
                                            <div id="pqsConditionSelectPanel" class="absolute z-30 mt-1 hidden w-full rounded-xl border border-gray-200 bg-white p-2 shadow-lg">
                                                <ul id="pqsConditionSelectList" class="space-y-1">
                                                    <li>
                                                        <button type="button" data-condition-option data-value="maintenance" data-label="Under Maintenance" data-chip-class="border-amber-200 bg-amber-50 text-amber-700" data-icon="fa-screwdriver-wrench" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-amber-50/70">
                                                            <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                                                                <i class="fas fa-screwdriver-wrench"></i>
                                                                Under Maintenance
                                                            </span>
                                                            <span class="text-[11px] text-gray-400">Needs estimated date</span>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button type="button" data-condition-option data-value="serviceable" data-label="Serviceable" data-chip-class="border-emerald-200 bg-emerald-50 text-emerald-700" data-icon="fa-circle-check" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-emerald-50/70">
                                                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                                                <i class="fas fa-circle-check"></i>
                                                                Serviceable
                                                            </span>
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button type="button" data-condition-option data-value="unserviceable" data-label="Unserviceable" data-chip-class="border-rose-200 bg-rose-50 text-rose-700" data-icon="fa-triangle-exclamation" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-rose-50/70">
                                                            <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">
                                                                <i class="fas fa-triangle-exclamation"></i>
                                                                Unserviceable
                                                            </span>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <input id="pqsConditionSelect" name="condition" type="hidden" value="">
                                        <p id="pqsConditionSelectError" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>
                                    <div>
                                        <label for="pqsConditionEffectiveAt" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Effective Date</label>
                                        <input id="pqsConditionEffectiveAt" name="effective_at" type="date" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                                    </div>
                                    <div id="pqsConditionExpectedFixDateWrap" class="hidden">
                                        <label for="pqsConditionExpectedFixDate" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estimated Completion Date <span class="text-rose-600">*</span></label>
                                        <input id="pqsConditionExpectedFixDate" name="expected_return_at" type="date" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
                                        <p id="pqsConditionExpectedFixDateError" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="pqsConditionRemarks" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks <span class="text-rose-600">*</span></label>
                                        <textarea id="pqsConditionRemarks" name="remarks" rows="2" maxlength="1000" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Required condition notes"></textarea>
                                        <p id="pqsConditionRemarksError" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                                    </div>

                                    <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                                        <button type="button" id="pqsConditionSubmit" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#285641]">
                                            <i class="fas fa-floppy-disk"></i>
                                            Save Condition
                                        </button>
                                    </div>
                                </form>

                                <div class="mt-4 rounded-xl border border-gray-200 bg-white px-3 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Condition History</p>
                                        <span class="text-[11px] text-gray-400">Latest maintenance events</span>
                                    </div>
                                    <div id="pqsConditionHistory" class="mt-2 space-y-2 text-xs text-gray-700">
                                        <p class="text-gray-400 italic">No condition history yet.</p>
                                    </div>
                                </div>
                            </div>

                        </section>

                        <section class="pqs-detail-section rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Movement Timeline</h4>
                            <div id="pqsMovementTimeline" class="mt-3 space-y-3 text-sm text-gray-700">
                                <p class="text-gray-400 italic">No movement records yet.</p>
                            </div>
                            <div id="pqsMovementTimelinePager" class="mt-3 hidden items-center justify-between gap-3 border-t border-gray-100 pt-3">
                                <button id="pqsTimelinePrev" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                    Previous
                                </button>
                                <p id="pqsTimelinePageInfo" class="text-xs font-medium text-gray-500">Page 1 of 1</p>
                                <button id="pqsTimelineNext" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                                    Next
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            {{-- FIX: Modal Footer (Flex item, does not shrink, always visible) --}}
            <div class="flex-shrink-0 flex flex-wrap items-center justify-center gap-3 border-t bg-gray-50 px-6 py-4 sm:px-8">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button type="button" data-pqs-action-tab="details" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-[#285641]">
                        <i class="fas fa-eye"></i>
                        Details
                    </button>
                    <button type="button" data-pqs-action-tab="transfer" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                        <i class="fas fa-right-left"></i>
                        Transfer
                    </button>
                    <button type="button" data-pqs-action-tab="condition" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50">
                        <i class="fas fa-screwdriver-wrench"></i>
                        Condition
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>