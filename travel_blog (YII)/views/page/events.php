<?php 
use frontend\models\EventVisitors;
use frontend\models\Connect;
use frontend\models\UserForm;

$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');  

$Auth = '';
if(isset($user_id) && $user_id != '') 
{
$authstatus = UserForm::isUserExistByUid($user_id);
if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') 
{
	$Auth = $authstatus;
}
}	
else	
{
	$Auth = 'checkuserauthclassg';
}

?>
<div class="combined-column">
	<div class="content-box nbg">
		<div class="fake-title-area divided-nav">
			<ul class="tabs">								
				<li class="tab"><a href="#commevents-all">All</a></li>
				<li class="tab"><a href="#commevents-attending">Attending</a></li>
				<li class="tab"><a href="#commevents-yours">Yours</a></li>
			</ul>
		</div>
		<div class="cbox-desc" id="self-events">										
			<div class="tab-content view-holder grid-view">												
				<div class="tab-pane fade main-pane active in" id="commevents-all">							
					<div class="commevents-list generalbox-list">
						<div class="row">							
							<?php if(empty($pageevent) && ($user_id != $pagedetails['created_by'])){ ?>
							<div class="col-lg-12">
								<?php $this->context->getnolistfound('noeventexists'); ?>
							</div>
							<?php } ?>
							<?php 
								$start = 0;
								foreach($pageevent as $allevent){
								$event_id = (string)$allevent['_id'];
								$event_count = EventVisitors::getEventCounts($event_id);
								$date = $allevent['event_date'];
								$event_created_by = $allevent['created_by'];
								$event_privacy = $allevent['event_privacy'];
								$dp = $this->context->geteventimage($event_id);
								$is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$event_created_by",'status' => '1'])->one();
								if(($event_privacy == 'Public' || ($user_id == $event_created_by)) || ($event_privacy == 'Friends' && ($is_connect || ($user_id == $event_created_by)))){
							?>
							<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
								<a href="javascript:void(0);" onclick="joinEvent(event,'<?=$event_id;?>',this)" class="commevents-box general-box <?=$Auth?> directcheckuserauthclass">				
									<div class="photo-holder">
										<img src="<?=$dp?>">
									</div>
									<div class="content-holder">
										<h4><?= $allevent['event_name'];?></h4>
										<div class="userinfo">
											<span class="month"><?=date("M", strtotime($date))?></span>
											<span class="date"><?=date("d", strtotime($date))?></span>
										</div>	

										<div class="usertag dis-none">
											<span class="month">month</span>
											<span class="date">date</span>
										</div>	
										<div class="username">
											<span>Greg Batmarx</span>
										</div>											
										<div class="action-btns">											
											<span class="noClick <?=$Auth?> directcheckuserauthclass" onclick="joinEvent(event,'<?=$event_id;?>',this)"><?= EventVisitors::getEventGoing($event_id);?></span>
										</div>
									</div>
								</a>
							</div>
							<?php $start++;} } ?>
						</div>
					</div>
				</div>
				<div class="tab-pane fade main-pane" id="commevents-attending">
				</div>
				
				<div class="tab-pane fade main-pane commevents-yours general-yours" id="commevents-yours">
					<div class="commevents-list generalbox-list">
						<div class="row">
							<?php if($user_id == $pagedetails['created_by']) { ?>
							<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12 add-cbox">
								<div class="commevents-box general-box">
									<a href="#add-commevents-popup" class="add-commevents add-general popup-modal">
										<span class="icont">+</span>
										Create Page Event
									</a>
								</div>
							</div>
							<?php } ?>
							<?php if(empty($pageevent) && ($user_id != $pagedetails['created_by'])){ ?>
							<div class="col-lg-12">
								<?php $this->context->getnolistfound('noeventexists'); ?>
							</div>
							<?php } ?>
							<?php 
								$start = 0;
								foreach($pageevent as $allevent){
								$event_id = (string)$allevent['_id'];
								$event_count = EventVisitors::getEventCounts($event_id);
								$date = $allevent['event_date'];
								$event_created_by = $allevent['created_by'];
								$event_privacy = $allevent['event_privacy'];
								$dp = $this->context->geteventimage($event_id);
								$is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$event_created_by",'status' => '1'])->one();
								if(($event_privacy == 'Public' || ($user_id == $event_created_by)) || ($event_privacy == 'Friends' && ($is_connect || ($user_id == $event_created_by)))){
							?>
							<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
								<a href="javascript:void(0);" onclick="joinEvent(event,'<?=$event_id;?>',this)" class="commevents-box general-box <?=$Auth?> directcheckuserauthclass">				
									<div class="photo-holder">
										<img src="<?=$dp?>">
									</div>
									<div class="content-holder">
										<h4><?= $allevent['event_name'];?></h4>
										<div class="userinfo">
											<span class="month"><?=date("M", strtotime($date))?></span>
											<span class="date"><?=date("d", strtotime($date))?></span>
										</div>	
										<div class="username">
											<span>Greg Batmarx</span>
										</div>											
										<div class="action-btns">										
											<span class="noClick <?=$Auth?> directcheckuserauthclass" onclick="joinEvent(event,'<?=$event_id;?>',this)"><?= EventVisitors::getEventGoing($event_id);?></span>
										</div>
									</div>
								</a>
							</div>
							<?php $start++;} } ?>
						</div>
					</div>
				</div>				
			</div>
		</div>
	</div>
</div>
<?php exit;?>