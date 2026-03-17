<table class="min-w-full divide-y divide-gray-100">
    <thead class="bg-[#f5f8f6] text-left text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
        <tr>
            <th class="px-6 py-3">Employee</th>
            <th class="px-6 py-3">Contact</th>
            <th class="px-6 py-3">Section / Division</th>
            <th class="px-6 py-3 text-right">Account</th>
            <th class="px-6 py-3 text-center">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 bg-white text-sm text-gray-700">
        @forelse ($employees as $employee)
            <tr data-employee-row class="transition-colors hover:bg-gray-50/70">
                <td class="px-6 py-4">
                    <div class="font-semibold text-gray-900">{{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}</div>
                    <div class="font-mono text-xs text-gray-500">{{ $employee->employee_id }}</div>
                    @if ($employee->position)
                        <div class="mt-1 text-xs font-semibold text-emerald-600">{{ $employee->position->position_title }}</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm">{{ $employee->email ?: 'N/A' }}</div>
                    <div class="text-xs text-gray-500">{{ $employee->contact_no ?: '—' }}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium">{{ optional($employee->section)->section_name ?: 'Unassigned' }}</div>
                    <div class="text-xs text-gray-500">{{ optional(optional($employee->section)->division)->division_name ?: '—' }}</div>
                </td>
                <td class="px-6 py-4 text-right">
                    @if ($employee->account)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            {{ $employee->account->username }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            None
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center">
                        <button
                            type="button"
                            data-view-employee-btn
                            data-employee-id="{{ $employee->id }}"
                            data-employee-code="{{ $employee->employee_id }}"
                            data-employee-name="{{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}"
                            data-date-of-birth="{{ $employee->date_of_birth ? \Illuminate\Support\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : '' }}"
                            data-gender="{{ $employee->gender ?? '' }}"
                            data-marital-status="{{ $employee->marital_status ?? '' }}"
                            data-first-name="{{ $employee->first_name ?? '' }}"
                            data-middle-name="{{ $employee->middle_name ?? '' }}"
                            data-last-name="{{ $employee->last_name ?? '' }}"
                            data-suffix="{{ $employee->suffix ?? '' }}"
                            data-position-id="{{ $employee->position_id ?? '' }}"
                            data-section-id="{{ $employee->section_id ?? '' }}"
                            data-division-id="{{ optional($employee->section)->division_id ?? '' }}"
                            data-account-id="{{ $employee->account_id ?? '' }}"
                            data-account-label="{{ $employee->account ? ($employee->account->username . ' (' . (strtolower((string) $employee->account->role) === 'bac' ? 'BAC Officer' : (strtolower((string) $employee->account->role) === 'iac' ? 'IAC' : ucwords(str_replace('_', ' ', (string) $employee->account->role)))) . ')') : '' }}"
                            data-profile-img="{{ $employee->profile_img ?? '' }}"
                            data-email-raw="{{ $employee->email ?? '' }}"
                            data-contact-raw="{{ $employee->contact_no ?? '' }}"
                            data-employee-email="{{ $employee->email ?: 'Not provided' }}"
                            data-employee-contact="{{ $employee->contact_no ?: 'Not provided' }}"
                            data-employee-position="{{ $employee->position?->position_title ?? 'Unassigned' }}"
                            data-employee-section="{{ optional($employee->section)->section_name ?: 'Unassigned' }}"
                            data-employee-division="{{ optional(optional($employee->section)->division)->division_name ?: '—' }}"
                            data-employee-account="{{ $employee->account?->username ?: 'None' }}"
                            data-modal-update-url="{{ route('employees.update.modal', $employee) }}"
                            data-employee-delete-url="{{ route('employees.destroy', $employee) }}"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-emerald-950/15 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#1a3a2d] shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-900/30 hover:bg-emerald-100 hover:shadow"
                        >
                            <i class="fas fa-eye text-[11px]"></i>
                            View
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                    <i class="fas fa-users-slash text-4xl text-gray-300"></i>
                    <p class="mt-3 font-medium">No employees matched your filters.</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>