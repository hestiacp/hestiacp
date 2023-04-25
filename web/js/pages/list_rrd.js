async function init() {
	const Chart = await loadChartJs();
	const chartCanvases = document.querySelectorAll('.js-rrd-chart');

	for (const chartCanvas of chartCanvases) {
		const service = chartCanvas.dataset.service;
		const period = chartCanvas.dataset.period;
		const rrdData = await fetchRrdData(service, period);
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
	const module = await import('/js/dist/chart.js-auto.min.js');
	return module.Chart;
}

async function fetchRrdData(service, period) {
	const response = await fetch('/list/rrd/ajax.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ service, period }),
	});

	return response.json();
}

function prepareChartData(rrdData, period) {
	return {
		labels: rrdData.data.map((_, index) => {
			const timestamp = rrdData.meta.start + index * rrdData.meta.step;
			const date = new Date(timestamp * 1000);
			return formatLabel(date, period);
		}),
		datasets: rrdData.meta.legend.map((legend, legendIndex) => {
			const lineColor = Hestia.helpers.getCssVariable(`--chart-line-${legendIndex + 1}-color`);

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
	};

	return date.toLocaleString([], options[period]);
}

function getChartOptions(unit) {
	const labelColor = Hestia.helpers.getCssVariable('--chart-label-color');
	const gridColor = Hestia.helpers.getCssVariable('--chart-grid-color');

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
					display: !!unit,
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

init();
