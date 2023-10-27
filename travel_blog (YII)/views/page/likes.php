<?php 
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Page;
use frontend\models\LoginForm;
use frontend\models\Like;
use frontend\models\Notification;

$baseUrl = AppAsset::register($this)->baseUrl;

$mnn = '';
$proo = '';
$prooo = '';
$cntrrr = '';
for($x=6; $x>=0; $x--)
{
	$time = strtotime(" -" . $x . " day");
	$graphmonth = date('Y-m-d', $time);
	$monthh = date('m/d', $time);
	$mnn .= "'$monthh',";

	$likecount = Page::getLikeCountGraph($page_id,$graphmonth);
	$proo .= "'$likecount',";
}
for($x=13; $x>=7; $x--)
{
	$time = strtotime(" -" . $x . " day");
	$graphmonth = date('Y-m-d', $time);
	$likecount = Page::getLikeCountGraph($page_id,$graphmonth);
	$prooo .= "'$likecount',";
}
$valmonthss = substr($mnn,0,-1);
$provistitt = substr($proo,0,-1);
$provistittt = substr($prooo,0,-1);
if($lastweekcount == 0){$lastweekcounts = 1;}else{$lastweekcounts = $lastweekcount;}
if($currentweekcount == 0){$currentweekcounts = 1;}else{$currentweekcounts = $currentweekcount;}
$per = ($currentweekcounts*100)/($lastweekcounts);
if($currentweekcounts >= $lastweekcounts)
{
	$color = 'green';
	$status = '<i class="mdi mdi-menu-up"></i>';
}
else
{
	$color = 'red';
	$status = '<i class="mdi mdi-menu-down"></i>';
	$per = 100 - $per;
}
if($lastweekcount == 0 && $currentweekcount == 0){$per = 0;}
if($per == 0){$color = 'violet';$status = '<i class="mdi mdi-adjust"></i>';}
?>
<div class="combined-column">
	<div class="content-box bshadow">
		<div class="cbox-title nborder hidetitle-mbl">
			<i class="zmdi zmdi-thumb-up"></i>Likes						
		</div>
		<div class="cbox-desc">
			<div class="row">
				<div class="col l6 m6 s12">
					<ul class="bb-gray">
						<li>
							<div class="like-state">
								<h6><?=$talks_count?> <span>People talking about this</span></h6>
							</div>
						</li>
						<li>
							<div class="like-state">
								<h6><?=$likes_count?> <span>Total page likes</span></h6>
								<span class="state-arrow" style="color:<?=$color?>">
									<?=$status?>
									<?=number_format($per, 1)?>%
									<span>from last week</span>
								</span>
							</div>
						</li>
						<li>
							<div class="like-state">
								<h6><?=$currentweekcount?></h6>
								<span class="state-arrow" style="color:<?=$color?>">
									<?=$status?>
									<?=number_format($per, 1)?>%
									<span>new page likes</span>
								</span>
								<canvas id="pagelikesChart" style="width:100%; height:450px;"></canvas>
							</div>												
						</li>
					</ul>
				</div>
				<div class="col l6 m6 s12 nice-scroll">
					<ul class="bb-gray">
						<li>
							<div class="likes-summery">
								<div class="friend-likes connect-likes">
										<h5><a href="javascript:void(0)"><?=$like_count?> User<?php if($like_count > 1){?>s<?php } ?></a> liked <?=$page_details['page_name']?></h5>
										<?php if($like_count>0){ ?>
										<ul>
											<?php foreach($pageuserdetails as $pageuserdetail){
												$like_user_id = (string)$pageuserdetail['user']['_id'];
												$user_img = $this->context->getimage($like_user_id,'thumb');
												$link = Url::to(['userwall/index', 'id' => $like_user_id]);
											?>
											<li><a href="<?=$link?>" title="<?=$pageuserdetail['user']['fullname']?>"><img src="<?=$user_img?>"/></a></li>
											<?php } ?>
										</ul>
										<?php } else { ?>
										<?php $this->context->getnolistfound('becomefirsttolikepage'); ?>
										<?php } ?>
								</div>
								<div class="invite-likes">
									<?php if(count($invitedconnect) > 0){ ?>
									<p>Invite your friends to like this page<a href="javascript:void(0)">See All</a></p>
									<?php } ?>
									<div class="invite-holder">
									<?php if(count($invitedconnect) > 0){ ?>
										<form onsubmit="return false;">
											<div class="tholder">
												<div class="sliding-middle-custom anim-area underlined">
													<input type="text" placeholder="Type a friend's name" class="invite_connect_search" data-id="invite_connect_search"/>
													<a href="javascript:void(0)" onclick="removeinvitesearchinput(this);"><img src="<?=$baseUrl?>/images/cross-icon.png"/></a>
												</div>
											</div>
										</form>
									<?php } ?>
										<div class="list-holder blockinvite_connect_search">
											<ul>
												<?php 
												if(count($invitedconnect) > 0){
													foreach($invitedconnect as $invitedconnections){
													$connectid = (string)$invitedconnections['to_id'];
													$result = LoginForm::find()->where(['_id' => $connectid])->one();
													$frndimg = $this->context->getimage($connectid,'thumb');
													$pagelikeexist = Like::find()->where(['post_id' => "$page_id", 'user_id' => "$connectid", 'status' => '1', 'like_type' => 'page'])->all();
													$invitaionsent = Notification::find()->where(['post_id' => "$page_id", 'status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id", 'notification_type' => 'pageinvite'])->one();
												?>
												<li class="invite_<?=$connectid?>">
													<div class="invitelike-friend invitelike-connect">
														<div class="imgholder"><img src="<?=$frndimg?>"/></div>
														<div class="descholder">
															<h6><?=$result['fullname']?></h6>
															<div class="btn-holder events_<?=$connectid?>">
																<?php if($pagelikeexist)
																	{
																		echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Liked</label>';
																	}
																	else if($invitaionsent)
																	{
																		echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>';
																	}
																	else
																	{ ?>
																<a href="javascript:void(0)" onclick="sendinvite('<?=$connectid?>','<?=$page_id?>')" class="btn-invite">Invite</a>
																<a href="javascript:void(0)" onclick="cancelinvite('<?=$connectid?>')" class="btn-invite-close"><i class="mdi mdi-close"></i></a>
																<?php } ?>
															</div>
															<div class="dis-none btn-holder sendinvitation_<?=$connectid?>">
																<label class="infolabel"><i class="zmdi zmdi-check"></i> Invitation sent</label>
															</div>
														</div>														
													</div>
												</li>
												<?php } } else { ?>
												<?php $this->context->getnolistfound('allconnectionslikepage'); ?>
												<?php } ?>
											</ul>
										</div>
									</div>
								</div>
							</div>										
						</li>											
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
var ctx = document.getElementById("pagelikesChart");
var data = {
	labels: [<?=$valmonthss?>],
	datasets: [
		{
			label: 'This week',
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
			data: [<?=$provistitt?>],
			spanGaps: false,
		},
		{
			label: 'Last week',
			fill: false,
			lineTension: 0.1,
			backgroundColor: "lightgrey",
			borderColor: "lightgrey",
			borderCapStyle: 'butt',
			borderDash: [],
			borderDashOffset: 0.0,
			borderJoinStyle: 'miter',
			pointBorderColor: "lightgrey",
			pointBackgroundColor: "#fff",
			pointBorderWidth: 1,
			pointHoverRadius: 5,
			pointHoverBackgroundColor: "lightgrey",
			pointHoverBorderColor: "lightgrey",
			pointHoverBorderWidth: 2,
			pointRadius: 4,
			pointHitRadius: 10,
			data: [<?=$provistittt?>],
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
<?php exit;?>