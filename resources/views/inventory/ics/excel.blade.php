<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ICS Registry Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>Inventory Custodian Slip (ICS)</h1>
    <p>Generated on: {{ date('F d, Y h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>ICS No.</th>
                <th>Property No.</th>
                <th>Article</th>
                <th>Description</th>
                <th>Date Acquired</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
                <th>Accountable Officer</th>
                <th>Officer ID</th>
                <th>Estimated Useful Life</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td>{{ $record->ics_no }}</td>
                <td>{{ $record->pqsRecord ? $record->pqsRecord->property_no : '' }}</td>
                <td>{{ $record->pqsRecord ? $record->pqsRecord->article : '' }}</td>
                <td>{{ $record->description }}</td>
                <td class="text-center">{{ $record->pqsRecord && $record->pqsRecord->date_acquired ? $record->pqsRecord->date_acquired->format('m/d/Y') : '—' }}</td>
                <td>{{ $record->pqsRecord ? $record->pqsRecord->unit : '' }}</td>
                <td class="text-center">{{ number_format($record->quantity) }}</td>
                <td class="text-right">{{ number_format($record->unit_cost, 2) }}</td>
                <td class="text-right">{{ number_format($record->total_cost, 2) }}</td>
                <td>{{ $record->pqsRecord && $record->pqsRecord->accountableOfficer ? $record->pqsRecord->accountableOfficer->full_name : 'Unassigned' }}</td>
                <td>{{ $record->pqsRecord && $record->pqsRecord->accountableOfficer ? $record->pqsRecord->accountableOfficer->employee_id : '—' }}</td>
                <td>{{ $record->estimated_useful_life }}</td>
                <td>{{ $record->remarks ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">TOTAL:</th>
                <th class="text-center">{{ number_format($records->sum('quantity')) }}</th>
                <th class="text-right">—</th>
                <th class="text-right">₱{{ number_format($records->sum('total_cost'), 2) }}</th>
                <th colspan="4"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
