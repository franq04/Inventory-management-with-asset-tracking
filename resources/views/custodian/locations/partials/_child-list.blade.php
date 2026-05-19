@php
    $depth = $depth ?? 0;
@endphp

<ul class="space-y-2">
    @foreach($children as $child)
        @php
            $payload = $locationPayloads[$child->location_id] ?? null;
            $locationCode = $child->location_code ? ' (' . $child->location_code . ')' : '';
        @endphp
        <li class="rounded-xl border border-slate-200 bg-white/90 p-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $child->location_name }}{{ $locationCode }}</p>
                    <p class="text-xs text-slate-500">Type: {{ ucfirst($child->location_type) }} @if(!$child->is_active) • Inactive @endif</p>
                </div>
                <div class="flex flex-wrap items-center gap-2" data-no-toggle="true">
                    <button type="button" data-open-location-registry-modal data-location-registry-parent="{{ $child->location_id }}" data-location-registry-lock-parent="true" data-no-toggle="true" class="inline-flex items-center gap-2 rounded-lg border border-emerald-100 bg-white px-3 py-1.5 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50">
                        <i class="fas fa-plus"></i>
                        Add sublocation
                    </button>
                    <button type="button" data-location-edit data-location='@json($payload)' data-no-toggle="true" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-pen"></i>
                        Edit
                    </button>
                    <button type="button" data-location-delete data-location='@json($payload)' data-has-children="{{ $child->children->isNotEmpty() ? '1' : '0' }}" data-no-toggle="true" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-[11px] font-semibold text-rose-700 hover:bg-rose-100">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>

            @if($child->description)
                <p class="mt-2 text-xs text-slate-600">{{ $child->description }}</p>
            @endif

            @if($child->children->isNotEmpty())
                <div class="mt-3 border-l-2 border-emerald-200/70 pl-3">
                    @include('custodian.locations.partials._child-list', [
                        'children' => $child->children,
                        'locationPayloads' => $locationPayloads,
                        'depth' => $depth + 1,
                    ])
                </div>
            @endif
        </li>
    @endforeach
</ul>
