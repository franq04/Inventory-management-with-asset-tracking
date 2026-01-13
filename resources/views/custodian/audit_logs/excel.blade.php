<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Audit Logs Export</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-center { text-align: center; }
        .separator { background-color: #e5e7eb; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <h1>Audit Trail Report</h1>
    <p>Generated on: {{ date('F d, Y h:i:s A') }} PHT</p>
    
    <table>
        <thead>
            <tr>
                <th>Timestamp (PHT)</th>
                <th>User</th>
                <th>Role</th>
                <th>Action</th>
                <th>Description</th>
                <th>Table</th>
            </tr>
        </thead>
        <tbody>
            @php
                $lastUser = null;
            @endphp
            @foreach($logs as $log)
                @php
                    $currentUser = $log->account->username ?? 'System';
                    $showSeparator = $lastUser !== null && $lastUser !== $currentUser;
                    $lastUser = $currentUser;
                @endphp
                @if($showSeparator)
                <tr class="separator">
                    <td colspan="6">— Different User —</td>
                </tr>
                @endif
                <tr>
                    <td>{{ optional($log->log_time)->format('M d, Y h:i:s A') }}</td>
                    <td>{{ $log->account->username ?? 'System' }}</td>
                    <td>{{ $log->account?->role ? ucfirst(str_replace('_', ' ', $log->account->role)) : '—' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->table_name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-center">Total Records: {{ number_format($logs->count()) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
