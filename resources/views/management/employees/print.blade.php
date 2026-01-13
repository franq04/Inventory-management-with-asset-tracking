<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees Registry - Print</title>
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 0.5in;
            }
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a3a2d;
        }
        .logo { width: 60px; height: 60px; }
        .header-center { text-align: center; flex: 1; }
        .header-center h1 { margin: 0; font-size: 20px; color: #1a3a2d; }
        .header-center p { margin: 5px 0 0 0; font-size: 12px; color: #666; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: auto;
        }
        thead { background-color: #1a3a2d; color: white; }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th { font-weight: bold; font-size: 9px; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        $recordsPerPage = 18;
        $chunks = $employees->chunk($recordsPerPage);
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        @if($chunkIndex > 0)
            <div class="page-break"></div>
        @endif

        <div class="header">
            <img src="{{ asset('images/bpi-logo.png') }}" alt="Logo" class="logo">
            <div class="header-center">
                <h1>Employees Registry</h1>
                <p>Generated on {{ now()->format('F d, Y h:i A') }} (PHT)</p>
            </div>
            <img src="{{ asset('images/pqslogo.png') }}" alt="Logo" class="logo">
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Employee ID</th>
                    <th style="width: 15%;">Full Name</th>
                    <th style="width: 12%;">Position</th>
                    <th style="width: 12%;">Section</th>
                    <th style="width: 12%;">Division</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 10%;">Contact</th>
                    <th style="width: 8%;">Gender</th>
                    <th style="width: 8%;">Account</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chunk as $employee)
                    <tr>
                        <td>{{ $employee->employee_id }}</td>
                        <td>
                            {{ $employee->first_name }} 
                            {{ $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '' }} 
                            {{ $employee->last_name }}
                        </td>
                        <td>{{ $employee->position?->position_name ?? 'N/A' }}</td>
                        <td>{{ $employee->section?->section_name ?? 'N/A' }}</td>
                        <td>{{ $employee->section?->division?->division_name ?? 'N/A' }}</td>
                        <td>{{ $employee->email ?? 'N/A' }}</td>
                        <td>{{ $employee->contact_no ?? 'N/A' }}</td>
                        <td>{{ ucfirst($employee->gender ?? 'N/A') }}</td>
                        <td>{{ $employee->account ? '✓ Linked' : '✗ None' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($loop->last)
            <div class="summary">
                <strong>Summary:</strong> Total Employees: {{ number_format($employees->count()) }} |
                Page {{ $chunkIndex + 1 }} of {{ $chunks->count() }}
            </div>
        @endif
    @endforeach

    <button onclick="window.print()" class="no-print" style="position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; background-color: #1a3a2d; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fas fa-print"></i> Print
    </button>
</body>
</html>
