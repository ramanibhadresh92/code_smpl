<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<?php include('../views/layouts/page_strength.php'); ?>

<div class="content-box bshadow">
    <div class="cbox-title">
        How often people viewed page
    </div>  
    <div class="cbox-desc">
        <p><?=$cntrr?> page views in last quarter</p>
        <!-- <img src="<?=$baseUrl?>/images/graph.png"> -->
        <canvas id="mbChart" width="280" height="171"></canvas>
    </div>      
</div>

<?php include('../views/layouts/user_recently_pages.php'); ?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
<script>
    var ctx = document.getElementById("mbChart");

    var data = {
        labels: [<?=$valmonths?>],
        datasets: [
            {
                fill: false,
                lineTension: 0.1,
                backgroundColor: "rgba(75,192,192,0.4)",
                borderColor: "rgba(75,192,192,1)",
                borderCapStyle: 'butt',
                borderDash: [],
                borderDashOffset: 0.0,
                borderJoinStyle: 'miter',
                pointBorderColor: "rgba(75,192,192,1)",
                pointBackgroundColor: "#fff",
                pointBorderWidth: 1,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: "rgba(75,192,192,1)",
                pointHoverBorderColor: "rgba(220,220,220,1)",
                pointHoverBorderWidth: 2,
                pointRadius: 4,
                pointHitRadius: 10,
                data: [<?=$provistit?>],
                spanGaps: false,
            }
        ]
    };

    var myLineChart = Chart.Line(ctx, {
        data: data,
        options: {
            tooltips: {
                enabled: true
            },
            legend: {
                display: false
            },
            hover: {
                display: false
            },
            scales: {
                yAxes: [{
                    ticks: {
                        stepSize: 10,
                        beginAtZero:true
                    }
                }]
            }
        }
    });
</script>