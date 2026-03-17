<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inspection Items Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Inspection Items Report</h1>
    <p>Generated on: {{ date('F d, Y h:i:s A') }} PHT</p>

    <table>
        <thead>
            <tr>
                <th>IAR No.</th>
                <th>PO No.</th>
                <th>Item Description</th>
                <th>Supplier</th>
                <th>Delivered</th>
                <th>Accepted</th>
                <th>Rejected</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $latest = $item->latestInspectionItem;
                @endphp
                <tr>
                    <td>{{ $latest?->ia_no ?? $latest?->report?->ia_no ?? '—' }}</td>
                    <td>{{ $item->po_no ?? $item->purchaseOrder?->po_no ?? '—' }}</td>
                    <td>{{ $item->item_description ?? '—' }}</td>
                    <td>{{ $item->purchaseOrder?->supplier?->supplier_name ?? '—' }}</td>
                    <td class="text-right">{{ number_format((float) ($latest?->quantity_delivered ?? $item->quantity ?? 0)) }}</td>
                    <td class="text-right">{{ number_format((float) ($latest?->quantity_accepted ?? 0)) }}</td>
                    <td class="text-right">{{ number_format((float) ($latest?->quantity_rejected ?? 0)) }}</td>
                    <td>{{ $latest?->status?->status_name ?? 'Pending Inspection' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8">Total Records: {{ number_format($items->count()) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
