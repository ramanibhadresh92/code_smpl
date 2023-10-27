<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');
$time = time();
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

$fullname = $this->context->getuserdata($user_id,'fullname');

$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
if ($result_security)
{
    $my_post_view_status = $result_security['my_post_view_status'];
    if ($my_post_view_status == 'Private')
    {
        $post_dropdown_class = 'lock';
    }
    else if ($my_post_view_status == 'Connections')
    {
        $post_dropdown_class = 'user';
    }
    else
    {
        $my_post_view_status = 'Public';
        $post_dropdown_class = 'globe';
    }
}
else
{
    $my_post_view_status = 'Public';
    $post_dropdown_class = 'globe';  
}

$place_type = 'none';
$from_place_page = 'no';
$is_place_data = 'no';
$header_text='';
$label = 'Write Post';

if(strstr($_SERVER['HTTP_REFERER'],'r=tripexperience'))
{	
	$label = 'Write a experience';
    $placeholder = 'Share your trip experience with others';
	//$from_place_page = 'yes';
}
else if(strstr($_SERVER['HTTP_REFERER'],'r=tripstory'))
{	
	$label = 'Write a experience';
    $placeholder = 'Share your trip story with others';
}
else
{
    $placeholder = "What's new?";
}
?>    
	<div class="hidden_header">
		<div class="content_header">
			<button class="close_span cancel_poup waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>
			<p class="modal_header_xs">
			<?php 
			if(strstr($_SERVER['HTTP_REFERER'],'r=tripexperience')) {
				echo 'Create trip experience';
			} else {
				echo 'Write Post';
			} ?>
			</p>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="post_btn action_btn post_btn_xs waves-effect"  onclick="addNewPost()">Post</a>				
		</div>
	</div>
	<div class="modal-content <?php if(strstr($_SERVER['HTTP_REFERER'],'r=collection/detail')){ echo 'final';} ?>">
		<div class="new-post active" id="directcall_new_post">			
			<div class="top-stuff">
				<div class="postuser-info">
					<span class="img-holder"><img src="<?=$this->context->getimage($user_id,'thumb');?>"></span>
					<div class="desc-holder">
						<span class="profile_name"><?= $fullname?></span>
						<label id="tag_person" class="tag_person_new"></label>
						<?php if($from_place_page == 'no'){ ?>
						<div class="public_dropdown_container damagedropdown">
							<a class="dropdown_text dropdown-button-left normalpostcreateprivacylabel" href="javascript:void(0)" onclick="privacymodal(this)" data-modeltag="normalpostcreateprivacylabel" data-fetch="no" data-label="normalpost">
								<span id="post_privacy" class="post_privacy_label active_check">Public</span>
								<i class="zmdi zmdi-caret-down"></i>
							</a>
						</div>
						<?php } ?>
					</div>
				</div>
				<div class="settings-icon">
					<div class="dropdown dropdown-custom dropdown-xxsmall">
						<a class="dropdown-button more_btn"  href="javascript:void(0)" data-activates="sharing_setting_btns">
							<i class="zmdi zmdi-more"></i>
						</a>
						<ul id="sharing_setting_btns" class="dropdown-content custom_dropdown echeck-list">
						<?php if($is_place_data != 'yes'){ ?>
							<li class="disable_share">
								<a href="javascript:void(0)">
									<input type="checkbox" id="<?=$time?>toolbox_disable_sharing" class="toolbox_disable_sharing" />
									<label for="<?=$time?>toolbox_disable_sharing">Disable Sharing</label>
								</a>
							</li>
							<li class="disable_comment">
								<a href="javascript:void(0)">
									<input type="checkbox" id="<?=$time?>toolbox_disable_comments" class="toolbox_disable_comments" />
									<label for="<?=$time?>toolbox_disable_comments">Disable Comments</label>
								</a>
							</li>
						<?php } ?>
							<li class="cancel_post">
								<a  onclick="clearPost()">Clear Post</a>
							</li>
						</ul>
					</div>
				</div>		 													
			</div> 							
			<div class="npost-content">
				<div class="npost-title title_post_container">
					<input type="text" class="title npinput capitalize" placeholder="Title of this post" id="title">
				</div>
				<div class="clear"></div>								
				<div class="desc">									
					<textarea type="text" class="textInput npinput capitalize materialize-textarea comment_textarea new_post_comment" id="textInput"  placeholder="<?=$placeholder?>"></textarea>
				</div>
				<div class="post-photos">
					<div class="img-row">
					</div>
				</div>								
				<div class="location_parent">
					<label id="selectedlocation" class="selected_loc"></label>
				</div>
			</div>          
		</div>	
	</div>
	<div class="modal-footer">
		<div class="post-bcontent" id="directcall_new_post">
			<div class="footer_icon_container">
				<?php if($place_type != 'reviews' && $place_type != 'ask'){ ?>
				<button class="comment_footer_icon waves-effect" id="compose_uploadphotomodalAction">
					<i class="zmdi zmdi-camera"></i>
				</button>
				<?php } ?>
				<?php if($place_type != 'tip' && $place_type != 'ask'){ ?>
				<button class="comment_footer_icon waves-effect compose_addpersonAction" id="compose_addpersonAction">
					<i class="zmdi zmdi-account"></i>
				</button>
				<?php } ?>
				<?php if($is_place_data == 'no'){ ?>
				<button class="comment_footer_icon waves-effect" data-query="all" onfocus="filderMapLocationModal(this)">
					<i class="zmdi zmdi-pin"></i>
				</button>
				<?php } ?>
				<button class="comment_footer_icon waves-effect compose_titleAction" id="compose_titleAction">
					<img src="<?=$baseUrl?>/images/addtitleBl.png">
				</button>
			</div>
			<?php if($from_place_page == 'no'){ ?>
			<div class="public_dropdown_container_xs damagedropdown">
				<a class="dropdown_text dropdown-button-left normalpostcreateprivacylabel" href="javascript:void(0)" onclick="privacymodal(this)" data-modeltag="normalpostcreateprivacylabel" data-fetch="no" data-label="normalpost">
					<span id="post_privacy2" class="post_privacy_label">Public</span>
					<i class="zmdi zmdi-caret-down"></i>
				</a>
			</div>
			<?php } ?>
			<div class="post-bholder">
				<input type="hidden" name="country" id="country" />					
				<input type="hidden" name="imgfilecount" id="imgfilecount" value="0" />
				<input type="hidden" name="share_setting" id="share_setting" value="Enable"/>
				<input type="hidden" name="comment_setting" id="comment_setting" value="Enable"/>
				<input type="hidden" name="link_title" id="link_title" />
				<input type="hidden" name="link_url" id="link_url" />
				<input type="hidden" name="link_description" id="link_description" />
				<input type="hidden" name="link_image" id="link_image" />
				<input type="hidden" id="hiddenCount" value="1">
				<div class="hidden_xs">
					<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
					<a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal waves-effect">cancel</a>
					<a href="javascript:void(0)" class="mainbtn btngen-center-align btn-flat waves-effect btn-flat disabled submit" onclick="addNewPost()">Post</a>
				</div>
			</div>
		</div>			
	</div> 
<?php 
exit;