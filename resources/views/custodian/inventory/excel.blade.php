<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Assignment Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Inventory Assignment Report</h1>
    <p>Generated on: {{ date('F d, Y h:i:s A') }} PHT</p>

    <table>
        <thead>
            <tr>
                <th>Property No.</th>
                <th>Description</th>
                <th>Category</th>
                <th>Sub-category</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
                <th>PO No.</th>
                <th>IA No.</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['property_no'] ?? '—' }}</td>
                    <td>{{ $item['item_description'] ?? '—' }}</td>
                    <td>{{ $item['category'] ?? '—' }}</td>
                    <td>{{ $item['sub_category'] ?? '—' }}</td>
                    <td class="text-right">{{ number_format((float) ($item['quantity'] ?? 0)) }}</td>
                    <td class="text-right">{{ number_format((float) ($item['unit_cost'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($item['total_cost'] ?? 0), 2) }}</td>
                    <td>{{ $item['po_no'] ?? '—' }}</td>
                    <td>{{ $item['ia_no'] ?? '—' }}</td>
                    <td>{{ $item['status_name'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Grand Total</th>
                <th class="text-right">{{ number_format((float) collect($items)->sum('total_cost'), 2) }}</th>
                <th colspan="3">{{ number_format(collect($items)->count()) }} record(s)</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
