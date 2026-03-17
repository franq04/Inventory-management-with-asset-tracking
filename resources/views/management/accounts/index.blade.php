@extends('layouts.app')

@section('title', 'Manage Accounts')

@section('content')
<div id="accountsPage" class="space-y-8 animate-card">
    <div class="relative overflow-hidden rounded-[28px] border border-emerald-950/10 bg-gradient-to-br from-[#173628] via-[#1a3a2d] to-[#285641] px-6 py-7 text-white shadow-[0_20px_60px_-25px_rgba(26,58,45,0.65)] sm:px-8 lg:px-10">
        <div class="absolute inset-y-0 right-0 w-1/2 bg-[radial-gradient(circle_at_top_right,_rgba(249,191,15,0.16),_transparent_58%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/80">
                    <span class="h-2 w-2 rounded-full bg-[#f9bf0f]"></span>
                    Access Control
                </span>
                <h2 class="text-3xl font-extrabold tracking-tight">Manage Accounts</h2>
                <p class="text-sm text-white/75">Oversee user roles, assignments, and system access.</p>
            </div>

            <div id="accountsKpiCards" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4 lg:min-w-[700px]">
        @php
            $statCards = [
                ['label' => 'Total Accounts', 'value' => $stats['total'], 'icon' => 'fa-users', 'color' => 'blue'],
                ['label' => 'Linked To Employees', 'value' => $stats['withEmployee'], 'icon' => 'fa-link', 'color' => 'emerald'],
                ['label' => 'Unassigned Accounts', 'value' => $stats['withoutEmployee'], 'icon' => 'fa-user-slash', 'color' => 'amber'],
                ['label' => 'Roles Covered', 'value' => $roleOptions->count(), 'icon' => 'fa-tags', 'color' => 'purple'],
            ];
            $colors = [
                'blue' => 'bg-blue-300/20 text-blue-100',
                'emerald' => 'bg-emerald-300/20 text-emerald-100',
                'amber' => 'bg-amber-300/20 text-amber-100',
                'purple' => 'bg-violet-300/20 text-violet-100',
            ];
        @endphp
        @foreach ($statCards as $card)
        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full {{ $colors[$card['color']] }}">
                    <i class="fa-solid {{ $card['icon'] }} text-base"></i>
                </div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">{{ $card['label'] }}</p>
            </div>
            <p class="mt-3 text-3xl font-bold">{{ number_format($card['value']) }}</p>
        </div>
        @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Content Column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Enhanced Filters & Actions --}}
            <div class="relative z-40 overflow-visible rounded-[26px] border border-emerald-950/8 bg-white/95 p-5 shadow-[0_24px_60px_-35px_rgba(15,23,42,0.42)] backdrop-blur">
                <form id="accountsFiltersForm" method="GET" class="space-y-4" action="{{ route('accounts.index') }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 items-end">
                        <div>
                            <label for="search" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Search</label>
                            <div class="relative mt-1">
                                <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#2d5a4a]/45"></i>
                                <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Prefix search: username, employee, account ID..."
                                       class="h-11 w-full rounded-2xl border border-emerald-950/10 bg-[#f7faf8] pl-11 pr-3 text-sm shadow-inner shadow-emerald-950/5 focus:border-[#1a3a2d] focus:bg-white focus:ring-4 focus:ring-[#1a3a2d]/10" />
                            </div>
                        </div>

                        <div id="accountsRoleDropdown" class="relative z-[70]">
                            @php
                                $selectedRoleValue = $roleFilter && $roleFilter !== '' ? $roleFilter : 'all';
                                $selectedRoleLabel = $selectedRoleValue === 'all' ? 'All Roles' : ucfirst(str_replace('_', ' ', $selectedRoleValue));
                            @endphp
                            <label for="role" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Role</label>
                            <input type="hidden" name="role" id="role" value="{{ $selectedRoleValue }}">

                            <button id="accountsRoleDropdownToggle" type="button" class="mt-1 flex w-full items-center justify-between rounded-2xl border border-emerald-950/10 bg-white px-3 py-[9px] text-sm shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-1 focus:ring-[#1a3a2d]/50" aria-haspopup="listbox" aria-expanded="false">
                                <span id="accountsRoleDropdownLabel" class="block truncate text-gray-700">{{ $selectedRoleLabel }}</span>
                                <i id="accountsRoleDropdownChevron" class="fas fa-chevron-down text-[#2d5a4a]/45 text-xs transition-transform duration-200"></i>
                            </button>

                            <div id="accountsRoleDropdownMenu" class="absolute z-[90] mt-2 hidden w-full origin-top-right rounded-2xl border border-gray-100 bg-white opacity-100 shadow-[0_8px_30px_rgb(0,0,0,0.12)] ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="border-b border-gray-50 px-3 pb-2 pt-3">
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input id="accountsRoleDropdownSearch" type="text" placeholder="Search prefix..." class="w-full rounded-xl border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-emerald-500 focus:bg-white focus:ring-1 focus:ring-emerald-500" autocomplete="off">
                                    </div>
                                </div>
                                <ul id="accountsRoleDropdownOptions" class="custom-scrollbar max-h-56 overflow-y-auto py-1" role="listbox">
                                    <li class="account-role-option relative cursor-pointer select-none py-2 pl-4 pr-9 text-sm text-gray-700 transition-colors hover:bg-emerald-50/80 {{ $selectedRoleValue === 'all' ? 'bg-emerald-50/50 font-medium text-[#1a3a2d]' : '' }}" data-value="all" data-name="All Roles" role="option" aria-selected="{{ $selectedRoleValue === 'all' ? 'true' : 'false' }}">
                                        <span class="block truncate">All Roles</span>
                                        <span class="account-role-check absolute inset-y-0 right-0 flex items-center pr-4 text-[#1a3a2d] {{ $selectedRoleValue === 'all' ? '' : 'hidden' }}">
                                            <i class="fas fa-check text-xs"></i>
                                        </span>
                                    </li>
                                    @foreach ($roleOptions as $role)
                                        @php
                                            $roleName = ucfirst(str_replace('_', ' ', $role));
                                            $isSelectedRole = $selectedRoleValue === $role;
                                        @endphp
                                        <li class="account-role-option relative cursor-pointer select-none py-2 pl-4 pr-9 text-sm text-gray-700 transition-colors hover:bg-emerald-50/80 {{ $isSelectedRole ? 'bg-emerald-50/50 font-medium text-[#1a3a2d]' : '' }}" data-value="{{ $role }}" data-name="{{ $roleName }}" role="option" aria-selected="{{ $isSelectedRole ? 'true' : 'false' }}">
                                            <span class="block truncate">{{ $roleName }}</span>
                                            <span class="account-role-check absolute inset-y-0 right-0 flex items-center pr-4 text-[#1a3a2d] {{ $isSelectedRole ? '' : 'hidden' }}">
                                                <i class="fas fa-check text-xs"></i>
                                            </span>
                                        </li>
                                    @endforeach
                                    <li id="accountsRoleDropdownEmpty" class="hidden px-4 py-3 text-center text-sm italic text-gray-500">No roles found</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-100 pt-4 sm:flex-row">
                        <span id="accountsTotalCount" class="text-sm text-gray-500">{{ number_format($accounts->total()) }} records found</span>
                        <div class="flex flex-wrap items-center gap-2">
                            <a id="accountsResetFilters" href="{{ route('accounts.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#1a3a2d]/20 bg-white text-[#1a3a2d] shadow-sm transition hover:bg-[#1a3a2d] hover:text-white" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                            <button type="button" id="accountsPrintPdfBtn" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                <i class="fas fa-file-pdf text-rose-600"></i> Print PDF
                            </button>
                            <button type="button" id="accountsExportExcelBtn" class="inline-flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-all hover:border-[#1a3a2d]/20 hover:bg-[#f7faf8] hover:text-[#1a3a2d]">
                                <i class="fas fa-file-excel text-green-600"></i> Export Excel
                            </button>
                        </div>
                    </div>
                    <button type="submit" data-no-global-loading="true" class="sr-only">Submit</button>
                </form>
            </div>

            {{-- Accounts Table (AJAX-updatable partial) --}}
            <div id="accountsTableWrapper">
                @include('management.accounts._table')
            </div>
        </div>

        {{-- Side Column --}}
        <div class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Role Distribution</h3>
                <div class="mt-4 space-y-4">
                    @forelse ($stats['roles'] as $role => $count)
                        @php
                            $percentage = ($stats['total'] > 0) ? ($count / $stats['total']) * 100 : 0;
                            $roleColorClass = match(strtolower($role)) {
                                'custodian' => 'bg-blue-500',
                                'iac' => 'bg-purple-500',
                                'employee' => 'bg-emerald-500',
                                'division_head' => 'bg-indigo-500',
                                default => 'bg-gray-500',
                            };
                        @endphp
                        <div>
                            <div class="flex justify-between mb-1 text-sm">
                                <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                <span class="text-gray-500">{{ number_format($count) }} Users</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $roleColorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No role data available.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">Recently Added</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentAccounts as $recent)
                        @php
                            $recentEmployee = $recent->employee;
                            $recentName = $recentEmployee ? trim(($recentEmployee->first_name ?? '') . ' ' . ($recentEmployee->last_name ?? '')) : 'Unassigned';
                            $initial = strtoupper(substr($recent->username, 0, 1));
                             $roleColor = match(strtolower($recent->role)) {
                                'custodian' => 'bg-blue-100 text-blue-700',
                                'iac' => 'bg-purple-100 text-purple-700',
                                'employee' => 'bg-emerald-100 text-emerald-700',
                                'division_head' => 'bg-indigo-100 text-indigo-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <li class="flex items-center gap-4">
                             <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center {{ $roleColor }} font-bold">
                                {{ $initial }}
                            </div>
                            <div class="flex-1 text-sm">
                                <p class="font-semibold text-gray-800">{{ $recent->username }}</p>
                                <p class="text-xs text-gray-500">{{ $recentName }}</p>
                            </div>
                            <span class="text-xs font-semibold uppercase text-gray-500" title="{{ optional($recent->created_at)->format('M d, Y H:i') }}">{{ optional($recent->created_at)->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 py-4 text-center">No recent accounts to show.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div id="accountEditModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" aria-labelledby="accountEditModalTitle" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-close-account-edit-modal></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="modal-panel relative w-full max-w-lg rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2">
            <div class="flex items-center justify-between rounded-t-2xl bg-gradient-to-r from-[#1a3a2d] to-[#285641] px-5 py-4 text-white">
                <h3 id="accountEditModalTitle" class="text-lg font-bold tracking-tight">Edit Account</h3>
                <button type="button" class="rounded-lg p-2 text-white/80 transition-all hover:bg-white/10 hover:text-white" data-close-account-edit-modal data-account-edit-focus>
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="accountEditForm" class="space-y-4 p-5">
                <input type="hidden" id="accountEditId" name="account_id">

                <div>
                    <label for="accountEditUsername" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Username</label>
                    <input id="accountEditUsername" name="username" type="text" readonly
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:outline-none">
                </div>

                <div>
                    <label for="accountEditRole" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Role</label>
                    <select id="accountEditRole" name="role"
                        class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10">
                        @foreach ($roleOptions as $role)
                            <option value="{{ $role }}">{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                    <p id="accountEditRoleError" class="mt-1 hidden text-xs font-medium text-red-600"></p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="accountEditPassword" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">New Password</label>
                        <div class="relative">
                            <input id="accountEditPassword" name="password" type="password" autocomplete="new-password"
                                class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 pr-10 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10"
                                placeholder="Leave blank to keep current">
                            <button type="button" id="accountEditPasswordToggle" data-target-input="accountEditPassword" aria-label="Show password" class="absolute inset-y-0 right-2 inline-flex items-center rounded-lg px-2 text-gray-500 transition hover:text-[#1a3a2d]">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="accountEditPasswordConfirm" class="mb-1 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Confirm Password</label>
                        <div class="relative">
                            <input id="accountEditPasswordConfirm" name="password_confirmation" type="password" autocomplete="new-password"
                                class="w-full rounded-xl border border-emerald-950/15 bg-white px-3 py-2.5 pr-10 text-sm text-gray-700 shadow-sm focus:border-[#1a3a2d] focus:outline-none focus:ring-4 focus:ring-[#1a3a2d]/10"
                                placeholder="Re-enter password">
                            <button type="button" id="accountEditPasswordConfirmToggle" data-target-input="accountEditPasswordConfirm" aria-label="Show password" class="absolute inset-y-0 right-2 inline-flex items-center rounded-lg px-2 text-gray-500 transition hover:text-[#1a3a2d]">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <p id="accountEditPasswordError" class="-mt-1 hidden text-xs font-medium text-red-600"></p>

                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <i class="fas fa-circle-info mr-1"></i>
                    Role and password updates apply immediately. Use changes carefully for active user sessions.
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                    <button type="button" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50" data-close-account-edit-modal>
                        Close
                    </button>
                    <button type="submit" id="accountEditSaveBtn" data-loading-text="Saving account changes..." class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-4 py-2 text-sm font-semibold text-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#204835] disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="accountsToast" class="pointer-events-none fixed right-4 top-20 z-[100] hidden min-w-[260px] max-w-md rounded-xl border border-emerald-800 bg-[#1a3a2d] px-5 py-3 text-sm font-semibold text-white shadow-2xl"></div>
@endsection

@push('scripts')
<script>
    (() => {
        const wrapper = document.getElementById('accountsTableWrapper');
        const form = document.querySelector('#accountsPage form');
        const searchInput = document.getElementById('search');
        const roleInput = document.getElementById('role');
        const roleDropdown = document.getElementById('accountsRoleDropdown');
        const roleToggle = document.getElementById('accountsRoleDropdownToggle');
        const roleMenu = document.getElementById('accountsRoleDropdownMenu');
        const roleLabel = document.getElementById('accountsRoleDropdownLabel');
        const roleChevron = document.getElementById('accountsRoleDropdownChevron');
        const roleSearchInput = document.getElementById('accountsRoleDropdownSearch');
        const roleEmpty = document.getElementById('accountsRoleDropdownEmpty');
        const roleOptions = Array.from(document.querySelectorAll('.account-role-option'));
        const resetLink = document.getElementById('accountsResetFilters');
        const totalCount = document.getElementById('accountsTotalCount');
        const editModal = document.getElementById('accountEditModal');
        const editModalPanel = editModal?.querySelector('.modal-panel') ?? null;
        const editCloseTarget = editModal?.querySelector('[data-account-edit-focus]') ?? null;
        const editForm = document.getElementById('accountEditForm');
        const editIdInput = document.getElementById('accountEditId');
        const editUsernameInput = document.getElementById('accountEditUsername');
        const editRoleSelect = document.getElementById('accountEditRole');
        const editRoleError = document.getElementById('accountEditRoleError');
        const editPasswordInput = document.getElementById('accountEditPassword');
        const editPasswordConfirmInput = document.getElementById('accountEditPasswordConfirm');
        const passwordToggleButtons = Array.from(document.querySelectorAll('[data-target-input]'));
        const editPasswordError = document.getElementById('accountEditPasswordError');
        const editSaveBtn = document.getElementById('accountEditSaveBtn');
        const toast = document.getElementById('accountsToast');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (!wrapper || !form) {
            return;
        }

        let debounceTimer = null;
        let activeController = null;
        let toastTimer = null;

        const showToast = (message, type = 'success') => {
            if (!toast || !message) {
                return;
            }

            toast.textContent = message;
            toast.classList.remove('hidden', 'border-emerald-800', 'bg-[#1a3a2d]', 'border-red-800', 'bg-red-700');

            if (type === 'error') {
                toast.classList.add('border-red-800', 'bg-red-700');
            } else {
                toast.classList.add('border-emerald-800', 'bg-[#1a3a2d]');
            }

            if (toastTimer) {
                clearTimeout(toastTimer);
            }

            toastTimer = setTimeout(() => {
                toast.classList.add('hidden');
            }, 2600);
        };

        const setLoadingState = (loading) => {
            wrapper.classList.toggle('opacity-60', loading);
            wrapper.classList.toggle('pointer-events-none', loading);

            if (!loading) {
                const existing = document.getElementById('accountsTableSkeleton');
                if (existing) existing.remove();
                return;
            }

            if (document.getElementById('accountsTableSkeleton')) {
                return;
            }

            const skeleton = document.createElement('div');
            skeleton.id = 'accountsTableSkeleton';
            skeleton.className = 'mt-3 rounded-2xl border border-gray-100 bg-white/95 p-5';
            skeleton.innerHTML = `
                <div class="space-y-3">
                    <div class="h-3 w-40 animate-pulse rounded bg-gray-200"></div>
                    <div class="h-10 w-full animate-pulse rounded-xl bg-gray-100"></div>
                    <div class="h-10 w-full animate-pulse rounded-xl bg-gray-100"></div>
                    <div class="h-10 w-full animate-pulse rounded-xl bg-gray-100"></div>
                    <div class="h-10 w-full animate-pulse rounded-xl bg-gray-100"></div>
                </div>
            `;
            wrapper.appendChild(skeleton);
        };

        const animateRows = () => {
            wrapper.querySelectorAll('[data-accounts-row]').forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(6px)';
                row.style.transition = 'opacity 180ms ease, transform 180ms ease';

                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 35);
            });
        };

        const roleBadgeClassMap = {
            custodian: 'bg-blue-100 text-blue-700',
            iac: 'bg-purple-100 text-purple-700',
            employee: 'bg-emerald-100 text-emerald-700',
            division_head: 'bg-indigo-100 text-indigo-700',
        };

        const applyRoleToRow = (accountId, role, pending = false) => {
            const row = wrapper.querySelector(`[data-account-row-id="${accountId}"]`);
            if (!row) {
                return false;
            }

            const normalizedRole = String(role || '').toLowerCase();
            const roleClass = roleBadgeClassMap[normalizedRole] || 'bg-gray-100 text-gray-700';
            const roleLabel = normalizedRole.replace(/_/g, ' ');

            const badge = row.querySelector('[data-account-role-badge]');
            if (badge) {
                badge.className = `inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide ${roleClass}`;
                badge.textContent = roleLabel;
                badge.classList.toggle('opacity-70', pending);
            }

            const editBtn = row.querySelector('[data-account-edit-btn]');
            if (editBtn) {
                editBtn.dataset.accountRole = normalizedRole;
            }

            row.classList.toggle('ring-2', pending);
            row.classList.toggle('ring-emerald-200', pending);
            row.classList.toggle('ring-inset', pending);

            return true;
        };

        const closeRoleDropdown = () => {
            if (!roleMenu || !roleToggle || !roleChevron) {
                return;
            }
            roleMenu.classList.add('hidden');
            roleToggle.setAttribute('aria-expanded', 'false');
            roleChevron.classList.remove('rotate-180');
            if (roleSearchInput) {
                roleSearchInput.value = '';
                filterRoleOptions();
            }
        };

        const openRoleDropdown = () => {
            if (!roleMenu || !roleToggle || !roleChevron) {
                return;
            }
            roleMenu.classList.remove('hidden');
            roleToggle.setAttribute('aria-expanded', 'true');
            roleChevron.classList.add('rotate-180');
            if (roleSearchInput) {
                roleSearchInput.focus();
                roleSearchInput.select();
            }
        };

        const filterRoleOptions = () => {
            if (!roleSearchInput) {
                return;
            }

            const query = roleSearchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            roleOptions.forEach((option) => {
                const name = String(option.dataset.name || '').toLowerCase();
                const isVisible = query === '' || name.startsWith(query);
                option.classList.toggle('hidden', !isVisible);
                if (isVisible) {
                    visibleCount += 1;
                }
            });

            roleEmpty?.classList.toggle('hidden', visibleCount > 0);
        };

        const setSelectedRole = (value, name) => {
            if (!roleInput || !roleLabel) {
                return;
            }

            roleInput.value = value;
            roleLabel.textContent = name;

            roleOptions.forEach((option) => {
                const isSelected = option.dataset.value === value;
                option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                option.classList.toggle('bg-emerald-50/50', isSelected);
                option.classList.toggle('font-medium', isSelected);
                option.classList.toggle('text-[#1a3a2d]', isSelected);

                const check = option.querySelector('.account-role-check');
                check?.classList.toggle('hidden', !isSelected);
            });
        };

        const toggleEditModal = (show) => {
            if (!editModal || !editModalPanel) {
                return;
            }

            if (show) {
                editModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                requestAnimationFrame(() => {
                    editModal.classList.remove('opacity-0');
                    editModal.classList.add('opacity-100');
                    editModalPanel.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                    editCloseTarget?.focus();
                });
                return;
            }

            editModal.classList.remove('opacity-100');
            editModal.classList.add('opacity-0');
            editModalPanel.classList.add('opacity-0', 'scale-95', 'translate-y-2');

            setTimeout(() => {
                editModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        };

        const openEditModalFromButton = (button) => {
            if (!editForm || !editIdInput || !editUsernameInput || !editRoleSelect) {
                return;
            }

            editForm.dataset.actionUrl = button.dataset.accountUpdateUrl || '';
            editForm.dataset.originalRole = button.dataset.accountRole || '';
            editIdInput.value = button.dataset.accountId || '';
            editUsernameInput.value = button.dataset.accountUsername || '';
            editRoleSelect.value = button.dataset.accountRole || '';
            if (editRoleError) {
                editRoleError.textContent = '';
                editRoleError.classList.add('hidden');
            }
            if (editPasswordError) {
                editPasswordError.textContent = '';
                editPasswordError.classList.add('hidden');
            }
            if (editPasswordInput) {
                editPasswordInput.value = '';
            }
            if (editPasswordConfirmInput) {
                editPasswordConfirmInput.value = '';
            }
            toggleEditModal(true);
        };

        const buildUrlFromForm = () => {
            const params = new URLSearchParams(new FormData(form));
            const path = window.location.pathname;
            return `${path}?${params.toString()}`;
        };

        const fetchTable = async (url, push = false) => {
            const target = new URL(url, window.location.origin);
            target.searchParams.set('ajax', '1');

            if (activeController) {
                activeController.abort();
            }

            const controller = new AbortController();
            activeController = controller;
            setLoadingState(true);

            try {
                const res = await fetch(target.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    signal: controller.signal,
                });

                if (!res.ok) {
                    throw new Error(`Accounts fetch failed (status ${res.status})`);
                }

                const data = await res.json();
                wrapper.innerHTML = data.html;

                if (totalCount && typeof data.total !== 'undefined') {
                    totalCount.textContent = `${Number(data.total).toLocaleString('en-PH')} records found`;
                }

                animateRows();

                target.searchParams.delete('ajax');
                const nextUrl = `${target.pathname}${target.search ? target.search : ''}`;
                if (push) {
                    window.history.pushState({}, '', nextUrl);
                } else {
                    window.history.replaceState({}, '', nextUrl);
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    console.error('Failed to fetch accounts table:', err);
                }
            } finally {
                if (activeController === controller) {
                    activeController = null;
                }
                setLoadingState(false);
            }
        };

        const scheduleFetch = (delay = 350) => {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchTable(buildUrlFromForm()), delay);
        };

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchTable(buildUrlFromForm());
        });

        roleToggle?.addEventListener('click', () => {
            const isClosed = roleMenu?.classList.contains('hidden');
            if (isClosed) {
                openRoleDropdown();
            } else {
                closeRoleDropdown();
            }
        });

        roleSearchInput?.addEventListener('input', filterRoleOptions);

        roleOptions.forEach((option) => {
            option.addEventListener('click', () => {
                const nextValue = option.dataset.value || 'all';
                const nextName = option.dataset.name || 'All Roles';
                setSelectedRole(nextValue, nextName);
                closeRoleDropdown();
                scheduleFetch(0);
            });
        });

        searchInput?.addEventListener('input', () => scheduleFetch(350));
        searchInput?.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchTable(buildUrlFromForm());
        });

        resetLink?.addEventListener('click', (e) => {
            e.preventDefault();
            if (searchInput) {
                searchInput.value = '';
            }
            setSelectedRole('all', 'All Roles');
            closeRoleDropdown();
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchTable(resetLink.href, true);
        });

        document.addEventListener('click', (e) => {
            if (!roleDropdown) {
                return;
            }

            const clickedInside = e.target instanceof Element && roleDropdown.contains(e.target);
            if (!clickedInside) {
                closeRoleDropdown();
            }
        });

        wrapper.addEventListener('click', (e) => {
            const link = e.target instanceof Element ? e.target.closest('a[href]') : null;
            if (!link || !link.href.includes('page=')) {
                const editBtn = e.target instanceof Element ? e.target.closest('[data-account-edit-btn]') : null;
                if (editBtn) {
                    e.preventDefault();
                    openEditModalFromButton(editBtn);
                }
                return;
            }

            e.preventDefault();
            fetchTable(link.href, true);
        });

        editModal?.addEventListener('click', (e) => {
            const closeEl = e.target instanceof Element ? e.target.closest('[data-close-account-edit-modal]') : null;
            if (closeEl) {
                e.preventDefault();
                toggleEditModal(false);
            }
        });

        passwordToggleButtons.forEach((toggleBtn) => {
            toggleBtn.addEventListener('click', () => {
                const inputId = toggleBtn.getAttribute('data-target-input');
                const input = inputId ? document.getElementById(inputId) : null;
                const icon = toggleBtn.querySelector('i');
                if (!input || !(input instanceof HTMLInputElement) || !icon) {
                    return;
                }

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !isHidden);
                icon.classList.toggle('fa-eye-slash', isHidden);
                toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeRoleDropdown();
            }

            if (e.key === 'Escape' && editModal && !editModal.classList.contains('hidden')) {
                toggleEditModal(false);
            }
        });

        editForm?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const actionUrl = editForm.dataset.actionUrl;
            if (!actionUrl) {
                showToast('Unable to update account: missing endpoint.', 'error');
                return;
            }

            if (editRoleError) {
                editRoleError.textContent = '';
                editRoleError.classList.add('hidden');
            }
            if (editPasswordError) {
                editPasswordError.textContent = '';
                editPasswordError.classList.add('hidden');
            }

            editSaveBtn?.setAttribute('disabled', 'disabled');

            const accountId = editIdInput?.value || '';
            const previousRole = editForm.dataset.originalRole || '';
            const nextRole = editRoleSelect?.value || '';
            const nextPassword = editPasswordInput?.value || '';
            const nextPasswordConfirm = editPasswordConfirmInput?.value || '';
            const optimisticApplied = accountId && nextRole
                ? applyRoleToRow(accountId, nextRole, true)
                : false;

            try {
                const payload = {
                    role: nextRole,
                };

                if (nextPassword || nextPasswordConfirm) {
                    payload.password = nextPassword;
                    payload.password_confirmation = nextPasswordConfirm;
                }

                const res = await fetch(actionUrl, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok) {
                    const roleError = data?.errors?.role?.[0];
                    const passwordError = data?.errors?.password?.[0];
                    if (roleError && editRoleError) {
                        editRoleError.textContent = roleError;
                        editRoleError.classList.remove('hidden');
                    }
                    if (passwordError && editPasswordError) {
                        editPasswordError.textContent = passwordError;
                        editPasswordError.classList.remove('hidden');
                    }
                    throw new Error(data?.message || `Account update failed (status ${res.status})`);
                }

                toggleEditModal(false);

                if (accountId) {
                    const applied = applyRoleToRow(accountId, data?.account?.role || nextRole, false);
                    if (!applied) {
                        await fetchTable(buildUrlFromForm(), false);
                    }
                }

                showToast(data?.message || 'Account updated successfully.');
            } catch (err) {
                if (optimisticApplied && accountId && previousRole) {
                    applyRoleToRow(accountId, previousRole, false);
                }
                console.error('Failed to update account:', err);
                showToast(err.message || 'Failed to update account.', 'error');
            } finally {
                editSaveBtn?.removeAttribute('disabled');
            }
        });

        window.addEventListener('popstate', () => {
            fetchTable(window.location.href, false);
        });

        const pdfBtn = document.getElementById('accountsPrintPdfBtn');
        const excelBtn = document.getElementById('accountsExportExcelBtn');

        pdfBtn?.addEventListener('click', () => {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = '{{ route("accounts.print.pdf") }}' + (params.toString() ? '?' + params.toString() : '');
            window.open(url, '_blank');
        });

        excelBtn?.addEventListener('click', () => {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = '{{ route("accounts.export.excel") }}' + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        });
    })();
</script>
@endpush