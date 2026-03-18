import $ from 'jquery';
import {
    csrfToken,
    formatCurrency,
    escapeHtml,
    formatDateDisplay,
    isDatePast,
    itemStatusMeta,
    hideToast,
    showToast,
    toggleModal,
} from './purchase-requests-shared.js';

let workflowBound = false;
let queueNavigationBound = false;

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

const initPurchaseRequestWorkflow = () => {
    const config = window.purchaseRequestWorkflowConfig;
    if (!config) {
        return;
    }

    // Guard against double-binding direct event handlers on persistent modal elements
    if (workflowBound) {
        return;
    }
    workflowBound = true;

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
    const $requestedSignature = $('#custodianPrRequestedSignature');
    const $recommendedSignature = $('#custodianPrRecommendedSignature');
    const $approvedSignature = $('#custodianPrApprovedSignature');
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

    const renderSignature = (signatureDataUrl) => {
        if (!signatureDataUrl) {
            return '';
        }

        return `<img src="${signatureDataUrl}" alt="Signature" class="max-h-10 w-auto object-contain">`;
    };

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
        $requestedSignature.empty();
        $recommendedSignature.empty();
        $approvedSignature.empty();
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
            segments.push(`<div class="text-xs text-gray-500 italic">"${escapeHtml(item.employee_wait_note)}"</div>`);
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
        $requestedSignature.html(renderSignature(data.requester_signature || ''));
        $recommendedSignature.html(renderSignature(data.recommended_signature || ''));
        $approvedSignature.html(renderSignature(data.approved_signature || ''));

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
        .on('click.custodianReview', '.js-view-custodian-pr', function (event) {
        event.preventDefault();
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
        const $rows = getFilterableRows();
        const totalRows = $rows.length;
        let visibleCount = 0;

        $rows.each((_, row) => {
            const $row = $(row);
            const rowSearch = normalize($row.data('search') || $row.text());
            const matchesSearch = !filterValue || rowSearch.includes(filterValue);
            const isMatch = matchesSearch;

            $row.toggleClass('hidden', !isMatch);
            if (isMatch) {
                visibleCount += 1;
            }
        });

        const $noResultsRow = getNoResultsRow();
        if ($noResultsRow.length) {
            $noResultsRow.toggleClass('hidden', !(totalRows > 0 && visibleCount === 0));
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
            // If user already navigated away, ignore stale queue responses.
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
        const { pushState = true, searchTerm = '' } = options;

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

                const $searchInput = $('#purchaseRequestSearch');
                if ($searchInput.length) {
                    $searchInput.val(searchTerm);
                }
                applyFilter(searchTerm);
            },
            error: (_xhr, textStatus) => {
                if (textStatus === 'abort') {
                    return;
                }
                if (!$(queuePageSelector).length) {
                    return;
                }
                // Keep the current route stable on partial-fetch failure.
            },
            complete: () => {
                if (requestToken === queueRequestToken) {
                    isQueueRequestInFlight = false;
                    setQueueLoadingState(false);
                }
            },
        });
    };

    // Queue links use delegated AJAX handlers for smooth, no-refresh tab and pagination transitions.
    queueNavigationBound = true;

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
        .off('click.custodianQueueStatus', '#custodianQueuePage [data-status-btn]')
        .on('click.custodianQueueStatus', '#custodianQueuePage [data-status-btn]', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const url = this.getAttribute('data-tab-url') || this.getAttribute('href');
            if (!url || isQueueRequestInFlight) {
                return;
            }

            const searchTerm = String($('#purchaseRequestSearch').val() || '');
            loadQueueContent(url, { pushState: true, searchTerm });
        });

    $(document)
        .off('click.custodianQueuePageLinks', '#custodianQueuePage nav[aria-label="Pagination Navigation"] a')
        .on('click.custodianQueuePageLinks', '#custodianQueuePage nav[aria-label="Pagination Navigation"] a', function (event) {
            event.preventDefault();
            event.stopPropagation();
            const url = this.getAttribute('href');
            if (!url || isQueueRequestInFlight) {
                return;
            }

            const searchTerm = String($('#purchaseRequestSearch').val() || '');
            loadQueueContent(url, { pushState: true, searchTerm });
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

    if (!window.__custodianQueuePopstateBound) {
        window.addEventListener('popstate', () => {
            if (!$(queuePageSelector).length || isQueueRequestInFlight) {
                return;
            }

            const searchTerm = String($('#purchaseRequestSearch').val() || '');
            loadQueueContent(window.location.href, { pushState: false, searchTerm });
        });

        window.__custodianQueuePopstateBound = true;
    }

    const initialSearch = $('#purchaseRequestSearch').val();
    applyFilter(initialSearch);
};

const init = () => {
    initPurchaseRequestWorkflow();
    initCustodianPurchaseRequestUtilities();
};

// Standard page load
$(init);

// Turbo Drive navigations — module scripts only execute once,
// so jQuery-ready won't re-fire. Reset the guard so element
// handlers are re-bound to the fresh DOM that Turbo swapped in.
// queueNavigationBound stays true to keep queue navigation delegated to Turbo/default links.
document.addEventListener('turbo:load', () => {
    workflowBound = false;
    init();
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        workflowBound = false;
        init();
    }, { once: true });
} else {
    workflowBound = false;
    init();
}
