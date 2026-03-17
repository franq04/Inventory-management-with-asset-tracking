import $ from 'jquery';
import Chart from 'chart.js/auto';

// Track chart instances for Turbo cleanup
const chartInstances = [];

function initDashboardCharts() {
    // Destroy any existing charts first
    chartInstances.forEach(c => c.destroy());
    chartInstances.length = 0;

    const data = window.dashboardData || {};

    const normalizeNumericArray = (source, length) => {
        if (!Array.isArray(source)) {
            return Array.from({ length }, () => 0);
        }
        return Array.from({ length }, (_, index) => {
            const value = source[index];
            return Number.isFinite(Number(value)) ? Number(value) : 0;
        });
    };

    // Enhanced Expense Chart
    const $expenseCtx = $('#expenseChart');
    if ($expenseCtx.length && data.expenseMonths?.length && data.expenseTotals?.length) {
        const ctx = $expenseCtx[0].getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.clientHeight);
        gradient.addColorStop(0, 'rgba(26, 58, 45, 0.4)');
        gradient.addColorStop(1, 'rgba(26, 58, 45, 0)');
        
        chartInstances.push(new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.expenseMonths,
                datasets: [{
                    label: 'Total Expense (₱)',
                    data: normalizeNumericArray(data.expenseTotals, data.expenseMonths.length),
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: '#1a3a2d',
                    pointBackgroundColor: '#1a3a2d',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (value) => `₱${Number(value).toLocaleString('en-PH')}` },
                        grid: { drawBorder: false },
                    },
                    x: { grid: { display: false } },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `₱${Number(context.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
                        },
                        backgroundColor: '#1f2937',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 4,
                    },
                },
            },
        }));
    }

    // Enhanced PQS Trend Chart
    const $pqsCtx = $('#pqsTrendChart');
    if ($pqsCtx.length && data.pqsMonths?.length && (data.pqsCounts?.some(Boolean) || data.pqsValues?.some(Boolean))) {
        chartInstances.push(new Chart($pqsCtx[0].getContext('2d'), {
            data: {
                labels: data.pqsMonths,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Assets Recorded',
                        data: normalizeNumericArray(data.pqsCounts, data.pqsMonths.length),
                        backgroundColor: 'rgba(26, 58, 45, 0.75)',
                        hoverBackgroundColor: 'rgba(26, 58, 45, 0.9)',
                        borderRadius: 6,
                        order: 2,
                        barPercentage: 0.6,
                    },
                    {
                        type: 'line',
                        label: 'Asset Value (₱)',
                        data: normalizeNumericArray(data.pqsValues, data.pqsMonths.length),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        order: 1,
                        yAxisID: 'y1',
                        pointStyle: 'circle',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Asset Count', font: { weight: 'bold', size: 10 } },
                        grid: { drawBorder: false },
                        ticks: { font: { size: 10 } }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: { display: true, text: 'Asset Value (₱)', font: { weight: 'bold', size: 10 } },
                        grid: { drawOnChartArea: false },
                        ticks: { 
                            callback: (value) => `₱${Number(value).toLocaleString('en-PH')}`,
                            font: { size: 10 }
                        },
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                },
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, padding: 20, boxWidth: 10 } },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.dataset.type === 'line') {
                                    label += `₱${Number(context.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
                                } else {
                                    label += Number(context.parsed.y).toLocaleString('en-PH');
                                }
                                return label;
                            },
                        },
                    },
                },
            },
        }));
    }

    // Enhanced Assignment Breakdown Chart
    const $assignmentCtx = $('#assignmentBreakdownChart');
    if ($assignmentCtx.length && data.assignment) {
        const breakdownSegments = [
            { label: 'Ready for PQS', value: Number(data.assignment.ready ?? 0), color: '#f59e0b' },
            { label: 'With ICS', value: Number(data.assignment.withIcs ?? 0), color: '#10b981' },
            { label: 'With PAR', value: Number(data.assignment.withPar ?? 0), color: '#8b5cf6' },
            { label: 'Awaiting Docs', value: Number(data.assignment.awaiting ?? 0), color: '#64748b' },
        ];

        const total = breakdownSegments.reduce((sum, segment) => sum + segment.value, 0);
        if (total > 0) {
            chartInstances.push(new Chart($assignmentCtx[0].getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: breakdownSegments.map((segment) => segment.label),
                    datasets: [{
                        data: breakdownSegments.map((segment) => segment.value),
                        backgroundColor: breakdownSegments.map((segment) => segment.color),
                        borderWidth: 4,
                        borderColor: '#ffffff',
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, padding: 16, boxWidth: 10, font: { size: 11 } },
                        },
                        tooltip: {
                             backgroundColor: '#1f2937',
                             titleFont: { size: 14, weight: 'bold' },
                             bodyFont: { size: 12 },
                             padding: 12,
                             cornerRadius: 8,
                             boxPadding: 4,
                        }
                    },
                },
            }));
        } else {
            $assignmentCtx.parent().addClass('flex flex-col items-center justify-center').html(`
                <div class="text-center text-gray-500">
                    <i class="fas fa-chart-pie fa-3x text-gray-300 mb-3"></i>
                    <p class="font-semibold">No pipeline data yet.</p>
                    <p class="text-xs">Data will appear here as items are processed.</p>
                </div>`);
        }
    }
}

// Initialize on first load and Turbo navigations
document.addEventListener('turbo:load', initDashboardCharts);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardCharts, { once: true });
} else {
    initDashboardCharts();
}

// Destroy charts before Turbo caches the page to avoid canvas reuse errors
document.addEventListener('turbo:before-cache', () => {
    chartInstances.forEach(c => c.destroy());
    chartInstances.length = 0;
});

if (!window.__pqsDashboardAutoRefreshBound) {
    document.addEventListener('pqs:auto-refresh', (event) => {
        const isDashboardPage = Boolean(
            document.getElementById('expenseChart') ||
            document.getElementById('pqsTrendChart') ||
            document.getElementById('assignmentBreakdownChart')
        );

        if (!isDashboardPage) {
            return;
        }

        event.preventDefault();

        if (window.Turbo && typeof window.Turbo.visit === 'function') {
            window.Turbo.visit(window.location.href, { action: 'replace' });
            return;
        }

        window.location.reload();
    });

    window.__pqsDashboardAutoRefreshBound = true;
}