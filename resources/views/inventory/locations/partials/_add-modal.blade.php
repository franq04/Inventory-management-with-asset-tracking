<div id="locationRegistryModal" class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-900/55 p-4 opacity-0 transition duration-300" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="locationRegistryModalTitle">
    <div class="modal-panel w-full max-w-2xl rounded-2xl border border-emerald-950/10 bg-white opacity-0 scale-95 translate-y-2 shadow-2xl transition duration-300">
        <div class="flex items-start justify-between border-b border-gray-200 px-6 py-4">
            <div>
                <h3 id="locationRegistryModalTitle" class="text-lg font-semibold text-[#1a3a2d]">Add Physical Location</h3>
                <p class="mt-1 text-sm text-gray-600">Create a location once and reuse it across PQS, transfers, and movement workflows.</p>
            </div>
            <button type="button" data-close-location-registry-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <form id="locationRegistryForm" action="{{ $locationRegistryStoreRoute ?? route('physical_locations.store') }}" method="POST" class="space-y-4 px-6 py-5">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="locationRegistryName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">New Location Name</label>
                    <input id="locationRegistryName" name="location_name" type="text" maxlength="255" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm" placeholder="e.g. Building 1 - 2nd Floor" required>
                    <p data-location-error-for="location_name" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="locationRegistryType" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Location Type</label>
                    <select id="locationRegistryType" name="location_type" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm">
                        <option value="room">Room</option>
                        <option value="building">Building</option>
                        <option value="floor">Floor</option>
                        <option value="storage">Storage</option>
                        <option value="other">Other</option>
                    </select>
                    <p data-location-error-for="location_type" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="locationRegistryParent" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Parent Location</label>
                    <select id="locationRegistryParent" name="parent_location_id" class="mt-1 h-11 w-full rounded-xl border border-gray-200 px-3 text-sm">
                        <option value="">No parent</option>
                        @foreach(($locationRegistryParents ?? collect()) as $parent)
                            <option value="{{ $parent->location_id }}">{{ $parent->location_name }}{{ $parent->location_code ? ' (' . $parent->location_code . ')' : '' }}</option>
                        @endforeach
                    </select>
                    <p data-location-error-for="parent_location_id" class="mt-1 hidden text-xs font-medium text-rose-700"></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button id="locationRegistrySubmit" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#214f3d]">
                    <i class="fas fa-location-dot"></i>
                    Add Location
                </button>
                <button type="button" data-close-location-registry-modal class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@once
    @push('scripts')
        <script>
        (() => {
            const modal = document.getElementById('locationRegistryModal');
            const form = document.getElementById('locationRegistryForm');
            const submitButton = document.getElementById('locationRegistrySubmit');

            if (!modal || !form) {
                return;
            }

            const modalPanel = modal.querySelector('.modal-panel');

            const closeButtons = Array.from(document.querySelectorAll('[data-close-location-registry-modal]'));
            const openButtons = Array.from(document.querySelectorAll('[data-open-location-registry-modal]'));
            const errorNodes = Array.from(form.querySelectorAll('[data-location-error-for]'));
            const locationTypeField = form.querySelector('[name="location_type"]');
            const parentField = form.querySelector('[name="parent_location_id"]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let restoreBodyOverflowOnClose = false;

            const resetErrors = () => {
                errorNodes.forEach((node) => {
                    node.textContent = '';
                    node.classList.add('hidden');
                });
            };

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

            const setSubmitLoading = (isLoading) => {
                if (!submitButton) {
                    return;
                }

                if (isLoading) {
                    submitButton.dataset.originalText = submitButton.innerHTML;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                    submitButton.disabled = true;
                    return;
                }

                if (submitButton.dataset.originalText) {
                    submitButton.innerHTML = submitButton.dataset.originalText;
                    delete submitButton.dataset.originalText;
                }
                submitButton.disabled = false;
            };

            const applyOpenOverrides = (triggerButton = null) => {
                if (!locationTypeField) {
                    return;
                }

                locationTypeField.disabled = false;
                locationTypeField.classList.remove('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');

                if (parentField) {
                    parentField.disabled = false;
                    parentField.classList.remove('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                }

                const forcedType = String(triggerButton?.dataset?.locationRegistryType || '').trim();
                const lockTypeRaw = String(triggerButton?.dataset?.locationRegistryLockType || '').trim().toLowerCase();
                const shouldLockType = lockTypeRaw === '1' || lockTypeRaw === 'true';
                const forcedParent = String(triggerButton?.dataset?.locationRegistryParent || '').trim();
                const lockParentRaw = String(triggerButton?.dataset?.locationRegistryLockParent || '').trim().toLowerCase();
                const shouldLockParent = lockParentRaw === '1' || lockParentRaw === 'true';

                if (forcedType !== '' && Array.from(locationTypeField.options).some((option) => option.value === forcedType)) {
                    locationTypeField.value = forcedType;
                }

                if (shouldLockType) {
                    locationTypeField.disabled = true;
                    locationTypeField.classList.add('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                }

                if (parentField) {
                    if (forcedParent !== '' && Array.from(parentField.options).some((option) => option.value === forcedParent)) {
                        parentField.value = forcedParent;
                    }

                    if (shouldLockParent) {
                        parentField.disabled = true;
                        parentField.classList.add('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                    }
                }
            };

            const openModal = (triggerButton = null) => {
                resetErrors();

                if (!modal.classList.contains('hidden')) {
                    return;
                }

                form.reset();
                applyOpenOverrides(triggerButton);

                restoreBodyOverflowOnClose = !document.body.classList.contains('overflow-hidden');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        modal.scrollTop = 0;
                        if (modalPanel) {
                            modalPanel.scrollTop = 0;
                            modalPanel.querySelectorAll('.overflow-y-auto, .overflow-y-scroll').forEach((element) => {
                                element.scrollTop = 0;
                            });
                        }

                        modal.classList.remove('opacity-0');
                        modalPanel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                    });
                });
                document.body.classList.add('overflow-hidden');

                setTimeout(() => {
                    const focusTarget = form.querySelector('input, select, textarea');
                    if (focusTarget) {
                        try {
                            focusTarget.focus({ preventScroll: true });
                        } catch (error) {
                            focusTarget.focus();
                        }
                    }

                    modal.scrollTop = 0;
                    if (modalPanel) {
                        modalPanel.scrollTop = 0;
                        modalPanel.querySelectorAll('.overflow-y-auto, .overflow-y-scroll').forEach((element) => {
                            element.scrollTop = 0;
                        });
                    }
                }, 300);
            };

            const closeModal = () => {
                if (modal.classList.contains('hidden')) {
                    return;
                }

                modal.classList.add('opacity-0');
                modalPanel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    if (restoreBodyOverflowOnClose) {
                        document.body.classList.remove('overflow-hidden');
                    }
                    restoreBodyOverflowOnClose = false;
                    form.reset();
                    locationTypeField?.classList.remove('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                    if (locationTypeField) {
                        locationTypeField.disabled = false;
                    }
                    parentField?.classList.remove('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
                    if (parentField) {
                        parentField.disabled = false;
                    }
                    resetErrors();
                }, 300);
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => openModal(button));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                resetErrors();
                setSubmitLoading(true);

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        if (payload?.errors && typeof payload.errors === 'object') {
                            Object.entries(payload.errors).forEach(([field, messages]) => {
                                const errorNode = form.querySelector(`[data-location-error-for="${field}"]`);
                                if (!errorNode) {
                                    return;
                                }

                                errorNode.textContent = Array.isArray(messages) ? (messages[0] || '') : String(messages || '');
                                errorNode.classList.remove('hidden');
                            });
                        }

                        showToast(payload?.message || 'Unable to save location. Please review the fields.', 'error');
                        return;
                    }

                    const createdLocation = payload?.data?.location || null;
                    if (createdLocation) {
                        document.dispatchEvent(new CustomEvent('location-registry:created', {
                            detail: {
                                location: createdLocation,
                                created: Boolean(payload?.data?.created),
                                message: payload?.message || 'Location saved successfully.',
                            },
                        }));
                    }

                    showToast(payload?.message || 'Location saved successfully.', 'success');
                    setTimeout(closeModal, 300);
                } catch (error) {
                    showToast('Network error while saving location. Please try again.', 'error');
                } finally {
                    setSubmitLoading(false);
                }
            });
        })();
        </script>
    @endpush
@endonce
