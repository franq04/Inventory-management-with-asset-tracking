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
                        <div class="relative hidden md:block">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-gray-400"></i>
                            </span>
                            <input type="text" placeholder="Search..." class="w-full max-w-xs pl-10 pr-4 py-2 border border-gray-200 rounded-full bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[var(--secondary-color)] transition-all">
                        </div>

                        {{-- === REPLACE STATIC BELL WITH DYNAMIC NOTIFICATION COMPONENT === --}}
                        @include('partials._notifications')
                        {{-- ============================================================= --}}

                        <div class="relative">
                            <button id="user-menu-button" class="flex items-center space-x-3 transition-transform duration-200 hover:scale-105">
                                @php
                                    $profileImg = session('profile_img')
                                        ? asset('storage/' . session('profile_img'))
                                        : asset('images/default-avatar.png');
                                @endphp
                                <img src="{{ $profileImg }}" alt="User Avatar" class="rounded-full w-10 h-10 border-2 border-[var(--primary-color)] shadow-sm object-cover">
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-semibold text-gray-800">{{ session('username') }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ session('role') }}</p>
                                </div>
                            </button>

                            <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl py-2 z-30 hidden" style="animation-duration: 0.2s;">
                                <div class="px-4 py-3 border-b">
                                    <p class="font-bold text-gray-800">{{ session('username') }}</p>
                                    <p class="text-xs text-gray-500 capitalize">Logged in as {{ session('role') }}</p>
                                </div>
                                <a href="#" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-user-circle w-4"></i> My Profile</a>
                                <a href="{{ route('logout') }}" class="logout-trigger flex items-center gap-3 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-sign-out-alt w-4"></i> Logout</a>
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

        // ✨ NEW: Modal Element Selectors
        const logoutTriggers = document.querySelectorAll('.logout-trigger');
        const logoutModal = document.getElementById('logout-modal');
        const confirmLogoutBtn = document.getElementById('confirm-logout-btn');
        const cancelLogoutBtn = document.getElementById('cancel-logout-btn');
        const modalBackdrop = document.getElementById('modal-backdrop');
        let logoutUrl = ''; // To store the logout URL when a link is clicked

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

        window.__pqsLayoutCleanup = () => {
            saveScrollPositions();
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
        if (sidebarScroll) sidebarScroll.addEventListener('scroll', saveScrollPositions, { passive: true, signal });
        if (mainContent) mainContent.addEventListener('scroll', saveScrollPositions, { passive: true, signal });

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
                    userMenuDropdown.classList.add('hidden');
                }
            }, { signal });
        }
        
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

        document.addEventListener('turbo:before-cache', () => {
            saveScrollPositions();
            userMenuDropdown?.classList.add('hidden');
            if (logoutModal) {
                logoutModal.classList.add('hidden', 'opacity-0');
                logoutModal.querySelector('[role="dialog"]')?.classList.add('scale-95');
            }
        }, { once: true, signal });
    })();
</script>
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