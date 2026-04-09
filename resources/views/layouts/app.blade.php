<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - PQS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/pqslogo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #f9bf0f;
            --secondary-color: #1a3a2d;
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .animate-card {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease-out forwards;
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        /* Fix for Alpine.js hiding elements */
        [x-cloak] { display: none !important; }

    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">

    <div class="h-screen flex">
        @include('partials.sidebar')

        <div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-30 hidden transition-opacity duration-300"></div>

        <div id="main-content" class="flex-1 flex flex-col overflow-y-auto transition-all duration-300 ease-in-out md:ml-64">
            
            <header class="sticky top-0 z-20 m-4">
                <div class="w-full bg-white/80 backdrop-blur-lg rounded-xl shadow-lg px-6 py-4 flex justify-between items-center">
                    
                    <div class="flex items-center space-x-4">
                        <button id="menu-toggle" class="p-2 -ml-2 rounded-md text-gray-600 hover:bg-gray-200 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                        </button>
                        <div class="hidden lg:block">
                            <p id="live-datetime" class="text-sm text-gray-500 font-medium"></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-5">
                        <div id="auto-refresh-widget" class="hidden md:flex items-center gap-3 rounded-full border border-emerald-100 bg-emerald-50/70 px-4 py-2 text-xs text-emerald-800">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                            <span id="auto-refresh-status" class="font-semibold uppercase tracking-wide">Auto refresh</span>
                            <span class="text-emerald-700/80">in</span>
                            <span id="auto-refresh-countdown" class="font-bold">30s</span>
                            <span class="text-emerald-700/80">|</span>
                            <span id="auto-refresh-last" class="text-emerald-700/90">updated just now</span>
                        </div>

                        {{-- === REPLACE STATIC BELL WITH DYNAMIC NOTIFICATION COMPONENT === --}}
                        @include('partials._notifications')
                        {{-- ============================================================= --}}

                        <div class="relative">
                            <button id="user-menu-button" class="flex items-center space-x-3 group transition-all duration-200 hover:scale-[1.03] focus:outline-none">
                                @php
                                    $profileImg = session('profile_img')
                                        ? asset('storage/' . session('profile_img'))
                                        : asset('images/default-avatar.png');
                                @endphp
                                <div class="relative">
                                    <img id="header-avatar" src="{{ $profileImg }}" alt="User Avatar" class="rounded-full w-10 h-10 border-2 border-emerald-500 shadow-md object-cover ring-2 ring-emerald-100 group-hover:ring-emerald-200 transition-all duration-200">
                                    <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p id="header-username" class="text-sm font-semibold text-gray-800">{{ session('username') }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ session('role') }}</p>
                                </div>
                                <svg class="hidden sm:block w-4 h-4 text-gray-400 group-hover:text-emerald-600 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div id="user-menu-dropdown" class="absolute right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 z-30 hidden overflow-hidden" style="animation-duration: 0.2s;">
                                {{-- Gradient header with user info --}}
                                <div class="relative bg-gradient-to-br from-emerald-600 via-emerald-700 to-[#1a3a2d] px-5 py-5">
                                    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.15&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                                    <div class="relative flex items-center gap-4">
                                        <div class="relative flex-shrink-0">
                                            <img id="dropdown-avatar" src="{{ $profileImg }}" alt="User Avatar" class="w-14 h-14 rounded-full border-[3px] border-white/30 shadow-lg object-cover">
                                            <span class="absolute bottom-0.5 right-0.5 block h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-white shadow-sm"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <p id="dropdown-username" class="text-base font-bold text-white truncate">{{ session('username') }}</p>
                                            <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wider bg-white/20 text-emerald-50 backdrop-blur-sm">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                                {{ session('role') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Menu items --}}
                                <div class="py-2 px-2">
                                    <a href="#" id="my-profile-trigger" class="group/item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-all duration-150">
                                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 group-hover/item:bg-emerald-100 group-hover/item:text-emerald-700 transition-colors duration-150">
                                            <i class="fas fa-user-circle text-sm"></i>
                                        </span>
                                        <span>My Profile</span>
                                        <svg class="w-4 h-4 ml-auto text-gray-300 group-hover/item:text-emerald-400 group-hover/item:translate-x-0.5 transition-all duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>

                                {{-- Divider --}}
                                <div class="mx-4 border-t border-gray-100"></div>

                                {{-- Logout --}}
                                <div class="py-2 px-2">
                                    <a href="{{ route('logout') }}" class="logout-trigger group/item flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-150">
                                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-500 group-hover/item:bg-red-100 group-hover/item:text-red-600 transition-colors duration-150">
                                            <i class="fas fa-sign-out-alt text-sm"></i>
                                        </span>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-8 flex-1">
                @yield('content')
            </main>
        </div>
    </div>

    <div id="globalAppToast" class="pointer-events-none fixed right-4 top-20 z-[130] hidden min-w-[260px] max-w-md rounded-xl border border-emerald-800 bg-[#1a3a2d] px-5 py-3 text-sm font-semibold text-white shadow-2xl opacity-0 translate-y-2 transition-all duration-300 ease-out"></div>

    @stack('scripts')
<script>
    (() => {
        window.__pqsLayoutCleanup?.();

        const controller = new AbortController();
        const { signal } = controller;

        // --- Element Selectors ---
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarScroll = document.getElementById('sidebar-scroll');
        const mainContent = document.getElementById('main-content');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');
        
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        const globalAppToast = document.getElementById('globalAppToast');
        const globalToastStorageKey = 'pqs.globalToast';
        let globalToastTimerId = null;

        const showGlobalToast = (message, type = 'success') => {
            if (!globalAppToast || !message) {
                return;
            }

            globalAppToast.textContent = message;
            globalAppToast.classList.remove('hidden', 'border-emerald-800', 'bg-[#1a3a2d]', 'border-red-800', 'bg-red-700', 'opacity-0', 'translate-y-2');
            if (type === 'error') {
                globalAppToast.classList.add('border-red-800', 'bg-red-700');
            } else {
                globalAppToast.classList.add('border-emerald-800', 'bg-[#1a3a2d]');
            }

            requestAnimationFrame(() => {
                globalAppToast.classList.add('opacity-100', 'translate-y-0');
            });

            if (globalToastTimerId !== null) {
                window.clearTimeout(globalToastTimerId);
            }

            globalToastTimerId = window.setTimeout(() => {
                globalAppToast.classList.remove('opacity-100', 'translate-y-0');
                globalAppToast.classList.add('opacity-0', 'translate-y-2');
                window.setTimeout(() => {
                    globalAppToast.classList.add('hidden');
                }, 220);
            }, 2600);
        };

        // Expose the shared top-right toast for page-level scripts.
        window.pqsShowToast = showGlobalToast;

        const consumeStoredToast = () => {
            try {
                const raw = window.sessionStorage.getItem(globalToastStorageKey);
                if (!raw) {
                    return;
                }

                window.sessionStorage.removeItem(globalToastStorageKey);
                const payload = JSON.parse(raw);
                const message = String(payload?.message || '').trim();
                const type = String(payload?.type || 'success').trim();

                if (message) {
                    showGlobalToast(message, type);
                }
            } catch (error) {
                window.sessionStorage.removeItem(globalToastStorageKey);
            }
        };

        consumeStoredToast();

        // ✨ NEW: Modal Element Selectors
        const logoutTriggers = document.querySelectorAll('.logout-trigger');
        const logoutModal = document.getElementById('logout-modal');
        const confirmLogoutBtn = document.getElementById('confirm-logout-btn');
        const cancelLogoutBtn = document.getElementById('cancel-logout-btn');
        const modalBackdrop = document.getElementById('modal-backdrop');
        let logoutUrl = ''; // To store the logout URL when a link is clicked

        const closeUserMenuDropdown = () => {
            userMenuDropdown?.classList.add('hidden');
        };

        const exportActionSelector = [
            'button[id$="PrintPdfBtn"]',
            'button[id$="ExportExcelBtn"]',
            'button[id$="PrintExcelBtn"]',
        ].join(', ');

        const exportButtonTimers = new WeakMap();
        const exportButtonState = new WeakMap();
        const loadingActionTimers = new WeakMap();

        const setExportButtonLabel = (button, label) => {
            const labelSpan = button.querySelector('span');
            if (labelSpan) {
                labelSpan.textContent = label;
                return;
            }

            const textNode = Array.from(button.childNodes).find((node) => {
                return node.nodeType === Node.TEXT_NODE && node.textContent.trim().length > 0;
            });

            if (textNode) {
                textNode.textContent = ` ${label}`;
            } else {
                button.appendChild(document.createTextNode(` ${label}`));
            }
        };

        const setExportButtonLoadingState = (button, loading) => {
            let state = exportButtonState.get(button);
            if (!state) {
                state = {
                    originalLabel: button.querySelector('span')?.textContent?.trim() || 'Export',
                    originalTextNode: Array.from(button.childNodes).find((node) => {
                        return node.nodeType === Node.TEXT_NODE && node.textContent.trim().length > 0;
                    })?.textContent?.trim() || '',
                };
                exportButtonState.set(button, state);
            }

            const icon = button.querySelector('i');

            if (loading) {
                const activeTimer = exportButtonTimers.get(button);
                if (activeTimer) {
                    window.clearTimeout(activeTimer);
                }

                button.disabled = true;
                button.setAttribute('aria-disabled', 'true');
                button.classList.add('opacity-60', 'cursor-not-allowed');
                if (icon) {
                    icon.classList.add('fa-spin');
                }
                setExportButtonLabel(button, 'Processing...');

                const timerId = window.setTimeout(() => {
                    setExportButtonLoadingState(button, false);
                }, 2200);
                exportButtonTimers.set(button, timerId);
                return;
            }

            const activeTimer = exportButtonTimers.get(button);
            if (activeTimer) {
                window.clearTimeout(activeTimer);
                exportButtonTimers.delete(button);
            }

            button.disabled = false;
            button.removeAttribute('aria-disabled');
            button.classList.remove('opacity-60', 'cursor-not-allowed');
            if (icon) {
                icon.classList.remove('fa-spin');
            }

            const fallbackLabel = state.originalTextNode || state.originalLabel || 'Export';
            setExportButtonLabel(button, fallbackLabel);
        };

        const loadingActionKeywordRegex = /\b(save|submit|edit|update|approve|confirm|cancel|recommend|apply|generate|send)\b/i;

        const getActionLabelFromControl = (control) => {
            if (!control) {
                return '';
            }

            if (control instanceof HTMLInputElement) {
                return (control.value || '').trim();
            }

            return (control.textContent || '').trim().replace(/\s+/g, ' ');
        };

        const deriveLoadingLabel = (control) => {
            const explicitLabel = (control.dataset.loadingText || '').trim();
            if (explicitLabel) {
                return explicitLabel;
            }

            const currentLabel = getActionLabelFromControl(control).toLowerCase();
            if (currentLabel.includes('save')) {
                return 'Saving...';
            }
            if (currentLabel.includes('submit')) {
                return 'Submitting...';
            }
            if (currentLabel.includes('apply')) {
                return 'Applying...';
            }
            if (currentLabel.includes('generate')) {
                return 'Generating...';
            }
            if (currentLabel.includes('send')) {
                return 'Sending...';
            }
            if (currentLabel.includes('approve')) {
                return 'Approving...';
            }
            if (currentLabel.includes('confirm')) {
                return 'Confirming...';
            }
            if (currentLabel.includes('cancel')) {
                return 'Canceling...';
            }
            if (currentLabel.includes('recommend')) {
                return 'Submitting...';
            }
            if (currentLabel.includes('edit')) {
                return 'Updating...';
            }
            if (currentLabel.includes('update')) {
                return 'Updating...';
            }

            return 'Processing...';
        };

        const setControlLoadingState = (control, loading) => {
            if (!control || control.dataset.noGlobalLoading === 'true') {
                return;
            }

            if (loading) {
                if (control.dataset.loadingActive === 'true') {
                    return;
                }

                control.dataset.loadingActive = 'true';
                control.setAttribute('aria-busy', 'true');

                if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                    control.disabled = true;
                    control.classList.add('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
                }

                if (control instanceof HTMLInputElement) {
                    control.dataset.loadingOriginalValue = control.value;
                    control.value = deriveLoadingLabel(control);
                } else {
                    control.dataset.loadingOriginalHtml = control.innerHTML;
                    control.innerHTML = '<i class="fas fa-spinner fa-spin mr-2 text-xs" aria-hidden="true"></i><span>' + deriveLoadingLabel(control) + '</span>';
                }

                const existingTimer = loadingActionTimers.get(control);
                if (existingTimer) {
                    window.clearTimeout(existingTimer);
                }

                const timerId = window.setTimeout(() => {
                    setControlLoadingState(control, false);
                }, 9000);
                loadingActionTimers.set(control, timerId);
                return;
            }

            const existingTimer = loadingActionTimers.get(control);
            if (existingTimer) {
                window.clearTimeout(existingTimer);
                loadingActionTimers.delete(control);
            }

            control.dataset.loadingActive = 'false';
            control.removeAttribute('aria-busy');

            if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                control.disabled = false;
                control.classList.remove('opacity-80', 'cursor-not-allowed', 'pointer-events-none');
            }

            if (control instanceof HTMLInputElement) {
                control.value = control.dataset.loadingOriginalValue || control.value;
            } else {
                control.innerHTML = control.dataset.loadingOriginalHtml || control.innerHTML;
            }
        };

        const shouldHandleClickLoading = (control) => {
            if (!control || control.dataset.noGlobalLoading === 'true') {
                return false;
            }

            if (control.dataset.loadingOnClick === 'true') {
                return true;
            }

            const label = getActionLabelFromControl(control);
            if (!loadingActionKeywordRegex.test(label)) {
                return false;
            }

            if (control instanceof HTMLAnchorElement) {
                const href = control.getAttribute('href') || '';
                return href.length > 0 && href !== '#' && !href.startsWith('javascript:');
            }

            if (control instanceof HTMLButtonElement) {
                const buttonType = (control.getAttribute('type') || 'submit').toLowerCase();
                if (buttonType === 'submit' || control.hasAttribute('form')) {
                    return false;
                }

                return Boolean(
                    control.getAttribute('onclick') ||
                    control.dataset.showUrl ||
                    control.dataset.url ||
                    control.dataset.action ||
                    control.dataset.route
                );
            }

            return false;
        };

        // --- Sidebar Logic ---
        const isDesktop = () => window.innerWidth >= 768;
        const sidebarStateKey = 'pqs.sidebar.collapsed';
        const sidebarScrollKey = 'pqs.sidebar.scrollTop';
        const mainContentScrollKey = 'pqs.main-content.scrollTop';

        const getStoredSidebarCollapsed = () => {
            try {
                return window.localStorage.getItem(sidebarStateKey) === 'true';
            } catch (error) {
                return false;
            }
        };

        const storeSidebarCollapsed = (collapsed) => {
            try {
                window.localStorage.setItem(sidebarStateKey, collapsed ? 'true' : 'false');
            } catch (error) {
                // Ignore storage failures and keep the in-memory state only.
            }
        };

        const readSessionNumber = (key) => {
            try {
                const value = window.sessionStorage.getItem(key);

                if (value === null) {
                    return 0;
                }

                const parsed = Number.parseInt(value, 10);

                return Number.isNaN(parsed) ? 0 : parsed;
            } catch (error) {
                return 0;
            }
        };

        const writeSessionNumber = (key, value) => {
            try {
                window.sessionStorage.setItem(key, String(Math.max(0, value)));
            } catch (error) {
                // Ignore storage failures and keep default browser scroll behavior.
            }
        };

        let saveScrollTimeoutId = null;

        const flushScrollPositions = () => {
            if (saveScrollTimeoutId !== null) {
                window.clearTimeout(saveScrollTimeoutId);
                saveScrollTimeoutId = null;
            }
            saveScrollPositions();
        };

        const queueScrollPositionSave = () => {
            if (saveScrollTimeoutId !== null) {
                return;
            }

            saveScrollTimeoutId = window.setTimeout(() => {
                saveScrollTimeoutId = null;
                saveScrollPositions();
            }, 140);
        };

        const saveScrollPositions = () => {
            if (sidebarScroll) {
                writeSessionNumber(sidebarScrollKey, sidebarScroll.scrollTop);
            }

            if (mainContent) {
                writeSessionNumber(mainContentScrollKey, mainContent.scrollTop);
            }
        };

        const restoreScrollPositions = () => {
            if (sidebarScroll) {
                sidebarScroll.scrollTop = readSessionNumber(sidebarScrollKey);
            }

            if (mainContent) {
                mainContent.scrollTop = readSessionNumber(mainContentScrollKey);
            }
        };

        let autoRefreshIntervalId = null;

        window.__pqsLayoutCleanup = () => {
            if (autoRefreshIntervalId !== null) {
                window.clearInterval(autoRefreshIntervalId);
                autoRefreshIntervalId = null;
            }
            flushScrollPositions();
            controller.abort();
        };

        const setDesktopSidebarCollapsed = (collapsed) => {
            if (!sidebar || !mainContent) return;

            sidebar.dataset.collapsed = collapsed ? 'true' : 'false';
            sidebar.classList.toggle('w-20', collapsed);
            sidebar.classList.toggle('w-64', !collapsed);
            mainContent.classList.toggle('md:ml-20', collapsed);
            mainContent.classList.toggle('md:ml-64', !collapsed);
            storeSidebarCollapsed(collapsed);
        };

        const syncSidebarState = () => {
            if (!sidebar || !mainContent) return;

            if (isDesktop()) {
                // Desktop: show sidebar, hide backdrop
                sidebar.classList.remove('-translate-x-full');
                if (sidebarBackdrop) {
                    sidebarBackdrop.classList.add('hidden');
                    sidebarBackdrop.classList.add('opacity-0');
                }

                setDesktopSidebarCollapsed(getStoredSidebarCollapsed());
            } else {
                // Mobile: reset margins
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                mainContent.classList.remove('md:ml-64', 'md:ml-20');
            }
        };

        const toggleSidebar = () => {
            if (!sidebar || !mainContent) return;

            if (!isDesktop()) {
                // Mobile: slide sidebar and toggle backdrop
                const isHidden = sidebar.classList.contains('-translate-x-full');
                sidebar.classList.toggle('-translate-x-full');
                
                if (sidebarBackdrop) {
                    if (isHidden) {
                        // Show backdrop
                        sidebarBackdrop.classList.remove('hidden');
                        setTimeout(() => sidebarBackdrop.classList.remove('opacity-0'), 10);
                    } else {
                        // Hide backdrop
                        sidebarBackdrop.classList.add('opacity-0');
                        setTimeout(() => sidebarBackdrop.classList.add('hidden'), 300);
                    }
                }
            } else {
                // Desktop: toggle collapsed state
                setDesktopSidebarCollapsed(sidebar.dataset.collapsed !== 'true');
            }
        };

        const closeSidebar = () => {
            if (window.innerWidth < 768 && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
                if (sidebarBackdrop) {
                    sidebarBackdrop.classList.add('opacity-0');
                    setTimeout(() => sidebarBackdrop.classList.add('hidden'), 300);
                }
            }
        };

        syncSidebarState();
        restoreScrollPositions();
        requestAnimationFrame(restoreScrollPositions);
        window.addEventListener('resize', syncSidebarState, { signal });
        window.addEventListener('beforeunload', saveScrollPositions, { signal });
        window.addEventListener('pagehide', saveScrollPositions, { signal });
        if (menuToggle) menuToggle.addEventListener('click', toggleSidebar, { signal });
        if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', closeSidebar, { signal });
        if (sidebarScroll) sidebarScroll.addEventListener('scroll', queueScrollPositionSave, { passive: true, signal });
        if (mainContent) mainContent.addEventListener('scroll', queueScrollPositionSave, { passive: true, signal });

        // Improve Turbo perceived speed by preloading sidebar destinations on hover/focus.
        document.querySelectorAll('#sidebar a[href]').forEach((link) => {
            link.setAttribute('data-turbo-preload', 'true');
        });

        // Close sidebar when clicking main content on small screens (but ignore clicks on the menu toggle)
        if (mainContent) {
            mainContent.addEventListener('click', (e) => {
                if (window.innerWidth >= 768) return; // only on mobile
                if (!sidebar || sidebar.classList.contains('-translate-x-full')) return; // already closed

                // ignore clicks that originate from the menu toggle or inside the sidebar
                if (e.target.closest('#menu-toggle') || e.target.closest('#sidebar')) return;

                closeSidebar();
            }, { signal });
        }

        // --- User Dropdown Logic ---
        if (userMenuButton) {
            userMenuButton.addEventListener('click', () => {
                userMenuDropdown.classList.toggle('hidden');
            }, { signal });
            document.addEventListener('click', (event) => {
                if (!userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                    closeUserMenuDropdown();
                }
            }, { signal });
        }

        document.addEventListener('click', (event) => {
            const button = event.target.closest(exportActionSelector);
            if (!button || button.disabled) {
                return;
            }

            setExportButtonLoadingState(button, true);
        }, { signal });

        document.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const submitter = event.submitter instanceof HTMLElement
                ? event.submitter
                : form.querySelector('button[type="submit"], input[type="submit"]');

            if (!(submitter instanceof HTMLElement) || submitter.dataset.noGlobalLoading === 'true') {
                return;
            }

            setControlLoadingState(submitter, true);
        }, { signal });

        document.addEventListener('click', (event) => {
            const control = event.target.closest('button, a');
            if (!(control instanceof HTMLElement) || !shouldHandleClickLoading(control)) {
                return;
            }

            setControlLoadingState(control, true);
        }, { signal });
        
        // --- Sidebar Accordion Dropdown Logic ---
        dropdownToggles.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const submenu = button.nextElementSibling;
                submenu.classList.toggle('hidden');
                const chevron = button.querySelector('i.fa-chevron-down');
                if(chevron) chevron.classList.toggle('rotate-180');
            }, { signal });
        });

        // ✨ NEW: Logout Modal Logic ---
        if (logoutModal) {
            const showModal = () => {
                logoutModal.classList.remove('hidden');
                setTimeout(() => {
                    logoutModal.classList.remove('opacity-0');
                    logoutModal.querySelector('[role="dialog"]').classList.remove('scale-95');
                }, 10);
            };

            const hideModal = () => {
                logoutModal.classList.add('opacity-0');
                logoutModal.querySelector('[role="dialog"]').classList.add('scale-95');
                setTimeout(() => logoutModal.classList.add('hidden'), 300);
            };

            logoutTriggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    closeUserMenuDropdown();
                    logoutUrl = trigger.href; // Capture the URL from the link
                    showModal();
                }, { signal });
            });

            confirmLogoutBtn.addEventListener('click', () => {
                if (logoutUrl) window.location.href = logoutUrl; // Proceed to the captured URL
            }, { signal });

            cancelLogoutBtn.addEventListener('click', hideModal, { signal });
            modalBackdrop.addEventListener('click', hideModal, { signal });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !logoutModal.classList.contains('hidden')) {
                    hideModal();
                }
            }, { signal });
        }

        // ✨ My Profile Modal Logic ---
        const profileTrigger = document.getElementById('my-profile-trigger');
        const profileJsonUrl = @json(route('my-profile.json'));
        const profileUpdateUrl = @json(route('my-profile.update'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (profileTrigger) {
            const getProfileModal = () => document.getElementById('profile-modal');
            const getProfilePanel = () => getProfileModal()?.querySelector('.modal-panel');
            const headerUsername = document.getElementById('header-username');
            const dropdownUsername = document.getElementById('dropdown-username');
            const headerAvatar = document.getElementById('header-avatar');
            const dropdownAvatar = document.getElementById('dropdown-avatar');
            const maxAvatarBytes = 2 * 1024 * 1024;
            const allowedAvatarTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

            const getProfileDetailsSection = () => document.getElementById('profileModalDetailsSection');
            const getProfileEditSection = () => document.getElementById('profileModalEditSection');
            const getProfileViewAction = () => document.getElementById('profileModalViewAction');
            const getProfileEditAction = () => document.getElementById('profileModalEditAction');
            const getProfileSaveBtn = () => document.getElementById('profileModalSaveBtn');
            const getProfileEditForm = () => document.getElementById('profileModalEditForm');
            const getProfileEditError = () => document.getElementById('profileModalEditError');
            const getProfileCloseBtn = () => document.getElementById('close-profile-btn');
            const getProfileAvatarInput = () => document.getElementById('profileEditAvatar');
            const getProfileAvatarPreview = () => document.getElementById('profileEditAvatarPreview');
            const getProfileAvatarError = () => document.getElementById('profileEditAvatarError');

            const profileFieldIds = {
                username: 'profileEditUsername',
                first_name: 'profileEditFirstName',
                middle_name: 'profileEditMiddleName',
                last_name: 'profileEditLastName',
                suffix: 'profileEditSuffix',
                gender: 'profileEditGender',
                date_of_birth: 'profileEditDateOfBirth',
                marital_status: 'profileEditMaritalStatus',
                email: 'profileEditEmail',
                contact_no: 'profileEditContactNo',
            };

            const showProfileModal = () => {
                const modal = getProfileModal();
                const panel = getProfilePanel();
                if (!modal) return;
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    panel?.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                }, 10);
            };

            const hideProfileModal = () => {
                const modal = getProfileModal();
                const panel = getProfilePanel();
                if (!modal) return;
                modal.classList.add('opacity-0');
                panel?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
                setTimeout(() => modal.classList.add('hidden'), 300);
            };

            const setEditMode = (editing) => {
                const detailsSection = getProfileDetailsSection();
                const editSection = getProfileEditSection();
                const saveBtn = getProfileSaveBtn();
                const viewBtn = getProfileViewAction();
                const editBtn = getProfileEditAction();

                detailsSection?.classList.toggle('hidden', editing);
                editSection?.classList.toggle('hidden', !editing);
                saveBtn?.classList.toggle('hidden', !editing);

                if (viewBtn) {
                    viewBtn.classList.toggle('bg-[#1a3a2d]', !editing);
                    viewBtn.classList.toggle('text-white', !editing);
                    viewBtn.classList.toggle('border', editing);
                    viewBtn.classList.toggle('border-gray-300', editing);
                    viewBtn.classList.toggle('bg-white', editing);
                    viewBtn.classList.toggle('text-gray-700', editing);
                }

                if (editBtn) {
                    editBtn.classList.toggle('bg-[#1a3a2d]', editing);
                    editBtn.classList.toggle('text-white', editing);
                    editBtn.classList.toggle('border', !editing);
                    editBtn.classList.toggle('border-gray-300', !editing);
                    editBtn.classList.toggle('bg-white', !editing);
                    editBtn.classList.toggle('text-gray-700', !editing);
                }
            };

            const setSaveButtonLoadingState = (loading) => {
                const saveBtn = getProfileSaveBtn();
                if (!saveBtn) return;

                if (!saveBtn.dataset.defaultHtml) {
                    saveBtn.dataset.defaultHtml = saveBtn.innerHTML;
                }

                if (loading) {
                    saveBtn.disabled = true;
                    saveBtn.classList.add('opacity-80', 'cursor-not-allowed');
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-sm"></i>Saving...';
                    return;
                }

                saveBtn.disabled = false;
                saveBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                saveBtn.innerHTML = saveBtn.dataset.defaultHtml;
            };

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value || '—';
            };

            const setEditField = (id, value) => {
                const field = document.getElementById(id);
                if (!field) return;
                field.value = value ?? '';
            };

            const hydrateEditForm = (data) => {
                setEditField(profileFieldIds.username, data.username);
                setEditField(profileFieldIds.first_name, data.first_name);
                setEditField(profileFieldIds.middle_name, data.middle_name);
                setEditField(profileFieldIds.last_name, data.last_name);
                setEditField(profileFieldIds.suffix, data.suffix);
                setEditField(profileFieldIds.gender, data.gender === '—' ? '' : data.gender);
                setEditField(profileFieldIds.date_of_birth, data.date_of_birth_iso || '');
                setEditField(profileFieldIds.marital_status, data.marital_status === '—' ? '' : data.marital_status);
                setEditField(profileFieldIds.email, data.email === '—' ? '' : data.email);
                setEditField(profileFieldIds.contact_no, data.contact_no === '—' ? '' : data.contact_no);
                const avatarInput = getProfileAvatarInput();
                const avatarError = getProfileAvatarError();
                const avatarPreview = getProfileAvatarPreview();

                if (avatarInput) {
                    avatarInput.value = '';
                }
                if (avatarError) {
                    avatarError.textContent = '';
                    avatarError.classList.add('hidden');
                }
                if (avatarPreview) {
                    avatarPreview.src = data.profile_img || @json(asset('images/default-avatar.png'));
                }
            };

            const applyProfileData = (data) => {
                const avatarEl = document.getElementById('profile-modal-avatar');
                if (avatarEl) avatarEl.src = data.profile_img;
                setText('profile-modal-fullname', data.full_name);
                setText('profile-modal-role', data.role);
                setText('profile-modal-empid', data.employee_id);
                setText('profile-modal-username', data.username);
                setText('profile-modal-email', data.email);
                setText('profile-modal-contact', data.contact_no);
                setText('profile-modal-gender', data.gender);
                setText('profile-modal-dob', data.date_of_birth);
                setText('profile-modal-marital', data.marital_status);
                setText('profile-modal-position', data.position);
                setText('profile-modal-section', data.section);
                setText('profile-modal-division', data.division);

                if (headerUsername) {
                    headerUsername.textContent = data.username || '—';
                }
                if (dropdownUsername) {
                    dropdownUsername.textContent = data.username || '—';
                }
                if (headerAvatar) {
                    headerAvatar.src = data.profile_img || @json(asset('images/default-avatar.png'));
                }
                if (dropdownAvatar) {
                    dropdownAvatar.src = data.profile_img || @json(asset('images/default-avatar.png'));
                }

                hydrateEditForm(data);
            };

            const validateAvatarFile = (file) => {
                const avatarError = getProfileAvatarError();

                if (!file) {
                    if (avatarError) {
                        avatarError.textContent = '';
                        avatarError.classList.add('hidden');
                    }
                    return true;
                }

                if (!allowedAvatarTypes.includes(file.type)) {
                    if (avatarError) {
                        avatarError.textContent = 'Invalid file type. Please use PNG, JPG, or WEBP.';
                        avatarError.classList.remove('hidden');
                    }
                    return false;
                }

                if (file.size > maxAvatarBytes) {
                    if (avatarError) {
                        avatarError.textContent = 'File is too large. Maximum allowed size is 2MB.';
                        avatarError.classList.remove('hidden');
                    }
                    return false;
                }

                if (avatarError) {
                    avatarError.textContent = '';
                    avatarError.classList.add('hidden');
                }
                return true;
            };

            const showSkeleton = (show) => {
                const skeleton = document.getElementById('profile-skeleton');
                const content = document.getElementById('profile-content');
                if (skeleton) skeleton.classList.toggle('hidden', !show);
                if (content) content.classList.toggle('hidden', show);
            };

            profileTrigger.addEventListener('click', (e) => {
                e.preventDefault();
                closeUserMenuDropdown();
                setEditMode(false);
                getProfileEditError()?.classList.add('hidden');
                showSkeleton(true);
                showProfileModal();

                fetch(profileJsonUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    applyProfileData(data);
                    showSkeleton(false);
                })
                .catch(() => {
                    showSkeleton(false);
                    setText('profile-modal-fullname', 'Unable to load profile');
                });
            }, { signal });

            document.addEventListener('change', (event) => {
                const input = event.target.closest('#profileEditAvatar');
                if (!input) {
                    return;
                }

                const file = input.files?.[0];
                const isValid = validateAvatarFile(file);
                if (!isValid) {
                    input.value = '';
                    return;
                }

                const avatarPreview = getProfileAvatarPreview();
                if (!file || !avatarPreview) {
                    return;
                }

                const blobUrl = URL.createObjectURL(file);
                avatarPreview.src = blobUrl;
            }, { signal });

            document.addEventListener('click', (event) => {
                if (event.target.closest('#profileModalViewAction')) {
                    setEditMode(false);
                    getProfileEditError()?.classList.add('hidden');
                    return;
                }

                if (event.target.closest('#profileModalEditAction')) {
                    setEditMode(true);
                    getProfileEditError()?.classList.add('hidden');
                    return;
                }

                if (event.target.closest('#profileModalSaveBtn')) {
                    getProfileEditForm()?.requestSubmit();
                }
            }, { signal });

            getProfileCloseBtn()?.addEventListener('click', (event) => {
                event.preventDefault();
                hideProfileModal();
            }, { signal });

            document.addEventListener('submit', async (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.id !== 'profileModalEditForm') {
                    return;
                }

                event.preventDefault();

                if (!profileUpdateUrl) {
                    return;
                }

                const profileEditError = getProfileEditError();
                const profileEditForm = getProfileEditForm();
                profileEditError?.classList.add('hidden');
                setSaveButtonLoadingState(true);

                const formData = new FormData(profileEditForm);
                const avatarFile = getProfileAvatarInput()?.files?.[0];
                if (!validateAvatarFile(avatarFile)) {
                    setSaveButtonLoadingState(false);
                    return;
                }
                formData.append('_method', 'PUT');

                try {
                    const response = await fetch(profileUpdateUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        const firstError = Object.values(payload?.errors || {})[0];
                        const message = Array.isArray(firstError) ? firstError[0] : (payload?.message || 'Unable to update profile.');
                        if (profileEditError) {
                            profileEditError.textContent = message;
                            profileEditError.classList.remove('hidden');
                        }
                        return;
                    }

                    applyProfileData(payload.profile || {});
                    setEditMode(false);
                    if (typeof showGlobalToast === 'function') {
                        showGlobalToast(payload.message || 'Profile updated successfully.', 'success');
                    }
                } catch (error) {
                    if (profileEditError) {
                        profileEditError.textContent = 'Unable to update profile. Please try again.';
                        profileEditError.classList.remove('hidden');
                    }
                } finally {
                    setSaveButtonLoadingState(false);
                }
            }, { signal });

            document.addEventListener('click', (e) => {
                const modal = getProfileModal();
                if (!modal || modal.classList.contains('hidden')) return;
                if (e.target.id === 'close-profile-btn' || e.target.closest('#close-profile-btn')) {
                    hideProfileModal();
                } else if (e.target.id === 'profile-modal-backdrop') {
                    hideProfileModal();
                }
            }, { signal });

            document.addEventListener('keydown', (e) => {
                const modal = getProfileModal();
                if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                    hideProfileModal();
                }
            }, { signal });
        }

        // --- Global Auto Refresh (30s) ---
        const autoRefreshWidget = document.getElementById('auto-refresh-widget');
        const autoRefreshStatus = document.getElementById('auto-refresh-status');
        const autoRefreshCountdown = document.getElementById('auto-refresh-countdown');
        const autoRefreshLast = document.getElementById('auto-refresh-last');
        const AUTO_REFRESH_SECONDS = 30;
        const autoRefreshNextKey = 'pqs.autoRefresh.nextAt';
        const autoRefreshLastKey = 'pqs.autoRefresh.lastAt';
        let remainingSeconds = AUTO_REFRESH_SECONDS;
        let hasDirtyForm = false;

        const formatTime = (date) => {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        };

        const readSessionValue = (key) => {
            try {
                return window.sessionStorage.getItem(key);
            } catch (error) {
                return null;
            }
        };

        const writeSessionValue = (key, value) => {
            try {
                window.sessionStorage.setItem(key, value);
            } catch (error) {
                // Ignore storage failures.
            }
        };

        const getNextRefreshAt = () => {
            const now = Date.now();
            const stored = Number.parseInt(readSessionValue(autoRefreshNextKey) || '', 10);
            if (!Number.isFinite(stored) || stored <= now) {
                const next = now + (AUTO_REFRESH_SECONDS * 1000);
                writeSessionValue(autoRefreshNextKey, String(next));
                return next;
            }

            return stored;
        };

        const setNextRefreshAt = (timestamp) => {
            writeSessionValue(autoRefreshNextKey, String(timestamp));
        };

        const markLastRefreshNow = () => {
            writeSessionValue(autoRefreshLastKey, String(Date.now()));
            if (autoRefreshLast) {
                autoRefreshLast.textContent = `updated ${formatTime(new Date())}`;
            }
        };

        const renderLastRefreshFromSession = () => {
            if (!autoRefreshLast) {
                return;
            }

            const stored = Number.parseInt(readSessionValue(autoRefreshLastKey) || '', 10);
            if (Number.isFinite(stored) && stored > 0) {
                autoRefreshLast.textContent = `updated ${formatTime(new Date(stored))}`;
                return;
            }

            autoRefreshLast.textContent = 'updated just now';
        };

        const setRefreshUi = (state) => {
            if (!autoRefreshWidget || !autoRefreshStatus || !autoRefreshCountdown) {
                return;
            }

            if (state === 'active') {
                autoRefreshWidget.classList.remove('border-amber-100', 'bg-amber-50/80', 'text-amber-800');
                autoRefreshWidget.classList.add('border-emerald-100', 'bg-emerald-50/70', 'text-emerald-800');
                autoRefreshStatus.textContent = 'Auto refresh';
                autoRefreshCountdown.textContent = `${remainingSeconds}s`;
                return;
            }

            autoRefreshWidget.classList.remove('border-emerald-100', 'bg-emerald-50/70', 'text-emerald-800');
            autoRefreshWidget.classList.add('border-amber-100', 'bg-amber-50/80', 'text-amber-800');
            autoRefreshStatus.textContent = 'Refresh paused';
            autoRefreshCountdown.textContent = `${remainingSeconds}s`;
        };

        const hasActiveInputFocus = () => {
            const active = document.activeElement;
            if (!active) {
                return false;
            }

            const tagName = (active.tagName || '').toLowerCase();
            if (tagName === 'input' || tagName === 'textarea' || tagName === 'select') {
                return true;
            }

            return active.isContentEditable === true;
        };

        const canAutoRefresh = () => {
            if (document.visibilityState !== 'visible') {
                return false;
            }

            if (hasActiveInputFocus()) {
                return false;
            }

            if (hasDirtyForm) {
                return false;
            }

            return true;
        };

        const triggerAutoRefresh = () => {
            const refreshDetail = {
                source: 'global-timer',
                at: Date.now(),
            };

            const refreshEvent = new CustomEvent('pqs:auto-refresh', {
                cancelable: true,
                detail: refreshDetail,
            });

            const shouldFallbackRefresh = document.dispatchEvent(refreshEvent);
            if (!shouldFallbackRefresh) {
                return;
            }

            if (typeof window.__pqsAutoRefresh === 'function') {
                try {
                    const wasHandled = window.__pqsAutoRefresh(refreshDetail);
                    if (wasHandled === true) {
                        return;
                    }
                } catch (error) {
                    console.error('Auto-refresh hook failed:', error);
                }
            }

            if (window.Turbo && typeof window.Turbo.visit === 'function') {
                window.Turbo.visit(window.location.href, { action: 'replace' });
                return;
            }

            window.location.reload();
        };

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('input', () => {
                hasDirtyForm = true;
            }, { signal });

            form.addEventListener('change', () => {
                hasDirtyForm = true;
            }, { signal });

            form.addEventListener('submit', () => {
                hasDirtyForm = false;
            }, { signal });
        });

        if (autoRefreshWidget) {
            renderLastRefreshFromSession();

            autoRefreshIntervalId = window.setInterval(() => {
                if (!canAutoRefresh()) {
                    remainingSeconds = AUTO_REFRESH_SECONDS;
                    setNextRefreshAt(Date.now() + (AUTO_REFRESH_SECONDS * 1000));
                    setRefreshUi('paused');
                    return;
                }

                const nextRefreshAt = getNextRefreshAt();
                remainingSeconds = Math.max(0, Math.ceil((nextRefreshAt - Date.now()) / 1000));
                setRefreshUi('active');
                if (remainingSeconds > 0) {
                    return;
                }

                markLastRefreshNow();
                setNextRefreshAt(Date.now() + (AUTO_REFRESH_SECONDS * 1000));
                triggerAutoRefresh();
            }, 1000);
        }

        document.addEventListener('turbo:before-cache', () => {
            saveScrollPositions();
            closeUserMenuDropdown();
            const profileModal = document.getElementById('profile-modal');
            if (profileModal) {
                profileModal.classList.add('hidden', 'opacity-0');
                profileModal.querySelector('.modal-panel')?.classList.add('opacity-0', 'scale-95', 'translate-y-2');
            }
            if (logoutModal) {
                logoutModal.classList.add('hidden', 'opacity-0');
                logoutModal.querySelector('[role="dialog"]')?.classList.add('scale-95');
            }
        }, { once: true, signal });

        // Turbo-specific transition/progress handlers removed.
    })();
</script>
{{-- ✨ My Profile Modal --}}
<div id="profile-modal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div id="profile-modal-backdrop" class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="modal-panel relative flex h-[90vh] max-h-[820px] w-full max-w-5xl min-h-0 flex-col overflow-hidden rounded-2xl bg-white shadow-2xl transition-all duration-300 ease-out opacity-0 scale-95 translate-y-2" role="dialog" aria-modal="true" aria-labelledby="profile-modal-title">

            {{-- Loading skeleton --}}
            <div id="profile-skeleton" class="hidden">
                <div class="bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-8 py-8">
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-full bg-white/20 animate-pulse"></div>
                        <div class="space-y-3 flex-1">
                            <div class="h-6 w-40 bg-white/20 rounded animate-pulse"></div>
                            <div class="h-4 w-24 bg-white/15 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                        <div class="space-y-2"><div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div><div class="h-5 w-28 bg-gray-100 rounded animate-pulse"></div></div>
                        <div class="space-y-2"><div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div><div class="h-5 w-28 bg-gray-100 rounded animate-pulse"></div></div>
                        <div class="space-y-2"><div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div><div class="h-5 w-28 bg-gray-100 rounded animate-pulse"></div></div>
                        <div class="space-y-2"><div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div><div class="h-5 w-28 bg-gray-100 rounded animate-pulse"></div></div>
                        <div class="space-y-2"><div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div><div class="h-5 w-28 bg-gray-100 rounded animate-pulse"></div></div>
                        <div class="space-y-2"><div class="h-3 w-20 bg-gray-200 rounded animate-pulse"></div><div class="h-5 w-28 bg-gray-100 rounded animate-pulse"></div></div>
                    </div>
                </div>
            </div>

            {{-- Actual content --}}
            <div id="profile-content" class="hidden flex min-h-0 flex-1 flex-col">
                {{-- Gradient header --}}
                <div class="relative flex-shrink-0 bg-gradient-to-r from-[#1a3a2d] to-[#2d5a4a] px-8 py-8 text-white">
                    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.15&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                    {{-- Close button --}}
                    <button id="close-profile-btn" type="button" aria-label="Close profile modal" class="absolute top-4 right-4 z-20 cursor-pointer text-white/80 hover:text-white hover:bg-white/10 p-2 rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-white/50">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                    <div class="relative flex items-center gap-5 pr-14">
                        <div class="relative flex-shrink-0">
                            <img id="profile-modal-avatar" src="" alt="Profile" class="w-20 h-20 rounded-full border-[3px] border-white/30 shadow-lg object-cover">
                            <span class="absolute bottom-1 right-1 block h-4 w-4 rounded-full bg-emerald-400 ring-[3px] ring-[#1a3a2d] shadow-sm"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-white/70 font-medium">My Profile</p>
                            <h3 id="profile-modal-title" class="sr-only">My Profile</h3>
                            <h3 id="profile-modal-fullname" class="text-2xl font-bold text-white tracking-tight mt-0.5"></h3>
                            <span class="inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-[0.18em] bg-white/15 text-white/90 backdrop-blur-sm">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                <span id="profile-modal-role"></span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Info sections --}}
                <div id="profileModalDetailsSection" class="min-h-0 flex-1 overflow-y-auto px-8 py-7 space-y-7">
                    {{-- Personal Information --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600">
                                <i class="fas fa-id-card text-xs"></i>
                            </span>
                            <h4 class="text-sm font-bold uppercase tracking-[0.16em] text-[#1a3a2d]">Personal Information</h4>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Employee ID</p>
                                <p id="profile-modal-empid" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Username</p>
                                <p id="profile-modal-username" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Gender</p>
                                <p id="profile-modal-gender" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Date of Birth</p>
                                <p id="profile-modal-dob" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Marital Status</p>
                                <p id="profile-modal-marital" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-gray-100"></div>

                    {{-- Contact Information --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-50 text-sky-600">
                                <i class="fas fa-envelope text-xs"></i>
                            </span>
                            <h4 class="text-sm font-bold uppercase tracking-[0.16em] text-[#1a3a2d]">Contact Information</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Email Address</p>
                                <p id="profile-modal-email" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Contact No.</p>
                                <p id="profile-modal-contact" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-gray-100"></div>

                    {{-- Organization --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 text-amber-600">
                                <i class="fas fa-building text-xs"></i>
                            </span>
                            <h4 class="text-sm font-bold uppercase tracking-[0.16em] text-[#1a3a2d]">Organization</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-5">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Position</p>
                                <p id="profile-modal-position" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Section / Unit</p>
                                <p id="profile-modal-section" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400 mb-1.5">Division</p>
                                <p id="profile-modal-division" class="text-sm font-semibold text-gray-800"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="profileModalEditSection" class="hidden min-h-0 flex-1 overflow-y-auto px-8 py-7 space-y-4">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                        <p class="text-sm font-bold text-[#1a3a2d]">Edit Profile</p>
                        <p class="mt-1 text-xs text-emerald-900/75">Update your personal and contact details.</p>
                    </div>

                    <form id="profileModalEditForm" class="space-y-4">
                        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Profile Photo</p>
                            <div class="mt-3 flex items-center gap-4">
                                <img id="profileEditAvatarPreview" src="" alt="Avatar preview" class="h-16 w-16 rounded-full border-2 border-emerald-200 object-cover bg-white">
                                <div class="flex-1">
                                    <label for="profileEditAvatar" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Upload New Image</label>
                                    <input id="profileEditAvatar" name="profile_img" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="mt-1 block w-full cursor-pointer text-xs text-gray-700 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-[#1a3a2d] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-[#204835]">
                                    <p class="mt-1 text-[11px] text-gray-500">PNG/JPG/WEBP, up to 2MB.</p>
                                    <p id="profileEditAvatarError" class="mt-1 hidden text-[11px] font-medium text-red-600"></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="profileEditUsername" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Username</label>
                                <input id="profileEditUsername" name="username" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div>
                                <label for="profileEditContactNo" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Contact No.</label>
                                <input id="profileEditContactNo" name="contact_no" type="text" maxlength="50" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div>
                                <label for="profileEditFirstName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">First Name</label>
                                <input id="profileEditFirstName" name="first_name" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div>
                                <label for="profileEditLastName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Last Name</label>
                                <input id="profileEditLastName" name="last_name" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div>
                                <label for="profileEditMiddleName" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Middle Name</label>
                                <input id="profileEditMiddleName" name="middle_name" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div>
                                <label for="profileEditSuffix" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Suffix</label>
                                <input id="profileEditSuffix" name="suffix" type="text" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div>
                                <label for="profileEditGender" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Gender</label>
                                <select id="profileEditGender" name="gender" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Not set</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label for="profileEditMaritalStatus" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Marital Status</label>
                                <select id="profileEditMaritalStatus" name="marital_status" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                                    <option value="">Not set</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="widowed">Widowed</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="separated">Separated</option>
                                </select>
                            </div>
                            <div>
                                <label for="profileEditDateOfBirth" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Date of Birth</label>
                                <input id="profileEditDateOfBirth" name="date_of_birth" type="date" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="profileEditEmail" class="text-xs font-semibold uppercase tracking-[0.12em] text-[#2d5a4a]/75">Email</label>
                                <input id="profileEditEmail" name="email" type="email" maxlength="255" class="mt-1 h-10 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-800 focus:border-[#1a3a2d] focus:ring-1 focus:ring-[#1a3a2d]/40">
                            </div>
                        </div>

                        <p id="profileModalEditError" class="hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700"></p>
                        <button type="button" id="profileModalSaveBtn" data-no-global-loading="true" class="hidden ml-auto flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-6 py-2.5 text-base font-semibold text-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#204835]">
                            <i class="fas fa-save text-sm"></i>
                            Save Changes
                        </button>
                    </form>
                </div>

                <div class="shrink-0 flex items-center justify-center sm:justify-start gap-3 border-t border-gray-200 bg-gray-50 px-8 py-5">
                    <div class="flex items-center gap-3">
                        <button type="button" id="profileModalViewAction" class="inline-flex items-center gap-2 rounded-xl bg-[#1a3a2d] px-6 py-2.5 text-base font-semibold text-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#204835]">
                            <i class="fas fa-eye text-sm"></i>
                            View
                        </button>
                        <button type="button" id="profileModalEditAction" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-base font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100">
                            <i class="fas fa-pen text-xs"></i>
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="logout-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 transition-opacity duration-300 ease-in-out opacity-0 hidden">
    <div id="modal-backdrop" class="fixed inset-0"></div>
    <div role="dialog" aria-modal="true" class="relative bg-white w-full max-w-md p-6 rounded-2xl shadow-xl transition-transform duration-300 ease-in-out scale-95">
        
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100">
            <i class="fas fa-exclamation-triangle text-2xl text-yellow-500"></i>
        </div>

        <div class="mt-4 text-center">
            <h3 class="text-xl font-bold text-gray-800">Confirm Logout</h3>
            <p class="mt-2 text-gray-500">Are you sure you want to end your current session?</p>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-4">
            <button id="cancel-logout-btn" type="button" class="px-4 py-3 rounded-xl bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                Cancel
            </button>
            <button id="confirm-logout-btn" type="button" class="px-4 py-3 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Logout
            </button>
        </div>
    </div>
</div>
</body>
</html>