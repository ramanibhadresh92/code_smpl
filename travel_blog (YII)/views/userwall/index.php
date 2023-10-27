<?php
use yii\helpers\Url; 
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use frontend\assets\AppAsset;
use frontend\models\Connect;
use frontend\models\LoginForm;
use frontend\models\UserForm;
use frontend\models\Page;
use frontend\models\ProfileVisitor;
use frontend\models\PostForm;
use frontend\models\Like;
use frontend\models\Comment;
use frontend\models\UserSetting;
use frontend\models\Occupation;  
use frontend\models\Personalinfo;
use frontend\models\Language;
use frontend\models\Education;
use frontend\models\Interests;
use frontend\models\SecuritySetting;
use frontend\models\PinImage;
use frontend\models\Credits;
use frontend\models\Vip;
use frontend\models\Verify;
use frontend\models\Referal;
use frontend\models\Destination;
use frontend\models\UserPhotos;
use frontend\models\Notification;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$request = Yii::$app->request;
$email = $session->get('email'); 
$user_id = (string) $session->get('user_id');  
$wall_user_id = (string) $request->get('id');
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
$totalcredits = Credits::travusertotalcredits($wall_user_id);
$total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
$isvip = Vip::isVIP($wall_user_id);
$isVerify = Verify::isVerify($wall_user_id);
$result = LoginForm::find()->where(['email' => $email])->one();
$wallresult = LoginForm::find()->where(['_id' => $wall_user_id])->one();
$user_city = $wallresult['city'];
$user_gender = $wallresult['gender'];
$user_fullname = $wallresult['fullname'];
$data = array('id' => $user_id, 'email'=> $email, 'fullname' => $user_fullname);			
if($user_gender == 'Male')
{
	$gender = 'his';
}
else if($user_gender == 'Female')
{
	$gender = 'her';
}
else
{
	$gender = '';
}

$user_img = $this->context->getimage($wall_user_id,'photo');
if(isset($user_basicinfo['cover_photo']) && !empty($user_basicinfo['cover_photo']))
{
    $cover_photo = $user_basicinfo['cover_photo'];
}
else
{
    $cover_photo = 'cover.jpg';
}

$model_connect = new Connect();
$connections = $model_connect->userlist();
$userid = (string)$session->get('user_id');
$visitors = ProfileVisitor::find()->with('user')->where(['user_id' => "$wall_user_id"])->all();
$personal_info = Personalinfo::find()->where(['user_id' => (string)$userid])->one();
$user_created_date = Yii::$app->EphocTime->time_elapsed_A(time(),$user_basicinfo['created_date']);
if(empty($user_basicinfo['last_time'])){$user_basicinfo['last_time'] = $user_basicinfo['created_date'];}
$last_login = Yii::$app->EphocTime->time_elapsed_A(time(),$user_basicinfo['last_time']);
$result_setting = UserSetting::find()->where(['user_id' => $_GET['id']])->one();
$email_access = $result_setting['email_access'];
$mobile_access = $result_setting['mobile_access'];
$birth_date_access = $result_setting['birth_date_access'];
$session = Yii::$app->session;
$email = $session->get('email');
$user = LoginForm::find()->where(['email' => $email])->one();
$suserid = (string) $result['_id'];
$guserid = $_GET['id'];
$model_pv = new ProfileVisitor();
$count_pv = ProfileVisitor::getAllVisitors($guserid);
$connections_city = Connect::getConnectionsCity($guserid);
$connections_city_array = Connect::getConnectionsCityValue($guserid);
//$connections_city_array = json_encode($connections_city_array, true);
$connections_details = Connect::getConnectionsMapDetails($guserid);
//$connections_details = json_encode($connections_details, true);
$occupation = isset($user_data['occupation']) ? $user_data['occupation'] : '';
$interests = isset($user_data['interests']) ? $user_data['interests'] : '';
$language = isset($user_data['language']) ? $user_data['language'] : '';
$education = isset($user_data['education']) ? $user_data['education'] : '';
$visited_countries = isset($user_data['visited_countries']) ? $user_data['visited_countries'] : '';
$lived_countries = isset($user_data['lived_countries']) ? $user_data['lived_countries'] : '';

$occu_str = array();
if(isset($occupation) && $occupation != '') {
	$occu_str = explode(",", trim($occupation));
	array_filter($occu_str);
}
$occu_str = json_encode($occu_str, true);

$inter_str = array();
if(isset($interests) && $interests != '') {
	$inter_str = explode(",", trim($interests));
	array_filter($inter_str);
}
$inter_str = json_encode($inter_str, true);

$lang_str = array();
if(isset($language) && $language != '') {
	$lang_str = explode(",", trim($language));
	array_filter($lang_str);
}
$lang_str = json_encode($lang_str, true);

$edu_str = array();
if(isset($education) && $education != '') {
	$edu_str = explode(",", trim($education));
	array_filter($edu_str);
}
$edu_str = json_encode($edu_str, true);

$visited_str = array();
if(isset($visited_countries) && $visited_countries != '') {
	$visited_str = explode(",", trim($visited_countries));
	array_filter($visited_str);
}
$visited_str = json_encode($visited_str, true);

$lived_str = array();
if(isset($lived_countries) && $lived_countries != '') {
	$lived_str = explode(",", trim($lived_countries));
	array_filter($lived_str);
}
$lived_str = json_encode($lived_str, true);

$result_security = SecuritySetting::find()->where(['user_id' => $guserid])->one();
if ($result_security)
{
    $photo_setting = $result_security['view_photos'];
    $add_post_on_wall = $result_security['add_public_wall'];
    $my_connect_view_status = $result_security['connect_list'];
    $my_post_view_status = $result_security['my_post_view_status'];
    if ($my_post_view_status == 'Private') {
        $post_dropdown_class = 'lock';
    } else if ($my_post_view_status == 'Connections') {
        $post_dropdown_class = 'user';
    } else {
        $my_post_view_status = 'Public';
        $post_dropdown_class = 'globe';
    }  
} else {
    $my_post_view_status = 'Public';
    $post_dropdown_class = 'globe';
    $photo_setting = 'Public';
    $my_connect_view_status = 'Public';
    $add_post_on_wall = 'Public';
}
$is_connect = Connect::find()->where(['from_id' => "$guserid",'to_id' => "$suserid",'status' => '1'])->one();
$this->title = 'Wall';
$par = '';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<link href="<?=$baseUrl?>/css/custom-croppie.css" rel="stylesheet">
<link href="<?=$baseUrl?>/css/jquery-gauge.css" type="text/css" rel="stylesheet">	
<link href="<?=$baseUrl?>/css/animate.css" rel="stylesheet">
<div class="page-wrapper wallpage subpage-wrapper hidemenu-wrapper noopened-search menutransheader-wrapper userwall transheadereffect show-sidebar"> 
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
		<div class="wallpage-content">	
			<div class="bstsection">  
				<div class="wall-left-section">
					<?php include('headerwall.php'); ?>
					<div class="clear"></div>
					<div class="main-content gone">
						<div class="tab-content main-tab-content">
							<div id="wall-content" class="tab-pane main-tabpane wall-content active in" tabname="Wall">
								<div class="wallcontent-column">
									<div class="wall-info swapping-parent">
										<div class="content-box bshadow">
											<div class="cbox-title"><span class="directeditmode">
												<i class="mdi mdi-account"></i>
												<a href="javascript:void(0)" onclick="openAboutSection('normal'),section_about('<?=$wall_user_id?>')">
													About
												</a>
												</span>
											</div>
											<div class="cbox-desc">
												<?php if(((count($user_data) > 0) && ($user_basicinfo['city'] != "") && ($user_basicinfo['city'] != null)) || ($guserid == $suserid)){ ?>
												<div class="normal-mode">
													<?php if(($user_data['occupation']!="" && ($guserid != $suserid)) || ($guserid == $suserid)){ ?>
													<div class="info-row">
														<div class="icon-holder">
															<span class="directeditmode"><i class="mdi mdi-briefcase"></i></span>
														</div>
														<div class="desc-holder">
															<span class="occupation_rs directeditmode">
																<?php if($user_data['occupation']!=""){ ?>
																	<span class="darktext">Profession</span>
																<?php echo str_replace(",", ", ", $user_data['occupation']); }
																else { ?>
																<a <?php if($guserid == $suserid){ ?>onclick="swapMode(this)"<?php } ?> href="javascript:void(0)">What your profession</a>
																<?php } ?>
															</span>
														</div>
													</div>	
													<?php } ?>
													
													<?php if(($user_data['education']!="" && ($guserid != $suserid)) || ($guserid == $suserid)){ ?>
														<div class="info-row">
															<div class="icon-holder">
																<span class="directeditmode"><i class="mdi mdi-school"></i></span>
															</div>
															<div class="desc-holder">
																<span class="education_rs directeditmode">
																	<?php if($user_data['education']!=""){ ?>
																		<span class="darktext">Studied</span>
																	<?php echo str_replace(",", ", ", $user_data['education']); }
																	else { ?>
																	<a <?php if($guserid == $suserid){ ?>onclick="swapMode(this)"<?php } ?> href="javascript:void(0)">What is your highest degree</a>
																	<?php } ?>
																</span>
															</div>
														</div>
													<?php } ?>
													
													<?php if(($user_basicinfo['city']!="" && ($guserid != $suserid)) || ($guserid == $suserid)){ ?>
														<div class="info-row">
															<div class="icon-holder">
																<span class="directeditmode"><i class="mdi mdi-home"></i></span>
															</div>
															<div class="desc-holder">
																<span class="darktext directeditmode">Lives in</span>
																<span class="lives_rs directeditmode"><?= $user_basicinfo['city']?></span>
															</div>
														</div>
													<?php } ?>
													
													<?php if(($user_data['language']!="" && ($guserid != $suserid)) || ($guserid == $suserid)){ ?>
														<div class="info-row">
															<div class="icon-holder">
																<span class="directeditmode"><i class="mdi mdi-library-books"></i></span>
															</div>
															<div class="desc-holder">
																<span class="language_rs directeditmode">
																	<?php if($user_data['language']!=""){?>
																		<span class="darktext">Speaks</span>
																	<?php echo str_replace(",", ", ", $user_data['language']); }
																	else { ?>
																	<a <?php if($guserid == $suserid){ ?>onclick="swapMode(this)"<?php } ?> href="javascript:void(0)">What languages you speak</a>
																	<?php } ?>
																</span>
															</div>
														</div>
													<?php } ?>
													
													<?php if(($user_data['interests']!="" && ($guserid != $suserid)) || ($guserid == $suserid)){ ?>
														<div class="info-row">
															<div class="icon-holder">
																<span class="directeditmode"><i class="zmdi zmdi-thumb-up"></i></span>
															</div>
															<div class="desc-holder">
																<span class="interests_rs directeditmode">
																	<?php if($user_data['interests']!=""){ ?>
																		<span class="darktext">Likes</span>
																	<?php echo str_replace(",", ", ", $user_data['interests']); }
																	else { ?>
																	<a onclick="swapMode(this)" href="javascript:void(0)">What are your interest</a>
																	<?php } ?>
																</span>
															</div>
														</div>
													<?php } ?>
													
													<?php if(($user_data['about']!="" && ($guserid != $suserid)) || ($guserid == $suserid)){ ?>
														<div class="info-row">
															<div class="icon-holder">
																<span class="directeditmode"><i class="mdi mdi-rss"></i></span>
															</div>
															<div class="desc-holder">
																<div class="para-section">
																	<div class="para">
																		<span class="about_rs directeditmode">
																			<?php if($user_data['about']!=""){?>
																				<span class="darktext">Personally, </span><?php echo $user_data['about']; 
																			} else { ?>
																			<a onclick="swapMode(this)" href="javascript:void(0)">Tell other about you</a>
																			<?php } ?>
																		</span>
																	</div>
																</div>
															</div>
														</div>
													<?php } ?>
												</div>
												<?php } else {
													if($wallresult['gender'] == 'Male'){$gencat = 'his';}
													else if($wallresult['gender'] == 'Female'){$gencat = 'her';}
													else{$gencat = '';}
												?>
												<span class="directeditmode">About section will be soon be update by <?=$wallresult['fname']?></span>
												<?php } ?>
											</div>
										</div>
									</div>

									<div class="content-box bshadow">
										<div class="cbox-title">
											<i class="mdi mdi-account-group"></i>
											<a class="wallconnectclk" onclick="openWallTabInternally('connections-content')">Connections
											<?php 
												$mutualcount = Connect::mutualconnectcount($wall_user_id);
												$totalcount = Connect::getuserConnections($wall_user_id);
											?>
											<span class="suminfo"><?= count($totalcount)?> connections</span></a>
										</div>
										<div class="cbox-desc new-md-tabs">
											<ul class="tabs nav-custom-tabs text-right">
												<li class="tab"><a href="#umap"><i class="zmdi zmdi-pin"></i>Map</a></li>
												<li class="tab"><a href="#ulist"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>List</a></li>
											</ul>
											<div class="tab-content">
												<div class="tab-pane fade in active" id="umap">
													<div class="map-holder mt10" style="width:100%;height:278px;"></div>
												</div>
												<div class="tab-pane fade in" id="ulist">
												
													<div class="connect-list grid-list">
														<div class="row">
														<?php if(($my_connect_view_status == 'Public') || ($my_connect_view_status == 'Connections' && ($is_connect || $guserid == $suserid)) || ($my_connect_view_status == 'Private' && ($is_connect || $guserid == $suserid))) { ?>
														<?php if(count($user_connections) > 0) { 
															foreach($user_connections as $user_connect) {

																
																$lmodel = new \frontend\models\LoginForm();
																$connectinfo = LoginForm::find()->where(['_id' => $user_connect['from_id']])->one();
																$fmodel = new \frontend\models\Connect();
																$mutualcount = Connect::mutualconnectcount($connectinfo['_id']);
																$countposts = count(PostForm::getUserPost($user_connect['from_id']));
																$frnd_img = $this->context->getimage($connectinfo['_id'],'photo');
																
																 $link = Url::to(['userwall/index', 'id' => (string)$connectinfo['_id']]);
																	$getuserinfo = LoginForm::find()->where(['_id' => $connectinfo['_id']])->one();
																	$personalinfo = Personalinfo::find()->where(['user_id' => $connectinfo['_id']])->one();
																	$dp = $this->context->getimage($getuserinfo['_id'],'photo');
																	if(isset($getuserinfo['cover_photo']) && !empty($getuserinfo['cover_photo']))
																	{
																		$cover = $getuserinfo['cover_photo'];
																		if(strstr($cover,'cover'))
																		{
																			$cvr = substr_replace($getuserinfo['cover_photo'], '-thumb.jpg', -4);
																			$cover = '../uploads/cover/thumbs/'.$cvr;
																		}
																		else
																		{
																			$cover = '../uploads/cover/thumbs/thumb_'.$getuserinfo['cover_photo'];
																		}
																	}
																	else
																	{
																		$cover = 'cover.jpg';
																	}
																	if(isset($uid) && !empty($uid)) {
																	$dpforpopup = $this->context->getimage($uid,'thumb'); 
																	}
																	$mutualcount = Connect::mutualconnectcount($getuserinfo['_id']);
																	$frndid = $getuserinfo['_id'];
																	$isconnect = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$frndid",'status' => '1'])->one();
																	$isconnectrequestsent = Connect::find()->where(['from_id' => "$user_id",'to_id' => "$frndid",'status' => '0'])->one();
																		
														?>
															<div class="grid-box connect-tooltip" id="<?=$connectinfo['_id']?>">
																<div class="connect-box">
																	<div class="imgholder"><img src="<?= $frnd_img?>"/></div>
																	<div class="descholder">
																		<a href="<?php $id =  $connectinfo['_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>">											
																			<span class="userlink"><?php echo $connectinfo['fname'].' '.$connectinfo['lname']?></span>
																		</a>
																		<?php if($countposts > 0){?>
																			<span class="info"><?= $countposts?> posts</span>
																		<?php } ?>
																	</div>								
																	<span class="online-mark"><i class="zmdi zmdi-check"></i></span>
																</div>							
															</div>
															<?php
																	} 
																} else {
																	$this->context->getnolistfound('noconnectfound');
																} 
															} else { 
																echo '<span class="no-listcontent">'.$wallresult['fname'].' has set connect list as private</span>';
															 } ?>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<?php if(($photo_setting == 'Public') || ($photo_setting == 'Connections' && ($is_connect || (string)$guserid == $suserid)) || ($photo_setting == 'Private' && (string)$guserid == $suserid)) { ?>
										<div class="content-box bshadow">
											<div class="cbox-title">
												<i class="mdi mdi-file-image"></i>
												<a class="wallphotosclk" onclick="openWallTabInternally('photos-content')">
													Photos
													<?php
													$total_pictures = UserPhotos::getPics($wall_user_id);
													$profile_albums = UserPhotos::getProfilePics($wall_user_id);
													$total_profile_albums = count($profile_albums);
													$cover_albums = UserPhotos::getCoverPics($wall_user_id);
													$total_cover_albums = count($cover_albums);
													$totalcounts = $total_pictures;
													if($totalcounts>0){
													?>
														<span class="suminfo"><?= $totalcounts;?> photos</span>
													<?php } ?>
												</a>
											</div>
											<div class="cbox-desc gallary-wall">
												<div class="photo-list grid-list">
													<?php if(($photo_setting == 'Public') || ($photo_setting == 'Connections' && ($is_connect || (string)$guserid == $suserid)) || ($photo_setting == 'Private' && (string)$guserid == $suserid)) { ?>
													<div class="row">
													<div class="photo-box himg-box imgfix">
													<?php  
													if(count($photos)>0){
														$totalcomplete = 1;
														foreach($photos as $post){
														if(isset($post['image']) && !empty($post['image'])){
														$eximgs = explode(',',$post['image'],-1);
														foreach ($eximgs as $eximg) {
															if($totalcomplete>6) {
																continue 2;
															}

															$picsize = '';
															$imgclass = '';
															$inameclass = $this->context->getimagefilename($eximg);
															$pinval = 'pin';
															if(file_exists('../web'.$eximg)) {
																$val = getimagesize('../web'.$eximg);
																$iname = $this->context->getimagename($eximg);
																$pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
																if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
																$picsize .= $val[0] .'x'. $val[1] .', ';
																if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
															}
														?>
														<div class="grid-box">
															<div class="lgt-gallery lgt-gallery-photo post-img one-img <?=$imgclass?>-box dis-none">
																<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-pinit="<?=$pinval?>" data-sizes="<?=(string)$post['_id']?>|||UserPhotos" class="allow-gallery imgpin pimg-holder">
																	<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?=$imgclass?>"/>
																</a>
															</div>							
														</div>
														<?php 
														$totalcomplete++;
													} } } }
														else {
															$this->context->getnolistfound('nophotofound');
														} ?>
													</div>
													</div>
													<?php } else {
															$this->context->getnolistfound('photosecurity');
													} ?>
												</div>
											</div>
										</div>
									<?php } ?>	

									<div class="content-box bshadow">
										<div class="cbox-title">
											<i class="zmdi zmdi-thumb-up"></i>
											<a class=" walllikesclk" onclick="openWallTabInternally('likes-content')">
											Likes
											<span class="suminfo"><?php echo $allpageslikescount;?> likes</span>
															</a>
										</div>
										<div class="cbox-desc">
											<div class="like-list grid-list">
												<?php if($allpageslikescount > 0) { ?>
												<div class="row">
												<?php foreach($lastsixpageslikes as $lastsixpageslike){
													$pageid = (string)$lastsixpageslike['post_id'];
													$pagedetail = Page::find()->where(['_id' => $pageid])->one();
													$page_img = $this->context->getpageimage($pageid);
													$getpagelikes = Page :: getPageLikes($pageid);
												?>
													<div class="grid-box">
														<div class="like-box himg-box">
															<div class="imgholder"><img src="<?=$page_img?>" class="himg"/></div>
															<div class="descholder">	
																<a href="javascript:void(0)" class="userlink"><?=$pagedetail['page_name']?>
																	<span class="info"><?php echo $getpagelikes;?> Like<?php if($getpagelikes > 1){?>s<?php } ?></span>
																</a>
															</div>
														</div>							
													</div>
													<?php } ?>
												</div>
												<?php } else { ?>
												<?php $this->context->getnolistfound('nolikefound'); ?>
												<?php } ?>
											</div>
										</div> 
									</div>
								</div>
								
								<?php include('post.php'); ?>
							</div>

							<div id="activity-content" class="tab-pane main-tabpane activity-content" tabname="Activity">
								<div class="combined-column">												
									<div class="content-box nobg">
										<div class="cbox-title nborder maintitle">
											<div class="subtitle"><h5>Activity Log</h5></div>
										</div>
									</div>
								</div>
								<div class="cbox-desc maindesc">
									<div class="activity-holder">
										<div class="content-box bshadow">
											<div class="cbox-desc">
												<ul>
													<li class="main-li">
														<center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
													</li>
												</ul>
											</div>
										</div>
									</div>	
								</div>
							</div>
	 
							<div id="about-content" class="tab-pane main-tabpane about-content" tabname="About">
								<div class="combined-column">
									<div class="content-box bshadow">
										<div class="cbox-title nborder hidetitle-mbl">
											<i class="mdi mdi-account mdi-16px"></i>
											About 
										</div>
										<div class="cbox-desc" id="section_about">
										</div>
									</div>
								</div>
							</div>

							<div id="gallery-content" class="tab-pane main-tabpane gallery-content" tabname="Gallery">
								<div class="combined-column">
								   <div class="cbox-desc">
										<div class="right upload-gallery" style="cursor: pointer;">Upload Photo</div> 
									</div>
								</div>
							</div>

							<div id="contribution-content" class="tab-pane main-tabpane contribution-content" tabname="Contribution">
								<div class="combined-column">
								</div>
							</div>

							<div id="saved-content" class="tab-pane main-tabpane saved-content" tabname="Saved">
								<div class="combined-column">
								</div>
							</div>					
							
							<div id="connections-content" class="tab-pane main-tabpane  connections-gridview connections-content connections-content-main" tabname="Connections">
								<div class="combined-column"></div>
							</div>
							 
							<div id="photos-content" class="tab-pane main-tabpane  photos-content" tabname="Photos">
								<div class="combined-column"></div>
							</div>
							
							<div id="destinations-content" class="tab-pane main-tabpane destinations-content" tabname="Destinations" style="display: none;">
								<div class="combined-column"> 
									<div class="fake-title-area divided-nav">
										<ul class="tabs nav-custom-tabs text-right">
											<li class="tab"><a href="#destination-map"><i class="zmdi zmdi-pin"></i>Map</a></li>
											<li class="tab" onclick="destList();"><a href="#destination-list"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>List</a></li>
										</ul>			 													
									</div>
									<div id="wish-dest-stbox">
										<center>
											<div class="lds-css ng-scope"> 
												<div class="lds-rolling lds-rolling100"> 
													<div></div> 
												</div>
											</div>
										</center>
									</div>								
								</div>
							</div> 
							
							<div id="likes-content" class="tab-pane main-tabpane pages-page grid-view general-page likes-content-main" tabname="Likes">
								<div class="combined-column">
								</div>
							</div>
							
							<div id="refers-content" class="tab-pane main-tabpane refers-content" tabname="References">
								<div class="combined-column">
								</div>
							</div>

							<div id="endorsements-content" class="tab-pane main-tabpane dis-none" tabname="Endorsements">
								<div class="combined-column">
									<div class="content-box bshadow">
										<div class="cbox-title nborder">
											<i class="mdi mdi-file-image"></i>
											Endorsements
										</div>
										<?php $this->context->getnolistfound('comingsoon'); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="wall-right-section">
					<div class="custom-map">
						<?php $this->context->GetMap($user_city);?>
					</div>
					<div class="custom-wall-links">
						<ul class="tabs" id="rightmenulist">
							<li class="wallclk tab"><a tabname="Wall" href="#wall-content" class="rgttblb2HU active" id="wall-contentmenu">Wall</a></li>
							
							<li class="aboutclk tab"><a tabname="About" href="#about-content" class="rgttblb2HU" id="about-contentmenu" onclick="section_about('<?=$wall_user_id?>')">About</a></li>
							
							<li class="wallconnectclk tab rem-shadow-none"><a tabname="Connections"  class="rgttblb2HU" href="#connections-content" id="connections-contentmenu">Connections</a></li>
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
								$lastsixpageslikes = array_slice($lastsixpageslikes, 0, 6);  
								$destcount = Destination::getDestinationCount($wall_user_id);
							?>
							<li class="wallphotosclk tab rem-shadow-none"><a tabname="Photos" class="rgttblb2HU" href="#photos-content" id="photos-contentmenu">Photos</a></li>
							
							<li class="wallcontrclk tab"><a tabname="Contribution" class="rgttblb2HU" href="#contribution-content" id="contribution-contentmenu">Contribution</a></li>
							
							<li class="wallgalleryclk tab" data-wallid=<?=$wall_user_id?>><a tabname="Gallery" href="#gallery-content" class="rgttblb2HU" id="gallery-contentmenu" onclick="box()">Gallery</a></li>
							
							<li class="walldestclk tab rem-shadow-none"><a tabname="Destinations" class="rgttblb2HU" href="#destinations-content" id="destinations-contentmenu">Destinations</a></li>
							
							<?php if($userid == $user_basicinfo['_id']) {?>
							<li class="wallsavedclk tab"><a tabname="Saved" href="#saved-content" class="rgttblb2HU" id="saved-contentmenu">Saved</a></li>
							<?php } ?>
							
							<li class="walllikesclk tab"><a tabname="Likes" href="#likes-content" class="rgttblb2HU" id="likes-contentmenu">Likes</a></li>
							
							<li class="wallrefersclk tab"><a tabname="References" class="rgttblb2HU" id="refers-contentmenu" href="#refers-content" <?php if($wall_user_id == $user_id){ ?>class="disabled" <?php } ?>>Refers</a></li>
						</ul>
					</div>
					
					<div class="scontent-column">
						<?php include('../views/layouts/people_view_you.php'); ?>
						<?php include('../views/layouts/recently_joined.php'); ?>
						<div class="adslistingblock">
						<?php include('../views/layouts/travads.php'); ?>
						</div> 
					</div> 
				</div>
				<div id="chatblock">
					<div class="float-chat anim-side">
						<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
						</div>
					</div>
				</div>
				<div class="new-post-mobile clear">
					<a href="javascript:void(0)" class="popup-window"  id="composetoolboxAction"><i class="mdi mdi-pencil"></i></a>
				</div>	
			</div>
			<div class="right-section">
				<div id="detailbox">
					<div class="main-msgwindow">
						<h4 class="dateshower"></h4>
						<div class="photos-thread">
							<a href="javascript:void(0)" onclick="hideMsgPhotos()" class="backlink"><i class="mdi mdi-menu-left"></i> Back to conversation</a>
							<div class="albums-grid images-container">
								<div class="row">												
								</div>
							</div>

						</div>
						<div class="allmsgs-holder">
							<div class="msg-notice">
								<div class="mute-notice">
									This conversation has been muted. All the push notifications will be turned off. <a href="javascript:void(0)" onclick="manageMuteConverasion()">Unmute</a>
								</div>
								<div class="block-notice">
									This conversation is blocked. <a href="javascript:void(0)" onclick="manageBlockConverasion(this)">Unblock</a>
								</div>
							</div>
							<ul class="current-messages"> 
								<li class="mainli active" id="li_msg_<?=(string)$wall_user_id?>"> 
									<div class="msgdetail-list nice-scroll" tabindex="18" style="opacity: 1; outline: none;">
										<div class="msglist-holder images-container">
											<ul class="outer"></ul>
										</div>
									</div>
								</li>
							</ul>
							<div class="newmessage" id="li-user-blank">
								<div class="msgdetail-list nice-scroll" style="overflow-y: hidden;" tabindex="6"></div>
							</div>

							<div class="hidden-attachment-box hidden">
                              <div class="innerhidden-attachment-box">
                                 <div class="up">
                                    <div class="wp-location attachmentdiv">
                                       <a href="javascript:void(0)">
                                          <div class="selfdiv">
                                             <center>
                                                <img src="<?=$baseUrl?>/images/wp-simily.png">
                                                <div>Video</div>
                                             </center>
                                          </div>
                                       </a>
                                    </div>
                                    <div class="wp-photo attachmentdiv">
                                       <a href="javascript:void(0)">
                                          <div class="selfdiv">
                                             <center>
                                                <img src="<?=$baseUrl?>/images/wp-camera.png">
                                                <div>Camera</div>
                                             </center>
                                          </div>
                                       </a>
                                    </div>
                                    <div class="wp-video attachmentdiv">
                                       <a href="javascript:void(0)">
                                          <div class="selfdiv">
                                             <center>
                                             	<img src="<?=$baseUrl?>/images/wp-photovideo.png" onclick="attach_file_add()">
                                                <div>Photo</div>
                                                <input type="file" id="attach_file_add" class="dis-none" />
                                             </center>
                                          </div>
                                       </a>
                                    </div>
                                 </div>
                                 <div class="bottom">
                                    <div class="wp-gift attachmentdiv">
                                       <a href="javascript:void(0)" onclick="giftModalAction()">
                                          <div class="selfdiv">
                                             <center>
                                                <img src="<?=$baseUrl?>/images/wp-gift.png">
                                                <div>Gift</div>
                                             </center>
                                          </div>
                                       </a>
                                    </div>
                                    <div class="wp-location attachmentdiv">
                                       <a href="javascript:void(0)">
                                          <div class="selfdiv">
                                             <center>
                                                <img src="<?=$baseUrl?>/images/wp-location.png">
                                                <div>Location</div>
                                             </center>
                                          </div>
                                       </a>
                                    </div>
                                    <div class="wp-location attachmentdiv">
                                       <a href="javascript:void(0)">
                                          <div class="selfdiv">
                                             <center>
                                                <img src="<?=$baseUrl?>/images/wp-contact.png">
                                                <div>Contact</div>
                                             </center>
                                          </div>
                                       </a>
                                    </div>
                                    
                                 </div>
                              </div>
                            </div>

							<div class="addnew-msg">
							   <div class="write-msg input-field">
							      <div class="fixed-action-btn horizontal click-to-toggle attachment_add_icon docustomize">
							         <a href="javascript:void(0)"><i class="mdi mdi-attachment mdi-rotate-135 prefix"></i></a>
							         <ul>
										<li>	
							         		<a href="javascript:void(0)">
							                  <img src="<?=$baseUrl?>/images/wp-location.png">
							               </a>
							            </li>
							            <li>	
							         		<a href="javascript:void(0)">
							                  <img src="<?=$baseUrl?>/images/wp-contact.png">
							               </a>
							            </li>
							            <li>
							               <a href="javascript:void(0)">
							                  <img src="<?=$baseUrl?>/images/wp-photovideo.png" onclick="attach_file_add()">
							                  <input type="file" id="attach_file_add" class="dis-none" />
							               </a>
							            </li>
							            <li>
							               <a href="javascript:void(0)" onclick="giftModalAction()">
							                  <img src="<?=$baseUrl?>/images/wp-gift.png">
							               </a>
							            </li>
							         </ul>
							      </div>
							      <div class="emotion-holder gifticonclick">
							          <i class="zmdi zmdi-mood" onclick="manageEmotionBox(this,'messages')"></i>
							          <div class="emotion-box dis-none">
							            <div class="nice-scroll emotions">
							               <ul class="emotion-list">
							               </ul>
							            </div>
							          </div>
							      </div>
							      <textarea id="inputMessageWall" class="inputMessageWall materialize-textarea" placeholder="Type your message"></textarea>
							   </div>   
							   <div class="msg-stuff">
							      <div class="send-msg">
							         <button class="btn btn-primary btn-xxs btn-msg-send" onclick="messageSendFromMessage();"><i class="mdi mdi-telegram"></i></button>
							      </div>
							   </div>
							</div>
							
							<div class="bottom-stuff">
								<h6>Select messages to delete</h6>
								<div class="btn-holder">
									<a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="deleteselectedmessage()">Delete</a>
									<a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="hideMsgCheckbox()">Cancel</a>
								</div>
							</div>
							<div class="selected_messages_box">
								<a class="close_selected_messages_box waves-effect" onclick="closeSelectedMessage()">
									<i class="mdi mdi-close mdi-20px	"></i>
								</a>
								<p class="selected_msg_number">
									<span>0</span>  selected
								</p>
								<div class="selected_msg_functions">
									<a onclick="selectedmessagesaved()">
										<i class="zmdi zmdi-star"></i>
									</a>
									<a onclick="deleteselectedmessage()">
										<i class="zmdi zmdi-delete"></i>
									</a> 
									<a>
										<i class="zmdi zmdi-forward"></i>
									</a>
									<a class="downloadicon">
										<i class="zmdi zmdi-upload"></i>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="callbox" class="dis-none" style="height: 500px; width: 98%; background: black; padding-left: 36%;">
					<div class="callboxinnerbox" id="box1" style="width: 200px; height: 200px; ">
						<img style="width: 100%; height: 100%" src="">
					</div>
					<div class="callboxinnerbox" id="box2" style="margin: 20px 0; height: 55px;  width: 200px; padding-left: 57px;"><img style="transform: rotate(90deg); padding-left: 0px;" src="<?=$baseUrl?>/images/msgvideoprocess.gif"></div>

					<div class="callboxinnerbox" id="box3" style="width: 200px; height: 200px; ">
						<video id='minivideo' autoplay='autoplay' style="width: 100%; height: 100%;"></video>
							<div id='ownvolume'></div>
					</div>
				</div>
			</div>
		</div>
	</div> 
	<?php include('../views/layouts/footer.php'); ?>
</div>	
</div>

<div id="compose_tool_box" class="modal compose_tool_box post-popup custom_modal main_modal">
</div> 

<div id="composeeditpostmodal" class="modal compose_tool_box edit_post_modal post-popup main_modal custom_modal compose_edit_modal">
</div>

<div id="sharepostmodal" class="modal sharepost_modal post-popup main_modal custom_modal">
</div>
 
<!-- Post detail modal -->
<div id="postopenmodal" class="modal modal_main compose_tool_box custom_modal postopenmodal_main postopenmodal_new">	
</div>     

<!--post comment modal for xs view-->
<div id="comment_modal_xs" class="modal comment_modal_xs">
</div>  

<div id="compose_mapmodal" class="modal map_modal compose_inner_modal modalxii_level1 map_modalUniq">
	<?php include('../views/layouts/mapmodal.php'); ?>
</div>

<?php include('../views/layouts/addpersonmodal.php'); ?>
<?php include('../views/layouts/editphotomadol.php'); ?>

<div id="compose_Comment_Action" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose compose_Comment_Action"></div>

<div id="compose_camera" class="modal compose_tool_box post-popup custom_modal main_modal imgcrop-pop">
    <div class="modal-content  text-imgcrop">
        <div class="new-post active">
            <div class="npost-content">
                <div class="cropper cropper-wrapper">   
                    <div class="crop dis-none">
                        <div class="green-top desktop-view showon">Drag to crop</div>
                        <div class="js-cropping"></div> 
                        <i class="js-cropper-result--btn zmdi zmdi-check upload-btn"></i>
                        <i class="mdi mdi-close	 img-cancel-btn" onclick="$('.js-cropper-result').show();$('.crop').hide();$('.image-upload').show();"></i>
                    </div>                                    
                </div>      
            </div>
        </div>
    </div>                  
</div>

<div id="addAlbumContentPopup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose"></div>

<div id="moveImageToAlbum" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose"></div>
	
<div id="compose_Reference" class="modal compose_tool_box post-popup custom_modal main_modal Reference-new-popup"></div>

<div id="edit-album-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose"></div>

<div id="suggest-connections" class="modal tbpost_modal custom_modal split-page main_modal"></div>

<div id="add-photo-popup" class="modal addphoto_modal custom_md_modal"></div>

<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

<div id="edit-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

<div id="userwall_tagged_users" class="modal modalxii_level1">
	<div class="content_header">
		<button class="close_span waves-effect">
			<i class="mdi mdi-close mdi-20px"></i>
		</button>
		<p class="selected_photo_text"></p>
		<a href="javascript:void(0)" class="chk_person_done_new done_btn focoutTRV03 action_btn">Done</a>
	</div>
	<nav class="search_for_tag">
		<div class="nav-wrapper">
		  <form>
		    <div class="input-field">
		      <input id="tagged_users_search_box" class="search_box" type="search" required="">
		        <label class="label-icon" for="tagged_users_search_box">
		          <i class="zmdi zmdi-search mdi-22px"></i>
		        </label>
		      </div>
		  </form>
		</div>
	</nav>
	<div class="person_box"></div>
</div>


<?php include('../views/layouts/custom_modal.php'); ?>
<?php 
if(isset($_GET['par']) && !empty($_GET['par']) && $_GET['par'])
{
    $par = $_GET['par'];
}
?>
<?php $this->endBody() ?> 

<script>
var data1=<?php echo json_encode($usrfrdlist); ?>;
var data2 = <?php echo json_encode($data);?>;
var $par = '<?=$par?>';
var wall_user_id ='<?php echo (string) $request->get('id'); ?>';
var baseUrl ='<?php echo (string) $baseUrl; ?>';
var $occu_str = <?php echo json_encode($occu_str, true); ?>;
var $inter_str = <?php echo json_encode($inter_str, true); ?>;
var $lang_str = <?php echo json_encode($lang_str, true); ?>;
var $edu_str = <?php echo json_encode($edu_str, true); ?>; 
var $visited_str = <?php echo json_encode($visited_str, true); ?>;
var $lived_str = <?php echo json_encode($lived_str, true); ?>;
var wall_name = '<?=$user_fullname;?>';
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>

<script type="text/javascript" src="<?=$baseUrl?>/js/jquery.cropit.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/waterfall-light.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/jquery-gauge.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/loader.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/wall.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/userwall.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/connect.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/chart.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/referal.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/croppie.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/post.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/jquery.mousewheel.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/emoticons.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/custom-emotions.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/emostickers.js"></script> 
<script type="text/javascript" src="<?=$baseUrl?>/js/custom-emostickers.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/messages-function.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/messages-handler.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/wNumb.min.js"></script>
<script src="<?=$baseUrl?>/js/chat.js" type="text/javascript"></script>
<script src="<?=$baseUrl?>/js/socket.io.js"></script>

<script type="text/javascript">

if (window.location.href.indexOf("localhost") > -1) {
    var socket = io('http://localhost:3000'); ////////// LOCAL
} else {
    var socket = io('http://iaminjapan.com:3000'); ////////// LIVE
}
socket.emit('userInfo', data2);  

$(document).ready(function () {
    var map;
    var mapOptions = {
        zoom: 1,
        center: new google.maps.LatLng(0, 0),
        mapTypeId: 'terrain',
        minZoom: 1
    };
    map = new google.maps.Map($('#fremap')[0], mapOptions);
    var markers = [<?php echo $connections_city_array;?>];
    var infoWindowContent = [<?php echo $connections_details;?>];
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    
    for(i = 0; i < markers.length; i++)
    {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        marker = new google.maps.Marker({
            position: position,
            map: map
        });
        google.maps.event.addListener(marker, 'mouseover', (function(marker, i)
        {
            return function()
            {
                infoWindow.setContent(infoWindowContent[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));
    }

    justifiedGalleryinitialize();
	lightGalleryinitialize();
}); 

</script>