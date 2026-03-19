<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $parentFilter = $request->input('parent');

        $query = Category::query()
            ->with('parent')
            ->withCount('pqsRecords');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('cat_name', 'like', "%{$search}%")
                    ->orWhere('cat_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($parentFilter === 'root') {
            $query->whereNull('parent_id');
        } elseif ($parentFilter === 'child') {
            $query->whereNotNull('parent_id');
        } elseif ($parentFilter) {
            $query->where('parent_id', $parentFilter);
        }

        $sharedQueryParams = $request->except(['page', 'parent_page', 'section']);

        $categories = $query
            ->orderByDesc('cat_id')
            ->paginate(5)
            ->appends($sharedQueryParams);

        $stats = [
            'total' => Category::count(),
            'root' => Category::whereNull('parent_id')->count(),
            'child' => Category::whereNotNull('parent_id')->count(),
            'withPqs' => Category::has('pqsRecords')->count(),
        ];

        $parentOptions = Category::query()
            ->whereNull('parent_id')
            ->orderBy('cat_name')
            ->get(['cat_id', 'cat_name']);

        $topCategories = Category::query()
            ->withCount('pqsRecords')
            ->orderByDesc('pqs_records_count')
            ->limit(5)
            ->get();

        $parentPerPage = max(1, (int) $request->input('parent_per_page', 6));

        $categoryTreeQuery = Category::query()
            ->with(['children' => function ($relation) {
                $relation->orderByDesc('cat_id')->withCount('pqsRecords');
            }])
            ->withCount('pqsRecords')
            ->whereNull('parent_id');

        if ($search !== '') {
            $categoryTreeQuery->where(function ($builder) use ($search) {
                $builder->where('cat_name', 'like', "%{$search}%")
                    ->orWhere('cat_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($childQuery) use ($search) {
                        $childQuery->where('cat_name', 'like', "%{$search}%")
                            ->orWhere('cat_id', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        $categoryTree = $categoryTreeQuery
            ->orderByDesc('cat_id')
            ->paginate($parentPerPage, ['*'], 'parent_page')
            ->appends($sharedQueryParams);

        if ($request->ajax()) {
            if ($request->query('section') === 'overview') {
                $parentHtml = view('management.categories.partials.parent-grid', [
                    'categoryTree' => $categoryTree,
                ])->render();

                return response()->json([
                    'stats' => $stats,
                    'parentCount' => $categoryTree->total(),
                    'parentHtml' => $parentHtml,
                ]);
            }

            $html = view('management.categories._table', [
                'categories' => $categories,
            ])->render();

            return response()->json([
                'html' => $html,
                'total' => $categories->total(),
            ]);
        }

        return view('management.categories.index', [
            'categories' => $categories,
            'stats' => $stats,
            'search' => $search,
            'parentFilter' => $parentFilter,
            'parentOptions' => $parentOptions,
            'topCategories' => $topCategories,
            'categoryTree' => $categoryTree,
        ]);
    }

    public function printPdf(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $query = Category::query()->with(['parent', 'children'])->withCount('pqsRecords');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('cat_name', 'like', "%{$search}%")
                    ->orWhere('cat_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderByDesc('cat_id')->get();

        return view('management.categories.print', [
            'categories' => $categories,
            'search' => $search,
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $search = trim((string) $request->input('search'));

        $query = Category::query()->with(['parent', 'children'])->withCount('pqsRecords');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('cat_name', 'like', "%{$search}%")
                    ->orWhere('cat_id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderByDesc('cat_id')->get();

        $html = view('management.categories.excel', ['categories' => $categories])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="Categories-' . date('Y-m-d') . '.xls"');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cat_id' => ['required', 'string', 'max:50', 'unique:categories,cat_id'],
            'cat_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category = Category::create([
            'cat_id' => $validated['cat_id'],
            'cat_name' => $validated['cat_name'],
            'description' => $validated['description'] ?? '',
            'parent_id' => null,
        ]);

        return $this->respondWithMessage($request, 'Category created successfully.', 201, $category);
    }

    public function storeChild(Request $request, Category $category)
    {
        if ($category->parent_id !== null) {
            return $this->respondWithError($request, 'Only parent categories can contain subcategories.', 422);
        }

        $validated = $request->validate([
            'cat_id' => ['required', 'string', 'max:50', 'unique:categories,cat_id'],
            'cat_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $child = Category::create([
            'cat_id' => $validated['cat_id'],
            'cat_name' => $validated['cat_name'],
            'description' => $validated['description'] ?? '',
            'parent_id' => $category->cat_id,
        ]);

        return $this->respondWithMessage($request, 'Subcategory created successfully.', 201, $child);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'cat_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'cat_id')->ignore($category->cat_id, 'cat_id'),
            ],
            'cat_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'string', Rule::exists('categories', 'cat_id')],
        ]);

        $newParentId = $validated['parent_id'] ?? null;

        if ($newParentId === $category->cat_id) {
            return $this->respondWithError($request, 'A category cannot be its own parent.', 422);
        }

        if ($category->parent_id === null && $newParentId !== null && $category->children()->exists()) {
            return $this->respondWithError($request, 'Cannot convert a parent category with subcategories into a child category. Remove or reassign its subcategories first.', 422);
        }

        $category->update([
            'cat_id' => $validated['cat_id'],
            'cat_name' => $validated['cat_name'],
            'description' => $validated['description'] ?? '',
            'parent_id' => $newParentId,
        ]);

        return $this->respondWithMessage($request, 'Category updated successfully.', 200, $category);
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->parent_id === null && $category->children()->exists()) {
            return $this->respondWithError($request, 'Cannot delete a parent category that still has subcategories. Remove its subcategories first.', 422);
        }

        if ($category->pqsRecords()->exists()) {
            return $this->respondWithError($request, 'Cannot delete a category that is associated with assets.', 422);
        }

        $category->delete();

        return $this->respondWithMessage($request, 'Category deleted successfully.');
    }

    protected function respondWithMessage(Request $request, string $message, int $status = 200, ?Category $category = null)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'category' => $category,
            ], $status);
        }

        return redirect()->route('categories.index')->with('status', $message);
    }

    protected function respondWithError(Request $request, string $message, int $status)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return redirect()->back()->withErrors(['category' => $message]);
    }
}
