<table border="1">
    <thead>
        <tr style="background-color: #1a3a2d; color: white; font-weight: bold;">
            <th>Account ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Employee ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Section</th>
            <th>Division</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($accounts as $account)
            <tr>
                <td>{{ $account->account_id }}</td>
                <td>{{ $account->username }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $account->role)) }}</td>
                <td>{{ $account->employee?->employee_id ?? 'N/A' }}</td>
                <td>{{ $account->employee?->first_name ?? '' }}</td>
                <td>{{ $account->employee?->middle_name ?? '' }}</td>
                <td>{{ $account->employee?->last_name ?? '' }}</td>
                <td>{{ $account->employee?->section?->section_name ?? 'N/A' }}</td>
                <td>{{ $account->employee?->section?->division?->division_name ?? 'N/A' }}</td>
                <td>{{ $account->employee ? 'Linked' : 'Unassigned' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <td colspan="10">Total Accounts: {{ number_format($accounts->count()) }}</td>
        </tr>
    </tfoot>
</table>
