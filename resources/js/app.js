import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format(value);

function initDashboardCharts() {
    const trend = document.getElementById('revenueChart');
    if (trend) {
        new Chart(trend, {
            data: {
                labels: JSON.parse(trend.dataset.labels),
                datasets: [
                    {
                        type: 'line',
                        label: 'Revenue',
                        data: JSON.parse(trend.dataset.revenue),
                        yAxisID: 'y',
                        borderColor: '#0f3b39',
                        backgroundColor: 'rgba(15, 59, 57, 0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        borderWidth: 2,
                    },
                    {
                        type: 'bar',
                        label: 'Orders',
                        data: JSON.parse(trend.dataset.orders),
                        yAxisID: 'y1',
                        backgroundColor: 'rgba(208, 164, 68, 0.75)',
                        hoverBackgroundColor: 'rgba(208, 164, 68, 1)',
                        borderRadius: 6,
                        maxBarThickness: 22,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: {
                        callbacks: {
                            label: (item) =>
                                item.dataset.label === 'Revenue'
                                    ? ` Revenue: ${formatMoney(item.parsed.y)}`
                                    : ` Orders: ${item.parsed.y}`,
                        },
                    },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (value) => formatMoney(value) },
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { precision: 0 },
                    },
                },
            },
        });
    }

    const status = document.getElementById('statusChart');
    if (status) {
        new Chart(status, {
            type: 'doughnut',
            data: {
                labels: JSON.parse(status.dataset.labels),
                datasets: [
                    {
                        data: JSON.parse(status.dataset.counts),
                        backgroundColor: [
                            '#ddbd6b', // pending
                            '#3b82f6', // confirmed
                            '#6366f1', // in tailoring
                            '#a855f7', // ready
                            '#22c55e', // delivered
                            '#ef4444', // cancelled
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 14 } },
                },
            },
        });
    }
}

initDashboardCharts();

Alpine.start();
