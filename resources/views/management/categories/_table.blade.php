<div class="bg-white border border-gray-100 rounded-2xl shadow-sm" id="categoriesTableContainer" data-total="{{ $categories->total() }}">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3">Category</th>
                    <th class="px-6 py-3">Parent</th>
                    <th class="px-6 py-3">Description</th>
                    <th class="px-6 py-3 text-right">Assets</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $category->cat_name }}</div>
                            <div class="text-xs text-gray-500 font-mono">ID: {{ $category->cat_id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($category->parent)
                                {{ $category->parent->cat_name }}
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Root Category</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-sm truncate" title="{{$category->description}}">
                            {{ $category->description ?: '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($category->pqs_records_count > 0)
                                <span class="font-semibold text-gray-800">{{ number_format($category->pqs_records_count) }}</span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button class="text-[#1a3a2d] hover:text-opacity-80 font-semibold text-xs">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                            <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                            <p class="mt-3 font-medium">No categories matched your filters.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($categories->hasPages())
    <div class="px-6 py-4 border-t border-gray-100" id="categoriesPagination">
        {{ $categories->onEachSide(1)->links() }}
    </div>
    @endif
</div>
