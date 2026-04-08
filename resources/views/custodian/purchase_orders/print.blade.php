<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order Register Report</title>
    @vite('resources/css/app.css')
    <style>
        :root {
            --ink: #0f172a;
            --line: #1f2937;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
        }

        .report-shell {
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
        }

        .report-header {
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
        }

        .report-table th,
        .report-table td {
            border: 1px solid #1e293b;
        }

        .report-table th {
            background: #e2e8f0;
            color: #0f172a;
            letter-spacing: 0.02em;
        }

        .prepared-by-block {
            width: 240px;
            margin-left: auto;
            text-align: right;
        }

        .prepared-by-line {
            border-bottom: 1px solid var(--line);
            width: 190px;
            height: 0;
            margin-top: 2px;
            margin-left: auto;
            margin-right: auto;
        }

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
            .no-print { display: none !important; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-white p-6 text-gray-900">
    <div class="report-shell">
        <div class="report-header px-4 py-4">
            <div class="flex items-center justify-between">
                <img src="{{ asset('images/bpi-logo.png') }}" alt="Department of Agriculture" class="h-16 w-16 object-contain">
                <div class="flex-1 text-center">
                    <h1 class="text-3xl font-black uppercase tracking-[0.04em]">Purchase Order Register Report</h1>
                    <p class="mt-1 text-sm text-slate-700">Procurement Order Monitoring Summary</p>
                    <p class="mt-2 text-xs text-gray-600">Generated on: {{ $generatedOnLabel ?? now()->format('F d, Y') }}</p>
                </div>
                <img src="{{ asset('images/pqslogo.png') }}" alt="Plant Quarantine Service" class="h-16 w-16 object-contain">
            </div>
        </div>

        <table class="report-table w-full border-collapse text-xs">
            <thead>
                <tr>
                    <th class="px-2 py-2 text-left">PO Number</th>
                    <th class="px-2 py-2 text-left">PR Number</th>
                    <th class="px-2 py-2 text-left">Supplier</th>
                    <th class="px-2 py-2 text-left">Status</th>
                    <th class="px-2 py-2 text-center">Order Date</th>
                    <th class="px-2 py-2 text-center">Delivery Date</th>
                    <th class="px-2 py-2 text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $order)
                    @php
                        $totalAmount = (float) $order->items->sum('total_cost');
                    @endphp
                    <tr>
                        <td class="px-2 py-1.5">{{ $order->po_no }}</td>
                        <td class="px-2 py-1.5">{{ $order->pr_no }}</td>
                        <td class="px-2 py-1.5">{{ $order->supplier?->supplier_name ?? '—' }}</td>
                        <td class="px-2 py-1.5">{{ $order->status?->status_name ?? '—' }}</td>
                        <td class="px-2 py-1.5 text-center">{{ optional($order->order_date)->format('m/d/Y') ?: '—' }}</td>
                        <td class="px-2 py-1.5 text-center">{{ optional($order->delivery_date)->format('m/d/Y') ?: '—' }}</td>
                        <td class="px-2 py-1.5 text-right">{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-2 py-4 text-center text-gray-500">No purchase orders found.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="px-2 py-2 text-right">Grand Total</th>
                    <th class="px-2 py-2 text-right">{{ number_format((float) $purchaseOrders->sum(fn($order) => (float) $order->items->sum('total_cost')), 2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="px-5 pb-5 pt-4 border-t border-slate-800">
            <div class="prepared-by-block">
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide text-left">Prepared by:</p>
                <div class="mt-3 text-center">
                    <p class="text-sm font-bold">{{ $preparedByName ?? 'System User' }}</p>
                    <div class="prepared-by-line"></div>
                    <p class="text-xs text-slate-600 mt-1">{{ $preparedByRole ?? 'User' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-4 right-4 no-print">
        <button onclick="window.print()" class="rounded bg-blue-600 px-4 py-2 text-white shadow hover:bg-blue-700">Print</button>
    </div>
</body>
</html>
