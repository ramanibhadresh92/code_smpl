<?php   
use frontend\models\TravAdsVisitors;
use frontend\models\Vip;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$adid = $ad['_id'];
$adruntype = $ad['adruntype'];
if($adruntype == 'daily')
{
	$start = date('d M Y', $ad['post_created_date']);
	$end = 'Today';
}
else
{
	$start = date('d M Y', $ad['adstartdate']);
	$end = date('d M Y', $ad['adenddate']);
}

$impression = TravAdsVisitors::getCount($adid,'impression');
$action = TravAdsVisitors::getCount($adid,'action');
$click = TravAdsVisitors::getCount($adid,'click');
$total = $impression + $action + $click;

$isvip = Vip::isVIP((string)$user_id);
$rate_impression = $this->context->getadrate($isvip,'impression');
$rate_action = $this->context->getadrate($isvip,'action');
$rate_click = $this->context->getadrate($isvip,'click');
$spent_impression = (($impression/1000)*$rate_impression);
$spent_action = $action*$rate_action;
$spent_click = $click*$rate_click;
$spent = $spent_impression + $spent_action + $spent_click;

$countdate = date('d M Y');
$strcurtime = time();
$sd = strtotime("-7 days",$strcurtime);
$sevendays = date('d M Y', ($sd));
$od = strtotime("-1 day",$strcurtime);
$oneday = date('d M Y', ($od));

$sevencount = TravAdsVisitors::getSpecificCount($adid,$strcurtime,$sd);

$lastcount = TravAdsVisitors::getSpecificCount($adid,$strcurtime,$od);

$mn = $adbudgetcount = $bucount = $days = $conday = '';
for($x=5; $x>=0; $x--)
{
	$mntime = strtotime("-".($x*1)." month");
	$mnntime = strtotime("-".($x+1)." month");
	$graphmonth = date('Y-m-d h:i:s', $mntime);
	$monthh = date('M', $mntime);
	$mn .= "'$monthh',";
	
	$impressions = TravAdsVisitors::getSpecificTypeCount($adid,'impression',$mntime,$mnntime);
	$actions = TravAdsVisitors::getSpecificTypeCount($adid,'action',$mntime,$mnntime);
	$clicks = TravAdsVisitors::getSpecificTypeCount($adid,'click',$mntime,$mnntime);
	$totaladcounts = (($impressions/1000)*$rate_impression) + ($actions*$rate_action) + ($clicks*$rate_click);
	$adbudgetcount .= "'$totaladcounts',";

	$budcount = TravAdsVisitors::getSpecificCount($adid,$mntime,$mnntime);
	$bucount .= "'$budcount',";

	$mndays = strtotime("-".($x*15)." days");
	$mnndays = strtotime("-".($x+15)." days");
	$graphdays = date('Y-m-d h:i:s', $mndays);
	$dayss = date('d M', $mndays);
	$days .= "'$dayss',";
	$converday = TravAdsVisitors::getSpecificCount($adid,$mndays,$mnndays);
	$conday .= "'$converday',";
}
$valmonths = substr($mn,0,-1);
$adbudcount = substr($adbudgetcount,0,-1);
$bcount = substr($bucount,0,-1);
$lifedays = substr($days,0,-1);
$conver = substr($conday,0,-1);
?>

<div class="title">
	<div class="container">
		<div class="left">
			<h5>Detail info - <?=$ad['adname']?> <span><?=$this->context->getAdType($ad['adobj'])?></span></h5>
		</div>
		<div class="right">
			<h6><?=$start?> - <?=$end?></h6>
		</div>
	</div>
</div>
<div class="details">
	<div class="container">
		<div class="travad-states">
			<div class="state-tab">
				<h4><img src="<?=$baseUrl?>/images/adgraph-icon.png"/>Spending</h4>
				<div class="bordered-tab">
					<ul class="tabs">
						<li class="tab"><a href="#spending-budget">Budget</a></li>
						<li class="tab"><a href="#spending-spent">Spent</a></li>
						<li class="tab"><a href="#spending-schedule">Schedule</a></li>
					</ul>
					<div class="tab-content">
						<div id="spending-budget">
							<p>Your Starting Budget</p>
							<h5>$<?=$ad['adbudget']?></h5>
						</div>
						<div id="spending-spent">
							<p>Total Spent</p>
							<h5>$<?=$spent?></h5>
						</div>
						<div id="spending-schedule">
							<p>Advertisement Schedule</p>
							<h5><?=$start?> - <?=$end?></h5>
						</div>
					</div>
				</div>
			</div>
			<div class="state-graph">
				<div class="state-summery dis_n">
					<ul>
						<li>
							$<?=$ad['adbudget']?>
							<span>Budget</span>
						</li>
						<li>
							$<?=$spent?>
							<span>Spent</span>
						</li>
						<li>
							<?=$start?> - <?=$end?>
							<span>Schedule</span>
						</li>
					</ul>
				</div>											
				<div class="graph-holder" style="width:550px">
					<canvas id="spendinggraph"></canvas>
				</div>
			</div>										
		</div>
	</div>	
</div>
<div class="details">
	<div class="container">
		<div class="travad-states">
			<div class="state-tab">	
				<h4><img src="<?=$baseUrl?>/images/adgraph-icon.png"/>Results</h4>
				<div class="bordered-tab">
					<ul class="tabs link-menu">
						<li class="tab"><a href="#results-impression">Impression</a></li>
						<li class="tab"><a href="#results-action">Action</a></li>
						<li class="tab"><a href="#results-click">Click</a></li>
					</ul>
					<div class="tab-content">
						<div id="results-impression" class="tab-pane fade active in">
							<p><?=$start?> - <?=$end?></p>
							<h5><?=$impression?></h5>
						</div>
						<div id="results-action" class="tab-pane fade">
							<p><?=$start?> - <?=$end?></p>
							<h5><?=$action?></h5>
						</div>
						<div id="results-click" class="tab-pane fade">
							<p><?=$start?> - <?=$end?></p>
							<h5><?=$click?></h5>
						</div>
					</div>
				</div>
			</div>
			<div class="state-graph">
				<div class="state-summery dis_n">												
					<ul class="graylabel">
						<li>
							<?=$impression?>
							<span>Impression</span>
						</li>
						<li>
							<?=$action?>
							<span>Action</span>
						</li>
						<li>
							<?=$click?>
							<span>Click</span>
						</li>
					</ul>
				</div>
				<div class="graph-holder" style="width:550px">
					<canvas id="resultgraph"></canvas>
				</div>
			</div>
		</div>
	</div>	
</div>
<div class="details">
	<div class="container">
		<div class="travad-states">
			<div class="state-tab">
				<h4><img src="<?=$baseUrl?>/images/adgraph-icon.png"/>Conversions</h4>
				<div class="bordered-tab">
					<ul class="tabs link-menu">
						<li class="tab"><a href="#conversion-lifetime">Lifetime</a></li>
						<li class="tab"><a href="#conversion-7days">7 Days</a></li>
						<li class="tab"><a href="#conversion-1day">1 Day</a></li>
					</ul>
					<div class="tab-content">
						<div id="conversion-lifetime" class="tab-pane fade active in">
							<p><?=$start?> - <?=$end?></p>
							<h5><?=$total?></h5>
						</div>
						<div id="conversion-7days" class="tab-pane fade">
							<p><?=$sevendays?> - <?=$countdate?></p>
							<h5><?=$sevencount?></h5>
						</div>
						<div id="conversion-1day" class="tab-pane fade">
							<p><?=$oneday?> - <?=$countdate?></p>
							<h5><?=$lastcount?></h5>
						</div>
					</div>
				</div>
			</div>
			<div class="state-graph">
				<div class="state-summery dis_n">
					<ul>
						<li>
							<?=$total?>
							<span>Lifetime</span>
						</li>
						<li>
							<?=$sevencount?>
							<span>7 Days</span>
						</li>
						<li>
							<?=$lastcount?>
							<span>1 Day</span>
						</li>
					</ul>
				</div>
				<div class="graph-holder" style="width:550px">
					<canvas id="congraph"></canvas>
				</div>
			</div>
		</div>
	</div>	
</div>
<script src="<?=$baseUrl?>/js/chart.js"></script>
<script>
var spendinggraph = document.getElementById("spendinggraph");
var data = {
	labels: [<?=$valmonths?>],
	datasets: [
		{
			fill: false,
			lineTension: 0.1,
			backgroundColor: "darkblue",
			borderColor: "darkblue",
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: "darkblue",
			pointBackgroundColor: "#fff",
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: "darkblue",
			pointHoverBorderColor: "darkblue",
			pointHoverBorderWidth: 2,
			pointRadius: 4,
			pointHitRadius: 10,
			data: [<?=$adbudcount?>],
			spanGaps: false,
		}
	]
};
var myLineChart = Chart.Line(spendinggraph, {
	data: data,
	options: {
		tooltips: {
			enabled: true
		},
		hover: {
			display: false
		},
		legend: {
			display: false
		},
		responsive: true,
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
var resultgraph = document.getElementById("resultgraph");
var data = {
	labels: [<?=$valmonths?>],
	datasets: [
		{
			fill: false,
			lineTension: 0.1,
			backgroundColor: "darkblue",
			borderColor: "darkblue",
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: "darkblue",
			pointBackgroundColor: "#fff",
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: "darkblue",
			pointHoverBorderColor: "darkblue",
			pointHoverBorderWidth: 2,
			pointRadius: 4,
			pointHitRadius: 10,
			data: [<?=$bcount?>],
			spanGaps: false,
		}
	]
};
var myLineChart = Chart.Line(resultgraph, {
	data: data,
	options: {
		tooltips: {
			enabled: true
		},
		hover: {
			display: false
		},
		legend: {
			display: false
		},
		responsive: true,
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
var congraph = document.getElementById("congraph");
var data = {
	labels: [<?=$lifedays?>],
	datasets: [
		{
			fill: false,
			lineTension: 0.1,
			backgroundColor: "darkblue",
			borderColor: "darkblue",
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: "darkblue",
			pointBackgroundColor: "#fff",
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: "darkblue",
			pointHoverBorderColor: "darkblue",
			pointHoverBorderWidth: 2,
			pointRadius: 4,
			pointHitRadius: 10,
			data: [<?=$conver?>],
			spanGaps: false,
		}
	]
};
var myLineChart = Chart.Line(congraph, {
	data: data,
	options: {
		tooltips: {
			enabled: true
		},
		hover: {
			display: false
		},
		legend: {
			display: false
		},
		responsive: true,
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
<?php exit;?>