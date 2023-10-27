<?php
use yii\helpers\Url; 
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use frontend\models\LoginForm;
use frontend\models\Like;
use frontend\models\Page;
use frontend\models\PostForm;
use frontend\models\UserForm;
use frontend\models\Notification;
use yii\helpers\ArrayHelper;
use frontend\models\PinImage;
use frontend\models\BusinessCategory;
use frontend\models\PageVisitor;  
use frontend\models\PageEndorse;
use frontend\models\PageRoles;
use frontend\models\UserPhotos;
use frontend\models\ReportPost;
use frontend\models\BlockConnect;
use frontend\models\Connect; 
use backend\models\Googlekey;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;                      
$request = Yii::$app->request;
$email = $session->get('email'); 
$user_id = (string)$session->get('user_id');  

$page_id = (string)$request->get('id');   
$Auth = '';
if(isset($user_id) && $user_id != '') {
	$authstatus = UserForm::isUserExistByUid($user_id);
	if($authstatus == 'checkuserauthclassg' || $authstatus == 'checkuserauthclassnv') {
		$Auth = $authstatus;
	}
} else {
	$Auth = 'checkuserauthclassg';
}

$pagerole = PageRoles::pageRole($user_id,$page_id);
$like_count = Like::getLikeCount($page_id);
$pagereviewscount = Page::getPageReviewsCount($page_id);
$pagelastreviews = Page::getLastThreePageReviews($page_id);
$page_details = Page::Pagedetails($page_id);
$page_city = $page_details['city'];
$page_owner = $page_details['created_by'];
$pageuserdetails = Page::getPageLikeDetails($page_id);
$pageendorsecount = PageEndorse::getAllEndorseCount($page_id);
$likeexist = Like::getPageLike($page_id);
if($likeexist){$likestatus = 'Liked';}
else{$likestatus = 'Like';}
$invitedconnect = Page::getConnectList($page_id);  
$buscatmodel = new BusinessCategory();
$this->title = 'Page';
$page_img = $this->context->getpageimage($page_id);

if(isset($user_basicinfo['cover_photo']) && !empty($user_basicinfo['cover_photo'])) {
    $cover_photo = "uploads/cover/".$user_basicinfo['cover_photo'];
} else {
    $cover_photo = $baseUrl."/images/wallbanner.jpg";
} 

$total_pictures = UserPhotos::getPagepics($page_id);
$profile_albums = UserPhotos::getProfilePics($page_id);
$total_profile_albums = count($profile_albums);
$cover_albums = PostForm::getCoverPics($page_id);
$cover_albums2 = UserPhotos::getCoverPics($page_id);
$total_cover_albums = count($cover_albums+$cover_albums2);
$totalcounts = $total_pictures+$total_profile_albums+$total_cover_albums;  
$photos = UserPhotos::getPagePostPhotos($page_id);
$visitorcount = PageVisitor::getAllPageVisitorsCount($page_id);
$mn = '';
$pro = '';
$cntrr = '';

for($x=3; $x>=0; $x--) {
    $month = date('M', strtotime(date('Y-m')." -" . $x . " month"));
    $year = date('Y', strtotime(date('Y-m')." -" . $x . " month"));
    $mn .= "'$month',";
    $visitor = PageVisitor::find()->where(['page_id' => "$page_id",'year' => $year,'month' => $month])->all();
    if($visitor) {
        $cnt = count($visitor);
        $cntrr += $cnt; 
    } else {
        $cnt = 0;
    }
    $pro .= "'$cnt',";
}

$valmonths = substr($mn,0,-1);
$provistit = substr($pro,0,-1);
$profilecomplete = 40;  
if(isset($page_details['short_desc']) && !empty($page_details['short_desc'])) {
    $profilecomplete += 10;
}
if(isset($page_details['city']) && !empty($page_details['city'])) {
    $profilecomplete += 10;
}
if(isset($page_details['email']) && !empty($page_details['email'])) {
    $profilecomplete += 10;
}
if(isset($page_details['site']) && !empty($page_details['site'])) {
    $profilecomplete += 10;
}
if(isset($user_basicinfo['thumbnail']) && !empty($user_basicinfo['thumbnail'])) {
    $profilecomplete += 10;
}
if(isset($user_basicinfo['cover_photo']) && !empty($user_basicinfo['cover_photo'])) {
    $profilecomplete += 10;  
}
if($profilecomplete == 40){$protext = 'Good';}
if($profilecomplete == 50 || $profilecomplete == 60){$protext = 'Better';}
if($profilecomplete == 70 || $profilecomplete == 80 || $profilecomplete == 90){$protext = 'Strong';}
if($profilecomplete == 100){$protext = 'Best';}

$lastalbum = UserPhotos::find()->where(['post_user_id' => $user_id,'is_deleted' => '0','is_album' => '1'])->orderBy(['post_created_date'=>SORT_DESC])->one();
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<link href="<?=$baseUrl?>/css/custom-croppie.css" rel="stylesheet">
<style type="text/css">
	.wall-header .header-strip .tabs {
	    margin-left: 10px;
	    float: left;
	}

	.tabs {
	    border: none;
	}

	.wall-header .header-strip .tabs li {
	    margin-left: 25px;
	}


	.tabs > li {
	    display: inline-block;
	}
</style>
	<div class="page-wrapper  wallpage businesspage subpage-wrapper hidemenu-wrapper noopened-search menutransheader-wrapper transheadereffect show-sidebar">
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
		<div class="fixed-layout ipad-mfix <?php if($page_details['created_by'] != $user_id) { ?>hide-addflow<?php } ?>">
			<div class="wallpage-content">			
				<div class="wall-left-section">
					<?php include('headerwall.php');
					if(isset($page_details['short_desc']) && !empty($page_details['short_desc'])) {
						$busdes = $editbusdes = $page_details['short_desc'];
					} else {
						$busdes = 'Not added';
						$editbusdes = '';
					}

					if(isset($page_details['email']) && !empty($page_details['email'])) {
						$busemail = $editbusemail = $page_details['email'];
						$linktext = 'mailto:'.$busemail;
					} else {
						$busemail = 'Not added';
						$linktext = 'javascript:void(0)';
						$editbusemail = '';
					}
					if(isset($page_details['site']) && !empty($page_details['site'])) {
						$bussite = $link = $editbussite = $page_details['site'];
						if(substr( $link, 0, 4 ) === "http") {
							$link = $link;
						} else {      
							$link = 'http://'.$link;
						}
					} else {
						$bussite = 'Not added';
						$link = 'javascript:void(0)';
						$editbussite = '';
					}
					
					if(isset($page_details['address']) && !empty($page_details['address'])) {
						$edit_page_address = $page_address = $page_details['address'];
					} else {
						$page_address = 'Not added';
						$edit_page_address = '';
					}
					if(isset($page_details['postal_code']) && !empty($page_details['postal_code'])) {
						$editpostalcode = $postalcode = $page_details['postal_code'];
					} else {
						$postalcode = 'Not added';
						$editpostalcode = '';
					}
					if(isset($page_details['phone']) && !empty($page_details['phone'])) {
						$editbusphone = $busphone = $page_details['phone'];
					} else {
						$busphone = 'Not added';
						$editbusphone = 'Not added';
					}
					?>
	<div class="main-content gone">
		<div class="tab-content main-tab-content">
			<div id="wall-content" class="tab-pane main-tabpane fade wall-content active in" tabname="Wall">
				<?php if(isset($_GET['type']) && !empty($_GET['type'])) { ?>
				<div class="wallcontent-column">
					<div class="post-column">
						<center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
					</div>
				</div>	
				<?php } 
				else { ?>
				<div class="wallcontent-column">
					<div class="wall-info swapping-parent">
						<div class="content-box bshadow">
							<div class="cbox-title"><span class="directeditmode">
								<i class="mdi mdi-account"></i>
								<a href="javascript:void(0)" onclick="openAboutSection('normal')">
									About										
								</a>
								</span>
							</div>
							<div class="cbox-desc">
								<div class="normal-mode">
									<div class="info-row">
										<div class="icon-holder">
											<span class="directedit	mode"><i class="mdi mdi-briefcase"></i></span>
										</div>
										<div class="desc-holder"><span class="directeditmode">
											<span class="darktext">Business type</span>
											<span class="buscat"><?=$page_details['category']?></span></span>
										</div>
									</div>		 				
									<div class="info-row">
										<div class="icon-holder">
											<span class="directeditmode"><i class="mdi mdi-rss"></i></span>
										</div>
										<div class="desc-holder">
											<div class="para-section">
												<div class="para"><span class="directeditmode">
												<span class="darktext">Short description</span> <span class="about"><?=$editbusdes?></span></span></div>
											</div>
										</div>
									</div>						
									<div class="info-row">
										<div class="icon-holder">
											<span class="directeditmode"><i class="mdi mdi-email"></i></span>
										</div>
										<div class="desc-holder"><span class="directeditmode">
											<span class="darktext">Email address</span>
											<a href="<?=$linktext?>" target="_blank" class="maillink"><span class="email"><?=$busemail?></span></a></span>
										</div>
									</div>
									<div class="info-row">
										<div class="icon-holder">
											<span class="directeditmode"><i class="mdi mdi-earth"></i></span>
										</div>
										<div class="desc-holder"><span class="directeditmode">
											<span class="darktext">Website</span>
											<a href="<?=$link?>" target="_blank" class="sitelink"><span class="website"><?=$bussite?></span></a></span>
										</div>
									</div>
									<div class="info-row">
										<div class="icon-holder">
											<span class="directeditmode"><i class="zmdi zmdi-pin"></i></span>
										</div>
										<div class="desc-holder"><span class="directeditmode">
											<a <?php if($pagecity != 'City not added'){ ?>href="https://www.google.co.in/maps/place/<?=$pagecity?>" target="_blank"<?php } ?>>Google it for location</a></span>
										</div>
									</div>
									<?php if($page_details['created_by'] != $user_id && isset($page_details['bsnesbtn'])){ ?>
									<div class="info-row action-btn">
										<?php if($page_details['bsnesbtn'] == 'Call Now' || $page_details['bsnesbtn'] == 'Contact Us'){ ?>
									  <div class="businessbtn"><a href="javascript:void(0)" class="simple-tooltip btn btn-primary waves-effect btn-md fullwidth" title="<?=$page_details['bsnesbtnvalue']?>"><?=$page_details['bsnesbtn']?></a></div>
									  <?php } else if($page_details['bsnesbtn'] == 'Send Email' || $page_details['bsnesbtn'] == 'Send Message'){ ?>
									  <div class="businessbtn"><a href="mailto:<?=$page_details['bsnesbtnvalue']?>" class="btn btn-primary waves-effect btn-md fullwidth" target="_blank"><?=$page_details['bsnesbtn']?></a></div>
									  <?php } else {
											$link = $page_details['bsnesbtnvalue'];
											if(substr( $link, 0, 4 ) === "http")
											{
												$link = $link;
											} 
											else
											{
												$link = 'http://'.$link;
											}
											$page_details['bsnesbtnvalue'] = $link;
										?>
									  <div class="businessbtn"><a href="<?=$page_details['bsnesbtnvalue']?>" class="btn btn-primary waves-effect btn-md fullwidth" target="_blank"><?=$page_details['bsnesbtn']?></a></div>
									  <?php } ?>
									</div>
									<?php } ?>
									
									<?php if($page_details['created_by'] == $user_id){
										if(isset($page_details['bsnesbtn']) && !empty($page_details['bsnesbtn']))
										{
											$bscl = '';
											$bslbl = $page_details['bsnesbtn'];
											$bsval = $page_details['bsnesbtnvalue'];
											$bscnt = '';
											if($page_details['bsnesbtn'] == 'Call Now' || $page_details['bsnesbtn'] == 'Contact Us'){
												$bscnt = '<a href="javascript:void(0)" class="simple-tooltip" title="'.$bsval.'">'.$bslbl.'</a>';
											} else if($page_details['bsnesbtn'] == 'Send Email' || $page_details['bsnesbtn'] == 'Send Message'){
												$bscnt = '<a href="mailto:'.$bsval.'" class="simple-tooltip" target="_blank">'.$bslbl.'</a>';
											} else {
											$link = $page_details['bsnesbtnvalue'];
											if(substr( $link, 0, 4 ) === "http")
											{
												$link = $link;
											}
											else
											{
												$link = 'http://'.$link;
											}
											$bscnt = '<a href="'.$link.'" class="simple-tooltip" target="_blank">'.$bslbl.'</a>';
											}
										}
										else
										{
											$bslbl = 'Contact Us';
											$bscnt = '<div class="hidden-businessbtn"><a href="javascript:void(0)" class="simple-tooltip" title="232-232-1232">Contact Us</a></div>';
										}
									?>  
									<div class="info-row action-btn">											
										<div class="dropdown dropdown-custom leftDrop resist">
										  <div class="hidden-businessbtn"><?=$bscnt?></div>
										  <a  data-activates="main_business11212" href="javascript:void(0)" class="dropdown-toggle btn btn-primary waves-effect btn-md fullwidth businessbtn-caption" role="button" aria-haspopup="true" aria-expanded="false"><?=$bslbl?></a>
										  <ul id="main_business11212" class="dropdown-content custom_dropdown">
											<li><a href="javascript:void(0)" onclick="testBusinessButton()">Test button</a></li>
											<li><a href="javascript:void(0)" class="edit_button_business" data-pageid='<?=$page_id?>'>Edit button</a></li>
											<li><a href="javascript:void(0)" onclick="deleteBusinessButton()">Delete button</a></li>
										  </ul>
										</div>
									</div>
									<?php } ?>
								</div>	
							</div>
						</div>
					</div>
					<div class="content-box bshadow">
						<div class="cbox-title">
							<i class="mdi mdi-file-image"></i>
							<a href="javascript:void(0)" class="pagephotos" onclick="openWallTabInternally('photos-content')">
								Photos
								<span class="suminfo"><?=$total_pictures?> Photos</span>
							</a>
						</div>
						<div class="cbox-desc">
							<?php if($total_pictures>0){ ?>
							<div class="photo-list grid-list">
								<div class="row">
										<?php $ctr = 1;
											foreach($photos as $post){
											if(isset($post['image']) && !empty($post['image'])){
											$eximgs = explode(',',$post['image'],-1);
											foreach ($eximgs as $eximg) {

											if($ctr < 7){

											$picsize = '';
											$imgclass = '';

											$inameclass = '';
											$pinval = '';
											if(file_exists('../web'.$eximg)) {
											$val = getimagesize('../web'.$eximg);
											$iname = $this->context->getimagename($eximg);
											$inameclass = $this->context->getimagefilename($eximg);
											$pinit = PinImage::find()->where(['user_id' => "$user_id",'imagename' => $iname,'is_saved' => '1'])->one();
											if($pinit){ $pinval = 'pin';} else {$pinval = 'unpin';}
											$picsize .= $val[0] .'x'. $val[1] .', ';
											if($val[0] > $val[1]){$imgclass = 'himg';}else if($val[1] > $val[0]){$imgclass = 'vimg';}else{$imgclass = 'himg';}
											}
											?>
											<div class="grid-box">
												<div class="photo-box <?= $imgclass?>-box">
													<a href="javascript:void(0)"><img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>" class="<?=$imgclass?>"/></a>
													<?php /*
													<a href="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-imgid="<?=$inameclass?>" data-size="1600x1600"  data-med="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" data-med-size="1024x1024" data-author="Folkert Gorter" data-pinit="<?=$pinval?>" class="imgpin pimg-holder">
														<img src="<?= Yii::$app->getUrlManager()->getBaseUrl() ?><?= $eximg ?>" class="<?= $imgclass?>"/>
													</a> */ ?>
												</div>							
											</div>

											<?php } $ctr++; } } } ?>
								</div>
							</div>
							<?php } else { ?>
							<?php $this->context->getnolistfound('nophotoadded'); ?>
							<?php } ?>
						</div>
					</div>
					<div class="content-box bshadow">
						<div class="cbox-title">
							<i class="zmdi zmdi-thumb-up"></i>
							<a class="pagelike" onclick="openWallTabInternally('likes-content')">
								Likes
								<span class="suminfo"><?=$like_count?> Like<?php if($like_count > 1){?>s<?php } ?></span>
							</a>                                   
						</div>
						<div class="cbox-desc">
							<div class="likes-summery">
								<div class="friend-likes connect-likes">
									<h5><a href="javascript:void(0)"><?=$like_count?> User<?php if($like_count > 1){?>s<?php } ?></a> liked <?=$page_details['page_name']?></h5>
									<?php if($like_count > 0){ ?>
									<ul>
										<?php foreach($pageuserdetails as $pageuserdetail){
											$like_user_id = (string)$pageuserdetail['user']['_id'];
											$user_img = $this->context->getimage($like_user_id,'thumb');
											$link = Url::to(['userwall/index', 'id' => $like_user_id]);
										?>
										<li><a href="<?=$link?>" title="<?=$pageuserdetail['user']['fullname']?>"><img src="<?=$user_img?>"/></a></li>
										<?php } ?>
									</ul>
										<?php } else { ?>
										<?php $this->context->getnolistfound('becomefirsttolikepage'); ?>
										<?php } ?>
								</div>
								<div class="invite-likes"> 
								<?php 
									if(count($invitedconnect) > 0){
								?>
									<p>Invite your friends to like this page<a href="javascript:void(0)">See All</a></p>
									<?php } ?>
									<div class="invite-holder">
									<?php 
										if(count($invitedconnect) > 0){
									?>
										<form>
											<div class="tholder">
												<div class="sliding-middle-custom anim-area underlined">
													<input type="text" placeholder="Type a friend's name" class="invite_connect_search" data-id="invite_connect_search"/>
													<a href="javascript:void(0)" onclick="removeinvitesearchinput(this);"><img src="<?=$baseUrl?>/images/cross-icon.png"/></a>
												</div>
											</div>
										</form>
										<?php } ?>
										<div class="list-holder blockinvite_connect_search">
											<ul>
												<?php 
												if(count($invitedconnect) > 0){
													foreach($invitedconnect as $invitedconnections){
													$connectid = (string)$invitedconnections['to_id'];
													$result = LoginForm::find()->where(['_id' => $connectid])->one();
													$frndimg = $this->context->getimage($connectid,'thumb');
													$pagelikeexist = Like::find()->where(['post_id' => "$page_id", 'user_id' => "$connectid", 'status' => '1', 'like_type' => 'page'])->all();
													$invitaionsent = Notification::find()->where(['post_id' => "$page_id", 'status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id", 'notification_type' => 'pageinvite'])->one();
												?>
												<li class="invite_<?=$connectid?>">
												<div class="invitelike-friend invitelike-connect">
													<div class="imgholder"><img src="<?=$frndimg?>"/></div>
													<div class="descholder">
														<h6><?=$result['fullname']?></h6>
														<div class="btn-holder events_<?=$connectid?>">
														<?php if($pagelikeexist)
														{
														echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Liked</label>';
														}
														else if($invitaionsent)
														{
														echo '<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>';
														}
														else
														{ ?>
														<a href="javascript:void(0)" onclick="sendinvite('<?=$connectid?>','<?=$page_id?>')" class="btn-invite">Invite</a>
														<a href="javascript:void(0)" onclick="cancelinvite('<?=$connectid?>')" class="btn-invite-close"><i class="mdi mdi-close"></i></a>
														<?php } ?>
														</div>
														<div class="dis-none btn-holder sendinvitation_<?=$connectid?>">
															<label class="infolabel"><i class="zmdi zmdi-check"></i> Invitation sent</label>
														</div>
													</div>														
												</div>
												</li>
												<?php } } else { ?>
												<?php $this->context->getnolistfound('allconnectionslikepage'); ?>
												<?php } ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php if($page_details['gen_reviews'] == 'on'){ ?>
					<div class="content-box bshadow"> 
						<div class="cbox-title">
							<i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>
							<a class="pagereviews" onclick="openWallTabInternally('reviews-content')">
							Reviews
							<span class="suminfo"><?=$pagereviewscount?> Review<?php if($pagereviewscount > 1){?>s<?php } ?></span>
							</a>
						</div>
						<div class="cbox-desc">
							<div class="reviews-summery">
								<div class="reviews-add">
									<div class="stars-holder">
										<?php
										$totalpagereviewscount = $pagereviewscount = Page::getPageReviewsCount($page_id);
										$sumpagereviewscount = Page::getPageReviewsSum($page_id);
										if($totalpagereviewscount == 0) { $totalpagereviewscount = 1; }
										$avgreview = $sumpagereviewscount / $totalpagereviewscount;
										$avn = number_format($avgreview);
										?>
										<?php for($i=0;$i<5;$i++){
										if($i < $avn){$status = 'filled'; }
										else{$status = 'blank'; }
										?>
										<img src="<?=$baseUrl?>/images/<?=$status?>-star.png"/>
										<?php } ?>
										<div class="dropdown dropdown-custom dropdown-friendlock no-sword setDropVal">
										  <a class="dropdown-button more_btn" href="javascript:void(0);" data-activates="pub_drop">
											<span class="glyphicon glyphicon-globe"></span><span class="sword">Public</span> <span class="caret"></span>
										  </a>
											<ul id="pub_drop" class="dropdown-content custom_dropdown">
												<li><a href="javascript:void(0)"><span class="mdi mdi-lock"></span><span class="sword">Private</span></a></li>
												<li><a href="javascript:void(0)"><span class="mdi mdi-account"></span><span class="sword">Friends</span></a></li>
												<li><a href="javascript:void(0)"><span class="mdi mdi-account-multiple"></span><span class="sword">Friend of friend</span></a></li>
												<li><a href="javascript:void(0)"><span class="mdi mdi-settings"></span><span class="sword">Custom</span></a></li>
												<li><a href="javascript:void(0)"><span class="mdi mdi-earth"></span><span class="sword">Public</span></a></li>
											</ul>
										</div>
									</div>
									<p>What do you think about this page?</p>
								</div>
									<?php if($pagereviewscount > 0) { ?>
								<div class="reviews-people">    
									<ul>
										<?php foreach($pagelastreviews as $pagelastreview){ 
										$reviewimg = $this->context->getimage($pagelastreview['post_user_id'],'thumb');
										$fullname = $this->context->getuserdata($pagelastreview['post_user_id'],'fullname');
										$time = Yii::$app->EphocTime->time_elapsed_A(time(),$pagelastreview['post_created_date']);
										?>
										<li>
										<div class="reviewpeople-box">
											<div class="imgholder"><img src="<?=$reviewimg?>"/></div>
											<div class="descholder">
												<h6> <?=$fullname?><span><?=$time?></span></h6>
												<div class="stars-holder">
												<?php for($i=0;$i<5;$i++){
												if($i < $pagelastreview['rating']){$status = 'filled'; }
												else{$status = 'blank'; }
												?>
												<img src="<?=$baseUrl?>/images/<?=$status?>-star.png"/>
												<?php } ?>
												<?php if($pagelastreview['post_text'] == null) { $pagelastreview['post_text'] = 'Not added'; } ?>
												<p><?=$pagelastreview['post_text']?></p>
												</div>
											</div>
										</div>
										</li>
										<?php } ?>
									</ul> 
								</div>
								<?php } else { ?>
								<?php $this->context->getnolistfound('becomefirsttoreview'); ?>
								<?php } ?>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>
				<div class="post-column">
					<?php if(($page_details['created_by'] == $user_id) || ($page_details['gen_post'] == 'allowPost')){ ?>
						<div class="new-post base-newpost">
							<form action="">
								<div class="npost-content">
									<div class="post-mcontent">
										<i class="mdi mdi-pencil-box-outline main-icon"></i>
											<div class="desc">
											<div class="input-field comments_box">
												<input placeholder="What's new?" class="validate commentmodalAction_form" type="text">							
											</div>
											</div>
									</div>
								</div>				
							</form>
							<div class="overlay" id="composetoolboxAction"></div>
						</div>
					<?php } ?>
					<input type="hidden" name="pagename" id="pagename" value="page" />
					<input type="hidden" name="tlid" id="tlid" value="<?=$page_id?>" />
					<input type="hidden" name="baseurl" id="baseurl" value="<?=$baseUrl?>" />
					<div class="post-list margint15">
					<div class="row">
					 <?php
					$lp = 1; 
					foreach($posts as $post)
					{ 
						$existing_posts = '1';
						$cls = '';
						if(count($posts)==$lp) {
						  $cls = 'lazyloadscroll'; 
						}

						$postid = (string)$post['_id'];
						$postownerid = (string)$post['post_user_id'];
						$postprivacy = $post['post_privacy'];

						$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
						if($isOk == 'ok2389Ko') {
							if(($lp%8) == 0) {
								$ads = $this->context->getad(true); 
								if(isset($ads) && !empty($ads))
								{
									$ad_id = (string) $ads['_id'];	
									$this->context->display_last_post($ad_id, $existing_posts, '', $cls);
									$lp++;
								} else {
									$lp++;
								}
							} else {
								$this->context->display_last_post((string)$postid, $existing_posts, '', $cls);
								$lp++;	
							}						
						}
					}
					?>
					</div>	
					</div>
					<div class="clear"></div>
					<?php if($lp <= 1) {
						$this->context->getwelcomebox("page");
					} ?>
					<center><div class="lds-css ng-scope dis-none"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
				</div>
				<?php } ?>
			</div>

			<div id="about-content" class="tab-pane main-tabpane outer-tab fade about-content" tabname="About">
				<div class="combined-column">
					<div class="content-box bshadow ">
						<div class="cbox-title nborder">
							<i class="mdi mdi-account mdi-16px"></i>   
							About
						</div>
						<div class="cbox-desc">
							<div class="about-summary">									
								<div class="row">
									<div class="col m8 l8 summeryinfo">
										<div class="section-title">Summary</div>
										<div class="row">
											<div class="col l6 summerybox">
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-file-outline"></i></div><div class="desc-holder">Page of <span class="darktext busname"><?=$page_details['page_name']?></span></div></div>
												<div class="info-row"><div class="icon-holder"><i class="zmdi zmdi-pin"></i></div><div class="desc-holder">Location <span class="darktext"><?=$pagecity?></span></div></div>
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-phone"></i></div><div class="desc-holder">Phone <span class="darktext busphone"><?=$busphone?></span></div></div>
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-clock-outline"></i></div><div class="desc-holder">Page created <span class="darktext"><?=date("F d, Y", $page_details['created_date'])?></span></div></div>
											</div>
											<div class="col l6 summerybox"> 
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-briefcase"></i></div><div class="desc-holder">Business Type <span class="darktext buscat"><?=$page_details['category']?></span></div></div>
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-earth"></i></div><div class="desc-holder">Website <span class="darktext website"><?=$bussite?></span></div></div>
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-email"></i></div><div class="desc-holder">Email Address <span class="darktext email"><?=$busemail?></span></div></div>
												<div class="info-row"><div class="icon-holder"><i class="mdi mdi-percent"></i></div><div class="desc-holder">Profile <span class="darktext"><?=$profilecomplete?>% Completed</span></div></div>
											</div>
										</div>
									</div>  
									<div class="col m4 l4 summerystatus">
										<div class="highlightInfo">
											<ul>
												<li class="success">
													<span class="pull-left">Likes</span>	
													<span class="pull-right"><?=$like_count?></span>
													<div class="clear"></div>
												</li>
												<li class="pendding">
													<span class="pull-left">Views</span>	
													<span class="pull-right"><?=$visitorcount?></span>
													<div class="clear"></div>
												</li>
												<li class="success">
													<span class="pull-left">Reviews</span>	
													<span class="pull-right"><?=$pagereviewscount?></span>
													<div class="clear"></div>
												</li>
											</ul>
										</div>   
									</div>
								</div>
							</div>
							<div class="clear"></div>
							<div class="editable-summery mode-holder">
								<div class="normal-mode">
									<div class="about-personal">
										<div class="section-title">Page information
											<?php if($page_details['created_by'] == $user_id || (!empty($pagerole) && $pagerole!='Supporter')){ ?>
											<a href="javascript:void(0)" onclick="open_detail(this)" class="pull-right editicon waves-effect waves-theme"><i class="zmdi zmdi-edit md-20"></i></a>
											<?php } ?>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Page name</span></div>
												<div class="col l10 m9"><span class="detail busname"><?=$page_details['page_name']?></span></div> 
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Business Type</span></div>
												<div class="col l10 m9"><span class="detail buscat"><?=$page_details['category']?></span></div>
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Services</span></div>
												<div class="col l6 m9"><span class="detail about"><?=$busdes?></span></div>
											</div>
										</div>
										<div class="section-title mt30">Page contact information</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Address</span></div>
												<div class="col l8 m9 s12"><span class="detail busaddress"><?=$page_address?></span></div>
											</div>
										</div>											
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">City</span></div>
												<div class="col l8 m9 s12"><span class="detail bustown"><?=$pagecity?></span></div>
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Postal code</span></div>
												<div class="col l8 m9 s12"><span class="detail buscode"><?=$postalcode?></span></div>
											</div>
										</div>										
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Email Address</span></div>
												<div class="col l8 m9 s12"><span class="detail email"><?=$busemail?></span></div>
											</div>
										</div>											
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Website</span></div>
												<div class="col l8 m9 s12"><span class="detail website"><?=$bussite?></span></div> 
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><span class="darktext">Phone</span></div>
												<div class="col l8 m9 s12"><span class="detail busphone"><?=$busphone?></span></div>
											</div>
										</div>
									</div>
								</div>
								<?php if($page_details['created_by'] == $user_id || (!empty($pagerole) && $pagerole!='Supporter')){ ?>
								<div class="detail-mode">
									<div class="about-personal">
										<div class="section-title">Page information</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Page name</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined width350">
														<input type="text" class="title" id="buspagename" placeholder="Page name" value="<?=$page_details['page_name']?>"/>
													</div>					
												</div> 
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Business Type</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined fullwidth dropdown782">
														<?php $form = ActiveForm::begin(['options'=>['onsubmit'=>'return false;',],]); ?> 
															<select id="buscat" class="select2 pageservices" name="BusinessCategory[name]" data-fill="y" data-action="pageservices" data-selectore="pageservices" style="width: 100%">
															<option value="" disabled selected>Choose business type</option>
															<?php 
															$xhi = ArrayHelper::map(BusinessCategory::find()->orderBy(['name'=>SORT_ASC])->all(), 'name', 'name');

															foreach ($xhi as $xhii) {
																echo '<option value="'.$xhii.'">'.$xhii.'</option>';
															}
															?>
																
															</select>
													
														<?php ActiveForm::end() ?>
													</div>
												</div>
											</div>       
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Services</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder">
														<textarea class="materialize-textarea materialize-textarea mb0 md_textarea descinput" placeholder="Services that you offer" id="pageshort"><?=$editbusdes?></textarea>
													</div>
												</div>  
											</div>
										</div>
										<div class="section-title mt30">Page contact information</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Address</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined  width350">
														<input type="text" class="fullwidth" placeholder="Address" value="<?=$edit_page_address?>" id="busaddress"/>
													</div>
												</div>
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>City</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined width350">
														<input type="text" class="fullwidth" placeholder="City" value="<?=$pagecity?>" id="autocomplete" data-query="M"  onfocus="filderMapLocationModal(this)" autocomplete='off'/>
														<input type="hidden" readonly="true" name="isd_code" id="isd_code"/>
														<input type="hidden" id="country_code" name="country_code" />
														<input type="hidden" id="country" name="country" />
													</div>
												</div>
											</div>
										</div>                
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Postal Code</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined width350">
														<input type="text" class="fullwidth" placeholder="Postal Code" value="<?=$editpostalcode?>" id="buscode"/>
													</div>
												</div>
											</div>
										</div>										
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Email Address</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined fullwidth">
														<input type="text" class="fullwidth" placeholder="Email Address" value="<?=$busemail?>" id="email"/>
													</div>
												</div>           
											</div>
										</div>
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Website</label></div>
												<div class="col l8 m9 s12">
													<div class="sliding-middle-custom anim-area underlined width350">
														<input type="text" class="title" placeholder="Website URL" value="<?=$editbussite?>" id="website"/>
													</div>
												</div>
											</div>
										</div>   
										<div class="personal-info">
											<div class="row">
												<div class="col l2 m3"><label>Phone</label></div>
												<div class="col l8 m9 s12">
													<div class="row">
														<div class="col l2 m3 s4">		<div class="sliding-middle-custom anim-area underlined fullwidth">
														<input type="text" value="" placeholder="isd code" readonly="true"/>
														</div>
														</div>
														<div class="col l4 m6 s8">
														<div class="sliding-middle-custom anim-area underlined fullwidth">
																<input type="text" class="title" placeholder="Phone" value="<?=$editbusphone?>" id="busphone"/>
															</div>										
														</div>											
													</div>	
												</div>	
											</div>  
										</div>			
										<div class="personal-info fullwidth">
											<div class="pull-right settings-btn">	
												<a class="btngen-center-align waves-effect" onclick="close_detail(this)" tabindex="1">Cancel</a>
												<a class="btngen-center-align waves-effect" onclick="update_page_about(this)" tabindex="1">Save</a>
											</div>
										</div>											
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>  
			
			<div id="settings-content" class="tab-pane main-tabpane outer-tab fade settings-content" tabname="Page Settings">
				<div class="combined-column">
					<div class="content-box bshadow ">
						<div class="cbox-title hidetitle-mbl">
							<i class="zmdi zmdi-settings"></i>
							Page Settings                
						</div>
						<div class="cbox-desc">
							<div class="vertical-tabs tabs-box open-innerpage xmdjkv">
								<div class="tabs-list">
									<ul class="tabs">	     								
										<li class="tab"><a href="#pagesettings-general" pagename="General Settings">General</a></li>
										         
										<li class="tab pagesettings-notifications"><a href="#pagesettings-notifications" pagename="Notifications Settings">Notifications</a></li>
										
										<?php if($page_details['created_by'] == $user_id || (!empty($pagerole) && $pagerole=='Admin')){ ?>
										<li class="tab pagesettings-pageroles"><a href="#pagesettings-pageroles" pagename="Page Admins">Page Admins</a></li>
										<?php } ?>
										
										<li class="tab"><a href="#pagesettings-blocking" pagename="Block List">Block List</a></li>

										<li class="tab pagesettings-activity"><a href="#pagesettings-activity" pagename="Activity Log">Activity Log</a></li>
										
										<li class="tab pagesettings-reviewposts"><a href="#pagesettings-reviewposts" pagename="Review Posts">Review Posts</a></li>
										
										<li class="tab pagesettings-reviewphotos"><a href="#pagesettings-reviewphotos" pagename="Review Photos">Review Photos</a></li>
									</ul>
								</div>
								<div class="tabs-detail gone">
									<div class="tab-content">
										<div class="tab-pane fade active in pagesettings-general" id="pagesettings-general">
											<h4><i class="zmdi zmdi-settings"></i> General
										      <span class="pull-right">
										      <a href="javascript:void(0)" class="editiconCircleEffect editicon1 waves-effect waves-theme" onclick="open_edit_bp_general(this)"><i class="mdi mdi-pencil mdi-22px"></i></a>
										      </span>
										   </h4>
											<div class="content-box">
												<div class="settings-ul normal-part">
													<div class="settings-group editicon2">
											            <div class="normal-mode">
											               <div class="row"> 
											                  <div class="col l12 m12 s12">
											                     <div class="pull-right linkholder">
											                        <a href="javascript:void(0)" class="editiconCircleEffect waves-effect waves-theme" onclick="open_edit_bp_general(this)"><i class="zmdi zmdi-edit mdi-22px"></i></a>
											                     </div>
											                  </div>
											               </div>
											            </div>
											         </div>
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Page status</label>
																</div>
																<div class="col l9 m9 s12">
																	<?php 
																	if(isset($page_details['is_deleted']) && $page_details['is_deleted'] == '1') {
																		$page_publish = 'Page published';	
																	} else {
																		$page_publish = 'Page unpublished';	
																	}
																	?>
																	<span id="gen_publish"><?=$page_publish?></span>
																</div> 
															</div>      
														</div>  
													</div>
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Page posts</label> 
																</div>
																<?php if($page_details['gen_post'] == 'allowPost'){$pagepoststatus = 'Public can add posts to the page';$pagepoststatusvalue = 'checked';$pagepostdenyvalue = '';}
																else{$pagepoststatus = 'Public can\'t add posts to the page';$pagepoststatusvalue = '';$pagepostdenyvalue = 'checked';} ?>
																<div class="col l9 m9 s12">
																	<span id="pagepost_value"><?=$pagepoststatus?></span>
																</div>
															</div>
														</div>
													</div>             
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Page photos</label>
																</div>
																<?php if($page_details['gen_photos'] == 'allowPhotos'){$pagephotosstatus = 'Public can add photos to the page';$pagephotostatusvalue = 'checked';$pagephotodenyvalue = '';}
																else{$pagephotosstatus = 'Public can\'t add photos to the page';$pagephotostatusvalue = '';$pagephotodenyvalue = 'checked';} ?>
																<div class="col l9 m9 s12">
																	<span id="pagephotos_value"><?=$pagephotosstatus?></span>
																</div>
															</div>
														</div>  
													</div>
													<div class="settings-group">
														<div class="normal-mode">					
															<?php
															$pgfltr = isset($page_details['gen_page_filter']) ? $page_details['gen_page_filter'] : '';
															/*$pgfltr = explode(",", $pgfltr);
															$pgfltr = array_filter(array_values($pgfltr));*/

															if($pgfltr != '') {
															    $pgfltrlabel = $pgfltr;
															} else {
															    $pgfltrlabel = 'No words or phrases are blocked by the page';
															}
															?>															
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Page filteration</label>
																</div>
																<div class="col l9 m9 s12">
																	<?=$pgfltrlabel?>
																</div>
															</div>
														</div>
													</div>													
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Download page</label>
																</div>
																<div class="col l9 m9 s12">
																	Download page
																</div>
															</div>
														</div>
													</div>
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Remove page</label>
																</div>
																<div class="col l9 m9 s12">
																	Delete your page
																</div>
															</div>
														</div>
													</div>
												</div>

												<div class="settings-ul edit-part dis-none">
												</div>
											</div>
										</div>
										<div class="tab-pane fade pagesettings-notifications" id="pagesettings-notifications">
										</div>   
										<div class="tab-pane fade pagesettings-pageroles" id="pagesettings-pageroles">
										</div> 
										<div class="tab-pane fade" id="pagesettings-blocking">
											<h4><i class="zmdi zmdi-block"></i> Block List
										      <span class="pull-right">
										      <a href="javascript:void(0)" class="editiconCircleEffect editicon1 waves-effect waves-theme" onclick="open_edit_bp_blocking(this)"><i class="mdi mdi-pencil mdi-22px"></i></a>
										      </span>
										    </h4>
											<div class="content-box">
												<div class="settings-ul normal-part">
													<div class="settings-group editicon2">
											            <div class="normal-mode">
											               <div class="row"> 
											                  <div class="col l12 m12 s12">
											                     <div class="pull-right linkholder">
											                        <a href="javascript:void(0)" class="editiconCircleEffect waves-effect waves-theme" onclick="open_edit_bp_blocking(this)"><i class="zmdi zmdi-edit mdi-22px"></i></a>
											                     </div>
											                  </div>
											               </div>
											            </div>
											         </div>
													<div class="settings-group">
														<div class="normal-mode">
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Restricted List</label>
																</div>
																<div class="col l9 m9 s12">
																	People on this list cannot engage with the page
																</div>
															</div>
														</div>
													</div>
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Blocked List</label>
																</div>
																<div class="col l9 m9 s12">
																	People on this list cannot view the page
																</div>
															</div>
														</div>
													</div> 							
													<div class="settings-group">
														<div class="normal-mode">									
															<div class="row">
																<div class="col l3 m3 s12">
																	<label>Messages filtering</label>
																</div>
																<div class="col l9 m9 s12">
																	People on this list will not be able to send the page any messages
																</div>  
															</div>  
														</div>
													</div>			
												</div>		

												<div class="settings-ul edit-part dis-none">
												</div>										
											</div>
										</div> 
										<div class="tab-pane fade activity-content" id="pagesettings-activity">
										</div>
										<div class="tab-pane fade reviewpost-tab" id="pagesettings-reviewposts">
										</div>
										<div class="tab-pane fade reviewphoto-tab" id="pagesettings-reviewphotos">
										</div>
									</div>
								</div>
							</div>
						
						</div>
					</div>
				</div>
			</div>

			<div id="notifications-content" class="tab-pane main-tabpane outer-tab fade" tabname="Notifications">
					<div class="combined-column">
					</div>
			</div>
			<div id="insights-content" class="tab-pane main-tabpane outer-tab fade insights-content" tabname="Insights">
					<div class="combined-column">
					</div>                                  
			</div>
			 
			<div id="photos-content" class="tab-pane main-tabpane fade photos-content" tabname="Photos">
				<div class="combined-column">
				</div>					
			</div> 

			<div id="likes-content" class="tab-pane main-tabpane outer-tab fade likes-content likes-content-main" tabname="Likes">
				<div class="combined-column">  
				</div>					
			</div> 
 
			<div id="reviews-content" class="tab-pane main-tabpane outer-tab fade reviews-content" tabname="Reviews">
				<div class="combined-column">
				</div>
			</div>
			
			<div id="events-content" class="tab-pane main-tabpane outer-tab fade events-content commevents-page main-page grid-view general-page" tabname="Events">
				<div class="combined-column">
				</div>
			</div>
			
			<div id="endorsement-content" class="tab-pane main-tabpane outer-tab fade endorsement-content" tabname="Endorsement">
				<div class="combined-column">
				</div>
			</div>
		</div>
	</div>
</div>

				<div class="wall-right-section">
					<div class="custom-map">
						<?php $this->context->GetMap($page_city);?>
					</div>
					<div class="custom-wall-links">
						<ul class="tabs">
							<li class="pagewall tab"><a tabname="Wall" href="#wall-content" class="<?php if($page_details['created_by'] != $user_id) { ?>disabled<?php } ?>">Wall</a></li>
														
							<li class="tab"><a tabname="About" href="#about-content">About</a></li>
							
							<li class="pagephotos tab rem-shadow-none"><a tabname="Photos" href="#photos-content">Photos</a></li>
							
							<?php if($page_details['created_by'] == $user_id){ ?>
							<li class="pagenotifications tab"><a tabname="Notifications" href="#notifications-content">Notifications</a></li>
							<?php } ?>
							
							<?php if($page_details['created_by'] == $user_id || $pagerole){ ?>
							<li class="pagepromote tab"><a tabname="Insights" href="#insights-content">Insight</a></li>
							<?php } ?>

							<li class="pagelike tab"><a tabname="Likes" href="#likes-content">Likes</a></li>
							
							<?php if($page_details['gen_reviews'] == 'on' && !strstr($page_details['blk_restrct_list'],(string)$user_id)){?>
							<li class="tab <?php if($page_details['gen_reviews'] == 'on' && !strstr($page_details['blk_restrct_list'],(string)$user_id)){?>pagereviews<?php } else { ?>class=disabled<?php } ?>">
								<a tabname="Reviews" href="#reviews-content" class="<?php if($page_details['created_by'] == $user_id) { ?>disabled<?php } ?>">
									Review
								</a>
							</li>
							<?php } ?>
							
							<?php if(!strstr($page_details['blk_restrct_list'],(string)$user_id)){?>
							<li class="tab <?php if(!strstr($page_details['blk_restrct_list'],(string)$user_id)){?>pageendorse<?php } else { ?>disabled<?php } ?>">
								<a tabname="Endorsement" href="#endorsement-content">
									Endorsement
								</a>
							</li>
							<?php } ?>
						</ul>
						<div class="scontent-column desc-pageinfo">
							<?php include('../views/layouts/mb_page_view.php'); ?>
							<div class="adslistingblock">
							<?php include('../views/layouts/travads.php'); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="new-post-mobile clear">
					<a href="javascript:void(0)" class="popup-window"  id="composetoolboxAction"><i class="mdi mdi-pencil"></i></a>
				</div>
			</div>
		</div>
		<?php include('../views/layouts/footer.php'); ?>
	</div>


<!-- compose tool box modal -->
<div id="compose_tool_box" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post">
	<div class="hidden_header">
		<div class="content_header">
			<button class="close_span cancel_poup waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>
			<p class="modal_header_xs">Write Post</p>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="post_btn action_btn post_btn_xs close_modal waves-effect"  onclick="verify()">Post</a>
		</div>
	</div>
	<div class="modal-content">
		<div class="new-post active">
			<div class="top-stuff">
				<div class="postuser-info">
					<span class="img-holder"><img src="<?=$baseUrl?>/images/demo-profile.jpg"></span>
					<div class="desc-holder">
						<span class="profile_name">Nimish Parekh</span>
						<label id="tag_person"></label>
						<div class="public_dropdown_container">
							<a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="post_privacy_compose1212">
								<span>
									Public
								</span>
								<i class="zmdi zmdi-caret-down"></i>
							</a>
							<ul id="post_privacy_compose1212" class="dropdown-privacy dropdown-content custom_dropdown ">
								<li>
									<a href="javascript:void(0)">
										Private
									</a>
								</li>
								<li>
									<a href="javascript:void(0)">
										Friends
									</a>
								</li>
								<li>
									<a href="javascript:void(0)">
										Public
									</a>
								</li>
								<li>
									<a href="javascript:void(0)">
										Custom
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="settings-icon">
					<a class="dropdown-button "  href="javascript:void(0)" data-activates="newpost_settings">
						<i class="zmdi zmdi-hc-2x zmdi-more"></i>
					</a>
					<ul id="newpost_settings" class="dropdown-content custom_dropdown">
						<li>
							<a href="javascript:void(0)">
								<input type="checkbox" id="toolbox_disable_sharing1111" />
								<label for="toolbox_disable_sharing1111">Disable Sharing</label>
							</a>
						</li>
						<li>
							<a href="javascript:void(0)" class="savepost-link">
								<input type="checkbox" id="toolbox_disable_comments221" />
								<label for="toolbox_disable_comments221">Disable Comments</label>
							</a>
						</li>
						<li>
							<a  onclick="clearPost()">Clear Post</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="npost-content">
				<div class="npost-title title_post_container">									
					<input type="text" class="title" placeholder="Title of this post">									
				</div>
				<div class="clear"></div>									
				<div class="desc">
					<textarea id="new_post_comment" placeholder="What's new?" class="materialize-textarea comment_textarea new_post_comment"></textarea>
				</div>
				
				<div class="post-photos">
					<div class="img-row">
					
					<div class="img-box"></div>
					</div>
				</div>
				<div class="post-tag">
					<div class="areatitle">With</div>
					<div class="areadesc">
						<input type="text" class="ptag" placeholder="Who are you with?"/>
					</div>
				</div>
				<div class="location_parent">
					<label id="selectedlocation"></label>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div class="post-bcontent">
			<div class="footer_icon_container">
				<a href="javascript:void(0)" class="comment_footer_icon waves-effect" id="">
				<input type="file" id="upload_pic" name="upload" class="upload_pic_input upload custom-upload" title="Choose a file to upload" required="" data-class=".main_modal.open .post-photos .img-row" multiple="">
					<i class="zmdi zmdi-hc-lg zmdi-camera"></i>
				</a>
				<button class="comment_footer_icon waves-effect" id="compose_addpersonAction">
					<i class="zmdi zmdi-hc-lg zmdi-account"></i>
				</button>
				<button class="comment_footer_icon waves-effect" data-query="all" onfocus="filderMapLocationModal(this)">
					<i class="zmdi zmdi-hc-lg zmdi-pin"></i>
				</button>
				<button class="comment_footer_icon waves-effect compose_titleAction" id="compose_titleAction">
					<img src="<?=$baseUrl?>/images/addtitleBl.png">
				</button>
			</div>
			<div class="public_dropdown_container_xs">
				<a class="dropdown_text dropdown-button" href="javascript:void(0)" data-activates="post_privacy_compose_xs">
					<span>Public</span>
					<i class="zmdi zmdi-caret-up zmdi-hc-lg"></i>
				</a>
				<ul id="post_privacy_compose_xs" class="dropdown-privacy dropdown-content public_dropdown_xs">
					<li>
						<a href="javascript:void(0)">
						Private
						</a>
					</li>
					<li>
						<a href="javascript:void(0)">
							Friends
						</a>
					</li>
					<li>
						<a href="javascript:void(0)">
							Custom
						</a>
					</li>
					<li>
						<a href="javascript:void(0)">
						Public
						</a>
					</li>
				</ul>
			</div>

			<div class="post-bholder">
				<div class="hidden_xs">
					<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
					<a class="btngen-center-align  close_modal open_discard_modal waves-effect">cancel</a>
					<a type="button" class="btngen-center-align  waves-effect close_modal">Post</a>
				</div>
			</div>
		</div>
	</div>			
</div>
 
<div id="compose_newreview" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post set_re_height compose_newreview_popup"></div>

<!--upload photo modal for compose tool box-->
<?php include('../views/layouts/editphotomadol.php'); ?>

<!--attachment modal--> 
<?php include('../views/layouts/addpersonmodal.php'); ?>

<div id="compose_addpersonAction_as_modal" class="modal modalxii_level1">
	<div class="content_header">
		<button class="close_span waves-effect">
		  <i class="mdi mdi-close mdi-20px	"></i>
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

<div id="add-photo-popup" class="modal addphoto_modal custom_md_modal"></div>
	
<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
	<?php include('../views/layouts/mapmodal.php'); ?>
</div>

<!-- compose tb post modal -->
<div id="compose_tb_post" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose">
	<div class="modal_content_container">
		<div class="modal_content_child modal-content">
			<div class="popup-title ">
				<button class="hidden_close_span close_span waves-effect">
					<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
				</button>			
				<h3>Create new album</h3>
				<a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" >Done</a>
			</div>
		
			<div class="custom_modal_content modal_content" id="createpopup">
				<div class="ablum-yours profile-tab">
					<div class="ablum-box detail-box">															
						<div class="content-holder main-holder">
							<div class="summery">																	
								<div class="dsection bborder expandable-holder expanded">	
									<div class="form-area expandable-area">
										<form class="ablum-form">
											<div class="form-box">
											<div class="fulldiv mobile275">
											<div class="half">
												<div class="frow">
													<div class="caption-holder">
														<label>Album title</label>
													</div>
													<div class="detail-holder">
														<div class="input-field">
															<input type="text" placeholder="Album title" class="fullwidth locinput "/>
														</div>
													</div>
												</div>
												</div>
												</div>
												
												
											<div class="fulldiv mobile275">
											<div class="half">
												<div class="frow">
													<div class="caption-holder">
														<label>Say something about it</label>
													</div>
													<div class="detail-holder">
														<div class="input-field">
															<input type="text" placeholder="Tell us friends about the album" class="fullwidth locinput "/>
														</div>
													</div>
												</div>
												</div>
												</div>
												
											<div class="fulldiv mobile275">
											<div class="half">
												<div class="frow">
													<div class="caption-holder">
														<label>Where was it taken?</label>
													</div>
													<div class="detail-holder">
														<div class="input-field">
															<input type="text" placeholder="Location" class="fullwidth locinput" data-query="M"  onfocus="filderMapLocationModal(this)" id="createlocation"/>
														</div>
													</div>
												</div>
												</div>
												</div>
												
												
												<div class="frow nomargin new-post">
													<div class="caption-holder">
														<label>Add photos to album</label>
													</div>
													<div class="detail-holder">
														<div class="input-field ">					
														<div class="post-photos new_pic_add">
															<div class="img-row">		
																<div class="img-box">
																	<div class="custom-file addimg-box add-photo ablum-add">
																	<span class="icont">+</span><br><span class="">Upload photo</span>
																	<div class="addimg-icon">
																	</div>
																	<input class="upload custom-upload remove-custom-upload" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true" type="file">
																	</div>
																</div>
															</div>
														</div>
										
														</div>
													</div>
												</div>
														
												
												
												
											</div>											
										</form>
									</div>
								
								</div>																
																						
							</div>																
						</div>
					</div>
				
				</div>
			</div>
			
		</div>
	</div>
	<div class="valign-wrapper additem_modal_footer modal-footer">		
		<a href="javascript:void(0)" class="btngen-center-align  close_modal open_discard_modal waves-effect">Cancel</a>
		<a href="javascript:void(0)" class="btngen-center-align waves-effect close_modal">Create</a>
	</div>
</div>

<?php include('../views/layouts/preferences.php'); ?>

<div id="sharepostmodal" class="modal sharepost_modal post-popup main_modal custom_modal">
</div>

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

<div id="moveImageToAlbum" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose"></div>

<div id="edit-album-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose"></div>

<div id="edit_button_business" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose"> 
</div>

<div id="composeeditpostmodal" class="modal compose_tool_box edit_post_modal post-popup main_modal custom_modal compose_edit_modal compose_newreview_popup">
</div>

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
 
<div id="addAlbumContentPopup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose">	
</div>
<?php include('../views/layouts/custom_modal.php'); ?>

<?php
$par = ''; 
if(isset($_GET['par']) && !empty($_GET['par']) && $_GET['par'])
{
	$par = $_GET['par'];
}
?> 
<?php $this->endBody() ?> 
<script type="text/javascript">
    var data1=<?php echo json_encode($usrfrdlist); ?>;
    var pageid = '<?php echo $page_id; ?>';
    var pageowner = '<?php echo $page_details['created_by'];?>';
    var page_owner = '<?php echo $page_details['created_by'];?>';
    var page_details = '<?php echo $page_details['category'];?>';
	var wall_user_id ='<?php echo (string) $page_id; ?>';
	var baseUrl ='<?php echo (string) $baseUrl; ?>';  
    var $par = '<?=$par?>';
	var wall_name = '<?=$page_details['page_name'];?>'; 
</script> 
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/jquery.cropit.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/wall.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/pagewall.js"></script>
<script src="<?=$baseUrl?>/js/croppie.min.js" type="text/javascript"></script>
<script src="<?=$baseUrl?>/js/post.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/travpages.js"></script>
<?php 
if(isset($_GET['type']) && !empty($_GET['type']))
{
	?>
	<script> 
		endorseContent();
	</script>
	<?php 
}
?>