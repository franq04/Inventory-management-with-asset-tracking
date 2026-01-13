<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PQS Registry Export</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Property, Plant & Equipment Ledger Card (PQS Registry)</h1>
    <p>Generated on: {{ date('F d, Y h:i A') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>Property No.</th>
                <th>Article</th>
                <th>Description</th>
                <th>Category</th>
                <th>Date Acquired</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Unit Value</th>
                <th>Total Value</th>
                <th>Accountable Officer</th>
                <th>Officer ID</th>
                <th>ICS Number</th>
                <th>PAR Number</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            <tr>
                <td>{{ $record->property_no }}</td>
                <td>{{ $record->article }}</td>
                <td>{{ $record->description }}</td>
                <td>{{ $record->category ? $record->category->cat_name : '—' }}</td>
                <td class="text-center">{{ $record->date_acquired ? $record->date_acquired->format('m/d/Y') : '—' }}</td>
                <td>{{ $record->unit ?? '—' }}</td>
                <td class="text-center">{{ number_format($record->on_hand_per_count) }}</td>
                <td class="text-right">{{ number_format($record->unit_value, 2) }}</td>
                <td class="text-right">{{ number_format($record->total_value, 2) }}</td>
                <td>{{ $record->accountableOfficer ? $record->accountableOfficer->full_name : 'Unassigned' }}</td>
                <td>{{ $record->accountableOfficer ? $record->accountableOfficer->employee_id : '—' }}</td>
                <td>{{ $record->icsRecord ? $record->icsRecord->ics_no : '—' }}</td>
                <td>{{ $record->parRecord ? $record->parRecord->par_no : '—' }}</td>
                <td>
                    @if($record->icsRecord && $record->parRecord)
                        ICS/PAR
                    @elseif($record->icsRecord)
                        ICS
                    @elseif($record->parRecord)
                        PAR
                    @else
                        Unassigned
                    @endif
                </td>
                <td>{{ $record->remarks ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="15" class="text-center">No records found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">TOTAL:</th>
                <th class="text-center">{{ number_format($records->sum('on_hand_per_count')) }}</th>
                <th class="text-right">—</th>
                <th class="text-right">₱{{ number_format($records->sum('total_value'), 2) }}</th>
                <th colspan="6"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
