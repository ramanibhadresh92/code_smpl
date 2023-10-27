<?php 
use frontend\models\Like;
use frontend\models\LoginForm;
use frontend\models\UserForm;
use frontend\models\UnfollowConnect;
use frontend\models\HidePost;
use frontend\models\Comment;
use frontend\models\PostForm; 
use frontend\models\SavePost;
use frontend\models\Personalinfo;
use frontend\models\HideComment;
use frontend\models\Connect; 
use frontend\models\TravAds;
use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\ReportPost; 
use frontend\models\BlockConnect; 

$session = Yii::$app->session;
$email = $session->get('email'); 
$result = UserForm::find()->where(['email' => $email])->one();
$userid = $user_id =  (string)$session->get('user_id');

if(isset($userid) && $userid != '') {
	$authstatus = UserForm::isUserExistByUid($user_id);
} else {
	$authstatus = 'checkuserauthclassg';
} 

$baseUrl = AppAsset::register($this)->baseUrl;  
$unfollow = new UnfollowConnect();
$unfollow = UnfollowConnect::find()->where(['user_id' => (string)$user_id])->one();
$profile_tip_counter = 1;
$_SESSION['loadedAds'] = array();
$isEmpty = true;

?>
<div class="col s12 m12">  
	<div class="new-post base-newpost">
		<form action="">
			<div class="npost-content">
				<div class="post-mcontent">
					<i class="mdi mdi-pencil-box-outline main-icon"></i>
					<div class="desc">									
						<div class="input-field comments_box">
							<input placeholder="What's new?" type="text" class="validate commentmodalAction_form" disabled="disabled" readonly />
						</div>
					</div>
				</div>
			</div>				
			
		</form>
		<div class="overlay <?=$authstatus?>" id="composetoolboxAction"></div>
	</div>
</div>
 
<div class="post-list margint15"> 
	<div class="row">
		<?php if(count($posts) > 0 ) { ?>
			<?php
			$lpDHSU = 1; 
			
			foreach($posts as $post)
			{  
				// you are exist in post owner restartiction list....
				$post_user_id = $post['post_user_id'];         
				$SecuritySetting = SecuritySetting::find()->where(['user_id' => $post_user_id])->asarray()->one();
				if(isset($userid) && !empty($userid)){
					if(!empty($SecuritySetting)) {
						$filterrestrict = isset($SecuritySetting['restricted_list']) ? $SecuritySetting['restricted_list'] : '';
						$filterrestrict = explode(",", trim($filterrestrict));
						if(in_array($userid, $filterrestrict)) {
							continue;
						}
					}
				}
				
				$existing_posts = '1';
				$cls = '';

				$postid = (string)$post['_id'];
				$postownerid = (string)$post['post_user_id'];
				$postprivacy = $post['post_privacy'];
 
				$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
				if($isOk == 'ok2389Ko') {
					if(($lpDHSU%8) == 0) {
						$ads = $this->context->getad(true);
						if(isset($ads) && !empty($ads))
						{
							$ad_id = (string) $ads['_id'];  
							$this->context->display_last_post($ad_id, $existing_posts, '', $cls,'','restingimagefixes','',$lpDHSU);
						}
					}
					
					$this->context->display_last_post((string)$post['_id'], $existing_posts, '', $cls,'','restingimagefixes','',$lpDHSU);
					$lpDHSU++;	
				}

				$isEmpty = false;
			} 
			?>
		<?php } ?> 
	</div>
</div>

<div class="clear"></div>
<center><div class="lds-css ng-scope dis-none"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
<?php
    if($isEmpty == true) 
    { 	
		$this->context->getwelcomebox("post");
	} 
?>
