import $ from 'jquery';

const csrfToken = () => $('meta[name="csrf-token"]').attr('content');

const formatCurrency = (value) => {
    const number = Number(value ?? 0);
    return `₱${number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const todayIso = () => new Date().toISOString().slice(0, 10);

$(() => {
    const config = window.inventoryAssignmentConfig || {};
    const routes = config.routes || {};
    const categories = config.categories || [];
    const locations = config.locations || [];

    const $itemsBody = $('#inventoryItemsBody');
    const $search = $('#inventorySearch');
    const $stateFilter = $('#inventoryState');
    const $paginationContainer = $('#paginationContainer');
    const $pageLinks = $('#pageLinks');
    const $pageInfo = $('#pageInfo');
    const $pageMeta = $('#pageMeta');
    const pageQueryKey = 'inventory_page';
    let currentItems = [];
    let currentPage = 1;
    const itemsPerPage = 5;

    const $dateFrom = $('#date_from');
    const $dateTo = $('#date_to');
    const $applyDateBtn = $('#inventoryApplyDate');
    const $printPdfBtn = $('#inventoryPrintPdfBtn');
    const $exportExcelBtn = $('#inventoryExportExcelBtn');
    const $pendingCount = $('#inventoryPendingCount');
    const $recordedCount = $('#inventoryRecordedCount');

    const $createModal = $('#inventoryCreateModal');
    const $createForm = $('#inventoryCreateForm');
    const $createErrors = $('#inventoryCreateErrors');
    const $categorySelect = $('#inventoryCategory');
    const $subCategorySelect = $('#inventorySubCategory');
    const $descriptionField = $('#inventoryPropertyDescription');
    const $unitField = $('#inventoryUnit');
    const $quantityField = $('#inventoryQuantity');
    const $unitCostField = $('#inventoryUnitCost');
    const $totalCostField = $('#inventoryTotalCost');
    const $dateAcquiredField = $('#inventoryDateAcquired');
    const $usefulLifeField = $('#inventoryUsefulLife');
    const $initialLocationField = $('#inventoryInitialLocation');
    const $initialCustodianField = $('#inventoryInitialCustodian');
    const $serialsContainer = $('#inventorySerialsContainer');

    const $itemDescription = $('#inventoryItemDescription');
    const $itemSource = $('#inventoryItemSource');
    const $itemAccepted = $('#inventoryItemAccepted');
    const $itemSupplier = $('#inventoryItemSupplier');
    const $itemAccountableOfficer = $('#inventoryAccountableOfficer');

    const $viewModal = $('#inventoryViewModal');
    const $successModal = $('#inventorySuccessModal');
    const $successMessage = $('#inventorySuccessMessage');
    const $feedbackModal = $('#inventoryFeedbackModal');
    const $feedbackTitle = $('#inventoryFeedbackTitle');
    const $feedbackSubtitle = $('#inventoryFeedbackSubtitle');
    const $feedbackMessage = $('#inventoryFeedbackMessage');
    const $feedbackIcon = $('#inventoryFeedbackIcon');
    const $feedbackClose = $('#inventoryFeedbackClose');
    const $viewPropertyNo = $('#inventoryViewPropertyNo');
    const $viewSource = $('#inventoryViewSource');
    const $viewDateAcquired = $('#inventoryViewDateAcquired');
    const $viewDescription = $('#inventoryViewDescription');
    const $viewCategory = $('#inventoryViewCategory');
    const $viewSubCategory = $('#inventoryViewSubCategory');
    const $viewQuantity = $('#inventoryViewQuantity');
    const $viewUnitCost = $('#inventoryViewUnitCost');
    const $viewTotalCost = $('#inventoryViewTotalCost');
    const $viewOfficer = $('#inventoryViewOfficer');
    const $viewUsefulLife = $('#inventoryViewUsefulLife');
    const $viewSerials = $('#inventoryViewSerials');
    const $viewRemarks = $('#inventoryViewRemarks');
    const $viewDocumentBadge = $('#inventoryViewDocumentBadge');
    const $viewDocumentMeta = $('#inventoryViewDocumentMeta');
    const $viewDocumentExtra = $('#inventoryViewDocumentExtra');

    let fetchTimeout = null;
    let currentStoreUrl = null;
    let appliedDateFrom = null;
    let appliedDateTo = null;

    const escapeHtml = (value = '') => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const escapeAttr = (value = '') => escapeHtml(value);

    const renderLocationOptions = (selectedLocation = null) => {
        if (!$initialLocationField.length) {
            return;
        }

        const selected = selectedLocation == null ? '' : String(selectedLocation);
        const options = ['<option value="">No location selected</option>'];

        locations.forEach((location) => {
            const value = String(location.id ?? '');
            if (!value) {
                return;
            }

            const label = escapeHtml(location.label || location.name || value);
            const selectedAttr = selected !== '' && value === selected ? ' selected' : '';
            options.push(`<option value="${escapeAttr(value)}"${selectedAttr}>${label}</option>`);
        });

        $initialLocationField.html(options.join(''));
    };

    const getCurrentPageFromUrl = () => {
        const params = new URLSearchParams(window.location.search);
        const page = Number.parseInt(params.get(pageQueryKey) || '1', 10);
        return Number.isFinite(page) && page > 0 ? page : 1;
    };

    const syncCurrentPageToUrl = (page, replace = false) => {
        const nextUrl = new URL(window.location.href);
        if (page <= 1) {
            nextUrl.searchParams.delete(pageQueryKey);
        } else {
            nextUrl.searchParams.set(pageQueryKey, String(page));
        }

        const nextState = { inventoryPage: page };
        if (replace) {
            window.history.replaceState(nextState, '', nextUrl.toString());
        } else {
            window.history.pushState(nextState, '', nextUrl.toString());
        }
    };

    const applyCategoriesToSelect = (selectedParent = null, selectedChild = null) => {
        $categorySelect.empty();
        const parentPlaceholderSelected = selectedParent ? '' : 'selected';
        $categorySelect.append(`<option value="" disabled ${parentPlaceholderSelected}>Select category</option>`);
        categories.forEach((parent) => {
            $categorySelect.append(`<option value="${parent.id}" ${String(parent.id) === String(selectedParent) ? 'selected' : ''}>${parent.name}</option>`);
        });

        populateSubCategories(selectedParent, selectedChild);
    };

    const populateSubCategories = (parentId, selectedChild = null) => {
        $subCategorySelect.empty();
        const childPlaceholderSelected = selectedChild ? '' : 'selected';
        $subCategorySelect.append(`<option value="" disabled ${childPlaceholderSelected}>Select sub-category</option>`);

        const parent = categories.find((cat) => String(cat.id) === String(parentId));
        if (!parent) {
            return;
        }

        parent.children.forEach((child) => {
            $subCategorySelect.append(`<option value="${child.id}" ${String(child.id) === String(selectedChild) ? 'selected' : ''}>${child.name}</option>`);
        });

        if (!parent.children.length) {
            $subCategorySelect.append('<option value="" disabled>No sub-categories available</option>');
        }
    };

    $categorySelect.on('change', function () {
        populateSubCategories($(this).val(), null);
    });

    const toggleModal = ($modal, open) => {
        const $panel = $modal.find('.modal-panel').first();

        if (open) {
            $modal.removeClass('hidden');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    $modal.scrollTop(0);
                    if ($panel.length) {
                        $panel.scrollTop(0);
                        $panel.find('.overflow-y-auto, .overflow-y-scroll').scrollTop(0);
                    }
                    $modal.removeClass('opacity-0');
                    if ($panel.length) {
                        $panel.removeClass('opacity-0 scale-95 translate-y-2');
                    }
                });
            });
            $('body').addClass('overflow-hidden');
            return;
        }

        $modal.addClass('opacity-0');
        if ($panel.length) {
            $panel.addClass('opacity-0 scale-95 translate-y-2');
        }

        setTimeout(() => {
            $modal.addClass('hidden');

            if ($modal.is($createModal)) {
                $createForm[0].reset();
                $createErrors.addClass('hidden').empty();
                currentStoreUrl = null;
                populateSubCategories(null, null);
                $categorySelect.empty();
                renderLocationOptions(null);
                $totalCostField.val('0.00');
                renderSerialInputs(1);
                $itemAccountableOfficer.text('—');
                $initialCustodianField.val('');
            }

            if ($createModal.hasClass('hidden') && $viewModal.hasClass('hidden') && $successModal.hasClass('hidden') && $feedbackModal.hasClass('hidden')) {
                $('body').removeClass('overflow-hidden');
            }
        }, 300);
    };

    const showSuccessModal = (message) => {
        if ($successMessage.length) {
            $successMessage.text(message || 'PQS record created successfully.');
        }

        toggleModal($successModal, true);
    };

    const showFeedbackModal = ({
        title = 'Notice',
        subtitle = 'Please review this message.',
        message = 'Something needs your attention.',
        tone = 'warning',
        buttonLabel = 'OK',
    } = {}) => {
        if ($feedbackTitle.length) {
            $feedbackTitle.text(title);
        }
        if ($feedbackSubtitle.length) {
            $feedbackSubtitle.text(subtitle);
        }
        if ($feedbackMessage.length) {
            $feedbackMessage.text(message);
        }
        if ($feedbackClose.length) {
            $feedbackClose.text(buttonLabel);
            $feedbackClose.removeClass('bg-amber-500 hover:bg-amber-600 bg-rose-600 hover:bg-rose-700 bg-blue-600 hover:bg-blue-700');
        }

        if ($feedbackIcon.length) {
            $feedbackIcon.removeClass('bg-amber-100 text-amber-700 bg-rose-100 text-rose-700 bg-blue-100 text-blue-700');
        }

        if (tone === 'error') {
            $feedbackIcon.html('<i class="fas fa-triangle-exclamation"></i>').addClass('bg-rose-100 text-rose-700');
            $feedbackClose.addClass('bg-rose-600 hover:bg-rose-700');
        } else if (tone === 'info') {
            $feedbackIcon.html('<i class="fas fa-circle-info"></i>').addClass('bg-blue-100 text-blue-700');
            $feedbackClose.addClass('bg-blue-600 hover:bg-blue-700');
        } else {
            $feedbackIcon.html('<i class="fas fa-circle-exclamation"></i>').addClass('bg-amber-100 text-amber-700');
            $feedbackClose.addClass('bg-amber-500 hover:bg-amber-600');
        }

        toggleModal($feedbackModal, true);
    };

    $(document).on('click', '[data-close-modal]', function () {
        toggleModal($(this).closest('.fixed'), false);
    });

    $(document).on('click', function (event) {
        const $target = $(event.target);
        if ($target.data('closeModal') !== undefined) {
            toggleModal($target.closest('.fixed'), false);
        }
    });

    $(document).on('keydown', (event) => {
        if (event.key === 'Escape') {
            if (!$createModal.hasClass('hidden')) toggleModal($createModal, false);
            if (!$viewModal.hasClass('hidden')) toggleModal($viewModal, false);
            if (!$successModal.hasClass('hidden')) toggleModal($successModal, false);
            if (!$feedbackModal.hasClass('hidden')) toggleModal($feedbackModal, false);
        }
    });

    const collectSerialInputs = () => $serialsContainer.find('input[name^="serial_numbers"]').map((_, input) => $(input).val()).get();

    const renderSerialInputs = (count, existingValues = []) => {
        const total = Number.isFinite(count) && count > 0 ? count : 1;
        const inputs = [];

        for (let index = 0; index < total; index += 1) {
            const value = escapeAttr(existingValues[index] ?? '');
            inputs.push(`
                <div class="flex items-center gap-3">
                    <span class="w-14 text-xs font-semibold uppercase tracking-wide text-gray-500">#${index + 1}</span>
                    <input type="text" name="serial_numbers[${index}]" value="${value}" class="flex-1 rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400" placeholder="Serial number" autocomplete="off">
                </div>
            `);
        }

        $serialsContainer.html(inputs.join(''));
    };

    const recalculateTotalCost = () => {
        const quantity = Number($quantityField.val() || 0);
        const unitCost = Number($unitCostField.val() || 0);
        const total = quantity * unitCost;
        $totalCostField.val(total.toFixed(2));
    };

    const handleQuantityInput = () => {
        const max = Number($quantityField.attr('max')) || Number.POSITIVE_INFINITY;
        let quantity = Number($quantityField.val());

        if (!Number.isFinite(quantity) || quantity <= 0) {
            quantity = 1;
        }

        if (quantity > max) {
            quantity = max;
            $quantityField.val(quantity);
        }

        recalculateTotalCost();
        const preservedValues = collectSerialInputs();
        renderSerialInputs(quantity, preservedValues);
    };

    $quantityField.on('input', handleQuantityInput);
    $unitCostField.on('input', recalculateTotalCost);

    const renderEmptyRow = (message) => {
        $itemsBody.html(`
            <tr>
                <td colspan="8" class="px-5 py-20 text-center text-gray-500">
                    <i class="fas fa-folder-open text-5xl text-gray-300 mb-4"></i>
                    <p class="font-medium text-lg">${message}</p>
                    <p class="text-sm">Try adjusting your filters or switch to another status tab.</p>
                </td>
            </tr>
        `);
    };

    const buildActionButtons = (item) => {
        const createStateAttrs = item.can_create ? '' : 'disabled aria-disabled="true"';
        const viewStateAttrs = item.property_no ? '' : 'disabled aria-disabled="true"';
        const showUrl = routes.show?.replace('__ID__', item.ia_item_id);
        const storeUrl = routes.store?.replace('__ID__', item.ia_item_id);
        const showUrlAttr = showUrl ?? '';
        const storeUrlAttr = storeUrl ?? '';

        return `
            <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" class="js-view-pqs inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs font-semibold text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-60"
                    data-item-id="${item.ia_item_id}"
                    data-show-url="${showUrlAttr}"
                    ${viewStateAttrs}>
                    <i class="fas fa-eye"></i>
                    View Record
                </button>
                <button type="button" class="js-create-pqs inline-flex cursor-pointer items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:-translate-y-0.5 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0 disabled:hover:shadow-md"
                    data-item-id="${item.ia_item_id}"
                    data-show-url="${showUrlAttr}"
                    data-store-url="${storeUrlAttr}"
                    ${createStateAttrs}>
                    <i class="fas fa-barcode"></i>
                    Create PQS
                </button>
            </div>
        `;
    };

    const renderPagination = () => {
        const totalItems = currentItems.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        }

        if (totalItems === 0) {
            $paginationContainer.addClass('hidden');
            return;
        }
        
        $paginationContainer.removeClass('hidden');
        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);
        
        $pageInfo.html(`Showing <span class="font-semibold text-gray-900">${start}</span> to <span class="font-semibold text-gray-900">${end}</span> of <span class="font-semibold text-gray-900">${totalItems}</span> results`);
        $pageMeta.html(`Showing page ${currentPage} of ${Math.max(totalPages, 1)} <span class="text-gray-400">(${totalItems} total records)</span>`);
        
        let linksHtml = '';

        if (currentPage <= 1) {
            linksHtml += '<span aria-disabled="true" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-medium text-gray-400 shadow-sm cursor-default">&laquo; Prev</span>';
        } else {
            linksHtml += `<button type="button" data-page="${currentPage - 1}" class="inline-flex h-10 items-center rounded-xl border border-[#1a3a2d]/15 bg-white px-4 text-sm font-medium text-[#496255] shadow-sm transition hover:border-[#1a3a2d]/30 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">&laquo; Prev</button>`;
        }

        const pageItems = [];
        for (let i = 1; i <= totalPages; i += 1) {
            const onBoundary = i === 1 || i === totalPages;
            const onWindow = i >= currentPage - 1 && i <= currentPage + 1;
            if (onBoundary || onWindow) {
                pageItems.push(i);
            }
        }

        let lastRendered = 0;
        pageItems.forEach((page) => {
            if (page - lastRendered > 1) {
                linksHtml += '<span aria-disabled="true" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-[#f8faf9] px-3 text-sm font-medium text-gray-400 shadow-sm">...</span>';
            }

            if (page === currentPage) {
                linksHtml += `<span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#1a3a2d] bg-[#1a3a2d] px-3 text-sm font-semibold text-white shadow-[0_12px_24px_-14px_rgba(26,58,45,0.8)]">${page}</span>`;
            } else {
                linksHtml += `<button type="button" data-page="${page}" aria-label="Go to page ${page}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-medium text-gray-600 shadow-sm transition hover:border-[#1a3a2d]/25 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">${page}</button>`;
            }

            lastRendered = page;
        });

        if (currentPage < totalPages) {
            linksHtml += `<button type="button" data-page="${currentPage + 1}" class="inline-flex h-10 items-center rounded-xl border border-[#1a3a2d]/15 bg-white px-4 text-sm font-medium text-[#496255] shadow-sm transition hover:border-[#1a3a2d]/30 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">Next &raquo;</button>`;
        } else {
            linksHtml += '<span aria-disabled="true" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-medium text-gray-400 shadow-sm cursor-default">Next &raquo;</span>';
        }
        
        $pageLinks.html(linksHtml);
    };

    $pageLinks.on('click', 'button[data-page]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const page = Number($(this).data('page'));
        if (page && page !== currentPage) {
            currentPage = page;
            syncCurrentPageToUrl(currentPage);
            renderItems();
        }
    });

    const renderItems = () => {
        const items = currentItems;
        if (!items.length) {
            renderEmptyRow('No accepted items found for the selected filters.');
            $pendingCount.text('0');
            $recordedCount.text('0');
            $paginationContainer.addClass('hidden');
            return;
        }

        const pending = items.filter((item) => item.can_create).length;
        const recorded = items.length - pending;
        $pendingCount.text(`${pending}`);
        $recordedCount.text(`${recorded}`);

        const paginatedItems = items.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);
        const rows = paginatedItems.map((item, index) => {
            const description = escapeHtml(item.item_description ?? '—');
            const category = escapeHtml(item.category ?? '—');
            const subCategory = escapeHtml(item.sub_category ?? '—');
            const unit = item.unit ? ` ${escapeHtml(item.unit)}` : '';
            const quantityValue = item.quantity != null ? escapeHtml(String(item.quantity)) : null;
            const quantityDisplay = quantityValue ? `${quantityValue}${unit}` : '—';
            const sourceSegments = [];
            if (item.po_no) sourceSegments.push(`PO ${item.po_no}`);
            if (item.ia_no) sourceSegments.push(`IA ${item.ia_no}`);
            if (item.supplier_name) sourceSegments.push(item.supplier_name);
            const sourceText = sourceSegments.length ? escapeHtml(sourceSegments.join(' • ')) : '—';

            const statusName = item.status_name ?? '';
            const statusLower = statusName.toLowerCase();
            let badgeClasses = 'bg-slate-100 text-slate-600';
            let badgeIcon = 'fa-tag';
            if (statusLower.includes('record')) {
                badgeClasses = 'bg-emerald-100 text-emerald-700';
                badgeIcon = 'fa-clipboard-check';
            } else if (statusLower.includes('accept') || statusLower.includes('pending')) {
                badgeClasses = 'bg-amber-100 text-amber-700';
                badgeIcon = 'fa-hourglass-half';
            }
            const statusBadge = statusName
                ? `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ${badgeClasses}"><i class="fas ${badgeIcon} text-[10px]"></i>${escapeHtml(statusName)}</span>`
                : '';

            const propertyInfo = item.property_no
                ? `<div class="mt-2 text-xs font-semibold text-[#1a3a2d]">Property No: ${escapeHtml(item.property_no)}</div>`
                : '';

            const delay = Math.min(index * 35, 280);

            return `
                <tr class="border-b last:border-0 hover:bg-gray-50 js-reveal-animated" style="opacity: 0; transform: translateY(6px); transition: opacity 220ms ease ${delay}ms, transform 260ms ease ${delay}ms;">
                    <td class="px-5 py-4 align-top">
                        <div class="font-semibold text-gray-800">${description}</div>
                        <div class="text-xs text-gray-500">IA: ${escapeHtml(item.ia_no ?? '—')} • PO: ${escapeHtml(item.po_no ?? '—')}</div>
                        <div class="mt-1">${statusBadge}</div>
                        ${propertyInfo}
                    </td>
                    <td class="px-5 py-4 align-top text-gray-700">${category}</td>
                    <td class="px-5 py-4 align-top text-gray-700">${subCategory}</td>
                    <td class="px-5 py-4 align-top text-right font-semibold text-gray-800">${quantityDisplay}</td>
                    <td class="px-5 py-4 align-top text-right text-gray-700">${formatCurrency(item.unit_cost)}</td>
                    <td class="px-5 py-4 align-top text-right text-gray-800 font-semibold">${formatCurrency(item.total_cost)}</td>
                    <td class="px-5 py-4 align-top text-gray-600">${sourceText}</td>
                    <td class="px-5 py-4 align-top text-right">${buildActionButtons(item)}</td>
                </tr>
            `;
        });

        $itemsBody.html(rows.join(''));
        renderPagination();

        requestAnimationFrame(() => {
            $itemsBody.find('tr.js-reveal-animated').each(function() {
                this.style.opacity = '1';
                this.style.transform = 'translateY(0)';
            });
        });
    };

    const fetchItems = () => {
        const params = {
            search: $search.val(),
            state: $stateFilter.val(),
        };

        // Include date filters only if the date range has been applied by the user
        if (appliedDateFrom) params.date_from = appliedDateFrom;
        if (appliedDateTo) params.date_to = appliedDateTo;

        $itemsBody.html(`
            <tr>
                <td colspan="8" class="px-5 py-20 text-center text-gray-500">
                    <i class="fas fa-spinner fa-pulse mb-4 text-3xl text-gray-300"></i>
                    <p class="font-medium text-lg">Loading properties...</p>
                </td>
            </tr>
        `);

        $.ajax({
            url: routes.items,
            method: 'GET',
            data: params,
            success: (response) => {
                currentItems = response.data?.items ?? [];
                currentPage = getCurrentPageFromUrl();
                renderItems();
                syncCurrentPageToUrl(currentPage, true);
            },
            error: () => {
                renderEmptyRow('Unable to load items at this time. Please try again later.');
                $pendingCount.text('0');
                $recordedCount.text('0');
                $paginationContainer.addClass('hidden');
            },
        });
    };

    window.__refreshInventoryAssignment = () => {
        if (
            $('#inventoryCreateModal').length && !$('#inventoryCreateModal').hasClass('hidden')
            || $('#inventoryViewModal').length && !$('#inventoryViewModal').hasClass('hidden')
            || $('#inventorySuccessModal').length && !$('#inventorySuccessModal').hasClass('hidden')
            || $('#inventoryFeedbackModal').length && !$('#inventoryFeedbackModal').hasClass('hidden')
        ) {
            return;
        }

        fetchItems();
    };

    const debounceFetch = () => {
        clearTimeout(fetchTimeout);
        fetchTimeout = setTimeout(fetchItems, 350);
    };

    $search.on('input', debounceFetch);
    $stateFilter.on('change', fetchItems);

    const buildInventoryExportUrl = (baseUrl) => {
        if (!baseUrl) {
            return null;
        }

        const targetUrl = new URL(baseUrl, window.location.origin);
        const searchValue = String($search.val() || '').trim();
        const stateValue = String($stateFilter.val() || 'all').trim();

        if (searchValue) {
            targetUrl.searchParams.set('search', searchValue);
        }

        if (stateValue) {
            targetUrl.searchParams.set('state', stateValue);
        }

        if (appliedDateFrom) {
            targetUrl.searchParams.set('date_from', appliedDateFrom);
        }

        if (appliedDateTo) {
            targetUrl.searchParams.set('date_to', appliedDateTo);
        }

        return targetUrl.toString();
    };

    $printPdfBtn.on('click', function () {
        const url = buildInventoryExportUrl($(this).data('printUrl'));
        if (!url) {
            return;
        }

        window.open(url, '_blank');
    });

    $exportExcelBtn.on('click', function () {
        const url = buildInventoryExportUrl($(this).data('excelUrl'));
        if (!url) {
            return;
        }

        window.location.href = url;
    });

    window.addEventListener('popstate', () => {
        currentPage = getCurrentPageFromUrl();
        if (currentItems.length) {
            renderItems();
        }
    });

    $(document).on('click', '.inventory-tab', function() {
        const $tab = $(this);
        const tabKey = $tab.data('inventory-tab');
        
        $stateFilter.val(tabKey).trigger('change');
        
        $('.inventory-tab').each(function() {
            const $t = $(this);
            const activeClass = $t.data('activeClass') || '';
            const inactiveClass = $t.data('inactiveClass') || '';
            
            $t.removeClass(activeClass).removeClass('is-selected').addClass(inactiveClass);
        });
        
        const activeClass = $tab.data('activeClass') || '';
        const inactiveClass = $tab.data('inactiveClass') || '';
        $tab.removeClass(inactiveClass).addClass(activeClass).addClass('is-selected');
    });

    // Apply button for date range: only clicking this will apply the date filters
    $applyDateBtn.on('click', () => {
        const from = $dateFrom.val() || null;
        const to = $dateTo.val() || null;

        // Simple validation: if one is provided ensure the other is too
        if ((from && !to) || (!from && to)) {
            showFeedbackModal({
                title: 'Incomplete Date Range',
                subtitle: 'Both dates are required to filter records.',
                message: 'Please provide both start and end dates to apply the date range.',
                tone: 'warning',
            });
            return;
        }

        appliedDateFrom = from;
        appliedDateTo = to;

        fetchItems();
    });

    const populateCreateModal = (data) => {
        const item = data || {};
        const categoriesData = {
            parent_id: item.category_id || null,
            sub_id: item.sub_category_id || null,
        };

        applyCategoriesToSelect(categoriesData.parent_id, categoriesData.sub_id);

        $('#inventoryIaItemId').val(item.ia_item_id);
        $descriptionField.val(item.property_record?.description || item.item_description || '');
        $unitField.val(item.unit || '');

        const acceptedQuantity = item.quantity || 1;
        $quantityField.attr('max', acceptedQuantity).val(acceptedQuantity);
        $unitCostField.val(item.unit_cost ?? 0);
        $totalCostField.val((acceptedQuantity * (item.unit_cost ?? 0)).toFixed(2));
        $dateAcquiredField.val(item.property_record?.date_acquired || todayIso());
        $usefulLifeField.val(item.property_record?.estimated_useful_life || '');
        const preferredLocationId = item.property_record?.current_location_id
            || item.recommended_initial_location_id
            || null;
        renderLocationOptions(preferredLocationId);
        renderSerialInputs(acceptedQuantity);

        $itemDescription.text(item.item_description ?? '—');
        $itemSource.text(`PO ${item.po_no ?? '—'} • IA ${item.ia_no ?? '—'}`);
        $itemAccepted.text(`${acceptedQuantity} accepted`);
        $itemSupplier.text(item.supplier_name ?? '—');
        $itemAccountableOfficer.text(item.accountable_officer_name ?? '—');
        $initialCustodianField.val(item.accountable_officer_name || 'No accountable officer linked');
    };

    const openCreateModal = (button) => {
        const showUrl = button.data('showUrl');
        currentStoreUrl = button.data('storeUrl');

        if (!showUrl || !currentStoreUrl) {
            showFeedbackModal({
                title: 'Configuration Missing',
                subtitle: 'Action cannot proceed right now.',
                message: 'Endpoints for this item are not configured.',
                tone: 'error',
            });
            return;
        }

        $.ajax({
            url: showUrl,
            method: 'GET',
            headers: { Accept: 'application/json' },
            success: (response) => {
                populateCreateModal(response.data);
                toggleModal($createModal, true);
            },
            error: () => {
                showFeedbackModal({
                    title: 'Load Failed',
                    subtitle: 'Unable to open create form.',
                    message: 'Unable to load item details.',
                    tone: 'error',
                });
            },
        });
    };

    const openViewModal = (button) => {
        const showUrl = button.data('showUrl');
        if (!showUrl) {
            return;
        }

        $.ajax({
            url: showUrl,
            method: 'GET',
            headers: { Accept: 'application/json' },
            success: (response) => {
                const data = response.data || {};
                if (!data.property_no && !data.property_record) {
                    showFeedbackModal({
                        title: 'No Record Yet',
                        subtitle: 'This item has not been recorded in PQS.',
                        message: 'No PQS record found for this item yet.',
                        tone: 'info',
                    });
                    return;
                }

                const propertyRecord = data.property_record || {};

                const propertyNumber = data.property_no || propertyRecord.property_no || '—';
                const unit = propertyRecord.unit || data.unit || '';
                const quantityRaw = propertyRecord.quantity ?? data.quantity ?? null;
                const quantityDisplay = quantityRaw != null
                    ? `${quantityRaw} ${unit}`.trim()
                    : (unit || '—');

                const sourceSegments = [];
                if (data.po_no) sourceSegments.push(`PO ${data.po_no}`);
                if (data.ia_no) sourceSegments.push(`IA ${data.ia_no}`);
                if (data.supplier_name) sourceSegments.push(data.supplier_name);
                $viewSource.text(sourceSegments.join(' • ') || '—');

                $viewPropertyNo.text(propertyNumber);
                $viewDateAcquired.text(propertyRecord.date_acquired || '—');
                $viewDescription.text(propertyRecord.description || data.item_description || '—');
                $viewCategory.text(data.category || '—');
                $viewSubCategory.text(data.sub_category || '—');
                $viewQuantity.text(quantityDisplay || '—');

                const unitCostValue = propertyRecord.unit_value ?? data.unit_cost ?? null;
                const totalCostValue = propertyRecord.total_value ?? data.total_cost ?? (quantityRaw != null && unitCostValue != null
                    ? Number(quantityRaw) * Number(unitCostValue)
                    : null);
                $viewUnitCost.text(unitCostValue != null ? formatCurrency(unitCostValue) : '—');
                $viewTotalCost.text(totalCostValue != null ? formatCurrency(totalCostValue) : '—');

                $viewOfficer.text(propertyRecord.accountable_officer_name || data.accountable_officer_name || '—');
                const usefulLife = propertyRecord.estimated_useful_life || data.ics_record?.estimated_useful_life || '';
                $viewUsefulLife.text(usefulLife || '—');

                const serialRaw = propertyRecord.serial_number || '';
                const serials = serialRaw
                    ? serialRaw.split(/[\n,;]+/).map((serial) => serial.trim()).filter(Boolean)
                    : [];
                if (serials.length) {
                    $viewSerials.html(serials.map((serial) => `
                        <li class="flex items-center gap-2 text-sm text-gray-700">
                            <i class="fas fa-circle text-[6px] text-gray-400"></i>
                            <span>${escapeHtml(serial)}</span>
                        </li>
                    `).join(''));
                } else {
                    $viewSerials.html('<li class="text-sm text-gray-400 italic">No serial numbers recorded.</li>');
                }

                $viewRemarks.text(propertyRecord.remarks || 'No additional remarks recorded.');

                const badgeBaseClasses = 'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-sm';
                let badgeClasses = 'bg-amber-100 text-amber-700';
                let badgeIcon = 'fa-hourglass-half';
                let badgeLabel = 'Pending Document';
                let docMetaText = 'No custodial document generated yet.';
                let docExtraText = 'Generate PQS record to create custodial documents automatically.';

                if (data.ics_record) {
                    badgeClasses = 'bg-emerald-100 text-emerald-700';
                    badgeIcon = 'fa-file-circle-check';
                    badgeLabel = data.ics_record.ics_no ? `ICS #${data.ics_record.ics_no}` : 'ICS Issued';
                    const quantityForDoc = data.ics_record.quantity ?? quantityRaw ?? 0;
                    const unitLabel = unit || 'unit(s)';
                    docMetaText = `${quantityForDoc} ${unitLabel} • ${formatCurrency(data.ics_record.total_cost)}`;
                    docExtraText = data.ics_record.estimated_useful_life
                        ? `Estimated useful life: ${data.ics_record.estimated_useful_life}`
                        : '';
                } else if (data.par_record) {
                    badgeClasses = 'bg-purple-100 text-purple-700';
                    badgeIcon = 'fa-file-contract';
                    badgeLabel = data.par_record.par_no ? `PAR #${data.par_record.par_no}` : 'PAR Issued';
                    const quantityForDoc = data.par_record.quantity ?? quantityRaw ?? 0;
                    const unitLabel = unit || 'unit(s)';
                    docMetaText = `${quantityForDoc} ${unitLabel} • ${formatCurrency(data.par_record.amount)}`;
                    docExtraText = data.par_record.date_acquired
                        ? `Acquired on ${data.par_record.date_acquired}`
                        : '';
                }

                $viewDocumentBadge
                    .attr('class', `${badgeBaseClasses} ${badgeClasses}`)
                    .html(`<i class="fas ${badgeIcon}"></i><span>${escapeHtml(badgeLabel)}</span>`);

                $viewDocumentMeta.text(docMetaText);
                if (docExtraText) {
                    $viewDocumentExtra.text(docExtraText).removeClass('hidden');
                } else {
                    $viewDocumentExtra.text('Generate PQS record to create custodial documents automatically.').removeClass('hidden');
                }

                toggleModal($viewModal, true);
            },
            error: () => {
                showFeedbackModal({
                    title: 'Load Failed',
                    subtitle: 'Unable to open the PQS record.',
                    message: 'Unable to load PQS record.',
                    tone: 'error',
                });
            },
        });
    };

    $(document).on('click', '.js-create-pqs', function () {
        if ($(this).is(':disabled')) {
            return;
        }
        openCreateModal($(this));
    });

    $(document).on('click', '.js-view-pqs', function () {
        if ($(this).is(':disabled')) {
            return;
        }
        openViewModal($(this));
    });

    $createForm.on('submit', function (event) {
        event.preventDefault();
        if (!currentStoreUrl) {
            showFeedbackModal({
                title: 'Configuration Missing',
                subtitle: 'Save action is not available.',
                message: 'No endpoint configured for saving this record.',
                tone: 'error',
            });
            return;
        }

        $createErrors.addClass('hidden').empty();
        const $submitBtn = $createForm.find('button[type="submit"]');
        $submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            url: currentStoreUrl,
            method: 'POST',
            data: $createForm.serialize(),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            success: (response) => {
                toggleModal($createModal, false);
                setTimeout(() => {
                    showSuccessModal(response.message || 'PQS record created successfully.');
                }, 320);
                fetchItems();
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $createErrors.html(messages.map((msg) => `<div>${msg}</div>`).join(''));
                    $createErrors.removeClass('hidden');
                } else if (xhr.responseJSON?.message) {
                    $createErrors.text(xhr.responseJSON.message).removeClass('hidden');
                } else {
                    $createErrors.text('Something went wrong while saving the record.').removeClass('hidden');
                }
            },
            complete: () => {
                $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });

    // Initialize state
    populateSubCategories(null, null);
    renderLocationOptions(null);
    renderSerialInputs(1);
    fetchItems();

    if (!window.__inventoryAssignmentAutoRefreshTimer) {
        window.__inventoryAssignmentAutoRefreshTimer = window.setInterval(() => {
            if (document.hidden) {
                return;
            }

            if (typeof window.__refreshInventoryAssignment === 'function') {
                window.__refreshInventoryAssignment();
            }
        }, 30000);
    }
});
