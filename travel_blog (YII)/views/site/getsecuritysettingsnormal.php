<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use frontend\models\LoginForm;
use frontend\models\CountryCode;
use frontend\models\UserSetting;
use frontend\models\Personalinfo;
use frontend\models\SecuritySetting;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();

$my_view_status = (isset($result_security['my_view_status']) && !empty($result_security['my_view_status'])) ? $result_security['my_view_status'] : 'Public';

$connect_request = (isset($result_security['connect_request']) && !empty($result_security['connect_request'])) ? $result_security['connect_request'] : 'Public';

$connect_list = (isset($result_security['connect_list']) && !empty($result_security['connect_list'])) ?  $result_security['connect_list'] : 'Public';

$view_photos = (isset($result_security['view_photos']) && !empty($result_security['view_photos'])) ? $result_security['view_photos'] : 'Public';	

$my_post_view_status_new =(isset( $result_security['my_post_view_status']) && !empty( $result_security['my_post_view_status'])) ? $result_security['my_post_view_status'] : 'Public'; 

$add_public_wall = (isset($result_security['add_public_wall']) && !empty($result_security['add_public_wall'])) ? $result_security['add_public_wall'] : 'Public';

$review_posts = (isset($result_security['review_posts']) && !empty($result_security['review_posts'])) ? $result_security['review_posts'] : 'Disabled';

$review_tags = (isset($result_security['review_tags']) && !empty($result_security['review_tags'])) ? $result_security['review_tags'] : 'Disabled';

$add_post_on_your_wall_view = (isset($result_security['add_post_on_your_wall_view']) && !empty($result_security['add_post_on_your_wall_view'])) ? $result_security['add_post_on_your_wall_view'] : 'Public';
?>
<!-- Security Question -->				
<li class="securityquestionli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Security Question</label>
					</div>
				</div>
				<div class="col s12 m9 l9 detail-holder">
					<div class="info">
						<label>Set your security question</label>
					</div>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Lookup Setting -->
<li class="lookupsettingli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Lookup Setting</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who can look me up?</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">
					<span class="security-setting lookupsettingdisplay"><?=$my_view_status?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Connect Request Settings -->
<li class="connectrequestsettingsli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Connect Request Settings</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who can send me connect requests?</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">		
					<span class="security-setting connectrequestsettingsdisplay"><?=$connect_request?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Connect List -->
<li class="connectlistli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Connect List</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who should see my connect list?</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">				
					<span class="security-setting connectlistdisplay"><?=$connect_list?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Photo Security -->
<li class="photosecurityli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Photo Security</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who can see my photos?</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">
					<span class="security-setting photosecuritydisplay"><?=$view_photos?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Post Security -->
<li class="postsecurityli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Post Security</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who can see my posts?</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">									
					<span class="security-setting postsecuritydisplay"><?=$my_post_view_status_new?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Post on wall -->
<li class="postingpermissionli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Post on wall</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who can add stuff to my public Wall</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">									
					<span class="security-setting postingpermissiondisplay"><?=$add_public_wall?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Post Review -->
<li class="postreviewli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Post Review</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Review posts connections tag you in before they appear on your public wall</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">											
					<span class="security-setting postreviewdisplay"><?=$review_posts?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- Tag Reviews -->
<li class="tagreviewsli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Tag Reviews</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Review tags people add to your own posts before the tags appear on site</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">										
					<span class="security-setting tagreviewsdisplay"><?=$review_tags?></span>
				</div>
			</div>
		</div>
	</div>
</li>

<!-- View Permission -->
<li class="activitypermissionli">
	<div class="settings-group">
		<div class="normal-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>View Permission</label>
					</div>
				</div>

				<div class="col s12 m6 l7 detail-holder">
					<div class="info">
						<label>Who can see what others post on your public Wall</label>
					</div>
				</div>

				<div class="col s12 m3 l2 btn-holder has-security">										
					<span class="security-setting activitypermissiondisplay"><?=$add_post_on_your_wall_view?></span>
				</div>
			</div>
		</div>
	</div>
</li>
<?php
exit;