<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Acknowledgment Receipt Registry</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 0.5in;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
        .border-black {
            border-color: #000 !important;
        }
        body {
            font-family: 'Arial', sans-serif;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Header Section with Logos -->
    <div class="mb-6 border-b-2 border-black pb-4">
        <div class="flex items-center justify-between">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
            <div class="flex-1 text-center">
                <h1 class="text-2xl font-bold uppercase">Property Acknowledgment Receipt Registry</h1>
                <p class="text-sm mt-1">Comprehensive PAR Report</p>
                @if($search || !empty($categoryFilters) || $assignmentFilter)
                <p class="text-xs text-gray-600 mt-2">
                    Filters Applied:
                    @if($search) Search: "{{ $search }}" @endif
                    @if(!empty($categoryFilters)) | Categories: {{ count($categoryFilters) }} selected @endif
                    @if($assignmentFilter) | Assignment: {{ ucfirst($assignmentFilter) }} @endif
                </p>
                @endif
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    @php
        $recordsPerPage = 15;
        $chunks = $records->chunk($recordsPerPage);
        $totalRecords = $records->count();
        $totalAmount = $records->sum('amount');
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        @if($chunkIndex > 0)
            <div class="page-break"></div>
            <!-- Repeat header on new page -->
            <div class="mb-4 text-center border-b border-black pb-2">
                <h2 class="text-lg font-bold">PAR Registry (continued)</h2>
                <p class="text-xs text-gray-600">Page {{ $chunkIndex + 1 }} of {{ $chunks->count() }}</p>
            </div>
        @endif

        <!-- Table -->
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-gray-100 border-black">
                    <th class="border border-black px-2 py-2 text-left font-semibold w-24">PAR No.</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-24">Property No.</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-40">Description</th>
                    <th class="border border-black px-2 py-2 text-center font-semibold w-20">Date Acquired</th>
                    <th class="border border-black px-2 py-2 text-center font-semibold w-16">Quantity</th>
                    <th class="border border-black px-2 py-2 text-right font-semibold w-20">Unit Value</th>
                    <th class="border border-black px-2 py-2 text-right font-semibold w-24">Amount</th>
                    <th class="border border-black px-2 py-2 text-left font-semibold w-32">Accountable Officer</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chunk as $record)
                <tr class="hover:bg-gray-50">
                    <td class="border border-black px-2 py-1.5 text-xs">{{ $record->par_no }}</td>
                    <td class="border border-black px-2 py-1.5 text-xs">{{ $record->pqsRecord ? $record->pqsRecord->property_no : '—' }}</td>
                    <td class="border border-black px-2 py-1.5 text-xs">
                        {{ Str::limit($record->article_desc, 60) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center text-xs">
                        {{ $record->pqsRecord && $record->pqsRecord->date_acquired ? $record->pqsRecord->date_acquired->format('m/d/Y') : '—' }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-center text-xs">
                        {{ number_format($record->quantity) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right text-xs">
                        ₱{{ number_format($record->unit_value, 2) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-right text-xs font-semibold">
                        ₱{{ number_format($record->amount, 2) }}
                    </td>
                    <td class="border border-black px-2 py-1.5 text-xs">
                        {{ $record->pqsRecord && $record->pqsRecord->accountableOfficer ? $record->pqsRecord->accountableOfficer->full_name : 'Unassigned' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="border border-black px-2 py-4 text-center text-gray-500">
                        No records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($chunkIndex === $chunks->count() - 1)
        <!-- Summary on last page -->
        <div class="mt-6 border-t-2 border-black pt-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-semibold">Total Records: {{ number_format($totalRecords) }}</p>
                    <p class="text-sm font-semibold mt-1">Total Amount: ₱{{ number_format($totalAmount, 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-600">Prepared by:</p>
                    <div class="border-t border-black mt-8 pt-1 px-4">
                        <p class="text-xs font-semibold">{{ Auth::user()->full_name ?? 'System Administrator' }}</p>
                        <p class="text-xs text-gray-600">{{ Auth::user()->position->position_name ?? 'Administrator' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <!-- Print Controls (screen only) -->
    <div class="fixed bottom-4 right-4 no-print flex gap-2">
        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 flex items-center gap-2">
            <i class="fas fa-print"></i> Print
        </button>
    </div>

</body>
</html>
