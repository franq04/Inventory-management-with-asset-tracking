<table border="1">
    <thead>
        <tr style="background-color: #1a3a2d; color: white; font-weight: bold;">
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Description</th>
            <th>Parent Category</th>
            <th>Type</th>
            <th>PQS Records Count</th>
            <th>Child Categories</th>
        </tr>
    </thead>
    <tbody>
        @foreach($categories as $category)
            <tr>
                <td>{{ $category->cat_id }}</td>
                <td>{{ $category->cat_name }}</td>
                <td>{{ $category->description ?? '' }}</td>
                <td>{{ $category->parent?->cat_name ?? 'None' }}</td>
                <td>{{ $category->parent_id ? 'Child' : 'Parent' }}</td>
                <td>{{ $category->pqs_records_count ?? 0 }}</td>
                <td>{{ $category->children?->count() ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <td colspan="7">Total Categories: {{ number_format($categories->count()) }}</td>
        </tr>
    </tfoot>
</table>
