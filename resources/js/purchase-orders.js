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

const ensureToast = () => {
    let toast = document.getElementById('pqs-global-toast');
    if (toast) {
        return toast;
    }

    toast = document.createElement('div');
    toast.id = 'pqs-global-toast';
    toast.className = 'fixed bottom-6 right-6 z-[90] hidden max-w-sm rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg';
    document.body.appendChild(toast);
    return toast;
};

const showToast = (message, type = 'error') => {
    const toast = ensureToast();
    toast.textContent = message;
    toast.classList.remove('hidden', 'bg-rose-600', 'bg-emerald-600', 'bg-amber-600');
    toast.classList.add(type === 'success' ? 'bg-emerald-600' : type === 'warning' ? 'bg-amber-600' : 'bg-rose-600');

    window.clearTimeout(showToast._timer);
    showToast._timer = window.setTimeout(() => {
        toast.classList.add('hidden');
    }, 2600);
};

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
    const $panel = $modal.find('.modal-panel').first();

    const toggleModal = (open) => {
        if (open) {
            $modal.removeClass('hidden');
            requestAnimationFrame(() => {
                $modal.removeClass('opacity-0');
                $panel.removeClass('opacity-0 scale-95 translate-y-2');
            });
        } else {
            $modal.addClass('opacity-0');
            $panel.addClass('opacity-0 scale-95 translate-y-2');
            setTimeout(() => $modal.addClass('hidden'), 250);
        }
    };

    $modal.on('click', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            toggleModal(false);
        }
    });

    $modal.find('[data-close-modal]').on('click', () => toggleModal(false));

    $(document).off('click.jsViewPO').on('click.jsViewPO', '.js-view-po', function (e) {
        e.preventDefault();
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
                $('#poDetailsPoNo').text(data.po_no || '');
                $('#poDetailsNumber').text(data.po_no || '');
                $('#poDetailsPr').text(data.pr_no || '');
                $('#poDetailsSupplier').text(data.supplier || '—');
                $('#poDetailsAddress').text(data.supplier_address || '—');
                $('#poDetailsTin').text(data.supplier_tin || '—');
                $('#poDetailsMode').text(data.mode_of_procurement || '—');
                $('#poDetailsPlace').text(data.place_of_delivery || '—');
                $('#poDetailsDeliveryTerm').text(data.delivery_term || '—');
                $('#poDetailsPaymentTerm').text(data.payment_term || '—');
                $('#poDetailsOrderDate').text(data.order_date || '—');
                $('#poDetailsDeliveryDate').text(data.delivery_date || '—');
                $('#poDetailsAmountWords').text(data.amount_in_words || '');
                $('#poDetailsFundCluster').text(data.fund_cluster || '');
                $('#poDetailsFundsAvailable').text(data.funds_available ? formatCurrency(data.funds_available) : '');
                $('#poDetailsOrsBursNo').text(data.ors_burs_no || '');
                $('#poDetailsOrsBursDate').text(data.ors_burs_date || '');
                $('#poDetailsOrsBursAmount').text(data.ors_burs_amount ? formatCurrency(data.ors_burs_amount) : '');
                $('#poDetailsConformeName').text(data.conforme_name || '');
                $('#poDetailsConformeDate').text(data.conforme_date || '');
                $('#poDetailsAuthorizedOfficial').text(data.authorized_official_name || '');
                $('#poDetailsDesignation').text(data.authorized_official_designation || '');
                
                $itemsTarget.empty();
                (data.items || []).forEach((item, index) => {
                        const statusHtml = renderFulfillmentStatus(item.fulfillment_status, item.employee_decision);
                        const alternate = item.alternate_description
                            ? `<div class="text-gray-700">${item.alternate_description}</div>`
                            : '<span class="text-gray-400">—</span>';

                    $itemsTarget.append(`
                        <tr class="border-b last:border-0 hover:bg-gray-50">
                            <td class="border-r border-gray-300 px-4 py-2 text-center text-gray-700">${index + 1}</td>
                            <td class="border-r border-gray-300 px-4 py-2 text-center text-gray-700 font-medium">${item.quantity}</td>
                            <td class="border-r border-gray-300 px-4 py-2 text-center text-gray-500">${item.unit}</td>
                            <td class="border-r border-gray-300 px-4 py-2 text-gray-900 font-medium">
                                <div class="mb-1">${item.item_description}</div>
                                ${item.alternate_description ? `<div class="text-xs text-amber-700 mt-1 flex items-center gap-1"><i class="fas fa-exchange-alt"></i> Alt: ${item.alternate_description}</div>` : ''}
                            </td>
                            <td class="border-r border-gray-300 px-4 py-2 text-center">${statusHtml}</td>
                            <td class="border-r border-gray-300 px-4 py-2 text-right text-gray-700">${formatCurrency(item.unit_cost)}</td>
                            <td class="px-4 py-2 text-right text-gray-900 font-bold">${formatCurrency(item.total_cost)}</td>
                        </tr>
                    `);
                });

                // Render status history
                const $statusHistory = $('#poStatusHistory');
                $statusHistory.empty();
                const historyItems = data.status_history || [];
                if (historyItems.length === 0) {
                    $statusHistory.html('<p class="text-gray-500 text-sm italic">No status history available.</p>');
                } else {
                    const historyHtml = historyItems.map((entry, index) => {
                        const isFirst = index === 0;
                        const statusChange = entry.old_status
                            ? `<span class="text-gray-500">${escapeHtml(entry.old_status)}</span> <i class="fas fa-arrow-right text-gray-400 text-xs mx-1"></i> <span class="font-semibold text-indigo-600">${escapeHtml(entry.new_status)}</span>`
                            : `<span class="font-semibold text-emerald-600">${escapeHtml(entry.new_status)}</span>`;

                        const remarksHtml = entry.remarks
                            ? `<div class="mt-1 text-xs text-gray-600 italic bg-gray-100 rounded px-2 py-1"><i class="fas fa-comment-alt mr-1 text-gray-400"></i>${escapeHtml(entry.remarks)}</div>`
                            : '';

                        return `
                            <div class="relative pl-6 pb-4 ${!isFirst ? 'border-l-2 border-gray-200 ml-2' : ''}">
                                <div class="absolute left-0 top-0 w-4 h-4 rounded-full ${isFirst ? 'bg-indigo-500' : 'bg-gray-300'} flex items-center justify-center -translate-x-1/2">
                                    <i class="fas fa-circle text-white text-[6px]"></i>
                                </div>
                                <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm ml-2">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div class="text-sm">${statusChange}</div>
                                        <div class="text-xs text-gray-400">${escapeHtml(entry.changed_at)}</div>
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        <i class="fas fa-user mr-1 text-gray-400"></i>
                                        ${entry.changed_by ? escapeHtml(entry.changed_by) : '<span class="italic">System</span>'}
                                    </div>
                                    ${remarksHtml}
                                </div>
                            </div>
                        `;
                    }).join('');
                    $statusHistory.html(`<div class="space-y-0">${historyHtml}</div>`);
                }

                toggleModal(true);
            },
            error: (xhr) => {
                console.error('Failed to load purchase order details', xhr);
                const serverMessage = xhr?.responseJSON?.message || xhr?.responseText || xhr?.statusText;
                showToast(serverMessage || 'Unable to load purchase order details.');
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
    const $supplierAddressRow = $('#poSupplierAddressRow');
    const $supplierAddress = $('#poSupplierAddress');
    const $supplierTin = $('#poSupplierTin');
    const $supplierTinRow = $('#poSupplierTinRow');
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
        $supplierAddressRow.toggleClass('hidden', !isExisting);
        $supplierTinRow.toggleClass('hidden', !isExisting);

        $supplierSelect.prop('disabled', !isExisting);
        if (isExisting) {
            if ($supplierSelect.val()) {
                populateExistingSupplier($supplierSelect.val());
            } else {
                clearExistingSupplierDisplay();
            }
        } else {
            $supplierSelect.val('');
            clearExistingSupplierDisplay();
        }

        $newSupplierFields.each(function () {
            const $field = $(this);
            if (isExisting) {
                $field.prop('disabled', true).val('');
            } else {
                $field.prop('disabled', false);
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
                softNavigate(response.redirect || config.redirectUrl || '/custodian/purchase-orders');
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
        $altInput.prop('disabled', false);
        $unitCost.prop('disabled', false);
        return;
    }

    $altWrapper.addClass('hidden');
    $altInput.prop('disabled', true).val('');

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
                softNavigate(window.location.href, { replace: true });
            },
            error: (xhr) => {
                let message = 'Unable to update this item. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                showToast(message);
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
                softNavigate(window.location.href, { replace: true });
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Unable to retain the original item right now.';
                showToast(message);
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
                softNavigate(window.location.href, { replace: true });
            },
            error: (xhr) => {
                let message = 'Unable to accept this purchase order. Please try again.';
                if (xhr.status === 422 && xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                showToast(message);
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
                softNavigate(window.location.href, { replace: true });
            },
            error: (xhr) => {
                const message = xhr.responseJSON?.message || 'Unable to mark this item as received right now.';
                showToast(message);
            },
            complete: () => {
                $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            },
        });
    });
};

const bindCustodianRequestViewFallback = () => {
    let $modal = $('#custodianPrModal');
    if (!$modal.length) {
        $('body').append(`
            <div id="custodianPrModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-modal></div>
                <div class="relative flex items-center justify-center min-h-screen p-4">
                    <div class="modal-panel relative flex flex-col bg-white w-full max-w-4xl rounded-2xl shadow-2xl max-h-[calc(100vh-2rem)] transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
                        <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white">
                            <h3 class="text-xl font-bold">Purchase Request Details</h3>
                            <button type="button" class="p-2 rounded hover:bg-white/10" data-close-modal>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div id="custodianPrNumber" class="text-sm font-semibold text-gray-700"></div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                <div><span class="font-semibold">PR No:</span> <span id="custodianPrNo"></span></div>
                                <div><span class="font-semibold">Date:</span> <span id="custodianPrDate"></span></div>
                                <div><span class="font-semibold">Division:</span> <span id="custodianPrDivisionInput"></span></div>
                                <div><span class="font-semibold">Section:</span> <span id="custodianPrSectionInput"></span></div>
                            </div>
                            <textarea id="custodianPrPurposeTextarea" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" rows="3" readonly></textarea>
                            <div class="overflow-x-auto border rounded">
                                <table class="w-full text-sm">
                                    <tbody id="custodianPrItemsTable"></tbody>
                                </table>
                            </div>
                            <div class="text-right font-bold" id="custodianPrGrandTotal"></div>
                        </div>
                    </div>
                </div>
            </div>
        `);
        $modal = $('#custodianPrModal');
    }

    const $panel = $modal.find('.modal-panel');
    const $items = $('#custodianPrItemsTable');

    const formatDateTime = (value) => {
        if (!value) {
            return '—';
        }
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const openModal = () => {
        $modal.removeClass('hidden opacity-0');
        requestAnimationFrame(() => {
            $panel.removeClass('opacity-0 scale-95 translate-y-2');
        });
    };

    const closeModal = () => {
        $modal.addClass('opacity-0');
        $panel.addClass('opacity-0 scale-95 translate-y-2');
        setTimeout(() => $modal.addClass('hidden'), 200);
    };

    $modal.off('click.custodianPrFallback').on('click.custodianPrFallback', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            closeModal();
        }
    });
    $modal.find('[data-close-modal]').off('click.custodianPrFallback').on('click.custodianPrFallback', closeModal);

    const openRequestFromTrigger = (triggerElement) => {
        const showUrl = $(triggerElement).data('show-url');
        if (!showUrl) {
            return;
        }

        $('#custodianPrNumber').text('Loading purchase request...');
        $items.html('<tr><td colspan="8" class="border border-gray-500 px-4 py-8 text-center text-gray-500">Loading...</td></tr>');
        openModal();

        $.ajax({
            method: 'GET',
            url: showUrl,
            headers: { Accept: 'application/json' },
            success: (response) => {
                const data = response?.data || {};

                $('#custodianPrNumber').text(data.pr_no || 'Purchase Request Review');
                $('#custodianPrNo').text(data.pr_no || '—');
                $('#custodianPrDate').text(formatDateTime(data.created_at));
                $('#custodianPrDivisionInput').text(data.division || '—');
                $('#custodianPrSectionInput').text(data.section || '—');
                $('#custodianPrSaiInput').text(data.sai_no || '—');
                $('#custodianPrAlobsInput').text(data.alobs_no || '—');
                $('#custodianPrFundClusterInput').text(data.fund_cluster || '—');
                $('#custodianPrFundsAvailableInput').text(data.funds_available ? formatCurrency(data.funds_available) : '—');
                $('#custodianPrPurposeTextarea').val(data.purpose || '');
                $('#custodianPrRequestedPrintedName').text(data.requester || '');
                $('#custodianPrApprovedPrintedName').text(data.approved_by || '');

                if (data.recommended_by_name) {
                    $('#custodianPrRecommendedPrintedName').text(data.recommended_by_name);
                    $('#custodianPrRecommendedDate').text(formatDateTime(data.recommended_at));
                    $('#custodianPrRecommendationRemarks').text(data.recommendation_remarks || '—');
                    $('#custodianPrRecommendedBlock').removeClass('hidden');
                } else {
                    $('#custodianPrRecommendedPrintedName').text('');
                    $('#custodianPrRecommendedDate').text('');
                    $('#custodianPrRecommendationRemarks').text('');
                    $('#custodianPrRecommendedBlock').addClass('hidden');
                }

                const items = Array.isArray(data.items) ? data.items : [];
                if (!items.length) {
                    $items.html('<tr><td colspan="8" class="border border-gray-500 px-4 py-8 text-center text-gray-500">No items found.</td></tr>');
                    $('#custodianPrGrandTotal').text(formatCurrency(data.total_estimated_cost || 0));
                    return;
                }

                $items.empty();
                items.forEach((item) => {
                    $items.append(`
                            <tr>
                                <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.quantity ?? '')}</td>
                                <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.unit ?? '')}</td>
                                <td class="border border-gray-500 px-3 py-2">${escapeHtml(item.item_description ?? '')}</td>
                                <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.stock_number ?? '')}</td>
                                <td class="border border-gray-500 px-3 py-2 text-right">${formatCurrency(item.estimated_unit_cost ?? 0)}</td>
                                <td class="border border-gray-500 px-3 py-2 text-right">${formatCurrency(item.estimated_total_cost ?? 0)}</td>
                                <td class="border border-gray-500 px-3 py-2 text-center">${escapeHtml(item.fulfillment_status ?? 'pending')}</td>
                                <td class="border border-gray-500 px-3 py-2 text-center">—</td>
                            </tr>
                    `);
                });

                $('#custodianPrGrandTotal').text(formatCurrency(data.total_estimated_cost || 0));
            },
            error: (xhr) => {
                const message = xhr?.responseJSON?.message || xhr?.responseText || xhr?.statusText || 'Unable to load purchase request details.';
                $items.html(`<tr><td colspan="8" class="border border-gray-500 px-4 py-8 text-center text-rose-600">${escapeHtml(message)}</td></tr>`);
            },
        });
    };

    window.__openCustodianRequestModalFallback = openRequestFromTrigger;
    if (typeof window.__openCustodianRequestModal !== 'function') {
        window.__openCustodianRequestModal = openRequestFromTrigger;
    }

    $(document)
        .off('click.jsViewCustodianPrFallback', '.js-ready-po-pr-view')
        .on('click.jsViewCustodianPrFallback', '.js-ready-po-pr-view', function (event) {
            event.preventDefault();
            openRequestFromTrigger(this);
        });
};

const bindPurchaseOrderRegisterPagination = () => {
    const sectionSelector = '#purchaseOrderRegisterSection';
    if (!$(sectionSelector).length) {
        return;
    }

    let activeRequest = null;
    let requestToken = 0;
    let isLoading = false;

    const setLoadingState = (loading) => {
        const $section = $(sectionSelector);
        if (!$section.length) {
            return;
        }

        $section.toggleClass('opacity-70 pointer-events-none', loading);
        $section.find('nav[aria-label="Pagination Navigation"] a').toggleClass('pointer-events-none', loading);
    };

    const swapSection = (html, url, pushState = true) => {
        const $parsed = $('<div>').append($.parseHTML(html, document, true));
        const $incoming = $parsed.find(sectionSelector).first();
        const $current = $(sectionSelector).first();

        if (!$incoming.length || !$current.length) {
            return;
        }

        $incoming.addClass('opacity-0 translate-y-1');
        $current.replaceWith($incoming);

        if (pushState && window.location.href !== url) {
            window.history.pushState({ purchaseOrderRegisterUrl: url }, '', url);
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                $incoming.removeClass('opacity-0 translate-y-1');
            });
        });
    };

    const loadRegisterPage = (url, options = {}) => {
        const { pushState = true } = options;
        if (!url || isLoading) {
            return;
        }

        if (activeRequest && activeRequest.readyState !== 4) {
            activeRequest.abort();
        }

        const currentToken = ++requestToken;
        isLoading = true;
        setLoadingState(true);

        activeRequest = $.ajax({
            method: 'GET',
            url,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
            },
            success: (html) => {
                if (currentToken !== requestToken) {
                    return;
                }
                swapSection(html, url, pushState);
            },
            error: (_xhr, textStatus) => {
                if (textStatus === 'abort') {
                    return;
                }
            },
            complete: () => {
                if (currentToken === requestToken) {
                    isLoading = false;
                    setLoadingState(false);
                }
            },
        });
    };

    const captureHandler = (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const link = target.closest(`${sectionSelector} nav[aria-label="Pagination Navigation"] a[href]`);
        if (!link) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const url = link.getAttribute('href');
        if (!url) {
            return;
        }

        loadRegisterPage(url, { pushState: true });
    };

    if (window.__purchaseOrderRegisterCaptureHandler) {
        document.removeEventListener('click', window.__purchaseOrderRegisterCaptureHandler, true);
    }

    window.__purchaseOrderRegisterCaptureHandler = captureHandler;
    document.addEventListener('click', captureHandler, true);

    if (!window.__purchaseOrderRegisterPopstateBound) {
        window.addEventListener('popstate', () => {
            if (!$(sectionSelector).length || isLoading) {
                return;
            }

            loadRegisterPage(window.location.href, { pushState: false });
        });

        window.__purchaseOrderRegisterPopstateBound = true;
    }
};

$(() => {
    bindPoDetailsModal();
    bindCustodianRequestViewFallback();
    bindPurchaseOrderRegisterPagination();
    bindPurchaseOrderForm();
    bindPartialOrderActions();
    bindAcceptPoActions();
    bindReceiveItemActions();
});
