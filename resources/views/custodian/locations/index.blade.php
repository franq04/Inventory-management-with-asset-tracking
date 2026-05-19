@extends('layouts.app')

@section('title', 'Locations')

@section('content')
@php
    $totalLocations = $totalLocations ?? 0;
    $activeLocations = $activeLocations ?? 0;
@endphp

<style>
    .location-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .location-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 45px -30px rgba(15, 23, 42, 0.65);
    }

    .location-details {
        transition: max-height 0.2s ease, opacity 0.2s ease;
    }

    .location-details.is-open {
        opacity: 1;
    }
</style>

<div class="space-y-7">
    <section class="animate-card relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Location Registry
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Locations</h2>
                <p class="text-sm text-white/75">Maintain parent locations and nested sublocations used across inventory workflows.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-2 text-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/70">Total</p>
                    <p class="text-lg font-semibold">{{ number_format($totalLocations) }}</p>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-2 text-sm">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/70">Active</p>
                    <p class="text-lg font-semibold">{{ number_format($activeLocations) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="animate-card rounded-[26px] border border-emerald-950/8 bg-white/95 p-5 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
        <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-[#1a3a2d]">Parent Location Cards</h3>
                <p class="text-sm text-gray-500">Click a card to reveal its detail tree.</p>
            </div>
            <button type="button" data-open-location-registry-modal class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#1a3a2d] px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#204835] hover:shadow-lg sm:w-auto">
                <i class="fas fa-plus-circle"></i>
                New Location
            </button>
        </div>

        @if($locationCards->isEmpty())
            <div class="rounded-2xl border border-emerald-950/10 bg-[#f8fbf9] px-5 py-10 text-center text-sm text-gray-500">
                No locations found yet. Add your first location to get started.
            </div>
        @else
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($locationCards as $location)
                    @php
                        $payload = $locationPayloads[$location->location_id] ?? null;
                        $locationCode = $location->location_code ? ' (' . $location->location_code . ')' : '';
                    @endphp
                    <article class="location-card group cursor-pointer rounded-3xl border border-emerald-950/10 bg-white p-5 shadow-sm" data-location-card>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700/70">Parent Location</p>
                                <h4 class="mt-1 text-lg font-bold text-gray-900">{{ $location->location_name }}{{ $locationCode }}</h4>
                                <p class="text-xs text-gray-500">Type: {{ ucfirst($location->location_type) }} @if(!$location->is_active) • Inactive @endif</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                                {{ $location->children->count() }} sublocations
                            </span>
                        </div>

                        <div class="location-details mt-4 hidden opacity-0">
                            <div class="rounded-2xl border border-emerald-950/10 bg-[#f8fbf9] p-4 text-sm text-gray-600">
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/70">Details</p>
                                <p class="mt-2 text-sm text-gray-700">{{ $location->description ?: 'No description provided.' }}</p>
                            </div>

                            <div class="mt-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Sublocations</p>
                                    <button type="button" data-open-location-registry-modal data-location-registry-parent="{{ $location->location_id }}" data-location-registry-lock-parent="true" data-no-toggle="true" class="inline-flex items-center gap-2 rounded-lg border border-emerald-100 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                        <i class="fas fa-plus"></i>
                                        Add sublocation
                                    </button>
                                </div>

                                @if($location->children->isEmpty())
                                    <div class="rounded-xl border border-dashed border-emerald-200 bg-white px-4 py-3 text-xs text-gray-500">
                                        No sublocations recorded yet.
                                    </div>
                                @else
                                    @include('custodian.locations.partials._child-list', [
                                        'children' => $location->children,
                                        'locationPayloads' => $locationPayloads,
                                    ])
                                @endif
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-2" data-no-toggle="true">
                                <button type="button" data-location-edit data-location='@json($payload)' data-no-toggle="true" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-pen"></i>
                                    Edit
                                </button>
                                <button type="button" data-location-delete data-location='@json($payload)' data-has-children="{{ $location->children->isNotEmpty() ? '1' : '0' }}" data-no-toggle="true" class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>

@include('inventory.locations.partials._add-modal', [
    'locationRegistryParents' => $locationParents,
    'locationRegistryStoreRoute' => route('custodian.locations.store'),
])

<div id="locationEditModal" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-900/55 p-4 opacity-0 transition duration-300" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="locationEditTitle">
    <div class="modal-panel w-full max-w-2xl rounded-2xl border border-emerald-950/10 bg-white opacity-0 scale-95 translate-y-2 shadow-2xl transition duration-300">
        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
            <div>
                <h3 id="locationEditTitle" class="text-lg font-semibold text-[#1a3a2d]">Edit Location</h3>
                <p class="mt-1 text-sm text-gray-600">Update location details and hierarchy.</p>
            </div>
            <button type="button" data-close-location-edit-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <form id="locationEditForm" class="space-y-4 px-6 py-5">
            @csrf
            <input type="hidden" name="location_id" id="locationEditId">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="locationEditName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Location Name</label>
                    <input id="locationEditName" name="location_name" type="text" maxlength="255" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm" required>
                    <p data-edit-error-for="location_name" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div>
                    <label for="locationEditCode" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Location Code</label>
                    <input id="locationEditCode" name="location_code" type="text" maxlength="64" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm" placeholder="Optional">
                    <p data-edit-error-for="location_code" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div>
                    <label for="locationEditType" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Location Type</label>
                    <select id="locationEditType" name="location_type" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm">
                        <option value="room">Room</option>
                        <option value="building">Building</option>
                        <option value="floor">Floor</option>
                        <option value="storage">Storage</option>
                        <option value="other">Other</option>
                    </select>
                    <p data-edit-error-for="location_type" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="locationEditParent" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Parent Location</label>
                    <select id="locationEditParent" name="parent_location_id" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm">
                        <option value="">No parent</option>
                        @foreach($locationParents as $parent)
                            <option value="{{ $parent->location_id }}">{{ $parent->location_name }}{{ $parent->location_code ? ' (' . $parent->location_code . ')' : '' }}</option>
                        @endforeach
                    </select>
                    <p data-edit-error-for="parent_location_id" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="locationEditDescription" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Description</label>
                    <textarea id="locationEditDescription" name="description" rows="3" class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm"></textarea>
                    <p data-edit-error-for="description" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div class="md:col-span-2 flex items-center gap-3">
                    <input id="locationEditActive" name="is_active" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="locationEditActive" class="text-sm text-gray-600">Active location</label>
                    <p data-edit-error-for="is_active" class="ml-auto hidden text-xs font-medium text-rose-700"></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button id="locationEditSubmit" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#214f3d]">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
                <button type="button" data-close-location-edit-modal class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="locationDeleteModal" class="fixed inset-0 z-[95] hidden items-center justify-center bg-slate-900/55 p-4 opacity-0 transition duration-300" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="locationDeleteTitle">
    <div class="modal-panel w-full max-w-md rounded-2xl border border-rose-200 bg-white opacity-0 scale-95 translate-y-2 shadow-2xl transition duration-300">
        <div class="border-b border-rose-100 px-6 py-4">
            <h3 id="locationDeleteTitle" class="text-lg font-semibold text-rose-700">Delete Location</h3>
            <p class="mt-1 text-sm text-rose-600">This will deactivate the selected location.</p>
        </div>
        <div class="px-6 py-5 text-sm text-gray-600">
            <p id="locationDeletePrompt">Are you sure you want to delete this location?</p>
            <p id="locationDeleteWarning" class="mt-2 hidden text-xs text-rose-600">Deactivate sublocations first before deleting this location.</p>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-rose-100 px-6 py-4">
            <button type="button" data-close-location-delete-modal class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="locationDeleteSubmit" type="button" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const cardNodes = Array.from(document.querySelectorAll('[data-location-card]'));
    const editModal = document.getElementById('locationEditModal');
    const deleteModal = document.getElementById('locationDeleteModal');
    const editForm = document.getElementById('locationEditForm');
    const editSubmit = document.getElementById('locationEditSubmit');
    const deleteSubmit = document.getElementById('locationDeleteSubmit');
    const deletePrompt = document.getElementById('locationDeletePrompt');
    const deleteWarning = document.getElementById('locationDeleteWarning');
    const editErrors = editForm ? Array.from(editForm.querySelectorAll('[data-edit-error-for]')) : [];

    let activeDeletePayload = null;

    const showToast = (text, type = 'success') => {
        const message = String(text || '').trim();
        if (!message) {
            return;
        }

        if (typeof window.pqsShowToast === 'function') {
            window.pqsShowToast(message, type === 'error' ? 'error' : 'success');
            return;
        }

        if (window.alertify) {
            if (type === 'error' && typeof window.alertify.error === 'function') {
                window.alertify.error(message);
                return;
            }
            if (type !== 'error' && typeof window.alertify.success === 'function') {
                window.alertify.success(message);
            }
        }
    };

    const toggleModal = (modal, isOpen) => {
        if (!modal) {
            return;
        }

        const panel = modal.querySelector('.modal-panel');
        if (isOpen) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    modal.classList.remove('opacity-0');
                    panel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                });
            });
            document.body.classList.add('overflow-hidden');
            return;
        }

        modal.classList.add('opacity-0');
        panel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }, 300);
    };

    const resetEditErrors = () => {
        editErrors.forEach((node) => {
            node.textContent = '';
            node.classList.add('hidden');
        });
    };

    const setEditLoading = (isLoading) => {
        if (!editSubmit) {
            return;
        }

        if (isLoading) {
            editSubmit.dataset.originalText = editSubmit.innerHTML;
            editSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            editSubmit.disabled = true;
            return;
        }

        if (editSubmit.dataset.originalText) {
            editSubmit.innerHTML = editSubmit.dataset.originalText;
            delete editSubmit.dataset.originalText;
        }
        editSubmit.disabled = false;
    };

    cardNodes.forEach((card) => {
        card.addEventListener('click', (event) => {
            if (event.target.closest('[data-no-toggle]')) {
                return;
            }

            const details = card.querySelector('.location-details');
            if (!details) {
                return;
            }

            const isOpen = !details.classList.contains('hidden');
            if (isOpen) {
                details.classList.add('hidden');
                details.classList.remove('is-open');
                return;
            }

            details.classList.remove('hidden');
            requestAnimationFrame(() => details.classList.add('is-open'));
        });
    });

    document.addEventListener('click', (event) => {
        const editButton = event.target.closest('[data-location-edit]');
        if (editButton) {
            const payload = editButton.dataset.location ? JSON.parse(editButton.dataset.location) : null;
            if (!payload || !editForm) {
                return;
            }

            resetEditErrors();
            editForm.reset();

            editForm.querySelector('#locationEditId').value = payload.location_id || '';
            editForm.querySelector('#locationEditName').value = payload.location_name || '';
            editForm.querySelector('#locationEditCode').value = payload.location_code || '';
            editForm.querySelector('#locationEditType').value = payload.location_type || 'room';
            editForm.querySelector('#locationEditParent').value = payload.parent_location_id || '';
            editForm.querySelector('#locationEditDescription').value = payload.description || '';
            editForm.querySelector('#locationEditActive').checked = Boolean(payload.is_active);

            toggleModal(editModal, true);
            return;
        }

        const deleteButton = event.target.closest('[data-location-delete]');
        if (deleteButton) {
            const payload = deleteButton.dataset.location ? JSON.parse(deleteButton.dataset.location) : null;
            if (!payload || !deleteModal) {
                return;
            }

            activeDeletePayload = payload;
            const hasChildren = deleteButton.dataset.hasChildren === '1';
            deletePrompt.textContent = `Delete ${payload.location_name || 'this location'}?`;
            deleteWarning.classList.toggle('hidden', !hasChildren);

            toggleModal(deleteModal, true);
        }
    });

    document.querySelectorAll('[data-close-location-edit-modal]').forEach((button) => {
        button.addEventListener('click', () => toggleModal(editModal, false));
    });

    document.querySelectorAll('[data-close-location-delete-modal]').forEach((button) => {
        button.addEventListener('click', () => toggleModal(deleteModal, false));
    });

    editModal?.addEventListener('click', (event) => {
        if (event.target === editModal) {
            toggleModal(editModal, false);
        }
    });

    deleteModal?.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            toggleModal(deleteModal, false);
        }
    });

    editForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        resetEditErrors();
        setEditLoading(true);

        const locationId = editForm.querySelector('#locationEditId').value;
        const updateUrl = "{{ route('custodian.locations.update', ['location' => '__location__']) }}".replace('__location__', locationId || '0');
        const formData = new FormData(editForm);

        if (!formData.get('is_active')) {
            formData.set('is_active', '0');
        }

        try {
            const response = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-HTTP-Method-Override': 'PUT',
                },
                body: formData,
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                if (payload?.errors && typeof payload.errors === 'object') {
                    Object.entries(payload.errors).forEach(([field, messages]) => {
                        const errorNode = editForm.querySelector(`[data-edit-error-for="${field}"]`);
                        if (!errorNode) {
                            return;
                        }

                        errorNode.textContent = Array.isArray(messages) ? (messages[0] || '') : String(messages || '');
                        errorNode.classList.remove('hidden');
                    });
                }

                showToast(payload?.message || 'Unable to update location.', 'error');
                return;
            }

            showToast(payload?.message || 'Location updated successfully.');
            window.location.reload();
        } catch (error) {
            showToast('Network error while updating location. Please try again.', 'error');
        } finally {
            setEditLoading(false);
        }
    });

    deleteSubmit?.addEventListener('click', async () => {
        if (!activeDeletePayload) {
            return;
        }

        const deleteUrl = "{{ route('custodian.locations.destroy', ['location' => '__location__']) }}".replace('__location__', activeDeletePayload.location_id || '0');

        try {
            const response = await fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-HTTP-Method-Override': 'DELETE',
                },
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                showToast(payload?.message || 'Unable to delete location.', 'error');
                return;
            }

            showToast(payload?.message || 'Location deleted successfully.');
            window.location.reload();
        } catch (error) {
            showToast('Network error while deleting location. Please try again.', 'error');
        }
    });

    document.addEventListener('location-registry:created', () => {
        window.location.reload();
    });
})();
</script>
@endpush

@endsection
