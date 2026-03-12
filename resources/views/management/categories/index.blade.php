@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div class="space-y-8 animate-card">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Manage Categories</h2>
            <p class="mt-1 text-gray-500">Organize and classify assets with a structured category system.</p>
        </div>
        <button type="button" class="bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center gap-2" id="addCategoryBtn">
            <i class="fas fa-plus-circle"></i>
            Add New Category
        </button>
    </div>

    {{-- Enhanced Stat Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $statCards = [
                ['key' => 'total', 'label' => 'Total Categories', 'value' => $stats['total'], 'icon' => 'fa-sitemap', 'color' => 'blue'],
                ['key' => 'root', 'label' => 'Parent Categories', 'value' => $stats['root'], 'icon' => 'fa-folder-tree', 'color' => 'purple'],
                ['key' => 'child', 'label' => 'Child Categories', 'value' => $stats['child'], 'icon' => 'fa-folder', 'color' => 'emerald'],
                ['key' => 'withPqs', 'label' => 'Categories With Assets', 'value' => $stats['withPqs'], 'icon' => 'fa-boxes-stacked', 'color' => 'amber'],
            ];
            $colors = [
                'blue' => 'bg-blue-100 text-blue-600',
                'purple' => 'bg-purple-100 text-purple-600',
                'emerald' => 'bg-emerald-100 text-emerald-600',
                'amber' => 'bg-amber-100 text-amber-600',
            ];
        @endphp
        @foreach ($statCards as $card)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 flex items-center gap-5 transition hover:shadow-xl hover:-translate-y-1">
            <div class="flex-shrink-0 w-14 h-14 rounded-full flex items-center justify-center {{ $colors[$card['color']] }}">
                <i class="fa-solid {{ $card['icon'] }} text-2xl"></i>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider font-semibold text-gray-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900" data-stat="{{ $card['key'] }}">{{ number_format($card['value']) }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Main Content Area with Filters and Category Cards --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-lg">
        <div class="p-6">
            {{-- Enhanced Filters & Actions --}}
            <form method="GET" class="space-y-4" data-category-filters>
                <div class="grid grid-cols-1 gap-4 items-end">
                    <div class="relative w-full">
                        <label for="search" class="text-xs font-semibold text-gray-500">Search</label>
                        <i class="fas fa-search absolute left-4 top-1/2 mt-2 -translate-y-1/2 text-gray-400"></i>
                        <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search by category name, code, or description..." class="w-full rounded-xl border-gray-200 pl-11 shadow-sm focus:border-[#1a3a2d] focus:ring-[#1a3a2d]" data-auto-submit />
                    </div>
                </div>
                 <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                     <span class="text-sm text-gray-500" data-parent-count>{{ number_format($categoryTree->total()) }} parent categories found</span>
                     <div class="flex items-center gap-2">
                        <button type="button" id="categoriesPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                            <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                        </button>
                        <button type="button" id="categoriesExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 shadow-sm hover:bg-green-100">
                            <i class="fas fa-file-excel text-green-600"></i> Export Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>

    {{-- Responsive Grid for Category Cards --}}
    <div data-parent-region>
        @include('management.categories.partials.parent-grid', ['categoryTree' => $categoryTree])
    </div>
    </div>
</div>
@endsection

@push('scripts')
@include('management.categories.partials.modal')
<script>
    (() => {
        window.__categoriesPageCleanup?.();

        const controller = new AbortController();
        const { signal } = controller;
        window.__categoriesPageCleanup = () => controller.abort();

        const tableWrapper = document.getElementById('categoriesTableWrapper');
        const totalSpan = document.getElementById('categoriesTotalCount');
        const modal = document.querySelector('[data-category-modal]');
        const modalTitle = modal.querySelector('[data-modal-title]');
        const modalSubtitle = modal.querySelector('[data-modal-subtitle]');
        const modalMode = modal.querySelector('[data-modal-mode]');
        const modalParentInput = modal.querySelector('[data-modal-parent]');
        const parentIndicator = modal.querySelector('[data-parent-indicator]');
        const parentNameSpan = modal.querySelector('[data-parent-name]');
        const categoryForm = modal.querySelector('[data-category-form]');
        const submitLabel = modal.querySelector('[data-submit-label]');
        const submitButton = modal.querySelector('[data-submit-button]');
        const idField = modal.querySelector('#categoryIdField');
        const nameField = modal.querySelector('#categoryNameField');
        const descriptionField = modal.querySelector('#categoryDescriptionField');
    const parentRegionSelector = '[data-parent-region]';
    const filtersForm = document.querySelector('[data-category-filters]');

        document.querySelectorAll('[data-auto-submit]').forEach(function (element) {
            element.addEventListener('input', function () {
                const form = element.closest('form');
                if (!form) return;
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                params.delete('page');
                params.delete('parent_page');
                updateBrowserQuery(params);
                refreshOverview(params);
            });
        });

        function bindCategoryPaginationLinks() {
            const container = document.getElementById('categoriesTableContainer');
            if (!container) return;
            container.querySelectorAll('a').forEach(link => {
                if (!link.href || !link.href.includes('page=')) return;
                if (link.dataset.ajaxBound === 'true') return;
                link.dataset.ajaxBound = 'true';
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(res => res.json())
                        .then(data => {
                            if (tableWrapper && data.html) {
                                tableWrapper.innerHTML = data.html;
                            }
                            if (totalSpan && typeof data.total !== 'undefined') {
                                totalSpan.textContent = `${Number(data.total).toLocaleString()} records found`;
                            }
                            bindCategoryPaginationLinks();
                        });
                });
            });
        }

        function openModal(config) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            modalMode.value = config.mode;
            categoryForm.action = config.action;
            categoryForm.method = 'POST';

            const existingMethodField = categoryForm.querySelector('input[name="_method"]');
            if (existingMethodField) {
                existingMethodField.remove();
            }

            if (config.mode === 'edit') {
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PUT';
                categoryForm.appendChild(methodField);
            }

            idField.value = config.cat_id || '';
            nameField.value = config.cat_name || '';
            descriptionField.value = config.description || '';

            idField.disabled = config.mode === 'edit';

            if (config.parent_id) {
                parentIndicator.classList.remove('hidden');
                modalParentInput.value = config.parent_id;
                parentNameSpan.textContent = config.parent_name || '';
            } else {
                parentIndicator.classList.add('hidden');
                modalParentInput.value = '';
                parentNameSpan.textContent = '';
            }

            modalTitle.textContent = config.title;
            modalSubtitle.textContent = config.subtitle;
            submitLabel.textContent = config.submitLabel;
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            categoryForm.reset();
            idField.disabled = false;
            parentIndicator.classList.add('hidden');
            const existingMethodField = categoryForm.querySelector('input[name="_method"]');
            if (existingMethodField) {
                existingMethodField.remove();
            }
        }

        modal.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        modal.querySelector('[data-modal-overlay]').addEventListener('click', closeModal);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        }, { signal });

        document.getElementById('addCategoryBtn').addEventListener('click', function () {
            openModal({
                mode: 'create',
                action: '{{ route('categories.store') }}',
                title: 'Add Parent Category',
                subtitle: 'Create a new parent category',
                submitLabel: 'Save Category'
            });
        });

        function updateBrowserQuery(params) {
            const displayParams = new URLSearchParams(params);
            displayParams.delete('section');
            const queryString = displayParams.toString();
            history.replaceState(null, '', queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname);
        }

        function refreshOverview(customParams = null) {
            const baseParams = customParams
                ? new URLSearchParams(customParams)
                : new URLSearchParams(window.location.search);

            baseParams.delete('section');

            const fetchParams = new URLSearchParams(baseParams);
            fetchParams.set('section', 'overview');
            const url = `${window.location.pathname}?${fetchParams.toString()}`;

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then(data => {
                    if (data.stats) {
                        Object.entries(data.stats).forEach(([key, value]) => {
                            const statEl = document.querySelector(`[data-stat="${key}"]`);
                            if (statEl) {
                                statEl.textContent = Number(value).toLocaleString();
                            }
                        });
                    }

                    if (typeof data.parentCount !== 'undefined') {
                        const parentCountEl = document.querySelector('[data-parent-count]');
                        if (parentCountEl) {
                            parentCountEl.textContent = `${Number(data.parentCount).toLocaleString()} parent categories found`;
                        }
                    }

                    if (data.parentHtml) {
                        const parentRegion = document.querySelector(parentRegionSelector);
                        if (parentRegion) {
                            parentRegion.innerHTML = data.parentHtml;
                            bindCardActions();
                            bindParentPaginationLinks();
                            initSubcategoryPagination();
                        }
                    }
                })
                .catch(async (errorResponse) => {
                    if (errorResponse && typeof errorResponse.json === 'function') {
                        try {
                            const errorData = await errorResponse.json();
                            alert(errorData.message || 'Failed to refresh categories.');
                            return;
                        } catch (error) {
                            console.error('Failed to parse refresh error.', error);
                        }
                    }
                    window.location.reload();
                });
        }

        function bindParentPaginationLinks() {
            const pagination = document.querySelector('[data-parent-pagination]');
            if (!pagination) return;

            pagination.querySelectorAll('a').forEach(link => {
                if (!link.href) return;
                if (link.dataset.ajaxBound === 'true') return;
                link.dataset.ajaxBound = 'true';
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const linkUrl = new URL(link.href);
                    const params = new URLSearchParams(linkUrl.search);
                    params.delete('page');
                    params.delete('section');
                    updateBrowserQuery(params);
                    refreshOverview(params);
                });
            });
        }

        function bindCardActions() {
            document.querySelectorAll('[data-action="add-subcategory"]').forEach(button => {
                if (button.dataset.bound === 'true') return;
                button.dataset.bound = 'true';
                button.addEventListener('click', function () {
                    openModal({
                        mode: 'create-child',
                        action: `{{ url('management/categories') }}/${button.dataset.parentId}/children`,
                        parent_id: button.dataset.parentId,
                        parent_name: button.dataset.parentName,
                        title: 'Add Subcategory',
                        subtitle: `Register a subcategory under ${button.dataset.parentName}`,
                        submitLabel: 'Save Subcategory'
                    });
                });
            });

            document.querySelectorAll('[data-action="edit-category"]').forEach(button => {
                if (button.dataset.bound === 'true') return;
                button.dataset.bound = 'true';
                button.addEventListener('click', function () {
                    openModal({
                        mode: 'edit',
                        action: `{{ url('management/categories') }}/${button.dataset.categoryId}`,
                        cat_id: button.dataset.categoryId,
                        cat_name: button.dataset.categoryName,
                        description: button.dataset.categoryDescription,
                        parent_id: button.dataset.parentId,
                        parent_name: button.dataset.parentName,
                        title: button.dataset.parentId ? `Edit Subcategory` : 'Edit Category',
                        subtitle: button.dataset.parentId ? `Update details for ${button.dataset.categoryName}` : `Update parent category ${button.dataset.categoryName}`,
                        submitLabel: 'Update Category'
                    });
                });
            });

            document.querySelectorAll('[data-action="delete-category"]').forEach(button => {
                if (button.dataset.bound === 'true') return;
                button.dataset.bound = 'true';
                button.addEventListener('click', function () {
                    if (!confirm(`Delete category ${button.dataset.categoryName}? This action cannot be undone.`)) return;
                    fetch(`{{ url('management/categories') }}/${button.dataset.categoryId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ _method: 'DELETE' })
                    })
                        .then(async response => {
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => ({}));
                                throw new Error(errorData.message || 'Failed to delete category.');
                            }
                            return response.json();
                        })
                        .then(() => refreshOverview())
                        .catch(error => {
                            alert(error.message || 'Failed to delete category.');
                        });
                });
            });
        }

        bindCardActions();
        bindParentPaginationLinks();

        categoryForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitButton.disabled = true;
            const formData = new FormData(categoryForm);
            fetch(categoryForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then(() => {
                    closeModal();
                    refreshOverview();
                })
                .catch(async (errorResponse) => {
                    if (errorResponse.json) {
                        const data = await errorResponse.json();
                        alert(data.message || 'Failed to save category.');
                    } else {
                        alert('Failed to save category.');
                    }
                })
                .finally(() => {
                    submitButton.disabled = false;
                });
        });

        // Client-side pagination for subcategories within each card
        function initSubcategoryPagination() {
            document.querySelectorAll('.subcategory-list').forEach(function (container) {
                const perPage = Number(container.dataset.perPage) || 4;
                const total = Number(container.dataset.total) || 0;
                if (total <= perPage) return;

                const items = Array.from(container.querySelectorAll('.subcat-item'));
                const totalPages = Math.ceil(total / perPage);
                const currentSpan = container.querySelector('.subcat-current');
                const totalSpan = container.querySelector('.subcat-total');
                const prevBtn = container.querySelector('.subcat-prev');
                const nextBtn = container.querySelector('.subcat-next');
                let current = 1;

                function renderPage(page) {
                    current = Math.min(Math.max(1, page), totalPages);
                    const start = (current - 1) * perPage;
                    const end = start + perPage;
                    items.forEach((el, idx) => {
                        el.style.display = (idx >= start && idx < end) ? '' : 'none';
                    });
                    if (currentSpan) currentSpan.textContent = current;
                    if (totalSpan) totalSpan.textContent = totalPages;
                    if (prevBtn) prevBtn.disabled = current <= 1;
                    if (nextBtn) nextBtn.disabled = current >= totalPages;
                }

                renderPage(1); // Initial render
                if (prevBtn) prevBtn.addEventListener('click', () => renderPage(current - 1));
                if (nextBtn) nextBtn.addEventListener('click', () => renderPage(current + 1));
            });
        }
        bindCategoryPaginationLinks();
        initSubcategoryPagination();
        window.addEventListener('popstate', () => refreshOverview());

        // PDF and Excel Export Handlers
        (function() {
            const pdfBtn = document.getElementById('categoriesPrintPdfBtn');
            const excelBtn = document.getElementById('categoriesExportExcelBtn');
            const form = document.querySelector('[data-category-filters]');

            if (pdfBtn) {
                pdfBtn.addEventListener('click', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = '{{ route("categories.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
                    window.open(url, '_blank');
                });
            }

            if (excelBtn) {
                excelBtn.addEventListener('click', function() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams(formData);
                    const url = '{{ route("categories.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
                    window.location.href = url;
                });
            }
        })();
        document.addEventListener('turbo:before-cache', window.__categoriesPageCleanup, { once: true, signal });
    })();
</script>
@endpush