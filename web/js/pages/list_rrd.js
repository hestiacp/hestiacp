document.addEventListener('DOMContentLoaded', main);

async function main() {
	const chartCanvases = document.querySelectorAll('.js-rrd-chart');

	for (const chartCanvas of chartCanvases) {
		const service = chartCanvas.getAttribute('data-service');
		const period = chartCanvas.getAttribute('data-period');
		const rrdData = await fetchRrdData(service, period);
		const chartData = prepareChartData(rrdData, period);
		const chartOptions = getChartOptions();

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
	return {
		labels: rrdData.data.map((_, index) => {
			const timestamp = rrdData.meta.start + index * rrdData.meta.step;
			const date = new Date(timestamp * 1000);
			return formatLabel(date, period);
		}),
		datasets: rrdData.meta.legend.map((legend, legendIndex) => {
			return {
				label: legend,
				data: rrdData.data.map((dataPoint) => dataPoint[legendIndex]),
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

function getChartOptions() {
	const currentTheme = getCurrentTheme();
	const textColor = currentTheme === 'dark' ? '#cdcdcd' : '#7c7c7c';
	const gridColor = currentTheme === 'dark' ? '#434343' : '#e9e9e9';

	return {
		plugins: {
			legend: {
				position: 'bottom',
				labels: {
					color: textColor,
				},
			},
		},
		scales: {
			x: {
				ticks: {
					color: textColor,
				},
				grid: {
					color: gridColor,
				},
			},
			y: {
				ticks: {
					color: textColor,
				},
				grid: {
					color: gridColor,
				},
			},
		},
	};
}

function getCurrentTheme() {
	return document.body.getAttribute('data-theme');
}
