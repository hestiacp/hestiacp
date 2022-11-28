// Default max of 3 lines are drawn for memory. Colors need to be update to work better
colors = ['rgba(255,52,120,0.5)', 'rgba(255,52,0,0.5)', 'rgba(255,255,120,0.5)'];
// Other markups are working see https://www.chartjs.org/docs/latest/

//todo make charts reponsive
$(document).ready(function () {
	$('canvas').each(function () {
		$.post(
			'/list/rrd/ajax.php',
			{ service: $(this).attr('id'), period: $(this).attr('period') },
			function (response) {
				labels = [];
				//data is stored as start, end time and step between each step
				for (i = response.meta.start; i < response.meta.end; i = i + response.meta.step) {
					labels.push(new Date(i * 1000).toLocaleString());
				}
				datasets = [];
				//response.data stores data as i[x,y] useless for chartjs split in separate datasets
				for (i = 0; i < response.meta.legend.length; i++) {
					data = [];
					for (b of response.data) {
						data.push(b[i]);
					}
					dataset = { label: response.meta.legend[i], data: data, borderColor: colors[i] };
					datasets.push(dataset);
				}
				//draw chart
				const ctx = document.getElementById(response.service).getContext('2d');
				const myChart = new Chart(ctx, {
					type: 'line',
					data: {
						labels: labels,
						datasets: datasets,
					},
					options: {
						scales: {
							y: {
								beginAtZero: true,
							},
						},
					},
				});
			},
			'json'
		);
	});
});
