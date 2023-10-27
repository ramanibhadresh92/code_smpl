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
    else if ($my_post_view_status == 'Friends')
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
		<a type="button" class="post_btn action_btn post_btn_xs waves-effect" onclick="addHomestayReview()">Post</a>
	</div>
</div>					
<div class="modal-content">
	<div class="new-post active">
		<div class="top-stuff">
			<div class="postuser-info">
				<span class="img-holder"><img src="<?=$this->context->getimage($user_id,'thumb');?>"></span>
				<div class="desc-holder">
					<span class="profile_name"><?= $fullname?></span>
					<label id="tag_person" class="tag_person_new"></label>
					<div class="public_dropdown_container damagedropdown">
						<a class="dropdown_text dropdown-button-left normalpostcreateprivacylabel" href="javascript:void(0)" onclick="privacymodal(this)" data-modeltag="normalpostcreateprivacylabel" data-fetch="no" data-label="normalpost">
							<span id="post_privacy" class="post_privacy_label active_check">Public</span>
							<i class="zmdi zmdi-caret-down"></i>
						</a>
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
			<div class="npost-title title_post_container">
				<input type="text" class="title npinput capitalize" placeholder="Title of this post" id="title">
			</div>
			<div class="clear"></div>	
			<div class="rating-stars setRating cus_starts">
                 <span>Your Rating</span>&nbsp;&nbsp; 
                 <span onmouseout="resettostart(this)">
                    <i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
                    <i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
                    <i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
                    <i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
                    <i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
                </span>&nbsp;&nbsp;
                <span class="star-text">Better</span>
          	</div>
			<div class="clear"></div>								
			<div class="desc">									
				<textarea type="text" class="textInput npinput capitalize materialize-textarea comment_textarea new_post_comment" id="textInput"  placeholder="Write your review"></textarea>
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
	<div class="post-bcontent">
		<div class="footer_icon_container">
			<button class="comment_footer_icon waves-effect compose_titleAction" id="compose_titleAction">
				<img src="<?=$baseUrl?>/images/addtitleBl.png">
			</button>
		</div>
		<div class="public_dropdown_container_xs damagedropdown">
			<a class="dropdown_text dropdown-button-left normalpostcreateprivacylabel" href="javascript:void(0)" onclick="privacymodal(this)" data-modeltag="normalpostcreateprivacylabel" data-fetch="no" data-label="normalpost">
				<span id="post_privacy2" class="post_privacy_label">Public</span>
				<i class="zmdi zmdi-caret-down"></i>
			</a>
		</div>
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
				<a href="javascript:void(0)" class="mainbtn btngen-center-align btn-flat waves-effect btn-flat  submit" onclick="addHomestayReview()">Post</a>
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