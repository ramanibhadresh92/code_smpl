<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\UserForm;

$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$email = $session->get('email'); 
$uid = (string)$session->get('user_id');

$Auth = '';
if(isset($uid) && $uid != '') {
	$authstatus = UserForm::isUserExistByUid($uid);
	if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
		$Auth = $authstatus;
	}
} else {
	$Auth = 'checkuserauthclassg';
}
	
$result = UserForm::find()->where(['email' => $email])->one();
$controllerID = Yii::$app->controller->id;
$controllerActionID = Yii::$app->controller->action->id;
$mHideArray = array('trip','site/credits','site/transfercredits','site/discussion','site/creditshistory','site/verifyme','site/addvip','site/billing','site/accountsettings','ads','ads/manage','ads/create','site/hotels','tours','userwall/index','site/messages', 'site/hotellist', 'site/restaurantlist', 'site/travnotifications','cityguide','homestay','homestay/detail','localdine','localdine/detail','camping','page','page/detail','flights');
$mhidelabel = '';
if(in_array($_GET['r'], $mHideArray)) {
	$mhidelabel = 'm-hide';
}
 
?>
 <div class="sidemenu-holder <?=$mhidelabel?>">
		<div class="sidemenu nice-scroll">
			<a href="javascript:void(0)" class="closemenu">
				<i class="mdi mdi-close"></i>
			</a>  
			<div class="side-user-cover">
				<img src="<?=$baseUrl?>/images/wgallery3.jpg">
			</div>
			<div class="side-user">
				<?php
				if($Auth == 'checkuserauthclassg') {
					$user_img = $baseUrl.'/images/guest_thumb.png';
					?>
					<span class="img-holder">
						<img class="circle" src="<?= $user_img?>">
					</span>
					<a href="javascript:void(0)"><span class="desc-holder">Hello Guest</span></a>
				<?php } else { 
					$user_img = $this->context->getimage($result['_id'],'thumb');
					?>
					<span class="img-holder">
						<img src="<?= $user_img?>">
					</span>
					<a href="<?php $uid = $result['_id']; echo Url::to(['userwall/index', 'id' => "$uid"]); ?>"><span class="desc-holder"><?= ucfirst($result['fullname']);?></span></a>
				<?php } ?>
			</div> 
			<div class="sidemenu-ul">
				<ul>
					<li class="lm-home <?php if($controllerActionID =='mainfeed'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>">Home</a></li>
					<li class="lm-home"><a href="<?php echo Yii::$app->urlManager->createUrl(['virtualtours']); ?>">Virtual Tours</a></li>
					<li class="lm-home"><a href="<?php echo Yii::$app->urlManager->createUrl(['todo']); ?>">To Do</a></li>
					<li class="lm-home"><a href="<?php echo Yii::$app->urlManager->createUrl(['watch']); ?>">Watch</a></li>
					<li class="lm-home <?php if($controllerID =='discussion'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['discussion']); ?>">Discussion</a></li>
					<li class="lm-pages <?php if($controllerID =='photostream'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>">Photos</a></li> 
					<li class="lm-tips <?php if($controllerID =='tips'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['tips']); ?>">Tips</a></li>
					<li class="lm-commeve <?php if($controllerID =='blog'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>">Blog</a></li>
					<li class="lm-questions <?php if($controllerID =='questions'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['questions']); ?>">Questions</a></li>
					<li class="lm-waround <?php if($controllerID =='tripstory'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['tripstory']); ?>">Trip Story</a></li>
					<li class="lm-tbuddy <?php if($controllerID =='reviews'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['reviews']); ?>">Reviews</a></li>
					<li class="lm-tbuddy <?php if($controllerID =='collections'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['collections']); ?>">Photo Collections</a></li>
					<li class="lm-lbuddy <?php if($controllerID =='locals'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['locals']); ?>">Japan Locals</a></li>
					<li class="lm-lbuddy <?php if($controllerID =='travellers'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['travellers']); ?>">People travelling to Japan</a></li>
					<li class="lm-localguide <?php if($controllerID =='localguide'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['localguide']); ?>">Local Guide</a></li>						
					<li class="lm-localdriver <?php if($controllerID =='localdriver'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['localdriver']); ?>">Local Driver</a></li>
					<li class="lm-groups <?php if($controllerID =='cityguide'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['cityguide']); ?>">City Guide</a></li>
					<li class="lm-pages <?php if($controllerID =='page'){echo'active';}?>"><a href="<?php echo Yii::$app->urlManager->createUrl(['page']); ?>">Business pages</a></li>
				</ul>
			</div>
		</div>
	   <div class="mobile-menu">
			<a href="javascript:void(0)"><i class="mdi mdi-menu"></i></a>				
		</div>
</div>