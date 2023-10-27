<?php 
use frontend\models\Page;
use frontend\models\PostForm;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;


for($x=11; $x>=0; $x--)
{
	$time = strtotime(" -" . ($x*1) . " month");
	$graphmonth = date('Y-m-d h:i:s', $time);
	$monthh = date('M', $time);
	$mn .= "'$monthh',";

	$likecount = Page::getInsightLikeCount($page_id,$graphmonth);
	$pro .= "'$likecount',";
	
	$viewcount = Page::getInsightViewCount($page_id,$graphmonth);
	$view .= "'$viewcount',";
	
	$postdate = $time;
	$postcount = PostForm::find()->where(['is_deleted'=>"0",'post_user_id'=>"$page_id"])->andwhere(['post_created_date'=> ['$lte'=>"$postdate"]])->count();
	$post .= "'$postcount',";
}
$valmonths = substr($mn,0,-1);
$provistit = substr($pro,0,-1);
$viewvistit = substr($view,0,-1);
$postvistit = substr($post,0,-1);
?>
<div class="combined-column">
	<div class="content-box bshadow">
		<div class="cbox-title hidetitle-mbl">
			<i class="mdi mdi-chart-line"></i>
			Insights
		</div>
		<div class="cbox-desc">
			<div class="insights-holder">
				<h4><i class="mdi mdi-calendar"></i>This year insight</h4>
				<ul class="tabs outertabs">	
					<li class="tab"><a href="#insights-viewed"><i class="mdi mdi-eye"></i>Who's viewed your page</a></li>
					<li class="tab"><a href="#insights-liked"><i class="zmdi zmdi-thumb-up"></i>Who's liked your page</a></li>
				</ul>
				<div class="tab-content outercontent">
					<div class="tab-pane fade in active outerpane" id="insights-viewed">
						<div class="tab-box">
							<ul class="tabs">	
								<li class="tab"><a href="#viewed-pageview"><span><?=$totalviewscount?></span> Page Views</a></li>
								<li class="tab"><a href="#viewed-sponsored" data-toggle="tab" aria-expanded="false"><span>0</span> Found your page from sponsored ad</a></li>
								<li><a href="#viewed-postreach"><span><?=$totalpostcount?></span> Post Reach</a></li>
							</ul>
							<div class="tab-content">
							<div class="tab-pane fade in active outertab" id="viewed-pageview">
								<div class="graph-box">
									<div class="graph-holder">
										<canvas id="insightviewed"></canvas>
									</div>
									<div class="graph-detail">
										<h6><?=$dd?></h6>
										<ul>
											<li>
												<span class="count"><?=$totalviewscount?></span>
												<div class="count-info">
													Page views
													<span class="graph-status up">Up 100% from the last year <i class="mdi mdi-menu-up"></i></span>
												</div>
											</li>
											<li class="dis-none">
												<span class="count">count</span>
												<div class="count-info">
													Page view
													<span class="graph-status up">Up 100% from the last year <i class="mdi mdi-menu-up"></i></span>
												</div>
											</li>
											<li>
												<span class="count"><?=$totalviewscount?></span>
												<div class="count-info">
													Page views
												</div>
												<p>Getting more page views can help you get found for the right opportunity.</p>
												<a href="javascript:void(0)">Get more page views <i class="mdi mdi-menu-right"></i></a>
											</li>
										</ul>
									</div>
								</div>														
							</div>
							<div class="tab-pane fade outertab" id="viewed-sponsored">
								<div class="graph-box">
									<div class="graph-holder">
										<img src="<?=$baseUrl?>/images/insight-graph.png">
									</div>
									<div class="graph-detail">
										<h6><?=$dd?></h6>
										<ul>
											<li>
												<span class="count">0</span>
												<div class="count-info">
													Found by sponsored ad
													<span class="graph-status up">Up 100% from the last year <i class="mdi mdi-menu-up"></i></span>
												</div>
											</li>
											<li>
												<span class="count">0</span>
												<div class="count-info">
													Page views
												</div>
												<p>Getting more page views can help you get found for the right opportunity.</p>
												<a href="javascript:void(0)">Get more page views <i class="mdi mdi-menu-right"></i></a>
											</li>
										</ul>
									</div>
								</div>														
							</div>
								<div class="tab-pane fade outertab" id="viewed-postreach">
									<div class="graph-box">
										<div class="graph-holder">
											<canvas id="insightposts"></canvas>
										</div>
										<div class="graph-detail">
											<h6><?=$dd?></h6>
											<ul>
												<li>
													<span class="count"><?=$totalpostcount?></span>
													<div class="count-info">
														Post reach
														<span class="graph-status up">Up 100% from the last year <i class="mdi mdi-menu-up"></i></span>
													</div>
												</li>
												<li>
													<span class="count"><?=$totalviewscount?></span>
													<div class="count-info">
														Page views
													</div>
													<p>Getting more page views can help you get found for the right opportunity.</p>
													<a href="javascript:void(0)">Get more page views <i class="mdi mdi-menu-right"></i></a>
												</li>
											</ul>
										</div>
									</div>														
								</div>
							</div>												
						</div>
						<div class="info-summery">
							<h5><?=$owner_name?>, page views matter.</h5>
							<p>
								Getting more page views can help your business getting more opportunity.<br>
								You got up to 38% more views by taking some of the steps below.
							</p>
						</div>
					</div>
					<div class="tab-pane fade outerpane" id="insights-liked">
						<div class="tab-box">
							<ul class="tabs">
								<li class="tab"><a href="#viewed-pageview"><span><?=$totalviewscount?></span> Page Views</a></li>
							</ul>
							<div class="tab-content">
								<div class="tab-pane fade in active outertab" id="viewed-pageview">
									<div class="graph-box">
										<div class="graph-holder">
											<canvas id="insightliked"></canvas>
										</div>
										<div class="graph-detail">
											<h6><?=$dd?></h6>
											<ul>
												<li>
													<span class="count"><?=$totallikesscount?></span>
													<div class="count-info">
														Page likes
														<span class="graph-status up">Up 100% from the last year <i class="mdi mdi-menu-up"></i></span>
													</div>
												</li>
												<li>
													<span class="count"><?=$totallikesscount?></span>
													<div class="count-info">
														Page likes
													</div>
													<p>Getting more page views can help you get found for the right opportunity.</p>
													<a href="javascript:void(0)">Get more page likes <i class="mdi mdi-menu-right"></i></a>
												</li>
											</ul>
										</div>
									</div>														
								</div>
							</div>												
						</div>
						<div class="info-summery">
							<h5><?=$owner_name?>, page likes matter.</h5>
							<p>
								Getting more page views can help your business getting more opportunity.<br>
								You got up to 38% more views by taking some of the steps below.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
var insightviewed = document.getElementById("insightviewed");
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
			data: [<?=$viewvistit?>],
			spanGaps: false,
		}
	]
};
var myLineChart = Chart.Line(insightviewed, {
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
var insightposts = document.getElementById("insightposts");
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
			data: [<?=$postvistit?>],
			spanGaps: false,
		}
	]
};
var myLineChart = Chart.Line(insightposts, {
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
var insightliked = document.getElementById("insightliked");
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
			data: [<?=$provistit?>],
			spanGaps: false,
		}
	]
};
var myLineChart = Chart.Line(insightliked, {
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