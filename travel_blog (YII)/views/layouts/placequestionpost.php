<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');

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

$placeholder = 'What’s your question';
$place_type = 'ask';
$from_place_page = 'yes';
$is_place_data = 'yes';
$header_text='Be specific about your question';

?>   
<div id="compose_newask" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post">
	<div class="hidden_header">
		<div class="content_header">
			<button class="close_span cancel_poup waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>
			<p class="modal_header_xs">Ask question</p>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="post_btn action_btn post_btn_xs close_modal waves-effect"  onclick="addAsk()">Post</a>
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
					<a class="dropdown-button"  href="javascript:void(0)" data-activates="newpost_settings">
						<i class="zmdi zmdi-hc-2x zmdi-more"></i> 
					</a>
					<ul id="newpost_settings" class="dropdown-content custom_dropdown echeck-list">
						<li>
							<a href="javascript:void(0)">
								<input type="checkbox" id="addasktoolbox_disable_sharing" />
								<label for="addasktoolbox_disable_sharing">Disable Sharing</label>
							</a>
						</li>
						<li>
							<a href="javascript:void(0)">
								<input type="checkbox" id="addasktoolbox_disable_comments" />
								<label for="addasktoolbox_disable_comments">Disable Comments</label>
							</a>
						</li>
						<li>
							<a  onclick="clearPost()">Clear Post</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="npost-content">
				<div class="clear"></div>		
				<div class="npost-title title_post_container">	
					<input id="title" class="title" placeholder="Title of your question" type="text">				
				</div>	
				<div class="clear"></div>								
				<div class="desc">
					<textarea id="textInput"  placeholder="<?=$placeholder?>" class="materialize-textarea comment_textarea new_post_comment"></textarea>
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
				<input type="hidden" name="country" id="country" />					
				<input type="hidden" name="imageFile1" id="imageFile1" value="0" />
				<input type="hidden" name="imgfilecount" id="imgfilecount" value="0" />
				<input type="hidden" name="share_setting" id="share_setting" value="Enable"/>
				<input type="hidden" name="comment_setting" id="comment_setting" value="Enable"/>
				<input type="hidden" name="link_title" id="link_title" />
				<input type="hidden" name="link_url" id="link_url" />
				<input type="hidden" name="link_description" id="link_description" />
				<input type="hidden" name="link_image" id="link_image" />
				<input type="hidden" id="hiddenCount" value="1">
				<input type="hidden" name="post_privacy" id="post_privacy" value="Public"/>
				<div class="hidden_xs">
					<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
					<a class="btngen-center-align close_modal open_discard_modal waves-effect" tabindex="1">cancel</a>
					<a type="button" class="btngen-center-align waves-effect" tabindex="1" onclick="addAsk()">Post</a>
				</div>
			</div>
		</div>
	</div>		
</div>	
<script>
	$("body").on('input propertychange', '#textInput', function()
	{
		mobiletoggle();
	});
	$("body").on('input propertychange', '#title', function()
	{
		mobiletoggle();
	});
</script>