import { csrfToken, openConfirmModal, showToast } from './purchase-requests-shared.js';

const pageSelector = '#prReviewPage';
const toastSelector = '#prReviewToast';

const normalize = (value = '') => String(value).toLowerCase().trim();

const statusClassMap = [
    { match: ['approved', 'completed'], classes: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    { match: ['cancelled', 'declined'], classes: 'bg-rose-50 text-rose-700 border-rose-200' },
    { match: ['pending', 'for approval', 'recommended', 'for recommendation'], classes: 'bg-amber-50 text-amber-700 border-amber-200' },
];

const getStatusBadgeClasses = (statusName = '') => {
    const normalized = normalize(statusName);
    const found = statusClassMap.find(({ match }) => match.includes(normalized));
    return found ? found.classes : 'bg-gray-100 text-gray-700 border-gray-200';
};

const renderBackLink = (backUrl, label) => `
    <a href="${backUrl}" class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-500 hover:text-[#1a3a2d] font-medium">
        <i class="fas fa-arrow-left"></i> ${label}
    </a>
`;

const renderDivisionHeadActions = (page, statusId) => {
    const backUrl = page.dataset.prReviewBackUrl || '#';
    const recommendUrl = page.dataset.prReviewRecommendUrl || '#';
    const cancelUrl = page.dataset.prReviewCancelUrl || '#';
    const prToken = csrfToken() || '';

    if (Number(statusId) === 102) {
        return `
            <div data-pr-review-actions class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-4">Division Head Actions</h3>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <form method="POST" action="${recommendUrl}" data-pr-review-form data-pr-review-action="recommend" data-confirm-message="Recommend this purchase request to BAC?">
                        <input type="hidden" name="_token" value="${prToken}">
                        <button type="submit" data-loading-text="Submitting recommendation..." class="px-3 sm:px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold text-xs sm:text-sm hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-thumbs-up mr-1"></i> Recommend
                        </button>
                    </form>

                    <form method="POST" action="${cancelUrl}" data-pr-review-form data-pr-review-action="cancel" data-confirm-message="Cancel this purchase request? This action cannot be undone.">
                        <input type="hidden" name="_token" value="${prToken}">
                        <input type="hidden" name="remarks" value="Cancelled by Division Head">
                        <button type="submit" data-loading-text="Canceling request..." class="px-3 sm:px-4 py-2 rounded-lg bg-rose-600 text-white font-semibold text-xs sm:text-sm hover:bg-rose-700 transition-colors">
                            <i class="fas fa-ban mr-1"></i> Cancel
                        </button>
                    </form>

                    ${renderBackLink(backUrl, 'Back to List')}
                </div>
            </div>
        `;
    }

    return `
        <div data-pr-review-actions class="bg-white rounded-2xl shadow-lg p-4 sm:p-6">
            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                <p class="font-semibold text-gray-900">This request is read-only.</p>
                <p class="mt-1 text-xs text-gray-600">No further actions are available for this request status.</p>
            </div>
            <div class="mt-3">
                ${renderBackLink(backUrl, 'Back to List')}
            </div>
        </div>
    `;
};

const renderBacActions = (page, statusId) => {
    const backUrl = page.dataset.prReviewBackUrl || '#';
    const reviewUrl = page.dataset.prReviewReviewUrl || '#';
    const approveUrl = page.dataset.prReviewApproveUrl || '#';
    const cancelUrl = page.dataset.prReviewCancelUrl || '#';
    const prToken = csrfToken() || '';
    const numericStatusId = Number(statusId);

    if (numericStatusId === 103) {
        return `
            <div data-pr-review-actions class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-[0_20px_45px_-35px_rgba(146,64,14,0.35)]">
                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-amber-900">BAC Actions</h3>
                <p class="mt-1 text-xs text-amber-800">Use actions below to progress or close this request.</p>
                <div class="mt-4 space-y-2">
                    <form method="POST" action="${reviewUrl}" data-pr-review-form data-pr-review-action="review" data-confirm-message="Move this purchase request to BAC review?">
                        <input type="hidden" name="_token" value="${prToken}">
                        <button type="submit" data-loading-text="Moving to review..." class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
                            <i class="fas fa-clipboard-check"></i>
                            Move to Review
                        </button>
                    </form>
                    <a href="${backUrl}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        <i class="fas fa-arrow-left"></i>
                        Back to Queue
                    </a>
                </div>
            </div>
        `;
    }

    if (numericStatusId === 104) {
        return `
            <div data-pr-review-actions class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-[0_20px_45px_-35px_rgba(146,64,14,0.35)]">
                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-amber-900">BAC Actions</h3>
                <p class="mt-1 text-xs text-amber-800">Use actions below to progress or close this request.</p>
                <div class="mt-4 space-y-2">
                    <form method="POST" action="${approveUrl}" data-pr-review-form data-pr-review-action="approve" data-confirm-message="Mark as Approved? This action is final.">
                        <input type="hidden" name="_token" value="${prToken}">
                        <button type="submit" data-loading-text="Approving..." class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            <i class="fas fa-check-circle"></i>
                            Approve
                        </button>
                    </form>
                    <form method="POST" action="${cancelUrl}" data-pr-review-form data-pr-review-action="cancel" data-confirm-message="Cancel this purchase request?">
                        <input type="hidden" name="_token" value="${prToken}">
                        <input type="hidden" name="remarks" value="Cancelled by BAC">
                        <button type="submit" data-loading-text="Canceling request..." class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                            <i class="fas fa-times-circle"></i>
                            Cancel Request
                        </button>
                    </form>
                    <a href="${backUrl}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        <i class="fas fa-arrow-left"></i>
                        Back to Queue
                    </a>
                </div>
            </div>
        `;
    }

    return `
        <div data-pr-review-actions class="rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_20px_45px_-35px_rgba(15,23,42,0.7)]">
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                <p class="font-semibold text-gray-900">This request is read-only.</p>
                <p class="mt-1 text-xs text-gray-600">No further actions are available for this request status.</p>
            </div>
            <div class="mt-4">
                ${renderBackLink(backUrl, 'Back to Queue')}
            </div>
        </div>
    `;
};

const updateStatusBadge = (page, statusName) => {
    const badge = page.querySelector('[data-pr-review-status-badge]');
    if (!badge) {
        return;
    }

    const normalizedStatus = normalize(statusName);
    badge.className = `inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold capitalize border ${getStatusBadgeClasses(normalizedStatus)}`;
    badge.innerHTML = `<i class="fas fa-circle text-[8px]"></i>${statusName || 'Unknown'}`;
    page.dataset.prReviewStatus = statusName || 'Unknown';
};

const updateActionPanel = (page, statusId) => {
    const actions = page.querySelector('[data-pr-review-actions]');
    if (!actions) {
        return;
    }

    const role = page.dataset.prReviewRole || '';
    actions.outerHTML = role === 'division_head'
        ? renderDivisionHeadActions(page, statusId)
        : renderBacActions(page, statusId);
};

const initReviewPage = () => {
    const page = document.querySelector(pageSelector);
    if (!page) {
        return;
    }

    const badgeText = page.querySelector('[data-pr-review-status-badge]')?.textContent?.trim() || 'Unknown';
    page.dataset.prReviewStatus = badgeText;

    document.addEventListener('submit', async (event) => {
        const form = event.target instanceof HTMLFormElement ? event.target : null;
        if (!form || !form.matches('[data-pr-review-form]')) {
            return;
        }

        event.preventDefault();

        const confirmMessage = form.dataset.confirmMessage;
        if (confirmMessage) {
            const action = form.dataset.prReviewAction || '';
            const titleMap = {
                recommend: 'Recommend Purchase Request',
                review: 'Move to For Approval',
                approve: 'Approve Purchase Request',
                cancel: 'Cancel Purchase Request',
            };
            const confirmLabelMap = {
                recommend: 'Recommend',
                review: 'Move to For Approval',
                approve: 'Approve',
                cancel: 'Cancel Request',
            };
            const toneMap = {
                recommend: 'success',
                review: 'warning',
                approve: 'success',
                cancel: 'danger',
            };

            const confirmed = await openConfirmModal({
                title: titleMap[action] || 'Confirm Action',
                message: confirmMessage,
                confirmLabel: confirmLabelMap[action] || 'Confirm',
                tone: toneMap[action] || 'warning',
            });

            if (!confirmed) {
                return;
            }
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const originalHtml = submitButton?.innerHTML || '';
        const loadingText = submitButton?.dataset.loadingText || 'Saving...';

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-70', 'cursor-not-allowed');
            submitButton.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken() || '',
                },
                body: new FormData(form),
            });

            const result = await response.json();

            if (!response.ok || !result?.success) {
                const message = result?.message || Object.values(result?.errors || {}).flat().join(' ') || 'Unable to complete this action.';
                showToast(toastSelector, message);
                return;
            }

            const data = result.data || {};
            updateStatusBadge(page, data.status || 'Unknown');
            updateActionPanel(page, data.status_id);
            showToast(toastSelector, result.message || 'Action completed successfully.');
        } catch (error) {
            console.error('Failed to submit review action:', error);
            showToast(toastSelector, 'Failed to complete this action.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-70', 'cursor-not-allowed');
                submitButton.innerHTML = originalHtml;
            }
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initReviewPage, { once: true });
} else {
    initReviewPage();
}
