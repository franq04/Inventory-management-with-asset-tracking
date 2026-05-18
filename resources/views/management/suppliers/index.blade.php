@extends('layouts.app')

@section('title', 'Manage Suppliers')

@section('content')
<div id="suppliersPage" class="space-y-8 animate-card">
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Supplier Registry
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Manage Suppliers</h2>
                <p class="text-sm text-white/75">Maintain supplier contacts used across purchase orders.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="rounded-[26px] border border-emerald-950/8 bg-white/95 p-5 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
        <form method="GET" action="{{ route('suppliers.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 items-end">
                <div>
                    <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Search</label>
                    <div class="relative mt-1">
                        <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                        <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Search supplier name, ID, contact..." class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-3 text-sm shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:ring-4 focus:ring-[#1a3a2d]/10" />
                    </div>
                </div>
                <div class="flex items-center justify-between gap-2 md:justify-end">
                    <span class="text-sm text-gray-500">{{ number_format($suppliers->total()) }} records found</span>
                    <a href="{{ route('suppliers.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-[#f5f8f6]">
                    <tr class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-[0.18em]">
                        <th class="px-6 py-3">Supplier</th>
                        <th class="px-6 py-3">Contact</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Address</th>
                        <th class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $supplier->supplier_name }}</div>
                                <div class="text-xs text-gray-500 font-mono">ID: {{ $supplier->supplier_id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $supplier->contact_person ?: '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $supplier->contact_no ?: '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $supplier->email ?: '—' }}</td>
                            <td class="px-6 py-4 text-gray-600 max-w-xs truncate" title="{{ $supplier->address }}">{{ $supplier->address ?: '—' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="js-edit-supplier inline-flex items-center gap-2 rounded-lg border border-emerald-950/15 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-[#1a3a2d] shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-900/30 hover:bg-emerald-100 hover:shadow"
                                        data-update-url="{{ route('suppliers.update', $supplier) }}"
                                        data-supplier-id="{{ $supplier->supplier_id }}"
                                        data-supplier-name="{{ $supplier->supplier_name }}"
                                        data-supplier-address="{{ $supplier->address }}"
                                        data-supplier-contact-person="{{ $supplier->contact_person }}"
                                        data-supplier-contact-no="{{ $supplier->contact_no }}"
                                        data-supplier-email="{{ $supplier->email }}"
                                    >
                                        <i class="fas fa-pen-to-square text-[11px]"></i>
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-300 hover:bg-red-100 hover:shadow">
                                            <i class="fas fa-trash text-[11px]"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                <i class="fas fa-truck text-4xl text-gray-300"></i>
                                <p class="mt-3 font-medium">No suppliers found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $suppliers->onEachSide(1)->links('vendor.pagination.procurement') }}
            </div>
        @endif
    </div>
</div>

<div id="supplierEditModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="supplierEditTitle">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-supplier-modal></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="modal-panel relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between rounded-t-2xl bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-5 py-4 text-white">
                <div>
                    <h3 id="supplierEditTitle" class="text-lg font-bold tracking-tight">Edit Supplier</h3>
                    <p class="text-xs text-white/80">Update supplier contact details.</p>
                </div>
                <button type="button" class="rounded-lg p-2 text-white/80 transition-all hover:bg-white/10 hover:text-white" data-close-supplier-modal data-supplier-modal-focus>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="supplierEditForm" method="POST" class="space-y-4 p-5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="supplierEditId" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Supplier ID</label>
                        <input id="supplierEditId" name="supplier_id" type="text" readonly class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:outline-none" />
                    </div>
                    <div>
                        <label for="supplierEditName" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Supplier Name</label>
                        <input id="supplierEditName" name="supplier_name" type="text" required class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10" />
                    </div>
                </div>

                <div>
                    <label for="supplierEditAddress" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Address</label>
                    <input id="supplierEditAddress" name="address" type="text" class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="supplierEditContactPerson" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Contact Person</label>
                        <input id="supplierEditContactPerson" name="contact_person" type="text" class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10" />
                    </div>
                    <div>
                        <label for="supplierEditContactNo" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Contact No</label>
                        <input id="supplierEditContactNo" name="contact_no" type="text" class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10" />
                    </div>
                </div>

                <div>
                    <label for="supplierEditEmail" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Email</label>
                    <input id="supplierEditEmail" name="email" type="email" class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10" />
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                    <button type="button" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" data-close-supplier-modal>
                        Close
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#204835]">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('supplierEditModal');
        const modalPanel = modal?.querySelector('.modal-panel') ?? null;
        const modalFocus = modal?.querySelector('[data-supplier-modal-focus]') ?? null;
        const form = document.getElementById('supplierEditForm');
        const idInput = document.getElementById('supplierEditId');
        const nameInput = document.getElementById('supplierEditName');
        const addressInput = document.getElementById('supplierEditAddress');
        const contactPersonInput = document.getElementById('supplierEditContactPerson');
        const contactNoInput = document.getElementById('supplierEditContactNo');
        const emailInput = document.getElementById('supplierEditEmail');

        const openModal = (button) => {
            if (!modal || !form) return;

            form.setAttribute('action', button.dataset.updateUrl || '');
            idInput.value = button.dataset.supplierId || '';
            nameInput.value = button.dataset.supplierName || '';
            addressInput.value = button.dataset.supplierAddress || '';
            contactPersonInput.value = button.dataset.supplierContactPerson || '';
            contactNoInput.value = button.dataset.supplierContactNo || '';
            emailInput.value = button.dataset.supplierEmail || '';

            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                modal.classList.add('opacity-100');
                modalPanel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
            });
            document.body.classList.add('overflow-hidden');
            modalFocus?.focus();
        };

        const closeModal = () => {
            if (!modal) return;
            modal.classList.remove('opacity-100');
            modalPanel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
            window.setTimeout(() => {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 200);
        };

        document.querySelectorAll('.js-edit-supplier').forEach((button) => {
            button.addEventListener('click', () => openModal(button));
        });

        modal?.querySelectorAll('[data-close-supplier-modal]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        modal?.addEventListener('click', (event) => {
            if (event.target instanceof HTMLElement && event.target.hasAttribute('data-close-supplier-modal')) {
                closeModal();
            }
        });
    })();
</script>
@endpush
