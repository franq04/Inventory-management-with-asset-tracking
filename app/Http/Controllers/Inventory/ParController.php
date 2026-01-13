<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ParRecord;
use App\Models\PqsRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ParController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $categoryFilters = $request->input('categories', []);
        $assignmentFilter = $request->input('assignment');

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

        $query = ParRecord::query()->with('pqsRecord.category');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('par_no', 'like', "%{$search}%")
                    ->orWhere('article_desc', 'like', "%{$search}%")
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

        $parRecords = $query
            ->orderByDesc('par_no')
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            $tableHtml = view('inventory.par.partials.records-table', [
                'records' => $parRecords,
            ])->render();

            return response()->json([
                'html' => $tableHtml,
                'total' => $parRecords->total(),
            ]);
        }

        $totalPar = ParRecord::count();
        $totalAmount = (float) ParRecord::sum('amount');
        $unassignedAssets = PqsRecord::doesntHave('icsRecord')->whereDoesntHave('parRecord')->count();

        $stats = [
            'total' => $totalPar,
            'totalAmount' => $totalAmount,
            'averageUsefulLife' => null, // PAR records may not have useful life
            'unassigned' => $unassignedAssets,
            'totalValue' => $totalAmount,
        ];

        $categories = Category::query()
            ->orderBy('cat_name')
            ->get(['cat_id', 'cat_name']);

        $recentPar = ParRecord::query()
            ->with('pqsRecord')
            ->orderByDesc('par_no')
            ->limit(5)
            ->get();

        return view('inventory.par.index', [
            'records' => $parRecords,
            'stats' => $stats,
            'search' => $search,
            'categoryFilters' => $categoryFilters,
            'assignmentFilter' => $assignmentFilter,
            'categories' => $categories,
            'recentPar' => $recentPar,
        ]);
    }

    public function show(ParRecord $parRecord)
    {
        $parRecord->load('pqsRecord.category.parent', 'pqsRecord.accountableOfficer.position');
        
        $pqsRecord = $parRecord->pqsRecord;
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
                'par_no' => $parRecord->par_no,
                'article_desc' => $parRecord->article_desc,
                'quantity' => (int) $parRecord->quantity,
                'unit' => $parRecord->unit,
                'unit_value' => (float) $parRecord->unit_value,
                'amount' => (float) $parRecord->amount,
                'date_acquired' => optional($parRecord->date_acquired)->format('M d, Y'),
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
                'print_url' => route('par.print', $parRecord),
            ],
        ]);
    }

    public function print(ParRecord $parRecord)
    {
        $parRecord->load('pqsRecord.category', 'pqsRecord.accountableOfficer.position');
        return view('inventory.par.print', compact('parRecord'));
    }

    public function printPdf(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $categoryFilters = $request->input('categories', []);
        $assignmentFilter = $request->input('assignment');

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

        $query = ParRecord::query()->with('pqsRecord.category', 'pqsRecord.accountableOfficer');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('par_no', 'like', "%{$search}%")
                    ->orWhere('article_desc', 'like', "%{$search}%")
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

        if ($assignmentFilter === 'assigned') {
            $query->whereHas('pqsRecord.accountableOfficer');
        } elseif ($assignmentFilter === 'unassigned') {
            $query->whereDoesntHave('pqsRecord.accountableOfficer');
        }

        $records = $query->orderBy('par_no')->get();

        return view('inventory.par.print-list', [
            'records' => $records,
            'search' => $search,
            'categoryFilters' => $categoryFilters,
            'assignmentFilter' => $assignmentFilter,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $categoryFilters = $request->input('categories', []);
        $assignmentFilter = $request->input('assignment');

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

        $query = ParRecord::query()->with('pqsRecord.category', 'pqsRecord.accountableOfficer');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('par_no', 'like', "%{$search}%")
                    ->orWhere('article_desc', 'like', "%{$search}%")
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

        if ($assignmentFilter === 'assigned') {
            $query->whereHas('pqsRecord.accountableOfficer');
        } elseif ($assignmentFilter === 'unassigned') {
            $query->whereDoesntHave('pqsRecord.accountableOfficer');
        }

        $records = $query->orderBy('par_no')->get();

        $html = view('inventory.par.excel', ['records' => $records])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="PAR-Registry-' . date('Y-m-d') . '.xls"');
    }
}
