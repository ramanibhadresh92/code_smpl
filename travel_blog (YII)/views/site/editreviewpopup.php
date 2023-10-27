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
    $tagstr = '';
    $time = time();	

    $my_post_view_status = $post['post_privacy'];
    if($my_post_view_status == 'Private') {$post_dropdown_class = 'lock';}
    else if($my_post_view_status == 'Connections') {$post_dropdown_class = 'account';}
    else if($my_post_view_status == 'Custom') {$post_dropdown_class = 'settings';}
    else {$post_dropdown_class = 'earth';}
    
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
			<p class="modal_header_xs">Write review</p>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="post_btn action_btn post_btn_xs waves-effect" onclick="edit_place_review('<?=$post['_id']?>');">Save</a>
		</div>
	</div>					
	<div class="modal-content">
		<div class="new-post active"> 
			<div class="top-stuff">
				<div class="postuser-info">
					<span class="img-holder"><img src="<?=$this->context->getimage($user_id,'thumb');?>"></span>
					<div class="desc-holder">
						<p class="profile_name"><?=$fullname?></p>
						<?php 
						if(!empty($taginfomatiom)) {
				            if(count($taginfomatiom) > 1) {
				            	if(count($taginfomatiom) > 2) {
				            		$label = (count($taginfomatiom) - 1).' Others';
				            	} else {
				            		$label = '1 Other';
				            	}
				                $tagstr =  "<span>&nbsp;with&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>" . $nvTag[0] . "</span><span>&nbsp;and&nbsp;</span><span class='pa-like sub-link livetooltip compose_addpersonAction tagged_person_name' title='".$content."'>".$label."</span>";
				            } else {
				                $tagstr =  "<span>&nbsp;with&nbsp;</span><span class='tagged_person_name compose_addpersonAction' id='compose_addpersonAction'>" . $nvTag[0] . "</span>";
				            }
				        }
						?> 
						<label id="tag_person">
							<?=$tagstr?>
				        </label>
				        <div class="public_dropdown_container damagedropdown">
							<a class="dropdown_text dropdown-button-left editpostcreateprivacylabel" onclick="privacymodal(this)" href="javascript:void(0)" data-modeltag="editpostcreateprivacylabel" data-fetch="yes" data-label="editpost">
								<span id="post_privacy" class="post_privacy_label active_check"><?=$my_post_view_status?></span>
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
				<div class="compose_post_title title_post_container npost-title" style="display: block">
					<?php
					 $post_title = (isset($post['post_title']) && !empty($post['post_title'])) ? $post['post_title'] : '';
					?>
					<input type="text" class="title post_title" placeholder="Title of this post" value="<?=$post_title?>">																								
				</div>
				<div class="clear"></div>
				<div class="rating-stars setRating cus_starts">
					<span>Edit your rating</span>&nbsp;&nbsp;
					<?php if(isset($post['placereview']) && ($post['placereview'] == '1' || $post['placereview'] == 1)) { ?>
						<span onmouseout="resettostart(this)">
						<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
						<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
						<i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
						<i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
						<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
						</span>&nbsp;&nbsp;
						<span class="star-text">Poor</span>
					<?php } else if(isset($post['placereview']) && ($post['placereview'] == '2' || $post['placereview'] == 2)) { ?>
						<span onmouseout="resettostart(this)">
						<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
						<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
						<i class="mdi mdi-star ratecls3 ratecls4 ratecls5" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
						<i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
						<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
						</span>&nbsp;&nbsp;
						<span class="star-text">Good</span>
					<?php } else if(isset($post['placereview']) && ($post['placereview'] == '3' || $post['placereview'] == 3)) { ?>
						<span onmouseout="resettostart(this)">
						<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
						<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
						<i class="mdi mdi-star ratecls3 ratecls4 ratecls5 active" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
						<i class="mdi mdi-star ratecls4 ratecls5" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
						<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
						</span>&nbsp;&nbsp;
						<span class="star-text">Better</span>
					<?php } else if(isset($post['placereview']) && ($post['placereview'] == '4' || $post['placereview'] == 4)) { ?>
						<span onmouseout="resettostart(this)">
						<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
						<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
						<i class="mdi mdi-star ratecls3 ratecls4 ratecls5 active" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
						<i class="mdi mdi-star ratecls4 ratecls5 active" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
						<i class="mdi mdi-star ratecls5" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
						</span>&nbsp;&nbsp;
						<span class="star-text">Superb</span>
					<?php } else if(isset($post['placereview']) && ($post['placereview'] == '5' || $post['placereview'] == 5)) { ?>
						<span onmouseout="resettostart(this)">
						<i class="mdi mdi-star ratecls1 ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="1" onmouseover="ratingJustOver(this)" onclick="pickrate(this,1)"></i> 
						<i class="mdi mdi-star ratecls2 ratecls3 ratecls4 ratecls5 active" data-value="2" onmouseover="ratingJustOver(this)" onclick="pickrate(this,2)"></i>
						<i class="mdi mdi-star ratecls3 ratecls4 ratecls5 active" data-value="3" onmouseover="ratingJustOver(this)" onclick="pickrate(this,3)"></i>
						<i class="mdi mdi-star ratecls4 ratecls5 active" data-value="4" onmouseover="ratingJustOver(this)" onclick="pickrate(this,4)"></i>
						<i class="mdi mdi-star ratecls5 active" data-value="5" onmouseover="ratingJustOver(this)" onclick="pickrate(this,5)"></i>
						</span>&nbsp;&nbsp;
						<span class="star-text">Excellent</span>
					<?php } ?>
              	</div>								
				<div class="clear"></div>								
				<div class="desc">									
					<textarea type="text" class="textInput npinput capitalize materialize-textarea comment_textarea new_post_comment" id="textInput"  placeholder="What's new?"><?=$post['post_text']?></textarea>
				</div>      
			</div> 
		</div>
	</div>
	<div class="modal-footer">
		<div class="post-bcontent">
			<div class="footer_icon_container">
				<button class="comment_footer_icon compose_addpersonAction waves-effect" id="compose_addpersonAction">
					<i class="zmdi zmdi-account"></i>
				</button>
				<button class="comment_footer_icon waves-effect compose_titleAction" id="compose_titleAction">
					<img src="<?=$baseUrl?>/images/addtitleBl.png">
				</button>
			</div>
			<div class="public_dropdown_container_xs damagedropdown">
				<a class="dropdown_text dropdown-button editpostcreateprivacylabel" onclick="privacymodal(this)" href="javascript:void(0)" data-modeltag="editpostcreateprivacylabel" data-fetch="yes" data-label="editpost">
					<span id="post_privacy2" class="post_privacy_label"><?=$my_post_view_status?></span>
					<i class="zmdi zmdi-caret-up zmdi-hc-lg"></i>
				</a>
			</div>
			<div class="post-bholder">
				<div class="hidden_xs">
					<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
					<a class="btngen-center-align close_modal open_discard_modal waves-effect" tabindex="1">cancel</a>
					<a type="button" class="btngen-center-align waves-effect" tabindex="1" onclick="edit_place_review('<?=$post['_id']?>');">Save</a>
				</div>
			</div>
		</div>
	</div>	
    <?php 
}	
?>
<?php exit; ?>