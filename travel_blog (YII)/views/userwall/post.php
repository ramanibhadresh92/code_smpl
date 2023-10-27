<?php
use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\Notification;
use frontend\models\Personalinfo;

$baseUrl = AppAsset::register($this)->baseUrl;
$user_data = Personalinfo::find()->where(['user_id' => (string)$wall_user_id])->one();
?>

<div class="post-column">
<?php
	$isWriteAllow = 'no';

	if($guserid == $suserid) {
		$isWriteAllow = 'yes';	    	
	} else if(($add_post_on_wall == 'Public') || 
		($add_post_on_wall == 'Connections' && ($is_connect)) || 
		($add_post_on_wall == 'Private')) {
		$isWriteAllow = 'yes';
	} else if($add_post_on_wall == 'Custom') {
		$add_public_wall_custom = array();
		if($add_post_on_wall == 'Custom') {
		    $add_public_wall_custom = isset($result_security['add_public_wall_custom']) ? $result_security['add_public_wall_custom'] : '';
		    $add_public_wall_custom = explode(',', $add_public_wall_custom);

		    if(in_array($user_id, $add_public_wall_custom)) {
				$isWriteAllow = 'yes';	    	
		    }
		}
	}

	if($isWriteAllow = 'yes') {  	
	?>
		<div class="new-post base-newpost">
			<form action="">
				<div class="npost-content">
					<div class="post-mcontent">
						<i class="mdi mdi-pencil-box-outline main-icon"></i>
							<div class="desc">
							<div class="input-field comments_box">
								<input placeholder="What's new?" class="validate commentmodalAction_form" type="text">							
							</div>
							</div>
					</div>
				</div>				
			</form>
            <div class="overlay" id="composetoolboxAction"></div>
	    </div>
	<?php 
	} else {
		if(count($posts) == 0) {
			$this->context->getwelcomebox("post");
		} 
	} ?>
	<input type="hidden" name="pagename" id="pagename" value="wall" />
	<input type="hidden" name="tlid" id="tlid" value="<?=$wall_user_id?>" />
	<?php
	$fullname = $this->context->getuserdata($wall_user_id,'fullname');
	$result_security = SecuritySetting::find()->where(['user_id' => $wall_user_id])->one();
	$my_post_view_status = $result_security['my_post_view_status'];
	if($my_post_view_status == 'Connections' && !($is_connect)){ ?>
	<div class="post-list">
		<div class="row">
		<span class="no-listcontent">You have to be a connect with <?=$fullname?> to see <?=$gender?> posts</span>
		</div>
	</div>	
	<?php } 
	else { ?>
	<div class="post-list">
	<div class="row">
	<?php
	if(count($posts) == 0){ ?>
		 <div class="post-holder bshadow">      
			<div class="joined-tb">
				<i class="mdi mdi-home"></i>        
				<h4>Welcome to Iaminjapan</h4>
				<p>Add connections to see more posts and photos in your feed</p>
			</div>    
		</div>
	<?php } else { 
	$lp = 1; 
	foreach($posts as $post)
	{ 
		$tag_review_on = Notification::find()->where(['notification_type' => 'tag_connect','user_id' => (string) $user_id,'post_id'=> (string) $post['_id']])->one();
		if($tag_review_on['review_setting'] == 'Enabled'){
			continue;
		}
		
		$existing_posts = '1'; 
		$cls = '';

		$postid = (string)$post['_id'];
		$postownerid = (string)$post['post_user_id'];
		$postprivacy = $post['post_privacy'];

		$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
		if($isOk == 'ok2389Ko') {
			if(($lp%8) == 0) {

				$ads = $this->context->getad(true); 
				if(isset($ads) && !empty($ads))
				{
					$ad_id = (string) $ads['_id'];	
					$this->context->display_last_post($ad_id, $existing_posts, '', $cls);
					$lp++;
				}
			} else {
				$this->context->display_last_post((string)$postid, $existing_posts, '', $cls);
				$lp++;	
			}						
		}
	}
	}
	?>
	</div>
	</div>
	<?php } ?>
	<div class="clear"></div>
	<center><div class="lds-css ng-scope dis-none"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
</div>