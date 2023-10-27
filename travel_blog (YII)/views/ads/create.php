<?php 
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use frontend\models\LoginForm;
use frontend\models\PageRoles;
use frontend\models\PageEvents;
use frontend\models\Like;
use frontend\models\PageEndorse;
use frontend\models\CountryCode;
use frontend\models\Occupation;
use frontend\models\Interests;
use frontend\models\Language;
use frontend\models\Vip;
use frontend\models\UserMoney;
use frontend\models\EventVisitors;
use backend\models\Googlekey;
  

$session = Yii::$app->session; 
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$rand = rand(999, 99999).time();
$money = UserMoney::usertotalmoney();
$total = (isset($money[0])) ? $money[0]['totalmoney'] : '0';

$server_name = $_SERVER['SERVER_NAME']; 
if($server_name == "localhost"){
	$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=ads/successads";
	$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=ads/successads";
}
else{
	$surl = $server_name . "/frontend/web/index.php?r=ads/successads";
	$furl = $server_name . "/frontend/web/index.php?r=ads/successads";
}

$record = LoginForm::find()->where(['email' => $session->get('email')])->one();
$fname = $record['fname'];  
$lname = $record['lname'];  
$email = $record['email'];  
$city = $record['city']; 

$this->title = 'Ad Manager';

$baseUrl = AppAsset::register($this)->baseUrl;

$adpages = ArrayHelper::map(PageRoles::getAdsPages($user_id), function($data) { return (string)$data['page']['_id'];}, function($data) { return $data['page']['page_name'];} );
$adpages = array_filter($adpages);
if(empty($adpages)){
	$pl = $pe = 0;
	$firstpagename = '';
	$firstpageid = '';
} else {
	$pageid = key($adpages);
	$firstpageid = key($adpages);
	$firstpagename = reset($adpages);
	$pl = Like::getLikeCount($pageid);
	$pe = PageEndorse::getAllEndorseCount($pageid);
}

$totaltravpeople = LoginForm::find()->where(['status' => '1'])->count();

$isvip = Vip::isVIP((string)$user_id);
$impression = $this->context->getadrate($isvip,'impression');
$action = $this->context->getadrate($isvip,'action');
$click = $this->context->getadrate($isvip,'click');

$settings_icon = '<div class="settings-icon"><div class="dropdown dropdown-custom dropdown-med"><a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="Sponsored_1'.$rand.'"><i class="zmdi zmdi-more"></i></a><ul id="Sponsored_1'.$rand.'" class="dropdown-content custom_dropdown"><li class="nicon"><ul class="post-sicon-list"><li class="nicon"><a href="javascript:void(0)">Hide ad</a></li><li class="nicon"><a href="javascript:void(0)">Save ad</a></li><li class="nicon"><a href="javascript:void(0)">Mute this seller ads</a></li><li class="nicon"><a href="javascript:void(0)">Report ad</a></li></ul></li></ul></div></div>';

$directauthcall = '';
if($checkuserauthclass == 'checkuserauthclassg' || $checkuserauthclass == 'checkuserauthclassnv') { 
$directauthcall = $checkuserauthclass . ' directcheckuserauthclass';
}
$today_date = date('d-m-Y');
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<link href="<?=$baseUrl?>/css/jquery-gauge.css" type="text/css" rel="stylesheet">	
<input type="hidden" value="<?=$totaltravpeople?>" id="totlatravusers">
<input type="hidden" value="" id="adobj">
<input type="hidden" value="new" id="pagename">
<input type="hidden" value="" id="adid">
<input type="hidden" value="<?=$today_date?>" id="today_date">
<div class="page-wrapper hidemenu-wrapper adpage full-wrapper noopened-search show-sidebar">
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
		<div class="main-content advertmanager-page">
			<div class="combined-column wide-open main-page full-page">
				<div class="box-container">
					<div class="top-box">
						<div class="pagetitle"><h6>Advert Manager</h6></div>
						<ul class="travad-navs">							
							<li class="active" data-class="step1" data-detailid="step1-detail" id="step1-link"><a href="javascript:void(0)" onclick="navigateAdDetails(this,'topnav', 'top')">Create an advert</a></li>
							<li class="disabled" data-class="step2" data-detailid="step2-detail" id="step2-link"><a href="javascript:void(0)" onclick="navigateAdDetails(this,'topnav', 'top')">Advert Details</a></li>
						</ul>						
					</div>
					<div class="detail-box admanager-detail">
						<div class="inner-box">
							<div class="travadvert-section active" id="step1-detail">
								<div class="mobile-visible">
									<div class="mobi_section">
									<div class="dirinfo backbtn" data-class="step2" data-ndataclass="step1">
										<a href="manage-ad.php"><i class="mdi mdi-arrow-left"></i></a>
									</div><h6>Create Advert</h6>
									</div>
								</div>								
								<div class="fsec">
									<label>Advert Name</label>
									<div class="clear"></div>
									<div class="sliding-middle-custom underlined anim-area maintitle m-t-3">
										<input type="text" class="fullwidth" placeholder="Advert Name" id="advert_name">
									</div>
								</div>
								<div class="fsec remove_btm">
									<label>Advert Objective</label>
									<p>Select an advert that best serve your objective</p>									
								</div>									
								<div class="travad-accordion">
									<!-- page likes -->
									<h3 class="mainhead" id="pagelikes_block">
										<i class="zmdi zmdi-thumb-up"></i>
										<div class="dirinfo right" data-class="step1" data-ndataclass="step2">
											<a href="javascript:void(0)" onclick="navigateAdDetails(this,'insidenav','pagelikes')" dataobj="pagelikes" objname="Page Engagement" class="<?=$directauthcall?>">
												<span class="obj-title">Page Engagement</span>
												<span class="desc-span">Get more people to like, share or comment on your page posts by posting your page in the stream feeds</span>										
												<div class="open-link"><i class="mdi mdi-menu-right"></i></div>
											</a>
										</div>
									</h3>
									<!-- brand awareness -->
									<h3 class="mainhead" id="brandawareness_block">
										<i class="mdi mdi-tag"></i>
										<div class="dirinfo right" data-class="step1" data-ndataclass="step2">
											<a href="javascript:void(0)" class="right <?=$directauthcall?>" onclick="navigateAdDetails(this,'insidenav','brandawareness')" dataobj="brandawareness" objname="Brand Awareness">
												<span class="obj-title">Brand Awareness</span>
												<span class="desc-span">Place your business name or product in front of many people and increase your brand name or product exposure</span>
												<div class="open-link"><i class="mdi mdi-menu-right"></i></div>
											</a>
										</div>
									</h3>
									<!-- website leads -->
									<h3 class="mainhead" id="websiteleads_block">
										<i class="mdi mdi-link-off"></i>
										<div class="dirinfo right" data-class="step1" data-ndataclass="step2">
											<a href="javascript:void(0)" class="right <?=$directauthcall?>" onclick="navigateAdDetails(this,'insidenav','websiteleads')" dataobj="websiteleads" objname="Website Leads">
												<span class="obj-title">Website Leads</span>
												<span class="desc-span">Send interested people to your websites and increase your website visitors</span>
												<div class="open-link"><i class="mdi mdi-menu-right"></i></div>
											</a>
										</div>										
									</h3>
									<!-- Website Conversion -->
									<h3 class="mainhead" id="websiteconversion_block">
										<i class="mdi mdi-earth"></i>
										<div class="dirinfo right" data-class="step1" data-ndataclass="step2">
											<a href="javascript:void(0)" class="right <?=$directauthcall?>" onclick="navigateAdDetails(this,'insidenav','websiteconversion')" dataobj="websiteconversion" objname="Website Conversion">
												<span class="obj-title">Website Conversion</span>
												<span class="desc-span">Get people to take an action on your ad by including the action button on your ad</span>
												<div class="open-link"><i class="mdi mdi-menu-right"></i></div>
											</a>
										</div>										
									</h3>
									<!-- inbox highlight -->
									<h3 class="mainhead" id="inboxhighlight_block">
										<i class="mdi mdi-email"></i>
										<div class="dirinfo right <?=$directauthcall?>" data-class="step1" data-ndataclass="step2">
											<a href="javascript:void(0)" class="right" onclick="navigateAdDetails(this,'insidenav','inboxhighlight')" dataobj="inboxhighlight" objname="Inbox Highlight">
												<span class="obj-title">Inbox Highlight</span>
												<span class="desc-span">Site members will get notification of your ad in their inbox</span>
												<div class="open-link"><i class="mdi mdi-menu-right"></i></div>
											</a>
										</div>										
									</h3>
									<!-- page endorsement -->									
									<h3 class="mainhead" id="pageendorse_block">
										<i class="mdi mdi-flag-variant"></i>
										<div class="dirinfo right" data-class="step1" data-ndataclass="step2">
											<a href="javascript:void(0)" class="right <?=$directauthcall?>" onclick="navigateAdDetails(this,'insidenav','pageendorse')" dataobj="pageendorse" objname="Page Endorsement">
												<span class="obj-title">Page Endorsement</span>
												<span class="desc-span">Invite people to endorse your page,  and gain more page publicity</span>
												<div class="open-link"><i class="mdi mdi-menu-right"></i></div>
											</a>
										</div>										
									</h3>	
								</div>
							</div>
							<?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
							<div class="travadvert-section" id="step2-detail">
								<div class="travad-accordion travad-details">
									<div class="mobile-visible">
										<div class="mobi_section">
										<div class="dirinfo backbtn" data-class="step2" data-ndataclass="step1">
											<a href="javascript:void(0)" onclick="navigateAdDetails(this,'insidenav')"><i class="mdi mdi-arrow-left"></i></a>
										</div><h6>Adert Detail</h6>
										</div>
									</div>
									<h3 class="sub-title"></h3>
									<div class="main-accContent">
										<!-- page likes -->			  				     	  	
<div class="travad-detailbox pagelikes">
	<div class="row arrange_row">
		<div class="col l5 m5 s12 detail-part">
			<div class="frow more_m5">
				<label>Select a page to advert</label>
				<div class="sliding-middle-custom anim-area underlined fullwidth select_change enterpagelistingarea">
					<select class="select2 pagenamechange" id="pagelikenames" onchange="pageSelect('pagelikenames','pagelikescount','pagelikesimage','pagelikestext','pl')">
						<?php
						if(empty($adpages)) {
							echo "<option>No Page</option>";
						} else {
							foreach($adpages as $key => $adpage) { 
								echo "<option value='".$key."'>".$adpage."</option>";
							} 
						}
						?>
					</select>
				</div>
			</div>													
			<div class="frow remove_m10">
				<label>Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
				<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th50">
					<textarea maxlength="70" class="materialize-textarea mb0 md_textarea descinput catch_phase" placeholder="Add a few sentences to catch people's attention to your ad" data-length="70" id="pagelikescatch"></textarea>
				</div>
			</div>
			<div class="frow more_m5">
				<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
				<div class="sliding-middle-custom underlined anim-area fullwidth">
					<input type="text" class="fullwidth change_head" placeholder="Write a headline of your ad" onkeyup="adHeader('pagelikesheader')" id="pagelikesheader">
				</div>
			</div>
			<div class="frow">
				<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
				<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
					<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput sub_title_you_ad" placeholder="Short description of your ad" data-length="140" id="pagelikestext"></textarea>
				</div>
			</div>
		</div>
		<div class="col l7 m7 s12 preview-part">
			<div class="frow top-sec">
				<label>Advert Preview</label>
				<div class="settings-icon">
					<div class="dropdown dropdown-custom dropdown-med resist">
						<a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="setting_btn2<?=$rand?>"><i class="zmdi zmdi-more"></i>
						</a>
						<ul id="setting_btn2<?=$rand?>" class="dropdown-content custom_dropdown">
							<li class="dmenu-title">Show advert in</li>
							<li class="divider"></li>
							<li>
								<ul class="echeck-list">
									<li class="selected"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Stream Feeds (Desktop)</a></li>
									<li class="selected"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Stream Feeds (Mobile)</a></li>
									<li class="selected"><a href="javascript:void(0)"><i class="zmdi zmdi-check"></i>Right Column (Desktop)</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="mbl-preview-part">
				<div class="adpreview-holder sfeed-m">
					<div class="post-holder ad-box travad-box page-travad">
						<div class="post-topbar">
							<div class="post-userinfo">
								<div class="img-holder">
									<div id="profiletip-4" class="profiletipholder">
										<span class="profile-tooltip">
											<img class="circle pagelikesimage" src="<?=$this->context->getpageimage($firstpageid)?>">   
										</span>
									</div>
									
								</div>
								<div class="desc-holder">
									<a class="pagelikenames" href="javascript:void(0)"><?=$firstpagename?></a>
									<span class="timestamp">Sponsored Ad</span>
								</div>
							</div> 
							<?=$settings_icon?>
						</div>
						<div class="post-content">							
							<div class="shared-box shared-category">
								<div class="post-holder">									
									<div class="post-content">
										<div class="post-details">
											<p class="pagelikescatch change_ad_word">Add a few sentences to catch people's attention to your ad</p>							
										</div>	
										<div class="post-img-holder">
											<div class="post-img one-img gallery">
												<div class="pimg-holder"><img src="<?=$baseUrl?>/images/pagead.jpg"/></div>
											</div>
										</div>
										<div class="share-summery">											
											<div class="travad-title pagelikesheader">Write a headline of your ad</div>
											<div class="travad-subtitle pagelikestext">Short description of your ad</div>										
											<div class="travad-info"><span class="pagelikescount"><?=$pl?></span> people liked this</div>
											<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light disactive"><i class="zmdi zmdi-thumb-up"></i>Like</a>
										</div>
									</div>																				
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<div class="preview-accordion">
			<ul class="collapsible" data-collapsible="accordion">
				<li>
					<h4 class="collapsible-header">
						Right Column (Desktop)
					</h4>
					<div class="collapsible-body">
					<div>
						<div class="adpreview-holder rcolumn-d travad-box">
							<div class="content-box post-content">
								<div class="cbox-desc post-holder">
									<div class="side-travad page-travad post-content">
										<div class="travad-maintitle"><img class="pagelikesimage" src="<?=$this->context->getpageimage($firstpageid)?>"><h6 class="pagelikenames"><?=$firstpagename?></h6><span>Sponsored</span></div> 
										<div class="post-details">
											<p class="change_ad_word">Add a few sentences to catch people's attention to your ad</p>							
										</div>	
										<div class="post-img-holder adsection_img">
											<div class="post-img one-img pos_rel">
												<div class="crop-holder mian-crop1 image-cropperGeneralRCDsk  ">
											        <div class="cropit-preview"></div>
											        <div class="main-img pimg-holder">
											            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
											        </div> 
											        <div class="main-img1 ">
											            <img id="imageid" draggable="false"/>
											        </div>
											        <div class="new-icon-cam">
											            <div class="btnupload custom_up_load" id="upload_img_action">
											                <div class="fileUpload">
											                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
											                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
											                </div>
											            </div>
											        </div>
											        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
											        <span class="zmdi zmdi-check"></span>
											        </a>
											        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg dis-none">
											        <i class="mdi mdi-close"></i>	
											        </a>
											    </div>
											</div>
										</div>
										<div class="descholder">								
											<div class="travad-title pagelikesheader">Write a headline of your ad</div>
											<div class="travad-subtitle pagelikestext">Short description of your ad</div>
											<div class="travad-info"><span class="pagelikescount"><?=$pl?></span> people liked this</div>										
											<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light disactive"><i class="zmdi zmdi-thumb-up"></i>Like</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>
				</li>
				<li>
					<h4 class="collapsible-header">Stream Feeds (Mobile)</h4>
					<div class="collapsible-body">
					<div>
						<div class="adpreview-holder sfeed-m">
							<div class="post-holder travad-box page-travad">
								<div class="post-topbar">
									<div class="post-userinfo">
										<div class="img-holder">
											<div id="profiletip-4" class="profiletipholder">
												<span class="profile-tooltip">
													<img class="circle pagelikesimage" src="<?=$this->context->getpageimage($firstpageid)?>"/>
												</span>
											</div>
										</div>
										<div class="desc-holder">
											<a href="javascript:void(0)" class="pagelikenames"><?=$firstpagename?></a>
											<span class="timestamp">Sponsored Ad</span>
										</div>
									</div>
									<?=$settings_icon?>
								</div>
								<div class="post-content">							
									<div class="shared-box shared-category">
										<div class="post-holder">				 					
											<div class="post-content">
												<div class="post-details">
													<p class="pagelikescatch change_ad_word">Add a few sentences to catch people's attention to your ad</p>	
													<div class="cropit-preview"></div>					
												</div>	
												<div class="post-img-holder">
													<div class="post-img one-img pos_rel">
														<div class="crop-holder mian-crop1 image-cropperGeneralMbl">
													        <div class="cropit-preview"></div>
													        <div class="main-img pimg-holder">
													            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
													        </div> 
													        <div class="main-img1 ">
													            <img id="imageid" draggable="false"/>
													        </div>
													        <div class="new-icon-cam">
													            <div class="btnupload custom_up_load" id="upload_img_action">
													                <div class="fileUpload">
													                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
													                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
													                </div>
													            </div>
													        </div>
													        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
													        <span class="zmdi zmdi-check"></span>
													        </a>
													        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg dis-none">
													        <i class="mdi mdi-close"></i>	
													        </a>
													    </div>
													</div>
												</div>
												<div class="share-summery">											
													<div class="travad-title pagelikesheader">Write a headline of your ad</div>
													<div class="travad-subtitle pagelikestext">Short description of your ad</div>										
													<div class="travad-info"><span class="pagelikescount"><?=$pl?></span> people liked this</div>
													<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light disactive"><i class="zmdi zmdi-thumb-up"></i>Like</a>
												</div>
											</div>																		
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>
				</li> 
				<li>
					<h4 class="collapsible-header last-item active">Stream Feeds (Desktop)</h4>
					<div class="collapsible-body">
					<div>
						<div class="adpreview-holder sfeed-d">
							<div class="post-holder travad-box page-travad">
								<div class="post-topbar">
									<div class="post-userinfo">
										<div class="img-holder">
											<div id="profiletip-4" class="profiletipholder">
												<span class="profile-tooltip">
													<img class="circle pagelikesimage" src="<?=$this->context->getpageimage($firstpageid)?>"/>
												</span>
											</div>
										</div>
										<div class="desc-holder">
											<a href="javascript:void(0)" class="pagelikenames"><?=$firstpagename?></a>
											<span class="timestamp">Sponsored Ad</span>
										</div>
									</div>
									<?=$settings_icon?>
								</div>
								<div class="post-content">							
									<div class="shared-box shared-category">
										<div class="post-holder">									
											<div class="post-content">
												<div class="post-details">
													<p class="pagelikescatch change_ad_word">Add a few sentences to catch people's attention to your ad</p>
												</div>	
												<div class="post-img-holder">
													<div class="post-img one-img pos_rel"> 
														<div class="crop-holder mian-crop1 image-cropperGeneralDsk ">
													        <div class="cropit-preview"></div>
													        <div class="main-img pimg-holder">
													            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
													        </div> 
													        <div class="main-img1 ">
													            <img id="pagelikesimagedp" draggable="false"/>
													        </div>
													        <div class="new-icon-cam">
													            <div class="btnupload custom_up_load" id="upload_img_action">
													                <div class="fileUpload">
													                    <i class="zmdi zmdi-camera"></i>
													                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
													                </div>
													            </div>
													        </div>
													        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
													        <span class="zmdi zmdi-check"></span>
													        </a>
													        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg dis-none">
													        <i class="mdi mdi-close"></i>	
													        </a>
													    </div>
													</div>
												</div>
												<div class="share-summery">											
													<div class="travad-title pagelikesheader">Write a headline of your ad</div>
													<div class="travad-subtitle pagelikestext">Short description of your ad</div>
													<div class="travad-info"><span class="pagelikescount"><?=$pl?></span> people liked this</div>
													<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light disactive"><i class="zmdi zmdi-thumb-up"></i>Like</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					</div>
					</li>
			</ul>
		</div>
		</div>
	</div>
</div>
										<!-- brand awareness -->									
										<div class="travad-detailbox brandawareness">
											<div class="row">
												<div class="col l5 m5 s12 detail-part">
													<div class="frow remove_m10">
														<label>Branding Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder">
															<input type='text' maxlength="50" class="fullwidth aware_head" placeholder="Slogan your brand in a few words" data-length="50" id="brandawarenesscatch"/>
														</div>
													</div>
													<div class="frow remove_m10">
														<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
															<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput aware_text" placeholder="Create personalized text that easily identify your brand name or product"  data-length="140" id="brandawarenesstext"></textarea>
														</div>
													</div>
													<div class="frow remove_m10">
														<label>Website URL <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth advert_url" placeholder="Add your website URL" id="brandawarenesssite">
														</div>
													</div>													
													<div class="frow remove_m10">
														<p class="note">For effective branding advert, add an image that show your product or business name</p>
													</div>
												</div>
												<div class="col l7 m7 s12 preview-part">
													<div class="frow top-sec">
														<label>Advert Preview</label>														
														<div class="settings-icon">
															<div class="dropdown dropdown-custom dropdown-med resist">
																<a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="setting_btn3<?=$rand?>">
																	<i class="zmdi zmdi-more"></i>
																</a>
																<ul id="setting_btn3<?=$rand?>" class="dropdown-content custom_dropdown">
																	<li class="dmenu-title">Show advert in</li>
																	<li class="divider"></li>
																	<li>
																		<ul class="echeck-list">
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="brandawareness_desktop_i"><label for="brandawareness_desktop_i">Stream Feeds (Desktop)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="brandawareness_mobile"><label for="brandawareness_mobile">Stream Feeds (Mobile)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="brandawareness_desktop_ii"><label for="brandawareness_desktop_ii">Right Column (Desktop)</label></a></li>
																		</ul>
																	</li>
																</ul>
															</div>
														</div>
													</div>
													<div class="mbl-preview-part">
														<div class="adpreview-holder sfeed-m">
															<div class="post-holder travad-box brand-travad">
																<div class="post-topbar">
																	<div class="post-userinfo brandawarenesscatch change_awar_head">
																		Slogan your brand in a few words
																	</div>
																	<?=$settings_icon?>
																</div>
																<div class="post-content">							
																	<div class="shared-box shared-category">		
																		<div class="post-holder">					
																			<div class="post-content">
																				<div class="post-img-holder">
																					<div class="post-img one-img">
																						<div class="pimg-holder"><img class="brandawareness" src="<?=$baseUrl?>/images/brand-p.jpg"/></div>
																					</div>
																				</div>
																				<div class="share-summery">	
																					<div class="travad-subtitle brandawarenesstext change_awar_text">Create personalized text that easily identify your brand name or product</div>	<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>					
																					<a href="javascript:void(0)" target="_blank" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Explore</a>
																				</div>
																			</div>																				
																		</div>
																	</div>
																</div>
															</div>															
														</div>
													</div>
													<div class="preview-accordion">
<ul class="collapsible" data-collapsible="accordion">
	<li>
		<h4 class="collapsible-header">Right Column (Desktop)</h4>
		<div class="collapsible-body">
		<div>
			<div class="adpreview-holder rcolumn-d">
				<div class="content-box">
					<div class="cbox-desc">
						<div class="side-travad brand-travad travad-box">
							<div class="travad-maintitle brandawarenesscatch change_awar_head">Slogan your brand in a few words</div>
							<div class="imgholder">
								<div class="post-img-holder">
									<div class="post-img one-img pos_rel">
										<div class="crop-holder mian-crop1 image-cropperGeneralRCDsk">
									        <div class="cropit-preview"></div>
									        <div class="main-img pimg-holder">
												<img class="brandawareness ui-corner-all" src="<?=$baseUrl?>/images/additem-photo.png">
									        </div> 
									        <div class="main-img1 ">
									            <img id="imageid" draggable="false"/>
									        </div>
									        <div class="new-icon-cam">
									            <div class="btnupload custom_up_load" id="upload_img_action">
									                <div class="fileUpload">
									                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
									                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
									                </div>
									            </div>
									        </div>
									        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
									        <span class="zmdi zmdi-check"></span>
									        </a>
									        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg dis-none">
									        <i class="mdi mdi-close"></i>	
									        </a>
									    </div>
									</div>
								</div>
							</div>
							<div class="descholder">	
								<div class="travad-subtitle brandawarenesstext change_awar_text">Create personalized text that easily identify your brand name or product</div>
								<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
								<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Explore</a>
							</div>
						</div>
					</div>	
				</div>
			</div>
		</div>
		</div>
	</li>
	<li>
		<h4 class="collapsible-header">Stream Feeds (Mobile)</h4>
		<div class="collapsible-body">
		<div>
			<div class="adpreview-holder sfeed-m">
				<div class="post-holder travad-box brand-travad">
					<div class="post-topbar">
						<div class="post-userinfo brandawarenesscatch change_awar_head">
							Slogan your brand in a few words
						</div>
						<?=$settings_icon?>
					</div>
					<div class="post-content">							
						<div class="shared-box shared-category">								
							<div class="post-holder">									
								<div class="post-content">
									<div class="post-img-holder">
										<div class="post-img one-img pos_rel">
											<div class="crop-holder mian-crop1 image-cropperGeneralMbl">
										        <div class="cropit-preview"></div>
										        <div class="main-img pimg-holder">
													<img class="brandawareness ui-corner-all" src="<?=$baseUrl?>/images/additem-photo.png">
										        </div> 
										        <div class="main-img1 ">
										            <img id="imageid" draggable="false"/>
										        </div>
										        <div class="new-icon-cam">
										            <div class="btnupload custom_up_load" id="upload_img_action">
										                <div class="fileUpload">
										                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
										                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
										                </div>
										            </div>
										        </div>
										        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
										        <span class="zmdi zmdi-check"></span>
										        </a>
										        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg dis-none">
										        <i class="mdi mdi-close"></i>	
										        </a>
										    </div>
										</div>
									</div>
									<div class="share-summery">	
										<div class="travad-subtitle brandawarenesstext change_awar_text">Create personalized text that easily identify your brand name or product</div>
										<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
										<a href="javascript:void(0)" target="_blank" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Explore</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>															
			</div>
		</div>
		</div>
	</li>
	<li>
		<h4 class="collapsible-header last-item active"> Stream Feeds (Desktop) </h4>
		<div>
			<div class="adpreview-holder sfeed-d">
				<div class="post-holder travad-box brand-travad">
					<div class="post-topbar">
						<div class="post-userinfo brandawarenesscatch change_awar_head">
							Slogan your brand in a few words
						</div>
						<?=$settings_icon?>
					</div>
					<div class="post-content">							
						<div class="shared-box shared-category">
							<div class="post-holder">			
								<div class="post-content">
									<div class="post-img-holder">
										<div class="post-img one-img pos_rel">
											<div class="crop-holder mian-crop1 image-cropperGeneralDsk  ">
										        <div class="cropit-preview"></div>
										        <div class="main-img pimg-holder">
										            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
										        </div>
										        <div class="main-img1 ">
										            <img id="brandawarenessimagedp" draggable="false"/>
										        </div>
										        <div class="new-icon-cam">
										            <div class="btnupload custom_up_load" id="upload_img_action">
										                <div class="fileUpload">
										                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
										                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
										                </div>
										            </div>
										        </div>
										        <a  href="javascript:void(0)" class="saveimg btn btn-save image_save_btn image_save dis-none">
										        <span class="zmdi zmdi-check"></span>
										        </a>
										        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
										        <i class="mdi mdi-close"></i>	
										        </a>
										    </div>
										</div>
									</div>
									<div class="share-summery">	
										<div class="travad-subtitle brandawarenesstext change_awar_text">Create personalized text that easily identify your brand name or product</div>
										<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
										<a href="javascript:void(0)" target="_blank" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Explore</a>
									</div>
								</div>																		
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
</ul>
													</div>
												</div>
												
											</div>
										</div>
										<!-- website leads -->									
										<div class="travad-detailbox websiteleads">
											<div class="row">
												<div class="col l5 m5 s12 detail-part">
													<div class="frow">
														<label>Advert Title <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth advert_title" placeholder="Add your advert title" onkeyup="adHeader('websiteleadstitle')" id="websiteleadstitle">
														</div>
													</div>
													<div class="frow">
														<label>Logo Image <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="img-holder logoimg-holder">
															<img src="<?=$baseUrl?>/images/adimg-placeholder-logo.png"/>
															<div class="overlay">
																<span class="overlayUploader">
																	<i class="mdi mdi-plus"></i>
																	<input type="file" id="websiteleadslogo" onchange="adPhotoInputChange(this,event,'adImageLogo','websiteleadslogo')"/><a href="javascript:void(0);" class="popup-modal">&nbsp;</a>
																</span>
															</div>
														</div>														
													</div>
													<div class="frow">
														<label>Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th50">
															<textarea  maxlength="70" class="materialize-textarea mb0 md_textarea descinput advert_phrase" placeholder="Slogan your brand in a few words" data-length="70" id="websiteleadscatch"></textarea>
														</div>
													</div>
													<div class="frow">
														<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth advert_head" placeholder="Write a headline of your ad" onkeyup="adHeader('websiteleadsheader')" id="websiteleadsheader">
														</div>
													</div>	
													<div class="frow">
														<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
															<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput advert_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="websiteleadstext"></textarea>
														</div>
													</div>
													<div class="frow">
														<label>Website URL<a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth advert_url" placeholder="Add your website URL" onkeyup="adHeader('websiteleadssite')" id="websiteleadssite">
														</div>
													</div>									
												</div>
												<div class="col l7 m7 s12 preview-part">
													<div class="frow top-sec">
														<label>Advert Preview</label>
														<div class="settings-icon">
															<div class="dropdown dropdown-custom dropdown-med resist">
																<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_btn4<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
																<ul id="setting_btn4<?=$rand?>" class="dropdown-content custom_dropdown">
																	<li class="dmenu-title">Show advert in</li>
																	<li class="divider"></li>
																	<li>
																		<ul class="echeck-list">
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="websiteleads_desktop_i"><label for="websiteleads_desktop_i">Stream Feeds (Desktop)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="websiteleads_mobile"><label for="websiteleads_mobile">Stream Feeds (Mobile)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="websiteleads_desktop_ii"><label for="websiteleads_desktop_ii">Right Column (Desktop)</label></a></li>
																		</ul>
																	</li>
																</ul>
															</div>
														</div>
													</div>
													<div class="mbl-preview-part">
														<div class="adpreview-holder sfeed-m">
															<div class="post-holder travad-box weblink-travad">
																<div class="post-topbar">
																	<div class="post-userinfo">
																		<div class="img-holder">
																			<div id="profiletip-4" class="profiletipholder">
																				<span class="profile-tooltip">
																					<img class="circle websiteleadslogo" src="<?=$baseUrl?>/images/demo-business.jpg"/>
																				</span>
																			</div>
																		</div>
																		<div class="desc-holder">
																			<div class="travad-maintitle websiteleadstitle lead_title">Add your advert title</div>
																			<span class="timestamp">Sponsored Ad</span>
																		</div>
																	</div>
																	<?=$settings_icon?>
																</div>
																<div class="post-content">							
																	<div class="shared-box shared-category">
																		<div class="post-holder">									
																			<div class="post-content">
																				<div class="post-details lead_phrase">
																					<p class="websiteleadscatch">Slogan your brand in a few words</p>
																				</div>	
																				<div class="post-img-holder">
																					<div class="post-img one-img">
																						<div class="pimg-holder"><img class="websiteleads" src="<?=$baseUrl?>/images/admain-food.jpg"/></div>
																					</div>
																				</div>
																				<div class="share-summery">											
																					<div class="travad-title websiteleadsheader">Write a headline of your ad</div>
																					<div class="travad-subtitle websiteleadstext">Create personalized text that easily identify your brand name or product</div>
																					<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite">Add your website URL</span></a>
																				</div>
																			</div>																				
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="preview-accordion">
<ul class="collapsible" data-collapsible="accordion">
	<li>
		<h4 class="collapsible-header">Right Column (Desktop)</h4>
		<div class="collapsible-body">
		<div>
			<div class="adpreview-holder rcolumn-d">
				<div class="content-box">					
					<div class="cbox-desc">
						<div class="side-travad weblink-travad travad-box">
							<div class="travad-maintitle"><img class="websiteleadslogo" src="<?=$baseUrl?>/images/demo-business.jpg"><h6 class="websiteleadstitle lead_title">Add your advert title</h6><span>Sponsored</span></div>
							<div class="post-img-holder">
								<div class="post-img one-img pos_rel">
									<div class="crop-holder mian-crop1 image-cropperGeneralRCDsk">
								        <div class="cropit-preview"></div>
								        <div class="main-img pimg-holder">
								            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
								        </div>
								        <div class="main-img1 ">
								            <img id="imageid" draggable="false"/>
								        </div>
								        <div class="new-icon-cam">
								            <div class="btnupload custom_up_load" id="upload_img_action">
								                <div class="fileUpload">
								                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
								                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
								                </div>
								            </div>
								        </div>
								        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
								        <span class="zmdi zmdi-check"></span>
								        </a>
								        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
								        <i class="mdi mdi-close"></i>	
								        </a>
								    </div>
								</div>
							</div>
							<div class="descholder">								
								<div class="travad-title websiteleadsheader lead_head">Write a headline of your ad</div>
								<div class="travad-subtitle websiteleadstext lead_text">Create personalized text that easily identify your brand name or product</div>
								<a href="javascript:void(0)" class="adlink"><i class="mdi mdi-earth"></i><span class="websiteleadssite lead_url">Add your website URL</span></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</li>
	<li>
		<h4 class="collapsible-header">Stream Feeds (Mobile)</h4>
			<div class="collapsible-body">
			<div>
			<div class="adpreview-holder sfeed-m">
				<div class="post-holder travad-box weblink-travad">
					<div class="post-topbar">
						<div class="post-userinfo">
							<div class="img-holder">
								<div id="profiletip-4" class="profiletipholder">
									<span class="profile-tooltip">
										<img class="circle websiteleadslogo" src="<?=$baseUrl?>/images/demo-business.jpg"/>
									</span>
								</div>
							</div>
							<div class="desc-holder">
								<div class="travad-maintitle websiteleadstitle lead_title">Add your advert title</div>
								<span class="timestamp">Sponsored Ad</span>
							</div>
						</div>
						<?=$settings_icon?>
					</div>
					<div class="post-content">							
						<div class="shared-box shared-category">
							<div class="post-holder">									
								<div class="post-content">
									<div class="post-details">
										<p class="websiteleadscatch lead_phrase">Slogan your brand in a few words</p>
									</div>	
									<div class="post-img-holder">
										<div class="post-img one-img pos_rel">
											<div class="crop-holder mian-crop1 image-cropperGeneralMbl">
										        <div class="cropit-preview"></div>
										        <div class="main-img pimg-holder">
										            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
										        </div>
										        <div class="main-img1 ">
										            <img id="imageid" draggable="false"/>
										        </div>
										        <div class="new-icon-cam">
										            <div class="btnupload custom_up_load" id="upload_img_action">
										                <div class="fileUpload">
										                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
										                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
										                </div>
										            </div>
										        </div>
										        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
										        <span class="zmdi zmdi-check"></span>
										        </a>
										        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
										        <i class="mdi mdi-close"></i>	
										        </a>
										    </div>
										</div>
									</div>
									<div class="share-summery">											
										<div class="travad-title websiteleadsheader lead_head">Write a headline of your ad</div>
										<div class="travad-subtitle websiteleadstext lead_text">Create personalized text that easily identify your brand name or product</div>
										<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite">Add your website URL</span></a>
									</div>
								</div>																	
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</li>
	<li>
		<h4 class="collapsible-header active last-item active">Stream Feeds (Desktop)</h4>
		<div class="collapsible-body">
		<div>
			<div class="adpreview-holder sfeed-d">
				<div class="post-holder travad-box weblink-travad">
					<div class="post-topbar">
						<div class="post-userinfo">
							<div class="img-holder">
								<div id="profiletip-4" class="profiletipholder">
									<span class="profile-tooltip">
										<img class="circle websiteleadslogo" src="<?=$baseUrl?>/images/demo-business.jpg"/>
									</span>
								</div>
							</div>
							<div class="desc-holder">
								<div class="travad-maintitle websiteleadstitle lead_title">Add your advert title</div>
								<span class="timestamp">Sponsored Ad</span>
							</div>
						</div>
						<?=$settings_icon?>
					</div>
					<div class="post-content">							
						<div class="shared-box shared-category">
							<div class="post-holder">									
								<div class="post-content">
									<div class="post-details">
										<p class="websiteleadscatch lead_phrase">Slogan your brand in a few words</p>
									</div>	
									<div class="post-img-holder">
										<div class="post-img one-img pos_rel">
											<div class="crop-holder mian-crop1 image-cropperGeneralDsk  ">
										        <div class="cropit-preview"></div>
										        <div class="main-img pimg-holder">
										            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
										        </div>
										        <div class="main-img1 ">
										            <img id="websiteleadsimagedp" draggable="false"/>
										        </div>
										        <div class="new-icon-cam">
										            <div class="btnupload custom_up_load" id="upload_img_action">
										                <div class="fileUpload">
										                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
										                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
										                </div>
										            </div>
										        </div>
										        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
										        <span class="zmdi zmdi-check"></span>
										        </a>
										        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
										        <i class="mdi mdi-close"></i>	
										        </a>
										    </div>
										</div>
									</div>
									<div class="share-summery">											
										<div class="travad-title websiteleadsheader lead_head">Write a headline of your ad</div>
										<div class="travad-subtitle websiteleadstext lead_text">Create personalized text that easily identify your brand name or product</div>
										<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite">Add your website URL</span></a>
									</div>
								</div>																	
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</li>
</ul>
													</div>
												
												</div>
											</div>
										</div>
										<!-- Website Conversion -->									
										<div class="travad-detailbox websiteconversion">
											<div class="row">
												<div class="col l5 m5 s12 detail-part">
													<div class="frow more_m5">
														<label>Advert Title <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth conversion_title" placeholder="Add your advert title" onkeyup="adHeader('websiteconversiontitle')" id="websiteconversiontitle">
														</div>
													</div>
													<div class="frow">
														<label>Logo Image <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="img-holder logoimg-holder">
															<img src="<?=$baseUrl?>/images/adimg-placeholder-logo.png"/>
															<div class="overlay">
																<span class="overlayUploader">
																	<i class="mdi mdi-plus"></i>
																	<input type="file" id="websiteconversionlogo" onchange="adPhotoInputChange(this,event,'adImageLogo','websiteconversionlogo')"/><a href="javascript:void(0);" class="popup-modal">&nbsp;</a>
																</span>
															</div>
														</div>														
													</div>
													<div class="frow remove_m10">
														<label>Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th50">
															<textarea  maxlength="70" class="materialize-textarea mb0 md_textarea descinput conversion_phrase" placeholder="Slogan your brand in a few words" data-length="70" id="websiteconversioncatch"></textarea>
														</div>
													</div>
													<div class="frow more_m5">
														<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth conversion_head" placeholder="Write a headline of your ad" onkeyup="adHeader('websiteconversionheader')" id="websiteconversionheader">
														</div>
													</div>	
													<div class="frow remove_m10">
														<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
															<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput conversion_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="websiteconversiontext"></textarea>
														</div>
													</div>
													<div class="frow">
														<label>Select action button</label>
														<div class="btndrop">
															<select class="select2" id="websiteconversiontype" onchange="adHeader('websiteconversiontype')">
																<option>Book Now</option>
																<option>Shop Now</option>
																<option>Explore</option>
																<option>Learn More</option>
																<option>Contact Us</option>
																<option>Sign Up</option>
															</select>
														</div>
													</div>
													<div class="frow remove_m10">
														<label>Website URL<a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth advert_url" placeholder="Add your website URL" id="websiteconversionsite">
														</div>
													</div>
												</div>
												<div class="col l7 m7 s12 preview-part">
													<div class="frow top-sec">
														<label>Advert Preview</label>
														<div class="settings-icon">
															<div class="dropdown dropdown-custom dropdown-med resist">
																<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_btn5<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
																<ul id="setting_btn5<?=$rand?>" class="dropdown-content custom_dropdown">
																	<li class="dmenu-title">Show advert in</li>
																	<li class="divider"></li>
																	<li>
																		<ul class="echeck-list">
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="websiteconversion_desktop_i"><label for="websiteconversion_desktop_i">Stream Feeds (Desktop)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="websiteconversion_mobile"><label for="websiteconversion_mobile">Stream Feeds (Mobile)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="websiteconversion_desktop_ii"><label for="websiteconversion_desktop_ii">Right Column (Desktop)</label></a></li>
																		</ul>
																	</li>
																</ul>
															</div>
														</div>
													</div>
													<div class="mbl-preview-part">
														<div class="adpreview-holder sfeed-m">
															<div class="post-holder travad-box actionlink-travad">
																<div class="post-topbar">
																	<div class="post-userinfo">
																		<div class="img-holder">
																			<div id="profiletip-4" class="profiletipholder">
																				<span class="profile-tooltip">
																					<img class="circle websiteconversionlogo" src="<?=$baseUrl?>/images/demo-business.jpg"/>
																				</span>
																			</div>
																		</div>
																		<div class="desc-holder">
																			<div class="travad-maintitle con_create_title websiteconversiontitle">Jet Airways</div>
																			<span class="timestamp">Sponsored Ad</span>
																		</div>
																	</div>
																	<?=$settings_icon?>
																</div>
																<div class="post-content">							
																	<div class="shared-box shared-category">
																		<div class="post-holder">									
																			<div class="post-content">
																				<div class="post-details">
																					<p class="websiteconversioncatch con_create_phrase">Slogan your brand in a few words</p>
																				</div>	
																				<div class="post-img-holder">
																					<div class="post-img one-img">
																						<div class="pimg-holder"><img class="websiteconversion" src="<?=$baseUrl?>/images/admain-flight.jpg"/></div>
																					</div>
																				</div>
																				<div class="share-summery">											
																					<div class="travad-title websiteconversionheader con_create_head">Write a headline of your ad</div>
																					<div class="travad-subtitle websiteconversiontext con_create_text">Create personalized text that easily identify your brand name or product</div>
																					<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
																					<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button waves-effect waves-light">Book Now</a>
																				</div>
																			</div>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
<div class="preview-accordion">
	<ul class="collapsible" data-collapsible="accordion">
	<li>
	<h4 class="collapsible-header">Right Column (Desktop)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder rcolumn-d">
			<div class="content-box">
				
				<div class="cbox-desc">
					<div class="side-travad actionlink-travad travad-box">
						<div class="travad-maintitle"><img class="websiteconversionlogo" src="<?=$baseUrl?>/images/demo-business.jpg"><h6 class="websiteconversiontitle">Add your advert title</h6><span>Sponsored</span></div>
						<div class="post-img-holder">
							<div class="post-img one-img pos_rel">
								<div class="crop-holder mian-crop1 image-cropperGeneralRCDsk">
							        <div class="cropit-preview"></div>
							        <div class="main-img pimg-holder">
							            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
							        </div>
							        <div class="main-img1 ">
							            <img id="imageid" draggable="false"/>
							        </div>
							        <div class="new-icon-cam">
							            <div class="btnupload custom_up_load" id="upload_img_action">
							                <div class="fileUpload">
							                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
							                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
							                </div>
							            </div>
							        </div>
							        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
							        <span class="zmdi zmdi-check"></span>
							        </a>
							        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
							        <i class="mdi mdi-close"></i>	
							        </a>
							    </div>
							</div>
						</div>
						<div class="descholder">								
							<div class="travad-title websiteconversionheader con_create_head">Write a headline of your ad</div>
							<div class="travad-subtitle websiteconversiontext con_create_text">Create personalized text that easily identify your brand name or product</div>
							<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
							<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button waves-effect waves-light">Book Now</a>
						</div>
				</div>
				</div>
			</div>
			
		</div>
	</div>
	</div>
	</li>
	<li>
	<h4 class="collapsible-header">Stream Feeds (Mobile)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder sfeed-m">
			<div class="post-holder travad-box actionlink-travad">
				<div class="post-topbar">
					<div class="post-userinfo">
						<div class="img-holder">
							<div id="profiletip-4" class="profiletipholder">
								<span class="profile-tooltip">
									<img class="circle websiteconversionlogo" src="<?=$baseUrl?>/images/demo-business.jpg"/>
								</span>
							</div>
							
						</div>
						<div class="desc-holder">
							<div class="travad-maintitle websiteconversiontitle con_create_title">Add your advert title</div>
							<span class="timestamp">Sponsored Ad</span>
						</div>
					</div>
					<?=$settings_icon?>
				</div>
				<div class="post-content">							
					<div class="shared-box shared-category">
						<div class="post-holder">									
							<div class="post-content">
								<div class="post-details">
									<p class="websiteconversioncatch con_create_phrase">Slogan your brand in a few words</p>
								</div>	
								<div class="post-img-holder">
									<div class="post-img one-img pos_rel">
										<div class="crop-holder mian-crop1 image-cropperGeneralMbl">
									        <div class="cropit-preview"></div>
									        <div class="main-img pimg-holder">
									            <img src="<?=$baseUrl?>/images/additem-photo.png" class="websiteconversion"/>
									        </div>
									        <div class="main-img1 ">
									            <img id="imageid" draggable="false"/>
									        </div>
									        <div class="new-icon-cam">
									            <div class="btnupload custom_up_load" id="upload_img_action">
									                <div class="fileUpload">
									                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
									                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
									                </div>
									            </div>
									        </div>
									        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
									        <span class="zmdi zmdi-check"></span>
									        </a>
									        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
									        <i class="mdi mdi-close"></i>	
									        </a>
									    </div>
									</div>
								</div>
								<div class="share-summery">											
									<div class="travad-title websiteconversionheader con_create_head">Write a headline of your ad</div>
									<div class="travad-subtitle websiteconversiontext con_create_text">Create personalized text that easily identify your brand name or product</div>
									<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
									<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button waves-effect waves-light">Book Now</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</li>
	<li>
	<h4 class="collapsible-header last-item active">Stream Feeds (Desktop)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder sfeed-d">
			<div class="post-holder travad-box actionlink-travad">
				<div class="post-topbar">
					<div class="post-userinfo">
						<div class="img-holder">
							<div id="profiletip-4" class="profiletipholder">
								<span class="profile-tooltip">
									<img class="circle websiteconversionlogo" src="<?=$baseUrl?>/images/webconversion-demo.png"/>
								</span>
							</div>
						</div>
						<div class="desc-holder">
							<div class="travad-maintitle websiteconversiontitle con_create_title">Add your advert title</div>
							<span class="timestamp">Sponsored Ad</span>
						</div>
					</div>
					<?=$settings_icon?>
				</div>
				<div class="post-content">							
					<div class="shared-box shared-category">
						<div class="post-holder">									
							<div class="post-content">
								<div class="post-details">
									<p class="websiteconversioncatch con_create_phrase">Slogan your brand in a few words</p>
								</div>	
								<div class="post-img-holder">
									<div class="post-img one-img pos_rel">
										<div class="crop-holder mian-crop1 image-cropperGeneralDsk  ">
									        <div class="cropit-preview"></div>
									        <div class="main-img pimg-holder">
									            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
									        </div>
									        <div class="main-img1 ">
									            <img id="websiteconversionimagedp" draggable="false"/>
									        </div>
									        <div class="new-icon-cam">
									            <div class="btnupload custom_up_load" id="upload_img_action">
									                <div class="fileUpload">
									                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
									                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
									                </div>
									            </div>
									        </div>
									        <a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save dis-none saveimg">
									        <span class="zmdi zmdi-check"></span>
									        </a>
									        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
									        <i class="mdi mdi-close"></i>	
									        </a>
									    </div>
									</div>
								</div>
								<div class="share-summery">											
									<div class="travad-title websiteconversionheader con_create_head">Write a headline of your ad</div>
									<div class="travad-subtitle websiteconversiontext con_create_text">Create personalized text that easily identify your brand name or product</div>
									<a href="javascript:void(0)" class="adlink lead_url">www.iaminjapan.com</a>
									<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button waves-effect waves-light">Book Now</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</li>
	</ul>
</div>
												</div>
											</div>
										</div>
										<!-- inbox highlight -->									
										<div class="travad-detailbox inboxhighlight">
											<div class="row">
												<div class="col l5 m5 s12 detail-part">
													<div class="frow">
														<label>Advert Title <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth inbox_title" placeholder="Add your advert title" id="inboxhighlighttitle" onkeyup="adHeader('inboxhighlighttitle')">
														</div>
													</div>
													<div class="frow">
														<label>Logo Image <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="img-holder logoimg-holder">
															<img src="<?=$baseUrl?>/images/adimg-placeholder-logo.png"/>
															<div class="overlay">
																<span class="overlayUploader">
																	<i class="mdi mdi-plus"></i>
																	<input type="file" id="inboxhighlightlogo" onchange="adPhotoInputChange(this,event,'adImageLogo','inboxhighlightlogo')"/><a href="javascript:void(0);" class="popup-modal">&nbsp;</a>
																</span>
															</div>
														</div>														
													</div>
													<div class="frow remove_m10">
														<label>Main Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder">
															<input type='text' maxlength="30" class="fullwidth inbox_main_catch" placeholder="Add your main catch phrase" data-length="30" id="inboxhighlightcatch"/>
														</div>
													</div>
													<div class="frow remove_m10">
														<label>Sub Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder">
															<input type='text' maxlength="35" class="fullwidth inbox_sub_catch" placeholder="Add your sub catch phrase" data-length="35" id="inboxhighlightsubcatch">
														</div>
													</div>
													<div class="frow more_m5">
														<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth inbox_head" placeholder="Write a headline of your ad" id="inboxhighlightheader" onkeyup="adHeader('inboxhighlightheader')">
														</div>
													</div>	
													<div class="frow remove_m10">
														<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
															<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput inbox_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="inboxhighlighttext"></textarea>
														</div>
													</div>
													<div class="frow">
														<label>Select action button</label>
														<div class="btndrop">
															<select class="select2" id="inboxhighlighttype" onchange="adHeader('inboxhighlighttype')">
																<option>Shop Now</option>
																<option>Book Now</option>
																<option>Explore</option>
																<option>Learn More</option>
																<option>Contact Us</option>
																<option>Sign Up</option>
															</select>
														</div>
													</div>
												</div>
													<div class="col l7 m7 s12 preview-part">
													<div class="frow top-sec">
														<label>Advert Preview</label>														
														<div class="settings-icon">
															<div class="dropdown dropdown-custom dropdown-med resist">
																<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_btn6<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
																<ul id="setting_btn6<?=$rand?>" class="dropdown-content custom_dropdown">
																	<li class="dmenu-title">Show advert in</li>
																	<li class="divider"></li>
																	<li>
																		<ul class="echeck-list">
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="inboxhighlight_desktop_i"><label for="inboxhighlight_desktop_i">Stream Feeds (Desktop)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="inboxhighlight_mobile"><label for="inboxhighlight_mobile">Stream Feeds (Mobile)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="inboxhighlight_desktop_ii"><label for="inboxhighlight_desktop_ii">Right Column (Desktop)</label></a></li>
																		</ul>
																	</li>
																</ul>
															</div>
														</div>
													</div>
													<div class="mbl-preview-part">
														<div class="adpreview-holder sfeed-d inboxad-preview">
															<div class="summery-adpreview">
																<h5>Summery Ad Preview</h5>
																<ul>
																	<li class="inbox-travad">
																		<a href="javascript:void(0)" onclick="openMessage(this)" id="ad">
																			<span class="muser-holder">
																				<span class="imgholder"><img class="inboxhighlightlogo" src="<?=$baseUrl?>/images/demo-business.jpg"/></span>
																				<span class="descholder">
																					<h6 class="inboxhighlightcatch inbox_create_main">Add your main catch phrase</h6>
																					<p class="inboxhighlightsubcatch inbox_create_sub">Add your sub catch phrase</p>
																					<span class="adspan"><span>Ad</span></span>
																				</span>
																			</span>
																		</a>
																	</li>
																</ul>
															</div>
															<div class="clear"></div>
															<div class="detail-adpreview">
																<h5>Detail Ad Preview</h5>
																<div class="right-section inbox-advert">
																	<div class="topstuff">
																		<div class="msgwindow-name">
																			<h4><label class="inboxhighlighttitle inbox_create_title">Add your advert title</label> <span>Sponsored Ad</span></h4>
																		</div>
																	</div>
																	<div class="main-msgwindow">
																		<div class="allmsgs-holder">				
																			<ul class="current-messages">
																				<li class="mainli active" id="li-travad">
																					<div class="msgdetail-list nice-scroll" tabindex="10">
																						<div class="inbox-travad">
																							<img class="inboxhighlightimage" src="<?=$baseUrl?>/images/inboxad-demo.png">
																							<h6 class="inboxhighlightheader inbox_create_head">Write a headline of your ad</h6>
																							<p class="inboxhighlighttext inbox_create_head">Create personalized text that easily identify your brand name or product</p>
																							<a href="javascript:void(0)" class="btn btn-primary btn-sm right inboxhighlighttype inbox_create_button waves-effect waves-light">Shop Now</a>
																						</div>
																					</div>
																				</li>
																			</ul>					
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
<div class="preview-accordion" id="ih-accrodion">
	<ul class="collapsible" data-collapsible="accordion">
	<li>
	<h4 class="collapsible-header">Stream Feeds (Mobile)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder sfeed-m travad-box inboxad-preview">
			<div class="summery-adpreview">
				<h5>Summery Ad Preview</h5>
				<ul>
					<li class="inbox-travad">
						<a href="javascript:void(0)" onclick="openMessage(this)" id="ad">
							<span class="muser-holder">
								<span class="imgholder"><img class="inboxhighlightlogo" src="<?=$baseUrl?>/images/demo-business.jpg"/></span>
								<span class="descholder">
									<h6 class="inboxhighlightcatch inbox_create_main">Add your main catch phrase</h6>
									<p class="inboxhighlightsubcatch inbox_create_sub">Add your sub catch phrase</p>
									<span class="adspan"><span>Ad</span></span>
								</span>
							</span>
						</a>
					</li>
				</ul>
			</div>
			<div class="clear"></div>
			<div class="detail-adpreview">
				<h5>Detail Ad Preview</h5>
				<div class="right-section inbox-advert">
					<div class="topstuff">
						<div class="msgwindow-name">
							<a href="javascript:void(0)" class="backMessageList" onclick="closeMessage()"><i class="mdi mdi-menu-left"></i></a>
							<h4 class="inboxhighlighttitle">Add your advert title <span>Sponsored Ad</span></h4>
						</div>
					</div>
					<div class="main-msgwindow">
						<div class="allmsgs-holder">				
							<ul class="current-messages">
								<li class="mainli active post-content" id="li-travad">
									<div class="msgdetail-list nice-scroll" tabindex="10">
										<div class="inbox-travad post-holder">
											<div class="post-img-holder adsection_img">
												<div class="post-img one-img pos_rel">
													<div class="crop-holder mian-crop1 image-cropperGeneralMbl">
												        <div class="cropit-preview"></div>
												        <div class="main-img pimg-holder">
												            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all inboxhighlightimage"/>
												        </div>
												        <div class="main-img1 ">
												            <img id="imageid" draggable="false"/>
												        </div>
												        <div class="new-icon-cam">
												            <div class="btnupload custom_up_load" id="upload_img_action">
												                <div class="fileUpload">
												                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
												                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
												                </div>
												            </div>
												        </div>
												        <a  href="javascript:void(0)" class="saveimg btn btn-save image_save_btn image_save dis-none">
												        <span class="zmdi zmdi-check"></span>
												        </a>
												        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
												        <i class="mdi mdi-close"></i>	
												        </a>
												    </div>
												</div>
											</div>
											<h6 class="inboxhighlightheader inbox_create_head">Write a headline of your ad</h6>
											<p class="inboxhighlighttext inbox_create_text">Create personalized text that easily identify your brand name or product</p>
											<a href="javascript:void(0)" class="btn btn-primary btn-sm right inboxhighlighttype inbox_create_button waves-effect waves-light">Shop Now</a>
										</div>
									</div>
								</li>
							</ul>					
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</li>
	<li>
	<h4 class="collapsible-header active last-item">Stream Feeds (Desktop)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder sfeed-d travad-box inboxad-preview">
			<div class="summery-adpreview">
				<h5>Summery Ad Preview</h5>
				<ul>
					<li class="inbox-travad">
						<a href="javascript:void(0)" onclick="openMessage(this)" id="ad">
							<span class="muser-holder">
								<span class="imgholder"><img class="inboxhighlightlogo" src="<?=$baseUrl?>/images/demo-business.jpg"/></span>
								<span class="descholder">
									<h6 class="inboxhighlightcatch inbox_create_main">Add your main catch phrase</h6>
									<p class="inboxhighlightsubcatch inbox_create_sub">Add your sub catch phrase</p>
									<span class="adspan"><span>Ad</span></span>
								</span>
							</span>
						</a>
					</li>
				</ul>
			</div>
			<div class="clear"></div>
			<div class="detail-adpreview">
				<h5>Detail Ad Preview</h5>
				<div class="right-section inbox-advert">
					<div class="topstuff">
						<div class="msgwindow-name">
							<h4 class="inboxhighlighttitle inbox_create_title">Add your advert title <span>Sponsored Ad</span></h4>
						</div>
					</div>
					<div class="main-msgwindow">
						<div class="allmsgs-holder">				
							<ul class="current-messages">
								<li class="mainli active" id="li-travad">
									<div class="msgdetail-list nice-scroll" tabindex="10">
										<div class="inbox-travad">
											<div class="post-img-holder">
												<div class="post-img one-img pos_rel">
													<div class="crop-holder mian-crop1 image-cropperGeneralDsk  ">
												        <div class="cropit-preview"></div>
												        <div class="main-img pimg-holder">
												            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all"/>
												        </div>
												        <div class="main-img1 ">
												            <img id="inboxhighlightimageimagedp" draggable="false"/>
												        </div>
												        <div class="new-icon-cam">
												            <div class="btnupload custom_up_load" id="upload_img_action">
												                <div class="fileUpload">
												                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
												                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
												                </div>
												            </div>
												        </div>
												        <a  href="javascript:void(0)" class="saveimg btn btn-save image_save_btn image_save dis-none">
												        <span class="zmdi zmdi-check"></span>
												        </a>
												        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
												        <i class="mdi mdi-close"></i>	
												        </a>
												    </div>
												</div>
											</div>
											<h6 class="inboxhighlightheader inbox_create_head">Write a headline of your ad</h6>
											<p class="inboxhighlighttext inbox_create_text">Create personalized text that easily identify your brand name or product</p>
											<a href="javascript:void(0)" class="btn btn-primary btn-sm right inboxhighlighttype inbox_create_button waves-effect waves-light">Shop Now</a>
										</div>
									</div>
								</li>
							</ul>					
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
													</li>
													</ul>
													</div>
												</div>
												
											</div>
										</div>
										<!-- page endorsement -->
										<div class="travad-detailbox pageendorse">
											<div class="row">
												<div class="col l5 m5 s12 detail-part">
													<div class="frow">
														<label>Select a page to advert</label>
														<div class="sliding-middle-custom anim-area underlined fullwidth enterpagelistingarea">
															<select class="select2 pagenamechange" id="pageendorsenames" onchange="pageSelect('pageendorsenames','pageendorsecount','pageendorseimage','pageendorsetext','pe')">
																<?php
																if(empty($adpages)) {
																	echo "<option>No Page</option>";
																} else {
																	foreach($adpages as $key => $adpage) { 
																		echo "<option value='".$key."'>".$adpage."</option>";
																	} 
																}
																?>
															</select>
														</div>
													</div>
													<div class="frow">
														<label>Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th50">
															<textarea maxlength="70" class="materialize-textarea mb0 md_textarea descinput endorsement_phrase" placeholder="Slogan your brand in a few words" data-length="70" id="pageendorsecatch"></textarea>
														</div>
													</div>
													<div class="frow">
														<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom underlined anim-area fullwidth">
															<input type="text" class="fullwidth endorsement_head" placeholder="Write a headline of your ad" id="pageendorseheader" onkeyup="adHeader('pageendorseheader')">
														</div>
													</div>	
													<div class="frow">
														<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
															<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput endorsement_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="pageendorsetext"></textarea>
														</div>
													</div>
												</div>
												<div class="col l7 m7 s12 preview-part">
													<div class="frow top-sec">
														<label>Advert Preview</label>														
														<div class="settings-icon">
															<div class="dropdown dropdown-custom dropdown-med resist">
																<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_btn7<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
																<ul id="setting_btn7<?=$rand?>" class="dropdown-content custom_dropdown">
																	<li class="dmenu-title">Show advert in</li>
																	<li class="divider"></li>
																	<li>
																		<ul class="echeck-list">
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="pageendorsement_desktop_i"><label for="pageendorsement_desktop_i">Stream Feeds (Desktop)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="pageendorsement_mobile"><label for="pageendorsement_mobile">Stream Feeds (Mobile)</label></a></li>
																			<li class="selected"><a href="javascript:void(0)"><input type="checkbox" id="pageendorsement_desktop_ii"><label for="pageendorsement_desktop_ii">Right Column (Desktop)</label></a></li>
																		</ul>
																	</li>
																</ul>
															</div>
														</div>
													</div>
													<div class="mbl-preview-part">
														<div class="adpreview-holder sfeed-m">
															<div class="post-holder travad-box page-travad">
																<div class="post-topbar">
																	<div class="post-userinfo">
																		<div class="img-holder">
																			<div id="profiletip-4" class="profiletipholder">
																				<span class="profile-tooltip">
																					<img class="circle pageendorseimage" src="<?=$this->context->getpageimage($firstpageid)?>">
																				</span>
																			</div>
																		</div>
																		<div class="desc-holder">
																			<a href="javascript:void(0)" class="pagelikenames"><?=$firstpagename?></a>
																			<span class="timestamp">Sponsored Ad</span>
																		</div>
																	</div>
																	<?=$settings_icon?>
																</div>
																<div class="post-content">							
																	<div class="shared-box shared-category">
																		<div class="post-holder">									
																			<div class="post-content">
																				<div class="post-details">
																					<p class="pageendorsecatch endorsement_create_phrase">Slogan your brand in a few words</p>
																				</div>	
																				<div class="post-img-holder">
																					<div class="post-img one-img">
																						<div class="pimg-holder"><img class="pageendorse" src="<?=$baseUrl?>/images/pagead-endorse.jpg"/></div>
																					</div>
																				</div>
																				<div class="share-summery">											
																					<div class="travad-title pageendorseheader endorsement_create_head">Write a headline of your ad</div>
																					<div class="travad-subtitle pageendorsetext endorsement_create_text">Create personalized text that easily identify your brand name or product</div>
																					<div class="travad-info"><span class="pageendorsecount"><?=$pe?></span> people endorsed this page</div>
																					<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Endorse</a>
																				</div>
																			</div>																				
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
<div class="preview-accordion">
	<ul class="collapsible" data-collapsible="accordion">
	<li>
	<h4 class="collapsible-header">Right Column (Desktop)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder rcolumn-d">
			<div class="content-box">
				<div class="cbox-desc">
					<div class="side-travad page-travad travad-box">
						<div class="travad-maintitle"><img class="pageendorseimage" src="<?=$this->context->getpageimage($firstpageid)?>"><h6 class="pagelikenames"><?=$firstpagename?></h6><span>Sponsored</span></div>
						<div class="post-img-holder">
							<div class="post-img one-img pos_rel">
								<div class="crop-holder mian-crop1 image-cropperGeneralRCDsk">
							        <div class="cropit-preview"></div>
							        <div class="main-img pimg-holder">
							            <img src="<?=$baseUrl?>/images/additem-photo.png" class="pageendorse ui-corner-all"/>
							        </div>
							        <div class="main-img1 ">
							            <img id="imageid" draggable="false"/>
							        </div>
							        <div class="new-icon-cam">
							            <div class="btnupload custom_up_load" id="upload_img_action">
							                <div class="fileUpload">
							                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
							                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
							                </div>
							            </div>
							        </div>
							        <a  href="javascript:void(0)" class="saveimg btn btn-save image_save_btn image_save dis-none">
							        <span class="zmdi zmdi-check"></span>
							        </a>
							        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
							        <i class="mdi mdi-close"></i>	
							        </a>
							    </div>
							</div>
						</div> 
						<div class="descholder">	
							<div class="travad-title pageendorseheader endorsement_create_main endorsement_create_head">Best facilites you ever found!</div>
							<div class="travad-subtitle endorsement_create_text pageendorsetext">Endorse us for the best hospitality services we provide.</div>
							<div class="travad-info"><span class="pageendorsecount"><?=$pe?></span> people endorsed this page</div>
							<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn endorsement_create_button waves-effect waves-light">Endorse</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</li>
	<li>
	<h4 class="collapsible-header">Stream Feeds (Mobile)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder sfeed-m">
			<div class="post-holder travad-box page-travad">
				<div class="post-topbar">
					<div class="post-userinfo">
						<div class="img-holder">
							<div id="profiletip-4" class="profiletipholder">
								<span class="profile-tooltip">
									<img class="circle pageendorseimage" src="<?=$this->context->getpageimage($firstpageid)?>"/>
								</span>
							</div>
						</div>
						<div class="desc-holder">
							<a href="javascript:void(0)" class="pagelikenames"><?=$firstpagename?></a>
							<span class="timestamp">Sponsored Ad</span>
						</div>
					</div>
					<?=$settings_icon?>
				</div>
				<div class="post-content">							
					<div class="shared-box shared-category">
						<div class="post-holder">									
							<div class="post-content">
								<div class="post-details">
									<p class="pageendorsecatch endorsement_create_phrase">Slogan your brand in a few words</p>
								</div>	
								<div class="post-img-holder">
									<div class="post-img one-img pos_rel">
										<div class="crop-holder mian-crop1 image-cropperGeneralMbl">
									        <div class="cropit-preview"></div>
									        <div class="main-img pimg-holder">
									            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all pageendorse"/>
									        </div>
									        <div class="main-img1 ">
									            <img id="imageid" draggable="false"/>
									        </div>
									        <div class="new-icon-cam">
									            <div class="btnupload custom_up_load" id="upload_img_action">
									                <div class="fileUpload">
									                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
									                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
									                </div>
									            </div>
									        </div>
									        <a  href="javascript:void(0)" class="saveimg btn btn-save image_save_btn image_save dis-none">
									        <span class="zmdi zmdi-check"></span>
									        </a>
									        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
									        <i class="mdi mdi-close"></i>	
									        </a>
									    </div>
									</div>
								</div>
								<div class="share-summery">											
										<div class="travad-title pageendorseheader endorsement_create_head">Best facilites you ever found!</div>
										<div class="travad-subtitle pageendorsetext endorsement_create_text">Endorse us for the best hospitality services we provide.</div>
										<div class="travad-info"><span class="pageendorsecount"><?=$pe?></span> people endorsed this page</div>
										<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Endorse</a>
								</div>
							</div>																		
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</li>
	<li>
	<h4 class="collapsible-header active last-item">Stream Feeds (Desktop)</h4>
	<div class="collapsible-body">
	<div>
		<div class="adpreview-holder sfeed-d">
			<div class="post-holder travad-box page-travad">
				<div class="post-topbar">
					<div class="post-userinfo">
						<div class="img-holder">
							<div id="profiletip-4" class="profiletipholder">
								<span class="profile-tooltip">
									<img class="circle pageendorseimage" src="<?=$this->context->getpageimage($firstpageid)?>"/>
								</span>
							</div>
						</div>
						<div class="desc-holder">
							<a href="javascript:void(0)" class="pagelikenames"><?=$firstpagename?></a>
							<span class="timestamp">Sponsored Ad</span>
						</div>
					</div>
					<?=$settings_icon?>
				</div>
				<div class="post-content">							
					<div class="shared-box shared-category">
						<div class="post-holder">									
							<div class="post-content">
								<div class="post-details">
									<p class="pageendorsecatch endorsement_create_phrase">Slogan your brand in a few words</p>
								</div>	
								<div class="post-img-holder">
									<div class="post-img one-img pos_rel">
										<div class="crop-holder mian-crop1 image-cropperGeneralDsk  ">
									        <div class="cropit-preview"></div>
									        <div class="main-img pimg-holder">
									            <img src="<?=$baseUrl?>/images/additem-photo.png" class="ui-corner-all pageendorse"/>
									        </div>
									        <div class="main-img1 ">
									            <img id="pageendorseimagedp" draggable="false"/>
									        </div>
									        <div class="new-icon-cam">
									            <div class="btnupload custom_up_load" id="upload_img_action">
									                <div class="fileUpload">
									                    <i class="zmdi zmdi-hc-lg zmdi-camera"></i>
									                    <input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
									                </div>
									            </div>
									        </div>
									        <a  href="javascript:void(0)" class="saveimg btn btn-save image_save_btn image_save dis-none">
									        <span class="zmdi zmdi-check"></span>
									        </a>
									        <a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash removeimg">
									        <i class="mdi mdi-close"></i>	
									        </a>
									    </div>
									</div>
								</div>
								<div class="share-summery">											
									<div class="travad-title pageendorseheader endorsement_create_head">Write a headline of your ad</div>
									<div class="travad-subtitle pageendorsetext endorsement_create_text">Create personalized text that easily identify your brand name or product</div>
									<div class="travad-info"><span class="pageendorsecount"><?=$pe?></span> people endorsed this page</div>
									<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn waves-effect waves-light">Endorse</a>
								</div>
							</div>																		
						</div>	
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
	</li>
	</ul>
</div>
												</div>
												
											</div>
										</div>
									</div>
								</div>
								<div class="adstep-holder hasDivider">
									<div class="adsec-divider"> 
									</div>
									<div class="adstep-title">
										<img src="<?=$baseUrl?>/images/adstep-audience.png"/>
										<h6>Target your audience</h6>
										<p>Create your audience details to achieve the desired daily reach</p>
									</div>
									<?php $form = ActiveForm::begin(['id' => 'ads-step-2','options'=>['onsubmit'=>'return false;',],]); ?>
									<div class="adstep-details">
										<div class="row">
											<div class="col l7 m6 s12">
												<div class="audience-form">
													<div class="frow">
														<div class="caption-holder">
															<label>Locations <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														</div>
														<div class="detail-holder dropdown782" id="countrycodedropdown">
															<div class="sliding-middle-custom anim-area underlined fullwidth">
																<select data-fill="n" data-selectore="countrydrp" data-action="country" class="countrydrp select2" style='width: 100%' multiple ='multiple' id='ads_loc' onchange='change_audience()'>
																	<option value="" disabled selected>Choose location</option>
																</select>
															</div>
														</div>
													</div>
													<div class="frow">
														<div class="caption-holder">
															<label>Age <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														</div>
														<div class="detail-holder">
															<div class="range-slider range-slider2">
																<input type="text" class="amount" readonly>
																<div id="test-slider"></div>
															</div>
														</div>
													</div>
													<div class="frow">
														<div class="caption-holder">
															<label>Language <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here&lt;br /&gt;some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														</div>
														<div class="detail-holder" id="languagedropdown">
															<div class="sliding-middle-custom anim-area underlined fullwidth dropdown782">
																<select class='select2 languagedrp' data-selectore="languagedrp" data-fill="true" data-action="language" style='width: 100%' multiple ='multiple' id='ads_lang' onchange='change_audience()'>
																	<option value="" disabled selected>Choose language</option>
																</select>
															</div>
														</div>
													</div>
													<div class="frow">
														<div class="caption-holder">
															<label>Gender <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														</div>
														<div class="detail-holder">
															<ul class="checkul">
																<li>
																	<input type="radio" id="All" class="All" name="adGender" checked value="All"/>
																	<label for="All">All</label>
																</li>
																<li>
																	<input type="radio" id="Male" class="Male" name="adGender"  value="Male"/>
																	<label for="Male">Male</label>
																</li>
																<li>
																	<input type="radio" id="Female" class="Female" name="adGender"  value="Female"/>
																	 <label for="Female">Female</label>
																</li>
															</ul>
														</div>
													</div>
													<div class="frow">
														<div class="caption-holder">
															<label>Proficient <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														</div>
														<div class="detail-holder dropdown782" id="occupationdropdown">
															<div class="sliding-middle-custom anim-area underlined fullwidth">
																<select style='width: 100%' multiple ='multiple' data-fill="n" data-selectore="proficientdrp" data-action="occupation" class="proficientdrp select2" id='ads_pro' onchange='change_audience()'>
																	<option value="" disabled selected>Choose proficient</option>
																</select>
															</div>
														</div>
													</div>
													<div class="frow">
														<div class="caption-holder">
															<label>Interest <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
														</div>
														<div class="detail-holder dropdown782" id="interestsdropdown">
															<div class="sliding-middle-custom anim-area underlined fullwidth">
																<select data-fill="n" data-selectore="interestdrp" data-action="interest" class="interestdrp select2" style='width: 100%' multiple ='multiple' id='ads_int' onchange='change_audience()'>
																	<option value="" disabled selected>Choose interest</option>
																</select>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col l5 m6 s12 right">
												<div class="estimate-box">  
													<h5>Estimated Target Audience</h5>
													<div class="databox">
														<div class="meter-holder">
															<div class="gauge2 audience-meter"></div>
														</div>
														<p>Audience selection is <span>fairly broad</span></p>
													</div>
													<div class="databox"> 
														<h6>Audience details</h6>
														<div class="frow"> 
															<div class="caption-holder"><label>Location</label></div>
															<div class="detail-holder" id="eta_location"><p></p></div>
														</div>
														<div class="frow">
															<div class="caption-holder"><label>Age</label></div>
															<div class="detail-holder" id="eta_age"><p>0 - 100</p></div>
														</div>
														<div class="frow">
															<div class="caption-holder"><label>Language</label></div>
															<div class="detail-holder" id="eta_language"><p></p></div>
														</div>
														<div class="frow">
															<div class="caption-holder"><label>Gender</label></div>
															<div class="detail-holder" id="eta_gender"><p>All</p></div>
														</div>
													</div>
													<h6>Estimated Daily Reach</h6>
													<div class="frow">
														<div class="range-slider fixmin-slider">
															<div id="test-slider1"></div>	
														</div>
														<div class="clear"></div>
														<label class="max-amount" readonly><span id="tgt_aud_cnt"><?=$totaltravpeople?></span> people on Iaminjapan</label>
													</div>
													<p class="note">This is just an estimate daily reach. Actual one may vary based on members response to your ad</p>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="adstep-holder hasDivider budget_pricing">
									<div class="adsec-divider">
									</div>
									<div class="adstep-title">
										<img src="<?=$baseUrl?>/images/adstep-pricing.png"/>
										<h6>Budget and Pricing</h6>
										<p>Adjust your budget and set an affordable pricing for your ads</p>										
									</div>
									<div class="adstep-details">
										<div class="pricing-form">
											<div class="frow margin_b6">
												<div class="caption-holder">
													<label>Average Cost <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
												</div>
												<div class="detail-holder">
													<?php if($isvip){ ?>
														<div class="vip-priceinfo">
															Being a VIP member, we are happy to provide you with 20% discount on all the advert price
														</div>
													<?php } ?>
													<ul class="adcost-ul">
														<li>
															<div class="details">
																<h6>Cost per click (CPC)</h6>
																<p>Pay when your ad is clicked through to your website</p>
															</div>
															<div class="costs">															
																<?php if($isvip){ ?>
																	<span class="linethrough">$<?=sprintf ("%.2f",$click+0.10);?></span>
																	<span class="cost">$<?=sprintf ("%.2f",$click);?></span>
																<?php } else { ?>
																	<span class="cost">$<?=sprintf ("%.2f",$click);?></span>
																<?php } ?>
															</div>
														</li>
														<li>
															<div class="details">
																<h6>Cost per engagement (CPE)</h6>
																<p>Pay when your ad is liked or shared</p>
															</div>
															<div class="costs">																
																<?php if($isvip){ ?>
																	<span class="linethrough">$<?=sprintf ("%.2f",$action+0.05);?></span>
																	<span class="cost">$<?=sprintf ("%.2f",$action);?></span>
																<?php } else { ?>
																	<span class="cost">$<?=sprintf ("%.2f",$action);?></span>
																<?php } ?>
															</div>
														</li>
														<li>
															<div class="details">
																<h6>Cost per impressions (CPM)</h6>
																<p>Cost per 1000 user views</p>
															</div>
															<div class="costs">																
																<?php if($isvip){ ?>
																	<span class="linethrough">$<?=sprintf ("%.2f",$impression+0.60);?></span>
																	<span class="cost">$<?=sprintf ("%.2f",$impression);?></span>
																<?php } else { ?>
																	<span class="cost">$<?=sprintf ("%.2f",$impression);?></span>
																<?php } ?>
															</div>
														</li>
													</ul>
												</div>
											</div>
											<div class="frow">
												<div class="caption-holder">
													<label>Daily Budget <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
												</div>
												<div class="detail-holder">
													<div class="sliding-middle-custom anim-area underlined budgetinput">
														$<input type="text" class="budget-text"  placeholder="1.00" id="min_budget">
													</div>
													<p class="note">Minimum budget : $1.00</p>
												</div>
											</div>
											<div class="frow">
												<div class="caption-holder">
													<label>Schedule <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
												</div>
												<div class="detail-holder">
													<ul class="radioul">
														<li>
															<div class="radio-holder">
																<label class="control control--radio">&nbsp;
																  <input type="radio" name="radio1" id="daily" checked="true" value="startdate"/>
																  <div class="control__indicator"></div>
																</label>
																<p>Run my advert set contineously starting today until</p>
																<div class="sliding-middle-custom anim-area underlined adddate">
																	<input type="text" placeholder="End date" class="form-control datepickerinput" id="daily_date" data-query="M" data-toggle="datepicker" readonly>
																</div>
															</div>
														</li>
														<li>
																<div class="radio-holder">
																	<label class="control control--radio">&nbsp;
																		<input type="radio" name="radio1" id="manual" value="startenddate"/>
																	  <div class="control__indicator"></div>
																	</label>
																	<p>Set a starting date</p>
																	<div class="sliding-middle-custom anim-area underlined adddate">
																		<input type="text" placeholder="Start date" placeholder="Start date" class="form-control datepickerinput" id="startdate" data-query="M" data-toggle="datepicker" readonly>
																	</div>
																	<span class="full-date">
																	<p>and end date</p>
																	<div class="sliding-middle-custom anim-area underlined adddate">
																		<input type="text" placeholder="End date" class="form-control datepickerinput" id="enddate" data-query="M" data-toggle="datepicker" readonly>
																	</div>
																	</span>
																</div>
															</li>									
													</ul>
												</div>
											</div>											
										</div>
									</div>
								</div>
								<input type="hidden" name="adaction" value="add" id="adaction"/>
								<input type="hidden" name="dif_amount" value="0" id="dif_amount"/>
								<div class="btn-holder">
									<div class="dirinfo left backbtn" data-class="step2" data-ndataclass="step1">
										<a href="javascript:void(0)" onclick="navigateAdDetails(this,'insidenav','step1')" class="btngen-center-align">Back</a>
									</div>
									<div class="dirinfo right" data-class="step2" data-ndataclass="step3">
										<a href="javascript:void(0)" id="goPayment" onclick="setVars()" class="btngen-center-align waves-effect">Place Order</a>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>		
		</div>
	</div>
	<?php include('../views/layouts/footer.php'); ?>
</div>
 
<div id="payment_popup" class="modal credit-payment-modal compose_inner_modal payment-popup fullpopup dis-none-popup ads_payment_popup" style="overflow-y: scroll;">
</div>  

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>
<?php include('../views/layouts/commonjs.php'); ?>

<script src="<?=$baseUrl?>/js/jquery-gauge.min.js" type="text/javascript"></script>
<script src='<?=$baseUrl?>/js/wNumb.min.js'></script>
<script src="<?=$baseUrl?>/js/jquery.cropit.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/create-ads.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/advertisement.js"></script> 
