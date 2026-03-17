<div class="grid grid-cols-1 gap-6 border-t border-gray-100 bg-gray-50/50 p-6 sm:grid-cols-2 xl:grid-cols-3" id="parentCategoryGrid">
    @forelse ($categoryTree as $node)
        <div class="flex flex-col overflow-hidden rounded-2xl border border-emerald-950/10 bg-white shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)] transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-xl">
            <div class="flex flex-col gap-3 p-5">
                <div class="flex items-start justify-between">
                    <p class="text-lg font-bold text-gray-900 flex items-center gap-3">
                        <i class="fas fa-folder-tree text-emerald-600 text-xl"></i>
                        <span>{{ $node->cat_name }}</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition" data-category-id="{{ $node->cat_id }}" data-category-name="{{ $node->cat_name }}" data-category-description="{{ $node->description }}" data-action="edit-category" title="Edit Category">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 hover:text-red-700 transition" data-category-id="{{ $node->cat_id }}" data-category-name="{{ $node->cat_name }}" data-action="delete-category" title="Delete Category">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition" data-parent-id="{{ $node->cat_id }}" data-parent-name="{{ $node->cat_name }}" data-action="add-subcategory" title="Add Subcategory">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                    <span class="inline-flex items-center gap-1.5" title="Category ID"><i class="fas fa-hashtag text-gray-400"></i>{{ $node->cat_id }}</span>
                    <span class="inline-flex items-center gap-1.5" title="Assets in this category"><i class="fas fa-boxes-stacked text-blue-400"></i>{{ number_format($node->pqs_records_count) }}</span>
                    <span class="inline-flex items-center gap-1.5" title="Subcategories"><i class="fas fa-layer-group text-purple-400"></i>{{ $node->children->count() }}</span>
                </div>
                @if ($node->description)
                    <p class="mt-1 text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($node->description, 100) }}</p>
                @endif
            </div>
            <div class="mt-auto border-t border-emerald-950/10 bg-[#fbfdfc] px-5 py-4 rounded-b-2xl">
                @php
                    $children = $node->children;
                    $childrenCount = $children->count();
                    $perPage = 3;
                @endphp
                @if ($childrenCount > 0)
                    <div id="subcats-{{ $node->cat_id }}" class="subcategory-list" data-per-page="{{ $perPage }}" data-total="{{ $childrenCount }}">
                        <div class="space-y-3">
                            @foreach ($children as $idx => $child)
                                <div class="subcat-item" data-index="{{ $idx }}">
                                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-white p-3">
                                        <div class="flex items-center gap-2 text-gray-700 font-medium text-sm">
                                            <i class="fas fa-tag text-gray-400"></i>
                                            <span>{{ $child->cat_name }}</span>
                                        </div>
                                        <span class="inline-flex items-center gap-2 text-xs font-semibold bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full">
                                            {{ number_format($child->pqs_records_count) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 mt-2 text-xs">
                                        <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-blue-200 text-blue-600 hover:bg-blue-50 transition" data-category-id="{{ $child->cat_id }}" data-category-name="{{ $child->cat_name }}" data-category-description="{{ $child->description }}" data-parent-id="{{ $node->cat_id }}" data-parent-name="{{ $node->cat_name }}" data-action="edit-category">
                                            <i class="fas fa-pen"></i> Edit
                                        </button>
                                        <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded-md border border-red-200 text-red-600 hover:bg-red-50 transition" data-category-id="{{ $child->cat_id }}" data-category-name="{{ $child->cat_name }}" data-action="delete-category">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($childrenCount > $perPage)
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-600">
                                <button type="button" class="subcat-prev inline-flex items-center gap-1 px-2.5 py-1 rounded-md border bg-white font-semibold text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Previous page"><i class="fas fa-chevron-left"></i> Prev</button>
                                <div class="subcat-pager font-semibold" aria-live="polite"><span class="subcat-current">1</span> / <span class="subcat-total">{{ ceil($childrenCount / $perPage) }}</span></div>
                                <button type="button" class="subcat-next inline-flex items-center gap-1 px-2.5 py-1 rounded-md border bg-white font-semibold text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Next page">Next <i class="fas fa-chevron-right"></i></button>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-center text-gray-400 italic py-4">No subcategories found.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="sm:col-span-2 xl:col-span-4 text-center py-12 text-gray-500">
            <i class="fas fa-folder-open text-5xl text-gray-300"></i>
            <p class="mt-4 font-medium">No categories have been registered yet.</p>
            <p class="text-sm">Click "Add New Category" to get started.</p>
        </div>
    @endforelse
</div>

<div class="px-6 py-4 border-t border-gray-100" data-parent-pagination>
    {{ $categoryTree->hasPages() ? $categoryTree->onEachSide(1)->links('vendor.pagination.procurement') : '' }}
</div>
