// BAC Purchase Request Review Modal Handler
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const modal = document.getElementById('bacPrModal');
    const toast = document.getElementById('bacPrToast');

    if (!modal) {
        console.warn('BAC PR Modal not found');
        return;
    }

    let currentPrData = null;

    // Show toast notification
    function showToast(message, type = 'success') {
        if (!toast) return;
        
        toast.textContent = message;
        toast.classList.remove('hidden', 'bg-emerald-600', 'bg-rose-600', 'bg-amber-600');
        
        if (type === 'error') {
            toast.classList.add('bg-rose-600');
        } else if (type === 'warning') {
            toast.classList.add('bg-amber-600');
        } else {
            toast.classList.add('bg-emerald-600');
        }
        
        toast.classList.remove('hidden');
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4000);
    }

    // Show modal
    function showModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Hide modal
    function hideModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentPrData = null;
        hideModalError();
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
        document.getElementById('bacPrDivisionInput').value = data.division || '';
        document.getElementById('bacPrSectionInput').value = data.section || '';
        document.getElementById('bacPrSaiInput').value = data.sai_no || '';
        document.getElementById('bacPrAlobsInput').value = data.alobs_no || '';
        document.getElementById('bacPrPurposeTextarea').value = data.purpose || '';
        
        // Requester info
        document.getElementById('bacPrRequestedPrintedName').textContent = data.requester || 'Unknown';
        document.getElementById('bacPrRequestedDate').textContent = formatDate(data.created_at);
        
        // Recommender info
        document.getElementById('bacPrRecommendedPrintedName').textContent = data.recommended_by_name || '';
        document.getElementById('bacPrRecommendedDate').textContent = formatDate(data.recommended_at);
        document.getElementById('bacPrRecommendationRemarks').textContent = data.recommendation_remarks || '';
        
        // Approver info (will be filled after approval)
        document.getElementById('bacPrApprovedPrintedName').textContent = data.approved_by || '';
        
        // Fund fields
        document.getElementById('bacFundCluster').value = data.fund_cluster || '';
        document.getElementById('bacFundsAvailable').value = data.funds_available != null ? Number(data.funds_available).toFixed(2) : '';
        
        // Items table
        const itemsTableBody = document.getElementById('bacPrItemsTableBody');
        itemsTableBody.innerHTML = '';
        
        let totalCost = 0;
        
        if (data.items && data.items.length > 0) {
            data.items.forEach(item => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';
                const itemTotal = parseFloat(item.estimated_total_cost || 0);
                totalCost += itemTotal;
                
                row.innerHTML = `
                    <td class="border border-gray-500 px-3 py-2 text-center">${item.quantity || 0}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${item.unit || ''}</td>
                    <td class="border border-gray-500 px-3 py-2">${item.item_description || ''}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${item.stock_property_no || ''}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${formatCurrency(item.estimated_unit_cost || 0)}</td>
                    <td class="border border-gray-500 px-3 py-2 text-center">${formatCurrency(itemTotal)}</td>
                `;
                
                itemsTableBody.appendChild(row);
            });
        } else {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="6" class="border border-gray-500 px-3 py-4 text-center text-gray-500">No items found</td>';
            itemsTableBody.appendChild(emptyRow);
        }
        
        document.getElementById('bacPrTotalCost').textContent = formatCurrency(totalCost);
        
        // Conditionally show/hide action buttons based on status
        updateActionButtons(data.status_id);
    }
    
    // Update action buttons based on PR status
    function updateActionButtons(statusId) {
        const btnMoveToReview = document.getElementById('bacBtnMoveToReview');
        const btnApprove = document.getElementById('bacBtnApprove');
        const btnCancel = document.getElementById('bacBtnCancel');
        
        // Status constants (from backend):
        // 103 = PR_RECOMMENDED (Awaiting BAC Review)
        // 104 = PR_FOR_APPROVAL (For Final Approval)
        // 105 = PR_APPROVED (Approved)
        // 106 = PR_CANCELLED (Cancelled)
        
        // Hide all buttons by default
        btnMoveToReview.style.display = 'none';
        btnApprove.style.display = 'none';
        btnCancel.style.display = 'none';
        
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
        }
        // For Approved (105) or Cancelled (106), no action buttons shown (read-only)
    }

    // Handle BAC actions
    async function handleBacAction(actionType, prNo) {
        // Clear previous errors
        hideModalError();
        
        const fundCluster = document.getElementById('bacFundCluster').value.trim();
        const fundsAvailable = document.getElementById('bacFundsAvailable').value.trim();
        const remarks = document.getElementById('bacRemarks').value.trim();

        // Validate funds for approval
        if (actionType === 'approve') {
            if (!fundsAvailable || parseFloat(fundsAvailable) <= 0) {
                showModalError('Funds available must be set before approval');
                return;
            }
            
            const totalCost = parseFloat(currentPrData?.total_estimated_cost || 0);
            if (parseFloat(fundsAvailable) < totalCost) {
                showModalError('Funds available must cover the total estimated cost');
                return;
            }

            if (!confirm('Approve this purchase request? This action is final.')) {
                return;
            }
        }

        if (actionType === 'cancel' && !confirm('Cancel this purchase request? This cannot be undone.')) {
            return;
        }

        if (actionType === 'review' && !confirm('Move this request to For Approval status?')) {
            return;
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
        
        try {
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
                showToast(result.message || 'Action completed successfully', 'success');
                hideModal();
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
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
        }
    }

    // Event listeners
    document.querySelectorAll('.js-view-bac-pr').forEach(button => {
        button.addEventListener('click', function() {
            const showUrl = this.dataset.showUrl;
            const prNo = this.dataset.prNo;
            
            if (showUrl && prNo) {
                loadPrDetails(showUrl, prNo);
            }
        });
    });

    // Close modal buttons
    document.querySelectorAll('[data-close-modal]').forEach(button => {
        button.addEventListener('click', hideModal);
    });

    // BAC action buttons
    document.getElementById('bacBtnMoveToReview')?.addEventListener('click', function() {
        if (currentPrData?.pr_no) {
            handleBacAction('review', currentPrData.pr_no);
        }
    });

    document.getElementById('bacBtnApprove')?.addEventListener('click', function() {
        if (currentPrData?.pr_no) {
            handleBacAction('approve', currentPrData.pr_no);
        }
    });

    document.getElementById('bacBtnCancel')?.addEventListener('click', function() {
        if (currentPrData?.pr_no) {
            handleBacAction('cancel', currentPrData.pr_no);
        }
    });

    // Search functionality
    const searchInput = document.getElementById('bacPurchaseRequestSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#bacPurchaseRequestsTable tbody tr:not([data-static-row])');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
    });
});
