import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', async () => {
    const chartElement = document.querySelector('#admin-overview-chart');
    const chartData = window.adminDashboardChart;

    if (!chartElement || !chartData) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');

    new ApexCharts(chartElement, {
        chart: {
            type: 'area',
            height: 260,
            toolbar: { show: false },
            zoom: { enabled: false },
            fontFamily: 'Inter, sans-serif',
        },
        series: chartData.series,
        colors: ['#6d28d9', '#1677e8'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.22, opacityTo: 0.02, stops: [0, 95] },
        },
        grid: { borderColor: '#e8edf5', strokeDashArray: 4, padding: { left: 4, right: 8 } },
        xaxis: {
            categories: chartData.categories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#667085', fontSize: '10px' }, rotate: 0, hideOverlappingLabels: true },
        },
        yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: '#667085', fontSize: '10px' } } },
        legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px', markers: { size: 5 } },
        tooltip: { theme: 'light' },
    }).render();
});
