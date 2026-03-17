const formatTime = (timestamp) => {
    if (!timestamp) {
        return '';
    }
    const date = new Date(timestamp);
    return date.toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const buildMarkUrl = (template, id) => template.replace('__ID__', String(id));

const updateBadge = (badgeEl, count) => {
    if (!badgeEl) {
        return;
    }
    if (count > 0) {
        badgeEl.textContent = count;
        badgeEl.classList.remove('hidden');
    } else {
        badgeEl.textContent = '';
        badgeEl.classList.add('hidden');
    }
};

const renderNotifications = (listEl, emptyEl, notifications) => {
    if (!listEl || !emptyEl) {
        return;
    }

    listEl.innerHTML = '';

    if (notifications.length === 0) {
        listEl.classList.add('hidden');
        emptyEl.classList.remove('hidden');
        return;
    }

    emptyEl.classList.add('hidden');
    listEl.classList.remove('hidden');

    notifications.forEach((notification) => {
        const li = document.createElement('li');
        li.className = 'px-4 py-3 flex items-start gap-3 hover:bg-gray-50 transition';
        li.dataset.notificationId = notification.id;
        li.innerHTML = `
            <div class="flex-shrink-0 mt-1">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-100 text-gray-600">
                    <i class="fas fa-info-circle"></i>
                </span>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-700 ${notification.is_read ? '' : 'font-semibold'}">${notification.message}</p>
                <p class="text-xs text-gray-500 mt-1">${notification.time_for_humans || formatTime(notification.created_at)}</p>
            </div>
            <button data-notif-mark class="text-xs text-[#1a3a2d] hover:text-[#154529] font-semibold ${notification.is_read ? 'hidden' : ''}">
                Mark read
            </button>
        `;
        listEl.appendChild(li);
    });
};

let cleanupNotifications = null;

const initNotifications = () => {
    cleanupNotifications?.();

    const config = window.notificationConfig;
    if (!config) {
        return;
    }

    const root = document.querySelector('[data-notif-root]');
    if (!root) {
        return;
    }

    const controller = new AbortController();
    const { signal } = controller;

    const toggleButton = root.querySelector('[data-notif-toggle]');
    const panel = root.querySelector('[data-notif-panel]');
    const listEl = root.querySelector('[data-notif-list]');
    const emptyEl = root.querySelector('[data-notif-empty]');
    const badgeEl = root.querySelector('[data-notif-count]');
    const markAllBtn = root.querySelector('[data-notif-mark-all]');
    const scrollContainer = root.querySelector('[data-notif-scroll]');

    let isPanelOpen = false;
    let isFetching = false;
    let lastFetchedAt = 0;
    let initialFetchTimeout = null;

    cleanupNotifications = () => {
        if (initialFetchTimeout !== null) {
            window.clearTimeout(initialFetchTimeout);
            initialFetchTimeout = null;
        }

        stopPolling();
        closePanel();
        controller.abort();
    };

    const fetchNotifications = async () => {
        if (isFetching) {
            return;
        }
        isFetching = true;
        try {
            const response = await fetch(config.listUrl, {
                headers: { Accept: 'application/json' }
            });
            if (!response.ok) {
                throw new Error('Failed to fetch notifications');
            }
            const payload = await response.json();
            renderNotifications(listEl, emptyEl, payload.data || []);
            updateBadge(badgeEl, payload.unread_count || 0);
            lastFetchedAt = Date.now();
        } catch (error) {
            console.error(error);
        } finally {
            isFetching = false;
        }
    };

    const fetchIfStale = () => {
        if (Date.now() - lastFetchedAt < 45000) {
            return;
        }

        fetchNotifications();
    };

    const markNotificationRead = async (notificationId, triggerEl) => {
        try {
            const response = await fetch(buildMarkUrl(config.markUrlTemplate, notificationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    Accept: 'application/json'
                }
            });
            if (!response.ok) {
                throw new Error('Unable to mark notification as read');
            }
            const payload = await response.json();
            if (triggerEl) {
                triggerEl.classList.add('hidden');
                const messageEl = triggerEl.closest('li')?.querySelector('p.text-sm');
                messageEl?.classList.remove('font-semibold');
            }
            updateBadge(badgeEl, payload.unread_count || 0);
        } catch (error) {
            console.error(error);
        }
    };

    const markAllRead = async () => {
        try {
            const response = await fetch(config.markAllUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    Accept: 'application/json'
                }
            });
            if (!response.ok) {
                throw new Error('Unable to mark notifications as read');
            }
            const payload = await response.json();
            updateBadge(badgeEl, payload.unread_count || 0);
            listEl.querySelectorAll('[data-notif-mark]').forEach((btn) => btn.classList.add('hidden'));
            listEl.querySelectorAll('p.text-sm').forEach((text) => text.classList.remove('font-semibold'));
        } catch (error) {
            console.error(error);
        }
    };

    const openPanel = () => {
        if (isPanelOpen) {
            return;
        }
        panel.classList.remove('hidden');
        isPanelOpen = true;
        fetchIfStale();
    };

    const closePanel = () => {
        if (!isPanelOpen) {
            return;
        }
        panel.classList.add('hidden');
        isPanelOpen = false;
    };

    toggleButton?.addEventListener('click', (event) => {
        event.stopPropagation();
        if (isPanelOpen) {
            closePanel();
        } else {
            openPanel();
        }
    }, { signal });

    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) {
            closePanel();
        }
    }, { signal });

    listEl?.addEventListener('click', (event) => {
        const markBtn = event.target.closest('[data-notif-mark]');
        if (!markBtn) {
            return;
        }
        const item = markBtn.closest('li');
        if (!item) {
            return;
        }
        const notificationId = item.dataset.notificationId;
        if (!notificationId) {
            return;
        }
        markNotificationRead(notificationId, markBtn);
    }, { signal });

    markAllBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        markAllRead();
    }, { signal });

    let pollInterval = null;
    const startPolling = () => {
        if (pollInterval || document.hidden) {
            return;
        }
        pollInterval = window.setInterval(fetchIfStale, 60000);
    };

    const stopPolling = () => {
        if (!pollInterval) {
            return;
        }
        window.clearInterval(pollInterval);
        pollInterval = null;
    };

    root.addEventListener('mouseenter', startPolling, { signal });
    root.addEventListener('mouseleave', stopPolling, { signal });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
            return;
        }

        startPolling();
        fetchIfStale();
    }, { signal });

    const deferInitialFetch = () => {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(() => {
                fetchNotifications();
                startPolling();
            }, { timeout: 2500 });
            return;
        }

        initialFetchTimeout = window.setTimeout(() => {
            fetchNotifications();
            startPolling();
            initialFetchTimeout = null;
        }, 1200);
    };

    if (document.readyState === 'complete') {
        deferInitialFetch();
    } else {
        window.addEventListener('load', deferInitialFetch, { once: true, signal });
    }

    document.addEventListener('turbo:before-cache', cleanupNotifications, { once: true, signal });
};

const bootNotifications = () => {
    initNotifications();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootNotifications, { once: true });
} else {
    bootNotifications();
}

document.addEventListener('turbo:load', bootNotifications);
