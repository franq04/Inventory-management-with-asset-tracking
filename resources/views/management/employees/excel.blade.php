@php
    $formatPhone = static function ($value) {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return $raw;
        }

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '+63' . substr($digits, 1);
        }

        if (str_starts_with($digits, '9') && strlen($digits) === 10) {
            return '+63' . $digits;
        }

        return str_starts_with($raw, '+63') ? $raw : '+' . $digits;
    };
@endphp

<table border="1">
    <thead>
        <tr style="background-color: #1a3a2d; color: white; font-weight: bold;">
            <th>Employee ID</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Last Name</th>
            <th>Position</th>
            <th>Section</th>
            <th>Division</th>
            <th>Email</th>
            <th>Contact No</th>
            <th>Gender</th>
            <th>Marital Status</th>
            <th>Date of Birth</th>
            <th>Address</th>
            <th>Account Status</th>
            <th>Account Username</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            <tr>
                <td>{{ $employee->employee_id }}</td>
                <td>{{ $employee->first_name }}</td>
                <td>{{ $employee->middle_name ?? '' }}</td>
                <td>{{ $employee->last_name }}</td>
                <td>{{ $employee->position?->position_name ?? 'N/A' }}</td>
                <td>{{ $employee->section?->section_name ?? 'N/A' }}</td>
                <td>{{ $employee->section?->division?->division_name ?? 'N/A' }}</td>
                <td>{{ $employee->email ?? '' }}</td>
                <td>{{ $formatPhone($employee->contact_no) }}</td>
                <td>{{ ucfirst($employee->gender ?? '') }}</td>
                <td>{{ ucfirst($employee->marital_status ?? '') }}</td>
                <td>{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('M d, Y') : '' }}</td>
                <td>{{ $employee->address ?? '' }}</td>
                <td>{{ $employee->account ? 'Linked' : 'No Account' }}</td>
                <td>{{ $employee->account?->username ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <td colspan="15">Total Employees: {{ number_format($employees->count()) }}</td>
        </tr>
    </tfoot>
</table>
