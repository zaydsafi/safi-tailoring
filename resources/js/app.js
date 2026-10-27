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

/*
 * Scroll-reveal: elements marked with [data-reveal] fade/slide in the
 * first time they enter the viewport. An optional --reveal-delay style
 * staggers siblings (set from templates, e.g. per grid index).
 */
function initScrollReveal() {
    const targets = document.querySelectorAll('[data-reveal]');

    if (!targets.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-revealed'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' },
    );

    targets.forEach((el) => observer.observe(el));
}

/*
 * Pointer tilt: cards marked with [data-tilt] rotate gently toward the
 * pointer. Only enabled for precise pointers (mouse/trackpad), never on
 * touch devices, and skipped for reduced-motion users.
 */
function initPointerTilt() {
    if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches)
        { /* fine-pointer only */
            return;
        }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    const MAX_DEGREES = 5;

    document.querySelectorAll('[data-tilt]').forEach((card) => {
        card.addEventListener('pointermove', (event) => {
            const rect = card.getBoundingClientRect();
            const x = (event.clientX - rect.left) / rect.width - 0.5;
            const y = (event.clientY - rect.top) / rect.height - 0.5;

            card.style.transform =
                `perspective(900px) rotateX(${(-y * MAX_DEGREES).toFixed(2)}deg) rotateY(${(x * MAX_DEGREES).toFixed(2)}deg) translateY(-3px)`;
            card.style.boxShadow = '0 18px 36px -18px rgba(15, 59, 57, 0.35)';
        });

        card.addEventListener('pointerleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
        });
    });
}

/*
 * Responsive admin tables: wide data tables (thead + tds) get their column
 * labels copied onto each cell as data attributes, then CSS re-lays the rows
 * out as stacked cards under 640px. No template changes needed, and the
 * original table markup stays untouched for desktop and screen readers.
 */
function initResponsiveTables() {
    document.querySelectorAll('.admin-shell table').forEach((table) => {
        const headers = table.querySelectorAll('thead th');
        if (!headers.length) {
            return;
        }

        table.classList.add('table-stack');

        table.querySelectorAll('tbody tr').forEach((row) => {
            // Full-width message rows (e.g. “No orders yet.”) render as a
            // single block instead of a labelled card.
            const spanning = row.querySelector('td[colspan]');
            if (spanning) {
                row.classList.add('table-stack-empty');
                spanning.setAttribute('data-label', '');

                return;
            }

            row.querySelectorAll(':scope > td').forEach((cell, index) => {
                const header = headers[index];

                if (header) {
                    cell.setAttribute('data-label', header.textContent.trim());
                }
            });
        });
    });
}

initScrollReveal();
initPointerTilt();
initResponsiveTables();

Alpine.start();
