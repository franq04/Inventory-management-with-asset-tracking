import $ from 'jquery';

const csrfToken = () => $('meta[name="csrf-token"]').attr('content');

const todayIso = () => new Date().toISOString().slice(0, 10);

const escapeHtml = (value) => $('<div>').text(value ?? '').html();

const initInspectionTabs = () => {
    const $tabs = $('[data-inspection-tab]');
    const $panels = $('[data-inspection-panel]');

    if (!$tabs.length || !$panels.length) {
        return;
    }

    $tabs.on('click', function () {
        const target = $(this).data('inspection-tab');

        // Reset all tabs (remove selected marker and visual styles)
        $tabs.removeClass('is-selected shadow-lg');

        // Mark clicked tab as selected and apply visual styles only to it
        $(this).addClass('is-selected shadow-lg');

        $panels.addClass('hidden');
        $panels.filter(`[data-inspection-panel="${target}"]`).removeClass('hidden');
    });

    const params = new URLSearchParams(window.location.search);
    let activeTabKey = null;

    $tabs.each(function () {
        const key = $(this).data('inspection-tab');
        if (key && params.has(`${key}_page`)) {
            activeTabKey = key;
            return false;
        }
    });

    const $initialTab = activeTabKey
        ? $tabs.filter(`[data-inspection-tab="${activeTabKey}"]`).first()
        : $tabs.first();

    if ($initialTab.length) {
        $initialTab.trigger('click');
    }
};

const prunePendingAccepted = () => {
    // Remove rows in the pending inspection panel where accepted >= delivered
    const $panel = $('[data-inspection-panel="pending"]');
    if (!$panel.length) return;

    $panel.find('tbody tr').each(function () {
        const $tr = $(this);
        // find delivered and accepted columns by position: Delivered (5th col index 4?), but safer to parse cells
        const deliveredText = $tr.find('td').eq(4).text().trim();
        const acceptedText = $tr.find('td').eq(5).text().trim();

        const delivered = Number(deliveredText.replace(/[^0-9.-]+/g, '')) || 0;
        const accepted = Number(acceptedText.replace(/[^0-9.-]+/g, '')) || 0;

        if (accepted > 0 && accepted >= delivered) {
            $tr.remove();
        }
    });
};

const initInspectionFilters = () => {
    const $search = $('#inspectionSearch');
    const $from = $('#inspectionDateFrom');
    const $to = $('#inspectionDateTo');
    const $applyDateFilter = $('#inspectionApplyDateFilter');

    const normalize = (s) => String(s ?? '').toLowerCase();

    const applyFiltersToPanel = ($panel) => {
        const term = normalize($search.val());
        const from = $from.val() ? new Date($from.val()) : null;
        const to = $to.val() ? new Date($to.val()) : null;

        $panel.find('tbody tr').each(function () {
            const $tr = $(this);
            const text = normalize($tr.text());

            // Date heuristic: look for date-like substrings in the row (YYYY-MM-DD or MMM DD, YYYY)
            let visible = true;

            if (term && !text.includes(term)) {
                visible = false;
            }

            if (visible && (from || to)) {
                // try to find a date in Warranty column (index 7)
                const dateCell = $tr.find('td').eq(7).text().trim();
                let parsedDate = null;
                // try ISO
                const isoMatch = dateCell.match(/(\d{4}-\d{2}-\d{2})/);
                if (isoMatch) parsedDate = new Date(isoMatch[1]);

                if (!parsedDate) {
                    // try Month name formats (e.g., Oct 26, 2025)
                    const altMatch = dateCell.match(/([A-Za-z]{3,9}\s+\d{1,2},\s*\d{4})/);
                    if (altMatch) parsedDate = new Date(altMatch[1]);
                }

                if (parsedDate) {
                    if (from && parsedDate < from) visible = false;
                    if (to && parsedDate > to) visible = false;
                }
            }

            $tr.toggle(visible);
        });
    };

    // Apply filters to all panels when inputs change
    $search.on('input', () => {
        $('[data-inspection-panel]').each(function () { applyFiltersToPanel($(this)); });
    });

    const applyDateFilters = () => {
        $('[data-inspection-panel]').each(function () { applyFiltersToPanel($(this)); });
    };

    $applyDateFilter.on('click', applyDateFilters);
    $from.on('keydown', (event) => { if (event.key === 'Enter') applyDateFilters(); });
    $to.on('keydown', (event) => { if (event.key === 'Enter') applyDateFilters(); });

    $('#inspectionPrintPdfBtn').on('click', () => {
        // Print currently visible rows for active panel
        const $activePanel = $('[data-inspection-panel]').filter(function () { return !$(this).hasClass('hidden'); }).first();
        if (!$activePanel.length) return;

        const tableHtml = $activePanel.find('table').clone();
        // remove hidden rows
        tableHtml.find('tbody tr').filter(function () { return $(this).css('display') === 'none'; }).remove();

        const win = window.open('', '_blank');
        if (!win) return;
        win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Inspection Report</title>
            <style>body{font-family:Segoe UI, Tahoma, sans-serif;padding:20px;color:#111}table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:8px;font-size:12px}</style>
            </head><body><h2>Inspection Items</h2>` + tableHtml.prop('outerHTML') + `</body></html>`);
        win.document.close();
        setTimeout(() => { win.print(); win.close(); }, 300);
    });
};

const initInspectionModal = () => {
    const $modal = $('#inspectionModal');
    if (!$modal.length) {
        return;
    }

    const $form = $('#inspectionForm');
    const $itemsContainer = $('#inspectionItems');
    const $loader = $('#inspectionModalLoader');
    const $errorBox = $('#inspectionModalErrors');

    const state = {
        storeUrl: null,
        statuses: [],
        statusCodeById: {},
    };

    const statusCodePriority = {
        'IT-01': 1,
        'IT-02': 2,
        'IT-06': 3,
        'IT-03': 4,
        'IT-04': 5,
        'IT-05': 6,
    };

    const statusesRequiringRemarks = new Set(['IT-03', 'IT-04', 'IT-05']);
    const quantityLockedStatuses = new Set(['IT-06']);

    const syncStatuses = (statuses = []) => {
        state.statuses = [...statuses].sort((a, b) => {
            const aPriority = statusCodePriority[a.status_code] ?? 999;
            const bPriority = statusCodePriority[b.status_code] ?? 999;
            if (aPriority === bPriority) {
                return (a.status_name || '').localeCompare(b.status_name || '');
            }
            return aPriority - bPriority;
        });

        state.statusCodeById = {};
        state.statuses.forEach((status) => {
            state.statusCodeById[String(status.status_id)] = status.status_code || null;
        });
    };

    const getStatusIdByCode = (code) => {
        if (!code) {
            return '';
        }

        const match = state.statuses.find((status) => status.status_code === code);
        return match ? String(match.status_id) : '';
    };

    const getStatusCodeById = (statusId) => state.statusCodeById[String(statusId ?? '')] || null;

    const setLoading = (loading) => {
        if (loading) {
            $loader.removeClass('hidden').addClass('flex');
            $form.addClass('pointer-events-none opacity-50');
        } else {
            $loader.addClass('hidden').removeClass('flex');
            $form.removeClass('pointer-events-none opacity-50');
        }
    };

    const toggleModal = (open) => {
        if (open) {
            $modal.removeClass('hidden opacity-0');
            document.body.classList.add('overflow-hidden');
        } else {
            setLoading(false);
            $modal.addClass('opacity-0');
            setTimeout(() => $modal.addClass('hidden'), 200);
            document.body.classList.remove('overflow-hidden');
            $form.trigger('reset');
            $itemsContainer.empty();
            $errorBox.addClass('hidden').empty();
            state.storeUrl = null;
            syncStatuses([]);
            // Ensure form inputs are enabled again and submit button visible for next open
            $form.find('input, select, textarea').prop('disabled', false).removeClass('opacity-60');
            $form.find('button[type="submit"]').show();
        }
    };

    const buildStatusOptions = (selectedId) => {
        const selectedString = selectedId != null ? String(selectedId) : '';
        const defaultOption = `<option value="" disabled ${selectedString ? '' : 'selected'}>Select status</option>`;
        if (!state.statuses.length) {
            return defaultOption;
        }

        return [
            defaultOption,
            ...state.statuses.map((status) => `
                <option value="${status.status_id}" ${selectedString && selectedString === String(status.status_id) ? 'selected' : ''}>
                    ${escapeHtml(status.status_name)}
                </option>
            `),
        ].join('');
    };

    const renderItems = (items = []) => {
        if (!items.length) {
            $itemsContainer.html(`
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">
                        No line items found for this purchase order.
                    </td>
                </tr>
            `);
            return;
        }

        const defaultAcceptedStatusId = getStatusIdByCode('IT-02');

        const rows = items.map((item, index) => {
            const delivered = Number(item.quantity ?? 0);
            const acceptedRaw = Number(item.quantity_accepted ?? 0);
            const rejectedRaw = Number(item.quantity_rejected ?? 0);
            let accepted = acceptedRaw;
            let rejected = rejectedRaw;

            if (accepted === 0 && rejected === 0) {
                accepted = delivered;
            }

            if (accepted + rejected > delivered) {
                accepted = Math.max(0, delivered - rejected);
            }

            const remarks = escapeHtml(item.remarks ?? '');
            const description = escapeHtml(item.item_description ?? '');
            const unit = escapeHtml(item.unit ?? '');
            const warranty = item.warranty_expiration ?? '';
            const statusId = item.status_id != null ? String(item.status_id) : defaultAcceptedStatusId;

            return `
                <tr data-index="${index}" data-delivered="${delivered}" class="bg-white">
                    <td class="px-3 py-3 align-top text-gray-700">
                        <input type="hidden" name="items[${index}][po_item_id]" value="${item.po_item_id}">
                        <div class="font-semibold">${description}</div>
                        <div class="text-xs text-gray-500">Unit: ${unit}</div>
                    </td>
                    <td class="px-3 py-3 text-right font-semibold text-gray-700">${delivered}</td>
                    <td class="px-3 py-3">
                        <input type="number" min="0" max="${delivered}" step="1"
                               name="items[${index}][quantity_accepted]"
                               value="${accepted}"
                               data-field="accepted"
                               class="w-20 rounded-lg border px-2 py-1 text-right text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]">
                    </td>
                    <td class="px-3 py-3">
                        <input type="number" min="0" max="${delivered}" step="1"
                               name="items[${index}][quantity_rejected]"
                               value="${rejected}"
                               data-field="rejected"
                               class="w-20 rounded-lg border px-2 py-1 text-right text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]">
                    </td>
                    <td class="px-3 py-3">
                        <select name="items[${index}][status_id]"
                                class="w-40 rounded-lg border px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]">
                            ${buildStatusOptions(statusId)}
                        </select>
                    </td>
                    <td class="px-3 py-3">
                        <input type="date" name="items[${index}][warranty_expiration]"
                               value="${warranty}"
                               class="w-40 rounded-lg border px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]">
                    </td>
                    <td class="px-3 py-3">
                        <textarea name="items[${index}][remarks]" rows="2"
                                  class="w-full rounded-lg border px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a3a2d]"
                                  data-default-placeholder="Add remarks"
                                  placeholder="Add remarks">${remarks}</textarea>
                    </td>
                </tr>
            `;
        });

        $itemsContainer.html(rows.join(''));
        $itemsContainer.find('tr').each(function () {
            applyStatusBehaviour($(this));
        });
    };

    const clampQuantities = ($row, changedField) => {
        const delivered = Number($row.data('delivered') ?? 0);
        let accepted = Number($row.find('[data-field="accepted"]').val() ?? 0);
        let rejected = Number($row.find('[data-field="rejected"]').val() ?? 0);

        accepted = Number.isNaN(accepted) ? 0 : accepted;
        rejected = Number.isNaN(rejected) ? 0 : rejected;

        accepted = Math.max(0, accepted);
        rejected = Math.max(0, rejected);

        if (accepted + rejected > delivered) {
            if (changedField === 'accepted') {
                accepted = Math.max(0, delivered - rejected);
            } else {
                rejected = Math.max(0, delivered - accepted);
            }
        }

        if (accepted > delivered) {
            accepted = delivered;
        }

        if (rejected > delivered) {
            rejected = delivered;
        }

        $row.find('[data-field="accepted"]').val(accepted);
        $row.find('[data-field="rejected"]').val(rejected);
    };

    const applyStatusBehaviour = ($row) => {
        const statusId = $row.find('select[name$="[status_id]"]').val();
        const statusCode = getStatusCodeById(statusId);
        const delivered = Number($row.data('delivered') ?? 0);
        const $accepted = $row.find('[data-field="accepted"]');
        const $rejected = $row.find('[data-field="rejected"]');
        const $remarks = $row.find('textarea[name$="[remarks]"]');
        const defaultPlaceholder = $remarks.data('defaultPlaceholder') || 'Add remarks';

        if (statusCode === 'IT-06') {
            $accepted.val(delivered);
            $rejected.val(0);
        }

        const lockQuantities = quantityLockedStatuses.has(statusCode);
        $accepted.prop('readonly', lockQuantities).toggleClass('bg-gray-100', lockQuantities);
        $rejected.prop('readonly', lockQuantities).toggleClass('bg-gray-100', lockQuantities);

        clampQuantities($row, lockQuantities ? 'accepted' : undefined);

        if (statusCode && statusesRequiringRemarks.has(statusCode)) {
            $remarks.attr('placeholder', 'Provide inspection notes for this status')
                .addClass('border-rose-400 focus:ring-rose-200 focus:border-rose-400');
        } else {
            $remarks.attr('placeholder', defaultPlaceholder)
                .removeClass('border-rose-400 focus:ring-rose-200 focus:border-rose-400');
        }
    };

    $itemsContainer.on('input', '[data-field="accepted"], [data-field="rejected"]', function () {
        const $row = $(this).closest('tr');
        clampQuantities($row, $(this).data('field'));
    });

    $itemsContainer.on('change', 'select[name$="[status_id]"]', function () {
        applyStatusBehaviour($(this).closest('tr'));
    });

    const populateForm = (payload) => {
        const { po = {}, report = null, items = [] } = payload;

        $('#inspectionPoNumber').text(po.po_no ?? '—');
        $('#inspectionPrNumber').text(po.pr_no ?? '—');
        $('#inspectionSupplier').text(po.supplier ?? '—');
        $('#inspectionOrderDate').text(po.order_date ?? '—');
        $('#inspectionDeliveryDate').text(po.delivery_date ?? '—');
        $('#inspectionIaNumber').text(report?.ia_no ?? 'Pending');

        const inspectionDate = report?.inspection_date ?? po.delivery_date ?? todayIso();
        $('#inspectionDate').val(inspectionDate ?? '');
        $('#acceptedDate').val(report?.accepted_date ?? '');
        $('#invoiceNo').val(report?.invoice_no ?? '');
        $('#invoiceDate').val(report?.invoice_date ?? '');
        $('#inspectionRemarks').val(report?.remarks ?? '');

        renderItems(items);
    };

    const handleOpen = ($trigger) => {
        const formUrl = $trigger.data('form-url');
        const storeUrl = $trigger.data('store-url') || null; // storeUrl may be absent for view-only

        if (!formUrl) {
            alert('Inspection endpoint is not configured for this record.');
            return;
        }

        state.storeUrl = storeUrl;
        $errorBox.addClass('hidden').empty();
        setLoading(true);
        toggleModal(true);

        $.ajax({
            method: 'GET',
            url: formUrl,
            headers: { Accept: 'application/json' },
            success: (response) => {
                const data = response.data ?? {};
                syncStatuses(data.statuses ?? []);
                populateForm(data);
                // If opened without a storeUrl, make the form read-only (view-only)
                if (!state.storeUrl) {
                    $form.find('button[type="submit"]').hide();
                    $form.find('input, select, textarea').prop('disabled', true).addClass('opacity-60');
                } else {
                    $form.find('button[type="submit"]').show();
                    $form.find('input, select, textarea').prop('disabled', false).removeClass('opacity-60');
                }
            },
            error: (xhr) => {
                toggleModal(false);
                const message = xhr.responseJSON?.message || 'Unable to load inspection data. Please try again.';
                alert(message);
            },
            complete: () => {
                setLoading(false);
            },
        });
    };

    $(document).on('click', '.js-open-inspection', function () {
        handleOpen($(this));
    });

    $(document).on('click', '[data-close-modal]', () => toggleModal(false));

    $modal.on('click', (event) => {
        if (event.target.dataset.closeModal !== undefined) {
            toggleModal(false);
        }
    });

    $(document).on('keydown', (event) => {
        if (event.key === 'Escape' && !$modal.hasClass('hidden')) {
            toggleModal(false);
        }
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if (!state.storeUrl) {
            alert('Unable to save inspection without a valid endpoint.');
            return;
        }

        $errorBox.addClass('hidden').empty();
        const $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed');

        $.ajax({
            method: 'POST',
            url: state.storeUrl,
            data: $form.serialize(),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                Accept: 'application/json',
            },
            success: (response) => {
                toggleModal(false);
                alert(response.message ?? 'Inspection results saved.');
                window.location.reload();
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $errorBox.html(messages.map((msg) => `<div>${escapeHtml(msg)}</div>`).join(''));
                    $errorBox.removeClass('hidden');
                } else {
                    $errorBox.text('An unexpected error occurred while saving inspection results.');
                    $errorBox.removeClass('hidden');
                }
            },
            complete: () => {
                $submitBtn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
            },
        });
    });
};

$(() => {
    initInspectionTabs();
    initInspectionModal();
    // prune pending panel of already accepted/recorded rows and initialize filters
    prunePendingAccepted();
    initInspectionFilters();
});
