<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICS Print - {{ $icsRecord->ics_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 0.25in; /* smaller margins to maximize content */
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0; /* remove body margin for print */
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
        }
        .border-black {
            border-color: #000 !important;
        }
        /* Print improvements: keep content within one page */
        @media print {
            html, body { height: 100%; margin: 0; padding: 0; overflow: hidden !important; }
            .no-print { display: none !important; }
            .print-card {
                box-sizing: border-box;
                border: none !important; /* remove border in print */
                border-radius: 0 !important;
                height: calc(100vh - 0.5in) !important; /* reduce to ensure fit */
                max-height: calc(100vh - 0.5in) !important;
                overflow: visible !important;
                page-break-inside: avoid;
                -webkit-page-break-inside: avoid;
                break-inside: avoid;
                padding: 0.25in !important;
                width: calc(100% - 0.5in) !important; /* use full width */
            }
            .print-card img { height: 44px !important; width: auto !important; }
            .print-card .overflow-auto { overflow: visible !important; max-height: none !important; }
            table { border-collapse: collapse; }
            table th, table td { padding: .25rem !important; font-size: .78rem !important; }
        }
    </style>
</head>
<body class="bg-white text-black p-8 text-sm font-sans">
    <div class="max-w-[11in] mx-auto border-2 border-black p-6 rounded-lg relative h-auto flex flex-col print-card" style="width: 10in; height: 7.5in;">@php $printDate = $icsRecord->pqsRecord && optional($icsRecord->pqsRecord->date_acquired) ? optional($icsRecord->pqsRecord->date_acquired)->format('M d, Y') : now()->format('M d, Y'); @endphp
        {{-- Header --}}
        <div class="text-center mb-6 relative">
            <div class="absolute left-0 top-0">
                 <img src="{{ asset('images/bpi-logo.png') }}" alt="BPI Logo" class="h-20 w-20 object-contain">
            </div>
            <div class="absolute right-0 top-0">
                 <img src="{{ asset('images/pqslogo.png') }}" alt="PQS Logo" class="h-20 w-20 object-contain">
            </div>
            
            <h1 class="font-bold text-xl uppercase tracking-widest mb-1">Inventory Custodian Slip</h1>
            <h2 class="font-semibold text-sm uppercase tracking-wide">Department of Agriculture - Bureau of Plant Industry</h2>
            <h3 class="font-semibold text-sm uppercase tracking-wide">Plant Quarantine Service</h3>
        </div>

        {{-- Info --}}
        <div class="flex justify-between items-end mb-4 border-b-2 border-black pb-2">
            <div class="w-2/3">
                <div class="flex items-center gap-2">
                    <span class="font-bold whitespace-nowrap">ENTITY NAME:</span>
                    <span class="border-b border-black flex-grow px-2">Department of Agriculture - Bureau of Plant Industry</span>
                </div>
            </div>
            <div class="w-1/3 pl-8">
                <div class="flex items-center gap-2">
                    <span class="font-bold whitespace-nowrap">FUND CLUSTER:</span>
                    <span class="border-b border-black flex-grow px-2">01</span>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
             <div class="flex items-center gap-2 w-1/3">
                <span class="font-bold whitespace-nowrap">ICS NO.:</span>
                <span class="border-b border-black flex-grow px-2 font-mono font-bold">{{ $icsRecord->ics_no }}</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 overflow-auto">
            @php
                // Compute dynamic table rows to avoid overflowing the page
                $rawDescription = strip_tags((string) $icsRecord->description);
                $maxDescriptionChars = 160; // limit description length for print to keep layout
                if (mb_strlen($rawDescription) > $maxDescriptionChars) {
                    $displayDescription = mb_substr($rawDescription, 0, $maxDescriptionChars - 3) . '...';
                } else {
                    $displayDescription = $rawDescription;
                }
                $lineBreaks = max(1, substr_count($displayDescription, "\n") + 1);
                $charsPerLine = 120; // larger estimation reduces wrapped lines
                $wrappedLines = intval(ceil(strlen($displayDescription) / max(1, $charsPerLine)));
                $contentRows = max(1, max($lineBreaks, $wrappedLines));
                $baseRows = 1; // the main row(s) for this record
                $maxRows = 6; // maximum rows to show in the table for print (reduced to fit page)
                $fillerRows = max(0, $maxRows - ($baseRows + $contentRows - 1));
            @endphp

            <table class="w-full border-collapse border border-black mb-4 h-full">
                <thead>
                    <tr class="text-center font-bold text-xs bg-gray-100">
                        <th class="border border-black p-2 w-16">QUANTITY</th>
                        <th class="border border-black p-2 w-16">UNIT</th>
                        <th class="border border-black p-2 w-24">UNIT COST</th>
                        <th class="border border-black p-2 w-24">TOTAL COST</th>
                        <th class="border border-black p-2">DESCRIPTION</th>
                        <th class="border border-black p-2 w-40">INVENTORY ITEM NO.</th>
                        <th class="border border-black p-2 w-24">ESTIMATED USEFUL LIFE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center align-top">
                        <td class="border border-black p-2">{{ number_format($icsRecord->quantity) }}</td>
                        <td class="border border-black p-2">{{ $icsRecord->unit }}</td>
                        <td class="border border-black p-2">{{ number_format($icsRecord->unit_cost, 2) }}</td>
                        <td class="border border-black p-2">{{ number_format($icsRecord->total_cost, 2) }}</td>
                        <td class="border border-black p-2 text-left">
                            {{ $displayDescription }} // truncated for print, full description available in PQS record
                            @if($icsRecord->pqsRecord && $icsRecord->pqsRecord->property_no)
                                <br><span class="text-xs">(SN: {{ $icsRecord->pqsRecord->property_no }})</span>
                            @endif
                        </td>
                        <td class="border border-black p-2">{{ $icsRecord->pqsRecord->property_no ?? 'N/A' }}</td>
                        <td class="border border-black p-2">{{ $icsRecord->estimated_useful_life }}</td>
                    </tr>
                    {{-- Fill remaining height with empty rows if needed, or just let the one row expand --}}
                    @for($i = 0; $i < $fillerRows; $i++)
                    <tr class="text-center align-top h-8">
                        <td class="border border-black p-2"></td>
                        <td class="border border-black p-2"></td>
                        <td class="border border-black p-2"></td>
                        <td class="border border-black p-2"></td>
                        <td class="border border-black p-2"></td>
                        <td class="border border-black p-2"></td>
                        <td class="border border-black p-2"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="flex justify-between gap-12 mt-auto">
            <div class="w-1/2">
                <p class="font-bold text-xs mb-6">RECEIVED BY:</p>

                <div class="mb-1">
                    <div class="border-b border-black h-8 flex items-end justify-center pb-1">
                        @if($icsRecord->pqsRecord && $icsRecord->pqsRecord->accountableOfficer)
                            <span class="font-bold text-xs uppercase">{{ $icsRecord->pqsRecord->accountableOfficer->full_name }}</span>
                        @endif
                    </div>
                    <p class="text-xs mb-4">Signature over Printed Name</p>
                </div>

                <div class="mb-1">
                    <div class="border-b border-black h-4 flex items-end pb-0.5">
                        @if($icsRecord->pqsRecord && $icsRecord->pqsRecord->accountableOfficer && $icsRecord->pqsRecord->accountableOfficer->position)
                            <span class="text-xs">{{ $icsRecord->pqsRecord->accountableOfficer->position->position_name }}</span>
                        @endif
                    </div>
                    <p class="text-xs mb-6">Position/Office</p>
                </div>

                <div class="mb-1">
                    <div class="border-b border-black h-8"></div>
                    <p class="text-xs text-center">Date</p>
                    <p class="text-xs mb-1 font-semibold text-center">{{ $printDate }}</p>
                </div>
            </div>

            <div class="w-1/2">
                <p class="font-bold text-xs mb-6">RECEIVED FROM:</p>
                
                <div class="mb-1">
                    <div class="border-b border-black h-8"></div>
                    <p class="text-xs mb-4">Signature over Printed Name</p>
                </div>

                <div class="mb-1">
                    <div class="border-b border-black h-4"></div>
                    <p class="text-xs mb-6">Position/Office</p>
                </div>

                <div class="mb-1">
                    <div class="border-b border-black h-8"></div>
                    <p class="text-xs text-center">Date</p>
                    <p class="text-xs mb-1 font-semibold text-center">{{ $printDate }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-4 right-4 no-print flex gap-2">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2">
            <i class="fas fa-print"></i> Print
        </button>
        <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 flex items-center gap-2">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>
</html>