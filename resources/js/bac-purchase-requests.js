import $ from 'jquery';
import {
    hideToast,
    openConfirmModal as sharedOpenConfirmModal,
    showToast as sharedShowToast,
    toggleModal,
} from './purchase-requests-shared.js';

// BAC Purchase Request Review Modal Handler
const initBacPurchaseRequestPage = function () {
    'use strict';

    window.__bacPurchaseRequestsCleanup?.();

    const controller = new AbortController();
    const { signal } = controller;
    window.__bacPurchaseRequestsCleanup = () => controller.abort();

    const modal = document.getElementById('bacPrModal');
    const $modal = $('#bacPrModal');
    const toast = document.getElementById('bacPrToast');
    const toastSelector = toast ? '#bacPrToast' : '#prReviewToast';
    const alternativeModal = document.getElementById('bacAlternativeModal');
    const actionConfirmModal = document.getElementById('bacActionConfirmModal');
    const actionConfirmPanel = actionConfirmModal?.querySelector('.confirm-panel');
    const actionConfirmTitle = document.getElementById('bacActionConfirmTitle');
    const actionConfirmMessage = document.getElementById('bacActionConfirmMessage');
    const actionConfirmIcon = document.getElementById('bacActionConfirmIcon');
    const actionConfirmYes = document.getElementById('bacActionConfirmYes');

    if (!modal) {
        console.warn('BAC PR Modal not found');
        return;
    }

    let currentPrData = null;
    let actionConfirmResolver = null;
    let isPerformingAction = false;
    let isConfirmAnimating = false;
    let previousFocusedElement = null;

    const actionButtonConfig = {
        review: {
            elementId: 'bacBtnMoveToReview',
            loadingText: 'Moving to approval...',
        },
        approve: {
            elementId: 'bacBtnApprove',
            loadingText: 'Approving...',
        },
        cancel: {
            elementId: 'bacBtnCancel',
            loadingText: 'Canceling request...',
        },
    };

    const statusIdToTabKey = {
        103: 'recommended',
        104: 'for_approval',
        105: 'approved',
        106: 'cancelled',
    };

    const actionTargetStatusId = {
        review: 104,
        approve: 105,
        cancel: 106,
    };

    const queuePageSelector = '#bacQueuePage';
    let isQueueRequestInFlight = false;
    let activeQueueRequestController = null;
    let queueRequestToken = 0;

    function renderSignature(signatureDataUrl) {
        if (!signatureDataUrl) {
            return '';
        }

        return `<img src="${signatureDataUrl}" alt="Signature" class="max-h-10 w-auto object-contain">`;
    }

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

    function showToast(message, type = 'success') {
        if (!message) {
            return;
        }

        if (toast) {
            toast.classList.remove('border-[#2d5a4a]', 'bg-[#1a3a2d]', 'border-rose-700', 'bg-rose-700', 'border-amber-700', 'bg-amber-600');

            if (type === 'error') {
                toast.classList.add('border-rose-700', 'bg-rose-700');
            } else if (type === 'warning') {
                toast.classList.add('border-amber-700', 'bg-amber-600');
            } else {
                toast.classList.add('border-[#2d5a4a]', 'bg-[#1a3a2d]');
            }
        }

        sharedShowToast(toastSelector, message);
    }

    const resetActionConfirmModalState = () => {
        if (actionConfirmYes) {
            actionConfirmYes.disabled = false;
            actionConfirmYes.classList.remove('scale-95', 'ring-2', 'ring-offset-2', 'ring-emerald-200');
            actionConfirmYes.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'bg-rose-600', 'hover:bg-rose-700');
            actionConfirmYes.classList.add('bg-amber-500', 'hover:bg-amber-600');
            actionConfirmYes.innerHTML = 'Confirm';
        }

        if (actionConfirmIcon) {
            actionConfirmIcon.classList.remove('bg-emerald-100', 'text-emerald-700', 'bg-rose-100', 'text-rose-700');
            actionConfirmIcon.classList.add('bg-amber-100', 'text-amber-700');
            actionConfirmIcon.innerHTML = '<i class="fas fa-circle-exclamation"></i>';
        }
    };

    const closeActionConfirmModal = (confirmed = false) => {
        if (!actionConfirmModal) {
            return;
        }

        if (isConfirmAnimating && !confirmed) {
            return;
        }

        actionConfirmModal.classList.add('opacity-0');
        if (actionConfirmPanel) {
            actionConfirmPanel.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        }

        setTimeout(() => {
            actionConfirmModal.classList.add('hidden');
            if (previousFocusedElement instanceof HTMLElement) {
                previousFocusedElement.focus();
            }
            previousFocusedElement = null;
            if (!confirmed) {
                resetActionConfirmModalState();
            }
        }, 220);

        if (actionConfirmResolver) {
            actionConfirmResolver(confirmed);
            actionConfirmResolver = null;
        }

        isConfirmAnimating = false;
    };

    const animateConfirmSuccess = () => {
        if (!actionConfirmYes || isConfirmAnimating) {
            return Promise.resolve();
        }

        isConfirmAnimating = true;
        actionConfirmYes.disabled = true;
        actionConfirmYes.classList.add('scale-95');
        actionConfirmYes.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

        return new Promise((resolve) => {
            setTimeout(() => {
                actionConfirmYes.classList.remove('scale-95');
                actionConfirmYes.classList.add('ring-2', 'ring-offset-2', 'ring-emerald-200');
                actionConfirmYes.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmed';

                setTimeout(resolve, 170);
            }, 170);
        });
    };

    const openActionConfirmModal = ({ title, message, actionType, confirmLabel }) => {
        if (!actionConfirmModal) {
            return sharedOpenConfirmModal({
                title: title || 'Confirm Action',
                message: message || 'Are you sure you want to continue?',
                confirmLabel: confirmLabel || 'Confirm',
                tone: actionType === 'approve' ? 'success' : (actionType === 'cancel' ? 'danger' : 'warning'),
            });
        }

        return new Promise((resolve) => {
            actionConfirmResolver = resolve;

            if (actionConfirmTitle) {
                actionConfirmTitle.textContent = title || 'Confirm Action';
            }

            if (actionConfirmMessage) {
                actionConfirmMessage.textContent = message || 'Are you sure you want to continue?';
            }

            if (actionConfirmYes) {
                actionConfirmYes.textContent = confirmLabel || 'Confirm';
                actionConfirmYes.disabled = false;
                actionConfirmYes.classList.remove('bg-amber-500', 'hover:bg-amber-600', 'bg-emerald-600', 'hover:bg-emerald-700', 'bg-rose-600', 'hover:bg-rose-700', 'ring-2', 'ring-offset-2', 'ring-emerald-200', 'scale-95');

                if (actionType === 'approve') {
                    actionConfirmYes.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                } else if (actionType === 'cancel') {
                    actionConfirmYes.classList.add('bg-rose-600', 'hover:bg-rose-700');
                } else {
                    actionConfirmYes.classList.add('bg-amber-500', 'hover:bg-amber-600');
                }
            }

            if (actionConfirmIcon) {
                actionConfirmIcon.classList.remove('bg-amber-100', 'text-amber-700', 'bg-emerald-100', 'text-emerald-700', 'bg-rose-100', 'text-rose-700');

                if (actionType === 'approve') {
                    actionConfirmIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    actionConfirmIcon.classList.add('bg-emerald-100', 'text-emerald-700');
                } else if (actionType === 'cancel') {
                    actionConfirmIcon.innerHTML = '<i class="fas fa-triangle-exclamation"></i>';
                    actionConfirmIcon.classList.add('bg-rose-100', 'text-rose-700');
                } else {
                    actionConfirmIcon.innerHTML = '<i class="fas fa-circle-exclamation"></i>';
                    actionConfirmIcon.classList.add('bg-amber-100', 'text-amber-700');
                }
            }

            previousFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            isConfirmAnimating = false;
            actionConfirmModal.classList.remove('hidden');
            requestAnimationFrame(() => {
                actionConfirmModal.classList.remove('opacity-0');
                if (actionConfirmPanel) {
                    actionConfirmPanel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                }
                actionConfirmYes?.focus();
            });
        });
    };

    const setActionLoadingState = (actionType, isLoading) => {
        const buttonIds = ['bacBtnMoveToReview', 'bacBtnApprove', 'bacBtnCancel'];
        buttonIds.forEach((id) => {
            const button = document.getElementById(id);
            if (!button) {
                return;
            }

            if (!button.dataset.defaultHtml) {
                button.dataset.defaultHtml = button.innerHTML;
            }

            button.disabled = isLoading;
            button.classList.toggle('opacity-70', isLoading);
            button.classList.toggle('cursor-not-allowed', isLoading);
        });

        if (!isLoading) {
            buttonIds.forEach((id) => {
                const button = document.getElementById(id);
                if (button?.dataset.defaultHtml) {
                    button.innerHTML = button.dataset.defaultHtml;
                }
            });
            return;
        }

        const config = actionButtonConfig[actionType];
        const activeButton = config ? document.getElementById(config.elementId) : null;
        if (!activeButton) {
            return;
        }

        activeButton.innerHTML = `<i class="fas fa-spinner fa-spin"></i>${config.loadingText}`;
    };

    const resetActionButtonState = (actionType) => {
        if (!actionType) {
            return;
        }

        setActionLoadingState(actionType, false);
    };

    const normalize = (value = '') => String(value).toLowerCase().replace(/\s+/g, ' ').trim();

    const getQueuePage = () => document.querySelector(queuePageSelector);

    const getQueueRows = () => {
        const tbody = document.querySelector('#bacPurchaseRequestsTable tbody');
        if (!tbody) {
            return [];
        }

        return Array.from(tbody.querySelectorAll('tr')).filter((row) => !row.dataset.staticRow);
    };

    const applyQueueFilter = (term = '') => {
        const filterValue = normalize(term);
        const rows = getQueueRows();

        rows.forEach((row) => {
            const rowSearch = normalize(row.textContent || '');
            const matches = !filterValue || rowSearch.includes(filterValue);
            row.classList.toggle('hidden', !matches);
        });
    };

    const setQueueLoadingState = (loading) => {
        const queuePage = getQueuePage();
        if (!queuePage) {
            return;
        }

        queuePage.classList.toggle('opacity-70', loading);
        queuePage.classList.toggle('pointer-events-none', loading);

        queuePage.querySelectorAll('[data-bac-tab], nav[aria-label="Pagination Navigation"] a').forEach((el) => {
            el.classList.toggle('pointer-events-none', loading);
        });
    };

    const swapQueuePage = (html, url, pushState = true) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nextQueuePage = doc.querySelector(queuePageSelector);
        const currentQueuePage = getQueuePage();

        if (!nextQueuePage || !currentQueuePage) {
            return;
        }

        nextQueuePage.classList.add('opacity-0', 'translate-y-1');
        currentQueuePage.replaceWith(nextQueuePage);

        if (pushState && window.location.href !== url) {
            window.history.pushState({ bacQueueUrl: url }, '', url);
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                nextQueuePage.classList.remove('opacity-0', 'translate-y-1');
            });
        });
    };

    const loadQueueContent = async (url, options = {}) => {
        const { pushState = true, searchTerm = '' } = options;
        if (!url) {
            return;
        }

        if (activeQueueRequestController) {
            activeQueueRequestController.abort();
        }

        const requestToken = ++queueRequestToken;
        activeQueueRequestController = new AbortController();

        isQueueRequestInFlight = true;
        setQueueLoadingState(true);

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                signal: activeQueueRequestController.signal,
            });

            if (!response.ok || requestToken !== queueRequestToken || !getQueuePage()) {
                return;
            }

            const html = await response.text();
            swapQueuePage(html, url, pushState);

            const searchInput = document.getElementById('bacPurchaseRequestSearch');
            if (searchInput) {
                searchInput.value = searchTerm;
            }
            applyQueueFilter(searchTerm);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Failed to load BAC queue content:', error);
            }
        } finally {
            if (requestToken === queueRequestToken) {
                isQueueRequestInFlight = false;
                setQueueLoadingState(false);
            }
        }
    };

    const parseCountValue = (raw) => {
        if (!raw) {
            return 0;
        }

        const cleaned = String(raw).replace(/,/g, '').trim();
        const parsed = Number.parseInt(cleaned, 10);
        return Number.isNaN(parsed) ? 0 : parsed;
    };

    const formatCountValue = (value) => Math.max(0, Number(value) || 0).toLocaleString('en-US');

    const adjustCount = (key, delta) => {
        if (!key || !delta) {
            return;
        }

        const selectors = [
            `[data-bac-count-card="${key}"]`,
            `[data-bac-tab-count="${key}"]`,
        ];

        selectors.forEach((selector) => {
            document.querySelectorAll(selector).forEach((node) => {
                const current = parseCountValue(node.textContent);
                node.textContent = formatCountValue(current + delta);
            });
        });
    };

    const ensureEmptyStateRow = (tbody) => {
        if (!tbody) {
            return;
        }

        const dataRows = Array.from(tbody.querySelectorAll('tr')).filter((row) => !row.dataset.staticRow);
        if (dataRows.length > 0) {
            return;
        }

        let emptyRow = tbody.querySelector('tr[data-static-row="empty"]');
        if (emptyRow) {
            emptyRow.classList.remove('hidden');
            return;
        }

        emptyRow = document.createElement('tr');
        emptyRow.dataset.staticRow = 'empty';
        emptyRow.innerHTML = `
            <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                <p class="font-medium text-lg">No purchase requests for BAC at the moment.</p>
            </td>
        `;
        tbody.appendChild(emptyRow);
    };

    const removeQueueRowByPrNo = (prNo) => {
        const tbody = document.querySelector('#bacPurchaseRequestsTable tbody');
        if (!tbody || !prNo) {
            return;
        }

        const row = Array.from(tbody.querySelectorAll('tr[data-pr-no]')).find((item) => item.dataset.prNo === prNo);
        if (row) {
            row.remove();
        }

        ensureEmptyStateRow(tbody);
    };

    const syncQueueAfterStatusChange = (prNo, previousStatusId, nextStatusId) => {
        const fromKey = statusIdToTabKey[Number(previousStatusId)] || null;
        const toKey = statusIdToTabKey[Number(nextStatusId)] || null;

        if (fromKey) {
            adjustCount(fromKey, -1);
        }
        if (toKey) {
            adjustCount(toKey, 1);
        }

        removeQueueRowByPrNo(prNo);
    };

    const showModal = () => {
        toggleModal($modal, true);
        document.body.classList.add('overflow-hidden');
    };

    const hideModal = () => {
        toggleModal($modal, false);
        document.body.classList.remove('overflow-hidden');
        currentPrData = null;
        hideModalError();
        const requestedSignature = document.getElementById('bacPrRequestedSignature');
        const recommendedSignature = document.getElementById('bacPrRecommendedSignature');
        const approvedSignature = document.getElementById('bacPrApprovedSignature');

        if (requestedSignature) requestedSignature.innerHTML = '';
        if (recommendedSignature) recommendedSignature.innerHTML = '';
        if (approvedSignature) approvedSignature.innerHTML = '';
        hideToast('#bacPrToast');
    };

    // Show/hide alternative modal
    function showAlternativeModal(item) {
        if (!alternativeModal) return;
        
        document.getElementById('alternativePriId').value = item.pri_id;
        document.getElementById('alternativeOriginalItem').textContent = item.item_description;
        document.getElementById('alternateDescription').value = '';
        document.getElementById('alternativeUnitCost').value = item.estimated_unit_cost || '';
        document.getElementById('alternativeRemarks').value = '';
        
        // Store quantity for total calculation
        alternativeModal.dataset.quantity = item.quantity || 1;
        updateAlternativeTotalCost();
        
        hideAlternativeModalError();
        
        alternativeModal.classList.remove('hidden');
    }

    function hideAlternativeModal() {
        if (!alternativeModal) return;
        alternativeModal.classList.add('hidden');
    }

    function showAlternativeModalError(message) {
        const errorDiv = document.getElementById('alternativeModalError');
        const errorMsg = document.getElementById('alternativeModalErrorMessage');
        if (errorDiv && errorMsg) {
            errorMsg.textContent = message;
            errorDiv.classList.remove('hidden');
        }
    }

    function hideAlternativeModalError() {
        const errorDiv = document.getElementById('alternativeModalError');
        if (errorDiv) errorDiv.classList.add('hidden');
    }
    
    // Show error in modal
    function showModalError(message) {
        const errorDiv = document.getElementById('bacModalError');
        const errorMessage = document.getElementById('bacModalErrorMessage');
        
        if (errorDiv && errorMessage) {
            errorMessage.textContent = message;
            errorDiv.classList.remove('hidden');
            
            // Scroll error into view
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
    
    // Hide error in modal
    function hideModalError() {
        const errorDiv = document.getElementById('bacModalError');
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
    }

    // Update alternative modal total cost when unit cost changes
    function updateAlternativeTotalCost() {
        const unitCostInput = document.getElementById('alternativeUnitCost');
        const totalCostDiv = document.getElementById('alternativeTotalCost');
        const quantity = parseInt(alternativeModal?.dataset?.quantity || 1);
        const unitCost = parseFloat(unitCostInput?.value || 0);
        const total = unitCost * quantity;
        if (totalCostDiv) {
            totalCostDiv.textContent = formatCurrency(total);
        }
    }

    // Format currency
    function formatCurrency(value) {
        const num = parseFloat(value);
        return isNaN(num) ? '₱0.00' : '₱' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Format date
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString.replace(' ', 'T'));
        if (isNaN(date.getTime())) return '';
        return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }

    // Get item status badge HTML
    function getItemStatusBadge(item) {
        if (item.removed_at) {
            return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                <i class="fas fa-ban mr-1"></i>Removed
            </span>`;
        }

        if (item.fulfillment_status === 'fulfilled') {
            return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                <i class="fas fa-check mr-1"></i>Alternative Accepted
            </span>`;
        }

        if (item.fulfillment_status === 'waiting') {
            return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                <i class="fas fa-clock mr-1"></i>Waiting until ${formatDate(item.employee_wait_until)}
            </span>`;
        }

        if (item.fulfillment_status === 'expired') {
            return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700">
                <i class="fas fa-times mr-1"></i>Wait Expired
            </span>`;
        }
        
        if (item.fulfillment_status === 'alternative') {
            if (item.employee_decision === 'accept') {
                return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                    <i class="fas fa-check mr-1"></i>Alternative Accepted
                </span>`;
            } else if (item.employee_decision === 'wait') {
                return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                    <i class="fas fa-clock mr-1"></i>Waiting until ${formatDate(item.employee_wait_until)}
                </span>`;
            } else {
                return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                    <i class="fas fa-hourglass-half mr-1"></i>Awaiting Response
                </span>`;
            }
        }
        
        return '';
    }

    // Load PR details
    async function loadPrDetails(showUrl, prNo) {
        try {
            const response = await fetch(showUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server response:', errorText);
                throw new Error(`Failed to load purchase request: ${response.statusText}`);
            }

            const result = await response.json();
            const data = result.data || result; // Handle both wrapped and unwrapped responses
            currentPrData = data;
            populateModal(data, prNo);
            showModal();
        } catch (error) {
            console.error('Error loading PR:', error);
            showToast(error.message || 'Failed to load purchase request details', 'error');
        }
    }

    // Populate modal with PR data
    function populateModal(data, prNo) {
        // Header
        document.getElementById('bacPrNumber').textContent = `PR No: ${prNo}`;
        
        // Basic info
        document.getElementById('bacPrNo').textContent = prNo;
        document.getElementById('bacPrDate').textContent = formatDate(data.created_at);
        document.getElementById('bacPrDivisionInput').textContent = data.division || '';
        document.getElementById('bacPrSectionInput').textContent = data.section || '';
        document.getElementById('bacPrSaiInput').textContent = data.sai_no || '';
        document.getElementById('bacPrAlobsInput').textContent = data.alobs_no || '';
        document.getElementById('bacPrPurposeTextarea').value = data.purpose || '';
        
        // Requester info
        document.getElementById('bacPrRequestedPrintedName').textContent = data.requester || 'Unknown';
        document.getElementById('bacPrRequestedDate').textContent = formatDate(data.created_at);
        const requestedSignature = document.getElementById('bacPrRequestedSignature');
        if (requestedSignature) {
            requestedSignature.innerHTML = renderSignature(data.requester_signature || '');
        }
        
        // Recommender info
        document.getElementById('bacPrRecommendedPrintedName').textContent = data.recommended_by_name || '';
        document.getElementById('bacPrRecommendedDate').textContent = formatDate(data.recommended_at);
        document.getElementById('bacPrRecommendationRemarks').textContent = data.recommendation_remarks || '';
        const recommendedSignature = document.getElementById('bacPrRecommendedSignature');
        if (recommendedSignature) {
            recommendedSignature.innerHTML = renderSignature(data.recommended_signature || '');
        }
        
        // Approver info (will be filled after approval)
        document.getElementById('bacPrApprovedPrintedName').textContent = data.approved_by || '';
        const approvedSignature = document.getElementById('bacPrApprovedSignature');
        if (approvedSignature) {
            approvedSignature.innerHTML = renderSignature(data.approved_signature || '');
        }
        
        // Fund fields
        document.getElementById('bacFundCluster').value = data.fund_cluster || '';
        document.getElementById('bacFundsAvailable').value = data.funds_available != null ? Number(data.funds_available).toFixed(2) : '';
        
        // Items table
        const itemsTableBody = document.getElementById('bacPrItemsTableBody');
        itemsTableBody.innerHTML = '';
        
        let totalCost = 0;
        const canSuggestAlternative = [103, 104].includes(data.status_id); // Recommended or For Approval
        
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                const itemTotal = parseFloat(item.estimated_total_cost || 0);
                
                // Don't count removed items in total
                if (!item.removed_at) {
                    totalCost += itemTotal;
                }
                
                // Build item description cell with status info
                let descriptionHtml = `<div class="font-medium">${item.item_description || ''}</div>`;
                
                // Show original vs alternative info (hide alternatives when requester chooses to wait)
                if (item.alternate_description && item.original_description) {
                    if (item.employee_decision === 'accept') {
                        descriptionHtml += `<div class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-history mr-1"></i>Original: ${item.original_description}
                        </div>`;
                    } else if (item.fulfillment_status === 'alternative') {
                        descriptionHtml += `<div class="text-xs text-blue-600 mt-1">
                            <i class="fas fa-exchange-alt mr-1"></i>Pending alt: ${item.alternate_description}
                        </div>`;
                    }
                }
                
                // Status badge
                const statusBadge = getItemStatusBadge(item);
                if (statusBadge) {
                    descriptionHtml += `<div class="mt-2">${statusBadge}</div>`;
                }
                
                // Removal reason
                if (item.removed_at && item.removal_reason) {
                    descriptionHtml += `<div class="text-xs text-rose-600 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>${item.removal_reason}
                    </div>`;
                }
                
                // Action button
                let actionHtml = '';
                if (canSuggestAlternative && !item.removed_at) {
                    // Only show suggest button if no pending alternative or waiting/fulfilled/expired status
                    const hasPendingAlt = item.fulfillment_status === 'alternative' && !item.employee_decision;
                    const isLocked = ['waiting', 'fulfilled', 'expired'].includes(item.fulfillment_status);
                    if (!hasPendingAlt && !isLocked) {
                        actionHtml = `
                            <button type="button" class="js-suggest-alternative inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-all"
                                data-pri-id="${item.pri_id}"
                                data-item-description="${(item.item_description || '').replace(/"/g, '&quot;')}"
                                data-quantity="${item.quantity || 1}"
                                data-unit-cost="${item.estimated_unit_cost || 0}"
                                title="Suggest an alternative item">
                                <i class="fas fa-exchange-alt"></i>
                                <span class="hidden sm:inline">Alt</span>
                            </button>
                        `;
                    } else {
                        actionHtml = `<span class="text-xs text-gray-400">Pending</span>`;
                    }
                }
                
                // For status 104 (For Approval), make unit cost editable
                const isForApproval = data.status_id === 104;
                const unitCostHtml = isForApproval && !item.removed_at
                    ? `<input type="number" min="0" step="0.01" 
                            class="js-item-unit-cost w-full border border-gray-300 rounded px-2 py-1 text-right text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" 
                            data-pri-id="${item.pri_id}" 
                            data-quantity="${item.quantity || 1}"
                            value="${parseFloat(item.estimated_unit_cost || 0).toFixed(2)}">`
                    : formatCurrency(item.estimated_unit_cost || 0);
                
                const totalCostHtml = isForApproval && !item.removed_at
                    ? `<span class="js-item-total-cost" data-pri-id="${item.pri_id}">${formatCurrency(itemTotal)}</span>`
                    : formatCurrency(itemTotal);
                
                row.innerHTML = `
                    <td class="border border-gray-500 px-3 py-2 text-center ${item.removed_at ? 'opacity-50 line-through' : ''}">${item.quantity || 0}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center ${item.removed_at ? 'opacity-50 line-through' : ''}">${item.unit || ''}</td>
                    <td class="border border-gray-500 px-3 py-2 ${item.removed_at ? 'opacity-50' : ''}">${descriptionHtml}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center ${item.removed_at ? 'opacity-50 line-through' : ''}">${item.stock_property_no || ''}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center ${item.removed_at ? 'opacity-50 line-through' : ''}">${unitCostHtml}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center ${item.removed_at ? 'opacity-50 line-through' : ''}">${totalCostHtml}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${actionHtml}</td>
                `;
                
                itemsTableBody.appendChild(row);
            });
            
            // Attach click handlers for suggest alternative buttons
            itemsTableBody.querySelectorAll('.js-suggest-alternative').forEach(btn => {
                btn.addEventListener('click', function() {
                    const priId = this.dataset.priId;
                    const itemDesc = this.dataset.itemDescription;
                    const quantity = this.dataset.quantity;
                    const unitCost = this.dataset.unitCost;
                    showAlternativeModal({ 
                        pri_id: priId, 
                        item_description: itemDesc,
                        quantity: quantity,
                        estimated_unit_cost: unitCost
                    });
                });
            });
            
            // Attach change handlers for editable unit cost inputs
            itemsTableBody.querySelectorAll('.js-item-unit-cost').forEach(input => {
                input.addEventListener('input', function() {
                    const priId = this.dataset.priId;
                    const quantity = parseInt(this.dataset.quantity || 1);
                    const unitCost = parseFloat(this.value || 0);
                    const totalCost = unitCost * quantity;
                    
                    // Update the total cost display for this item
                    const totalCostSpan = itemsTableBody.querySelector(`.js-item-total-cost[data-pri-id="${priId}"]`);
                    if (totalCostSpan) {
                        totalCostSpan.textContent = formatCurrency(totalCost);
                    }
                    
                    // Update the grand total
                    recalculateGrandTotal();
                });
            });
        } else {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="7" class="border border-gray-500 px-3 py-4 text-center text-gray-500">No items found</td>';
            itemsTableBody.appendChild(emptyRow);
        }
        
        document.getElementById('bacPrTotalCost').textContent = formatCurrency(totalCost);
        
        // Conditionally show/hide action buttons based on status
        updateActionButtons(data.status_id);
    }
    
    // Recalculate grand total from all item inputs or displays
    function recalculateGrandTotal() {
        const itemsTableBody = document.getElementById('bacPrItemsTableBody');
        let grandTotal = 0;
        
        // Check for editable inputs first
        const inputs = itemsTableBody.querySelectorAll('.js-item-unit-cost');
        if (inputs.length > 0) {
            inputs.forEach(input => {
                const quantity = parseInt(input.dataset.quantity || 1);
                const unitCost = parseFloat(input.value || 0);
                grandTotal += unitCost * quantity;
            });
        }
        
        document.getElementById('bacPrTotalCost').textContent = formatCurrency(grandTotal);
        
        // Also update currentPrData if available
        if (currentPrData) {
            currentPrData.total_estimated_cost = grandTotal;
        }
    }
    
    // Update action buttons based on PR status
    function updateActionButtons(statusId) {
        const btnMoveToReview = document.getElementById('bacBtnMoveToReview');
        const btnApprove = document.getElementById('bacBtnApprove');
        const btnCancel = document.getElementById('bacBtnCancel');
        const actionStateNote = document.getElementById('bacActionStateNote');
        const fundClusterInput = document.getElementById('bacFundCluster');
        const fundsAvailableInput = document.getElementById('bacFundsAvailable');
        const remarksInput = document.getElementById('bacRemarks');

        const toggleActionInputs = (isReadOnly) => {
            [fundClusterInput, fundsAvailableInput, remarksInput].forEach((input) => {
                if (!input) {
                    return;
                }

                input.readOnly = isReadOnly;
                input.disabled = isReadOnly;
                input.classList.toggle('bg-gray-100', isReadOnly);
                input.classList.toggle('cursor-not-allowed', isReadOnly);
            });
        };
        
        // Status constants (from backend):
        // 103 = PR_RECOMMENDED (Awaiting BAC Review)
        // 104 = PR_FOR_APPROVAL (For Final Approval)
        // 105 = PR_APPROVED (Approved)
        // 106 = PR_CANCELLED (Cancelled)
        
        // Hide all buttons by default
        btnMoveToReview.style.display = 'none';
        btnApprove.style.display = 'none';
        btnCancel.style.display = 'none';

        if (actionStateNote) {
            actionStateNote.classList.add('hidden');
            actionStateNote.textContent = '';
        }

        toggleActionInputs(false);
        
        if (statusId === 103) {
            // Status: Recommended (Awaiting BAC Review)
            // Show: Move to For Approval + Cancel
            btnMoveToReview.style.display = 'inline-flex';
            btnCancel.style.display = 'inline-flex';
        } else if (statusId === 104) {
            // Status: For Approval (For Final Approval)
            // Show: Approve + Cancel
            btnApprove.style.display = 'inline-flex';
            btnCancel.style.display = 'inline-flex';
        } else if (statusId === 105 || statusId === 106) {
            // Status: Approved/Cancelled (terminal)
            // Show: no actions + read-only notice
            toggleActionInputs(true);
            if (actionStateNote) {
                actionStateNote.textContent = 'This request is read-only. No further BAC actions are available for this status.';
                actionStateNote.classList.remove('hidden');
            }
        }
        // For Approved (105) or Cancelled (106), no action buttons shown (read-only)
    }

    // Collect edited item costs from the form
    function collectEditedItemCosts() {
        const itemsTableBody = document.getElementById('bacPrItemsTableBody');
        const inputs = itemsTableBody.querySelectorAll('.js-item-unit-cost');
        const items = [];
        
        inputs.forEach(input => {
            items.push({
                pri_id: input.dataset.priId,
                unit_cost: parseFloat(input.value || 0)
            });
        });
        
        return items;
    }

    // Save edited item costs before approval
    async function saveItemCosts(prNo) {
        const items = collectEditedItemCosts();
        if (items.length === 0) return true; // No editable items
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        items.forEach((item, index) => {
            formData.append(`items[${index}][pri_id]`, item.pri_id);
            formData.append(`items[${index}][unit_cost]`, item.unit_cost);
        });
        
        try {
            const response = await fetch(`/bac/purchase-requests/${prNo}/update-item-costs`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const result = await response.json();
                throw new Error(result.message || 'Failed to save item costs');
            }
            
            return true;
        } catch (error) {
            console.error('Error saving item costs:', error);
            showModalError(error.message || 'Failed to save item costs. Please try again.');
            return false;
        }
    }

    // Handle BAC actions
    async function handleBacAction(actionType, prNo) {
        if (isPerformingAction) {
            return;
        }

        // Clear previous errors
        hideModalError();
        
        const fundCluster = document.getElementById('bacFundCluster').value.trim();
        const fundsAvailable = document.getElementById('bacFundsAvailable').value.trim();
        const remarks = document.getElementById('bacRemarks').value.trim();

        // Validate funds for approval
        if (actionType === 'approve') {
            // First, save any edited item costs
            const costsSaved = await saveItemCosts(prNo);
            if (!costsSaved) {
                return; // Error already shown
            }
            
            // Recalculate total from current inputs
            recalculateGrandTotal();
            
            if (!fundsAvailable || parseFloat(fundsAvailable) <= 0) {
                showModalError('Funds available must be set before approval');
                return;
            }
            
            const totalCost = parseFloat(currentPrData?.total_estimated_cost || 0);
            if (parseFloat(fundsAvailable) < totalCost) {
                showModalError('Funds available must cover the total estimated cost');
                return;
            }

            const confirmed = await openActionConfirmModal({
                title: 'Approve Purchase Request',
                message: 'Approve this purchase request? This action is final.',
                actionType: 'approve',
                confirmLabel: 'Approve',
            });

            if (!confirmed) {
                resetActionButtonState(actionType);
                return;
            }
        }

        if (actionType === 'cancel') {
            const confirmed = await openActionConfirmModal({
                title: 'Cancel Purchase Request',
                message: 'Cancel this purchase request? This cannot be undone.',
                actionType: 'cancel',
                confirmLabel: 'Cancel Request',
            });

            if (!confirmed) {
                resetActionButtonState(actionType);
                return;
            }
        }

        if (actionType === 'review') {
            const confirmed = await openActionConfirmModal({
                title: 'Move to For Approval',
                message: 'Move this request to For Approval status?',
                actionType: 'review',
                confirmLabel: 'Move to For Approval',
            });

            if (!confirmed) {
                resetActionButtonState(actionType);
                return;
            }
        }

        const routes = {
            review: `/bac/purchase-requests/${prNo}/review`,
            approve: `/bac/purchase-requests/${prNo}/approve`,
            cancel: `/bac/purchase-requests/${prNo}/cancel`
        };

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        
        if (actionType === 'approve' || actionType === 'review') {
            if (fundCluster) formData.append('fund_cluster', fundCluster);
            if (fundsAvailable) formData.append('funds_available', fundsAvailable);
        }
        
        if (remarks) formData.append('remarks', remarks);
        
        const previousStatusId = Number(currentPrData?.status_id || 0);

        try {
            isPerformingAction = true;
            setActionLoadingState(actionType, true);

            const response = await fetch(routes[actionType], {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                const nextStatusId = Number(result?.data?.status_id || actionTargetStatusId[actionType] || previousStatusId);
                syncQueueAfterStatusChange(prNo, previousStatusId, nextStatusId);
                if (currentPrData) {
                    currentPrData.status_id = nextStatusId;
                }

                hideModal();
                showToast(result.message || 'Action completed successfully', 'success');
            } else {
                // Handle validation errors from server
                if (result.errors) {
                    const errorMessages = Object.values(result.errors).flat().join(', ');
                    showModalError(errorMessages);
                } else {
                    showModalError(result.message || 'Action failed');
                }
            }
        } catch (error) {
            console.error('Error performing BAC action:', error);
            showModalError(error.message || 'Failed to complete action');
        } finally {
            isPerformingAction = false;
            setActionLoadingState(actionType, false);
        }
    }

    // Event listeners
    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element ? event.target.closest('.js-view-bac-pr') : null;
        if (!button) {
            return;
        }

        event.preventDefault();
        const showUrl = button.dataset.showUrl;
        const prNo = button.dataset.prNo;

        if (showUrl && prNo) {
            loadPrDetails(showUrl, prNo);
        }
    }, { signal });

    // Close modal buttons
    modal.querySelectorAll('[data-close-modal]').forEach(button => {
        button.addEventListener('click', hideModal, { signal });
    });

    modal.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-close-modal]') : null;
        if (target) {
            hideModal();
        }
    }, { signal });

    // BAC action buttons
    document.getElementById('bacBtnMoveToReview')?.addEventListener('click', function() {
        if (currentPrData?.pr_no) {
            handleBacAction('review', currentPrData.pr_no);
        }
    }, { signal });

    document.getElementById('bacBtnApprove')?.addEventListener('click', function() {
        if (currentPrData?.pr_no) {
            handleBacAction('approve', currentPrData.pr_no);
        }
    }, { signal });

    document.getElementById('bacBtnCancel')?.addEventListener('click', function() {
        if (currentPrData?.pr_no) {
            handleBacAction('cancel', currentPrData.pr_no);
        }
    }, { signal });

    // Search functionality (delegated so it still works after queue partial swaps)
    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.id !== 'bacPurchaseRequestSearch') {
            return;
        }

        applyQueueFilter(input.value);
    }, { signal });

    document.addEventListener('click', (event) => {
        const tabLink = event.target instanceof Element ? event.target.closest(`${queuePageSelector} [data-bac-tab]`) : null;
        if (!tabLink) {
            return;
        }

        event.preventDefault();
        if (isQueueRequestInFlight) {
            return;
        }

        const url = tabLink.getAttribute('href');
        if (!url) {
            return;
        }

        const searchTerm = String(document.getElementById('bacPurchaseRequestSearch')?.value || '');
        loadQueueContent(url, { pushState: true, searchTerm });
    }, { signal });

    document.addEventListener('click', (event) => {
        const pageLink = event.target instanceof Element
            ? event.target.closest(`${queuePageSelector} nav[aria-label="Pagination Navigation"] a`)
            : null;

        if (!pageLink) {
            return;
        }

        event.preventDefault();
        if (isQueueRequestInFlight) {
            return;
        }

        const url = pageLink.getAttribute('href');
        if (!url) {
            return;
        }

        const searchTerm = String(document.getElementById('bacPurchaseRequestSearch')?.value || '');
        loadQueueContent(url, { pushState: true, searchTerm });
    }, { signal });

    if (!window.__bacQueuePopstateBound) {
        window.addEventListener('popstate', () => {
            if (!getQueuePage() || isQueueRequestInFlight) {
                return;
            }

            const searchTerm = String(document.getElementById('bacPurchaseRequestSearch')?.value || '');
            loadQueueContent(window.location.href, { pushState: false, searchTerm });
        });

        window.__bacQueuePopstateBound = true;
    }

    applyQueueFilter(String(document.getElementById('bacPurchaseRequestSearch')?.value || ''));

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && actionConfirmModal && !actionConfirmModal.classList.contains('hidden')) {
            closeActionConfirmModal(false);
            return;
        }

        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
        if (e.key === 'Escape' && alternativeModal && !alternativeModal.classList.contains('hidden')) {
            hideAlternativeModal();
        }
    }, { signal });

    // Alternative modal close buttons
    document.querySelectorAll('[data-close-alternative-modal]').forEach(button => {
        button.addEventListener('click', hideAlternativeModal, { signal });
    });

    if (actionConfirmModal) {
        actionConfirmModal.querySelectorAll('[data-close-action-confirm], [data-confirm-no]').forEach((button) => {
            button.addEventListener('click', () => closeActionConfirmModal(false), { signal });
        });

        actionConfirmYes?.addEventListener('click', async () => {
            await animateConfirmSuccess();
            closeActionConfirmModal(true);
        }, { signal });
    }

    // Alternative unit cost change handler
    const alternativeUnitCostInput = document.getElementById('alternativeUnitCost');
    if (alternativeUnitCostInput) {
        alternativeUnitCostInput.addEventListener('input', updateAlternativeTotalCost, { signal });
    }

    // Alternative form submission
    const alternativeForm = document.getElementById('bacAlternativeForm');
    if (alternativeForm) {
        alternativeForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            hideAlternativeModalError();
            
            const priId = document.getElementById('alternativePriId').value;
            const alternateDescription = document.getElementById('alternateDescription').value.trim();
            const unitCost = document.getElementById('alternativeUnitCost').value;
            const remarks = document.getElementById('alternativeRemarks').value.trim();
            
            if (!alternateDescription) {
                showAlternativeModalError('Please enter an alternative item description.');
                return;
            }
            
            if (!unitCost || parseFloat(unitCost) <= 0) {
                showAlternativeModalError('Please enter a valid unit cost.');
                return;
            }
            
            if (!currentPrData?.pr_no) {
                showAlternativeModalError('Unable to determine purchase request. Please try again.');
                return;
            }
            
            const submitBtn = document.getElementById('bacBtnSubmitAlternative');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                formData.append('pri_id', priId);
                formData.append('alternate_description', alternateDescription);
                formData.append('unit_cost', unitCost);
                if (remarks) formData.append('remarks', remarks);
                
                const response = await fetch(`/bac/purchase-requests/${currentPrData.pr_no}/suggest-alternative`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    hideAlternativeModal();
                    showToast(result.message || 'Alternative suggestion sent successfully.', 'success');
                    
                    // Reload PR data to refresh items
                    if (currentPrData) {
                        const showUrl = document.querySelector(`.js-view-bac-pr[data-pr-no="${currentPrData.pr_no}"]`)?.dataset.showUrl;
                        if (showUrl) {
                            loadPrDetails(showUrl, currentPrData.pr_no);
                        }
                    }
                } else {
                    showAlternativeModalError(result.message || 'Failed to send alternative suggestion.');
                }
            } catch (error) {
                console.error('Error sending alternative suggestion:', error);
                showAlternativeModalError('An error occurred. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Suggestion';
            }
        }, { signal });
    }
    document.addEventListener('turbo:before-cache', window.__bacPurchaseRequestsCleanup, { once: true, signal });
};

document.addEventListener('turbo:load', initBacPurchaseRequestPage);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBacPurchaseRequestPage, { once: true });
} else {
    initBacPurchaseRequestPage();
}
