<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');

if(isset($user_id) && $user_id != '') {
    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
} else {
    $checkuserauthclass = 'checkuserauthclassg';
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

$rand = rand(999, 9999).time();

?>   
<div class="hidden_header">
	<div class="content_header">
		<button class="close_span cancel_poup waves-effect">
			<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
		</button>
		<p class="modal_header_xs">Ask question</p>
		<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
		<a type="button" class="post_btn action_btn post_btn_xs close_modal waves-effect <?=$checkuserauthclass?>"  onclick="addAsk(this)">Post</a>
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
			<div class="hidden_xs">
				<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
				<a class="btngen-center-align close_modal open_discard_modal waves-effect" tabindex="1">cancel</a>
				<a type="button" class="btngen-center-align waves-effect <?=$checkuserauthclass?>" tabindex="1" onclick="addAsk(this)">Post</a>
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
<?php exit; ?>