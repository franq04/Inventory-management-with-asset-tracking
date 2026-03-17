import $ from 'jquery';

const csrfToken = () => $('meta[name="csrf-token"]').attr('content');

const todayIso = () => new Date().toISOString().slice(0, 10);

const escapeHtml = (value) => $('<div>').text(value ?? '').html();

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
    toast.className = 'fixed right-6 top-24 z-[90] hidden max-w-sm rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg';
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

const inspectionRootSelector = '#inspectionAcceptancePage';
const tabQueryKey = 'tab';

const getActiveInspectionTabFromUrl = () => {
    const params = new URLSearchParams(window.location.search);
    const knownTabs = ['pending', 'accepted', 'defective', 'returned'];
    const tabFromQuery = String(params.get(tabQueryKey) || '').toLowerCase();
    if (knownTabs.includes(tabFromQuery)) {
        return tabFromQuery;
    }
    const matched = knownTabs.find((key) => params.has(`${key}_page`));
    return matched || 'pending';
};

const buildTabUrl = (tabKey) => {
    const nextUrl = new URL(window.location.href);
    nextUrl.searchParams.set(tabQueryKey, tabKey);
    ['pending_page', 'accepted_page', 'defective_page', 'returned_page', 'all_page', 'page'].forEach((key) => {
        nextUrl.searchParams.delete(key);
    });
    return nextUrl.toString();
};

const activateInspectionTab = (tabKey, options = {}) => {
    const { syncUrl = false, replaceState = false } = options;
    const $root = $(inspectionRootSelector);
    if (!$root.length) {
        return;
    }

    const $tabs = $root.find('[data-inspection-tab]');
    const $panels = $root.find('[data-inspection-panel]');

    if (!$tabs.length || !$panels.length) {
        return;
    }

    $tabs.each(function () {
        const $tab = $(this);
        const isActive = String($tab.data('inspection-tab')) === String(tabKey);
        const activeClass = String($tab.data('activeClass') || '').trim();
        const inactiveClass = String($tab.data('inactiveClass') || '').trim();
        const activeCountClass = String($tab.data('countActiveClass') || '').trim();
        const inactiveCountClass = String($tab.data('countInactiveClass') || '').trim();
        const $count = $tab.find('.inspection-tab-count');

        $tab.removeClass('is-selected');
        if (activeClass) {
            $tab.removeClass(activeClass);
        }
        if (inactiveClass) {
            $tab.removeClass(inactiveClass);
        }

        if ($count.length) {
            if (activeCountClass) {
                $count.removeClass(activeCountClass);
            }
            if (inactiveCountClass) {
                $count.removeClass(inactiveCountClass);
            }
        }

        if (isActive) {
            $tab.addClass('is-selected');
            if (activeClass) {
                $tab.addClass(activeClass);
            }
            if ($count.length && activeCountClass) {
                $count.addClass(activeCountClass);
            }
        } else {
            if (inactiveClass) {
                $tab.addClass(inactiveClass);
            }
            if ($count.length && inactiveCountClass) {
                $count.addClass(inactiveCountClass);
            }
        }
    });

    $panels.addClass('hidden');
    $panels.filter(`[data-inspection-panel="${tabKey}"]`).removeClass('hidden');

    $root.find('[data-active-status-label]').text($tabs.filter(`[data-inspection-tab="${tabKey}"]`).first().find('span').first().text() || 'Pending Inspection');

    if (syncUrl) {
        const nextUrl = buildTabUrl(tabKey);
        if (window.location.href !== nextUrl) {
            if (replaceState) {
                window.history.replaceState({ inspectionTab: tabKey }, '', nextUrl);
            } else {
                window.history.pushState({ inspectionTab: tabKey }, '', nextUrl);
            }
        }
    }
};

const initInspectionTabs = () => {
    const $root = $(inspectionRootSelector);
    if (!$root.length) {
        return;
    }

    $(document)
        .off('click.inspectionTabs', `${inspectionRootSelector} [data-inspection-tab]`)
        .on('click.inspectionTabs', `${inspectionRootSelector} [data-inspection-tab]`, function () {
            activateInspectionTab($(this).data('inspection-tab'), { syncUrl: true });
        });

    activateInspectionTab(getActiveInspectionTabFromUrl(), { syncUrl: true, replaceState: true });
};

const prunePendingAccepted = () => {
    const $panel = $(`${inspectionRootSelector} [data-inspection-panel="pending"]`);
    if (!$panel.length) {
        return;
    }

    $panel.find('tbody tr').each(function () {
        const $tr = $(this);
        const $cells = $tr.find('td');
        if ($cells.length < 6) {
            return;
        }

        const deliveredText = $cells.eq(4).text().trim();
        const acceptedText = $cells.eq(5).text().trim();
        const delivered = Number(deliveredText.replace(/[^0-9.-]+/g, '')) || 0;
        const accepted = Number(acceptedText.replace(/[^0-9.-]+/g, '')) || 0;

        if (accepted > 0 && accepted >= delivered) {
            $tr.remove();
        }
    });
};

const initInspectionFilters = () => {
    const $root = $(inspectionRootSelector);
    if (!$root.length) {
        return;
    }

    const normalize = (s) => String(s ?? '').toLowerCase();

    const applyFiltersToPanel = ($panel, term, fromDate, toDate) => {
        $panel.find('tbody tr').each(function () {
            const $tr = $(this);
            const $cells = $tr.find('td');
            if ($cells.length < 8) {
                return;
            }

            const text = normalize($tr.text());
            let visible = true;

            if (term && !text.includes(term)) {
                visible = false;
            }

            if (visible && (fromDate || toDate)) {
                const dateCell = $cells.eq(7).text().trim();
                let parsedDate = null;
                const isoMatch = dateCell.match(/(\d{4}-\d{2}-\d{2})/);
                if (isoMatch) {
                    parsedDate = new Date(isoMatch[1]);
                }

                if (!parsedDate) {
                    const altMatch = dateCell.match(/([A-Za-z]{3,9}\s+\d{1,2},\s*\d{4})/);
                    if (altMatch) {
                        parsedDate = new Date(altMatch[1]);
                    }
                }

                if (parsedDate) {
                    if (fromDate && parsedDate < fromDate) {
                        visible = false;
                    }
                    if (toDate && parsedDate > toDate) {
                        visible = false;
                    }
                }
            }

            $tr.toggle(visible);
        });
    };

    const runFilters = () => {
        const term = normalize($root.find('#inspectionSearch').val());
        const fromValue = $root.find('#inspectionDateFrom').val();
        const toValue = $root.find('#inspectionDateTo').val();
        const fromDate = fromValue ? new Date(fromValue) : null;
        const toDate = toValue ? new Date(toValue) : null;

        $root.find('[data-inspection-panel]').each(function () {
            applyFiltersToPanel($(this), term, fromDate, toDate);
        });
    };

    $(document)
        .off('input.inspectionSearch', `${inspectionRootSelector} #inspectionSearch`)
        .on('input.inspectionSearch', `${inspectionRootSelector} #inspectionSearch`, runFilters);

    $(document)
        .off('click.inspectionDate', `${inspectionRootSelector} #inspectionApplyDateFilter`)
        .on('click.inspectionDate', `${inspectionRootSelector} #inspectionApplyDateFilter`, runFilters);

    $(document)
        .off('keydown.inspectionDateFrom', `${inspectionRootSelector} #inspectionDateFrom`)
        .on('keydown.inspectionDateFrom', `${inspectionRootSelector} #inspectionDateFrom`, (event) => {
            if (event.key === 'Enter') {
                runFilters();
            }
        });

    $(document)
        .off('keydown.inspectionDateTo', `${inspectionRootSelector} #inspectionDateTo`)
        .on('keydown.inspectionDateTo', `${inspectionRootSelector} #inspectionDateTo`, (event) => {
            if (event.key === 'Enter') {
                runFilters();
            }
        });

    runFilters();

    $('#inspectionPrintPdfBtn').off('click.inspectionPrintPdf').on('click.inspectionPrintPdf', () => {
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

const bindInspectionPagination = () => {
    const $root = $(inspectionRootSelector);
    if (!$root.length) {
        return;
    }

    let activeRequest = null;
    let requestToken = 0;
    let isLoading = false;

    const setLoadingState = (loading) => {
        const $currentRoot = $(inspectionRootSelector);
        if (!$currentRoot.length) {
            return;
        }

        $currentRoot.toggleClass('opacity-70 translate-y-1 pointer-events-none', loading);
        $currentRoot.find('nav[aria-label="Pagination Navigation"] a').toggleClass('pointer-events-none', loading);
    };

    const hydrate = () => {
        initInspectionTabs();
        prunePendingAccepted();
        initInspectionFilters();
    };

    const revealRows = ($scope) => {
        if (!$scope || !$scope.length) {
            return;
        }

        const $visibleRows = $scope
            .find('[data-inspection-panel]:not(.hidden) tbody tr')
            .filter(function () {
                return !$(this).hasClass('js-reveal-animated');
            });

        $visibleRows.each(function (index) {
            const row = this;
            const delay = Math.min(index * 35, 280);

            row.classList.add('js-reveal-animated');
            row.style.opacity = '0';
            row.style.transform = 'translateY(6px)';
            row.style.transition = `opacity 220ms ease ${delay}ms, transform 260ms ease ${delay}ms`;

            requestAnimationFrame(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            });
        });
    };

    const swapSection = (html, url, pushState = true) => {
        const $parsed = $('<div>').append($.parseHTML(html, document, true));
        const $incoming = $parsed.find(inspectionRootSelector).first();
        const $current = $(inspectionRootSelector).first();

        if (!$incoming.length || !$current.length) {
            return;
        }

        const searchValue = String($current.find('#inspectionSearch').val() || '');
        const fromValue = String($current.find('#inspectionDateFrom').val() || '');
        const toValue = String($current.find('#inspectionDateTo').val() || '');

        $incoming.addClass('opacity-0 translate-y-1');
        $current.replaceWith($incoming);

        if (pushState && window.location.href !== url) {
            window.history.pushState({ inspectionPageUrl: url }, '', url);
        }

        const $newRoot = $(inspectionRootSelector).first();
        $newRoot.find('#inspectionSearch').val(searchValue);
        $newRoot.find('#inspectionDateFrom').val(fromValue);
        $newRoot.find('#inspectionDateTo').val(toValue);

        hydrate();
        revealRows($newRoot);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                $newRoot.removeClass('opacity-0 translate-y-1');
            });
        });
    };

    const loadInspectionPage = (url, options = {}) => {
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

    $(document)
        .off('click.inspectionPagination', `${inspectionRootSelector} nav[aria-label="Pagination Navigation"] a[href]`)
        .on('click.inspectionPagination', `${inspectionRootSelector} nav[aria-label="Pagination Navigation"] a[href]`, function (event) {
            event.preventDefault();
            event.stopPropagation();
            const url = this.getAttribute('href');
            if (!url || isLoading) {
                return;
            }

            loadInspectionPage(url, { pushState: true });
        });

    if (!window.__inspectionPaginationPopstateBound) {
        window.addEventListener('popstate', () => {
            if (!$(inspectionRootSelector).length || isLoading) {
                return;
            }
            loadInspectionPage(window.location.href, { pushState: false });
        });

        window.__inspectionPaginationPopstateBound = true;
    }

    revealRows($root);
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
            showToast('Inspection endpoint is not configured for this record.');
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
                showToast(message);
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
            showToast('Unable to save inspection without a valid endpoint.');
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
                showToast(response.message ?? 'Inspection results saved.', 'success');
                softNavigate(window.location.href, { replace: true });
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
    bindInspectionPagination();
});
