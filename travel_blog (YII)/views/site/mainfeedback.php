<?php 
use frontend\assets\AppAsset;
use frontend\models\LoginForm;

$baseUrl = AppAsset::register($this)->baseUrl;
$this->title = 'Feedback';

if(isset($_GET['refid']) && !empty($_GET['refid']) && isset($_GET['userid']) && !empty($_GET['userid']))
{
    $referal_id = $_GET['refid'];
    $user_id = $_GET['userid'];
    $refid =  base64_decode(strrev($referal_id));
    $userid =  base64_decode(strrev($user_id));
    $referal_exist = LoginForm::find()->where(['_id' => $refid])->one();
    $user_exist = LoginForm::find()->where(['_id' => $userid])->one();
}
?>
<div class="page-wrapper ">
	<div class="header-section">
		<div class="header-themebar">
			<div class="logo-holder">
				<a href="<?php echo Yii::$app->urlManager->createUrl(['site/index']); ?>" class="desk-logo">
					<img src="<?=$baseUrl?>/images/black-logo.png"/>
				</a>
				<a href="<?php echo Yii::$app->urlManager->createUrl(['site/index']); ?>" class="mbl-logo">
					<img src="<?=$baseUrl?>/images/mobile-logo.png"/>
				</a>
			</div>
			<div class="page-name">Feedback</div>
		</div>	
	</div>
	<div class="main-content text-center">
		<div class="feedback-box bshadow">
		<?php if(isset($_GET['refid']) && !empty($_GET['refid']) && isset($_GET['userid']) && !empty($_GET['userid']) && $referal_exist && $user_exist) { ?>
		<div class="feedback-form">
			<h5>Please provide your feedback for "<?=$referal_exist['fullname']?>"</h5>
			<form class="givefeedback">
				<div class="dropdown dropdown-custom feedback-drop setDropVal">
					<a href="javascript:void(0)" class="dropdown-toggle"  data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						<span class="pc-text">Select Feedback</span>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li class="fb_positive"><a href="javascript:void(0)"><i class="mdi mdi-plus-square"></i>Positive</a></li>
						<li class="fb_neutral"><a href="javascript:void(0)"><i class="mdi mdi-bullseye"></i>Neutral</a></li>
						<li class="fb_negative"><a href="javascript:void(0)"><i class="mdi mdi-close-circle"></i>Negative</a></li>
						<input type="hidden" name="ref_point" id="ref_point"/>
					</ul>
				</div>
				<div class="clear"></div>
				<label>Write your comment here</label>
				<textarea class="materialize-textarea" id="feedbackcontent"></textarea>
				<input type="button" class="btn btn-primary" onclick="giveFeedback()" value="Submit"/>
				<input type="hidden" id="referal_id" value="<?=$refid?>"/>
				<input type="hidden" id="user_id" value="<?=$userid?>"/>
			</form>
		</div>
		<div class="feedback-success">
			<h5>Your feedback for "<span><?=$referal_exist['fullname']?>"</span> has been successfully submitted!</h5>
			<div class="clear"></div>
			<img src="<?=$baseUrl?>/images/feedback-submit.png"/>
			<div class="clear"></div>
			<a href="<?php echo Yii::$app->urlManager->createUrl(['site/index']); ?>">
				<input type="button" class="btn btn-primary" value="Go to home"/>
			</a>
		</div>
		<?php } else{ ?>
			<h5>Something went wrong...!!!</h5>
		<?php } ?>
		</div>
	</div>
</div>	
<?php $this->endBody() ?> 