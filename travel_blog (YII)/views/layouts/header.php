<?php
use yii\helpers\Html;
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\UserForm;
use frontend\models\PostForm;
use frontend\models\Connect;
use frontend\models\Notification;
use frontend\models\NotificationSetting;
use frontend\models\SecuritySetting;
use frontend\models\UserSetting; 
use frontend\models\LoginForm;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email'); 
$user_id = (string)$session->get('user_id');

$Auth = '';
if(isset($user_id) && $user_id != '') {
	$Auth = UserForm::isUserExistByUid($user_id);
} else {
	$Auth = 'checkuserauthclassg';
}

$result = UserForm::find()->where(['email' => $email])->one();
$user = LoginForm::find()->where(['email' => $email])->one();
$session->set('status',$user['status']);
$status = $session->get('status');

$userid = (string) $user['_id'];
$model_connect = new Connect();
$model_post = new PostForm();
$model_notification = new Notification();

if($user_id != '') {
	$notification_budge = $model_notification->getUserPostBudge();
	$notifications = $model_notification->getAllNotification();	
} else {
	$notification_budge = 0;
	$notifications = array(); 
}

$request_budge = $model_connect->connectRequestbadge();
$pending_requests = $model_connect->connectPendingRequests();

$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
if($result_security) {
    $my_post_view_status = $result_security['my_post_view_status'];
    if($my_post_view_status == 'Private') {$post_dropdown_class = 'lock';}
    else if($my_post_view_status == 'Connections') {$post_dropdown_class = 'user';}
    else {$my_post_view_status = 'Public'; $post_dropdown_class = 'globe';}
} else {
    $my_post_view_status = 'Public';
    $post_dropdown_class = 'globe';
}

$userSetting = UserSetting::find()->where(['user_id' => (string)$user_id])->one();
$theme_color = $userSetting['user_theme'];

$getR = (isset($_GET['r']) && $_GET['r'] != '') ? $_GET['r'] : '';
$getRData = UserForm::headerCustomArray($getR);

$notification_settings = NotificationSetting::find()->where(['user_id' => (string)$user_id])->one();
if(!$notification_settings || $notification_settings['sound_on_notification'] == 'Yes') { ?>
    <input type="hidden" id="notification_sound" value="Yes">
<?php } else { ?>
    <input type="hidden" id="notification_sound" value="No">
<?php }

if(!$notification_settings || $notification_settings['sound_on_message'] == 'Yes' || $notification_settings['sound_on_message'] == 'On') { ?>
	<input type="hidden" id="sound_on_message" value="Yes"> 
<?php } else { ?>
	<input type="hidden" id="sound_on_message" value="No">
<?php
}
$this->beginBody() ?>
<input type="hidden" name="user_status" value="<?= $status ?>" id="user_status"/>
<input type="hidden" name="user_email" value="<?= $email ?>" id="user_email"/>
<audio id="soundplay"> 
    <source src="sounds/new_notification.mp3" type="audio/mpeg">
</audio>
 
<div class="header-themebar">
	<div class="container">
		<div class="header-nav">
			<div class="mobile-menu <?=$getRData['getRDataClass']?>"><?=$getRData['commonhtml'];?></div>
			<div class="middle-wrapper">
				<div class="logo-holder">
					<?=$getRData['getRDataMblMenBtn']?>
					<a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>" class="desk-logo"><img src="<?=$baseUrl?>/images/black-logo.png"/></a>
				</div>
				<div class="page-name mainpage-name"><?= Html::encode($this->title) ?></div>
				<div class="page-name innerpage-name"><?=$getRData['getRDatapageNameLabel']?></div>
				<?php if(Yii::$app->controller->id != 'userwall') { ?>
				<div class="was-in-country">
	              <?php include('../views/layouts/compose_iwasincountry2.php'); ?>
	            </div>
	        	<?php } ?>
	        </div>
			<?php if(isset($_GET['r']) && ($_GET['r'] =='site/messages' || $_GET['r'] =='userwall/index')){ ?>
				<div class="mbl-innerhead">
					<div class="gotohome">
						<a href="javascript:void(0)" onclick="closeAddNewMsg()"><i class="mdi mdi-arrow-left"></i></a>				
					</div>
					<div class="logo-holder">					
						<span class="top_img">
							<img src="<?=$baseUrl?>/images/whoisaround-img.png"/>
						</span>
						<a href="javascript:void(0)" class="mbl-logo page-name" onclick="contactInfo()">Bhadresh Patel</a>
						<div class="top_message_status">																
							<span class="">last seen 1hr &nbsp; | </span>
							<span class=""> 12:57 PM </span>
							<span class="">| INDIA </span>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="profile-top">
				<?php
				if($Auth == 'checkuserauthclassg') { ?>
					<a href="javascript:void(0)" class="profile-info">
						<img  class="circle" src="<?=$baseUrl?>/images/guest_thumb.png">
						<span class="user-name">Hello Guest</span>
					</a>
				<?php } else { 
					$user_img = $this->context->getimage($result['_id'],'thumb'); ?>
					<a href="<?php $uid = $result['_id']; echo Url::to(['userwall/index', 'id' => "$uid"]); ?>" class="profile-info">
						<img class="circle" src="<?= $user_img?>">
						<span class="user-name"><?= ucfirst($result['fname']);?></span>
					</a>
				<?php } ?>			
				<div class="header_add_btn">
					<a class='dropdown-button add_btn' href='javascript:void(0)' data-activates='add_btn'> <i class="mdi mdi-plus"></i></a>
					<ul id='add_btn' class='dropdown-content custom_dropdown account_custom_app '>
						<li>
							<a href="<?php echo Yii::$app->urlManager->createUrl(['flights']); ?>" title="Hotels"><i class="mdi mdi-airplane"></i>Flights</a>
						</li>
						<li>
							<a href="<?php echo Yii::$app->urlManager->createUrl(['homestay']);?>" title="Homestay"><i class="mdi mdi-hotel blue"></i>Homestay</a>
						</li>
						<li>
							<a href="javascript:void(0)" title="Event"><i class="mdi mdi-calendar-clock"></i>Event</a>
						</li>
						<li>
							<a href="<?php echo Yii::$app->urlManager->createUrl(['localdine']);?>" title="Local Dine"><i class="mdi mdi-basecamp"></i>Local Dine</a>
						</li>
						<li>
							<a href="<?php echo Yii::$app->urlManager->createUrl(['camping']);?>" title="Camping"><i class="mdi mdi-terrain"></i>Camping</a>
						</li>
						<li>
							<a href="<?php echo Yii::$app->urlManager->createUrl(['ads']);?>" title="Trip"><i class="mdi mdi-plus"></i>Advert</a>
						</li>
						<?php if($Auth != 'checkuserauthclassg') { ?>
						<li>
							<a href="javascript:void(0)" onclick="generateDiscard('dis_logout')" title="Logout"><i class="mdi mdi-logout"></i></a>
						</li>												
						<?php } ?>
					</ul>
				</div>
				
				<?php 
				if($Auth != 'checkuserauthclassg') { ?>
					<a class='dropdown-button account_btn waves-effect' href='javascript:void(0)' data-activates='account_setting'><i class="zmdi zmdi-more-vert"></i></a>
					<ul id='account_setting' class='dropdown-content custom_dropdown account_custom_app'>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/accountsettings']); ?>">Account Settings</a></li>					
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/addvip']); ?>">VIP Member</a></li>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/credits']); ?>">Credits</a></li>					
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/verifyme']); ?>">Verification</a></li>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['ads']); ?>">Advertising Manager</a></li>							
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/billing']); ?>">Billing Information</a></li>
						<li><a href="javascript:void(0)" onclick="doLogout()">Logout</a></li>
					</ul>
				<?php } else {?>
					<a class="account_btn" href="javascript:void(0)" onclick="openLoginPopup()" title="Login"><i class="mdi mdi-lock"></i></a>
				<?php } ?>
			</div>
			
			<?=$getRData['getRDataMblMenBtn2']?>
			<div class="not-icons desktop">
				<div class="not-friends noticon">               
					<div class="dropdown dropdown-custom ">
						<?php if($Auth == 'checkuserauthclassg' || $Auth == 'checkuserauthclassnv') { ?>
						<a class='dropdown-button more_btn' href='javascript:void(0)' onclick="openLoginPopup()" >
						<?php } else  { ?>
						<a class='dropdown-button more_btn friendcountinner' href='javascript:void(0)' onclick="connectnotificationcall()" data-activates='not_frndreq'>
						<?php } ?>
							<i class="mdi mdi-account-outline"></i>
							<?php if($request_budge>0) { ?> 
								<span class="new-notification" id="request_budge"><?php echo $request_budge;?></span>
								<input type="hidden" id="friendcount" value="<?=$request_budge;?>"/> 
							<?php } ?>	
						</a>
						<ul id='not_frndreq' class='dropdown-content request_dropdown dropdown-menu'>
							<li id="not_frndreq_prts_li">
								<div class="fr-list not-area <?php if($request_budge==0) { ?>nopad<?php } ?>">
									<center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100 dis-none"> <div></div> </div></div></center>
								</div>
							</li>
						  </ul>
					</div>
				</div>
				<div class="not-messages noticon">	
					<div class="dropdown dropdown-custom">
						<?php if($Auth == 'checkuserauthclassg' || $Auth == 'checkuserauthclassnv') { ?>
						<a class='dropdown-button more_btn' href='javascript:void(0)' onclick="openLoginPopup()">
						<?php } else  { ?>
						<a class='dropdown-button more_btn messagebox' href='javascript:void(0)' data-activates='not_msg'>
						<?php } ?>
							<img src="<?=$getRData['chat_icon']?>"/>
						</a>
						<ul id='not_msg' class='dropdown-content custom_dropdown message_ul dropdown-menu'>
							<li>
								<div class="msg-list not-area nopad">
									<span class="not-title pull-left">Messages</span>
									<span class="not-title right right-align" onclick="setreadall();">Show all as read</span>
									<div class="not-resultlist nice-scroll no-listcontent">
                                    	<ul class="msg-listing">
                            			</ul>
										<?php if($Auth == 'checkuserauthclassg' || $Auth == 'checkuserauthclassnv') { ?>
										<span class="not-result bshadow left-align"><a href="javascript:void(0)" class="<?=$Auth?> directcheckuserauthclass">Show all messages <i class="mdi mdi-menu-right"></i></a></span>
										<?php } else { ?>
										<span class="not-result bshadow left-align"><a href="?r=site/messages">Show all messages <i class="mdi mdi-menu-right"></i></a></span>
										<?php } ?>
									</div>
								</div>
							</li>
						</ul>
					</div> 
				</div>
				<div class="not-notification noticon">              
					<div class="dropdown dropdown-custom">
						<?php if($Auth == 'checkuserauthclassg' || $Auth == 'checkuserauthclassnv') { ?>
						<a class='dropdown-button more_btn' href='javascript:void(0)' onclick="openLoginPopup()">
						<?php } else { ?>
						<a id="glob_budge" class='dropdown-button more_btn' href='javascript:void(0)' onclick="view_notification(),notificationcall()" data-activates='not_notify'>
						<?php } ?>
							<i class="mdi mdi-bell-outline"></i>
							<?php if($notification_budge>0){ ?>
								<span class="new-notification" id="noti_budge"><?php echo $notification_budge;?></span>
							<?php } ?>
						</a>

						<a class='dropdown-button more_btn connectcountinner' href='javascript:void(0)' onclick="connectnotificationcall()" data-activates='not_frndreq'>
						</a>

						<ul id='not_notify' class='dropdown-content request_dropdown'>
							<li id="not_notify_prts_li">
								<div class="noti-list not-area <?php if(count($notifications) == 0) { ?>nopad<?php } ?>" id="notifications">
									<center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100 dis-none"> <div></div> </div></div></center>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="search-holder main-sholder">
				<div class="search-section">
				<form>
					<input autocomplete="off"  type="text" placeholder="Enter your search term..." onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter your search term...'"  name="search" class="search-input" id="search" data-id="searchfirst" style="width:0;">
					<span class="search-btn">
					    <input type="text" value="" class="search-submit">
					    <i class="mdi mdi-magnify"></i>
					</span>
					<div class="search-result nice-scroll searchfirst"></div>
				</form>
				</div>
			</div>
		</div>
	</div>
</div>