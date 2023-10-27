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

$security_questions = (isset($result_security['security_questions']) && !empty($result_security['security_questions'])) ? $result_security['security_questions'] : '';

$securitygetdafault = (isset($result_security[$security_questions]) && !empty($result_security[$security_questions])) ? $result_security[$security_questions] : '';


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
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Security Question</label>
					</div>
				</div>
				<div class="col s12 m9 l9">
					<div class="row">
						<div class="col s12 m6 l6">
							<div class="sliding-middle-out anim-area underlined fullwidth">
								<select id="securityquestion" name="security_questions">		
									<option value="eml_ans" <?=($security_questions == 'eml_ans') ? 'selected' : '' ?>>What's your mother's name?</option>
									<option value="born_ans" <?=($security_questions == 'born_ans') ? 'selected' : '' ?>>Which city you were born in?</option>
									<option value="gf_ans"<?=($security_questions == 'gf_ans') ? 'selected' : '' ?>>What is your girlconnect's name?</option>
								</select>
							</div>
						</div>												
					</div>	
					<div class="row">
						<div class="col s12 m6 l6">

							<div class="eyeicon">						
								<?php
									$placeholder = 'Plase enter your mother\'s name';
									if($securitygetdafault == 'eml_ans') {
										$placeholder = 'Plase enter your mother\'s name';
									} elseif($securitygetdafault == 'eml_ans') {
										$placeholder = 'Plase enter your born place';
									} elseif($securitygetdafault == 'eml_ans') {
										$placeholder = 'Plase enter your girlconnect\'s name';
									}
								?>		

								<div class="sliding-middle-out anim-area underlined fullwidth">
									<input type="text" class="securityanswerinput title" name="securityanswer" id="securityanswer" placeholder="<?=$placeholder?>" value="<?=$securitygetdafault?>"/>
								</div>
							</div>
						</div>												
					</div>						
				</div>		
			</div>	
		</div>
	</div>
</li>

<!-- Lookup Setting -->
<li class="lookupsettingli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption"> 
						<label>Lookup Setting</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who can look me up?</label>
					</div>									 
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor lookupsettingprivacylabel" href="javascript:void(0)" data-modeltag="lookupsettingprivacylabel" data-fetch="yes" data-label="lookupsetting" data-activates="privancy1" id="my_view_status">
                       <span class="getvalue"><?=$my_view_status?></span> <span class="caret"></span>
                    </a> 
					<ul id='privancy1' class='dropdown-content'>
						<li class="selectore"><a href="javascript:void(0)">Connections</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>														
				</div>										
			</div>
		</div>
	</div>
</li>

<!-- Connect Request Settings -->
<li class="connectrequestsettingsli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Connect Request Settings</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who can send me connect requests?</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor connectrequestsettingsspan connectrequestsettingprivacylabel" href="javascript:void(0)" data-modeltag="connectrequestsettingprivacylabel" data-fetch="yes" data-label="connect_request" data-activates="privancy5" id="connect_request">
                       <span class="getvalue"><?=$connect_request?></span> <span class="caret"></span>
                    </a> 
					<ul id="privancy5" class="dropdown-content ftof">
						<li class="selectore"><a href="javascript:void(0)">Private</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>					
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- Connect List -->
<li class="connectlistli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Connect List</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who should see my connect list?</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor connectlistspan connectlistprivacylabel" href="javascript:void(0)" data-modeltag="connectlistprivacylabel" data-fetch="yes" data-label="connect_list" data-activates="privancy12" id="connect_list">
                       <span class="getvalue"><?=$connect_list?></span> <span class="caret"></span>
                    </a>
					<ul id="privancy12" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Private</a></li>
						<li class="selectore"><a href="javascript:void(0)">Connections</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>								
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- Photo Security -->
<li class="photosecurityli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Photo Security</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who can see my photos?</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor photosecurityspan photosecurityprivacylabel view_photos" href="javascript:void(0)" data-modeltag="photosecurityprivacylabel" data-fetch="yes" data-label="photosecurity" data-activates="privancy3" id="view_photos">
						<span class="getvalue"><?=$view_photos?></span> <span class="caret"></span>
					</a> 
					<ul id="privancy3" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Private</a></li>
						<li class="selectore"><a href="javascript:void(0)">Connections</a></li>
						<li class="selectore customli_modal"><a href="javascript:void(0)">Custom</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>								
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- Post Security -->
<li class="postsecurityli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Post Security</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who can see my posts?</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor postsecurityspan postprivacylabel my_post_view_status" href="javascript:void(0)" data-modeltag="postprivacylabel" data-fetch="yes" data-label="postprivacy" data-activates="privancy2" id="my_post_view_status">
						<span class="getvalue"><?=$my_post_view_status_new?></span> <span class="caret"></span>
					</a>
					<ul id="privancy2" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Connections</a></li>
						<li class="selectore customli_modal"><a href="javascript:void(0)">Custom</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>								
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- Post on wall -->
<li class="postingpermissionli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Post on wall</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who can add stuff to my public Wall</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor postonwallprivacylabel add_public_wall" href="javascript:void(0)" data-modeltag="postonwallprivacylabel" data-fetch="yes" data-label="postonwallprivacy" data-activates="privancy6" id="add_public_wall">
						<span class="getvalue"><?=$add_public_wall?></span> <span class="caret"></span>
					</a>
					<ul id="privancy6" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Private</a></li>
						<li class="selectore"><a href="javascript:void(0)">Connections</a></li>
						<li class="selectore customli_modal"><a href="javascript:void(0)">Custom</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>			
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- Post Review -->
<li class="postreviewli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Post Review</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Review posts connections tag you in before they appear on your public wall</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor postreviewspan postreviewprivacylabel" href="javascript:void(0)" data-modeltag="postreviewprivacylabel" data-fetch="yes" data-label="post_review" data-activates="privancy7" id="review_posts">
                       <span class="getvalue"><?=$review_posts?></span> <span class="caret"></span>
                    </a> 
					<ul id="privancy7" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Enabled</a></li>
						<li class="selectore"><a href="javascript:void(0)">Disabled</a></li>
					</ul>								
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- Tag Reviews -->
<li class="tagreviewsli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>Tag Reviews</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Review tags people add to your own posts before the tags appear on site</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor tagreviewsspan tagreviewprivacylabel" href="javascript:void(0)" data-modeltag="tagreviewprivacylabel" data-fetch="yes" data-label="tag_review" data-activates="privancy10" id="review_tags">
                       <span class="getvalue"><?=$review_tags?></span> <span class="caret"></span>
                    </a>
					<ul id="privancy10" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Enabled</a></li>
						<li class="selectore"><a href="javascript:void(0)">Disabled</a></li>
					</ul>					
				</div>
			</div>	
		
		</div>
	</div>
</li>

<!-- View Permission -->
<li class="activitypermissionli">
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 caption-holder">
					<div class="caption">
						<label>View Permission</label>
					</div>
				</div>
				<div class="col s12 m7 l7">
					<div class="info">
						<label>Who can see what others post on your public Wall</label>
					</div>									
				</div>									
				<div class="col s12 m2 l2 right">
					<a class="dropdown-button nothemecolor activitypermissionprivacylabel add_post_on_your_wall_view" href="javascript:void(0)" data-modeltag="activitypermissionprivacylabel" data-fetch="yes" data-label="activitypermissionprivacy" data-activates="privancy121" id="add_post_on_your_wall_view">
						<span class="getvalue"><?=$add_post_on_your_wall_view?></span> <span class="caret"></span>
					</a>
					<ul id="privancy121" class="dropdown-content">
						<li class="selectore"><a href="javascript:void(0)">Private</a></li>
						<li class="selectore"><a href="javascript:void(0)">Connections</a></li>
						<li class="selectore customli_modal"><a href="javascript:void(0)">Custom</a></li>
						<li class="selectore"><a href="javascript:void(0)">Public</a></li>
					</ul>	
				</div>
			</div>	
		</div>
	</div>
</li>

<li>
    <div class="personal-info fullwidth edit-mode">
        <div class="right">                                   
           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_act_ss_cl(false)">Cancel</a>                                    
           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_act_ss_cl(true)">Save</a>
        </div>
    </div>
</li>
<?php
exit;