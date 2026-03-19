<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\IcsRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IcsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $categoryFilters = $request->input('categories', []);

        if (!is_array($categoryFilters)) {
            $categoryFilters = array_filter([$categoryFilters]);
        }

        $categoryFilters = array_values(array_filter(
            array_map(
                static fn ($value) => is_numeric($value) ? (int) $value : null,
                $categoryFilters
            ),
            static fn ($value) => $value !== null
        ));

        $query = IcsRecord::query()->with('pqsRecord.category');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('ics_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('pqsRecord', function ($pqsQuery) use ($search) {
                        $pqsQuery->where('property_no', 'like', "%{$search}%")
                            ->orWhere('article', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($categoryFilters)) {
            $query->whereHas('pqsRecord', function ($pqsQuery) use ($categoryFilters) {
                $pqsQuery->whereIn('cat_id', $categoryFilters);
            });
        }

        $icsRecords = $query
            ->orderByDesc('ics_no')
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            $tableHtml = view('inventory.ics.partials.records-table', [
                'records' => $icsRecords,
            ])->render();

            return response()->json([
                'html' => $tableHtml,
                'total' => $icsRecords->total(),
            ]);
        }

        $agg = DB::table('ics')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(total_cost) as total_cost')
            ->selectRaw('AVG(estimated_useful_life) as avg_life')
            ->first();

        $unassignedAssets = (int) DB::table('pqs')
            ->whereNotIn('property_no', DB::table('ics')->select('property_no'))
            ->whereNotIn('property_no', DB::table('par')->select('property_no'))
            ->count();

        $stats = [
            'total' => (int) $agg->total,
            'totalCost' => (float) $agg->total_cost,
            'averageUsefulLife' => (float) $agg->avg_life,
            'unassigned' => $unassignedAssets,
            'totalValue' => (float) $agg->total_cost,
        ];

        $categories = Category::query()
            ->orderBy('cat_name')
            ->get(['cat_id', 'cat_name']);

        $recentIcs = IcsRecord::query()
            ->with('pqsRecord')
            ->orderByDesc('ics_no')
            ->limit(5)
            ->get();

        return view('inventory.ics.index', [
            'records' => $icsRecords,
            'stats' => $stats,
            'search' => $search,
            'categoryFilters' => $categoryFilters,
            'categories' => $categories,
            'recentIcs' => $recentIcs,
        ]);
    }

    public function show(IcsRecord $icsRecord)
    {
        $icsRecord->load('pqsRecord.category.parent', 'pqsRecord.accountableOfficer.position');
        
        $pqsRecord = $icsRecord->pqsRecord;
        $serialNumbers = [];
        if ($pqsRecord && $pqsRecord->serial_number) {
            $serialNumbers = collect(preg_split('/[\r\n;,]+/', (string) $pqsRecord->serial_number))
                ->map(static fn ($value) => trim($value))
                ->filter(static fn ($value) => $value !== '')
                ->values()
                ->all();
        }
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'ics_no' => $icsRecord->ics_no,
                'description' => $icsRecord->description,
                'quantity' => (int) $icsRecord->quantity,
                'unit' => $icsRecord->unit,
                'unit_cost' => (float) $icsRecord->unit_cost,
                'total_cost' => (float) $icsRecord->total_cost,
                'estimated_useful_life' => $icsRecord->estimated_useful_life,
                'pqs_record' => $pqsRecord ? [
                    'property_no' => $pqsRecord->property_no,
                    'article' => $pqsRecord->article,
                    'description' => $pqsRecord->description,
                    'date_acquired' => optional($pqsRecord->date_acquired)->toDateString(),
                    'unit' => $pqsRecord->unit,
                    'quantity' => $pqsRecord->on_hand_per_count,
                    'unit_value' => $pqsRecord->unit_value !== null ? (float) $pqsRecord->unit_value : null,
                    'total_value' => $pqsRecord->total_value !== null ? (float) $pqsRecord->total_value : null,
                    'remarks' => $pqsRecord->remarks,
                    'category' => $pqsRecord->category ? $pqsRecord->category->cat_name : null,
                    'category_parent' => $pqsRecord->category && $pqsRecord->category->parent ? $pqsRecord->category->parent->cat_name : null,
                    'serial_numbers' => $serialNumbers,
                    'accountable_officer' => $pqsRecord->accountableOfficer ? [
                        'id' => $pqsRecord->accountableOfficer->employee_id,
                        'name' => $pqsRecord->accountableOfficer->full_name,
                        'position' => $pqsRecord->accountableOfficer->position ? $pqsRecord->accountableOfficer->position->position_name : null,
                    ] : null,
                ] : null,
                'print_url' => route('ics.print', $icsRecord),
            ],
        ]);
    }

    public function print(IcsRecord $icsRecord)
    {
        $icsRecord->load('pqsRecord.category', 'pqsRecord.accountableOfficer.position');
        return view('inventory.ics.print', compact('icsRecord'));
    }

    public function printPdf(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $categoryFilters = $request->input('categories', []);

        if (!is_array($categoryFilters)) {
            $categoryFilters = array_filter([$categoryFilters]);
        }

        $categoryFilters = array_values(array_filter(
            array_map(
                static fn ($value) => is_numeric($value) ? (int) $value : null,
                $categoryFilters
            ),
            static fn ($value) => $value !== null
        ));

        $query = IcsRecord::query()->with('pqsRecord.category', 'pqsRecord.accountableOfficer');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('ics_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('pqsRecord', function ($pqsQuery) use ($search) {
                        $pqsQuery->where('property_no', 'like', "%{$search}%")
                            ->orWhere('article', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($categoryFilters)) {
            $query->whereHas('pqsRecord', function ($pqsQuery) use ($categoryFilters) {
                $pqsQuery->whereIn('cat_id', $categoryFilters);
            });
        }

        $records = $query->orderByDesc('ics_no')->get();

        return view('inventory.ics.print-list', [
            'records' => $records,
            'search' => $search,
            'categoryFilters' => $categoryFilters,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $categoryFilters = $request->input('categories', []);

        if (!is_array($categoryFilters)) {
            $categoryFilters = array_filter([$categoryFilters]);
        }

        $categoryFilters = array_values(array_filter(
            array_map(
                static fn ($value) => is_numeric($value) ? (int) $value : null,
                $categoryFilters
            ),
            static fn ($value) => $value !== null
        ));

        $query = IcsRecord::query()->with('pqsRecord.category', 'pqsRecord.accountableOfficer');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('ics_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('pqsRecord', function ($pqsQuery) use ($search) {
                        $pqsQuery->where('property_no', 'like', "%{$search}%")
                            ->orWhere('article', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($categoryFilters)) {
            $query->whereHas('pqsRecord', function ($pqsQuery) use ($categoryFilters) {
                $pqsQuery->whereIn('cat_id', $categoryFilters);
            });
        }

        $records = $query->orderByDesc('ics_no')->get();

        $html = view('inventory.ics.excel', ['records' => $records])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="ICS-Registry-' . date('Y-m-d') . '.xls"');
    }
}
