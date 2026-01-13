                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Employee</th>
                                <th class="px-6 py-3">Contact</th>
                                <th class="px-6 py-3">Section / Division</th>
                                <th class="px-6 py-3 text-right">Account</th>
                                <th class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse ($employees as $employee)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $employee->employee_id }}</div>
                                        @if ($employee->position)
                                            <div class="text-xs text-emerald-600 font-semibold mt-1">{{ $employee->position->position_title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>{{ $employee->email ?: '—' }}</div>
                                        <div class="text-xs text-gray-500">{{ $employee->contact_no ?: '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ optional($employee->section)->section_name ?: 'Unassigned' }}</div>
                                        <div class="text-xs text-gray-500">{{ optional(optional($employee->section)->division)->division_name ?: '—' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($employee->account)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold uppercase tracking-wide">
                                                {{ $employee->account->username }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-500 text-xs font-semibold uppercase tracking-wide">
                                                None
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border bg-white text-gray-600 hover:bg-gray-50 hover:text-[#1a3a2d] transition" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Delete this employee record? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border bg-white text-red-500 hover:bg-red-50 hover:text-red-700 transition" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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