<div class="bg-white border border-gray-100 rounded-2xl shadow-sm" id="accountsTableContainer" data-total="{{ $accounts->total() }}">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-[#f5f8f6]">
                <tr class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Employee</th>
                    <th class="px-6 py-3">Section</th>
                    <th class="px-6 py-3 text-right">Account ID</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($accounts as $account)
                    @php
                        $employee = $account->employee;
                        $fullName = $employee ? trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) : null;
                        $sectionName = $employee && $employee->section ? $employee->section->section_name : null;
                        $roleColor = match(strtolower($account->role)) {
                            'custodian' => 'bg-blue-100 text-blue-700',
                            'iac' => 'bg-purple-100 text-purple-700',
                            'employee' => 'bg-emerald-100 text-emerald-700',
                            'division_head' => 'bg-indigo-100 text-indigo-700',
                            'admin' => 'bg-rose-100 text-rose-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr data-accounts-row data-account-row-id="{{ $account->account_id }}" class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $account->username }}</td>
                        <td class="px-6 py-4">
                            <span data-account-role-badge class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide {{ $roleColor }}">
                                {{ str_replace('_', ' ', $account->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($fullName)
                                <div class="font-medium text-gray-800">{{ $fullName }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $employee->employee_id ?? 'N/A' }}</div>
                            @else
                                <span class="font-medium text-gray-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $sectionName ?: '—' }}</td>
                        <td class="px-6 py-4 text-right font-mono text-sm text-gray-500">{{ $account->account_id }}</td>
                        <td class="px-6 py-4 text-center">
                            <button
                                type="button"
                                data-account-edit-btn
                                data-account-id="{{ $account->account_id }}"
                                data-account-username="{{ $account->username }}"
                                data-account-role="{{ $account->role }}"
                                data-account-update-url="{{ route('accounts.update', $account) }}"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-emerald-950/15 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#1a3a2d] shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-900/30 hover:bg-emerald-100 hover:shadow"
                            >
                                <i class="fas fa-pen-to-square text-[11px]"></i>
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            <i class="fas fa-users-slash text-4xl text-gray-300"></i>
                            <p class="mt-3 font-medium">No accounts matched your filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($accounts->hasPages())
    <div class="px-6 py-4 border-t border-gray-100" id="accountsPagination">
        {{ $accounts->onEachSide(1)->links('vendor.pagination.procurement') }}
    </div>
    @endif
</div>
