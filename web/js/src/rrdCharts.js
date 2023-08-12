import { post, getCssVariable } from './helpers';

// Create Chart.js charts from in-page data on Task Monitor page
export default async function initRrdCharts() {
	const chartCanvases = document.querySelectorAll('.js-rrd-chart');

	if (!chartCanvases.length) {
		return;
	}

	const Chart = await loadChartJs();

	for (const chartCanvas of chartCanvases) {
		const service = chartCanvas.dataset.service;
		const period = chartCanvas.dataset.period;
		const rrdData = await post('/list/rrd/ajax.php', { service, period });
		const chartData = prepareChartData(rrdData, period);
		const chartOptions = getChartOptions(rrdData.unit);

		new Chart(chartCanvas, {
			type: 'line',
			data: chartData,
			options: chartOptions,
		});
	}
}

async function loadChartJs() {
	// NOTE: String expression used to prevent ESBuild from resolving
	// the import on build (Chart.js is a separate bundle)
	const chartJsBundlePath = '/js/dist/chart.js-auto.min.js';
	const chartJsModule = await import(`${chartJsBundlePath}`);
	return chartJsModule.Chart;
}

function prepareChartData(rrdData, period) {
	return {
		labels: rrdData.data.map((_, index) => {
			const timestamp = rrdData.meta.start + index * rrdData.meta.step;
			const date = new Date(timestamp * 1000);
			return formatLabel(date, period);
		}),
		datasets: rrdData.meta.legend.map((legend, legendIndex) => {
			const lineColor = getCssVariable(`--chart-line-${legendIndex + 1}-color`);

			return {
				label: legend,
				data: rrdData.data.map((dataPoint) => dataPoint[legendIndex]),
				tension: 0.3,
				pointStyle: false,
				borderWidth: 2,
				borderColor: lineColor,
			};
		}),
	};
}

function formatLabel(date, period) {
	const options = {
		daily: { hour: '2-digit', minute: '2-digit' },
		weekly: { weekday: 'short', day: 'numeric' },
		monthly: { month: 'short', day: 'numeric' },
		yearly: { month: 'long' },
		biennially: { month: 'long', year: 'numeric' },
		triennially: { month: 'long', year: 'numeric' },
	};

	return date.toLocaleString([], options[period]);
}

function getChartOptions(unit) {
	const labelColor = getCssVariable('--chart-label-color');
	const gridColor = getCssVariable('--chart-grid-color');

	return {
		plugins: {
			legend: {
				position: 'bottom',
				labels: {
					color: labelColor,
				},
			},
		},
		scales: {
			x: {
				ticks: {
					color: labelColor,
				},
				grid: {
					color: gridColor,
				},
			},
			y: {
				title: {
					display: Boolean(unit),
					text: unit,
					color: labelColor,
				},
				ticks: {
					color: labelColor,
				},
				grid: {
					color: gridColor,
				},
			},
		},
	};
}
