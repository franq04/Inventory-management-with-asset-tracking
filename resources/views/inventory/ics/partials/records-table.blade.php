<div class="bg-white border border-gray-100 rounded-2xl shadow-sm" data-ics-table>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3">ICS Number</th>
                    <th class="px-6 py-3">Property</th>
                    <th class="px-6 py-3">Quantity</th>
                    <th class="px-6 py-3">Unit Cost</th>
                    <th class="px-6 py-3 text-right">Total Cost</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($records as $record)
                    @php
                        $pqs = $record->pqsRecord;
                        $categoryName = ($pqs && $pqs->category) ? $pqs->category->cat_name : '—';
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $record->ics_no }}</div>
                            <div class="text-xs text-gray-500">{{ $categoryName }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium">{{ optional($pqs)->article ?? '—' }}</div>
                            <div class="text-xs text-gray-500 font-mono">P/N: {{ optional($pqs)->property_no ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono">{{ number_format((int) $record->quantity) }} {{ $record->unit }}</td>
                        <td class="px-6 py-4 font-mono">₱{{ number_format((float) $record->unit_cost, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold font-mono text-emerald-600">
                            ₱{{ number_format((float) $record->total_cost, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" 
                                class="js-ics-view inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
                                data-show-url="{{ route('ics.show', $record) }}">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                             <i class="fas fa-box-open text-4xl text-gray-300"></i>
                            <p class="mt-3 font-medium">No ICS records matched your filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($records->hasPages())
        <div class="px-6 py-4 border-t border-gray-100" data-ics-pagination>
            {{ $records->onEachSide(1)->links() }}
        </div>
    @endif
</div>