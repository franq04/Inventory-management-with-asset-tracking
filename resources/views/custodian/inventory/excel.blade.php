<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Assignment Export</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #0f172a; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #1e293b; padding: 8px; text-align: left; }
        th { background-color: #e2e8f0; font-weight: bold; letter-spacing: 0.02em; }
        .text-right { text-align: right; }
        .meta { margin: 4px 0; color: #475569; font-size: 12px; }
        .prepared { margin-top: 20px; width: 100%; }
        .prepared-wrap { width: 320px; margin-left: auto; text-align: right; }
        .line { border-bottom: 1px solid #1f2937; margin-top: 3px; }
    </style>
</head>
<body>
    <h1>Inventory Assignment Report</h1>
    <p class="meta">Generated Date: {{ $generatedDateLabel ?? now()->format('F d, Y') }}</p>

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

    <div class="prepared">
        <div class="prepared-wrap">
            <p class="meta" style="text-transform: uppercase; letter-spacing: 0.04em; font-weight: 700; text-align: left;">Prepared by:</p>
            <p style="font-weight: 700; margin-bottom: 0;">{{ $preparedByName ?? 'System User' }}</p>
            <div class="line"></div>
            <p class="meta" style="margin-top: 4px;">{{ $preparedByRole ?? 'User' }}</p>
        </div>
    </div>
</body>
</html>
