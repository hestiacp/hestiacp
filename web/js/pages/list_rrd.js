document.addEventListener('DOMContentLoaded', main);

async function main() {
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

async function fetchRrdData(service, period) {
	const response = await fetch('/list/rrd/ajax.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify({ service, period }),
	});

	return response.json();
}

function prepareChartData(rrdData, period) {
	const totalDatasets = rrdData.meta.legend.length;

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
				fill: legendIndex === 0 && totalDatasets > 1,
				borderWidth: 2,
				borderColor: lineColor,
				backgroundColor: lineColor,
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

function getCssVariable(variableName) {
	return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
}
