<div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-[0_12px_35px_-22px_rgba(15,60,45,0.45)]">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-emerald-100">
            <thead class="bg-emerald-50/70">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/80">Timestamp</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/80">User</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/80">Role</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/80">Action</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/80">Description</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-900/80">Entity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($logs as $log)
                    @php
                        $actionLower = strtolower($log->action ?? '');
                        $actionBadge = 'bg-gray-100 text-gray-700 border-gray-200';

                        if (str_contains($actionLower, 'create') || str_contains($actionLower, 'add')) {
                            $actionBadge = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) {
                            $actionBadge = 'bg-blue-100 text-blue-700 border-blue-200';
                        } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'remove')) {
                            $actionBadge = 'bg-red-100 text-red-700 border-red-200';
                        } elseif (str_contains($actionLower, 'login')) {
                            $actionBadge = 'bg-violet-100 text-violet-700 border-violet-200';
                        } elseif (str_contains($actionLower, 'logout')) {
                            $actionBadge = 'bg-amber-100 text-amber-700 border-amber-200';
                        } elseif (str_contains($actionLower, 'status')) {
                            $actionBadge = 'bg-cyan-100 text-cyan-700 border-cyan-200';
                        }

                        $roleLabel = ucfirst(str_replace('_', ' ', $log->account?->role ?? 'system'));
                    @endphp
                    <tr data-audit-row class="transition-colors duration-150 hover:bg-emerald-50/40">
                        <td class="whitespace-nowrap px-4 py-3 align-top text-sm text-gray-700">
                            <div class="font-semibold text-gray-900">{{ optional($log->log_time)->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ optional($log->log_time)->format('h:i:s A') }} PHT</div>
                        </td>
                        <td class="px-4 py-3 align-top text-sm text-gray-800">
                            <div class="font-semibold text-gray-900">{{ $log->account->username ?? 'System' }}</div>
                        </td>
                        <td class="px-4 py-3 align-top text-sm text-gray-700">
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-top text-sm">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $actionBadge }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="max-w-[520px] px-4 py-3 align-top text-sm text-gray-700">
                            <p class="line-clamp-2">{{ $log->description }}</p>
                        </td>
                        <td class="px-4 py-3 align-top text-sm text-gray-700">
                            {{ $log->table_name ?: 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center">
                            <i class="fas fa-history text-4xl text-gray-300"></i>
                            <p class="mt-3 text-base font-semibold text-gray-700">No audit logs found.</p>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your search, action filter, or date range.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-emerald-100 bg-[#fbfcfb] px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">
            Showing
            <span class="font-semibold text-gray-700">{{ number_format($logs->firstItem() ?? 0) }}</span>
            to
            <span class="font-semibold text-gray-700">{{ number_format($logs->lastItem() ?? 0) }}</span>
            of
            <span class="font-semibold text-gray-700">{{ number_format($logs->total()) }}</span>
            results
        </p>

        @if ($logs->hasPages())
            <div class="pagination-links">
                {{ $logs->onEachSide(1)->links('vendor.pagination.procurement') }}
            </div>
        @endif
    </div>
</div>