<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order Register Export</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #0f172a; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #1e293b; padding: 8px; text-align: left; }
        th { background-color: #e2e8f0; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .meta { margin: 4px 0; color: #475569; font-size: 12px; }
        .prepared { margin-top: 20px; width: 100%; }
        .prepared-wrap { width: 320px; margin-left: auto; text-align: right; }
        .line { border-bottom: 1px solid #1f2937; margin-top: 3px; }
    </style>
</head>
<body>
    <h1>Purchase Order Register Report</h1>
    <p class="meta">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>PO Number</th>
                <th>PR Number</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Delivery Date</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $order)
                @php
                    $totalAmount = (float) $order->items->sum('total_cost');
                @endphp
                <tr>
                    <td>{{ $order->po_no }}</td>
                    <td>{{ $order->pr_no }}</td>
                    <td>{{ $order->supplier?->supplier_name ?? '—' }}</td>
                    <td>{{ $order->status?->status_name ?? '—' }}</td>
                    <td class="text-center">{{ optional($order->order_date)->format('m/d/Y') ?: '—' }}</td>
                    <td class="text-center">{{ optional($order->delivery_date)->format('m/d/Y') ?: '—' }}</td>
                    <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No purchase orders found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Grand Total</th>
                <th class="text-right">{{ number_format((float) $purchaseOrders->sum(fn($order) => (float) $order->items->sum('total_cost')), 2) }}</th>
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
