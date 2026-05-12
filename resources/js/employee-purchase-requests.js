import $ from 'jquery';
import {
    csrfToken,
    formatCurrency,
    escapeHtml,
    formatDateDisplay,
    isDatePast,
    itemStatusMeta,
    decisionMessages,
    getRequestStatuses,
    renderItemStatus,
    renderItemActions,
    updateRowAfterDecision,
    hideToast,
    showToast,
    toggleModal,
} from './purchase-requests-shared.js';

let employeeBound = false;

const softNavigate = (url = window.location.href, options = {}) => {
    const { replace = false } = options;
    if (window.Turbo && typeof window.Turbo.visit === 'function') {
        window.Turbo.visit(url, { action: replace ? 'replace' : 'advance' });
        return;
    }

    if (url && url !== window.location.href) {
        window.location.assign(url);
        return;
    }

    window.location.reload();
};

const initEmployeePurchaseRequests = () => {
    // Guard against double-binding direct event handlers on persistent elements
    if (employeeBound) {
        return;
    }
    employeeBound = true;

    const config = window.employeePrConfig;
    const hasEmployeeConfig = Boolean(config);
    const bpiLogoUrl = config?.logos?.bpi || '/images/bpi-logo.png';
    const pqsLogoUrl = config?.logos?.pqs || '/images/pqslogo.png';

    const $employeeTable = $('#employeePurchaseRequestsTable');
    if ($employeeTable.length) {
        const $employeeRows = $employeeTable.find('tbody tr[data-row]');
        const $noResultsRow = $('#employeePrNoResults');
        const $emptyStateRow = $employeeTable.find('tbody tr[data-empty-state]');
        const $searchInput = $('#employeePrSearch');
        const $statusTabs = $('#employeePrStatusTabs');
        const $statusButtons = $statusTabs.find('[data-status-btn]');
        const $resetFilters = $('#employeePrResetFilters');
        const $resultCount = $('#employeePrResultCount');
        const $totalCount = $('#employeePrTotalCount');
        const totalRows = $employeeRows.length;
        let activeStatus = String(config?.activeStatusFilter || '').trim();

        const setStatusButtonState = (targetStatus) => {
            if (!$statusButtons.length) {
                return;
            }

            $statusButtons.each(function () {
                const $btn = $(this);
                const btnStatus = String($btn.data('status-btn') ?? '');
                const inactiveClass = ($btn.data('inactive-class') || '').trim();
                const activeClass = ($btn.data('active-class') || '').trim();

                if (inactiveClass) {
                    $btn.removeClass(inactiveClass);
                }
                if (activeClass) {
                    $btn.removeClass(activeClass);
                }
                $btn.removeClass('is-active');

                if (btnStatus === targetStatus) {
                    if (activeClass) {
                        $btn.addClass(activeClass);
                    }
                    $btn.addClass('is-active');
                } else if (inactiveClass) {
                    $btn.addClass(inactiveClass);
                }
            });
        };

        const applyEmployeeFilters = () => {
            const searchTerm = ($searchInput.val() || '').trim().toLowerCase();
            const statusTerm = String(activeStatus || '').toLowerCase();
            let visibleRows = 0;

            $employeeRows.each(function () {
                const $row = $(this);
                const rowSearch = String($row.data('search') || '').toLowerCase();
                const rowStatus = String($row.data('status') || '').toLowerCase();
                const matchesSearch = searchTerm === '' || rowSearch.includes(searchTerm);
                const matchesStatus = statusTerm === '' || rowStatus === statusTerm;
                const shouldShow = matchesSearch && matchesStatus;

                if (shouldShow) {
                    $row.removeClass('hidden');
                    visibleRows += 1;
                } else {
                    $row.addClass('hidden');
                }
            });

            const showNoResults = visibleRows === 0 && totalRows > 0;
            if ($noResultsRow.length) {
                $noResultsRow.toggleClass('hidden', !showNoResults);
            }

            if ($emptyStateRow.length) {
                const hasData = totalRows > 0;
                $emptyStateRow.toggleClass('hidden', hasData);
            }

            if ($resultCount.length) {
                $resultCount.text(totalRows === 0 ? 0 : visibleRows);
            }

            if ($resetFilters.length) {
                const hasFilters = searchTerm !== '' || statusTerm !== '';
                $resetFilters.prop('disabled', !hasFilters);
            }
        };

        if ($searchInput.length) {
            $searchInput.on('input', applyEmployeeFilters);
        }

        if ($statusTabs.length) {
            $statusTabs.on('click', '[data-status-btn]', function (event) {
                event.preventDefault();

                const buttonStatus = String($(this).data('status-btn') ?? '');
                const targetUrl = String($(this).data('status-target-url') ?? '').trim();
                const nextStatus = buttonStatus === activeStatus ? '' : buttonStatus;

                if (targetUrl) {
                    softNavigate(targetUrl);
                    return;
                }

                const baseIndexUrl = config?.indexUrl || window.location.pathname;
                if (nextStatus) {
                    const separator = baseIndexUrl.includes('?') ? '&' : '?';
                    softNavigate(`${baseIndexUrl}${separator}status=${encodeURIComponent(nextStatus)}`);
                } else {
                    softNavigate(baseIndexUrl);
                }
            });
        }

        if ($resetFilters.length) {
            $resetFilters.on('click', (event) => {
                event.preventDefault();

                const hasSearchTerm = String($searchInput.val() || '').trim() !== '';
                if (hasSearchTerm) {
                    $searchInput.val('');
                    applyEmployeeFilters();
                    return;
                }

                if (activeStatus) {
                    softNavigate(config?.indexUrl || window.location.pathname);
                }
            });
        }

        setStatusButtonState(activeStatus);
        applyEmployeeFilters();
    }

    const $modal = $('#createRequestModal');
    const $openBtn = $('#openCreateRequest');
    const $closeElements = $modal.find('[data-close-modal]');
    const $form = $('#purchaseRequestForm');
    const $itemRows = $('#itemRows');
    const $addItemRow = $('#addItemRow');
    const $errors = $('#formErrors');
    const $toast = '#employeePrToast';
    const $waitModal = $('#employeeWaitModal');
    const $waitForm = $('#employeeWaitForm');
    const $waitDate = $('#employeeWaitDate');
    const $waitNote = $('#employeeWaitNote');
    const $waitErrors = $('#employeeWaitErrors');
    const $waitContext = $('#employeeWaitContext');
    const $modalTitle = $('#purchaseRequestModalTitle');
    const $modalSubtitle = $('#purchaseRequestModalSubtitle');
    const $submitLabel = $('#purchaseRequestSubmitLabel');
    let activeWaitContext = { priId: null, status: null };
    let activeEditContext = { prNo: null, updateUrl: null };
    let rowIndex = 0;
    const suggestionCache = new Map();
    const suggestionMinChars = 2;
    const suggestionDelayMs = 250;
    const suggestionLimit = 10;
    let $floatingSuggestionPanel = $('#prItemDescriptionSuggest');

    if (!$floatingSuggestionPanel.length) {
        $floatingSuggestionPanel = $(
            '<div id="prItemDescriptionSuggest" class="fixed z-[120] hidden max-h-52 overflow-auto rounded-md border border-gray-300 bg-white shadow-lg"></div>'
        );
        $('body').append($floatingSuggestionPanel);
    }

    const updateRowTotal = ($row) => {
        const quantity = Number($row.find('[data-quantity]').val() || 0);
        const unitCost = Number($row.find('[data-unit-cost]').val() || 0);
        const total = quantity * unitCost;
        $row.find('[data-total-display]').text(formatCurrency(total));
    };

    const updateGrandTotal = () => {
        let grandTotal = 0;
        $itemRows.find('tr').each((_, tr) => {
            const $tr = $(tr);
            const quantity = Number($tr.find('[data-quantity]').val() || 0);
            const unitCost = Number($tr.find('[data-unit-cost]').val() || 0);
            grandTotal += quantity * unitCost;
        });
        $('#prGrandTotal').text(formatCurrency(grandTotal));
        validateFundAllocation(grandTotal);
        return grandTotal;
    };

    const validateFundAllocation = (grandTotal) => {
        const $fundSelect = $('#fundAllocation');
        const $warning = $('#fundAllocationWarning');
        const $warningText = $('#fundAllocationWarningText');
        const $selectedOption = $fundSelect.find('option:selected');
        const $label = $('#selectedFundLabel');
        const $remainingLabel = $('#selectedFundRemaining');

        if (!$selectedOption.val()) {
            $warning.addClass('hidden');
            $label.find('span').text('—');
            $remainingLabel.find('span').text('₱0.00');
            return;
        }

        const remaining = Number($selectedOption.data('remaining') || 0);
        const clusterName = $selectedOption.text().split('-')[0]?.trim() || '';

        // update label display
        $label.find('span').text(clusterName || '—');
        $remainingLabel.find('span').text(formatCurrency(remaining));

        if (grandTotal > remaining) {
            const shortfall = grandTotal - remaining;
            $warningText.html(`<strong>Insufficient funds!</strong> Total cost (₱${formatCurrency(grandTotal).replace('₱', '')}) exceeds available balance (₱${formatCurrency(remaining).replace('₱', '')}). Shortfall: <strong>₱${formatCurrency(shortfall).replace('₱', '')}</strong>`);
            $warning.removeClass('hidden');
        } else {
            $warning.addClass('hidden');
        }
    };

    const getDescriptionPanel = () => $floatingSuggestionPanel;

    const positionDescriptionPanel = ($textarea) => {
        if (!$textarea || !$textarea.length) {
            return;
        }

        const rect = $textarea[0].getBoundingClientRect();
        $floatingSuggestionPanel.css({
            left: `${rect.left}px`,
            top: `${rect.bottom + 4}px`,
            width: `${rect.width}px`,
        });
        $floatingSuggestionPanel.data('activeDescriptionInput', $textarea);
    };

    const isTextareaInView = ($textarea, $container) => {
        if (!$textarea || !$textarea.length || !$container || !$container.length) {
            return true;
        }

        const inputRect = $textarea[0].getBoundingClientRect();
        const containerRect = $container[0].getBoundingClientRect();
        return inputRect.bottom >= containerRect.top && inputRect.top <= containerRect.bottom;
    };

    const syncDescriptionPanelPosition = () => {
        const $active = $floatingSuggestionPanel.data('activeDescriptionInput');
        if (!$active || !$active.length) {
            return;
        }

        if ($modal.hasClass('hidden')) {
            hideDescriptionSuggestions($floatingSuggestionPanel);
            return;
        }

        const $container = $modal.find('.hide-scrollbar').first();
        if (!isTextareaInView($active, $container)) {
            hideDescriptionSuggestions($floatingSuggestionPanel);
            return;
        }

        positionDescriptionPanel($active);
    };

    const hideDescriptionSuggestions = ($panel) => {
        if ($panel && $panel.length) {
            $panel.addClass('hidden').empty();
            $panel.removeData('activeDescriptionInput');
        }
    };

    const renderDescriptionSuggestions = ($panel, suggestions = []) => {
        if (!Array.isArray(suggestions) || suggestions.length === 0) {
            hideDescriptionSuggestions($panel);
            return;
        }

        const html = suggestions.map((value) => {
            const safeValue = escapeHtml(value);
            return `<button type="button" class="block w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-emerald-50" data-description-suggestion>${safeValue}</button>`;
        }).join('');

        $panel.html(html).removeClass('hidden');
        const $active = $panel.data('activeDescriptionInput');
        if ($active && $active.length) {
            positionDescriptionPanel($active);
        }
    };

    const fetchItemDescriptionSuggestions = (term) => {
        const trimmed = String(term || '').trim();
        if (!config?.itemDescriptionSuggestUrl || trimmed.length < suggestionMinChars) {
            return Promise.resolve([]);
        }

        const cacheKey = trimmed.toLowerCase();
        if (suggestionCache.has(cacheKey)) {
            return Promise.resolve(suggestionCache.get(cacheKey));
        }

        return $.ajax({
            method: 'GET',
            url: config.itemDescriptionSuggestUrl,
            data: { q: trimmed, limit: suggestionLimit },
        })
            .then((response) => {
                const list = Array.isArray(response?.data) ? response.data : [];
                suggestionCache.set(cacheKey, list);
                return list;
            })
            .catch(() => []);
    };

    const scheduleDescriptionSuggest = ($textarea) => {
        const term = String($textarea.val() || '').trim();
        const $panel = getDescriptionPanel();

        if (term.length < suggestionMinChars) {
            hideDescriptionSuggestions($panel);
            return;
        }

        positionDescriptionPanel($textarea);

        const existingTimer = $textarea.data('suggestionTimer');
        if (existingTimer) {
            clearTimeout(existingTimer);
        }

        const timerId = setTimeout(() => {
            fetchItemDescriptionSuggestions(term).then((suggestions) => {
                renderDescriptionSuggestions($panel, suggestions);
            });
        }, suggestionDelayMs);

        $textarea.data('suggestionTimer', timerId);
    };

    const registerRowEvents = ($row) => {
        $row.find('[data-quantity], [data-unit-cost]').on('input', () => {
            updateRowTotal($row);
            updateGrandTotal();
        });

        $row.find('[data-unit-select]').on('change', function () {
            const $select = $(this);
            const $customInput = $row.find('[data-unit-custom]');

            if ($select.val() === '__other__') {
                $customInput.removeClass('hidden').val('').trigger('focus');
                $select.removeAttr('name');
                $customInput.attr('name', $select.data('fieldName'));
                return;
            }

            $customInput.addClass('hidden').val('').removeAttr('name');
            $select.attr('name', $select.data('fieldName'));
        });
    };

    // Validate fund allocation when selection changes; fetch live remaining amount
    $('#fundAllocation').on('change', function() {
        const id = $(this).val();
        const grandTotal = updateGrandTotal();

        if (!id) {
            validateFundAllocation(grandTotal);
            return;
        }

        $.ajax({
            method: 'GET',
            url: `/employee/fund-allocations/${id}`,
            success: (resp) => {
                // update selected option data and UI
                const $opt = $(`#fundAllocation option[value="${id}"]`);
                $opt.data('remaining', resp.remaining_amount);
                $opt.data('total', resp.total_amount);
                validateFundAllocation(grandTotal);
            },
            error: () => {
                // fallback to existing data attributes
                validateFundAllocation(grandTotal);
            }
        });
    });

    const closeWaitModal = () => {
        if (!$waitModal.length) {
            return;
        }
        toggleModal($waitModal, false);
        activeWaitContext = { priId: null, status: null };
        $waitErrors.addClass('hidden').empty();
        if ($waitForm.length) {
            $waitForm[0].reset();
        }
    };

    const openWaitModal = (priId, status, item = {}) => {
        if (!$waitModal.length) {
            return;
        }

        activeWaitContext = { priId, status };
        const waitUntil = item.employee_wait_until || '';
        const waitNote = item.employee_wait_note || '';

        $waitDate.val(waitUntil);
        $waitNote.val(waitNote);
        $waitErrors.addClass('hidden').empty();

        if ($waitContext.length) {
            if (status === 'unavailable') {
                $waitContext.text('Let us know how long you can wait while we work to source the original item.');
            } else {
                $waitContext.text('Confirm how long you are willing to wait for the original item to arrive.');
            }
        }

        toggleModal($waitModal, true);
    };

    const addItemRow = (itemData = {}) => {
        const idx = rowIndex++;
        const row = `
            <tr class="align-top" data-index="${idx}">
                <td class="border border-gray-500 px-2 py-2">
                    <input type="number" name="items[${idx}][quantity]" min="1" value="1" data-quantity class="w-full border border-gray-400 px-2 py-1 text-sm text-center font-semibold focus:border-[#1a3a2d] focus:ring-0" />
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <div class="space-y-2">
                        <select name="items[${idx}][unit]" data-field-name="items[${idx}][unit]" data-unit-select class="w-full border border-gray-400 px-2 py-1 text-sm focus:border-[#1a3a2d] focus:ring-0">
                            <option value="">Select unit</option>
                            <option value="pcs">pcs</option>
                            <option value="box">box</option>
                            <option value="pack">pack</option>
                            <option value="set">set</option>
                            <option value="kg">kg</option>
                            <option value="l">l</option>
                            <option value="__other__">Other (type manually)</option>
                        </select>
                        <input type="text" data-unit-custom class="hidden w-full border border-gray-400 px-2 py-1 text-sm focus:border-[#1a3a2d] focus:ring-0" placeholder="Type custom unit" />
                    </div>
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <div class="relative" data-description-wrapper>
                        <textarea name="items[${idx}][item_description]" class="w-full border border-gray-400 px-2 py-1 text-sm min-h-[72px] resize-y focus:border-[#1a3a2d] focus:ring-0" placeholder="Describe the item in detail..." data-description-input></textarea>
                        <div class="absolute left-0 right-0 z-20 mt-1 hidden max-h-48 overflow-auto rounded-md border border-gray-300 bg-white shadow-lg" data-description-suggestions></div>
                    </div>
                    <input type="hidden" name="items[${idx}][item_type]" value="consumable" />
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <input type="text" name="items[${idx}][stock_number]" class="w-full border border-gray-400 px-2 py-1 text-sm focus:border-[#1a3a2d] focus:ring-0" placeholder="Optional" />
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <div class="relative">
                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 text-xs">₱</span>
                        <input type="number" name="items[${idx}][estimated_unit_cost]" min="0" step="0.01" value="0" data-unit-cost class="w-full border border-gray-400 pl-6 pr-2 py-1 text-sm text-right font-semibold focus:border-[#1a3a2d] focus:ring-0" />
                    </div>
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-gray-800 min-w-[90px] text-right" data-total-display>₱0.00</span>
                        <button type="button" class="remove-item text-red-700 hover:text-red-800 border border-red-300 px-2 py-1 rounded text-xs font-bold transition">Remove</button>
                    </div>
                </td>
            </tr>
        `;

        const $row = $(row);
        $itemRows.append($row);

        const unitOptions = ['pcs', 'box', 'pack', 'set', 'kg', 'l'];
        const unitValue = String(itemData.unit || '').trim();
        const isKnownUnit = unitValue && unitOptions.includes(unitValue.toLowerCase());

        $row.find('[data-quantity]').val(Number(itemData.quantity || 1));
        $row.find('[name$="[item_description]"]').val(itemData.item_description || '');
        $row.find('[name$="[stock_number]"]').val(itemData.stock_number || '');
        $row.find('[data-unit-cost]').val(Number(itemData.estimated_unit_cost || 0));

        const $unitSelect = $row.find('[data-unit-select]');
        const $unitCustom = $row.find('[data-unit-custom]');
        if (unitValue && !isKnownUnit) {
            $unitSelect.val('__other__').trigger('change');
            $unitCustom.val(unitValue);
        } else {
            $unitSelect.val(unitValue || '');
        }

        registerRowEvents($row);
        updateRowTotal($row);
        updateGrandTotal();
    };

    const ensureAtLeastOneRow = () => {
        if ($itemRows.children().length === 0) {
            addItemRow();
        }
    };

    const resetCreateRequestForm = () => {
        if ($form.length) {
            $form[0].reset();
        }

        $itemRows.empty();
        rowIndex = 0;
        $('#fundAllocation').val('');
        $('#fundAllocationWarning').addClass('hidden');
        $errors.addClass('hidden').find('#formErrorsContent').empty();
        ensureAtLeastOneRow();
        updateGrandTotal();
    };

    const setCreateMode = () => {
        activeEditContext = { prNo: null, updateUrl: null };
        if ($modalTitle.length) {
            $modalTitle.text('New Purchase Request');
        }
        if ($modalSubtitle.length) {
            $modalSubtitle.text('Fill out the details of your procurement requirement');
        }
        if ($submitLabel.length) {
            $submitLabel.text('Submit Request');
        }
    };

    const setEditMode = (prNo, updateUrl) => {
        activeEditContext = { prNo, updateUrl };
        if ($modalTitle.length) {
            $modalTitle.text(`Edit Purchase Request ${prNo}`);
        }
        if ($modalSubtitle.length) {
            $modalSubtitle.text('Update your request details before recommending officer review.');
        }
        if ($submitLabel.length) {
            $submitLabel.text('Update Request');
        }
    };

    const populateFormForEdit = (data = {}) => {
        resetCreateRequestForm();

        $('#saiNo').val(data.sai_no || '');
        $('#alobsNo').val(data.alobs_no || '');
        $('#recommendingOfficer').val(data.recommending_officer_id || '');
        $('#purpose').val(data.purpose || '');
        $('#requestedDeliveryDate').val(data.requested_delivery_date || '');
        $('#requestedDeliveryTerm').val(data.requested_delivery_term || '');
        $('#requestedPaymentTerm').val(data.requested_payment_term || '');

        const fundAllocationId = data.fund_allocation_id || '';
        $('#fundAllocation').val(fundAllocationId).trigger('change');

        $itemRows.empty();
        rowIndex = 0;
        const items = Array.isArray(data.items) ? data.items : [];

        if (!items.length) {
            ensureAtLeastOneRow();
            return;
        }

        items.forEach((item) => addItemRow(item));
        updateGrandTotal();
    };

    $openBtn.on('click', () => {
        hideToast($toast);
        toggleModal($modal, true);

        requestAnimationFrame(() => {
            setCreateMode();
            resetCreateRequestForm();
        });
    });

    $closeElements.on('click', () => {
        hideToast($toast);
        toggleModal($modal, false);
        $('#fundAllocationWarning').addClass('hidden');
    });

    $modal.on('click', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            hideToast($toast);
            toggleModal($modal, false);
            $('#fundAllocationWarning').addClass('hidden');
        }
    });

    $itemRows.on('click', '.remove-item', function () {
        $(this).closest('tr').remove();
        ensureAtLeastOneRow();
        updateGrandTotal();
    });

    $itemRows.on('input', '[data-description-input]', function () {
        scheduleDescriptionSuggest($(this));
    });

    $itemRows.on('focus', '[data-description-input]', function () {
        scheduleDescriptionSuggest($(this));
    });

    $itemRows.on('blur', '[data-description-input]', function () {
        const $textarea = $(this);
        setTimeout(() => {
            hideDescriptionSuggestions(getDescriptionPanel($textarea));
        }, 150);
    });

    $(document).on('pointerdown.employeeDescriptionSuggestItem', '[data-description-suggestion]', function (event) {
        event.preventDefault();
        const $button = $(this);
        const $panel = getDescriptionPanel();
        const $textarea = $panel.data('activeDescriptionInput');
        if ($textarea && $textarea.length) {
            $textarea.val($button.text()).trigger('input');
            $textarea[0].focus({ preventScroll: true });
        }
        hideDescriptionSuggestions($panel);
    });

    $addItemRow.on('click', addItemRow);

    const $formScrollContainer = $modal.find('.hide-scrollbar').first();

    $(window).on('resize.employeeDescriptionSuggest scroll.employeeDescriptionSuggest', () => {
        syncDescriptionPanelPosition();
    });

    if ($formScrollContainer.length) {
        $formScrollContainer.on('scroll.employeeDescriptionSuggest', () => {
            syncDescriptionPanelPosition();
        });
    }

    $(document).on('click.employeeDescriptionSuggest', (event) => {
        if ($(event.target).closest('[data-description-wrapper]').length) {
            return;
        }

        hideDescriptionSuggestions(getDescriptionPanel());
    });

    resetCreateRequestForm();

    $form.on('submit', function (event) {
        event.preventDefault();

        if (!hasEmployeeConfig || !config.storeUrl) {
            showToast($toast, 'Purchase request configuration is unavailable. Please refresh the page and try again.');
            return;
        }

        const isEdit = Boolean(activeEditContext.prNo);
        if (isEdit && !activeEditContext.updateUrl) {
            showToast($toast, 'Update endpoint is unavailable. Please refresh the page and try again.');
            return;
        }

        $errors.addClass('hidden').find('#formErrorsContent').empty();
        const submitBtn = $form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html(`<i class="fas fa-spinner fa-spin mr-2"></i>${isEdit ? 'Updating...' : 'Submitting...'}`);

        const payload = isEdit ? `${$form.serialize()}&_method=PATCH` : $form.serialize();

        $.ajax({
            method: 'POST',
            url: isEdit ? activeEditContext.updateUrl : config.storeUrl,
            data: payload,
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: (response) => {
                toggleModal($modal, false);
                showToast($toast, response.message ?? (isEdit ? 'Purchase request updated.' : 'Purchase request submitted.'));
                setTimeout(() => softNavigate(window.location.href, { replace: true }), 1200);
            },
                          error: (xhr) => {
                  if (xhr.status === 422 && xhr.responseJSON?.errors) {
                      const messages = Object.values(xhr.responseJSON.errors).flat();
                      showToast($toast, messages.map((msg) => `<div class="mb-1">• ${msg}</div>`).join(''), true);
                  } else {
                      showToast($toast, 'An unexpected error occurred. Please try again.');
                  }
              },
            complete: () => {
                submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').html(originalText);
            },
        });
    });

    const $detailsModal = $('#employeePrDetailsModal');
    const $detailsClose = $detailsModal.find('[data-close-modal]');
    const $detailsItems = $('#employeePrDetailsItems');

    const renderDetailsLoadingState = () => {
        $('#employeePrDetailsModalTitle').text('Loading purchase request...');
        $('#employeePrAnnexContent').html(`
            <div class="flex min-h-[420px] items-center justify-center bg-white px-6 py-12">
                <div class="text-center text-gray-500">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f4f8f5] text-[#1a3a2d]">
                        <i class="fas fa-spinner fa-spin text-xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-gray-700">Loading purchase request details</p>
                    <p class="mt-1 text-xs text-gray-500">Please wait while we prepare the request document.</p>
                </div>
            </div>
        `);
    };

    const renderDetailsErrorState = () => {
        $('#employeePrDetailsModalTitle').text('Unable to load details');
        $('#employeePrAnnexContent').html(`
            <div class="flex min-h-[420px] items-center justify-center bg-white px-6 py-12">
                <div class="max-w-md text-center text-gray-500">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <i class="fas fa-triangle-exclamation text-xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-gray-700">Failed to load purchase request details</p>
                    <p class="mt-1 text-xs text-gray-500">Please close the modal and try again.</p>
                </div>
            </div>
        `);
    };

    const populateDetails = (data) => {
        const requestStatusId = data.status_id ?? null;
        $('#employeePrDetailsModalTitle').text(data.pr_no);
        
        // Build Annex G-6 HTML
        const itemsHtml = (data.items || []).map((item) => {
            item.request_status_id = requestStatusId;
            item.request_cancelled_by_role = data.latest_status_changed_by_role || '';
            const statusHtml = renderItemStatus(item, requestStatusId);
            const actionHtml = renderItemActions(item);
            
            return `
                <tr data-pri-id="${item.pri_id}">
                    <td class="border border-gray-500 px-3 py-2 text-center">${item.quantity}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${item.unit}</td>
                    <td class="border border-gray-500 px-3 py-2">${item.item_description}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${item.stock_number ?? '—'}</td>
                    <td class="border border-gray-500 px-3 py-2 text-right">${formatCurrency(item.estimated_unit_cost)}</td>
                    <td class="border border-gray-500 px-3 py-2 text-right">${formatCurrency(item.estimated_total_cost)}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center" data-item-status>${statusHtml}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center" data-item-action>${actionHtml}</td>
                </tr>
            `;
        }).join('');
        
        const statuses = getRequestStatuses();
        const declinedId = statuses.declined != null ? String(statuses.declined) : '';
        const statusIdString = requestStatusId != null ? String(requestStatusId) : '';
        const statusRemarkHtml = (statusIdString && declinedId && statusIdString === declinedId && data.latest_status_remarks) 
            ? `<div class="mt-3 p-3 bg-red-50 border border-red-200 rounded text-sm"><strong class="text-red-700">Decline Remarks:</strong> ${data.latest_status_remarks}</div>`
            : '';
        
        const createdDate = data.created_at ? new Date(data.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' }) : '—';
        const requestedDeliveryDate = data.requested_delivery_date
            ? new Date(`${data.requested_delivery_date}T00:00:00`).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
            : '—';
        const requestedDeliveryTerm = data.requested_delivery_term || '—';
        const requestedPaymentTerm = data.requested_payment_term || '—';
        
        const annexHtml = `
            <div class="bg-white">
                <div class="relative border-b border-gray-400 px-6 pt-6 pb-4">
                    <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">Annex G-6</span>
                    <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                        <img src="${bpiLogoUrl}" alt="BPI Logo" class="h-16 w-auto object-contain">
                        <div class="text-center">
                            <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                            <h2 class="mt-2 text-2xl font-black uppercase tracking-[0.35em] text-gray-900">Purchase Request</h2>
                            <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                            <p class="text-xs italic text-gray-500">(Agency)</p>
                        </div>
                        <img src="${pqsLogoUrl}" alt="PQS Logo" class="h-16 w-auto object-contain">
                    </div>
                </div>

                <div class="border-b border-gray-400 px-5 py-2.5">
                    <div class="grid grid-cols-1 gap-x-5 gap-y-1.5 text-xs text-gray-700 md:grid-cols-[1fr_1.1fr_0.7fr]">
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">Division :</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate">${data.division ?? '—'}</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">PR No.:</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">${data.pr_no ?? '—'}</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">${createdDate}</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">Section :</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900 truncate">${data.section ?? '—'}</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">SAI No.:</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">${data.sai_no ?? '—'}</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                        </div>
                        <div></div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">ALOBS No.:</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">${data.alobs_no ?? '—'}</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="font-bold uppercase tracking-wider whitespace-nowrap">Date:</span>
                            <span class="flex-1 border-b border-gray-500 pb-0.5 text-right text-sm text-gray-900">—</span>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-400 px-4 py-6">
                        <div class="overflow-x-auto hide-scrollbar">
                        <table class="w-full border border-gray-500 text-sm border-collapse">
                            <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-700">
                                <tr>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Quantity</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Unit of Issue</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Item Description</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Stock No.</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Estimated Unit Cost</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Estimated Cost</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Status</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody id="employeePrDetailsItems">
                                ${itemsHtml}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="border border-gray-500 px-3 py-3 text-right font-semibold uppercase tracking-wide" colspan="5">Total Estimated Cost</td>
                                    <td class="border border-gray-500 px-3 py-3 text-right text-lg font-bold" colspan="3">${formatCurrency(data.total_estimated_cost)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-3 text-right text-sm text-gray-700">
                        <span class="mr-4">Selected Cluster: <span class="font-semibold">${data.fund_cluster ?? '—'}</span></span>
                        <span>Remaining: <span class="font-bold">${formatCurrency(data.funds_available ?? 0)}</span></span>
                    </div>
                    ${statusRemarkHtml}
                </div>

                <div class="border-b border-gray-400 px-6 py-6 space-y-6">
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-3">Purpose</span>
                        <p class="text-sm text-gray-800 leading-relaxed">${data.purpose ?? '—'}</p>
                    </div>
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-3">Requested Delivery</span>
                        <div class="grid gap-4 md:grid-cols-3 text-sm text-gray-800">
                            <div>
                                <span class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Delivery Date</span>
                                <span class="mt-1 block">${requestedDeliveryDate}</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Delivery Term</span>
                                <span class="mt-1 block">${requestedDeliveryTerm}</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-semibold uppercase tracking-wide text-gray-500">Payment Term</span>
                                <span class="mt-1 block">${requestedPaymentTerm}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Requested by:</span>
                            <span class="mt-2 block border-b border-gray-500 pb-2 text-sm font-medium text-gray-900">${data.requester_name ?? '—'}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Status:</span>
                            <span class="mt-2 block border-b border-gray-500 pb-2 text-sm font-medium text-gray-900">${data.status ?? 'Unknown'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#employeePrAnnexContent').html(annexHtml);
        
        // Re-bind item action handlers after HTML is inserted
        $('#employeePrDetailsItems tr').each(function() {
            const $row = $(this);
            const priId = $row.data('pri-id');
            const item = (data.items || []).find(i => i.pri_id == priId);
            if (item) {
                $row.data('item', item);
                $row.data('requestStatusId', requestStatusId);
            }
        });
    };

    const handleItemDecision = ($button, priId, decision) => {
        if (!hasEmployeeConfig) {
            showToast($toast, 'Request action configuration is unavailable. Please refresh the page and try again.');
            return;
        }

        const urlTemplate = config.decisionUrlTemplate;
        if (!urlTemplate) {
            return;
        }

        const url = urlTemplate.replace('__PRI__', priId);
        const $row = $button.closest('tr');

        $row.find('.js-item-decision').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url,
            data: { decision },
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: (response) => {
                showToast($toast, response.message ?? 'Preference saved.');
                const item = response.data?.item || null;
                const newRequestStatusId = response.data?.request_status_id ?? $row.data('requestStatusId');
                if (item) {
                    updateRowAfterDecision($row, item, newRequestStatusId);
                }
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Unable to record your preference right now.';
                showToast($toast, message);
            },
            complete: () => {
                $row.find('.js-item-decision').prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    };

    $(document)
        .off('click.employeeViewPr', '.js-view-employee-pr')
        .on('click.employeeViewPr', '.js-view-employee-pr', function () {
        const url = $(this).data('show-url');
        if (!url) {
            return;
        }

        renderDetailsLoadingState();
        toggleModal($detailsModal, true);

        $.ajax({
            method: 'GET',
            url,
            headers: { Accept: 'application/json' },
            success: (response) => {
                populateDetails(response.data);
            },
            error: () => {
                renderDetailsErrorState();
                showToast($toast, 'Failed to load purchase request details.');
            },
        });
    });

    $(document)
        .off('click.employeeEditPr', '.js-edit-employee-pr')
        .on('click.employeeEditPr', '.js-edit-employee-pr', function () {
            const $button = $(this);
            const showUrl = String($button.attr('data-show-url') || '');
            const prNo = String($button.attr('data-pr-no') || '');
            const updateUrl = String($button.attr('data-update-url') || '');

            if (!showUrl || !prNo || !updateUrl) {
                showToast($toast, 'Edit data is incomplete. Please refresh the page and try again.');
                return;
            }

            $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            toggleModal($modal, true);
            setEditMode(prNo, updateUrl);
            resetCreateRequestForm();

            $.ajax({
                method: 'GET',
                url: showUrl,
                headers: { Accept: 'application/json' },
                success: (response) => {
                    const data = response?.data || {};
                    if (!data.can_edit) {
                        toggleModal($modal, false);
                        showToast($toast, 'This purchase request can no longer be edited.');
                        return;
                    }

                    requestAnimationFrame(() => {
                        setEditMode(prNo, updateUrl);
                        populateFormForEdit(data);
                    });
                },
                error: (xhr) => {
                    toggleModal($modal, false);
                    const message = xhr.responseJSON?.message || 'Unable to load purchase request for editing.';
                    showToast($toast, message);
                },
                complete: () => {
                    $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                },
            });
        });

    $(document)
        .off('click.employeeDeletePr', '.js-delete-employee-pr')
        .on('click.employeeDeletePr', '.js-delete-employee-pr', function () {
            if (!hasEmployeeConfig) {
                showToast($toast, 'Purchase request configuration is unavailable. Please refresh the page and try again.');
                return;
            }

            const $button = $(this);
            const prNo = String($button.attr('data-pr-no') || 'this request');
            const directUrl = String($button.attr('data-delete-url') || '');
            const template = String(config.destroyUrlTemplate || '');
            const url = directUrl || (template ? template.replace('__PR__', prNo) : '');

            if (!url) {
                showToast($toast, 'Delete endpoint is unavailable. Please refresh the page and try again.');
                return;
            }

            if (!window.confirm(`Delete ${prNo}? This action cannot be undone.`)) {
                return;
            }

            $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

            $.ajax({
                method: 'DELETE',
                url,
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    Accept: 'application/json',
                },
                success: (response) => {
                    showToast($toast, response.message ?? 'Purchase request deleted successfully.');
                    setTimeout(() => softNavigate(window.location.href, { replace: true }), 900);
                },
                error: (xhr) => {
                    const message = xhr.responseJSON?.message || 'Unable to delete this purchase request right now.';
                    showToast($toast, message);
                },
                complete: () => {
                    $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                },
            });
        });

    $detailsClose.on('click', () => toggleModal($detailsModal, false));
    $detailsModal.on('click', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            toggleModal($detailsModal, false);
        }
    });

    if ($waitModal.length) {
        $waitModal.on('click', (event) => {
            if (event.target.dataset.closeModal !== undefined) {
                closeWaitModal();
            }
        });

        $waitModal.find('[data-close-modal]').on('click', () => closeWaitModal());

        $waitForm.on('submit', function (event) {
            event.preventDefault();
            if (!activeWaitContext.priId) {
                return;
            }

            if (!hasEmployeeConfig) {
                $waitErrors.removeClass('hidden').text('Request action configuration is unavailable. Please refresh the page and try again.');
                return;
            }

            const urlTemplate = config.decisionUrlTemplate;
            if (!urlTemplate) {
                return;
            }

            const url = urlTemplate.replace('__PRI__', activeWaitContext.priId);
            const formData = {
                decision: 'wait',
                wait_until: $waitDate.val(),
                wait_note: $waitNote.val(),
            };

            $waitErrors.addClass('hidden').empty();
            const $submitBtn = $waitForm.find('button[type="submit"]');
            $submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

            $.ajax({
                method: 'POST',
                url,
                data: formData,
                headers: { 'X-CSRF-TOKEN': csrfToken() },
                success: (response) => {
                    const priId = activeWaitContext.priId;
                    closeWaitModal();
                    showToast($toast, response.message ?? 'Timeframe saved.');
                    const $row = priId ? $detailsItems.find(`tr[data-pri-id="${priId}"]`) : $();
                    const item = response.data?.item || null;
                    const newRequestStatusId = response.data?.request_status_id ?? $row.data('requestStatusId');
                    if ($row.length && item) {
                        updateRowAfterDecision($row, item, newRequestStatusId);
                    }
                },
                error: (xhr) => {
                    if (xhr.status === 422) {
                        if (xhr.responseJSON?.errors) {
                            const messages = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .map((msg) => `<div>${escapeHtml(msg)}</div>`)
                                .join('');
                            $waitErrors.html(messages).removeClass('hidden');
                        } else if (xhr.responseJSON?.message) {
                            $waitErrors.text(xhr.responseJSON.message).removeClass('hidden');
                        } else {
                            $waitErrors.text('Please double-check the timeframe and try again.').removeClass('hidden');
                        }
                    } else {
                        $waitErrors.text('Unable to save your timeframe right now. Please try again later.').removeClass('hidden');
                    }
                },
                complete: () => {
                    $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                },
            });
        });
    }

    // Use event delegation from the modal (which doesn't get replaced) instead of $detailsItems (which does get replaced)
    $detailsModal.on('click', '.js-item-decision', function () {
        const $btn = $(this);
        const priId = $btn.data('pri-id');
        const decision = $btn.data('decision');
        if (!priId || !decision) {
            return;
        }
        handleItemDecision($btn, priId, decision);
    });

    $detailsModal.on('click', '.js-item-timeframe', function () {
        const $btn = $(this);
        const priId = $btn.data('pri-id');
        const status = $btn.data('status');
        if (!priId || !status) {
            return;
        }
        const $row = $btn.closest('tr');
        const item = $row.data('item') || {};
        openWaitModal(priId, status, item);
    });
};

const init = () => {
    initEmployeePurchaseRequests();
};

// Standard page load
$(init);

// Turbo Drive navigations — module scripts only execute once,
// so jQuery-ready won't re-fire. Reset the guard so element
// handlers are re-bound to the fresh DOM that Turbo swapped in.
document.addEventListener('turbo:load', () => {
    employeeBound = false;
    init();
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        employeeBound = false;
        init();
    }, { once: true });
} else {
    employeeBound = false;
    init();
}
