import $ from 'jquery';

export const csrfToken = () => $('meta[name="csrf-token"]').attr('content');

export const formatCurrency = (value) => {
    const number = Number(value ?? 0);
    return `₱${number.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

export const escapeHtml = (value = '') => String(value).replace(/[&<>"']/g, (char) => {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    };
    return map[char] ?? char;
});

export const parseDate = (value) => {
    if (!value) {
        return null;
    }
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
};

export const formatDateDisplay = (value) => {
    const date = parseDate(value);
    if (!date) {
        return '';
    }
    return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
};

export const isDatePast = (value) => {
    const date = parseDate(value);
    if (!date) {
        return false;
    }
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
};

export const itemStatusMeta = {
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

export const decisionMessages = {
    accept: 'You accepted the substitute item.',
    wait: 'You prefer to wait for the original item.',
};

export const getRequestStatuses = () => (window.employeePrConfig && window.employeePrConfig.statuses) ? window.employeePrConfig.statuses : {};

export const renderItemStatus = (item, requestStatusId) => {
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
        html += `<div class="mt-1 text-xs text-gray-600 italic">"${escapeHtml(item.employee_wait_note)}"</div>`;
    }

    return html;
};

export const renderItemActions = (item) => {
    if (!item) {
        return '<span class="text-gray-400">—</span>';
    }

    const statuses = getRequestStatuses();
    const requestStatusId = item.request_status_id != null ? String(item.request_status_id) : '';
    const declinedId = statuses.declined != null ? String(statuses.declined) : '';
    const forApprovalId = statuses.forApproval != null ? String(statuses.forApproval) : '';

    if (requestStatusId && declinedId && requestStatusId === declinedId) {
        return '<span class="text-xs font-semibold text-rose-700">Request cancelled by custodian.</span>';
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

export const updateRowAfterDecision = ($row, item, requestStatusId) => {
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

export const clearToastTimers = (toast) => {
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

export const hideToast = (selector) => {
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

export const showToast = (selector, message, isHtml = false) => {
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
export const toggleModal = ($modal, open = false) => {
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
