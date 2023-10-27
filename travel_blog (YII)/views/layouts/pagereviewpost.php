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

$header_text='';
$placeholder = 'Write your review';
$place_type = 'reviews';
$from_place_page = 'yes';
$is_place_data = 'yes';

?>
<div class="hidden_header">
	<div class="content_header">
		<button class="close_span cancel_poup waves-effect">
			<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
		</button>
		<p class="modal_header_xs">Write review</p>
		<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
		<a type="button" class="post_btn action_btn post_btn_xs waves-effect" onclick="addPlaceReview()">Post</a>
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
			<?php if($place_type == 'reviews'){ ?>		
			<div class="rating-stars setRating cus_starts" onmouseout="setStarText(this,6)">
				<span>Your Rating</span>&nbsp;&nbsp;<i class="mdi mdi-star active" data-value="1" onmouseover="setStarText(this,1)" onclick="setRating(this,1)"></i>
				<i class="mdi mdi-star" data-value="2" onmouseover="setStarText(this,2)" onclick="setRating(this,2)"></i>
				<i class="mdi mdi-star" data-value="3" onmouseover="setStarText(this,3)" onclick="setRating(this,3)"></i>
				<i class="mdi mdi-star" data-value="4" onmouseover="setStarText(this,4)" onclick="setRating(this,4)"></i>
				<i class="mdi mdi-star" data-value="5" onmouseover="setStarText(this,5)" onclick="setRating(this,5)"></i>&nbsp;&nbsp;<span class="star-text">Poor</span>
			</div>
			<?php } ?>
			<div class="clear"></div>								
			<div class="desc">
				<textarea id="textInput" placeholder="<?=$placeholder?>" class="materialize-textarea comment_textarea new_post_comment"></textarea>
			</div>					
		</div>
	</div>
</div> 
<div class="modal-footer">
	<div class="post-bcontent">
		<div class="footer_icon_container">
			<button class="comment_footer_icon waves-effect" id="compose_uploadphotomodalAction">
				<i class="zmdi zmdi-camera"></i>
			</button>

			<button class="comment_footer_icon waves-effect compose_addpersonAction" id="compose_addpersonAction">
				<i class="zmdi zmdi-account"></i>
			</button>

			<button class="comment_footer_icon waves-effect" data-query="all" onfocus="filderMapLocationModal(this)">
				<i class="zmdi zmdi-pin"></i>
			</button>
			
			<button class="comment_footer_icon waves-effect compose_titleAction" id="compose_titleAction">
				<img src="<?=$baseUrl?>/images/addtitleBl.png">
			</button>
		</div>
		<div class="post-bholder">
			<div class="hidden_xs">
				<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
				<a class="btngen-center-align close_modal open_discard_modal waves-effect" tabindex="1">cancel</a>
				<a type="button" class="btngen-center-align waves-effect" tabindex="1" onclick="addPlaceReview()">Post</a>
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