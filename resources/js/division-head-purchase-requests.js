import $ from 'jquery';

const queuePageSelector = '#divisionHeadQueuePage';
const refreshIntervalMs = 3000;

let cleanupRegistered = false;

const initDivisionHeadPurchaseRequests = () => {
    window.__divisionHeadPurchaseRequestsCleanup?.();

    const controller = new AbortController();
    const { signal } = controller;
    let refreshTimerId = null;
    let countdownTimerId = null;
    let secondsUntilRefresh = Math.ceil(refreshIntervalMs / 1000);
    let requestToken = 0;
    let activeRequestController = null;

    window.__divisionHeadPurchaseRequestsCleanup = () => {
        controller.abort();
        if (refreshTimerId) {
            clearTimeout(refreshTimerId);
        }
        if (countdownTimerId) {
            clearInterval(countdownTimerId);
        }
        if (activeRequestController) {
            activeRequestController.abort();
        }
    };

    const getQueuePage = () => document.querySelector(queuePageSelector);

    const normalizeText = (value = '') => String(value).replace(/\s+/g, ' ').trim();

    const buildQueueSignature = (root = document) => {
        const activeTab = new URLSearchParams(window.location.search).get('tab') || 'for_recommendation';
        const rows = Array.from(root.querySelectorAll('#divisionHeadQueuePage table tbody tr'))
            .filter((row) => row.tagName === 'TR' && row.querySelector('td'))
            .map((row) => [
                row.querySelector('td')?.textContent || '',
                row.textContent || '',
                row.getAttribute('class') || '',
            ].map(normalizeText).join('|'));

        const tabCounts = Array.from(root.querySelectorAll('[data-division-head-tab-key]')).map((tab) => {
            const key = tab.getAttribute('data-division-head-tab-key') || '';
            const countNode = tab.querySelector('[data-division-head-tab-count]');
            return `${key}:${normalizeText(countNode?.textContent || '')}`;
        });

        return [activeTab, ...tabCounts, ...rows].join('||');
    };

    const updateRefreshBadge = (remainingSeconds = secondsUntilRefresh, statusText = 'updated just now') => {
        const countdownNode = document.querySelector('[data-division-head-refresh-countdown]');
        const statusNode = document.querySelector('[data-division-head-refresh-status]');

        if (countdownNode) {
            countdownNode.textContent = `${Math.max(0, remainingSeconds)}s`;
        }

        if (statusNode) {
            statusNode.textContent = statusText;
        }
    };

    const resetCountdown = () => {
        secondsUntilRefresh = Math.ceil(refreshIntervalMs / 1000);
        updateRefreshBadge(secondsUntilRefresh, 'updated just now');
    };

    const scheduleCountdown = () => {
        if (countdownTimerId) {
            clearInterval(countdownTimerId);
        }

        countdownTimerId = setInterval(() => {
            const queuePage = getQueuePage();
            if (!queuePage) {
                return;
            }

            secondsUntilRefresh -= 1;
            if (secondsUntilRefresh <= 0) {
                updateRefreshBadge(0, 'refreshing now');
            } else {
                updateRefreshBadge(secondsUntilRefresh, 'updated just now');
            }
        }, 1000);
    };

    const swapQueuePage = (html, url) => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nextQueuePage = doc.querySelector(queuePageSelector);
        const currentQueuePage = getQueuePage();

        if (!nextQueuePage || !currentQueuePage) {
            return false;
        }

        const currentSignature = buildQueueSignature(document);
        const nextSignature = buildQueueSignature(doc);

        if (currentSignature === nextSignature) {
            return false;
        }

        nextQueuePage.classList.add('opacity-0', 'translate-y-1');
        currentQueuePage.replaceWith(nextQueuePage);

        if (window.location.href !== url) {
            window.history.replaceState({ divisionHeadQueueUrl: url }, '', url);
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                nextQueuePage.classList.remove('opacity-0', 'translate-y-1');
            });
        });

        return true;
    };

    const loadQueueContent = async (url) => {
        if (!url || !getQueuePage()) {
            return;
        }

        if (activeRequestController) {
            activeRequestController.abort();
        }

        const currentToken = ++requestToken;
        activeRequestController = new AbortController();
        const requestUrl = new URL(url, window.location.origin);
        requestUrl.searchParams.set('_ts', String(Date.now()));

        try {
            const response = await fetch(requestUrl.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                cache: 'no-store',
                signal: activeRequestController.signal,
            });

            if (!response.ok || currentToken !== requestToken || !getQueuePage()) {
                return;
            }

            const html = await response.text();
            const swapped = swapQueuePage(html, url);
            if (swapped) {
                resetCountdown();
            } else {
                updateRefreshBadge(secondsUntilRefresh, 'no changes detected');
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Failed to refresh division head purchase requests:', error);
            }
        }
    };

    const refreshQueue = () => {
        loadQueueContent(window.location.href);
    };

    const scheduleNextRefresh = () => {
        if (refreshTimerId) {
            clearTimeout(refreshTimerId);
        }

        refreshTimerId = setTimeout(async () => {
            await refreshQueue();
            scheduleNextRefresh();
        }, refreshIntervalMs);
    };

    scheduleCountdown();
    refreshQueue();
    scheduleNextRefresh();
    resetCountdown();

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            updateRefreshBadge(secondsUntilRefresh, 'paused while hidden');
            return;
        }

        resetCountdown();
        refreshQueue();
    }, { signal });

    window.addEventListener('focus', () => {
        if (!getQueuePage()) {
            return;
        }

        resetCountdown();
        refreshQueue();
    }, { signal });

    if (!cleanupRegistered) {
        cleanupRegistered = true;
        window.addEventListener('beforeunload', () => {
            window.__divisionHeadPurchaseRequestsCleanup?.();
        }, { once: true });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDivisionHeadPurchaseRequests, { once: true });
} else {
    initDivisionHeadPurchaseRequests();
}
