<div class="bg-white border border-gray-100 rounded-2xl shadow-sm" data-pqs-table>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3">Property</th>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Accountable Officer</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Value</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($records as $record)
                    @php
                        $officer = $record->accountableOfficer;
                        $officerName = $officer ? trim(($officer->first_name ?? '') . ' ' . ($officer->last_name ?? '')) : null;
                        $hasIcs = $record->icsRecord !== null;
                        $hasPar = $record->parRecord !== null;
                        $isAssigned = $officerName !== null;
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $record->article }}</div>
                            <div class="text-xs text-gray-500 font-mono">P/N: {{ $record->property_no }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ optional($record->category)->cat_name ?: '—' }}</td>
                        <td class="px-6 py-4">
                            @if($isAssigned)
                                <div class="font-medium text-gray-800">{{ $officerName }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $officer->employee_id ?? '—' }}</div>
                            @else
                                <span class="text-gray-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($isAssigned)
                                <div class="flex gap-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wide {{ $hasIcs ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">ICS</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold tracking-wide {{ $hasPar ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' }}">PAR</span>
                                </div>
                            @else
                                 <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold capitalize bg-amber-100 text-amber-700">
                                    <i class="fas fa-hourglass-half"></i> Awaiting Assignment
                                 </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-semibold font-mono">₱{{ number_format((float) $record->total_value, 2) }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:justify-end">
                                <button type="button"
                                        class="js-pqs-view inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
                                        data-show-url="{{ route('pqs.show', $record) }}">
                                    <i class="fas fa-eye"></i>
                                    View Record
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                            <i class="fas fa-box-open text-4xl text-gray-300"></i>
                            <p class="mt-3 font-medium">No PQS records matched your filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($records->hasPages())
        <div class="px-6 py-4 border-t border-gray-100" data-pqs-pagination>
            {{ $records->onEachSide(1)->links() }}
        </div>
    @endif
</div>
