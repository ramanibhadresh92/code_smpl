<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');
$fullname = $this->context->getuserdata($user_id,'fullname');
$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
?>

<div class="hidden_header">
	<div class="content_header">
		<button class="close_span cancel_poup waves-effect">
			<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
		</button>
		<p class="modal_header_xs">Write a reference</p>
		<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
		<a type="button" class="post_btn action_btn post_btn_xs close_modal waves-effect" onclick="addReferal()">Post</a>
	</div>
</div>
<div class="modal-content  text-Reference">
	<div class="new-post active">
		<div class="top-stuff">
			<div class="postuser-info">
				<span class="img-holder"><img src="<?=$this->context->getimage($user_id,'thumb');?>"></span>
				<div class="desc-holder">
					<span class="profile_name"><?= $fullname?></span>
					<label id="tag_person"></label>
					<div class="public_dropdown_container">
						<span class="recomm">Recommanding as</span> 
						<a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="post_privacy_compose12">
							<span class="getvalue1">Personal</span>
							<i class="zmdi zmdi-caret-down"></i>
						</a>
						<ul id="post_privacy_compose12" class="dropdown-privacy dropdown-content custom_dropdown " >
							<li><a class ="reco_cate" cat_name= "personal" href="javascript:void(0)">Personal</a></li>
							<li><a class ="reco_cate" cat_name= "traveller" href="javascript:void(0)">Traveller</a></li>
							<li><a class ="reco_cate" cat_name= "host" href="javascript:void(0)">Host</a></li>	
						</ul>
					</div>
				</div>
			</div>
			<div class="settings-icon">
				<a class="dropdown-button" href="javascript:void(0)" data-activates="newpost_settings12">
					<i class="zmdi zmdi-hc-2x zmdi-more"></i>
				</a>
				<ul id="newpost_settings12" class="dropdown-content custom_dropdown">
					<li> <a onclick="clearPost()">Clear Post</a> </li>
				</ul>
			</div>
		</div>
		<div class="npost-content">
			<div class="desc">
				<textarea id="new_post_comment" placeholder="Write you reference" class="materialize-textarea comment_textarea new_post_comment"></textarea>
			</div>
		</div>
	</div>
</div>
<div class="modal-footer">
	<div class="post-bcontent">
		<div class="pos-nag footer_icon_container">			
			  <input name="group1" class="radioevent" type="radio" id="Positive1" value="positive" />
			  <label for="Positive1" class="feed-positive"><i class="mdi mdi-star"></i> Positive</label>
			  <input name="group1" class="radioevent" type="radio" id="Negative1" value="negative" />
			  <label for="Negative1" class="feed-Negative"><i class="mdi mdi-star-o"></i> Negative 12</label>
		</div>
		<div class="public_dropdown_container_xs">
			<a class="dropdown_text dropdown-button" href="javascript:void(0)" data-activates="post_privacy_compose13">
				<span class="getvalue2">Host</span>
				<i class="zmdi zmdi-caret-up zmdi-hc-lg"></i>
			</a>
			<ul id="post_privacy_compose13" class="dropdown-privacy dropdown-content custom_dropdown public_dropdown_xs">
				<li> <a href="javascript:void(0)"> Personal </a> </li>
				<li> <a href="javascript:void(0)"> Traveller </a> </li>
				<li> <a href="javascript:void(0)"> Host </a> </li>
			</ul>
		</div>
		<div class="post-bholder">
			<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<div class="hidden_xs">
				<a class="btngen-center-align  close_modal open_discard_modal waves-effect">cancel</a>
				<a type="button" class="btngen-center-align  waves-effect close_modal">Post</a>
			</div>
		</div>
	</div>
</div>	
<div class="valign-wrapper additem_modal_footer modal-footer">		
	<div class="pos-nag footer_icon_container">			
		  <input name="group11" class="radioevent" type="radio" id="Positive" value="positive" />
		  <label for="Positive" class="feed-positive"><i class="mdi mdi-star"></i> Positive</label>
		  <input name="group11" class="radioevent" type="radio" id="Negative" value="negative" />
		  <label for="Negative" class="feed-Negative"><i class="mdi mdi-star-o"></i> Negative</label>
	</div>
	<div class="frow nbm">		  
		<div class="btn-holder">
			<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a href="javascript:void(0)" class="btngen-center-align  close_modal open_discard_modal waves-effect">Cancel</a>
			<a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="addReferal()">Post</a>
		</div>	
	</div>
</div>
<?php exit; ?>