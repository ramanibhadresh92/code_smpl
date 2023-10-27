<?php
use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
$rand = rand(999, 99999).time();
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');
$fullname = $this->context->getuserdata($user_id,'fullname');

if(isset($user_id) && $user_id != '') {
    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
} else {
    $checkuserauthclass = 'checkuserauthclassg';
} 
?>   
<div class="hidden_header">
	<div class="content_header">
		<button class="close_span cancel_poup waves-effect">
			<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
		</button>
		<p class="modal_header_xs">Write tip</p>
		<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
		<a type="button" class="post_btn action_btn post_btn_xs waves-effect <?=$checkuserauthclass?>" onclick="addTip(this)">Post</a>
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
			<div class="npost-title title_post_container">									
				<input type="text" class="title" placeholder="Write a title." id="title">							
			</div>
			<div class="clear"></div>								
			<div class="desc">
				<textarea id="textInput" placeholder="Write your tip." class="materialize-textarea comment_textarea new_post_comment"></textarea>
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
				<a type="button" class="btngen-center-align postbtn waves-effect <?=$checkuserauthclass?>" tabindex="1" onclick="addTip(this)">Post</a>
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