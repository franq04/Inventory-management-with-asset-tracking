<?php

namespace App\Http\Controllers\Custodian;

use App\Http\Controllers\Controller;
use App\Models\PhysicalLocation;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PhysicalLocationController extends Controller
{
    public function index(Request $request)
    {
        $allLocations = PhysicalLocation::query()
            ->with([
                'division:division_id,division_name',
                'section:section_id,section_name,division_id',
                'parent:location_id,location_name',
            ])
            ->orderBy('location_name')
            ->get();

        $locationsByParent = $allLocations->groupBy(function (PhysicalLocation $location) {
            return $location->parent_location_id ?? 0;
        });
        $buildTree = function (int $parentId) use (&$buildTree, $locationsByParent) {
            return $locationsByParent
                ->get($parentId, collect())
                ->map(function (PhysicalLocation $location) use ($buildTree) {
                    $location->setRelation('children', $buildTree($location->location_id));
                    return $location;
                });
        };

        $parentLocations = $buildTree(0);
        $locationParents = $allLocations
            ->where('is_active', true)
            ->sortBy('location_name')
            ->values();

        $locationPayloads = $allLocations
            ->mapWithKeys(fn (PhysicalLocation $location) => [
                (int) $location->location_id => $this->formatLocationPayload($location),
            ])
            ->all();

        return view('custodian.locations.index', [
            'locationCards' => $parentLocations,
            'locationParents' => $locationParents,
            'locationPayloads' => $locationPayloads,
            'totalLocations' => $allLocations->count(),
            'activeLocations' => $allLocations->where('is_active', true)->count(),
        ]);
    }

    public function show(PhysicalLocation $location): JsonResponse
    {
        $location->load([
            'division:division_id,division_name',
            'section:section_id,section_name,division_id',
            'parent:location_id,location_name',
        ]);

        return response()->json([
            'data' => [
                'location' => $this->formatLocationPayload($location),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'location_name' => ['required', 'string', 'max:255'],
            'location_code' => ['nullable', 'string', 'max:64', 'unique:physical_locations,location_code'],
            'location_type' => ['required', Rule::in(['building', 'floor', 'room', 'storage', 'other'])],
            'parent_location_id' => ['nullable', Rule::exists('physical_locations', 'location_id')->where(fn ($query) => $query->where('is_active', true))],
            'division_id' => ['nullable', 'exists:divisions,division_id'],
            'section_id' => ['nullable', 'exists:sections,section_id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $normalizedName = preg_replace('/\s+/', ' ', trim((string) $validated['location_name']));
        $locationCode = trim((string) ($validated['location_code'] ?? ''));
        $locationCode = $locationCode !== '' ? $locationCode : null;
        $parentLocationId = isset($validated['parent_location_id']) ? (int) $validated['parent_location_id'] : null;
        $divisionId = isset($validated['division_id']) ? (int) $validated['division_id'] : null;
        $sectionId = isset($validated['section_id']) ? (int) $validated['section_id'] : null;

        if ($sectionId && $divisionId) {
            $sectionDivision = Section::query()->where('section_id', $sectionId)->value('division_id');
            if ((int) $sectionDivision !== $divisionId) {
                return $this->validationErrorResponse($request, [
                    'section_id' => ['Selected section does not belong to the chosen division.'],
                ]);
            }
        }

        $duplicateQuery = PhysicalLocation::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(location_name)) = ?', [mb_strtolower($normalizedName, 'UTF-8')]);

        if ($parentLocationId) {
            $duplicateQuery->where('parent_location_id', $parentLocationId);
        } else {
            $duplicateQuery->whereNull('parent_location_id');
        }

        $existing = $duplicateQuery->first();

        if ($existing) {
            $payload = [
                'status' => 'success',
                'message' => 'Location already exists. Selected existing record.',
                'data' => [
                    'location' => $this->formatLocationPayload($existing),
                    'created' => false,
                ],
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($payload);
            }

            return back()->with('status', $payload['message']);
        }

        $location = PhysicalLocation::query()->create([
            'location_name' => $normalizedName,
            'location_code' => $locationCode,
            'location_type' => $validated['location_type'],
            'parent_location_id' => $parentLocationId,
            'division_id' => $divisionId,
            'section_id' => $sectionId,
            'description' => $validated['description'] ?? null,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
        ]);

        $location->load([
            'division:division_id,division_name',
            'section:section_id,section_name,division_id',
            'parent:location_id,location_name',
        ]);

        $payload = [
            'status' => 'success',
            'message' => 'Location created successfully.',
            'data' => [
                'location' => $this->formatLocationPayload($location),
                'created' => true,
            ],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, 201);
        }

        return back()->with('status', $payload['message']);
    }

    public function update(Request $request, PhysicalLocation $location): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'location_name' => ['required', 'string', 'max:255'],
            'location_code' => ['nullable', 'string', 'max:64', Rule::unique('physical_locations', 'location_code')->ignore($location->location_id, 'location_id')],
            'location_type' => ['required', Rule::in(['building', 'floor', 'room', 'storage', 'other'])],
            'parent_location_id' => ['nullable', Rule::exists('physical_locations', 'location_id')],
            'division_id' => ['nullable', 'exists:divisions,division_id'],
            'section_id' => ['nullable', 'exists:sections,section_id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $normalizedName = preg_replace('/\s+/', ' ', trim((string) $validated['location_name']));
        $locationCode = trim((string) ($validated['location_code'] ?? ''));
        $locationCode = $locationCode !== '' ? $locationCode : null;
        $parentLocationId = isset($validated['parent_location_id']) ? (int) $validated['parent_location_id'] : null;
        $divisionId = isset($validated['division_id']) ? (int) $validated['division_id'] : null;
        $sectionId = isset($validated['section_id']) ? (int) $validated['section_id'] : null;

        if ($parentLocationId === (int) $location->location_id) {
            return $this->validationErrorResponse($request, [
                'parent_location_id' => ['A location cannot be its own parent.'],
            ]);
        }

        if ($parentLocationId && $this->isDescendantOf($location->location_id, $parentLocationId)) {
            return $this->validationErrorResponse($request, [
                'parent_location_id' => ['Invalid parent selection.'],
            ]);
        }

        if ($sectionId && $divisionId) {
            $sectionDivision = Section::query()->where('section_id', $sectionId)->value('division_id');
            if ((int) $sectionDivision !== $divisionId) {
                return $this->validationErrorResponse($request, [
                    'section_id' => ['Selected section does not belong to the chosen division.'],
                ]);
            }
        }

        $duplicateQuery = PhysicalLocation::query()
            ->where('location_id', '!=', $location->location_id)
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(location_name)) = ?', [mb_strtolower($normalizedName, 'UTF-8')]);

        if ($parentLocationId) {
            $duplicateQuery->where('parent_location_id', $parentLocationId);
        } else {
            $duplicateQuery->whereNull('parent_location_id');
        }

        if ($duplicateQuery->exists()) {
            return $this->validationErrorResponse($request, [
                'location_name' => ['Another active location with the same name already exists under this parent.'],
            ]);
        }

        $nextActiveState = array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : (bool) $location->is_active;
        if (! $nextActiveState && $location->children()->where('is_active', true)->exists()) {
            return $this->validationErrorResponse($request, [
                'is_active' => ['Deactivate child locations first before disabling this location.'],
            ]);
        }

        $location->update([
            'location_name' => $normalizedName,
            'location_code' => $locationCode,
            'location_type' => $validated['location_type'],
            'parent_location_id' => $parentLocationId,
            'division_id' => $divisionId,
            'section_id' => $sectionId,
            'description' => $validated['description'] ?? null,
            'is_active' => $nextActiveState,
        ]);

        $location->load([
            'division:division_id,division_name',
            'section:section_id,section_name,division_id',
            'parent:location_id,location_name',
        ]);

        $payload = [
            'status' => 'success',
            'message' => 'Location updated successfully.',
            'data' => [
                'location' => $this->formatLocationPayload($location),
            ],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return back()->with('status', $payload['message']);
    }

    public function destroy(Request $request, PhysicalLocation $location): JsonResponse|RedirectResponse
    {
        if ($location->children()->where('is_active', true)->exists()) {
            return $this->validationErrorResponse($request, [
                'location_id' => ['Deactivate child locations before deleting this location.'],
            ]);
        }

        $location->update(['is_active' => false]);

        $payload = [
            'status' => 'success',
            'message' => 'Location deleted successfully.',
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return back()->with('status', $payload['message']);
    }

    protected function isDescendantOf(int $locationId, int $parentLocationId): bool
    {
        $currentParentId = $parentLocationId;

        while ($currentParentId) {
            if ($currentParentId === $locationId) {
                return true;
            }

            $currentParentId = (int) PhysicalLocation::query()
                ->where('location_id', $currentParentId)
                ->value('parent_location_id');
        }

        return false;
    }

    protected function validationErrorResponse(Request $request, array $errors): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        return back()->withErrors($errors)->withInput();
    }

    protected function formatLocationPayload(PhysicalLocation $location): array
    {
        return [
            'location_id' => (int) $location->location_id,
            'location_name' => (string) $location->location_name,
            'location_code' => $location->location_code ? (string) $location->location_code : null,
            'location_type' => (string) $location->location_type,
            'parent_location_id' => $location->parent_location_id ? (int) $location->parent_location_id : null,
            'parent_location_name' => $location->parent?->location_name,
            'division_id' => $location->division_id ? (int) $location->division_id : null,
            'division_name' => $location->division?->division_name,
            'section_id' => $location->section_id ? (int) $location->section_id : null,
            'section_name' => $location->section?->section_name,
            'description' => $location->description,
            'is_active' => (bool) $location->is_active,
            'is_storage' => $location->location_type === 'storage',
            'display_name' => trim((string) $location->location_name) . ($location->location_code ? ' (' . $location->location_code . ')' : ''),
        ];
    }
}
