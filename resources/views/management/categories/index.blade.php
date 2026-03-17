@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div class="space-y-8 animate-card" id="categoriesPage">
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Asset Taxonomy
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Manage Categories</h2>
                <p class="text-sm text-white/75">Organize and classify assets with a structured category system.</p>
            </div>
        </div>
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
    <div class="rounded-[26px] border border-emerald-950/8 bg-white/95 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
        <div class="p-6">
            {{-- Enhanced Filters & Actions --}}
            <form method="GET" class="space-y-4" data-category-filters>
                <div class="grid grid-cols-1 gap-4 items-end">
                    <div class="w-full">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Search</label>
                        <div class="relative mt-1">
                            <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                            <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search by category name, code, or description..." class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-3 text-sm shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:ring-4 focus:ring-[#1a3a2d]/10" data-auto-submit />
                        </div>
                    </div>
                </div>
                 <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-4">
                     <span class="text-sm text-gray-500" data-parent-count>{{ number_format($categoryTree->total()) }} parent categories found</span>
                     <div class="flex flex-col items-end gap-2">
                        <div class="flex items-center justify-end gap-2">
                            <a id="categoriesResetFilters" href="{{ route('categories.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                            <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg" id="addCategoryBtn">
                                <i class="fas fa-plus-circle"></i>
                                Add New Category
                            </button>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" id="categoriesPrintPdfBtn" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 shadow-sm transition-all duration-300 hover:border-red-300 hover:bg-red-100 hover:shadow-md">
                                <i class="fas fa-file-pdf text-red-500"></i> Print PDF
                            </button>
                            <button type="button" id="categoriesExportExcelBtn" class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-semibold text-green-700 shadow-sm transition-all duration-300 hover:border-green-300 hover:bg-green-100 hover:shadow-md">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
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

<div id="categoriesToast" class="pointer-events-none fixed right-4 top-20 z-[120] hidden min-w-[260px] max-w-md rounded-xl border border-emerald-800 bg-[#1a3a2d] px-5 py-3 text-sm font-semibold text-white shadow-2xl"></div>

<div id="categoryDeleteModal" class="fixed inset-0 z-[130] hidden opacity-0 transition-opacity duration-300" aria-labelledby="categoryDeleteTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-delete-modal></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="delete-modal-panel relative w-full max-w-md rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between rounded-t-2xl bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-5 py-4 text-white">
                <h3 id="categoryDeleteTitle" class="text-lg font-bold tracking-tight">Delete Category</h3>
                <button type="button" class="rounded-lg p-2 text-white/80 transition-all hover:bg-white/10 hover:text-white" data-close-delete-modal data-delete-focus>
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4 px-5 py-4">
                <p class="text-sm text-gray-700">
                    Delete <span id="categoryDeleteName" class="font-semibold text-gray-900"></span>?
                    This action cannot be undone.
                </p>
                <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                    <button type="button" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" data-close-delete-modal>
                        Cancel
                    </button>
                    <button type="button" id="categoryDeleteConfirmBtn" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
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
        const modalPanel = modal.querySelector('.modal-panel');
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
        const resetLink = document.getElementById('categoriesResetFilters');
        const toastEl = document.getElementById('categoriesToast');
        const deleteModal = document.getElementById('categoryDeleteModal');
        const deleteModalPanel = deleteModal?.querySelector('.delete-modal-panel') ?? null;
        const deleteFocusTarget = deleteModal?.querySelector('[data-delete-focus]') ?? null;
        const deleteNameEl = document.getElementById('categoryDeleteName');
        const deleteConfirmBtn = document.getElementById('categoryDeleteConfirmBtn');

        let filterDebounceTimer = null;
        let pendingDelete = null;

        const syncBodyScrollLock = () => {
            const modalOpen = modal && !modal.classList.contains('hidden');
            const deleteOpen = deleteModal && !deleteModal.classList.contains('hidden');
            document.body.classList.toggle('overflow-hidden', Boolean(modalOpen || deleteOpen));
        };

        const showToast = (message, type = 'success') => {
            if (!toastEl || !message) return;

            toastEl.textContent = message;
            toastEl.classList.remove('hidden', 'border-emerald-800', 'bg-[#1a3a2d]', 'border-red-800', 'bg-red-700');
            if (type === 'error') {
                toastEl.classList.add('border-red-800', 'bg-red-700');
            } else {
                toastEl.classList.add('border-emerald-800', 'bg-[#1a3a2d]');
            }

            setTimeout(() => {
                toastEl.classList.add('hidden');
            }, 2500);
        };

        const setOverviewLoading = (isLoading) => {
            const region = document.querySelector(parentRegionSelector);
            if (!region) return;
            region.classList.toggle('opacity-60', isLoading);
            region.classList.toggle('pointer-events-none', isLoading);
        };

        const scheduleOverviewRefresh = (params) => {
            if (filterDebounceTimer) {
                clearTimeout(filterDebounceTimer);
            }

            filterDebounceTimer = setTimeout(() => {
                updateBrowserQuery(params, 'replace');
                refreshOverview(params);
            }, 280);
        };

        document.querySelectorAll('[data-auto-submit]').forEach(function (element) {
            element.addEventListener('input', function () {
                const form = element.closest('form');
                if (!form) return;
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                params.delete('page');
                params.delete('parent_page');
                scheduleOverviewRefresh(params);
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
            syncBodyScrollLock();

            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                modalPanel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
            });

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
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            modalPanel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');

            setTimeout(() => {
                modal.classList.add('hidden');
                syncBodyScrollLock();
                categoryForm.reset();
                idField.disabled = false;
                parentIndicator.classList.add('hidden');
                const existingMethodField = categoryForm.querySelector('input[name="_method"]');
                if (existingMethodField) {
                    existingMethodField.remove();
                }
            }, 300);
        }

        function openDeleteModal(categoryId, categoryName) {
            if (!deleteModal || !deleteModalPanel) return;

            pendingDelete = {
                id: categoryId,
                name: categoryName,
            };

            if (deleteNameEl) {
                deleteNameEl.textContent = categoryName || 'this category';
            }

            deleteModal.classList.remove('hidden');
            syncBodyScrollLock();

            requestAnimationFrame(() => {
                deleteModal.classList.remove('opacity-0');
                deleteModal.classList.add('opacity-100');
                deleteModalPanel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                deleteFocusTarget?.focus();
            });
        }

        function closeDeleteModal() {
            if (!deleteModal || !deleteModalPanel) return;

            deleteModal.classList.remove('opacity-100');
            deleteModal.classList.add('opacity-0');
            deleteModalPanel.classList.add('opacity-0', 'scale-95', 'translate-y-2');

            setTimeout(() => {
                deleteModal.classList.add('hidden');
                syncBodyScrollLock();
                pendingDelete = null;
            }, 300);
        }

        modal.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        modal.querySelector('[data-modal-overlay]').addEventListener('click', closeModal);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }

            if (event.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) {
                closeDeleteModal();
            }
        }, { signal });

        deleteModal?.addEventListener('click', function (event) {
            const closeEl = event.target instanceof Element ? event.target.closest('[data-close-delete-modal]') : null;
            if (!closeEl) return;
            event.preventDefault();
            closeDeleteModal();
        });

        deleteConfirmBtn?.addEventListener('click', function () {
            if (!pendingDelete?.id) return;

            deleteConfirmBtn.setAttribute('disabled', 'disabled');

            fetch(`{{ url('management/categories') }}/${pendingDelete.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
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
                .then((data) => {
                    closeDeleteModal();
                    refreshOverview();
                    showToast(data?.message || 'Category deleted successfully.');
                })
                .catch(error => {
                    showToast(error.message || 'Failed to delete category.', 'error');
                })
                .finally(() => {
                    deleteConfirmBtn.removeAttribute('disabled');
                });
        });

        document.getElementById('addCategoryBtn').addEventListener('click', function () {
            openModal({
                mode: 'create',
                action: '{{ route('categories.store') }}',
                title: 'Add Parent Category',
                subtitle: 'Create a new parent category',
                submitLabel: 'Save Category'
            });
        });

        function updateBrowserQuery(params, mode = 'replace') {
            const displayParams = new URLSearchParams(params);
            displayParams.delete('section');
            const queryString = displayParams.toString();
            const nextUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;

            if (mode === 'push') {
                history.pushState(null, '', nextUrl);
            } else {
                history.replaceState(null, '', nextUrl);
            }
        }

        function refreshOverview(customParams = null) {
            const baseParams = customParams
                ? new URLSearchParams(customParams)
                : new URLSearchParams(window.location.search);

            const searchField = filtersForm?.querySelector('#search');
            if (searchField) {
                searchField.value = baseParams.get('search') || '';
            }

            baseParams.delete('section');

            const fetchParams = new URLSearchParams(baseParams);
            fetchParams.set('section', 'overview');
            const url = `${window.location.pathname}?${fetchParams.toString()}`;

            setOverviewLoading(true);

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
                            showToast(errorData.message || 'Failed to refresh categories.', 'error');
                            return;
                        } catch (error) {
                            console.error('Failed to parse refresh error.', error);
                        }
                    }
                    showToast('Failed to refresh categories.', 'error');
                })
                .finally(() => {
                    setOverviewLoading(false);
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
                    updateBrowserQuery(params, 'push');
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
                    openDeleteModal(button.dataset.categoryId, button.dataset.categoryName);
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
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw response;
                    return response.json();
                })
                .then(() => {
                    closeModal();
                    refreshOverview();
                    showToast('Category saved successfully.');
                })
                .catch(async (errorResponse) => {
                    if (errorResponse.json) {
                        const data = await errorResponse.json();
                        showToast(data.message || 'Failed to save category.', 'error');
                    } else {
                        showToast('Failed to save category.', 'error');
                    }
                })
                .finally(() => {
                    submitButton.disabled = false;
                });
        });

        resetLink?.addEventListener('click', function (event) {
            event.preventDefault();
            if (filtersForm) {
                const searchField = filtersForm.querySelector('#search');
                if (searchField) {
                    searchField.value = '';
                }
            }

            const params = new URLSearchParams();
            updateBrowserQuery(params, 'push');
            refreshOverview(params);
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