<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Items Report</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            @page { size: landscape; margin: 0.5in; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; }
            .no-print { display: none !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-white p-6 text-gray-900">
    <div class="mb-6 border-b-2 border-black pb-4">
        <div class="flex items-center justify-between">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
            <div class="flex-1 text-center">
                <h1 class="text-2xl font-bold uppercase">Inspection Items Report</h1>
                <p class="mt-1 text-sm">Status View: {{ strtoupper($activeTab ?? 'ALL') }}</p>
                <p class="mt-2 text-xs text-gray-600">Generated on {{ now()->format('F d, Y h:i:s A') }} PHT</p>
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    <table class="w-full border-collapse text-xs">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black px-2 py-2 text-left">IAR No.</th>
                <th class="border border-black px-2 py-2 text-left">PO No.</th>
                <th class="border border-black px-2 py-2 text-left">Item Description</th>
                <th class="border border-black px-2 py-2 text-left">Supplier</th>
                <th class="border border-black px-2 py-2 text-right">Delivered</th>
                <th class="border border-black px-2 py-2 text-right">Accepted</th>
                <th class="border border-black px-2 py-2 text-right">Rejected</th>
                <th class="border border-black px-2 py-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $latest = $item->latestInspectionItem;
                @endphp
                <tr>
                    <td class="border border-black px-2 py-1.5">{{ $latest?->ia_no ?? $latest?->report?->ia_no ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $item->po_no ?? $item->purchaseOrder?->po_no ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $item->item_description ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $item->purchaseOrder?->supplier?->supplier_name ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) ($latest?->quantity_delivered ?? $item->quantity ?? 0)) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) ($latest?->quantity_accepted ?? 0)) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) ($latest?->quantity_rejected ?? 0)) }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $latest?->status?->status_name ?? 'Pending Inspection' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border border-black px-2 py-4 text-center text-gray-500">No inspection records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="border border-black px-2 py-2 text-center">Total Records: {{ number_format($items->count()) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="fixed bottom-4 right-4 no-print">
        <button onclick="window.print()" class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700">Print</button>
    </div>
</body>
</html>
