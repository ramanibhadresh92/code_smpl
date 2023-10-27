<?php   
use yii\helpers\Url;	
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm; 
use yii\helpers\ArrayHelper;
use frontend\models\LoginForm; 
use frontend\models\Education; 
use frontend\models\Language; 
use frontend\models\Interests;
use frontend\models\Occupation;
use frontend\models\UserSetting;
use frontend\models\Personalinfo;
use frontend\models\NotificationSetting; 
use frontend\models\CommunicationSettings; 
use frontend\models\SecuritySetting;
use frontend\models\CountryCode;
use backend\models\Googlekey;
 

$model3 = new \frontend\models\SecuritySetting();
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');
$session->set('pro_fb','profile_facebook');
$thumb = $this->context->getimage($user_id,'thumb');
$fullname = $this->context->getuserdata($user_id, 'fullname'); 
if($fullname == '') {
	$fullname = '';
}

$userr_img = $this->context->getimage($user_id,'photo'); 

if($email)
{   
    $result = LoginForm::find()->where(['email' => $email])->one();
    $user = LoginForm::find()->where(['email' => $email])->one();
	
    $user_id = (string) $result['_id'];
    $fname = $result['fname'];
    $lname = $result['lname'];
    $password = $result['password'];
    $con_password = $result['con_password'];
    $birth_date = $result['birth_date'];
    $gender = $result['gender'];
    $city = $result['city'];
    $country_code = $result['country_code'];
    $country = $result['country'];
    $phone = $result['phone'];
    $isd_code = $result['isd_code'];
    if($isd_code == '') {
		$countryCodeData = CountryCode::find()->where(['code' => strtoupper($country_code)])->orwhere(['country_name' => strtoupper($country)])->asarray()->one();

    	if(!empty($countryCodeData)) {
    		$isd_code = $countryCodeData['isd_code'];
    	}
    }

    $alternate_email = $result['alternate_email'];
    if(isset($result['pwd_changed_date']) && !empty($result['pwd_changed_date'])) {
        $result['pwd_changed_date'] = $result['pwd_changed_date'];
    } else {
        $result['pwd_changed_date'] = $result['created_date'];
    }

    $pwd_changed_date = Yii::$app->EphocTime->time_pwd_changed(time(),$result['pwd_changed_date']);
    $pwd_changed_date = date('F d, Y',$result['pwd_changed_date']);
        
    $result_setting = UserSetting::find()->where(['user_id' => $user_id])->one();
    $email_access = $result_setting['email_access'];
    $alternate_email_access = $result_setting['alternate_email_access'];
    $mobile_access = $result_setting['mobile_access'];
    $birth_date_access = $result_setting['birth_date_access'];
    $gender_access = $result_setting['gender_access'];
    $language_access = $result_setting['language_access'];
    $religion_access = $result_setting['religion_access'];
    $political_view_access = $result_setting['political_view_access'];

    $result_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();

    $about = $result_personal['about'];
    $education = $result_personal['education'];
    if($education=='null'){$education='';}
    
    $interests = $result_personal['interests'];
    if($interests=='null'){$interests='';}
    
    $occupation = $result_personal['occupation'];
    if($occupation=='null'){$occupation='';}
    
    $hometown = $result_personal['hometown'];    

    $language = $result_personal['language'];
    if($language=='null'){$language='';}
    
    $religion = $result_personal['religion'];
    $political_view = $result_personal['political_view'];
}
else
{
    $url = Yii::$app->urlManager->createUrl(['site/index']);
    Yii::$app->getResponse()->redirect($url);   
}

	/* Communication Settings */
	$communication_settings = CommunicationSettings::find()->where(['user_id' => $user_id])->asarray()->one();
	if(!empty($communication_settings)) {
		$is_received_message_tone_on = isset($communication_settings['is_received_message_tone_on']) ? $communication_settings['is_received_message_tone_on'] : '';
		$is_new_message_display_preview_on = isset($communication_settings['is_new_message_display_preview_on']) ? $communication_settings['is_new_message_display_preview_on'] : '';
		$communication_label = isset($communication_settings['communication_label']) ? $communication_settings['communication_label'] : '';
		$show_away = isset($communication_settings['show_away']) ? $communication_settings['show_away'] : '';
		$is_send_message_on_enter = isset($communication_settings['is_send_message_on_enter']) ? $communication_settings['is_send_message_on_enter'] : '';
	}
	/* Notification */
	
	$notification = NotificationSetting::find()->where(['user_id' => $user_id])->one();
    
	$connect_activity = $notification['connect_activity'];
	$email_on_account_issues = $notification['email_on_account_issues'];
	$connect_activity_on_user_post = $notification['connect_activity_on_user_post'];
	$non_connect_activity = $notification['non_connect_activity'];
	$connect_request_notify = $notification['connect_request'];
	$e_card = $notification['e_card'];
	$credit_activity = $notification['credit_activity'];
	$sound_on_notification = $notification['sound_on_notification'];
	$sound_on_message = $notification['sound_on_message'];
	$like_post = $notification['is_like'];
	$comment_post = $notification['is_comment'];
	$share_post = $notification['is_share'];
	$follow_collection = $notification['follow_collection'];
	$share_collection = $notification['share_collection'];
	$add_trip_by_connect = $notification['add_trip_by_connect'];
	$invited_for_trip = $notification['invited_for_trip'];
	
	/* Notification */
	
	/* Security Settings */

	$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();

	$security_questions = (isset($result_security['security_questions']) && !empty($result_security['security_questions'])) ? $result_security['security_questions'] : '';
	$securitygetdafault = (isset($result_security[$security_questions]) && !empty($result_security[$security_questions])) ? $result_security[$security_questions] : '';
	$answer = (isset($result_security['answer']) && !empty($result_security['answer'])) ? $result_security['answer'] : '';
	$view_photos = (isset($result_security['view_photos']) && !empty($result_security['view_photos'])) ? $result_security['view_photos'] : 'Public';
	$add_post_on_your_wall_view = (isset($result_security['add_post_on_your_wall_view']) && !empty($result_security['add_post_on_your_wall_view'])) ? $result_security['add_post_on_your_wall_view'] : 'Public';
	$eml_ans = (isset($result_security['eml_ans']) && !empty($result_security['eml_ans'])) ? $result_security['eml_ans'] : '';
	$born_ans = (isset($result_security['born_ans']) && !empty($result_security['born_ans'])) ? $result_security['born_ans'] : '';
	$gf_ans = (isset($result_security['gf_ans']) && !empty($result_security['gf_ans'])) ? $result_security['gf_ans'] : '';
	$my_view_status = (isset($result_security['my_view_status']) && !empty($result_security['my_view_status'])) ? $result_security['my_view_status'] : 'Public';
	$my_post_view_status_new =(isset( $result_security['my_post_view_status']) && !empty( $result_security['my_post_view_status'])) ? $result_security['my_post_view_status'] : 'Public'; 

	$restricted_listids = (isset($result_security['restricted_list']) && !empty($result_security['restricted_list'])) ? $result_security['restricted_list'] : '';
	$restricted_listids = explode(',', $restricted_listids);
	$blocked_list = SecuritySetting::find()->select(['blocked_list'])->where(['user_id' => $user_id])->one();
	$blocked_list = $blocked_list['blocked_list'];
                                                                                                
	$pair_social_actions = (isset($result_security['pair_social_actions']) && !empty($result_security['pair_social_actions'])) ? $result_security['pair_social_actions'] : 'Public';
	//$contact_me = (isset($result_security['contact_me']) && !empty($result_security['contact_me'])) ? $result_security['contact_me'] : 'Public';
	$message_filtering = (isset($result_security['message_filtering']) && !empty($result_security['message_filtering'])) ? $result_security['message_filtering'] : 'Public';
	$request_filter = (isset($result_security['request_filter']) && !empty($result_security['request_filter'])) ? $result_security['request_filter'] : '';
                                                                   
	$connect_request = (isset($result_security['connect_request']) && !empty($result_security['connect_request'])) ? $result_security['connect_request'] : 'Public';
	$bothering_me = (isset($result_security['bothering_me']) && !empty($result_security['bothering_me'])) ? $result_security['bothering_me'] : 'Public';
	$dashboard_view_status = (isset($result_security['dashboard_view_status']) && !empty($result_security['dashboard_view_status'])) ? $result_security['dashboard_view_status'] : 'Public';
	$add_public_wall = (isset($result_security['add_public_wall']) && !empty($result_security['add_public_wall'])) ? $result_security['add_public_wall'] : 'Public';


	$see_public_wall = (isset($result_security['see_public_wall']) && !empty($result_security['see_public_wall'])) ? $result_security['see_public_wall'] : 'Public';
	$review_posts = (isset($result_security['review_posts']) && !empty($result_security['review_posts'])) ? $result_security['review_posts'] : 'Disabled';
	$review_tags = (isset($result_security['review_tags']) && !empty($result_security['review_tags'])) ? $result_security['review_tags'] : 'Disabled';
	$connect_list = (isset($result_security['connect_list']) && !empty($result_security['connect_list'])) ?  $result_security['connect_list'] : 'Public';
	
	 $blocked_str = '';
     if(isset($blocked_list) && $blocked_list != '') {
		$blocked_str .= '"';
		$blocked_str .= str_replace(",", '","', $blocked_list);
		$blocked_str .= '"';	
     }	
	 
	 $message_filtering_str = '';
     if(isset($message_filtering) && $message_filtering != '') {
		$message_filtering_str .= '"';
		$message_filtering_str .= str_replace(",", '","', $message_filtering);
		$message_filtering_str .= '"';	
     }

     $request_filter_str = '';
     if(isset($request_filter) && $request_filter != '') {
		$request_filter_str .= '"';
		$request_filter_str .= str_replace(",", '","', $request_filter);
		$request_filter_str .= '"';	
     }
	/* Security Settings */

$this->title = 'Account Settings';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script>
    $(document).ready(function(){
      var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
           if (sParameterName[0] === 'security') {
               $(".basicinfo-content").css("display", "none");
               $(".security-content").css("display", "block");
               $(".menu-basicinfo").parents('li').removeClass("opened");
               $(".menu-basicinfo").parents('li').removeClass("active");
               $(".menu-security").parents('li').addClass("opened");
               $(".menu-security").parents('li').addClass("active");
            }
        }
    }); 
</script>
<link href="<?=$baseUrl?>/css/custom-croppie.css" rel="stylesheet">
<div class="settings-page page-wrapper settings-wrapper subpage-wrapper hidemenu-wrapper white-wrapper noopened-search show-sidebar">
    <div class="header-section"> 
        <?php include('../views/layouts/header.php'); ?>
    </div>
	<div class="floating-icon">		
		<div class="scrollup-btnbox anim-side btnbox scrollup-float">
			<div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>			
		</div>			
	</div>
	<div class="clear"></div>
	<?php include('../views/layouts/leftmenu.php'); ?>

	<div class="fixed-layout ipad-mfix">
		<div class="settings-holder">
			<div class="settings-menuholder">
				<div class="sidemenu">

					<div class="side-user setting-mobile">
						<span class="img-holder"><img src="<?=$thumb?>"></span>
						<a href="<?=Url::to(['userwall/index', 'id' => "$user_id"]); ?>"><span class="desc-holder"><?=$fullname?></span></a>
					</div>
				
					<a href="javascript:void(0)" class="closemenu"><i class="mdi mdi-close"></i></a>

					<div class="settings-menu">
						<div class="settingpic-holder open">
							<div class="setting-pic">
								<img src="<?=$userr_img?>"/>
							</div> 
						</div>
						<div class="sidemenu-setting">
							<ul id="settings-menu" class="submenu side-fbmenu set-menu-add">
								<li id="basic_info" <?php if(!(isset($_GET['type']) && !empty($_GET['type']))){ ?> class="active" <?php } ?>>
									<a href="javascript:void(0)" class="menu-basicinfo">Basic Information</a>
								</li>
								<li  id="profile_photo" <?php if(isset($_GET['type']) && !empty($_GET['type'])){ ?> class="active" <?php } ?>>
									<a href="javascript:void(0)" class="menu-profilepic">Profile Photo</a>
								</li>
								<li><a href="javascript:void(0)" class="menu-communication">Communication</a></li>
								<li class=""><a href="javascript:void(0)" class="menu-security">Security Setting</a></li>	
								<li><a href="javascript:void(0)" class="menu-notification">Notifications</a></li>
								<li class=""><a href="javascript:void(0)" class="menu-block">Blocking</a></li>
								<!-- <li><a href="javascript:void(0);" class="choose-theme">Choose Theme</a></li> -->
								<li><a href="javascript:void(0)" class="menu-close" onclick="checkuseradminstuff();">Close Account</a></li>
							</ul>
						</div>
					</div>
				</div>				
			</div>
			
			<div class="main-content with-lmenu">
				<div class="settings-content-holder">
					<div id="go-top"></div>
					<div id="menu-basicinfo" class="basicinfo-content settings-content <?php if(!(isset($_GET['type']) && !empty($_GET['type']))){ ?> active <?php } ?>">
						<div class="formtitle">
							<h4>Basic Information
							 	<span class="right">
							 		<a href="javascript:void(0)" class="editiconCircleEffect editicon1 waves-effect waves-theme" onclick="open_edit_act_bf()"><i class="mdi mdi-pencil"></i></a>
							 	</span>
							</h4>
						</div>
					
						<ul class="settings-ul basicinfo-ul normal-part">
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Name</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">	
												<div class="info">
													<label id="name"><?= $fname ?> <?= $lname ?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- email -->						
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Email</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">							
												<div class="info">
													<label id="email"><?= $email ?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
						
							<li>
							<!-- alternate email -->
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Alternate Email</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
												<label id="alt-email">
													<?php
													if($alternate_email == ""){
														echo 'No alternate email set';
													} else {
														echo $alternate_email;
													}
													?>
												</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							<?php if(!(isset($result['fb_id']) && !empty($result['fb_id']))) { ?>
							<!-- password -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Password</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="pwd-change">Password updated on <?= $pwd_changed_date?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							<?php } ?>
						
							<!-- city -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>City</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="city">
														<?php
														if($city == "") {
															echo 'No city added';
														} else {
															echo $city;
														}
														?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- country -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Country</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="country1">
													<?php
													if($country == ""){
															echo 'No country added';
													} else {
														echo $country;
													}
													?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
						
							<!-- mobile -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Mobile</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
												<label id="phone2">
												</label>
													<label id="phone1">
													<?php
													if($phone == ""){
														echo 'Add mobile number';
													} else {
														echo $isd_code.' '.$phone;
													}
													?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- birth date -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Birth Date</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">	
												<div class="info">		
												<label id="birth_date">	
													<?php
													if($birth_date == ""){
														echo 'No birthdate set';
													} else {
														$birth_date2 = strtotime($birth_date);
														$day=date("d",$birth_date2);
														$month = date("F",$birth_date2);
														$year=date("Y",$birth_date2);
														?>
														<?=$month?> <?=$day?>, <?=$year?>
													<?php 
													}
													?>
													</label>
												</div>
											</div>
										</div>	
									</div>
								</div>
							</li>
							
							<!-- gender -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Gender</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">		
												<label  id="gender">	
														<?= $gender ?>
													</label>
												</div>
											</div>
										</div>	
									</div>
								</div>
							</li>
							
							
							<!-- about us -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>About Yourself</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="about">
													<?php
													if($about == ""){
														echo 'Add about yourself';
													} else {
														echo trim($about);
													}
													?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- language -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Language</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="language">
													<?php
														if($language == ""){
															echo 'No language set';
														} else {
															echo str_replace(",", ", ", $language);
														}
													?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>

							<!-- education -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Education</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="education">
													<?php
													if($education == ""){
														echo 'No education set';
													} else {
														echo str_replace(",", ", ", $education);
													}
													?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- interests -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Interest</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="interests">
												<?php
												if($interests == ""){
													echo 'No interest set';
												} else {
													echo str_replace(",", ", ", $interests);
												}
												?>
												</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- occupation -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l2 caption-holder">
												<div class="caption">
													<label>Occupation</label>
												</div>
											</div>
											<div class="col s12 m9 l10 detail-holder">
												<div class="info">
													<label id="occupation">
													<?php
													if($occupation == ""){
														echo 'No occupation set';
													} else {
														echo str_replace(",", ", ", $occupation);
													}
													?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
						</ul>

						<ul class="settings-ul basicinfo-ul edit-part dis-none">
						</ul>

						<div class="new-post-mobile clear editicon2">
		                  	<a class="grey popup-window waves-effect waves-light" href="javascript:void(0)" onclick="open_edit_act_bf()"><i class="mdi mdi-pencil"></i></a>
		                </div>
					</div>
					
					<div id="menu-profilepic" class="profilepic-content settings-content <?php if(isset($_GET['type']) && !empty($_GET['type'])){ ?> active <?php } ?>">
						<div class="formtitle"><h4 class="border_bt">Profile Picture</h4></div>	
						<div class="profpic-settings custome-pic">
							<div class="row">
								<div class="col s12 m7 l5 cropping-section setting-crop">
									<div class="cropper cropper-wrapper"> 
										<div class="image-upload">
									  	    <label for="file-input">
									  	        <i class="zmdi zmdi-camera-bw"></i>
									  	    </label>

									  	    <input id="file-input" type="file" class="js-cropper-upload" value="Select" onclick="$('.js-cropper-result').hide();$('.crop').show();$('.image-upload').hide();"/>
									  	</div>
										<div class="js-cropper-result">
											<img src="<?=$userr_img?>">
										</div> 
										<div class="crop dis-none"><div class="green-top desktop-view showon">Drag to crop</div>
											<div class="js-cropping"></div> 
											<i class="js-cropper-result--btn zmdi zmdi-check upload-btn" onclick="UploadProfilePhoto();"></i>
											<i class="mdi mdi-close	 img-cancel-btn" onclick="$('.js-cropper-result').show();$('.crop').hide();$('.image-upload').show();"></i>
										</div>
									</div> 	
								
									<h2 class="desktop-none"><?=$fullname?></h2>
								</div>
								<div class="col s12 m5 l7 setting-crop-small cus6">
									<div class="uploadProfile-stuff">
										<p class="grayp">Your photo needs to be 200x200 with Gif or Jpeg format.</p>
										<div class="fakeFileButton main-crop-btn" id="cropContainerHeaderButton11">
											<img src="<?=$baseUrl?>/images/upload-green.png" /> &nbsp; Upload a photo
											<div class="form-group">
												<input type="hidden" id="cropContainerPreload" value="Upload"  accept="image/*">
											</div>									
										</div>
										<div class="divider"></div>
										
										<div class="fakeFileButton">
											<a href="#change-profile" class="popup-modal"><img src="<?=$baseUrl?>/images/webcam.png" /> &nbsp; Take a photo</a>
										</div>
										<div class="divider"></div>
										
										<div class="fakeFileButton">
											<a href="<?php echo Yii::$app->request->baseUrl.'?r=site/auth&authclient=facebook' ?>"><img src="<?=$baseUrl?>/images/fb.png" /> &nbsp; Use a photo from Facebook
											<div class="form-group">
												<input type="hidden" accept="image/*">
											</div>								
											</a>	
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div id="menu-communication" class="communication-content settings-content">
						<div class="formtitle"><h4 class="border_bt">Communication</h4></div>	
						<ul class="settings-ul basicinfo-ul">
						<div class="formtitle formtitle mobile-show"><h5>Sound</h5></div>
						<ul class="settings-ul notification-ul">
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s10 m10 l10 detail-holder">
												<div class="info">
													<label>Play a sound when new message is received</label>
												</div>
											</div>																		
											<div class="col s2 m2 l2  right btn-holder">
												<div class="right" id="connect">	
													<div class="switch">
														<label>
															<?php if(isset($is_received_message_tone_on) && $is_received_message_tone_on== 'on') { ?>
																<input type="checkbox" id="is_received_message_tone_on" checked>
															<?php } else { ?>
																<input type="checkbox" id="is_received_message_tone_on">
															<?php } ?>
														  	<span class="lever"></span>
														</label>
													</div>												
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
						</ul>
						<div class="clear"></div>

						<div class="formtitle mobile-show"><h5>Display preview</h5></div>
			
						<ul class="settings-ul notification-ul">
							<!-- sound for receiving notification -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s10 m10 l10 detail-holder">
												<div class="info">
													<label>Show new message preview</label>
												</div>
											</div>													
											<div class="col s2 m2 l2  right btn-holder">
												<div class="right" id="connect">	
													  <div class="switch">
														<label>
														    <?php if(isset($is_new_message_display_preview_on) && $is_new_message_display_preview_on== 'on') { ?>
																<input type="checkbox" id="is_new_message_display_preview_on" checked>
															<?php } else { ?>
																<input type="checkbox" id="is_new_message_display_preview_on">
															<?php } ?>
														  <span class="lever"></span>
														</label>
													  </div>										
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
						</ul>
						<div class="clear"></div>
						
						<ul class="settings-ul notification-ul">								
							<!-- sound for receiving notification -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m12 l10 detail-holder">				
												<div class="info">
													<div class="drop-holder">
														<div class="sliding-middle-out anim-area underlined fullwidth">
															<select id="communication_label">
																<?php 
																	$communication_labelArray = array('Get messages from connections', 'Get messages from connections of connect', 'Get messages from everyone');
																	foreach ($communication_labelArray as $communication_labelArraySingle) {
																		$commlabelcls = '';
																		if(isset($communication_label) && $communication_label == $communication_labelArraySingle) {
																			$commlabelcls = 'selected';
																		}	
																		?>
																		<option value="<?=$communication_labelArraySingle?>" <?=$commlabelcls?>><?=$communication_labelArraySingle?></option>
																		<?php
																	}

																?>
															</select>
														</div>
													</div>
												</div>
											</div>	
										</div>	
									
									</div>
								</div>
							</li>							
							<li>
		                     <div class="settings-group">
		                        <div class="normal-mode">
		                           <div class="row">
		                              <div class="col s12 m12 l10 detail-holder">
		                                 <div class="info">
		                                    <div class="drop-holder">
		                                       <div class="sliding-middle-out anim-area underlined fullwidth">
		                                          <select>
		                                             <option>Turn off alerts and sound for</option>
		                                             <option>1 hr</option>
		                                             <option>1 day</option>
		                                             <option>1 week</option>
		                                             <option>1 month</option>
		                                          </select>
		                                       </div>
		                                    </div>
		                                 </div>
		                              </div>
		                           </div>
		                        </div>
		                     </div>
		                  </li>
							<!-- sound for receiving notification -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">				
												<div class="info">
													<div class="subsetting-area"> 					
														<div class="commu_area">
															<?php if(isset($show_away) && $show_away== 'on') { ?>
																<input type="checkbox" id="show_away" checked>
															<?php } else { ?>
																<input type="checkbox" id="show_away">
															<?php } ?>
															<label for="show_away">Show me as away when I've been inactive for <input value="10" type="text" style="width:30px"> minutes</label>
														</div>					
														<div class="commu_area">
															<?php if(isset($is_send_message_on_enter) && $is_send_message_on_enter== 'on') { ?>
																<input type="checkbox" id="is_send_message_on_enter" checked>
															<?php } else { ?>
																<input type="checkbox" id="is_send_message_on_enter">
															<?php } ?>
															<label for="is_send_message_on_enter">Enter to send the message </label>
														</div>
													</div>
												</div>
											</div>	
										</div>										
									</div>
								</div>
							</li>
						</ul>
						<div class="clear"></div>
					</div>	
					
					<div id="menu-security" class="security-content settings-content uniqbtnarea">
						<div class="formtitle">
							<h4 class="border_bt">Security Settings
							 	<span class="right">
							 		<a href="javascript:void(0)" class="editiconCircleEffect editicon1 waves-effect waves-theme" onclick="open_edit_act_ss()"><i class="mdi mdi-pencil"></i></a>
							 	</span>	
							</h4>
						</div>
						<ul class="settings-ul security-ul normal-part">
	
							<!-- Security Question -->				
							<li class="securityquestionli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Security Question</label>
												</div>
											</div>
											<div class="col s12 m9 l9 detail-holder">
												<div class="info">
													<label>Set your security question</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Lookup Setting -->
							<li class="lookupsettingli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Lookup Setting</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who can look me up?</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">
												<span class="security-setting lookupsettingdisplay"><?=$my_view_status?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Connect Request Settings -->
							<li class="connectrequestsettingsli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Connect Request Settings</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who can send me connect requests?</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">		
												<span class="security-setting connectrequestsettingsdisplay"><?=$connect_request?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Connect List -->
							<li class="connectlistli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Connect List</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who should see my connect list?</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">				
												<span class="security-setting connectlistdisplay"><?=$connect_list?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Photo Security -->
							<li class="photosecurityli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Photo Security</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who can see my photos?</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">
												<span class="security-setting photosecuritydisplay"><?=$view_photos?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Post Security -->
							<li class="postsecurityli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Post Security</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who can see my posts?</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">									
												<span class="security-setting postsecuritydisplay"><?=$my_post_view_status_new?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Post on wall -->
							<li class="postingpermissionli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Post on wall</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who can add stuff to my public Wall</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">									
												<span class="security-setting postingpermissiondisplay"><?=$add_public_wall?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Post Review -->
							<li class="postreviewli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Post Review</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Review posts connections tag you in before they appear on your public wall</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">											
												<span class="security-setting postreviewdisplay"><?=$review_posts?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Tag Reviews -->
							<li class="tagreviewsli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Tag Reviews</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Review tags people add to your own posts before the tags appear on site</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">										
												<span class="security-setting tagreviewsdisplay"><?=$review_tags?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- View Permission -->
							<li class="activitypermissionli">
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>View Permission</label>
												</div>
											</div>

											<div class="col s12 m6 l7 detail-holder">
												<div class="info">
													<label>Who can see what others post on your public Wall</label>
												</div>
											</div>

											<div class="col s12 m3 l2 btn-holder has-security">										
												<span class="security-setting activitypermissiondisplay"><?=$add_post_on_your_wall_view?></span>
											</div>
										</div>
									</div>
								</div>
							</li>
						</ul>

						<ul class="settings-ul security-ul edit-part">
						</ul>

						<div class="new-post-mobile clear editicon2">
		                  	<a class="popup-window grey lighten-1 waves-effect waves-light" href="javascript:void(0)" onclick="open_edit_act_ss()"><i class="mdi mdi-pencil"></i></a>
		               	</div>
					</div>
					
					<div id="menu-notification" class="notification-content settings-content">
						<div class="formtitle"><h4 class="border_bt">Notifications</h4></div>
						<div class="formtitle mobile-show"><h5>Posts</h5></div>
						<ul class="settings-ul notification-ul">	
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">												
												<div class="info">
													<label>When someone like your post</label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="sound-notification">	
													<div class="switch">
														<label for="like_post">
													<?php if ($like_post == 'No'){   
													echo '<input id="like_post" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
													else{
														echo '<input id="like_post" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
															 <span class="lever"></span>
														</label>
													</div>
													<?php
															if(isset($like_post) && !empty($like_post)){
																echo '<input type="hidden" name="like_post" id="like_post_switch" value="'.$like_post.'" />';
															}else{
																echo '<input type="hidden" name="like_post" id="like_post_switch" value="Yes" />';	
															}
														?>
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
							
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">												
												<div class="info">
													<label>When someone comment your post</label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="sound-notification">	
													<div class="switch">
														<label for="comment_post">
													<?php if ($comment_post == 'No'){   
													echo '<input id="comment_post" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
													else{
														echo '<input id="comment_post" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
														 <span class="lever"></span>	
														</label>
													</div>
													<?php
															if(isset($comment_post) && !empty($comment_post)){
																echo '<input type="hidden" name="comment_post" id="comment_post_switch" value="'.$comment_post.'" />';
															}else{
																echo '<input type="hidden" name="comment_post" id="comment_post_switch" value="Yes" />';	
															}
														?>
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
							
							<!-- people start following you -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">											
												<div class="info">
													<label>When someone share your post</label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="sound-notification">	
													<div class="switch">
														<label for="share_post">
													<?php if ($share_post == 'No'){   
													echo '<input id="share_post" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
													else{
														echo '<input id="share_post" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
															 <span class="lever"></span>
														</label>
													</div>
													<?php
															if(isset($share_post) && !empty($share_post)){
																echo '<input type="hidden" name="share_post" id="share_post_switch" value="'.$share_post.'" />';
															}else{
																echo '<input type="hidden" name="share_post" id="share_post_switch" value="Yes" />';	
															}
														?>
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
						</ul>	
						<div class="formtitle mobile-show"><h5>Connections</h5></div>
						<ul class="settings-ul notification-ul">
							
							<!-- connections activity -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">
												<div class="info">
													<label>get notifications about your connect’s activities </label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="connect">	
													<div class="switch">
														<label for="connect_activity">
													<?php if ($connect_activity == 'No'){ 
													echo '<input id="connect_activity" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
													else{ 													   
														echo '<input id="connect_activity" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
															 <span class="lever"></span>
														</label>
													</div>
													<?php 
													if(isset($connect_activity) && !empty($connect_activity)){
													echo '<input type="hidden" name="connect_activity" id="connect_activity_switch" value="'.$connect_activity.'" />'; }
													else {
														echo '<input type="hidden" name="connect_activity" id="connect_activity_switch" value="Yes" />';
													}
													?>
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
							
							<!-- get e-card / e-gift -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">												
												<div class="info">
													<label>Get notifications about your connect’s activities on your post</label>
													
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="activity_on_user">	
													<div class="switch">
														<label for="connect_activity_on_user_post">
													<?php if ($connect_activity_on_user_post == 'No'){  
													echo '<input id="connect_activity_on_user_post" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
														else {
															echo '<input id="connect_activity_on_user_post" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
														}
														?>
															 <span class="lever"></span>
														</label>
													</div>
													<?php
															if(isset($connect_activity_on_user_post) && !empty($connect_activity_on_user_post)){
																echo '<input type="hidden" name="connect_activity_on_user_post" id="connect_activity_on_user_post_switch" value="'.$connect_activity_on_user_post.'" />';
															}else{
																echo '<input type="hidden" name="connect_activity_on_user_post" id="connect_activity_on_user_post_switch" value="Yes" />';	
															}
														?>
													<input type="hidden" name="connect_activity_on_user_post" id="connect_activity_on_user_post_switch" value="Yes" />
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
							
							<!-- non connect activity -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">											
												<div class="info">
													<label>Get notifications about your none connect’s activities on your post</label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="non_connect">
													<div class="switch">
														<label for="non_connect_activity">
													<?php if ($non_connect_activity == 'No'){ 
													echo '<input id="non_connect_activity" class="cmn-toggle cmn-toggle-round" type="checkbox">'; }
													else {
														echo '<input id="non_connect_activity" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
															
															<span class="lever"></span>
														</label>
													</div>
													<?php
															if(isset($non_connect_activity) && !empty($non_connect_activity)){
																echo '<input type="hidden" name="non_connect_activity" id="non_connect_activity_switch" value="'.$non_connect_activity.'" />';
															}else{
																echo '<input type="hidden" name="non_connect_activity" id="non_connect_activity_switch" value="Yes" />';	
															}
														?>
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
							
							<!-- get/confirm connect request -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">											
												<div class="info">
													<label>Get notified when a connect request confirmed</label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="connect_req">
													<div class="switch">
														<label for="connect_request">
													<?php if ($connect_request_notify == 'No'){ 
													echo '<input id="connect_request" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
													else{
														echo '<input id="connect_request" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
													<span class="lever"></span>
														</label>
													</div>
													<?php
															if(isset($connect_request_notify) && !empty($connect_request_notify)){
																echo '<input type="hidden" name="connect_request" id="connect_request_switch" value="'.$connect_request_notify.'" />';
															}else{
																echo '<input type="hidden" name="connect_request" id="connect_request_switch" value="Yes" />';	
															}
														?>
												</div>
											</div>
										</div>	
									
									</div>
								</div>
							</li>
			
							
							<!-- new notification -->
							<li>
								<div class="settings-group">								
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m10 l10 detail-holder">												
												<div class="info">
													<label>Play a sound when new notification is received</label>
												</div>
											</div>																		
											<div class="col s12 m2 l2 right btn-holder">
												<div class="right" id="sound-notification">	
													<div class="switch">
														<label for="sound_on_notification">
													<?php if ($sound_on_notification == 'No'){   
													echo '<input id="sound_on_notification" class="cmn-toggle cmn-toggle-round"  type="checkbox">'; }
													else{
														echo '<input id="sound_on_notification" class="cmn-toggle cmn-toggle-round" checked type="checkbox">';
													}
													?>
													<span class="lever"></span>		
													</label>
													</div>
													<?php
															if(isset($sound_on_notification) && !empty($sound_on_notification)){
																echo '<input type="hidden" name="sound_on_notification" id="sound_on_notification_switch" value="'.$sound_on_notification.'" />';
															}else{
																echo '<input type="hidden" name="sound_on_notification" id="sound_on_notification_switch" value="Yes" />';	
															}
														?>
												</div>
											</div>
										</div>	
									</div>
								</div>
							</li>
						</ul>
						<div class="clear"></div>
					</div>
					
					<div id="menu-block" class="block-content settings-content">
						<div class="formtitle">
							<h4 class="border_bt">Blocking
							 	<span class="right">
							 		<a href="javascript:void(0)" class="editiconCircleEffect editicon1 waves-effect waves-theme" onclick="open_edit_act_blocking()"><i class="mdi mdi-pencil"></i></a>
							 	</span>	
							</h4>
						</div>
						
						<ul class="settings-ul block-ul normal-part">
	
							<!-- Restricted List -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Restricted List</label>
												</div>
											</div>
											<div class="col s12 m9 l9">
												<div class="info">
													<label>People on this list cannot see my posts</label>
												</div>											
											</div>
										</div>
									</div>
								</div>
							</li>
							
							<!-- Blocked List -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Blocked List</label>
												</div>
											</div>
											<div class="col s12 m9 l9">
												<div class="info">
													<label>People on this list cannot contact me</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
						
							<!-- Message Filtering -->
							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Message filter</label>
												</div>
											</div>
											<div class="col s12 m9 l9">
												<div class="info">
													<label>people on this list will not be able to send messages</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>

							<li>
								<div class="settings-group">
									<div class="normal-mode">
										<div class="row">
											<div class="col s12 m3 l3 caption-holder">
												<div class="caption">
													<label>Request filter</label>
												</div>
											</div>
											<div class="col s12 m9 l9">
												<div class="info">
													<label>people on this list will not be able to send me requests</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</li>
						</ul>

						<ul class="settings-ul block-ul edit-part">
						</ul>

						<div class="new-post-mobile clear editicon2">
		                  	<a class="popup-window grey lighten-1 waves-effect waves-light" href="javascript:void(0)" onclick="open_edit_act_blocking()"><i class="mdi mdi-pencil"></i></a>
		               	</div>
					</div>
					
					<!-- close account-->
					<div id="menu-close" class="close-content settings-content lst-close">
					</div>
					<!-- close account-->
					
					<div id="choose-theme" class="choose-theme-content settings-content">
						<div class="formtitle"><h4 class="border_bt">Choose Theme</h4></div>
						<div class="settings-theme">
							<p class="grayp">Please click on a box to choose your theme color:</p>
							<div class="colorsBox theme-drawer">
								<div class="boxrow">
									<a href="javascript:void(0);" body-color="theme-color" onclick="theme_color('theme-color')" class="tm-dark-blue active"></a>
									<a href="javascript:void(0);" body-color="theme-purple" onclick="theme_color('theme-purple')" class="tm-purple"></a>
									<a href="javascript:void(0);" body-color="theme-light-blue" onclick="theme_color('theme-light-blue')" class="tm-light-blue"></a>
								</div>
								<div class="boxrow">
									<a href="javascript:void(0);" body-color="theme-green" onclick="theme_color('theme-green')" class="tm-green"></a>
									<a href="javascript:void(0);" body-color="theme-light-red" onclick="theme_color('theme-light-red')" class="tm-light-red"></a>
									<a href="javascript:void(0);" body-color="theme-light-purple" onclick="theme_color('theme-light-purple')" class="tm-light-purple"></a>
								</div>
								<div class="boxrow">
									<a href="javascript:void(0);" body-color="theme-black" onclick="theme_color('theme-black')" class="tm-black"></a>
									<a href="javascript:void(0);" body-color="theme-bright-blue" onclick="theme_color('theme-bright-blue')" class="tm-bright-blue"></a>
									<a href="javascript:void(0);" body-color="theme-emerald" onclick="theme_color('theme-emerald')" class="tm-emerald"></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	<?php include('../views/layouts/footer.php'); ?>
</div>

<div id="compose_addpersonAction_as_modal" class="modal modalxii_level1">
	<div class="content_header">
		<button class="close_span waves-effect">
		  <i class="mdi mdi-close mdi-20px"></i>
		</button>
		<p class="selected_photo_text"></p>
		<a href="javascript:void(0)" id="chk_person_done_ss" class="done_btn action_btn">Done</a>
	</div> 
	<nav class="search_for_tag">
		<div class="nav-wrapper">
		  <form>
		    <div class="input-field">
		      <input id="search_box" type="search" required="">
		        <label class="label-icon" for="search_box">
		          <i class="zmdi zmdi-search"></i>
		        </label>
		      </div>
		  </form>
		</div>
	</nav>
	<div class="person_box"></div>
</div>

<div id="manageadmin-popup" class="modal manage-modal manageadmin-popup">
	<div class="modal_header">
	<button class="close_btn custom_modal_close_btn close_modal waves-effect">
	<i class="mdi mdi-close mdi-20px"></i>
	</button>
	<h3>Manage Admin</h3>
	</div>
	<div class="custom_modal_content modal_content">
	<div class="main-pcontent spadding">
	<ul class="tabs">
		<li onclick="allmemberforadmin();" class="tab active"><a href="#member-all" data-toggle="tab" aria-expanded="false" class="active">Promote to Admin</a></li>
		<li onclick="memberrequest();" class="tab"><a href="#member-request" data-toggle="tab" aria-expanded="true" class="">Request</a></li>
	<li class="indicator" style="right: 605px; left: 0px;"></li></ul>
	<div class="tab-content">
		<div id="member-all" class="tab-pane in active dis-block" data-id="froup-stab">
			<ul class="manage-members">				<div class="post-holder bshadow">      
		<div class="joined-tb">
			<i class="mdi mdi-file-outline"></i>        
			<p>No record found</p>
		</div>    
	</div>
	</ul>
		</div>
		<div id="member-request" class="tab-pane dis-none">
			<ul class="manage-members">			<div class="post-holder bshadow">      
		<div class="joined-tb">
			<i class="mdi mdi-file-outline"></i>        
			<p>No request found</p>
		</div>    
	</div>
	</ul>
		</div>
	</div>
	</div>
	</div>
</div>

<!--manageorganizer-->
<div id="manageorganizer-popup" class="modal manage-modal manageadmin-popup">
	<div class="modal_header">
	<button class="close_btn custom_modal_close_btn close_modal waves-effect">
	<i class="mdi mdi-close mdi-20px"></i>
	</button>
	<h3>Manage Organizer</h3>
	</div>
	<div class="custom_modal_content modal_content">
	<div class="main-pcontent spadding">
	<ul class="tabs">
		<li onclick="allmemberforadmin();" class="tab active"><a href="#organizer-all" data-toggle="tab" aria-expanded="false" class="active">Promote to Organizer</a></li>
		<li onclick="memberrequest();" class="tab"><a href="#organizer-request" data-toggle="tab" aria-expanded="true" class="">Request</a></li>
	<li class="indicator" style="right: 605px; left: 0px;"></li></ul>
	<div class="tab-content">
		<div id="organizer-all" class="tab-pane in active dis-block" data-id="froup-stab">
			<ul class="manage-members">				<div class="post-holder bshadow">      
		<div class="joined-tb">
			<i class="mdi mdi-file-outline"></i>        
			<p>No record found</p>
		</div>    
	</div>
	</ul>
		</div>
		<div id="organizer-request" class="tab-pane dis-none">
			<ul class="manage-members">			<div class="post-holder bshadow">      
		<div class="joined-tb">
			<i class="mdi mdi-file-outline"></i>        
			<p>No request found</p>
		</div>    
	</div>
	</ul>
		</div>
	</div>
	</div>
	</div>
</div>
<!--manageowner-->
<div id="manageowner-popup" class="modal manage-modal manageadmin-popup">
	<div class="modal_header">
	<button class="close_btn custom_modal_close_btn close_modal">
	<i class="mdi mdi-close mdi-20px"></i>
	</button>
	<h3>Manage Owner</h3>
	</div>
	<div class="custom_modal_content modal_content">
	<div class="main-pcontent spadding">
	<ul class="tabs">
		<li onclick="allmemberforadmin();" class="tab active"><a href="#manageowner-all" data-toggle="tab" aria-expanded="false" class="active">Promote to Owner</a></li>
		<li onclick="memberrequest();" class="tab"><a href="#manageowner-request" data-toggle="tab" aria-expanded="true" class="">Request</a></li>
	<li class="indicator" style="right: 605px; left: 0px;"></li></ul>
	<div class="tab-content">
		<div id="manageowner-all" class="tab-pane in active dis-block" data-id="froup-stab">
			<ul class="manage-members">				<div class="post-holder bshadow">      
		<div class="joined-tb">
			<i class="mdi mdi-file-outline"></i>        
			<p>No record found</p>
		</div>    
	</div>
	</ul>
		</div>
		<div id="manageowner-request" class="tab-pane dis-none">
			<ul class="manage-members">			<div class="post-holder bshadow">      
		<div class="joined-tb">
			<i class="mdi mdi-file-outline"></i>        
			<p>No request found</p>
		</div>    
	</div>
	</ul>
		</div>
	</div>
	</div>
	</div>
</div>

<?php include('../views/layouts/custom_modal.php'); ?>



<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
	<?php include('../views/layouts/mapmodal.php'); ?>
</div>
<script type="text/javascript">
var addUserForAccountSettingsArray = <?php echo json_encode($restricted_listids, true)?>;
var croppicContainerPreloadOptions;
$(document).ready(function () {

	/* Blocking */	
	$("#blocked_list").val([<?php echo $blocked_str; ?>]).material_select();
	$("#message_filtering1").val([<?php echo $message_filtering_str; ?>]).material_select();
	$("#request_filter").val([<?php echo $request_filter_str; ?>]).material_select();
	
	if($(".page-wrapper").hasClass("settings-wrapper")){
		croppicContainerPreloadOptions = {
			cropUrl:'?r=site/profile-image-crop',
			loadPicture: '<?=str_replace('profile/','profile/ORI_',$userr_img)?>',		 
			enableMousescroll:true,
			onBeforeImgUpload: function(){ $('#profCrop').html(''); },
			onAfterImgCrop:function(){ location.reload();},		 
		}			
	}else{
		croppicContainerPreloadOptions = {
			cropUrl:'?r=page/page-image-crop',
			loadPicture: '<?=str_replace('profile/','profile/ORI_',$userr_img)?>',
			enableMousescroll:true
		}
	}
 });
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/accountsettings.js"></script>
<script src="<?=$baseUrl?>/js/croppie.min.js" type="text/javascript"></script> 