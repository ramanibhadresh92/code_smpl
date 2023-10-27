<?php

use frontend\assets\AppAsset;
use frontend\models\UserForm;
use frontend\models\LoginForm;
use frontend\models\Cover;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$status = $session->get('status');
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

if($user_id == '')
{
	$user_id = LoginForm::find()->limit(1)->offset(0)->all();
	$user_id = $user_id[0]['_id'];
} 
?>  
	<input type="hidden" name="page_status" value="<?=$page_details['is_deleted']?>" id="page_status"/>
    <input type="hidden" name="page_email" value="<?=$page_details['email']?>" id="page_email"/>
    <!-- Unverified Page -->
		<?php 
        if($page_details['is_deleted']=='0' || $page_details['is_deleted']=='2'){ ?>
        <div class="unreg-modal unreg-notice notice">
            <div class="status-note"><div class="success-note">Confirmation link sent successfully!</div><div class="error-note">Error occured. Please try again!</div></div>

            <div class="icon-holder"><i class="mdi mdi-information-variant"></i></div>
            <div class="desc-holder">       
                You are almost there! Please verify your page to publish.Check your <?=$page_details['company_email']?> account or <a onclick="pageVerify()" href="javascript:void(0)">request a new confirmation link</a>
            </div>
            <div class="loading"></div>
        </div>
        <?php } ?>                                   
        <!-- End Unregisted User Popup -->                 
        <div class="wall-header">
			<div class="wall-banner" style="background:url(<?=$cover_photo?>) no-repeat top center;">
				<div class="frow frowfull">
			  <div class="crop-holder mian-crop" id="image-cropper">
				<div class="cropit-preview"></div>
				<div class="main-img1">
				  <img id="imageid" draggable="false"/>
				</div>

				<?php if($page_details['created_by'] == $user_id){ ?>
					<a href="javascript:void(0)" onclick="openDefaultSlider()" class="cam-icon"><i class="zmdi zmdi-hc-2x zmdi-camera"></i></a>
					<a id="removeimg" href="javascript:void(0)" class="wall_image_trash image_trash removeimg">
					  <i class="mdi mdi-close"></i>	
					</a>
					
					<a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save covercropbtn dis-none">
					  <span class="zmdi zmdi-check"></span>
					</a>
				<?php } ?>
				<section class="cover-slider">
					<div id="carousel" class="carousel_master carousel_new"> 
						<div class="carousel">
						<?php /*  
							  $cover = Cover::find()->asarray()->all();
							  if(isset($cover) && !empty($cover)) 
							  {	  
							  	$i = 1;  
								foreach($cover as $coversingle) 
								{	
								    $name = $coversingle['cover_image'];
								    if(!file_exists("uploads/cover/thumbs/thumb_".$name."")) {
								    	continue;
									}

							  ?>
							  	<a class="carousel-item" href="#image<?=$i?>"><img src="uploads/cover/thumbs/thumb_<?=$name?>"></a>
							  <?php 
							  	$i++;
							  } ?>
							   <?php } else { */ ?>
							<a class="carousel-item" href="#one!"><img src="uploads/cover/thumbs/thumb_cover-1.jpg"></a>
							<a class="carousel-item" href="#two!"><img src="uploads/cover/thumbs/thumb_cover-2.jpg"></a>
							<a class="carousel-item" href="#three!"><img src="uploads/cover/thumbs/thumb_cover-3.jpg"></a>
							<a class="carousel-item" href="#four!"><img src="uploads/cover/thumbs/thumb_cover-4.jpg"></a>
							<a class="carousel-item" href="#five!"><img src="uploads/cover/thumbs/thumb_cover-5.jpg"></a>
							<a class="carousel-item" href="#six!"><img src="uploads/cover/thumbs/thumb_cover-6.jpg"></a>
							<a class="carousel-item" href="#seven!"><img src="uploads/cover/thumbs/thumb_cover-7.jpg"></a>
							<a class="carousel-item" href="#eight!"><img src="uploads/cover/thumbs/thumb_cover-8.jpg"></a>
							<a class="carousel-item" href="#nine!"><img src="uploads/cover/thumbs/thumb_cover-9.jpg"></a>
							<a class="carousel-item" href="#ten!"><img src="uploads/cover/thumbs/thumb_cover-10.jpg"></a>
							<?php /*}*/ ?>
						</div>
						<div class="cover-upload">
							<div class="btnupload custom_up_load" id="upload_img_action">
							  <div class="fileUpload">
								<i class="zmdi zmdi-hc-2x zmdi-upload"></i>
								<input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
							  </div>
							</div>						
						</div>
					</div>
					<div class="btn-holder">								
						<a href="javascript:void(0)" onclick="closeDefaultSlider()" class="close-btn"><i class="mdi mdi-close"></i></a>
					</div>
			  </section>
			</div>
			</div>
			</div>
			<div class="header-strip">
				<a href="javascript:void(0)" class="user-link busname getpgname"><?=$page_details['page_name']?></a>
				<ul class="nav nav-tabs link-menu">

					<?php if(!($page_details['created_by'] == $user_id)) { ?>
					<li><a href="javascript:void(0)" class="action-icon likeaction <?php if($likestatus == 'Liked'){echo 'active';}?>" title="<?=$likestatus?>" onclick="likepagefromwallthumb('<?=$page_id?>')"><i class="zmdi zmdi-thumb-up"></i></a></li>
					<?php } ?>
					
					<li><a <?php if(!strstr($page_details['blk_restrct_list'],(string)$user_id)){?>href="javascript:void(0)" data-sharepostid="page_<?=$page_id;?>"<?php } ?> class="action-icon share-it <?php if(!strstr($page_details['blk_restrct_list'],(string)$user_id)){?>sharepostmodalAction<?php } ?>"><i class="zmdi zmdi-share"></i></a></li>

					<?php if(($page_details['created_by'] != $user_id && !strstr($page_details['gen_msg_filter'],(string)$user_id))){ ?>
					<li><a href="javascript:void(0)" onclick="showMsg(this);" data-imgsrc="<?=$page_img;?>" data-pagename="<?=$page_details['page_name'];?>" class="action-icon" data-pageid="li-<?=$page_id;?>" data-id="chat_<?=$page_id;?>" data-owner="<?=$page_details['created_by'];?>" data-userid="<?=$user_id;?>"><i class="mdi mdi-telegram"></i></a></li>
					<?php } ?>
					
					<?php if($page_details['created_by'] == $user_id || (!empty($pagerole) && $pagerole!='Supporter')){ ?>
					<li onclick="openPageSettings();"><a href="javascript:void(0)" title="Page Settings" class="action-icon activity-link"><i class="zmdi zmdi-settings"></i></a></li>			
					<?php } 
					
					if($status == '10') { ?>
					<li><a href="#reportpost-popup-<?php echo $page_id;?>" data-reportpostid="<?php echo $page_id;?>" class="customreportpopup-modal reportpost-link action-icon"><i class="zmdi zmdi-delete"></i></a></li>
					<?php } ?>
					
				</ul>
				<div class="action-links business_preferences">
					<div class="dropdown dropdown-custom ">
						<a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="Preferences_drop">
							<i class="zmdi mdi-24px zmdi-more-vert"></i>
					  	</a>
					  <ul id="Preferences_drop" class="dropdown-content custom_dropdown preferences_drop_popup">
					  	<li><a href="javascript:void(0);"  onclick="preferencesopenpopup('<?=(string)$page_details['_id']?>', 'page')">Preferences</a></li>
					  </ul>
					</div>
				</div>
			</div>
				
			<div class="wall-header-stuff"> 
				<div class="profile-info">
					<div class="profile-pic">
						<div class="js-cropper-result">
						<img src="<?php echo $page_img;?>"/>
						</div>
						<?php if($page_details['created_by'] == $user_id){ ?>
							<div class="dropdown dropdown-custom context-dropdown">
								<a class="more_btn chng-dro-md" href="javascript:void(0);">	
								<div class="image-upload more_btn chng-dro-md compose_camera">
									<label for="file-input">
										<i class="zmdi zmdi-hc-2x zmdi-camera"></i>
									</label>
									<input id="file-input" type="file" class="js-cropper-upload" value="Select"/>
								</div>
							  </a>
							</div>
						<?php } ?>
						</div>
					</div>
				<?php
				if(isset($page_details['city']) && !empty($page_details['city']))
				{
					$pagecity = $page_details['city'];
				}
				else
				{
					$pagecity = 'City not added';
				}
				if(isset($page_details['country_code']) && !empty($page_details['country_code']))
				{
					$countrycode = 'flag '.$page_details['country_code'];
				}
				else
				{
					$countrycode = '';
				}
				?>
				
				<div class="loc-info"><span class="btext"><?=$page_details['short_desc']?></span></div>
				<div class="sendMessage">
					<input class="sendMsgTxt" placeholder="Write your message to the page admin" type="text" />
					<input type="button" value="Send">
				</div>
			</div>
			
			<div class="wall-tabs tablet-menu">
				<div class="sub-tabs tab-scroll">
					<div class="innerdiv">
						<ul class="tabs">
							<li class="tab"><a id="firstliclicked"  tabname="Wall" href="#wall-content">Wall</a></li>	
							
							<li class="tab aboutclk"><a  onclick="$('.innerpage-name').text('About')"  aria-expanded="true" data-toggle="tab" tabname="About" href="#about-content">About</a></li>

							<li class="pagephotos tab rem-shadow-none"><a  onclick="$('.innerpage-name').text('Photos')"  aria-expanded="false" data-toggle="tab" tabname="Photos" href="#photos-content"><span><?=$totalcounts?></span>Photos</a></li>
							
							<?php if($page_details['created_by'] == $user_id){ ?>
							<li class="pagenotifications tab"><a onclick="$('.innerpage-name').text('Notification')"  aria-expanded="false" data-toggle="tab" tabname="Notifications" href="#notifications-content"><span>2</span>Notifications</a></li>
							<?php } ?>
							
							<?php if($page_details['created_by'] == $user_id || $pagerole){ ?>
							<li class="pagepromote tab"><a onclick="$('.innerpage-name').text('Insights')"  aria-expanded="false" data-toggle="tab" tabname="Insights" href="#insights-content">Insights</a></li>
							<?php } ?>
							
							<li class="pagelike tab"><a onclick="$('.innerpage-name').text('Likes')"  aria-expanded="false" data-toggle="tab" tabname="Likes" href="#likes-content"><span><?=$like_count?></span>Likes</a></li>
							<?php if($page_details['gen_reviews'] == 'on' && !strstr($page_details['blk_restrct_list'],(string)$user_id)){?>

							<li class="tab <?php if($page_details['gen_reviews'] == 'on' && !strstr($page_details['blk_restrct_list'],(string)$user_id)){?>pagereviews<?php } else { ?>disabled<?php } ?>">
								<a onclick="$('.innerpage-name').text('Reviews')"  aria-expanded="false" data-toggle="tab" tabname="Reviews" href="#reviews-content" class="<?php if($page_details['created_by'] == $user_id) { ?>disabled<?php } ?>">
									<span><?=$pagereviewscount?></span>Review
								</a>
							</li>
							<?php } ?>
							
							<?php if(!strstr($page_details['blk_restrct_list'],(string)$user_id)){?>
							<li class="tab <?php if(!strstr($page_details['blk_restrct_list'],(string)$user_id)){?>pageendorse<?php } else { ?>disabled<?php } ?>">
								<a onclick="$('.innerpage-name').text('Endorsement')"  aria-expanded="false" data-toggle="tab" tabname="Endorsement" href="#endorsement-content">
									<span><?=$pageendorsecount?></span>Endorsement
								</a>
							</li>							
							<?php } ?>
						</ul>
	
					</div>
				</div>
			</div>
		</div>
		<div class="clear"></div>