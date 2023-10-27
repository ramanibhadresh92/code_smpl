<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');
$fullname = $session->get('fullname');
$pageid = $post['post_user_id'];
$post_type = '';

if($user_id == $post['post_user_id'] || $user_id == $post['shared_by']) {
    $post['post_user_id'] = $pageid;
    $my_post_view_status = $post['post_privacy'];
    
    $share_setting = '';
	$comment_setting = '';
	if(isset($post['share_setting']) && $post['share_setting'] == 'Disable') { 
		$share_setting = 'checked';
	}
	if(isset($post['comment_setting']) && $post['comment_setting'] == 'Disable') { 
		$comment_setting = 'checked';
	}
    ?> 
    <div class="hidden_header">
		<div class="content_header">
			<button class="close_span cancel_poup waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>
			<p class="modal_header_xs">Write tip</p>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="post_btn action_btn post_btn_xs waves-effect" onclick="edit_tip('<?=$post['_id']?>');">Save</a>
		</div>
	</div>					
	<div class="modal-content"> 
		<div class="new-post active">
			<div class="top-stuff">
				<div class="postuser-info">
					<span class="img-holder">
						<img src="<?=$this->context->getimage($user_id,'thumb');?>">
					</span>
					<div class="desc-holder">
						<span class="profile_name"><?= $fullname?></span>
						<label id="tag_person"></label> 
					</div>
				</div>
				<div class="settings-icon">
					<a href="javascript:void(0)" onclick="clearPost()">
						<i class="zmdi zmdi-refresh-alt"></i>
					</a>
				</div>
			</div>
			<div class="npost-content">
				<div class="npost-title title_post_container title_block">									
					<input type="text" class="title" value="<?=$post['post_title']?>" placeholder="Write a title." id="edittiptitle">
				</div>
				<div class="clear"></div>		
				<div class="desc">
					<textarea id="edittiptextInput" placeholder="Write your tip." class="materialize-textarea comment_textarea new_post_comment"><?= $post['post_text'] ?></textarea>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div class="post-bcontent">
			<div class="footer_icon_container">
				<button class="comment_footer_icon waves-effect compose_titleAction" id="compose_titleAction">
					<img src="<?=$baseUrl?>/images/addtitleBl.png">
				</button>
			</div>
			<div class="post-bholder">
				<input type="hidden" class="imgfile-count" value="0" />
				<input type="hidden" class="counter">
				<input type="hidden" name="comment_setting" id="comment_setting" value="Enable"/>
				<div class="hidden_xs">
					<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
					<a class="btngen-center-align close_modal open_discard_modal waves-effect" tabindex="1">cancel</a>
					<a type="button" class="btngen-center-align waves-effect" tabindex="1" onclick="edit_tip('<?=$post['_id']?>');">Save</a>
				</div>
			</div>
		</div>
	</div>
    <?php 
}	
?>
<?php exit; ?>