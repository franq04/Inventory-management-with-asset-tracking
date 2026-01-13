<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PqsRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PqsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $categoryFilter = $request->input('category');
        $assignmentFilter = $request->input('assignment');

        $query = PqsRecord::query()->with(['category', 'accountableOfficer', 'icsRecord', 'parRecord']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('property_no', 'like', "%{$search}%")
                    ->orWhere('article', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('accountableOfficer', function ($officerQuery) use ($search) {
                        $officerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryFilter) {
            $query->where('cat_id', $categoryFilter);
        }

        if ($assignmentFilter === 'ics') {
            $query->whereHas('icsRecord');
        } elseif ($assignmentFilter === 'par') {
            $query->whereHas('parRecord');
        } elseif ($assignmentFilter === 'unassigned') {
            $query->whereDoesntHave('icsRecord')->whereDoesntHave('parRecord');
        }

        $pqsRecords = $query
            ->orderBy('property_no')
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            $tableHtml = view('inventory.pqs.partials.records-table', [
                'records' => $pqsRecords,
            ])->render();

            return response()->json([
                'html' => $tableHtml,
                'total' => $pqsRecords->total(),
            ]);
        }

        $stats = [
            'total' => PqsRecord::count(),
            'withIcs' => PqsRecord::has('icsRecord')->count(),
            'withPar' => PqsRecord::has('parRecord')->count(),
            'unassigned' => PqsRecord::doesntHave('icsRecord')->whereDoesntHave('parRecord')->count(),
            'totalValue' => (float) PqsRecord::sum('total_value'),
        ];

        $categories = Category::query()
            ->orderBy('cat_name')
            ->get(['cat_id', 'cat_name']);

        $recentAssets = PqsRecord::query()
            ->with('category')
            ->orderByDesc('date_acquired')
            ->limit(5)
            ->get();

        return view('inventory.pqs.index', [
            'records' => $pqsRecords,
            'stats' => $stats,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'assignmentFilter' => $assignmentFilter,
            'categories' => $categories,
            'recentAssets' => $recentAssets,
        ]);
    }

    public function show(PqsRecord $pqsRecord): JsonResponse
    {
        $pqsRecord->loadMissing([
            'category.parent',
            'accountableOfficer',
            'icsRecord',
            'parRecord',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformRecord($pqsRecord),
        ]);
    }

    protected function transformRecord(PqsRecord $record): array
    {
        $category = $record->category;
        $parentCategory = $category?->parent;
        $accountableOfficer = $record->accountableOfficer;
        $icsRecord = $record->icsRecord;
        $parRecord = $record->parRecord;

        $serialNumbers = collect(preg_split('/[\r\n;,]+/', (string) $record->serial_number))
            ->map(static fn ($value) => trim($value))
            ->filter(static fn ($value) => $value !== '')
            ->values()
            ->all();

        return [
            'property_no' => $record->property_no,
            'article' => $record->article,
            'description' => $record->description,
            'serial_numbers' => $serialNumbers,
            'date_acquired' => optional($record->date_acquired)->toDateString(),
            'unit' => $record->unit,
            'quantity' => $record->on_hand_per_count,
            'unit_value' => $record->unit_value !== null ? (float) $record->unit_value : null,
            'total_value' => $record->total_value !== null ? (float) $record->total_value : null,
            'remarks' => $record->remarks,
            'category' => $category?->cat_name,
            'category_parent' => $parentCategory?->cat_name,
            'accountable_officer' => $accountableOfficer ? [
                'id' => $accountableOfficer->employee_id,
                'name' => $accountableOfficer->full_name,
            ] : null,
            'ics_record' => $icsRecord ? [
                'ics_no' => $icsRecord->ics_no,
                'description' => $icsRecord->description,
                'quantity' => (int) $icsRecord->quantity,
                'unit_cost' => (float) $icsRecord->unit_cost,
                'total_cost' => (float) $icsRecord->total_cost,
                'estimated_useful_life' => $icsRecord->estimated_useful_life,
            ] : null,
            'par_record' => $parRecord ? [
                'par_no' => $parRecord->par_no,
                'article_desc' => $parRecord->article_desc,
                'quantity' => (int) $parRecord->quantity,
                'unit_value' => (float) $parRecord->unit_value,
                'amount' => (float) $parRecord->amount,
                'date_acquired' => optional($parRecord->date_acquired)->toDateString(),
            ] : null,
        ];
    }

    public function printPdf(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $categoryFilter = $request->input('category');
        $assignmentFilter = $request->input('assignment');

        $query = PqsRecord::query()->with(['category', 'accountableOfficer', 'icsRecord', 'parRecord']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('property_no', 'like', "%{$search}%")
                    ->orWhere('article', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('accountableOfficer', function ($officerQuery) use ($search) {
                        $officerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryFilter) {
            $query->where('cat_id', $categoryFilter);
        }

        if ($assignmentFilter === 'ics') {
            $query->whereHas('icsRecord');
        } elseif ($assignmentFilter === 'par') {
            $query->whereHas('parRecord');
        } elseif ($assignmentFilter === 'unassigned') {
            $query->whereDoesntHave('icsRecord')->whereDoesntHave('parRecord');
        }

        $records = $query->orderBy('property_no')->get();

        return view('inventory.pqs.print', [
            'records' => $records,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'assignmentFilter' => $assignmentFilter,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $categoryFilter = $request->input('category');
        $assignmentFilter = $request->input('assignment');

        $query = PqsRecord::query()->with(['category', 'accountableOfficer', 'icsRecord', 'parRecord']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('property_no', 'like', "%{$search}%")
                    ->orWhere('article', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('accountableOfficer', function ($officerQuery) use ($search) {
                        $officerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryFilter) {
            $query->where('cat_id', $categoryFilter);
        }

        if ($assignmentFilter === 'ics') {
            $query->whereHas('icsRecord');
        } elseif ($assignmentFilter === 'par') {
            $query->whereHas('parRecord');
        } elseif ($assignmentFilter === 'unassigned') {
            $query->whereDoesntHave('icsRecord')->whereDoesntHave('parRecord');
        }

        $records = $query->orderBy('property_no')->get();

        $html = view('inventory.pqs.excel', ['records' => $records])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="PQS-Registry-' . date('Y-m-d') . '.xls"');
    }
}
