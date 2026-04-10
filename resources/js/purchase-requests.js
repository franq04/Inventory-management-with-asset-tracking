import $ from 'jquery';

const csrfToken = () => $('meta[name="csrf-token"]').attr('content');

const formatCurrency = (value) => {
    const number = Number(value ?? 0);
    return `₱${number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    };
    return map[char] ?? char;
});

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

const parseDate = (value) => {
    if (!value) {
        return null;
    }
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
};

const formatDateDisplay = (value) => {
    const date = parseDate(value);
    if (!date) {
        return '';
    }
    return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
};

const isDatePast = (value) => {
    const date = parseDate(value);
    if (!date) {
        return false;
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
};

const itemStatusMeta = {
    pending: {
        label: 'For recommendation',
        badge: 'bg-gray-100 text-gray-600',
    },
    ordered: {
        label: 'Ordered as requested',
        badge: 'bg-emerald-100 text-emerald-700',
    },
    unavailable: {
        label: 'Marked unavailable',
        badge: 'bg-rose-100 text-rose-700',
    },
    alternative: {
        label: 'Proposed alternative',
        badge: 'bg-sky-100 text-sky-700',
    },
};

const decisionMessages = {
    accept: 'You accepted the substitute item.',
    wait: 'You prefer to wait for the original item.',
};

const getRequestStatuses = () => (window.employeePrConfig && window.employeePrConfig.statuses) ? window.employeePrConfig.statuses : {};

const renderItemStatus = (item, requestStatusId) => {
    const statuses = getRequestStatuses();
    const statusId = requestStatusId != null ? String(requestStatusId) : '';
    const declinedId = statuses.declined != null ? String(statuses.declined) : '';
    const approvedId = statuses.approved != null ? String(statuses.approved) : '';

    if (!item || !item.fulfillment_status) {
        let meta = itemStatusMeta.pending;
        if (declinedId && statusId === declinedId) {
            meta = {
                label: 'Request cancelled',
                badge: 'bg-rose-100 text-rose-700',
            };
        } else if (approvedId && statusId === approvedId) {
            meta = {
                label: 'Request approved',
                badge: 'bg-emerald-100 text-emerald-700',
            };
        }

        return `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ${meta.badge}">
            <i class="fas fa-circle text-[8px]"></i>${meta.label}
        </span>`;
    }

    const fulfillment = item.fulfillment_status;
    const meta = itemStatusMeta[fulfillment] ?? itemStatusMeta.pending;
    let html = `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ${meta.badge}">
        <i class="fas fa-circle text-[8px]"></i>${meta.label}
    </span>`;

    if (fulfillment === 'alternative' && item.alternate_description) {
        html += `<div class="mt-1 text-xs text-gray-600">Alt: ${escapeHtml(item.alternate_description)}</div>`;
    }

    if (item.employee_decision) {
        const decisionText = decisionMessages[item.employee_decision] ?? item.employee_decision;
        html += `<div class="mt-1 text-xs italic text-gray-600">${decisionText}</div>`;
    }

    if (item.employee_wait_until) {
        const waitLabel = formatDateDisplay(item.employee_wait_until);
        const waitExpired = isDatePast(item.employee_wait_until);
        const waitClass = waitExpired ? 'text-rose-700' : 'text-amber-700';
        html += `<div class="mt-1 text-xs font-semibold ${waitClass}">
            <i class="fas fa-hourglass-half mr-1"></i>${waitExpired ? 'Wait period lapsed on ' : 'Waiting until '}${waitLabel}
        </div>`;
    } else if (fulfillment === 'unavailable') {
        html += `<div class="mt-1 text-xs text-amber-700"><i class="fas fa-hourglass-half mr-1"></i>Waiting timeframe pending</div>`;
    }

    if (item.employee_wait_note) {
        html += `<div class="mt-1 text-xs text-gray-600 italic">“${escapeHtml(item.employee_wait_note)}”</div>`;
    }

    return html;
};

const renderItemActions = (item) => {
    if (!item) {
        return '<span class="text-gray-400">—</span>';
    }

    const statuses = getRequestStatuses();
    const requestStatusId = item.request_status_id != null ? String(item.request_status_id) : '';
    const declinedId = statuses.declined != null ? String(statuses.declined) : '';
    const forApprovalId = statuses.forApproval != null ? String(statuses.forApproval) : '';

    const cancelRoleLabel = (() => {
        const role = String(item.request_cancelled_by_role || '').toLowerCase();
        if (role === 'bac') {
            return 'BAC';
        }
        if (role === 'division_head') {
            return 'Division Head';
        }
        if (role === 'custodian') {
            return 'Custodian';
        }
        if (role === 'employee') {
            return 'Requester';
        }
        return 'staff';
    })();

    if (requestStatusId && declinedId && requestStatusId === declinedId) {
        return `<span class="text-xs font-semibold text-rose-700">Request cancelled by ${cancelRoleLabel}.</span>`;
    }

    const fulfillment = item.fulfillment_status;

    if (!fulfillment || (!['alternative', 'unavailable'].includes(fulfillment))) {
        return '<span class="text-gray-400">—</span>';
    }

    if (forApprovalId && requestStatusId && requestStatusId !== forApprovalId) {
        return '<span class="text-gray-400">—</span>';
    }

    if (fulfillment === 'alternative' && !item.employee_decision) {
        return `
            <div class="flex justify-end gap-2">
                <button type="button" class="js-item-decision rounded-lg border border-emerald-500 px-3 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-500 hover:text-white" data-decision="accept" data-pri-id="${item.pri_id}">
                    Accept
                </button>
                <button type="button" class="js-item-timeframe rounded-lg border border-amber-500 px-3 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-500 hover:text-white" data-decision="wait" data-status="alternative" data-pri-id="${item.pri_id}">
                    Wait &amp; Set Timeframe
                </button>
            </div>
        `;
    }

    if (fulfillment === 'alternative' && item.employee_decision === 'accept') {
        const message = decisionMessages.accept ?? 'Alternative item accepted.';
        return `<span class="text-xs font-semibold text-gray-600">${message}</span>`;
    }

    if (fulfillment === 'alternative' && item.employee_decision === 'wait') {
        const buttonLabel = item.employee_wait_until ? 'Update Timeframe' : 'Set Timeframe';
        return `
            <div class="space-y-2 text-right">
                <span class="block text-xs font-semibold text-gray-600">${decisionMessages.wait ?? 'You prefer to wait for the original item.'}</span>
                <div class="flex justify-end">
                    <button type="button" class="js-item-timeframe rounded-lg border border-amber-500 px-3 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-500 hover:text-white" data-decision="wait" data-status="alternative" data-pri-id="${item.pri_id}">
                        ${buttonLabel}
                    </button>
                </div>
            </div>
        `;
    }

    if (fulfillment === 'unavailable') {
        const buttonLabel = item.employee_wait_until ? 'Update Timeframe' : 'Provide Timeframe';
        return `
            <div class="flex justify-end">
                <button type="button" class="js-item-timeframe rounded-lg border border-amber-500 px-3 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-500 hover:text-white" data-decision="wait" data-status="unavailable" data-pri-id="${item.pri_id}">
                    ${buttonLabel}
                </button>
            </div>
        `;
    }

    return '<span class="text-gray-400">—</span>';
};

const updateRowAfterDecision = ($row, item, requestStatusId) => {
    const priId = $row.data('priId');
    const effectiveStatusId = requestStatusId ?? $row.data('requestStatusId');
    const normalizedItem = {
        ...(item || {}),
        pri_id: item?.pri_id ?? priId,
        request_status_id: effectiveStatusId,
    };

    $row.data('item', normalizedItem);
    $row.data('requestStatusId', effectiveStatusId);
    $row.find('[data-item-status]').html(renderItemStatus(normalizedItem, effectiveStatusId));
    $row.find('[data-item-action]').html(renderItemActions(normalizedItem));
};

const clearToastTimers = (toast) => {
    const timerId = toast.data('toastTimerId');
    if (timerId) {
        clearTimeout(timerId);
        toast.removeData('toastTimerId');
    }

    const hideTimerId = toast.data('toastHideTimerId');
    if (hideTimerId) {
        clearTimeout(hideTimerId);
        toast.removeData('toastHideTimerId');
    }
};

const hideToast = (selector) => {
    const toast = $(selector);
    if (!toast.length) {
        return;
    }

    clearToastTimers(toast);

    toast.addClass('opacity-0');
    const nextHideTimerId = setTimeout(() => {
        toast.addClass('hidden');
        toast.removeData('toastHideTimerId');
    }, 300);

    toast.data('toastHideTimerId', nextHideTimerId);
};

const showToast = (selector, message, isHtml = false) => {
    const toast = $(selector);
    if (!toast.length) {
        const fallbackId = 'pqs-global-toast';
        let fallback = document.getElementById(fallbackId);
        if (!fallback) {
            fallback = document.createElement('div');
            fallback.id = fallbackId;
            fallback.className = 'fixed bottom-6 right-6 z-[90] hidden max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg';
            document.body.appendChild(fallback);
        }

        fallback.textContent = isHtml ? message.replace(/<[^>]*>?/gm, '') : message;
        fallback.classList.remove('hidden');
        window.clearTimeout(showToast._fallbackTimer);
        showToast._fallbackTimer = window.setTimeout(() => {
            fallback.classList.add('hidden');
        }, 3000);
        return;
    }

    clearToastTimers(toast);

    if (isHtml) {
        toast.html(message);
    } else {
        toast.text(message);
    }

    toast.removeClass('hidden opacity-0');
    const timerId = setTimeout(() => hideToast(selector), 5000);
    toast.data('toastTimerId', timerId);
};

// This function only handles showing and hiding the modal.
// The visual layout (preventing header/footer overlap) is controlled by
// the HTML structure and CSS classes in the Blade file, not by this JavaScript.
// No changes are needed here to fix the visual overlap issue.
const toggleModal = ($modal, open = false) => {
    const $panel = $modal.find('.modal-panel').first();
    if (open) {
        $modal.removeClass('hidden');
        // Double RAF: lets the browser compute the initial opacity-0/scale-95 state
        // before removing those classes, which triggers the CSS transition.
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
        setTimeout(() => {
            const focusTarget = $modal.find('[data-focus]').first().get(0);

            if (focusTarget) {
                try {
                    focusTarget.focus({ preventScroll: true });
                } catch (error) {
                    focusTarget.focus();
                }
            }

            $modal.scrollTop(0);
            if ($panel.length) {
                $panel.scrollTop(0);
                $panel.find('.overflow-y-auto, .overflow-y-scroll').scrollTop(0);
            }
        }, 300);
    } else {
        $modal.addClass('opacity-0');
        if ($panel.length) {
            $panel.addClass('opacity-0 scale-95 translate-y-2');
        }
        setTimeout(() => $modal.addClass('hidden'), 300);
    }
};

const initEmployeePurchaseRequests = () => {
    const config = window.employeePrConfig;
    const hasEmployeeConfig = Boolean(config);

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
        let activeStatus = '';

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
                if (buttonStatus === activeStatus) {
                    activeStatus = '';
                } else {
                    activeStatus = buttonStatus;
                }
                setStatusButtonState(activeStatus);
                applyEmployeeFilters();
            });
        }

        if ($resetFilters.length) {
            $resetFilters.on('click', (event) => {
                event.preventDefault();
                if ($searchInput.length) {
                    $searchInput.val('');
                }
                activeStatus = '';
                setStatusButtonState(activeStatus);
                applyEmployeeFilters();
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
    let activeWaitContext = { priId: null, status: null };
    let rowIndex = 0;

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

    const registerRowEvents = ($row) => {
        $row.find('[data-quantity], [data-unit-cost]').on('input', () => {
            updateRowTotal($row);
            updateGrandTotal();
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

    const addItemRow = () => {
        const idx = rowIndex++;
        const row = `
            <tr class="align-top" data-index="${idx}">
                <td class="border border-gray-500 px-2 py-2">
                    <input type="number" name="items[${idx}][quantity]" min="1" value="1" data-quantity class="w-full border border-gray-400 px-2 py-1 text-sm text-center font-semibold focus:border-[#1a3a2d] focus:ring-0" />
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <input type="text" name="items[${idx}][unit]" class="w-full border border-gray-400 px-2 py-1 text-sm focus:border-[#1a3a2d] focus:ring-0" placeholder="e.g., pcs, box" />
                </td>
                <td class="border border-gray-500 px-2 py-2">
                    <textarea name="items[${idx}][item_description]" class="w-full border border-gray-400 px-2 py-1 text-sm min-h-[72px] resize-y focus:border-[#1a3a2d] focus:ring-0" placeholder="Describe the item in detail..."></textarea>
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

    $openBtn.on('click', () => {
        hideToast($toast);
        toggleModal($modal, true);

        requestAnimationFrame(() => {
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

    $addItemRow.on('click', addItemRow);

    resetCreateRequestForm();

    $form.on('submit', function (event) {
        event.preventDefault();

        if (!hasEmployeeConfig || !config.storeUrl) {
            showToast($toast, 'Purchase request configuration is unavailable. Please refresh the page and try again.');
            return;
        }

        $errors.addClass('hidden').find('#formErrorsContent').empty();
        const submitBtn = $form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html('<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...');

        $.ajax({
            method: 'POST',
            url: config.storeUrl,
            data: $form.serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: (response) => {
                toggleModal($modal, false);
                showToast($toast, response.message ?? 'Purchase request submitted.');
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
        
        const annexHtml = `
            <div class="bg-white">
                <div class="relative border-b border-gray-400 px-6 pt-6 pb-4">
                    <span class="absolute top-4 right-6 text-xs font-semibold uppercase tracking-wide text-gray-500">Annex G-6</span>
                    <div class="flex flex-col items-center gap-4 md:flex-row md:items-center md:justify-between">
                        <img src="/images/bpi-logo.png" alt="BPI Logo" class="h-16 w-auto object-contain">
                        <div class="text-center">
                            <p class="text-[11px] uppercase tracking-[0.35em] text-gray-600">Department of Agriculture</p>
                            <h2 class="mt-2 text-2xl font-black uppercase tracking-[0.35em] text-gray-900">Purchase Request</h2>
                            <p class="mt-2 text-sm font-semibold uppercase text-gray-700">Bureau of Plant Industry</p>
                            <p class="text-xs italic text-gray-500">(Agency)</p>
                        </div>
                        <img src="/images/pqslogo.png" alt="PQS Logo" class="h-16 w-auto object-contain">
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
                    <div class="overflow-x-auto">
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

const initPurchaseRequestWorkflow = () => {
    const config = window.purchaseRequestWorkflowConfig;
    if (!config) {
        return;
    }

    const $modal = $('#custodianPrModal');
    const $closeElements = $modal.find('[data-close-modal]');
    const $statusForm = $('#custodianStatusForm');
    const $statusSelect = $('#custodianStatusSelect');
    const $statusErrors = $('#custodianStatusErrors');
    const $statusRemarks = $('#custodianStatusRemarks');
    const toastSelector = '#custodianPrToast';
    const $recommendedBlock = $('#custodianPrRecommendedBlock');
    const $recommendedName = $('#custodianPrRecommendedPrintedName');
    const $recommendedDate = $('#custodianPrRecommendedDate');
    const $recommendedRemarks = $('#custodianPrRecommendationRemarks');
    const $itemsTable = $('#custodianPrItemsTable');
    const $grandTotalLabel = $('#custodianPrGrandTotal');
    let currentUpdateUrl = null;

    const approvedStatusId = String(config.approvedStatusId ?? '');
    const declinedStatusId = String(config.declinedStatusId ?? '');
    const remarksRequiredStatuses = (config.remarksRequiredStatuses || []).map((statusId) => String(statusId));
    const remarksDefaultPlaceholder = $statusRemarks.attr('placeholder') || '';
    const systemManagedStatuses = (config.systemManagedStatuses || []).map((statusId) => String(statusId));
    const $systemNotice = $('#custodianSystemStatusNotice');
    const forApprovalStatusId = config.forApprovalStatusId != null ? String(config.forApprovalStatusId) : '';
    const allowItemEditing = Boolean(config.allowItemEditing && config.itemUpdateUrlTemplate);
    const itemUpdateTemplate = allowItemEditing ? config.itemUpdateUrlTemplate : null;
    const transitionMap = Object.entries(config.transitions || {}).reduce((acc, [source, targets]) => {
        acc[String(source)] = (targets || []).map((target) => String(target));
        return acc;
    }, {});

    const toNumber = (value, fallback = 0) => {
        const num = Number(value);
        return Number.isFinite(num) ? num : fallback;
    };

    const toFixed = (value) => toNumber(value, 0).toFixed(2);

    const renderWorkflowLoadingState = () => {
        $('#custodianPrNumber').text('Loading purchase request...');
        $('#custodianPrNo').text('—');
        $('#custodianPrDate').text('—');
        $('#custodianPrDivisionInput').text('—');
        $('#custodianPrSectionInput').text('—');
        $('#custodianPrSaiInput').text('—');
        $('#custodianPrAlobsInput').text('—');
        $('#custodianPrFundClusterInput').text('—');
        $('#custodianPrFundsAvailableInput').text('—');
        $('#custodianFundCluster').val('');
        $('#custodianFundsAvailable').val('');
        $('#custodianPrPurposeTextarea').val('');
        $('#custodianPrRequestedPrintedName').text('');
        $('#custodianPrApprovedPrintedName').text('');
        $recommendedName.text('');
        $recommendedDate.text('');
        $recommendedRemarks.text('');
        $recommendedBlock.addClass('hidden');
        $statusErrors.addClass('hidden').find('#custodianStatusErrorsContent').empty();
        $statusRemarks.val('');
        $grandTotalLabel.text(formatCurrency(0));
        $itemsTable.html(`
            <tr>
                <td colspan="8" class="border border-gray-500 px-4 py-16 text-center text-gray-500">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f4f8f5] text-[#1a3a2d]">
                        <i class="fas fa-spinner fa-spin text-xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-gray-700">Loading purchase request details</p>
                    <p class="mt-1 text-xs text-gray-500">Please wait while we prepare the review form.</p>
                </td>
            </tr>
        `);
    };

    const renderWorkflowErrorState = () => {
        $('#custodianPrNumber').text('Unable to load purchase request');
        $itemsTable.html(`
            <tr>
                <td colspan="8" class="border border-gray-500 px-4 py-16 text-center text-gray-500">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <i class="fas fa-triangle-exclamation text-xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-gray-700">Failed to load purchase request details</p>
                    <p class="mt-1 text-xs text-gray-500">Close the modal and try again.</p>
                </td>
            </tr>
        `);
    };

    const getAllowedStatusIds = (currentStatusId) => {
        const current = String(currentStatusId ?? '');
        if (!current) {
            return $statusSelect.find('option').map((_, option) => String(option.value)).get().filter((value) => value !== '');
        }
        const nextStatuses = transitionMap[current];
        if (!nextStatuses || nextStatuses.length === 0) {
            return [current];
        }
        const allowed = new Set([current, ...nextStatuses]);
        return Array.from(allowed);
    };

    const applyStatusOptionFilter = (currentStatusId) => {
        const allowed = new Set(getAllowedStatusIds(currentStatusId));
        $statusSelect.find('option').each(function () {
            const value = String($(this).val());
            const isSystemManaged = this.dataset.systemManaged === 'true';
            const shouldShow = allowed.size === 0 ? true : allowed.has(value);
            this.hidden = !shouldShow;
            if (!isSystemManaged) {
                $(this).prop('disabled', !shouldShow);
            }
        });

        const selectedValue = String($statusSelect.val() ?? '');
        if (allowed.size > 0 && !allowed.has(selectedValue)) {
            $statusSelect.val(String(currentStatusId ?? ''));
        }
    };

    const toggleSystemNotice = (selectedStatusId) => {
        if (!$systemNotice.length) {
            return;
        }

        const isManaged = systemManagedStatuses.includes(String(selectedStatusId ?? ''));
        if (isManaged) {
            $systemNotice.removeClass('hidden');
        } else {
            $systemNotice.addClass('hidden');
        }
    };

    const enforceRemarksRequirement = (selectedStatusId) => {
        const statusIdString = String(selectedStatusId ?? '');
        const shouldRequireRemarks = remarksRequiredStatuses.includes(statusIdString);

        if (shouldRequireRemarks) {
            $statusRemarks.attr('placeholder', 'Provide the required remarks for this status')
                .addClass('border-rose-400 focus:ring-rose-200 focus:border-rose-400');
        } else {
            $statusRemarks.attr('placeholder', remarksDefaultPlaceholder)
                .removeClass('border-rose-400 focus:ring-rose-200 focus:border-rose-400');
        }
    };

    const renderCustodianDecisionSummary = (item) => {
        if (!item) {
            return '';
        }

        const segments = [];

        if (item.employee_decision === 'accept') {
            segments.push('<div class="text-xs italic text-emerald-700">Requester accepted the alternative item.</div>');
        } else if (item.employee_decision === 'wait') {
            segments.push('<div class="text-xs italic text-amber-700">Requester prefers to wait for the original item.</div>');
        }

        if (item.employee_wait_until) {
            const waitLabel = formatDateDisplay(item.employee_wait_until);
            const waitExpired = isDatePast(item.employee_wait_until);
            const waitClass = waitExpired ? 'text-rose-700' : 'text-amber-700';
            segments.push(`<div class="text-xs font-semibold ${waitClass}"><i class="fas fa-hourglass-half mr-1"></i>${waitExpired ? 'Wait period lapsed on ' : 'Waiting until '}${waitLabel}</div>`);
        }

        if (item.employee_wait_note) {
            segments.push(`<div class="text-xs text-gray-500 italic">“${escapeHtml(item.employee_wait_note)}”</div>`);
        }

        return segments.length ? `<div class="mt-2 space-y-1">${segments.join('')}</div>` : '';
    };

    const renderCustodianStatusDisplay = (item) => {
        if (!item || !item.fulfillment_status) {
            return '<span class="text-xs text-gray-500">No fulfillment updates recorded.</span>';
        }

        const meta = itemStatusMeta[item.fulfillment_status] ?? itemStatusMeta.pending;
        let html = `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ${meta.badge}"><i class="fas fa-circle text-[8px]"></i>${meta.label}</span>`;

        if (item.fulfillment_status === 'alternative' && item.alternate_description) {
            html += `<div class="mt-1 text-xs text-gray-600">Alt: ${escapeHtml(item.alternate_description)}</div>`;
        }

        if (item.fulfillment_status === 'unavailable' && !item.employee_wait_until) {
            html += '<div class="mt-1 text-xs text-amber-700">Awaiting requester timeframe.</div>';
        }

        html += renderCustodianDecisionSummary(item);

        return html;
    };

    const fulfillmentOptions = [
        { value: 'ordered', label: 'Ordered as requested' },
        { value: 'alternative', label: 'Propose alternative' },
        { value: 'unavailable', label: 'Mark as unavailable' },
        { value: 'pending', label: 'Pending evaluation' },
    ];

    const updateRowTotal = ($row) => {
        const quantity = toNumber($row.data('quantity'), 0);
        const $unitCostInput = $row.find('.js-item-unit-cost');
        let unitCost = toNumber($row.data('unitCost'), 0);

        if ($unitCostInput.length) {
            unitCost = $unitCostInput.prop('disabled') ? 0 : toNumber($unitCostInput.val(), 0);
        }

        const total = quantity * unitCost;
        $row.data('unitCost', unitCost);
        $row.data('totalCost', total);
        $row.find('[data-item-total]').text(formatCurrency(total));
    };

    const refreshGrandTotal = () => {
        let aggregate = 0;
        $itemsTable.find('tr').each((_, row) => {
            aggregate += toNumber($(row).data('totalCost'), 0);
        });
        $grandTotalLabel.text(formatCurrency(aggregate));
    };

    const syncRowEditingState = ($row, status) => {
        const normalizedStatus = String(status ?? '');
        const $altWrapper = $row.find('[data-alt-wrapper]');
        const $altInput = $row.find('.js-item-alt');

        if ($altWrapper.length) {
            if (normalizedStatus === 'alternative') {
                $altWrapper.removeClass('hidden');
                $altInput.prop('disabled', false);
            } else {
                $altWrapper.addClass('hidden');
                $altInput.prop('disabled', true);
            }
        }

        const $unitCostInput = $row.find('.js-item-unit-cost');
        if ($unitCostInput.length) {
            if (normalizedStatus === 'unavailable') {
                $unitCostInput.val('0.00').prop('disabled', true).addClass('bg-gray-100 text-gray-500');
            } else {
                $unitCostInput.prop('disabled', false).removeClass('bg-gray-100 text-gray-500');
                if (!$unitCostInput.val()) {
                    $unitCostInput.val('0.00');
                }
            }
        }

        updateRowTotal($row);
    };

    const buildItemRow = (item, requestStatusId, canEditItems) => {
        const quantityNumber = toNumber(item.quantity, 0);
        const quantityDisplay = escapeHtml(String(item.quantity ?? '0'));
        const unitLabel = escapeHtml(item.unit ?? '');
        const description = escapeHtml(item.item_description ?? '');
        const stockDisplay = item.stock_number ? escapeHtml(item.stock_number) : '—';
        const rawUnitCost = item.estimated_unit_cost ?? (quantityNumber ? Number(item.estimated_total_cost ?? 0) / Math.max(quantityNumber, 1) : 0);
        const normalizedUnitCost = toNumber(rawUnitCost, 0);
        const fulfillment = item.fulfillment_status || 'pending';
        const effectiveUnitCost = fulfillment === 'unavailable' ? 0 : normalizedUnitCost;
        const totalCost = quantityNumber * effectiveUnitCost;
        const altDescription = item.alternate_description ?? '';
        const fulfillmentOptionsHtml = fulfillmentOptions.map(({ value, label }) => `<option value="${value}" ${value === fulfillment ? 'selected' : ''}>${label}</option>`).join('');

        const unitCostHtml = canEditItems
            ? `<div class="relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 text-xs">₱</span>
                    <input type="number" min="0" step="0.01" value="${toFixed(effectiveUnitCost)}" class="js-item-unit-cost w-full border border-gray-300 pl-6 pr-2 py-1.5 text-sm text-right font-semibold focus:border-[#1a3a2d] focus:outline-none focus:ring-0 rounded-lg ${fulfillment === 'unavailable' ? 'bg-gray-100 text-gray-500' : ''}" ${fulfillment === 'unavailable' ? 'disabled' : ''}>
               </div>`
            : `<span class="font-semibold text-gray-800">${formatCurrency(normalizedUnitCost)}</span>`;

        const fulfillmentHtml = canEditItems
            ? `<div class="space-y-2">
                    <label class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">Fulfillment Status</label>
                    <select class="js-item-fulfillment w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none">${fulfillmentOptionsHtml}</select>
                    <div data-alt-wrapper class="space-y-1 ${fulfillment === 'alternative' ? '' : 'hidden'}">
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Alternative Description</label>
                        <textarea class="js-item-alt w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-[#1a3a2d] focus:outline-none" rows="2" ${fulfillment === 'alternative' ? '' : 'disabled'}>${escapeHtml(altDescription)}</textarea>
                    </div>
                    ${renderCustodianDecisionSummary(item)}
               </div>`
            : renderCustodianStatusDisplay(item);

        const actionsHtml = canEditItems
            ? `<div class="flex flex-col items-end gap-2">
                    <button type="button" class="js-save-item rounded-lg bg-[#1a3a2d] px-4 py-2 text-xs font-semibold text-white shadow hover:bg-[#164928] transition">Save Item</button>
                    <div class="hidden text-xs text-rose-600 text-right" data-item-error></div>
               </div>`
            : '<span class="text-gray-400">—</span>';

        const rowHtml = `
            <tr class="align-top" data-pri-id="${escapeHtml(String(item.pri_id))}">
                <td class="border border-gray-500 px-2 py-2 text-center font-semibold">${quantityDisplay}</td>
                <td class="border border-gray-500 px-2 py-2">${unitLabel || '—'}</td>
                <td class="border border-gray-500 px-2 py-2"><div class="text-gray-800">${description}</div></td>
                <td class="border border-gray-500 px-2 py-2">${stockDisplay}</td>
                <td class="border border-gray-500 px-2 py-2 text-right font-semibold">${unitCostHtml}</td>
                <td class="border border-gray-500 px-2 py-2 text-right font-bold"><span data-item-total>${formatCurrency(totalCost)}</span></td>
                <td class="border border-gray-500 px-2 py-2">${fulfillmentHtml}</td>
                <td class="border border-gray-500 px-2 py-2 text-right">${actionsHtml}</td>
            </tr>
        `;

        const $row = $(rowHtml);
        $row.data('item', item);
        $row.data('quantity', quantityNumber);
        $row.data('unitCost', effectiveUnitCost);
        $row.data('totalCost', totalCost);
        $row.data('requestStatusId', String(requestStatusId ?? ''));

        if (canEditItems && itemUpdateTemplate && item.pri_id != null) {
            $row.data('updateUrl', itemUpdateTemplate.replace('__PRI__', item.pri_id));
        }

        if (canEditItems) {
            syncRowEditingState($row, fulfillment);
        } else {
            updateRowTotal($row);
        }

        return $row;
    };

    $statusSelect.on('change', function () {
        const selectedStatusId = $(this).val();
        // DO NOT preemptively show approver name - approval is BAC-only and uses separate workflow
        // The approver name should only display after BAC has actually approved via BAC\PurchaseRequestController
        enforceRemarksRequirement(selectedStatusId);
        toggleSystemNotice(selectedStatusId);
    });

    const populateModal = (data) => {
        $('#custodianPrNo').text(data.pr_no);
        $('#custodianPrDate').text(data.created_at ? new Date(data.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '');
        $('#custodianPrDivisionInput').text(data.division ?? '');
        $('#custodianPrSectionInput').text(data.section ?? '');
        $('#custodianPrSaiInput').text(data.sai_no ?? '');
        $('#custodianPrAlobsInput').text(data.alobs_no ?? '');
        $('#custodianPrFundClusterInput').text(data.fund_cluster ?? '');
        const formattedFunds = data.funds_available != null ? formatCurrency(data.funds_available) : '';
        $('#custodianPrFundsAvailableInput').text(formattedFunds);
        $('#custodianFundCluster').val(data.fund_cluster ?? '');
        $('#custodianFundsAvailable').val(
            data.funds_available != null && data.funds_available !== ''
                ? Number(data.funds_available).toFixed(2)
                : ''
        );
        $('#custodianPrPurposeTextarea').val(data.purpose ?? '');
        $('#custodianPrRequestedPrintedName').text(data.requester ?? 'Unknown');
        $('#custodianPrApprovedPrintedName').text(data.approved_by ?? '');

        const recommendedName = data.recommended_by_name ?? '';
        let recommendedDateLabel = '';

        if (data.recommended_at) {
            const parsed = new Date(String(data.recommended_at).replace(' ', 'T'));
            if (!Number.isNaN(parsed.getTime())) {
                recommendedDateLabel = parsed.toLocaleString('en-US', {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }
        }

        if (recommendedName) {
            $recommendedName.text(recommendedName);
            $recommendedDate.text(recommendedDateLabel || '—');
            $recommendedRemarks.text(data.recommendation_remarks ? data.recommendation_remarks : '—');
            $recommendedBlock.removeClass('hidden');
        } else {
            $recommendedName.text('');
            $recommendedDate.text('');
            $recommendedRemarks.text('');
            $recommendedBlock.addClass('hidden');
        }

        const statusIdString = data.status_id != null ? String(data.status_id) : '';
        $statusSelect.find('option').prop('selected', false);
        if (statusIdString !== '') {
            $statusSelect.find(`option[value="${statusIdString}"]`).prop('selected', true);
        }
        applyStatusOptionFilter(statusIdString);
        enforceRemarksRequirement(statusIdString);
        toggleSystemNotice(statusIdString);
        if (declinedStatusId !== '' && statusIdString === declinedStatusId && data.latest_status_remarks) {
            $statusRemarks.val(data.latest_status_remarks);
        } else {
            $statusRemarks.val('');
        }

        const canEditItems = allowItemEditing && statusIdString !== '' && statusIdString === forApprovalStatusId;

        $itemsTable.empty();
        const items = Array.isArray(data.items) ? data.items : [];
        items.forEach((item) => {
            const $row = buildItemRow(item, statusIdString, canEditItems);
            $itemsTable.append($row);
        });
        refreshGrandTotal();
    };

    $(document)
        .off('click.custodianReview', '.js-view-custodian-pr')
        .on('click.custodianReview', '.js-view-custodian-pr', function () {
        const showUrl = $(this).data('show-url');
        currentUpdateUrl = $(this).data('update-url');
        if (!showUrl) {
            return;
        }

        renderWorkflowLoadingState();
        toggleModal($modal, true);

        $.ajax({
            method: 'GET',
            url: showUrl,
            headers: { Accept: 'application/json' },
            success: (response) => {
                populateModal(response.data);
            },
            error: (xhr) => {
                renderWorkflowErrorState();
                console.error('Failed to load purchase request details', xhr);
                const serverMessage = xhr?.responseJSON?.message || xhr?.responseText || xhr?.statusText;
                showToast(toastSelector, serverMessage || 'Unable to load purchase request details.');
            },
        });
    });

    $itemsTable.on('change', '.js-item-fulfillment', function () {
        const $row = $(this).closest('tr');
        const status = $(this).val();
        syncRowEditingState($row, status);
        refreshGrandTotal();
    });

    $itemsTable.on('input', '.js-item-unit-cost', function () {
        const $row = $(this).closest('tr');
        updateRowTotal($row);
        refreshGrandTotal();
    });

    $itemsTable.on('click', '.js-save-item', function () {
        const $button = $(this);
        const $row = $button.closest('tr');
        const updateUrl = $row.data('updateUrl');
        if (!updateUrl) {
            return;
        }

        const status = String($row.find('.js-item-fulfillment').val() ?? '');
        const $altInput = $row.find('.js-item-alt');
        const altValue = $altInput.length ? $altInput.val() : '';
        const trimmedAlt = typeof altValue === 'string' ? altValue.trim() : '';
        const $unitCostInput = $row.find('.js-item-unit-cost');
        const unitCost = !$unitCostInput.length || $unitCostInput.prop('disabled') ? 0 : toNumber($unitCostInput.val(), 0);
        const $error = $row.find('[data-item-error]');

        $error.addClass('hidden').text('');

        if (status === 'alternative' && trimmedAlt === '') {
            $error.text('Please describe the alternative item.').removeClass('hidden');
            return;
        }

        const payload = {
            fulfillment_status: status,
            alternate_description: trimmedAlt || null,
            estimated_unit_cost: status === 'unavailable' ? 0 : unitCost,
        };

        const originalHtml = $button.html();
        $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');

        $.ajax({
            method: 'PATCH',
            url: updateUrl,
            data: payload,
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: (response) => {
                showToast(toastSelector, response.message ?? 'Item updated.');
                const updatedItem = response.data?.item;
                const requestStatusId = $row.data('requestStatusId');
                const canStillEdit = allowItemEditing && String(requestStatusId ?? '') === forApprovalStatusId;
                if (updatedItem) {
                    updatedItem.request_status_id = updatedItem.request_status_id ?? requestStatusId;
                    const $newRow = buildItemRow(updatedItem, requestStatusId, canStillEdit);
                    $row.replaceWith($newRow);
                    refreshGrandTotal();
                }
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $error.html(messages.map((msg) => `<div>${escapeHtml(msg)}</div>`).join('')).removeClass('hidden');
                } else {
                    const message = xhr.responseJSON?.message || 'Unable to update this item right now.';
                    $error.text(message).removeClass('hidden');
                }
            },
            complete: () => {
                $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').html(originalHtml);
            },
        });
    });

    $statusForm.on('submit', function (event) {
        event.preventDefault();
        if (!currentUpdateUrl) {
            return;
        }
        $statusErrors.addClass('hidden').find('#custodianStatusErrorsContent').empty();
        const submitBtn = $statusForm.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed').html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');

        $.ajax({
            method: 'POST',
            url: currentUpdateUrl,
            data: $statusForm.serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: (response) => {
                toggleModal($modal, false);
                showToast(toastSelector, response.message ?? 'Status updated.');
                setTimeout(() => softNavigate(window.location.href, { replace: true }), 1200);
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $('#custodianStatusErrorsContent').html(messages.map((msg) => `<div class="mb-1">• ${msg}</div>`).join(''));
                    $statusErrors.removeClass('hidden');
                } else {
                    $('#custodianStatusErrorsContent').text('Failed to update status. Please try again.');
                    $statusErrors.removeClass('hidden');
                }
                $modal.find('.overflow-y-auto').scrollTop($statusErrors.offset().top - 100);
            },
            complete: () => {
                submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed').html(originalText);
            },
        });
    });

    $closeElements.on('click', () => toggleModal($modal, false));
    $modal.on('click', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            toggleModal($modal, false);
        }
    });
};

const initCustodianPurchaseRequestUtilities = () => {
    const queuePageSelector = '#custodianQueuePage';
    if (!$(queuePageSelector).length) {
        return;
    }

    let isQueueRequestInFlight = false;
    let activeQueueRequest = null;
    let queueRequestToken = 0;

    const normalize = (value = '') => String(value).toLowerCase().replace(/\s+/g, ' ').trim();
    const getQueuePage = () => $(queuePageSelector);
    const getTable = () => $('#custodianPurchaseRequestsTable');
    const getNoResultsRow = () => $('#purchaseRequestsNoResults');
    const getFilterableRows = () => getTable().find('tbody tr').not('#purchaseRequestsNoResults').filter((_, row) => !row.dataset.staticRow);

    const applyFilter = (term) => {
        const filterValue = normalize(term);
        let visibleCount = 0;

        getFilterableRows().each((_, row) => {
            const $row = $(row);
            const rowText = normalize($row.text());
            const isMatch = !filterValue || rowText.includes(filterValue);
            $row.toggle(isMatch);
            if (isMatch) {
                visibleCount += 1;
            }
        });

        const $noResultsRow = getNoResultsRow();
        if ($noResultsRow.length) {
            if (visibleCount === 0 && filterValue) {
                $noResultsRow.removeClass('hidden');
            } else {
                $noResultsRow.addClass('hidden');
            }
        }
    };

    const buildPrintableTable = () => {
        const $table = getTable();
        const $clone = $table.clone();
        $clone.find('tr').filter((_, row) => $(row).is(':hidden') || row.id === 'purchaseRequestsNoResults' || row.dataset.staticRow).remove();
        return $clone[0]?.outerHTML ?? '';
    };

    const setQueueLoadingState = (loading) => {
        const $queuePage = getQueuePage();

        if (!$queuePage.length) {
            return;
        }

        $queuePage.toggleClass('opacity-70 translate-y-1 pointer-events-none', loading);
        $queuePage.find('.po-tab[data-po-tab], nav[aria-label="Pagination Navigation"] a').toggleClass('pointer-events-none', loading);
    };

    const swapQueuePage = (html, url, pushState = true) => {
        const $parsed = $('<div>').append($.parseHTML(html, document, true));
        const $nextQueuePage = $parsed.find(queuePageSelector).first();
        const $currentQueuePage = getQueuePage();

        if (!$nextQueuePage.length || !$currentQueuePage.length) {
            // If the queue shell no longer exists (user navigated away), ignore this stale response.
            return;
        }

        $nextQueuePage.addClass('opacity-0 translate-y-1');
        $currentQueuePage.replaceWith($nextQueuePage);

        if (pushState && window.location.href !== url) {
            window.history.pushState({ custodianQueueUrl: url }, '', url);
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                $nextQueuePage.removeClass('opacity-0 translate-y-1');
            });
        });
    };

    const loadQueueContent = (url, options = {}) => {
        const { pushState = true } = options;

        if (!url) {
            return;
        }

        if (activeQueueRequest && activeQueueRequest.readyState !== 4) {
            activeQueueRequest.abort();
        }

        const requestToken = ++queueRequestToken;

        isQueueRequestInFlight = true;
        setQueueLoadingState(true);

        activeQueueRequest = $.ajax({
            method: 'GET',
            url,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
            },
            success: (html) => {
                if (requestToken !== queueRequestToken) {
                    return;
                }
                if (!$(queuePageSelector).length) {
                    return;
                }
                swapQueuePage(html, url, pushState);
            },
            error: (_xhr, textStatus) => {
                if (textStatus === 'abort') {
                    return;
                }
                if (!$(queuePageSelector).length) {
                    return;
                }
                // Keep the current screen stable on partial-fetch failure.
                // Avoid redirecting to stale queue URLs during rapid navigation.
            },
            complete: () => {
                if (requestToken === queueRequestToken) {
                    isQueueRequestInFlight = false;
                    setQueueLoadingState(false);
                }
            },
        });
    };

    const buildQueueExportUrl = (baseUrl) => {
        if (!baseUrl) {
            return null;
        }

        const targetUrl = new URL(baseUrl, window.location.origin);
        const currentUrl = new URL(window.location.href);
        const activeTab = currentUrl.searchParams.get('tab')
            || $('#custodianQueuePage .po-tab.is-active').data('poTab')
            || 'all';

        targetUrl.searchParams.set('tab', String(activeTab));

        return targetUrl.toString();
    };

    const handlePrintPdf = () => {
        const baseUrl = String($('#purchaseRequestPrintPdf').data('printUrl') || '');
        const url = buildQueueExportUrl(baseUrl);
        if (!url) {
            return;
        }

        window.open(url, '_blank');
    };

    const handleExportExcel = () => {
        const baseUrl = String($('#purchaseRequestExportExcel').data('excelUrl') || '');
        const url = buildQueueExportUrl(baseUrl);
        if (!url) {
            return;
        }

        window.location.href = url;
    };

    $(document)
        .off('input.custodianQueueSearch', '#purchaseRequestSearch')
        .on('input.custodianQueueSearch', '#purchaseRequestSearch', function () {
            applyFilter($(this).val());
        });

    $(document)
        .off('click.custodianQueuePdf', '#purchaseRequestPrintPdf')
        .on('click.custodianQueuePdf', '#purchaseRequestPrintPdf', function (event) {
            event.preventDefault();
            handlePrintPdf();
        });

    $(document)
        .off('click.custodianQueueExcel', '#purchaseRequestExportExcel')
        .on('click.custodianQueueExcel', '#purchaseRequestExportExcel', function (event) {
            event.preventDefault();
            handleExportExcel();
        });

    $(document)
        .off('click.custodianQueueTabs', `${queuePageSelector} .po-tab[data-po-tab]`)
        .on('click.custodianQueueTabs', `${queuePageSelector} .po-tab[data-po-tab]`, function (event) {
            const href = this.href;

            event.preventDefault();

            if (!href || href === window.location.href) {
                return;
            }

            loadQueueContent(href);
        });

    $(document)
        .off('click.custodianQueuePagination', `${queuePageSelector} nav[aria-label="Pagination Navigation"] a`)
        .on('click.custodianQueuePagination', `${queuePageSelector} nav[aria-label="Pagination Navigation"] a`, function (event) {
            const href = this.href;

            event.preventDefault();

            if (!href || href === window.location.href) {
                return;
            }

            loadQueueContent(href);
        });

    if (!window.__custodianQueuePopstateBound) {
        window.addEventListener('popstate', () => {
            if ($(queuePageSelector).length) {
                loadQueueContent(window.location.href, { pushState: false });
            }
        });

        window.__custodianQueuePopstateBound = true;
    }

    if (!window.__custodianQueueBeforeVisitBound) {
        document.addEventListener('turbo:before-visit', () => {
            queueRequestToken += 1;
            isQueueRequestInFlight = false;
            if (activeQueueRequest && activeQueueRequest.readyState !== 4) {
                activeQueueRequest.abort();
            }
        });

        window.__custodianQueueBeforeVisitBound = true;
    }

    const initialSearch = $('#purchaseRequestSearch').val();
    if (initialSearch) {
        applyFilter(initialSearch);
    }
};

$(() => {
    initEmployeePurchaseRequests();
    initPurchaseRequestWorkflow();
    initCustodianPurchaseRequestUtilities();
});

if (!window.__pqsPurchaseRequestsAutoRefreshBound) {
    document.addEventListener('pqs:auto-refresh', (event) => {
        const isPurchaseRequestsPage = Boolean(
            document.getElementById('employeePurchaseRequestsTable') ||
            document.getElementById('custodianPurchaseRequestsTable') ||
            document.getElementById('custodianQueuePage')
        );

        if (!isPurchaseRequestsPage) {
            return;
        }

        event.preventDefault();
        softNavigate(window.location.href, { replace: true });
    });

    window.__pqsPurchaseRequestsAutoRefreshBound = true;
}