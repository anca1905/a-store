// script.js

document.addEventListener('DOMContentLoaded', () => {
    // Konfigurasi Font Global Chart.js
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#64748B';

    // ==========================================
    // 1. Sales Trend & Forecast (Line Chart)
    // ==========================================
    const ctxLine = document.getElementById('salesTrendChart');
    if (ctxLine) {
        // Gradient fill for actual sales
        const gradientActual = ctxLine.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradientActual.addColorStop(0, 'rgba(2, 132, 199, 0.4)'); // Sky blue
        gradientActual.addColorStop(1, 'rgba(2, 132, 199, 0.0)');

        // Gradient fill for forecast
        const gradientForecast = ctxLine.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradientForecast.addColorStop(0, 'rgba(245, 158, 11, 0.4)'); // Orange
        gradientForecast.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const actualSales = [12000, 15000, 18000, 14000, 28500, 22000];
        const smaForecast = [11000, 14000, 19000, 15000, 30000, 21000];

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Actual Sales',
                        data: actualSales,
                        borderColor: '#0284C7',
                        backgroundColor: gradientActual,
                        borderWidth: 3,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#0284C7',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Sales Forecast (SMA)',
                        data: smaForecast,
                        borderColor: '#F59E0B',
                        backgroundColor: gradientForecast,
                        borderWidth: 3,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#F59E0B',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20, font: { size: 13, weight: '500' } }
                    },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#E2E8F0', drawBorder: false }, ticks: { padding: 10 } },
                    x: { grid: { display: false, drawBorder: false }, ticks: { padding: 10 } }
                },
                interaction: { mode: 'index', intersect: false },
            }
        });
    }

    // ==========================================
    // 2. Actual vs Predicted Sales (Bar Chart)
    // ==========================================
    const ctxBar = document.getElementById('actualVsPredictedChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Mar', 'Apr', 'May'],
                datasets: [
                    {
                        label: 'Actual Sales',
                        data: [18000, 14000, 28500],
                        backgroundColor: '#3B82F6', // Blue
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Predicted Sales',
                        data: [19000, 15000, 30000],
                        backgroundColor: '#F97316', // Orange
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20, font: { size: 13 } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#E2E8F0', borderDash: [5, 5] }, display: false },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ==========================================
    // 3. Product Sales Distribution (Doughnut Chart)
    // ==========================================
    const ctxDoughnut = document.getElementById('salesDistributionChart');
    if (ctxDoughnut) {
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: ['Denim Jacket', 'Casual T-Shirt', 'Sneakers', 'Others'],
                datasets: [
                    {
                        data: [35, 20, 20, 25],
                        backgroundColor: [
                            '#3B82F6', // Blue
                            '#F59E0B', // Yellow/Orange
                            '#84CC16', // Green
                            '#10B981'  // Emerald
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '50%',
                plugins: {
                    legend: {
                        display: false // Sembunyikan legend default agar mirip layout referensi (bisa ditambah kustom HTML jika perlu)
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // ==========================================
    // Global Filter Interactivity Simulation
    // ==========================================
    const btnApplyFilter = document.getElementById('btnApplyFilter');
    if (btnApplyFilter) {
        btnApplyFilter.addEventListener('click', () => {
            const year = document.getElementById('filterYear').value;
            const month = document.getElementById('filterMonth').value;
            const day = document.getElementById('filterDay').value;
            
            // Animasi loading pada button
            const originalText = btnApplyFilter.innerHTML;
            btnApplyFilter.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Loading...';
            
            setTimeout(() => {
                alert(`Filter Diterapkan:\nTahun: ${year}\nBulan: ${month}\nHari: ${day}\n\n(Dalam implementasi PHP, ini akan memicu AJAX / reload page dengan parameter GET)`);
                btnApplyFilter.innerHTML = originalText;
            }, 800);
        });
    }

    // Sidebar UI Active state
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            this.parentElement.classList.add('active');
        });
    });
});
