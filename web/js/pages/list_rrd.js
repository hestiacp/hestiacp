// Default max of 3 lines are drawn for memory. Colors need to be update to work better
colors = ['rgba(255,52,120,0.5)', 'rgba(255,52,0,0.5)', 'rgba(255,255,120,0.5)'];
// Other markups are working see https://www.chartjs.org/docs/latest/

// todo make charts reponsive
(function () {
	document.querySelectorAll('canvas').forEach(async (el) => {
		const response = await fetch('/list/rrd/ajax.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: {
				service: el.getAttribute('id'),
				period: el.getAttribute('period'),
			},
		});
		const rrdData = await response.clone().json();

		// data is stored as start, end time and step between each step
		const labels = [];
		for (i = rrdData.meta.start; i < rrdData.meta.end; i = i + rrdData.meta.step) {
			labels.push(new Date(i * 1000).toLocaleString());
		}

		// rrdData.data stores data as i[x,y] useless for chartjs split in separate datasets
		const datasets = [];
		for (i = 0; i < rrdData.meta.legend.length; i++) {
			const data = [];
			for (b of rrdData.data) {
				data.push(b[i]);
			}
			dataset = { label: rrdData.meta.legend[i], data: data, borderColor: colors[i] };
			datasets.push(dataset);
		}

		// draw chart
		const ctx = document.getElementById(rrdData.service).getContext('2d');
		new Chart(ctx, {
			type: 'line',
			data: { labels, datasets },
			options: {
				scales: {
					y: { beginAtZero: true },
				},
			},
		});
	});
})();
