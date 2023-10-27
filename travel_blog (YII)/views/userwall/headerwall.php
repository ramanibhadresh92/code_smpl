<?php 

use frontend\assets\AppAsset;
use frontend\models\Connect;
use frontend\models\LoginForm;
use frontend\models\UserForm;
use frontend\models\Page;
use frontend\models\ProfileVisitor; 
use frontend\models\SecuritySetting; 
use frontend\models\Cover;
use frontend\models\Referal;
use frontend\models\Destination;
use frontend\models\Personalinfo;
use frontend\models\UserPhotos;

$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$request = Yii::$app->request;
$user_id = (string) $session->get('user_id');  
$wall_user_id = (string) $request->get('id');

$result = LoginForm::find()->where(['email' => $email])->one();
$Personalinfo = Personalinfo::find()->where(['user_id' => $wall_user_id])->one();
$about_us= $Personalinfo['about'];

$user_img = $this->context->getimage($wall_user_id,'photo');

if(isset($user_basicinfo['cover_photo']) && !empty($user_basicinfo['cover_photo']))
{
    $cover_photo = "uploads/cover/".$user_basicinfo['cover_photo'];
}
else
{
    $cover_photo = $baseUrl."/images/wallbanner.jpg";
}

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

$model_connect = new Connect();
$connections = $model_connect->userlist();
$userid = (string)$session->get('user_id');

$visitors = ProfileVisitor::find()->with('user')->where(['user_id' => "$wall_user_id"])->all();
$is_connect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '1'])->one();

$is_connect_request_sent = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$wall_user_id",'status' => '0'])->one();

$result_security = SecuritySetting::find()->where(['user_id' => "$wall_user_id"])->one();
if ($result_security)
{
	$request_setting = $result_security['connect_request'];
}
else
{
	$request_setting = 'Public';
}
$totalcount = Connect::getuserConnections($wall_user_id);
?>
<div class="wall-header">
	<div class="wall-banner" style="background:url(<?=$cover_photo?>) no-repeat center center;">
		<div class="frow frowfull">
		   	<div class="crop-holder mian-crop" id="image-cropper">
				<div class="cropit-preview"></div>
				<div class="main-img1">
				  <img id="imageid" draggable="false"/>
				</div>
				<?php if($wall_user_id == $user_id){ ?>
			  	<a href="javascript:void(0)" onclick="openDefaultSlider()" class="cam-icon"><i class="zmdi zmdi-hc-2x zmdi-camera"></i></a>
				<a id="removeimg" href="javascript:void(0)" class="wall_image_trash image_trash removeimg">
				  <i class="mdi mdi-close"></i>	
				</a>
				
				<a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save covercropbtn dis-none">
				  <span class="zmdi zmdi-check"></span>
				</a>
				<?php } ?>
				<section class="cover-slider">
					<div id="carousel" class="carousel_master">		
						<div class="carousel">
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
						</div>
						<div class="cover-upload">
							<div class="btnupload custom_up_load" id="upload_img_action">
							  <div class="fileUpload">
								<i class="zmdi zmdi-hc-2x zmdi-upload"></i>
								<input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
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
		<a href="javascript:void(0)" class="user-link"><?php echo $user_basicinfo['fname'].' '.$user_basicinfo['lname'];?></a>
		<ul class="nav nav-tabs link-menu">
			<?php if(($wall_user_id != $user_id) && !($is_connect)){
					if($is_connect_request_sent){$fr_title='Connect request sent';}
					else{$fr_title='Add connect';}
					if($request_setting != 'Private'){ ?>
					<li class=""><a href="javascript:void(0)" class="action-icon wallconnectaction">
					<input type="hidden" name="to_id" id="to_id" value="<?php echo $user_id;?>">
					<?php if($is_connect_request_sent){?>
					<i class="mdi mdi-account-minus" onclick="genFrdAction('<?=(string)$user_basicinfo['_id']?>', this)"></i>
					<?php } else { ?>
					<i class="mdi mdi-account-plus" onclick="genFrdAction('<?=(string)$user_basicinfo['_id']?>', this)"></i>
					<?php } ?>
					<input type="hidden" name="login_id" id="login_id" value="<?php echo $session->get('user_id');?>">
					</a></li>
			<?php } else { ?>
					<li class=""><a href="javascript:void(0)" class="action-icon">
						<img onclick="privateMessage()" src="<?=$baseUrl?>/images/private-connect.png"/>
					</a></li>
			<?php } } else { ?>
				<li class=""><a href="javascript:void(0)" class="action-icon wallconnectaction"><i class="mdi mdi-account-remove" onclick="genFrdAction('<?=(string)$user_basicinfo['_id']?>', this)"></i></a>
			<?php } ?>
			<?php if($wall_user_id != $user_id){ ?>
			<li class=""><a href="javascript:void(0)" class="action-icon" onClick="showMsg(this);" title="Send Message"><i class="mdi mdi-telegram"></i></a></li>
			<?php } ?>   
			<?php if($userid == $user_basicinfo['_id']) {?>
				<li class="activity_classs"><a  aria-expanded="false" data-toggle="tab" href="#activity-content" class="action-icon activity-link" title="Activity log"><i class="mdi mdi-flash"></i></a></li>
			<?php } ?>
		</ul>
	</div>                                                           
	<div class="wall-header-stuff"> 
		<div class="profile-info">
			<div class="profile-pic">
				<div class="js-cropper-result">
					<img src="<?php echo $user_img;?>"/>
				</div>
				<div class="dropdown dropdown-custom context-dropdown">
					<?php if($wall_user_id == $user_id){ ?>
					<a class="more_btn chng-dro-md" href="javascript:void(0);">	
					<div class="image-upload more_btn chng-dro-md compose_camera">
						<label for="file-input">
							<i class="zmdi zmdi-hc-2x zmdi-camera"></i>
						</label>
						<input id="file-input" type="file" class="js-cropper-upload" value="Select"/>
					</div>
				  </a>
					 <?php } ?>
				</div>
			</div>					
		</div>
		<div class="loc-info">
		<span class="btext" id="about_me"><?=$about_us?></span> 
		</div>
		<div class="sendMessage">
			<input class="sendMsgTxt" placeholder="Please Enter you message here" type="text" />
			<input type="button" value="Send">
		</div>
	</div>
	<div class="wall-tabs">
		<div class="sub-tabs tab-scroll">
			<div class="innerdiv">
				<ul class="tabs">
					<li class="wallclk active tab"><a  tabname="Wall" href="#wall-content">Wall</a></li>
					<li class="tab"><a tabname="About" href="#about-content" onclick="$('.innerpage-name').text('About'),section_about('<?=$wall_user_id?>')">About</a></li>
					<li class="wallconnectclk tab rem-shadow-none"><a tabname="Connections"  href="#connections-content" onclick="$('.innerpage-name').text('Connections')"><span class="frnd_count"><?=count($totalcount);?></span>Connections</a></li>
					<?php
						$total_pictures = UserPhotos::getPics($wall_user_id);
						$profile_albums = UserPhotos::getProfilePics($wall_user_id);
						$total_profile_albums = count($profile_albums);
						$cover_albums = UserPhotos::getCoverPics($wall_user_id);
						$total_cover_albums = count($cover_albums);
						$totalcounts = $total_pictures+$total_profile_albums+$total_cover_albums;  
						$total_refers = Referal::getTotalReferals($wall_user_id);
						$allpageslikescount = Page :: getMyLikesPagesCount($wall_user_id);
						$lastsixpageslikes = Page :: getLastSixMyLikesPages($wall_user_id);
						$destcount = Destination::getDestinationCount($wall_user_id);
					?>
					<li class="wallphotosclk tab rem-shadow-none"><a tabname="Photos" onclick="resetInnerPage('wall','hide');$('.innerpage-name').text('Photos')" href="#photos-content"><span class="photos_count"><?php echo $totalcounts;?></span>Photos</a></li>
					
					<li class="wallcontrclk tab"><a tabname="Contribution" onclick="$('.innerpage-name').text('Contribution')" href="#contribution-content">Contribution</a></li>
					
					<li class="wallgalleryclk tab"><a onclick="$('.innerpage-name').text('Gallery')" tabname="Gallery" href="#gallery-content" onclick="box()">Gallery</a></li>
					 
					<li class="walldestclk tab rem-shadow-none"><a onclick="$('.innerpage-name').text('Destinations')" tabname="Destinations" href="#destinations-content"><span><?=$destcount?></span>Destinations</a></li>
					
					<?php if($userid == $user_basicinfo['_id']) {?>
					<li class="wallsavedclk tab"><a onclick="$('.innerpage-name').text('Saved')" tabname="Saved" href="#saved-content">Saved</a></li>
					<?php } ?>
					
					<li class="walllikesclk tab"><a onclick="$('.innerpage-name').text('Likes')" tabname="Likes" href="#likes-content"><span><?php echo $allpageslikescount;?></span>Likes</a></li>
					
					<li class="wallrefersclk tab"><a onclick="$('.innerpage-name').text('Refers')" tabname="References" href="#refers-content" <?php if($wall_user_id == $user_id){ ?>class="disabled" <?php } ?>><span class="reflivecount"><?=$total_refers?></span>Refers</a></li>
				</ul>
			</div>
		</div>
	</div>

</div>