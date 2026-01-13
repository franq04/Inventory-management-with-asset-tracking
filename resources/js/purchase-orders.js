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

const fulfillmentMeta = {
    pending: {
        label: 'Pending',
        badge: 'bg-gray-100 text-gray-600',
    },
    ordered: {
        label: 'Ordered',
        badge: 'bg-emerald-100 text-emerald-700',
    },
    unavailable: {
        label: 'Unavailable',
        badge: 'bg-rose-100 text-rose-700',
    },
    alternative: {
        label: 'Alternative',
        badge: 'bg-sky-100 text-sky-700',
    },
};

const decisionLabels = {
    accept: 'Accepted by employee',
    wait: 'Employee prefers to wait',
};

const renderFulfillmentStatus = (status, decision) => {
    const meta = fulfillmentMeta[status] ?? {
        label: 'Pending',
        badge: 'bg-gray-100 text-gray-600',
    };

    const badge = `<span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold ${meta.badge}">
        <i class="fas fa-circle text-[8px]"></i>${meta.label}
    </span>`;

    if (!decision) {
        return badge;
    }

    const decisionText = decisionLabels[decision] ?? decision;
    return `${badge}<span class="ml-2 text-xs italic text-gray-600">${decisionText}</span>`;
};

const bindPoDetailsModal = () => {
    const $modal = $('#poDetailsModal');
    if (!$modal.length) {
        return;
    }

    const $itemsTarget = $('#poDetailsItems');

    const toggleModal = (open) => {
        if (open) {
            $modal.removeClass('hidden opacity-0');
        } else {
            $modal.addClass('opacity-0');
            setTimeout(() => $modal.addClass('hidden'), 200);
        }
    };

    $modal.on('click', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            toggleModal(false);
        }
    });

    $modal.find('[data-close-modal]').on('click', () => toggleModal(false));

    $('.js-view-po').on('click', function () {
        const url = $(this).data('show-url');
        if (!url) {
            return;
        }

        $.ajax({
            method: 'GET',
            url,
            headers: { Accept: 'application/json' },
            success: (response) => {
                const data = response.data || {};
                $('#poDetailsNumber').text(data.po_no || '');
                $('#poDetailsPr').text(data.pr_no || '');
                $('#poDetailsSupplier').text(data.supplier || '—');
                $('#poDetailsOrderDate').text(data.order_date || '—');
                $('#poDetailsDeliveryDate').text(data.delivery_date || '—');

                $itemsTarget.empty();
                (data.items || []).forEach((item) => {
                        const statusHtml = renderFulfillmentStatus(item.fulfillment_status, item.employee_decision);
                        const alternate = item.alternate_description
                            ? `<div class="text-gray-700">${item.alternate_description}</div>`
                            : '<span class="text-gray-400">—</span>';

                    $itemsTarget.append(`
                        <tr class="border-b last:border-0">
                            <td class="px-3 py-2 text-gray-700">${item.item_description}</td>
                            <td class="px-3 py-2 text-right text-gray-700">${item.quantity}</td>
                            <td class="px-3 py-2 text-center text-gray-500">${item.unit}</td>
                                <td class="px-3 py-2 text-center">${statusHtml}</td>
                                <td class="px-3 py-2">${alternate}</td>
                                <td class="px-3 py-2 text-right text-gray-700">${formatCurrency(item.unit_cost)}</td>
                                <td class="px-3 py-2 text-right text-gray-700">${formatCurrency(item.total_cost)}</td>
                        </tr>
                    `);
                });

                toggleModal(true);
            },
            error: (xhr) => {
                console.error('Failed to load purchase order details', xhr);
                const serverMessage = xhr?.responseJSON?.message || xhr?.responseText || xhr?.statusText;
                alert(serverMessage || 'Unable to load purchase order details.');
            },
        });
    });
};

const bindPurchaseOrderForm = () => {
    const config = window.purchaseOrderConfig;
    const $form = $('#purchaseOrderForm');
    if (!$form.length || !config) {
        return;
    }

    const requests = config.purchaseRequests || {};
    const suppliers = config.suppliers || {};
    const $requestSelect = $('#poPrNo');
    const $purposeField = $('#poPurpose');
    const $itemRows = $('#poItemRows');
    const $errors = $('#poFormErrors');
    const $summaryItems = $('#poSummaryItems');
    const $summaryTotal = $('#poSummaryTotal');
    const $supplierMode = $('input[name="supplier_mode"]');
    const $supplierSelect = $('#poSupplier');
    const $existingSupplierWrapper = $('#poSupplierExistingWrapper');
    const $newSupplierWrapper = $('#poSupplierNewWrapper');
    const $newSupplierFields = $newSupplierWrapper.find('[data-new-supplier-field]');
    const $newSupplierName = $('#poNewSupplierName');
    const $supplierAddress = $('#poSupplierAddress');
    const $supplierTin = $('#poSupplierTin');
    const $conformeName = $('#poConformeName');
    const $conformeDate = $('#poConformeDate');
    const $orderDate = $('#poOrderDate');
    const $orsDate = $('#poOrsDate');
    const $orsAmount = $('#poOrsAmount');
    const $fundsAvailable = $('#poFundsAvailable');

    let currentItems = [];

    const todayIso = new Date().toISOString().split('T')[0];
    if ($orderDate.length && !$orderDate.val()) {
        $orderDate.val(todayIso);
    }
    if ($conformeDate.length && !$conformeDate.val()) {
        $conformeDate.val(todayIso);
    }
    if ($orsDate.length && !$orsDate.val()) {
        $orsDate.val(todayIso);
    }

    const clearExistingSupplierDisplay = () => {
        $supplierAddress.val('');
        $supplierTin.val('');
    };

    const populateExistingSupplier = (supplierId) => {
        const info = suppliers[supplierId] || {};
        $supplierAddress.val(info.address || '');
        $supplierTin.val(info.tin || '');

        if (info.name && !$conformeName.is(':focus')) {
            $conformeName.val(info.name);
        }
    };

    const setNumericField = ($input, value) => {
        if (!$input.length) {
            return;
        }

        if (value === null || value === undefined || value === '') {
            $input.val('');
            return;
        }

        const numeric = Number(value);
        if (Number.isNaN(numeric)) {
            $input.val('');
            return;
        }

        $input.val(numeric.toFixed(2));
    };

    const toggleSupplierModeState = (mode) => {
        const isExisting = mode === 'existing';

        $existingSupplierWrapper.toggleClass('hidden', !isExisting);
        $newSupplierWrapper.toggleClass('hidden', isExisting);

        $supplierSelect.prop('disabled', !isExisting);
        if (isExisting) {
            $supplierSelect.prop('required', true);
            if ($supplierSelect.val()) {
                populateExistingSupplier($supplierSelect.val());
            } else {
                clearExistingSupplierDisplay();
            }
        } else {
            $supplierSelect.prop('required', false).val('');
            clearExistingSupplierDisplay();
        }

        $newSupplierFields.each(function () {
            const $field = $(this);
            if (isExisting) {
                $field.prop('disabled', true).prop('required', false).val('');
            } else {
                $field.prop('disabled', false);
                if ($field.is($newSupplierName)) {
                    $field.prop('required', true);
                }
            }
        });

        if (!isExisting) {
            if ($newSupplierName.val() && !$conformeName.is(':focus')) {
                $conformeName.val($newSupplierName.val());
            }
        } else if (!$supplierSelect.val()) {
            $conformeName.val('');
        }
    };

    $supplierSelect.on('change', function () {
        const selected = $(this).val();
        if (!selected) {
            clearExistingSupplierDisplay();
            return;
        }

        populateExistingSupplier(selected);
    });

    $supplierMode.on('change', function () {
        toggleSupplierModeState($(this).val());
    });

    $newSupplierName.on('input', () => {
        if ($supplierMode.filter(':checked').val() === 'new' && !$conformeName.is(':focus')) {
            $conformeName.val($newSupplierName.val());
        }
    });

    toggleSupplierModeState($supplierMode.filter(':checked').val() || 'existing');
    if (!$supplierSelect.find('option[value]:not([value=""])').length) {
        $supplierMode.filter('[value="new"]').prop('checked', true);
        toggleSupplierModeState('new');
    }

    const renderRequesterResponse = (item) => {
        const parts = [];

        if (item.fulfillment_status === 'alternative') {
            parts.push(item.alternate_description ? `<div class="font-semibold text-gray-700">${escapeHtml(item.alternate_description)}</div>` : '<span class="text-xs text-gray-500">Awaiting alternative details</span>');
        }

        if (item.employee_decision === 'accept') {
            parts.push('<span class="text-xs font-semibold text-emerald-700">Employee accepted the proposed change.</span>');
        } else if (item.employee_decision === 'wait') {
            const waitLabel = item.employee_wait_until ? new Date(`${item.employee_wait_until}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'N/A';
            parts.push(`<span class="text-xs font-semibold text-amber-700">Waiting until ${waitLabel}.</span>`);
            if (item.employee_wait_note) {
                parts.push(`<span class="mt-1 block text-xs italic text-gray-600">“${escapeHtml(item.employee_wait_note)}”</span>`);
            }
        } else {
            parts.push('<span class="text-xs text-gray-500">No employee response yet.</span>');
        }

        return parts.join('');
    };

    const renderItemsTable = () => {
        $itemRows.empty();

        if (!currentItems.length) {
            $itemRows.append('<tr><td colspan="8" class="border border-gray-300 px-4 py-6 text-center text-sm text-gray-500">Select an approved purchase request to preview its items.</td></tr>');
            $summaryItems.text('0');
            $summaryTotal.text(formatCurrency(0));
            setNumericField($orsAmount, 0);
            return;
        }

        let totalQuantity = 0;
        let grandTotal = 0;

        currentItems.forEach((item) => {
            const status = item.fulfillment_status || 'ordered';
            const quantity = Number(item.quantity || 0);
            const unitCost = status === 'unavailable' ? 0 : Number(item.estimated_unit_cost ?? 0);
            const rowTotal = quantity * unitCost;

            if (status !== 'unavailable') {
                totalQuantity += quantity;
                grandTotal += rowTotal;
            }

            $itemRows.append(`
                <tr class="border-b last:border-0">
                    <td class="border border-gray-300 px-3 py-2 text-sm text-gray-700">${item.stock_number ?? '—'}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center text-sm text-gray-700">${item.unit ?? '—'}</td>
                    <td class="border border-gray-300 px-3 py-2 text-sm text-gray-700">${escapeHtml(item.item_description ?? '')}</td>
                    <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-800">${quantity}</td>
                    <td class="border border-gray-300 px-3 py-2 text-right text-sm text-gray-700">${formatCurrency(unitCost)}</td>
                    <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold text-gray-800">${formatCurrency(rowTotal)}</td>
                    <td class="border border-gray-300 px-3 py-2 text-center">${renderFulfillmentStatus(status, item.employee_decision)}</td>
                    <td class="border border-gray-300 px-3 py-2">${renderRequesterResponse(item)}</td>
                </tr>
            `);
        });

        $summaryItems.text(totalQuantity);
        $summaryTotal.text(formatCurrency(grandTotal));
        setNumericField($orsAmount, grandTotal);
    };

    const populateFromRequest = (requestId) => {
        const request = requests[requestId];

        if (!request) {
            currentItems = [];
            $purposeField.val('');
            setNumericField($fundsAvailable, '');
            setNumericField($orsAmount, '');
            renderItemsTable();
            return;
        }

        $purposeField.val(request.purpose || '');
        const fundsSource = request.funds_available ?? request.total_estimated_cost ?? '';
        setNumericField($fundsAvailable, fundsSource);
        if (request.total_estimated_cost !== undefined) {
            setNumericField($orsAmount, request.total_estimated_cost ?? fundsSource);
        }

        currentItems = (request.items || []).map((item) => ({
            ...item,
            estimated_unit_cost: item.estimated_unit_cost ?? 0,
        }));

        renderItemsTable();
    };

    $requestSelect.on('change', function () {
        populateFromRequest($(this).val());
    });

    const initialRequest = config.initialPrNo || $requestSelect.val();
    if (initialRequest && requests[initialRequest]) {
        $requestSelect.val(initialRequest);
        populateFromRequest(initialRequest);
    } else {
        currentItems = [];
        renderItemsTable();
    }

    if ($supplierSelect.val()) {
        populateExistingSupplier($supplierSelect.val());
    }

    $form.on('submit', function (event) {
        event.preventDefault();
        $errors.addClass('hidden').empty();
        const submitBtn = $form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url: config.storeUrl,
            data: $form.serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken() },
            success: (response) => {
                window.location.href = response.redirect || config.redirectUrl || '/custodian/purchase-orders';
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $errors.html(messages.map((msg) => `<div>${msg}</div>`).join('')).removeClass('hidden');
                } else {
                    $errors.text('An unexpected error occurred. Please try again.').removeClass('hidden');
                }
            },
            complete: () => {
                submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });
};

const togglePartialItemFields = ($form) => {
    const status = $form.find('.js-item-status').val();
    const $altWrapper = $form.find('[data-alt-wrapper]');
    const $altInput = $form.find('.js-item-alt');
    const $unitCost = $form.find('.js-item-unit-cost');

    if (status === 'alternative') {
        $altWrapper.removeClass('hidden');
        $altInput.prop('disabled', false).attr('required', true);
        $unitCost.prop('disabled', false);
        return;
    }

    $altWrapper.addClass('hidden');
    $altInput.prop('disabled', true).attr('required', false).val('');

    if (status === 'unavailable') {
        $unitCost.val('0').prop('disabled', true);
    } else {
        $unitCost.prop('disabled', false);
    }
};

const bindPartialOrderActions = () => {
    const $container = $('#purchaseOrderPipeline');
    if (!$container.length) {
        return;
    }

    $container.find('.js-update-po-item').each(function () {
        togglePartialItemFields($(this));
    });

    $container.on('change', '.js-item-status', function () {
        const $form = $(this).closest('form');
        togglePartialItemFields($form);
    });

    $container.on('submit', '.js-update-po-item', function (event) {
        event.preventDefault();
        const $form = $(this);
        const url = $form.data('update-url');
        if (!url) {
            return;
        }

        const $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url,
            data: $form.serialize(),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-HTTP-Method-Override': 'PATCH',
            },
            success: () => {
                window.location.reload();
            },
            error: (xhr) => {
                let message = 'Unable to update this item. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                alert(message);
            },
            complete: () => {
                $submitBtn.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });

    $container.on('click', '.js-retain-original', function () {
        const $button = $(this);
        const url = $button.data('retain-url');
        if (!url) {
            return;
        }

        $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url,
            data: { _token: csrfToken() },
            success: () => {
                window.location.reload();
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Unable to retain the original item right now.';
                alert(message);
            },
            complete: () => {
                $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });
};

const bindAcceptPoActions = () => {
    $('#purchaseOrderPipeline').on('click', '.js-accept-po', function () {
        const $button = $(this);
        const url = $button.data('accept-url');
        if (!url) {
            return;
        }

        if (!confirm('Are you sure you want to accept this purchase order and send it to the supplier?')) {
            return;
        }

        $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url,
            data: { _token: csrfToken() },
            success: () => {
                window.location.reload();
            },
            error: (xhr) => {
                let message = 'Unable to accept this purchase order. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            },
            complete: () => {
                $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });
};

const bindReceiveItemActions = () => {
    const $container = $('#purchaseOrderPipeline');
    if (!$container.length) {
        return;
    }

    $container.on('submit', '.js-receive-item', function (event) {
        event.preventDefault();

        const $form = $(this);
        const url = $form.data('receive-url');
        if (!url) {
            return;
        }

        if (!confirm('Mark this item as received?')) {
            return;
        }

        const $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url,
            data: $form.serialize(),
            success: () => {
                window.location.reload();
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Unable to mark this item as received right now.';
                alert(message);
            },
            complete: () => {
                $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });
};

$(() => {
    bindPoDetailsModal();
    bindPurchaseOrderForm();
    bindPartialOrderActions();
    bindAcceptPoActions();
    bindReceiveItemActions();
});
