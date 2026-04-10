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
            fallback.className = 'pointer-events-none fixed top-20 right-4 sm:right-6 z-[100] hidden min-w-[250px] max-w-md rounded-xl border border-[#2d5a4a] bg-[#1a3a2d] px-5 py-4 text-sm font-semibold text-white shadow-2xl opacity-0 transition-all duration-300';
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

export const openConfirmModal = ({
    title = 'Confirm Action',
    message = 'Are you sure you want to continue?',
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    tone = 'warning',
} = {}) => {
    const modalId = 'pqs-shared-confirm-modal';
    let modal = document.getElementById(modalId);

    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'fixed inset-0 z-[120] hidden opacity-0 transition-opacity duration-300';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.innerHTML = `
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-confirm-close></div>
            <div class="relative flex min-h-screen items-center justify-center p-4">
                <div class="confirm-panel w-full max-w-md rounded-2xl border border-emerald-950/10 bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
                    <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4">
                        <div id="pqsConfirmIcon" class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                            <i class="fas fa-circle-exclamation"></i>
                        </div>
                        <div>
                            <h4 id="pqsConfirmTitle" class="text-base font-bold text-[#1a3a2d]">Confirm Action</h4>
                            <p class="text-xs text-gray-500">Please review before proceeding.</p>
                        </div>
                    </div>
                    <div class="px-5 py-4">
                        <p id="pqsConfirmMessage" class="text-sm text-gray-700">Are you sure you want to continue?</p>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 bg-[#fbfcfb] px-5 py-4">
                        <button type="button" id="pqsConfirmNo" class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" data-confirm-close>
                            Cancel
                        </button>
                        <button type="button" id="pqsConfirmYes" class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    }

    const panel = modal.querySelector('.confirm-panel');
    const icon = modal.querySelector('#pqsConfirmIcon');
    const titleNode = modal.querySelector('#pqsConfirmTitle');
    const messageNode = modal.querySelector('#pqsConfirmMessage');
    const confirmButton = modal.querySelector('#pqsConfirmYes');
    const cancelButton = modal.querySelector('#pqsConfirmNo');
    const closeTargets = modal.querySelectorAll('[data-confirm-close]');
    const previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;

    if (titleNode) {
        titleNode.textContent = title;
    }

    if (messageNode) {
        messageNode.textContent = message;
    }

    if (confirmButton) {
        confirmButton.textContent = confirmLabel;
    }

    if (cancelButton) {
        cancelButton.textContent = cancelLabel;
    }

    if (icon && confirmButton) {
        icon.className = 'flex h-10 w-10 items-center justify-center rounded-full';
        confirmButton.className = 'rounded-xl px-4 py-2 text-sm font-semibold text-white transition';

        if (tone === 'success') {
            icon.classList.add('bg-emerald-100', 'text-emerald-700');
            icon.innerHTML = '<i class="fas fa-check-circle"></i>';
            confirmButton.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
        } else if (tone === 'danger') {
            icon.classList.add('bg-rose-100', 'text-rose-700');
            icon.innerHTML = '<i class="fas fa-triangle-exclamation"></i>';
            confirmButton.classList.add('bg-rose-600', 'hover:bg-rose-700');
        } else {
            icon.classList.add('bg-amber-100', 'text-amber-700');
            icon.innerHTML = '<i class="fas fa-circle-exclamation"></i>';
            confirmButton.classList.add('bg-amber-500', 'hover:bg-amber-600');
        }
    }

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            panel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
            confirmButton?.focus();
        });
    });

    return new Promise((resolve) => {
        let settled = false;

        const cleanup = () => {
            closeTargets.forEach((target) => target.removeEventListener('click', onCancel));
            confirmButton?.removeEventListener('click', onConfirm);
            document.removeEventListener('keydown', onKeydown);
        };

        const settle = (result) => {
            if (settled) {
                return;
            }

            settled = true;
            cleanup();
            modal.classList.add('opacity-0');
            panel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');

            window.setTimeout(() => {
                modal.classList.add('hidden');
                if (previousFocus) {
                    previousFocus.focus();
                }
                resolve(result);
            }, 220);
        };

        const onCancel = () => settle(false);
        const onConfirm = () => settle(true);
        const onKeydown = (event) => {
            if (event.key === 'Escape') {
                onCancel();
            }
        };

        closeTargets.forEach((target) => target.addEventListener('click', onCancel));
        confirmButton?.addEventListener('click', onConfirm);
        document.addEventListener('keydown', onKeydown);
    });
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
