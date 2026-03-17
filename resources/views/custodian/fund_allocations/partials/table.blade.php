<div class="overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-[0_20px_50px_-38px_rgba(15,23,42,0.75)]">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-[#f5f8f6] text-left text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">
                <tr>
                    <th class="px-6 py-3">Fund Cluster</th>
                    <th class="px-6 py-3 text-right">Total Amount</th>
                    <th class="px-6 py-3 text-right">Allocated</th>
                    <th class="px-6 py-3 text-right">Remaining</th>
                    <th class="px-6 py-3 text-center">Utilization</th>
                    <th class="px-6 py-3">Created By</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white text-sm text-gray-700">
                @forelse($allocations as $allocation)
                    @php
                        $allocated = $allocation->total_amount - $allocation->remaining_amount;
                        $utilization = $allocation->total_amount > 0
                            ? ($allocated / $allocation->total_amount) * 100
                            : 0;
                        $utilizationColor = $utilization >= 90 ? 'bg-red-500' : ($utilization >= 70 ? 'bg-amber-500' : 'bg-emerald-500');

                        $employee = $allocation->creator?->employee;
                        $creatorName = $employee
                            ? trim(($employee->first_name ?? '') . ' ' . ($employee->middle_name ?? '') . ' ' . ($employee->last_name ?? ''))
                            : ($allocation->creator?->name ?? 'Unknown');
                    @endphp
                    <tr class="transition-colors hover:bg-gray-50/70" data-id="{{ $allocation->id }}" data-fund-row>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $allocation->fund_cluster }}</div>
                            <div class="text-xs text-gray-500">Created: {{ $allocation->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-900">₱{{ number_format($allocation->total_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-700">₱{{ number_format($allocated, 2) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-700">₱{{ number_format($allocation->remaining_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <div class="h-2 w-32 rounded-full bg-gray-200">
                                    <div class="{{ $utilizationColor }} h-2 rounded-full transition-all" style="width: {{ min($utilization, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-600">{{ number_format($utilization, 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $creatorName }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button class="edit-allocation inline-flex cursor-pointer items-center justify-center rounded-lg border border-[#1a3a2d]/15 bg-[#f4f8f5] px-3 py-1.5 text-[#1a3a2d] transition-all hover:-translate-y-0.5 hover:border-[#1a3a2d]/30 hover:bg-[#eaf4ee]"
                                        data-id="{{ $allocation->id }}"
                                        data-cluster="{{ $allocation->fund_cluster }}"
                                        data-total="{{ $allocation->total_amount }}"
                                        data-allocated="{{ $allocated }}"
                                        title="Edit allocation">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($allocated == 0)
                                    <button class="delete-allocation inline-flex cursor-pointer items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-red-700 transition-all hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-100"
                                            data-id="{{ $allocation->id }}"
                                            data-cluster="{{ $allocation->fund_cluster }}"
                                            title="Delete allocation">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <button class="inline-flex cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-gray-400" title="Cannot delete: has active allocations">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-14 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                            <p class="mt-3 text-base font-semibold text-gray-700">No fund allocations found.</p>
                            <p class="text-sm">Create your first fund allocation to get started.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($allocations->hasPages())
        <div class="border-t border-gray-100 px-6 py-4" data-fund-pagination>
            {{ $allocations->onEachSide(1)->links('vendor.pagination.procurement') }}
        </div>
    @endif
</div>
