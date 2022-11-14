$(document).ready( function(){
    $('canvas').each(function(){
        $.post('/list/rrd/ajax.php',{service:$(this).attr('id'),period:'daily'}, function(response){
            console.log(response.meta);
            console.log(response.data)
            labels=[];
            for(i = response.meta.start; i < response.meta.end; i=i + response.meta.step){
                labels.push(new Date(i * 1000).toLocaleString());
            }
            datasets = [];
            for(i = 0; i < response.meta.legend.length; i++){

                data=[];
                for( b of response.data){
                    data.push(b[i]);
                }
                dataset={label: response.meta.legend[i], data: data};
                datasets.push(dataset);
            }
            const ctx = document.getElementById(response.service).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },'json');
    });
})
