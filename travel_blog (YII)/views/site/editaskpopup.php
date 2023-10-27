<?php
use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;

$rand  = rand(999, 999999). time();
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');

$pageid = $post['post_user_id'];
$fullname = $session->get('fullname');

if($user_id == $post['post_user_id'] || $user_id == $post['shared_by']) 
{ 
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
				<p class="modal_header_xs">Ask question</p>
				<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
				<a type="button" class="post_btn action_btn post_btn_xs close_modal waves-effect"  onclick="edit_ask('<?=$post['_id']?>');">Save</a>
			</div>
		</div>					  
		<div class="modal-content">
			<div class="new-post active">
				<div class="top-stuff">
					<div class="postuser-info">
						<span class="img-holder"><img src="<?=$this->context->getimage($user_id,'thumb');?>"></span>
						<div class="desc-holder">
							<span class="profile_name"><?= $fullname?></span>
							<label id="tag_person"></label>
							<div class="public_dropdown_container">
								<a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="addask_privacy_compose">
									<span class="sword"><?= $my_post_view_status ?></span>
									<i class="zmdi zmdi-caret-down"></i>
								</a>
								<ul id="addask_privacy_compose" class="dropdown-privacy dropdown-content custom_dropdown">
									<li class="post-private"><a href="javascript:void(0)">Private</a></li>
									<li class="post-connections"><a href="javascript:void(0)">Connections</a></li>
									<li class="post-settings"><a href="javascript:void(0)">Custom</a></li>
									<li class="post-public"><a href="javascript:void(0)">Public</a></li>
								</ul>
							</div>
						</div>
					</div>
					<div class="settings-icon">
						<a href="javascript:void(0)" onclick="clearPost()">
							<i class="zmdi zmdi-refresh-alt"></i>
						</a>
					</div>
				</div>
				<div class="npost-content">
					<div class="clear"></div>		
					<div class="npost-title title_post_container title_block">	
						<input id="title" class="title" name="title" placeholder="Title of your question" type="text" value="<?=$post['post_title']?>">				
					</div>	
					<div class="clear"></div>								
					<div class="desc">
						<textarea id="edittextInput" placeholder="What’s your question?" class="materialize-textarea comment_textarea new_post_comment"><?= $post['post_text'] ?></textarea>
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
					<div class="hidden_xs">
						<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
						<a class="btngen-center-align close_modal open_discard_modal waves-effect" tabindex="1">cancel</a>
						<a type="button" class="btngen-center-align waves-effect" tabindex="1" onclick="edit_ask('<?=$post['_id']?>');">Save</a>
					</div>
				</div>
			</div>
		</div>		
    <?php 
}	
?>
<?php exit; ?>