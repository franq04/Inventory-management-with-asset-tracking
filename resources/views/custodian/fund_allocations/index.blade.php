@extends('layouts.app')

@section('title', 'Fund Allocations')

@section('content')
{{-- Page Header --}}
<div class="animate-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-3xl font-extrabold text-[#1a3a2d]">Fund Allocations</h2>
        <p class="mt-1 text-gray-500">Manage budget clusters for purchase requests.</p>
    </div>
    <button id="openCreateModal" class="w-full sm:w-auto bg-[#1a3a2d] text-white font-semibold px-5 py-2.5 rounded-lg shadow-md hover:bg-opacity-90 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
        <i class="fas fa-plus-circle"></i>
        New Fund Allocation
    </button>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="animate-card mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg shadow-sm">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

{{-- Fund Allocations Table --}}
<div class="animate-card bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white">
                <tr>
                    <th class="px-6 py-4 text-left font-semibold uppercase tracking-wide">Fund Cluster</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wide">Total Amount</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wide">Allocated</th>
                    <th class="px-6 py-4 text-right font-semibold uppercase tracking-wide">Remaining</th>
                    <th class="px-6 py-4 text-center font-semibold uppercase tracking-wide">Utilization</th>
                    <th class="px-6 py-4 text-left font-semibold uppercase tracking-wide">Created By</th>
                    <th class="px-6 py-4 text-center font-semibold uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($allocations as $allocation)
                    @php
                        $allocated = $allocation->total_amount - $allocation->remaining_amount;
                        $utilization = $allocation->total_amount > 0 
                            ? ($allocated / $allocation->total_amount) * 100 
                            : 0;
                        $utilizationColor = $utilization >= 90 ? 'bg-red-500' : ($utilization >= 70 ? 'bg-amber-500' : 'bg-green-500');
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $allocation->id }}">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $allocation->fund_cluster }}</div>
                            <div class="text-xs text-gray-500">Created: {{ $allocation->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-900">
                            ₱{{ number_format($allocation->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-700">
                            ₱{{ number_format($allocated, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-green-700">
                            ₱{{ number_format($allocation->remaining_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="{{ $utilizationColor }} h-2 rounded-full transition-all" style="width: {{ min($utilization, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-600">{{ number_format($utilization, 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            @php
                                $employee = $allocation->creator?->employee;
                                $creatorName = $employee 
                                    ? trim(($employee->first_name ?? '') . ' ' . ($employee->middle_name ?? '') . ' ' . ($employee->last_name ?? '')) 
                                    : ($allocation->creator?->name ?? 'Unknown');
                            @endphp
                            {{ $creatorName }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button class="edit-allocation text-blue-600 hover:text-blue-800 px-3 py-1 rounded-lg hover:bg-blue-50 transition" 
                                        data-id="{{ $allocation->id }}"
                                        data-cluster="{{ $allocation->fund_cluster }}"
                                        data-total="{{ $allocation->total_amount }}"
                                        data-allocated="{{ $allocated }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($allocated == 0)
                                    <button class="delete-allocation text-red-600 hover:text-red-800 px-3 py-1 rounded-lg hover:bg-red-50 transition"
                                            data-id="{{ $allocation->id }}"
                                            data-cluster="{{ $allocation->fund_cluster }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <button class="text-gray-400 px-3 py-1 cursor-not-allowed" title="Cannot delete: has active allocations">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-2"></i>
                            <p class="text-lg">No fund allocations found.</p>
                            <p class="text-sm">Create your first fund allocation to get started.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($allocations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $allocations->links() }}
        </div>
    @endif
</div>

{{-- Toast Notification --}}
<div id="fundAllocationToast" class="fixed bottom-6 right-6 hidden px-5 py-3 rounded-xl shadow-lg text-white bg-[#1a3a2d] text-sm font-semibold animate-card"></div>

{{-- Create/Edit Modal --}}
<div id="allocationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" data-close-modal></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] text-white">
                <h3 class="text-xl font-bold" id="modalTitle">New Fund Allocation</h3>
                <button class="text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all" data-close-modal>
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="allocationForm" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="allocationId" name="allocation_id">
                <input type="hidden" id="formMethod" value="POST">

                <div id="formErrors" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                        <div class="flex-1" id="formErrorsContent"></div>
                    </div>
                </div>

                <div>
                    <label for="fundCluster" class="block text-sm font-semibold text-gray-700 mb-2">
                        Fund Cluster <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="fundCluster" name="fund_cluster" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20"
                           placeholder="e.g., FY2025-GEN-001">
                </div>

                <div>
                    <label for="totalAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Total Amount <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span>
                        <input type="number" id="totalAmount" name="total_amount" step="0.01" min="0" required
                               class="w-full border border-gray-300 rounded-lg pl-8 pr-4 py-2 focus:border-[#1a3a2d] focus:ring-2 focus:ring-[#1a3a2d]/20"
                               placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1" id="allocatedNote"></p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition" data-close-modal>
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#1a3a2d] text-white font-semibold rounded-lg hover:bg-opacity-90 transition">
                        <span id="submitText">Create Allocation</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const $modal = $('#allocationModal');
    const $form = $('#allocationForm');
    const $errors = $('#formErrors');
    const $errorsContent = $('#formErrorsContent');
    const $toast = $('#fundAllocationToast');

    const showToast = (message) => {
        $toast.text(message).removeClass('hidden');
        setTimeout(() => $toast.addClass('hidden'), 3000);
    };

    const toggleModal = ($el, show) => {
        if (show) {
            $el.removeClass('hidden');
            setTimeout(() => $el.addClass('opacity-100'), 10);
        } else {
            $el.removeClass('opacity-100');
            setTimeout(() => $el.addClass('hidden'), 200);
        }
    };

    $('#openCreateModal').on('click', () => {
        $('#modalTitle').text('New Fund Allocation');
        $('#submitText').text('Create Allocation');
        $('#formMethod').val('POST');
        $('#allocationId').val('');
        $('#allocatedNote').text('');
        $form[0].reset();
        $errors.addClass('hidden');

        // Fetch suggested fund cluster code and prefill
        $.ajax({
            method: 'GET',
            url: '/custodian/fund-allocations/suggest',
            success: (resp) => {
                if (resp && resp.suggested_code) {
                    $('#fundCluster').val(resp.suggested_code);
                }
                toggleModal($modal, true);
            },
            error: () => {
                toggleModal($modal, true);
            }
        });
    });

    $('[data-close-modal]').on('click', () => toggleModal($modal, false));

    $('.edit-allocation').on('click', function() {
        const id = $(this).data('id');
        const cluster = $(this).data('cluster');
        const total = $(this).data('total');
        const allocated = $(this).data('allocated');

        $('#modalTitle').text('Edit Fund Allocation');
        $('#submitText').text('Update Allocation');
        $('#formMethod').val('PUT');
        $('#allocationId').val(id);
        $('#fundCluster').val(cluster);
        $('#totalAmount').val(total);
        $('#allocatedNote').text(`Already allocated: ₱${parseFloat(allocated).toLocaleString('en-PH', {minimumFractionDigits: 2})}`);
        $errors.addClass('hidden');
        toggleModal($modal, true);
    });

    $('.delete-allocation').on('click', function() {
        const id = $(this).data('id');
        const cluster = $(this).data('cluster');

        if (!confirm(`Delete fund allocation "${cluster}"?\n\nThis action cannot be undone.`)) {
            return;
        }

        $.ajax({
            method: 'DELETE',
            url: `/custodian/fund-allocations/${id}`,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (response) => {
                showToast(response.message ?? 'Fund allocation deleted successfully.');
                setTimeout(() => location.reload(), 1200);
            },
            error: (xhr) => {
                alert(xhr.responseJSON?.message || 'Failed to delete allocation.');
            }
        });
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        $errors.addClass('hidden');
        const method = $('#formMethod').val();
        const id = $('#allocationId').val();
        const url = method === 'PUT' ? `/custodian/fund-allocations/${id}` : '/custodian/fund-allocations';

        const submitBtn = $form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');

        $.ajax({
            method: method,
            url: url,
            data: $form.serialize(),
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: (response) => {
                toggleModal($modal, false);
                showToast(response.message ?? 'Fund allocation saved successfully.');
                setTimeout(() => location.reload(), 1200);
            },
            error: (xhr) => {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const messages = Object.values(xhr.responseJSON.errors).flat();
                    $errorsContent.html(messages.map(msg => `<div class="mb-1">• ${msg}</div>`).join(''));
                    $errors.removeClass('hidden');
                } else {
                    $errorsContent.text(xhr.responseJSON?.message || 'An error occurred.');
                    $errors.removeClass('hidden');
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
</script>
@endpush
@endsection
