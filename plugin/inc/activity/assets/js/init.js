(function ($) {
	function renderActivityReportChart(data) {
		const $chart = document.getElementById('captchaActivityReportChart');
		if( ! $chart ) {
			return;
		}
		const ctx = $chart.getContext('2d');
		const chart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: data.labels,
				datasets: [
					{
						label: 'Solved Captchas',
						data: data.solved_counts,
						backgroundColor: 'rgba(75, 192, 192, 0.5)',
						borderColor: 'rgb(75, 192, 192)',
						borderWidth: 1,
					},
					{
						label: 'Failed Captchas',
						data: data.failed_counts,
						backgroundColor: 'rgba(255, 99, 132, 0.5)',
						borderColor: 'rgb(255, 99, 132)',
						borderWidth: 1,
					},
					{
						label: 'Empty Captchas',
						data: data.empty_counts,
						backgroundColor: 'rgba(153, 102, 255, 0.5)',
						borderColor: 'rgb(153, 102, 255)',
						borderWidth: 1,
					},
				],
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						position: 'bottom',
						align: 'start',
					},
				},
				scales: {
					x: {
						beginAtZero: true,
					},
					y: {
						beginAtZero: true,
						ticks: {
							stepSize: 1,
							callback: function (value) {
								return value % 1 === 0 ? value : '';
							},
						},
					},
				},
			},
		});
	}	

	$(document).ready(function () {
		if ( pwrcapActivityData === undefined ) {
			return;
		}
		renderActivityReportChart(pwrcapActivityData);
	});
})(jQuery);