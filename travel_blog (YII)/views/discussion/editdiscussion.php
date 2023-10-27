<?php

use frontend\assets\AppAsset;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');

$pageid = $post['post_user_id'];
$fullname = $session->get('fullname');

if($user_id == $post['post_user_id'] || $user_id == $post['shared_by']) 
{ 
    $post['post_user_id'] = $pageid;
    $tagstr = '';
    $time = time();	

    $my_post_view_status = $post['post_privacy'];
    if($my_post_view_status == 'Private') {$post_dropdown_class = 'lock';}
    else if($my_post_view_status == 'Connections') {$post_dropdown_class = 'account';}
    else if($my_post_view_status == 'Custom') {$post_dropdown_class = 'settings';}
    else {$post_dropdown_class = 'earth';}
    ?>
    <div class="hidden_header">
		<div class="content_header">
			<button class="close_span cancel_poup waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>
			<p class="modal_header_xs">Write Post</p>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="post_btn action_btn post_btn_xs waves-effect"  onclick="edit_discussion('<?=$post['_id']?>')">Save</a>				
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
					<a class="dropdown-button" href="javascript:void(0)" data-activates="editpost_settings">
						<i class="zmdi zmdi-more"></i>
					</a>
					<ul id="editpost_settings" class="dropdown-content custom_dropdown">
						<li class="disable_comment">
							<a href="javascript:void(0)">
								<input type="checkbox" class="toolbox_disable_comments" id="<?=$time?>toolbox_disable_comments" <?php if($post['comment_setting'] == 'Disable'){ echo 'checked'; } ?>/>
								<label for="<?=$time?>toolbox_disable_comments">Disable Comments</label>
							</a>
						</li>
					</ul>
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
				<div class="desc">									
					<textarea type="text" class="textInput npinput capitalize materialize-textarea comment_textarea new_post_comment" id="textInput"  placeholder="What's new?"><?=$post['post_text']?></textarea>
				</div>
				<div class="post-photos">
					<div class="img-row">
						<?php
							$isImageExist = false;
							if(isset($post['image']) && !empty($post['image'])) {
								$eximgs = explode(',',$post['image'],-1);
								foreach ($eximgs as $eximg) {
									if (file_exists('../web'.$eximg)) {  
										$iname = $this->context->getimagefilename($eximg);
										$picsize = '';
										$val = getimagesize('../web'.$eximg);
										$picsize .= $val[0] .'x'. $val[1] .', ';
										if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}

										$isImageExist = true;
										?>
										<div class="img-box" id="imgbox_<?=$iname?>">
										<a href="javascript:void(0)" class="listalbum-box">
											<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" alt="" class="thumb-image <?=$imgclass?> ">
											<?php if(!(isset($post['shared_from']) && !empty($post['shared_from']))) { ?>
											<a href="javascript:void(0)" data-class="ep-delpic" class="removePhotoFile popup-imgdel" onclick="delete_image('<?= $iname ?>','<?= $eximg ?>','<?= $post['_id'] ?>')"><i class="mdi mdi-close	"></i>
											</a>
											<?php } ?>
										</a>
										</div>
									<?php }  
								}  
							} 

							if($isImageExist) {
								if(!(isset($post['shared_from']) && !empty($post['shared_from']))) { ?>
								<div class='img-box customuploadbox'><div class='custom-file addimg-box'><div class='addimg-icon'><i class="zmdi zmdi-plus zmdi-hc-lg"></i></div><input type='file' name='upload' class='upload custom-upload remove-custom-upload' title='Choose a file to upload' required='' data-class='.post-photos .img-row' multiple='true'/></div></div>
								<?php } 
							} ?>
					</div>
				</div>
			</div>          
		</div>
	</div>
	<div class="modal-footer">
		<div class="post-bcontent" id="directcall_new_post">
			<div class="footer_icon_container">
				<button class="comment_footer_icon waves-effect" id="compose_edituploadphotomodalAction">
					<i class="zmdi zmdi-camera"></i>
				</button>
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
					<a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal waves-effect">Cancel</a>
					<a href="javascript:void(0)" class="mainbtn postbtn btngen-center-align waves-effect" onclick="edit_discussion('<?=$post['_id']?>')">Save</a>
				</div>
			</div>
		</div>			
	</div> 		
    <?php 
}	
?>
<?php exit; ?>