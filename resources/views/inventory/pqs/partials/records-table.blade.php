<div class="overflow-hidden rounded-[24px] border border-emerald-950/10 bg-white shadow-[0_20px_50px_-38px_rgba(15,23,42,0.75)]" data-pqs-table>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-[#f5f8f6] text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                <tr>
                    <th class="px-6 py-3">Property Number</th>
                    <th class="px-6 py-3">Property Description</th>
                    <th class="px-6 py-3">Current Employee (Accountable Employee)</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($records as $record)
                    @php
                        $displayOwner = $record->currentCustodian ?: $record->accountableOfficer;
                        $officerName = $displayOwner ? trim(($displayOwner->first_name ?? '') . ' ' . ($displayOwner->last_name ?? '')) : null;
                        $hasIcs = $record->icsRecord !== null;
                        $hasPar = $record->parRecord !== null;
                        $isAssigned = $officerName !== null;
                        $assetStatus = (string) ($record->asset_status ?? \App\Models\PqsRecord::STATUS_ACTIVE);
                        $isMaintenance = $assetStatus === \App\Models\PqsRecord::STATUS_MAINTENANCE;
                        $showUnserviceable = in_array($assetStatus, [
                            \App\Models\PqsRecord::STATUS_FOR_REPAIR,
                            \App\Models\PqsRecord::STATUS_DISPOSED,
                            \App\Models\PqsRecord::STATUS_LOST,
                        ], true);
                    @endphp
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900 font-mono">{{ $record->property_no }}</div>
                            @if ($isMaintenance)
                                <div class="mt-2">
                                    <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Under Maintenance</span>
                                </div>
                            @elseif ($showUnserviceable)
                                <div class="mt-2">
                                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-rose-700">Unserviceable</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-800">{{ $record->description ?: $record->article }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($isAssigned)
                                <div class="font-medium text-gray-800">{{ $officerName }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $displayOwner->employee_id ?? '—' }}</div>
                            @else
                                <span class="text-gray-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end">
                                <button type="button"
                                        class="js-pqs-view inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#1a3a2d]/10 bg-[#f4f8f5] px-4 py-2.5 text-xs font-semibold text-[#1a3a2d] shadow-sm transition-all hover:-translate-y-0.5 hover:border-[#1a3a2d]/20 hover:bg-[#eaf4ee] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]/30 focus:ring-offset-2"
                                        data-property-no="{{ $record->property_no }}"
                                        data-detail-url="{{ route('pqs.show.by-property', ['property_no' => $record->property_no]) }}"
                                        data-show-url="{{ route('pqs.show', $record) }}">
                                    <i class="fas fa-eye"></i>
                                    View & Manage
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center text-sm text-gray-500">
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
            {{ $records->onEachSide(1)->links('vendor.pagination.procurement') }}
        </div>
    @endif
</div>
