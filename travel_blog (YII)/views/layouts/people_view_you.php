<?php

use frontend\models\ProfileVisitor;
use frontend\assets\AppAsset;

if(isset($_GET['r']) && $_GET['r'] == 'userwall/saved-content')
{
    $baseUrl = $_POST['baseUrl'];
}
else
{
    $baseUrl = AppAsset::register($this)->baseUrl;
}

$mn = '';
$pro = '';
$cntrr = '';
for($x=3; $x>=0; $x--)
{
    $month = date('M', strtotime(date('Y-m')." -" . $x . " month"));
    $year = date('Y', strtotime(date('Y-m')." -" . $x . " month"));
    $mn .= "'$month',";
    
    $visitor = ProfileVisitor::find()->where(['user_id' => "$user_id",'year' => $year,'month' => $month])->all();
    if($visitor)
    {
        $cnt = count($visitor);
        $cntrr += $cnt;
    }
    else
    {
        $cnt = 0;
    }
    $pro .= "'$cnt',";
}
$valmonths = substr($mn,0,-1);
$provistit = substr($pro,0,-1);
?>

<div class="content-box bshadow">
    <div class="cbox-title">
        How often people viewed you
    </div>
    <div class="cbox-desc">
        <span class="peopleview-count"><p><?=$cntrr?> profile views in last quarter</p></span>
        <canvas id="myChart" width="280" height="171"></canvas>
        <script> 
        $(document).ready(function() {
            var ctx = document.getElementById("myChart");

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
        });
        </script>
    </div>
</div>