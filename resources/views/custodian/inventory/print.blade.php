<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Assignment Report</title>
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
                <h1 class="text-2xl font-bold uppercase">Inventory Assignment Report</h1>
                <p class="mt-1 text-sm">State: {{ strtoupper($state ?? 'ALL') }}</p>
                <p class="mt-2 text-xs text-gray-600">Generated on {{ now()->format('F d, Y h:i:s A') }} PHT</p>
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
        </div>
    </div>

    <table class="w-full border-collapse text-xs">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-black px-2 py-2 text-left">Property No.</th>
                <th class="border border-black px-2 py-2 text-left">Description</th>
                <th class="border border-black px-2 py-2 text-left">Category</th>
                <th class="border border-black px-2 py-2 text-right">Qty</th>
                <th class="border border-black px-2 py-2 text-right">Unit Cost</th>
                <th class="border border-black px-2 py-2 text-right">Total Cost</th>
                <th class="border border-black px-2 py-2 text-left">Source</th>
                <th class="border border-black px-2 py-2 text-left">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td class="border border-black px-2 py-1.5">{{ $item['property_no'] ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $item['item_description'] ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">{{ ($item['category'] ?? '—') }} / {{ ($item['sub_category'] ?? '—') }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) ($item['quantity'] ?? 0)) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) ($item['unit_cost'] ?? 0), 2) }}</td>
                    <td class="border border-black px-2 py-1.5 text-right">{{ number_format((float) ($item['total_cost'] ?? 0), 2) }}</td>
                    <td class="border border-black px-2 py-1.5">PO {{ $item['po_no'] ?? '—' }} / IA {{ $item['ia_no'] ?? '—' }}</td>
                    <td class="border border-black px-2 py-1.5">{{ $item['status_name'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="border border-black px-2 py-4 text-center text-gray-500">No inventory assignment records found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="border border-black px-2 py-2 text-right">Grand Total</th>
                <th class="border border-black px-2 py-2 text-right">{{ number_format((float) collect($items)->sum('total_cost'), 2) }}</th>
                <th colspan="2" class="border border-black px-2 py-2">{{ number_format(collect($items)->count()) }} record(s)</th>
            </tr>
        </tfoot>
    </table>

    <div class="fixed bottom-4 right-4 no-print">
        <button onclick="window.print()" class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700">Print</button>
    </div>
</body>
</html>
