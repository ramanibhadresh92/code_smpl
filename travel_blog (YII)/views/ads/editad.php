
<?php 

use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use frontend\models\LoginForm;
use frontend\models\CountryCode;
use frontend\models\Occupation;
use frontend\models\Interests;
use frontend\models\Language;
use frontend\models\Vip;
use frontend\models\PageRoles;
use frontend\models\Like;
use frontend\models\PageEndorse;
use frontend\models\UserMoney;

$baseUrl = AppAsset::register($this)->baseUrl;

$rand = rand(999, 99999).time();
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$totaltravpeople = LoginForm::find()->where(['status' => '1'])->count();

$isvip = Vip::isVIP((string)$user_id);
$impression = $this->context->getadrate($isvip,'impression');
$action = $this->context->getadrate($isvip,'action');
$click = $this->context->getadrate($isvip,'click');

$settings_icon = '<div class="settings-icon"><div class="dropdown dropdown-custom dropdown-med"><a href="javascript:void(0)" class="dropdown-button more_btn" data-activates="Sponsored_2'.$rand.'"><i class="zmdi zmdi-more"></i></a><ul id="Sponsored_2'.$rand.'" class="dropdown-content custom_dropdown"><li class="nicon"><a href="javascript:void(0)">Hide ad</a></li><li class="nicon"><a href="javascript:void(0)">Save ad</a></li><li class="nicon"><a href="javascript:void(0)">Mute this seller ads</a></li><li class="nicon"><a href="javascript:void(0)">Report ad</a></li></ul></div></div>';

$adobj = $ad['adobj'];
$advrt_id = $ad['_id'];

$adtotbudget = $ad['adtotbudget'];

$ad_duration = $ad['ad_duration'];
if(isset($ad_duration) && !empty($ad_duration)){
	$daily_budget = $adtotbudget / $ad_duration;
	$daily_budget = number_format($daily_budget,2);
}
else
{
	$daily_budget = $ad['adtotbudget'];
	$daily_budget = number_format($daily_budget,2);
}

$adlocations = $ad['adlocations'];
$ads_loc = '';
if(isset($adlocations) && $adlocations != '') {
	$ads_loc .= '"';
	$ads_loc .= str_replace(",", '","', $adlocations);
	$ads_loc .= '"';	
}

$adlanguages = $ad['adlanguages'];
$ads_lang = '';
if(isset($adlanguages) && $adlanguages != '') {
	$ads_lang .= '"';
	$ads_lang .= str_replace(",", '","', $adlanguages);
	$ads_lang .= '"';	
}

$adpro = $ad['adpro'];
$ads_pro = '';
if(isset($adpro) && $adpro != '') {
	$ads_pro .= '"';
	$ads_pro .= str_replace(",", '","', $adpro);
	$ads_pro .= '"';	
}

$adint = $ad['adint'];
$ads_int = '';
if(isset($adint) && $adint != '') {
	$ads_int .= '"';
	$ads_int .= str_replace(",", '","', $adint);
	$ads_int .= '"';	
}

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

$today_date = date('d-m-Y');

$this->title = 'Edit Ad';
?>

<input type="hidden" id="totlatravusers" value="<?=$totaltravpeople?>">
<input type="hidden" id="adobj" value="<?=$adobj?>">
<input type="hidden" id="advert_name" value="<?=$ad['adname']?>">
<input type="hidden" id="pagename" value="edit">
<input type="hidden" id="adid" value="<?=$ad['_id']?>">
<input type="hidden" value="<?=$today_date?>" id="today_date">
<div class="edit-holder nice-scroll">
	<div class="advertmanager-page">
		<div class="detail-box">
			<div class="travadvert-section active">
				<div class="travad-accordion travad-details">
					<div class="main_edt_title">
						<h3 class="sub-title"><?=$ad['adname']?></h3>
						<div class="dirinfo right backbtn btn_back_cus">
							<a href="javascript:void(0)" onclick="closeEditAd()" class="btn btn-primary btn-md left"><i class="zmdi zmdi-chevron-left"></i></a>
						</div>
					</div>
					<div class="main-accContent">
						<?php if($adobj == 'pagelikes'){
							$pageid = $ad['adid'];
							$pl = Like::getLikeCount($pageid);
							$adcatch = $ad['adcatch'];
							$adheadeline = $ad['adheadeline'];
							$adtext = $ad['adtext'];
							$adimage = $ad['adimage'];
							$adpages = ArrayHelper::map(PageRoles::getAdsPages($user_id), function($data) { return (string)$data['page']['_id'];}, function($data) { return $data['page']['page_name'];} );
							$adpages = array_filter($adpages);
							if($adimage == 'undefined')
							{
								$adimage = $baseUrl.'/images/pagead-demo.png';
							}
							$pagename = $this->context->getpagename($pageid);
							$pageimage = $this->context->getpageimage($pageid); 
						?>
						<!-- page likes -->									
<div class="travad-detailbox pagelikes">
	<div class="row arrange_row">
		<div class="col l5 m5 s12 detail-part">
			<div class="frow more_m5">
				<label>Select a page to advert</label>
				<div class="sliding-middle-custom anim-area underlined fullwidth select_change">
					<select class="select2" id="pagelikenames" onchange="pageSelect('pagelikenames','pagelikescount','pagelikesimage','pagelikestext','pl')">
						<?php
						if(empty($adpages)) {
							echo "<option>No Page</option>";
						} else {
							foreach($adpages as $key => $adpage) { 
								if($key == $pageid) {
									echo "<option value='".$key."' selected>".$adpage."</option>";
								} else {
									echo "<option value='".$key."'>".$adpage."</option>";
								}
							} 
						}
						?>
					</select>
				</div>
			</div>
			<div class="frow remove_m10">
				<label>Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
				<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th50">
					<textarea maxlength="70" class="materialize-textarea mb0 md_textarea descinput catch_phase" placeholder="Add a few sentences to catch people's attention to your ad" data-length="70" id="pagelikescatch"><?=$adcatch?></textarea>
				</div>
			</div>
			<div class="frow more_m5">
				<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
				<div class="sliding-middle-custom underlined anim-area fullwidth">
					<input type="text" class="fullwidth change_head" placeholder="Write a headline of your ad" onkeyup="adHeader('pagelikesheader')" id="pagelikesheader" value="<?=$adheadeline?>">
				</div>
			</div>
			<div class="frow">
				<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
				<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
					<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput sub_title_you_ad" placeholder="Short description of your ad" data-length="140" id="pagelikestext"><?=$adtext?></textarea>
				</div>
			</div>
		</div>
		
		<div class="col l7 m7 s12 preview-part">
			<div class="frow top-sec">
				<label>Advert Preview</label>
				<div class="settings-icon">
					<div class="dropdown dropdown-custom dropdown-med resist">
						<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="xsetting_btn2<?=$rand?>">
						  <i class="zmdi zmdi-more"></i>
						</a>
						<ul id="xsetting_btn2<?=$rand?>" class="dropdown-content custom_dropdown">
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
					<div class="post-holder travad-box page-travad">
						<div class="post-topbar">
							<div class="post-userinfo">
								<div class="img-holder">
									<div id="profiletip-4" class="profiletipholder">
										<span class="profile-tooltip">
											<img class="circle" src="<?=$pageimage?>" class="pagelikesimage"/>
										</span>
									</div>
									
								</div>
								<div class="desc-holder">
									<a href="javascript:void(0)" class="pagelikenames head_chtitle"><?=$pagename?></a>
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
											<p class="pagelikescatch change_ad_word"><?=$adcatch?></p>
										</div>	
										<div class="post-img-holder">
											<div class="post-img one-img">
												<div class="pimg-holder"><img class="pagelikes" src="<?=$adimage?>"/></div>
											</div>
										</div>
										<div class="share-summery">											
											<div class="travad-title pagelikesheader"><?=$adheadeline?></div>
											<div class="travad-subtitle pagelikestext"><?=$adtext?></div>
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
						<h4 class="collapsible-header">Right Column (Desktop)</h4>
						<div class="collapsible-body">
						<div>
						<div class="adpreview-holder rcolumn-d">
							<div class="content-box">
								<div class="cbox-desc">
									<div class="side-travad page-travad">
										<div class="travad-maintitle"><img class="pagelikesimage" src="<?=$pageimage?>"><h6 class="pagelikenames"><?=$pagename?></h6><span>Sponsored</span></div>
										<div class="post-details">
				                          <p class="change_ad_word"><?=$adtext?></p>
				                       </div>
										<div class="imgholder adsection_img">
											<img class="pagelikes" src="<?=$adimage?>"/>
										</div>
										<div class="descholder">
											<div class="travad-title pagelikesheader"><?=$adheadeline?></div>
											<div class="travad-subtitle pagelikestext"><?=$adtext?></div>
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
													<img class="circle pagelikesimage" src="<?=$pageimage?>">
												</span>
											</div>
											
										</div>
										<div class="desc-holder">
											<a href="javascript:void(0)" class="pagelikenames head_chtitle"><?=$pagename?></a>
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
													<p class="pagelikescatch change_ad_word"><?=$adcatch?></p>
				 								</div>	
												<div class="post-img-holder">
													<div class="post-img one-img">
														<div class="pimg-holder"><img class="pagelikes" src="<?=$adimage?>"/></div>
													</div>
												</div>
												<div class="share-summery">											
													<div class="travad-title pagelikesheader"><?=$adheadeline?></div>
													<div class="travad-subtitle pagelikestext"><?=$adtext?></div>
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
													<img class="circle pagelikesimage" src="<?=$pageimage?>"/>
												</span>
											</div>
										</div>
										<div class="desc-holder">
											<a href="javascript:void(0)" class="pagelikenames"><?=$pagename?></a>
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
													<p class="pagelikescatch change_ad_word"><?=$adcatch?></p>
												</div>	
<!-- <div class="post-img-holder">
	<div class="post-img one-img">
		<div class="pimg-holder"><img class="pagelikes" src="<?=$adimage?>"/></div>
	</div>
</div> -->

<div class="post-img-holder">
	<div class="post-img one-img pos_rel"> 
		<div class="crop-holder mian-crop1 image-cropperGeneralDsk ">
	        <div class="cropit-preview"></div>
	        <div class="main-img pimg-holder">
	            <img src="<?=$adimage?>" class="ui-corner-all pagelikes"/>
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
													<div class="travad-title pagelikesheader"><?=$adheadeline?></div>
													<div class="travad-subtitle pagelikestext"><?=$adtext?></div>
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
						<?php } if($adobj == 'brandawareness'){
							$adcatch = $ad['adcatch'];
							$adtext = $ad['adtext'];
							$adurl = $ad['adurl'];
							$adimage = $ad['adimage'];
							if($adimage == 'undefined')
							{
								$adimage = $baseUrl.'/images/brandaware-demo.png';
							}
						?>
						<!-- brand awareness -->									
						<div class="travad-detailbox brandawareness">
							<div class="row">
								<div class="col l5 m5 s12 detail-part">
									<div class="frow remove_m10">
										<label>Branding Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder">
											<input type='text' maxlength="50" class="fullwidth" placeholder="Slogan your brand in a few words" data-length="50" id="brandawarenesscatch" value="<?=$adcatch?>"/>
										</div>
									</div>
									<div class="frow remove_m10">
										<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
											<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput aware_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="brandawarenesstext"><?=$adtext?></textarea>
										</div>
									</div>
									<div class="frow remove_m10">
										<label>Website URL <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth advert_url" placeholder="Add your website URL" id="brandawarenesssite" value="<?=$adurl?>">
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
												<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_btn3<?=$rand?>">
								                  <i class="zmdi zmdi-more"></i>
								                  </a>
								                  <ul id="setting_btn3<?=$rand?>" class="dropdown-content custom_dropdown">
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
											<div class="post-holder travad-box brand-travad">
												<div class="post-topbar">
													<div class="post-userinfo change_awar_head">
														Best coffee in the world!
													</div>
													<?=$settings_icon?>
												</div>
												<div class="post-content">							
													<div class="shared-box shared-category">
														<div class="post-holder">			
															<div class="post-content">
																<div class="post-img-holder">
																	<div class="post-img one-img">
																		<div class="pimg-holder"><img class="brandawareness" src="<?=$adimage?>"/></div>
																	</div>
																</div>
																<div class="share-summery">	
																	<div class="travad-subtitle brandawarenesstext change_awar_text"><?=$adtext?></div>
																	<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
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
														<div class="side-travad brand-travad">
															<div class="travad-maintitle brandawarenesscatch change_awar_head"><?=$adcatch?></div>
															<div class="imgholder">
																<img class="brandawareness" src="<?=$adimage?>">
															</div>
															<div class="descholder">								
																<div class="travad-subtitle brandawarenesstext change_awar_text waves-effect waves-light"><?=$adtext?></div>
																<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
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
															<?=$adcatch?>
														</div>
														<?=$settings_icon?>
													</div>
													<div class="post-content">							
														<div class="shared-box shared-category">								
															<div class="post-holder">									
																<div class="post-content">
																	<div class="post-img-holder">
																		<div class="post-img one-img">
																			<div class="pimg-holder"><img class="brandawareness" src="<?=$adimage?>"/></div>
																		</div>
																	</div>
																	<div class="share-summery">	
																		<div class="travad-subtitle brandawarenesstext change_awar_text"><?=$adtext?></div>
																		<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
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
										<h4 class="collapsible-header active last-item">Stream Feeds (Desktop)</h4>
						                  <div class="collapsible-body">
						                     <div>
											<div class="adpreview-holder sfeed-d">
												<div class="post-holder travad-box brand-travad">
													<div class="post-topbar">
														<div class="post-userinfo brandawarenesscatch change_awar_head">
															<?=$adcatch?>
														</div>
														<?=$settings_icon?>
													</div>
													<div class="post-content">							
														<div class="shared-box shared-category">	
															<div class="post-holder">				
																<div class="post-content">
																	<div class="post-img-holder">
																		<div class="post-img one-img">
																			<div class="pimg-holder"><img class="brandawareness" src="<?=$adimage?>"/></div>
																		</div>
																	</div>
																	<div class="share-summery">	
																		<div class="travad-subtitle brandawarenesstext change_awar_text"><?=$adtext?></div>
																		<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
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
									</ul>
									</div>
								</div>
							</div>
						</div>										
						<?php } if($adobj == 'websiteleads'){
							$adtitle = $ad['adtitle'];
							$adlogo = $ad['adlogo'];
							$adcatch = $ad['adcatch'];
							$adheadeline = $ad['adheadeline'];
							$adtext = $ad['adtext'];
							$adurl = $ad['adurl'];
							$adimage = $ad['adimage'];
							if($adlogo == 'undefined')
							{
								$adlogo = $baseUrl.'/images/demo-business.jpg';
							}
							if($adimage == 'undefined')
							{
								$adimage = $baseUrl.'/images/webleads-demo.png';
							}
						?>
						<!-- website leads -->									
						<div class="travad-detailbox websiteleads">
							<div class="row">
								<div class="col l5 m5 s12 detail-part">
									<div class="frow">
										<label>Advert Title <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth advert_title" placeholder="Add your advert title" onkeyup="adHeader('websiteleadstitle')" id="websiteleadstitle" value="<?=$adtitle?>">
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
											<textarea  maxlength="70" class="materialize-textarea mb0 md_textarea descinput advert_phrase" placeholder="Slogan your brand in a few words" data-length="70" id="websiteleadscatch"><?=$adcatch?></textarea>
										</div>
									</div>
									<div class="frow">
										<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth advert_head" placeholder="Write a headline of your ad" onkeyup="adHeader('websiteleadsheader')" id="websiteleadsheader" value="<?=$adheadeline?>">
										</div>
									</div>	
									<div class="frow">
										<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
											<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput advert_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="websiteleadstext"><?=$adtext?></textarea>
										</div>
									</div>
									<div class="frow">
										<label>Website URL<a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth advert_url" placeholder="Add your website URL" onkeyup="adHeader('websiteleadssite')" id="websiteleadssite" value="<?=$adurl?>">
										</div>
									</div>	
								</div>
								
								<div class="col l7 m7 s12 preview-part">
									<div class="frow top-sec">
										<label>Advert Preview</label>
										<div class="settings-icon">
											<div class="dropdown dropdown-custom dropdown-med resist">
												<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="xsetting_btn4<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
						                  		<ul id="xsetting_btn4<?=$rand?>" class="dropdown-content custom_dropdown">
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
											<div class="post-holder travad-box weblink-travad">
												<div class="post-topbar">
													<div class="post-userinfo">
														<div class="img-holder">
															<div id="profiletip-4" class="profiletipholder">
																<span class="profile-tooltip">
																	<img class="circle websiteleadslogo" src="<?=$adlogo?>"/>
																</span>
															</div>
														</div>
														<div class="desc-holder">
															<div class="travad-maintitle websiteleadstitle lead_title"><?=$adtitle?></div>
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
																	<p class="websiteleadscatch"><?=$adcatch?></p>							
																</div>	
																<div class="post-img-holder">
																	<div class="post-img one-img">
																		<div class="pimg-holder"><img class="websiteleads" src="<?=$adimage?>"/></div>
																	</div>
																</div>
																<div class="share-summery">											
																	<div class="travad-title websiteleadsheader lead_head"><?=$adheadeline?></div>
																	<div class="travad-subtitle websiteleadstext lead_text"><?=$adtext?></div>
																	<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite"><?=$adurl?></span></a>
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
														<div class="side-travad weblink-travad">
															<div class="travad-maintitle"><img class="websiteleadslogo" src="<?=$adlogo?>"><h6 class="websiteleadstitle"><?=$adtitle?></h6><span>Sponsored</span></div>
															<div class="imgholder">
																<img class="websiteleads" src="<?=$adimage?>"/>
															</div>
															<div class="descholder">								
																<div class="travad-title websiteleadsheader lead_head"><?=$adheadeline?></div>
																<div class="travad-subtitle websiteleadstext lead_text"><?=$adtext?></div>
																<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite"><?=$adurl?></span></a>
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
																		<img class="circle websiteleadslogo" src="<?=$adlogo?>"/>
																	</span>
																</div>
															</div>
															<div class="desc-holder">
																<div class="travad-maintitle websiteleadstitle lead_title"><?=$adtitle?></div>
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
																		<p class="websiteleadscatch lead_phrase"><?=$adcatch?></p>
																	</div>	
																	<div class="post-img-holder">
																		<div class="post-img one-img">
																			<div class="pimg-holder"><img class="websiteleads" src="<?=$adimage?>"/></div>
																		</div>
																	</div>
																	<div class="share-summery">											
																		<div class="travad-title websiteleadsheader lead_head"><?=$adheadeline?></div>
																		<div class="travad-subtitle websiteleadstext lead_text"><?=$adtext?></div>
																		<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite"><?=$adurl?></span></a>
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
												<div class="post-holder travad-box weblink-travad">
													<div class="post-topbar">
														<div class="post-userinfo">
															<div class="img-holder">
																<div id="profiletip-4" class="profiletipholder">
																	<span class="profile-tooltip">
																		<img class="circle websiteleadslogo" src="<?=$adlogo?>"/>
																	</span>
																</div>
																
															</div>
															<div class="desc-holder">
																<div class="travad-maintitle websiteleadstitle lead_title"><?=$adtitle?></div>
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
																		<p class="websiteleadscatch"><?=$adcatch?></p>
																	</div>	
																	<div class="post-img-holder">
																		<div class="post-img one-img">
																			<div class="pimg-holder"><img class="websiteleads" src="<?=$adimage?>"/></div>
																		</div>
																	</div>
																	<div class="share-summery">											
																		<div class="travad-title websiteleadsheader lead_head"><?=$adheadeline?></div>
																		<div class="travad-subtitle websiteleadstext lead_text"><?=$adtext?></div>
																		<a href="javascript:void(0)" class="adlink lead_url"><i class="mdi mdi-earth"></i><span class="websiteleadssite"><?=$adurl?></span></a>
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
						<?php } if($adobj == 'websiteconversion'){
							$adtitle = $ad['adtitle'];
							$adlogo = $ad['adlogo'];
							$adcatch = $ad['adcatch'];
							$adheadeline = $ad['adheadeline'];
							$adtext = $ad['adtext'];
							$adbtn = $ad['adbtn'];
							$adurl = $ad['adurl'];
							$adimage = $ad['adimage'];
							if($adlogo == 'undefined')
							{
								$adlogo = 'images/demo-business.jpg';
							}
							if($adimage == 'undefined')
							{
								$adimage = 'images/webconversion-demo.png';
							}
						?>
						<!-- Website Conversion -->									
						<div class="travad-detailbox websiteconversion">
							<div class="row">
								<div class="col l5 m5 s12 detail-part">
									<div class="frow more_m5">
										<label>Advert Title <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth conversion_title" placeholder="Add your advert title" onkeyup="adHeader('websiteconversiontitle')" id="websiteconversiontitle" value="<?=$adtitle?>">
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
											<textarea  maxlength="70" class="materialize-textarea mb0 md_textarea descinput conversion_phrase" placeholder="Slogan your brand in a few words" data-length="70" id="websiteconversioncatch"><?=$adcatch?></textarea>
										</div>
									</div>
									<div class="frow more_m5">
										<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth conversion_head" placeholder="Write a headline of your ad" onkeyup="adHeader('websiteconversionheader')" id="websiteconversionheader" value="<?=$adheadeline?>">
										</div>
									</div>	
									<div class="frow remove_m10">
										<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
											<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput conversion_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="websiteconversiontext"><?=$adtext?></textarea>
										</div>
									</div>
									<div class="frow">
										<label>Select action button</label>
										<div class="btndrop">
											<select class="select2" id="websiteconversiontype" onchange="adHeader('websiteconversiontype')">
												<option <?php if($adbtn == 'Book Now'){?>selected<?php } ?>>Book Now</option>
												<option <?php if($adbtn == 'Shop Now'){?>selected<?php } ?>>Shop Now</option>
												<option <?php if($adbtn == 'Explore'){?>selected<?php } ?>>Explore</option>
												<option <?php if($adbtn == 'Learn More'){?>selected<?php } ?>>Learn More</option>
												<option <?php if($adbtn == 'Contact Us'){?>selected<?php } ?>>Contact Us</option>
												<option <?php if($adbtn == 'Sign Up'){?>selected<?php } ?>>Sign Up</option>
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
												<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="xsetting_btn5<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
						                  		<ul id="xsetting_btn5<?=$rand?>" class="dropdown-content custom_dropdown">
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
											<div class="post-holder travad-box actionlink-travad">
												<div class="post-topbar">
													<div class="post-userinfo">
														<div class="img-holder">
															<div id="profiletip-4" class="profiletipholder">
																<span class="profile-tooltip">
																	<img class="circle websiteconversionlogo" src="<?=$adlogo?>"/>
																</span>
															</div>
															
														</div>
														<div class="desc-holder">
															<div class="travad-maintitle websiteconversiontitle con_create_title"><?=$adtitle?></div>
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
																	<p class="websiteconversioncatch con_create_phrase"><?=$adcatch?></p>
																</div>	
																<div class="post-img-holder">
																	<div class="post-img one-img">
																		<div class="pimg-holder"><img class="websiteconversion" src="<?=$adimage?>"/></div>
																	</div>
																</div>
																<div class="share-summery">											
																	<div class="travad-title websiteconversionheader con_create_head"><?=$adheadeline?></div>
																	<div class="travad-subtitle websiteconversiontext con_create_text"><?=$adtext?></div>
																	<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
																	<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button"><?=$adbtn?></a>
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
															<div class="side-travad actionlink-travad">
																<div class="travad-maintitle"><img class="websiteconversionlogo" src="<?=$adlogo?>"><h6 class="websiteconversiontitle"><?=$adtitle?></h6><span>Sponsored</span></div>
																<div class="imgholder">
																	<img class="websiteconversionlogo" src="<?=$adlogo?>"/>
																</div>
																<div class="descholder">								
																	<div class="travad-title websiteconversionheader con_create_head"><?=$adheadeline?></div>
																	<div class="travad-subtitle websiteconversiontext con_create_text"><?=$adtext?></div>
																	<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
																	<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button"><?=$adbtn?></a>
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
																					<img class="circle websiteconversionlogo" src="<?=$adlogo?>"/>
																				</span>
																			</div>
																			
																		</div>
																		<div class="desc-holder">
																			<div class="travad-maintitle websiteconversiontitle con_create_title"><?=$adtitle?></div>
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
																					<p class="websiteconversioncatch con_create_phrase"><?=$adcatch?></p>
																				</div>	
																				<div class="post-img-holder">
																					<div class="post-img one-img">
																						<div class="pimg-holder"><img class="websiteconversion" src="<?=$adimage?>"/></div>
																					</div>
																				</div>
																				<div class="share-summery">											
																					<div class="travad-title websiteconversionheader con_create_head"><?=$adheadeline?></div>
																					<div class="travad-subtitle websiteconversiontext con_create_text"><?=$adtext?></div>
																					<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
																					<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button"><?=$adbtn?></a>
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
													<div class="post-holder travad-box actionlink-travad">
														<div class="post-topbar">
															<div class="post-userinfo">
																<div class="img-holder">
																	<div id="profiletip-4" class="profiletipholder">
																		<span class="profile-tooltip">
																			<img class="circle websiteconversionlogo" src="<?=$adlogo?>"/>
																		</span>
																	</div>
																	
																</div>
																<div class="desc-holder">
																	<div class="travad-maintitle websiteconversiontitle con_create_title"><?=$adtitle?></div>
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
																			<p class="websiteconversioncatch con_create_phrase"><?=$adcatch?></p>
																		</div>	
																		<div class="post-img-holder">
																			<div class="post-img one-img">
																				<div class="pimg-holder"><img class="websiteconversion" src="<?=$adimage?>"/></div>
																			</div>
																		</div>
																		<div class="share-summery">											
																			<div class="travad-title websiteconversionheader con_create_head"><?=$adheadeline?></div>
																			<div class="travad-subtitle websiteconversiontext con_create_text"><?=$adtext?></div>
																			<a href="javascript:void(0)" class="adlink lead_url"><?=$adurl?></a>
																			<a href="javascript:void(0)" class="btn btn-primary adbtn websiteconversiontype con_create_button"><?=$adbtn?></a>
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
						<?php } if($adobj == 'inboxhighlight'){
							$adtitle = $ad['adtitle'];
							$adlogo = $ad['adlogo'];
							$adcatch = $ad['adcatch'];
							$adsubcatch = $ad['adsubcatch'];
							$adheadeline = $ad['adheadeline'];
							$adtext = $ad['adtext'];
							$adbtn = $ad['adbtn'];
							$adurl = $ad['adurl'];
							$adimage = $ad['adimage'];
							if($adlogo == 'undefined')
							{
								$adlogo = 'images/demo-business.jpg';
							}
							if($adimage == 'undefined')
							{
								$adimage = 'images/inboxad-demo.png';
							}
						?>
						<!-- inbox highlight -->									
						<div class="travad-detailbox inboxhighlight">
							<div class="row">
								<div class="col l5 m5 s12 detail-part">
									<div class="frow more_m5">
										<label>Advert Title <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth" placeholder="Add your advert title" id="inboxhighlighttitle" onkeyup="adHeader('inboxhighlighttitle')" value="<?=$adtitle?>">
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
											<input type='text' maxlength="30" class="fullwidth inbox_main_catch" placeholder="Add your main catch phrase" data-length="30" id="inboxhighlightcatch" value="<?=$adcatch?>"/>
										</div>
									</div>
									<div class="frow remove_m10">
										<label>Sub Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder">
											<input type='text' maxlength="35" class="fullwidth inbox_sub_catch" placeholder="Add your sub catch phrase" data-length="35" id="inboxhighlightsubcatch" value="<?=$adsubcatch?>">
										</div>
									</div>
									<div class="frow more_m5">
										<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth inbox_head" placeholder="Write a headline of your ad" id="inboxhighlightheader" onkeyup="adHeader('inboxhighlightheader')" value="<?=$adheadeline?>">
										</div>
									</div>	
									<div class="frow remove_m10">
										<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
											<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput inbox_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="inboxhighlighttext"><?=$adtext?></textarea>
										</div>
									</div>
									<div class="frow">
										<label>Select action button</label>
										<div class="btndrop">
											<select class="select2" id="inboxhighlighttype" onchange="adHeader('inboxhighlighttype')">
												<option <?php if($adbtn == 'Shop Now'){ ?>selected<?php } ?>>Shop Now</option>
												<option <?php if($adbtn == 'Book Now'){ ?>selected<?php } ?>>Book Now</option>
												<option <?php if($adbtn == 'Explore'){ ?>selected<?php } ?>>Explore</option>
												<option <?php if($adbtn == 'Learn More'){ ?>selected<?php } ?>>Learn More</option>
												<option <?php if($adbtn == 'Contact Us'){ ?>selected<?php } ?>>Contact Us</option>
												<option <?php if($adbtn == 'Sign Up'){ ?>selected<?php } ?>>Sign Up</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col l7 m7 s12 preview-part">
									<div class="frow top-sec">
										<label>Advert Preview</label>														
										<div class="settings-icon">
											<div class="dropdown dropdown-custom dropdown-med resist">
												<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="setting_btn6"><i class="zmdi zmdi-more"></i></a>
						                  		<ul id="setting_btn6" class="dropdown-content custom_dropdown">
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
										<div class="adpreview-holder sfeed-d inboxad-preview">
											<div class="summery-adpreview">
												<h5>Summery Ad Preview</h5>
												<ul>
													<li class="inbox-travad">
														<a href="javascript:void(0)" onclick="openMessage(this)" id="ad">
															<span class="muser-holder">
																<span class="imgholder"><img class="inboxhighlightlogo" src="<?=$adlogo?>"/></span>
																<span class="descholder">
																	<h6 class="inboxhighlightcatch inbox_create_main"><?=$adcatch?></h6>
																	<p class="inboxhighlightsubcatch inbox_create_sub"><?=$adsubcatch?></p>
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
															<h4 class="inboxhighlighttitle inbox_create_title"><?=$adtitle?> <span>Sponsored Ad</span></h4>
														</div>
														
													</div>
													<div class="main-msgwindow">
														<div class="allmsgs-holder">				
															<ul class="current-messages">
																
																<li class="mainli active" id="li-travad">
																	<div class="msgdetail-list nice-scroll" tabindex="10">
																		<div class="inbox-travad">
																			<img class="inboxhighlightimage" src="<?=$adimage?>">
																			<h6 class="inboxhighlightheader inbox_create_head"><?=$adheadeline?></h6>
																			<p class="inboxhighlighttext inbox_create_head"><?=$adtext?></p>
																			<a href="javascript:void(0)" class="btn btn-primary btn-sm right inboxhighlighttype inbox_create_button"><?=$adbtn?></a>
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
											<div class="adpreview-holder sfeed-m inboxad-preview">
												
												<div class="summery-adpreview">
													<h5>Summery Ad Preview</h5>
													<ul>
														<li class="inbox-travad">
															<a href="javascript:void(0)" onclick="openMessage(this)" id="ad">
																<span class="muser-holder">
																	<span class="imgholder"><img class="inboxhighlightlogo" src="<?=$adlogo?>"/></span>
																	<span class="descholder">
																		<h6 class="inboxhighlightcatch inbox_create_main"><?=$adcatch?></h6>
																		<p class="inboxhighlightsubcatch inbox_create_sub"><?=$adsubcatch?></p>
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
																<h4 class="inboxhighlighttitle inbox_create_title"><?=$adtitle?><span>Sponsored Ad</span></h4>
															</div>
															
														</div>
														<div class="main-msgwindow">
															<div class="allmsgs-holder">				
																<ul class="current-messages">
																	<li class="mainli active" id="li-travad">
																		<div class="msgdetail-list nice-scroll" tabindex="10">
																			<div class="inbox-travad">
																				<img class="inboxhighlightimage" src="<?=$adimage?>">
																				<h6 class="inboxhighlightheader inbox_create_head"><?=$adheadeline?></h6>
																				<p class="inboxhighlighttext inbox_create_text"><?=$adtext?></p>
																				<a href="javascript:void(0)" class="btn btn-primary btn-sm right inboxhighlighttype inbox_create_button"><?=$adbtn?></a>
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
											<div class="adpreview-holder sfeed-d inboxad-preview">
												
												<div class="summery-adpreview">
													<h5>Summery Ad Preview</h5>
													<ul>
														<li class="inbox-travad">
															<a href="javascript:void(0)" onclick="openMessage(this)" id="ad">
																<span class="muser-holder">
																	<span class="imgholder"><img class="inboxhighlightlogo" src="<?=$adlogo?>"/></span>
																	<span class="descholder">
																		<h6 class="inboxhighlightcatch inbox_create_main"><?=$adcatch?></h6>
																		<p class="inboxhighlightsubcatch inbox_create_sub"><?=$adsubcatch?></p>
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
																<h4 class="inboxhighlighttitle inbox_create_title"><?=$adtitle?><span>Sponsored Ad</span></h4>
															</div>
															
														</div>
														<div class="main-msgwindow">
															<div class="allmsgs-holder">				
																<ul class="current-messages">
																	
																	<li class="mainli active" id="li-travad">
																		<div class="msgdetail-list nice-scroll" tabindex="10">
																			<div class="inbox-travad">
																				<img class="inboxhighlightimage" src="<?=$adimage?>">
																				<h6 class="inboxhighlightheader inbox_create_head"><?=$adheadeline?></h6>
																				<p class="inboxhighlighttext inbox_create_text"><?=$adtext?></p>
																				<a href="javascript:void(0)" class="btn btn-primary btn-sm right inboxhighlighttype inbox_create_button"><?=$adbtn?></a>
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
						<?php } if($adobj == 'pageendorse'){
							$pageid = $ad['adid'];
							$pe = PageEndorse::getAllEndorseCount($pageid);
							$adcatch = $ad['adcatch'];
							$adheadeline = $ad['adheadeline'];
							$adtext = $ad['adtext'];
							$adimage = $ad['adimage'];
							$adpages = ArrayHelper::map(PageRoles::getAdsPages($user_id), function($data) { return (string)$data['page']['_id'];}, function($data) { return $data['page']['page_name'];} );
							$adpages = array_filter($adpages);
							if($adimage == 'undefined')
							{
								$adimage = 'images/pagead-endorse-demo.png';
							}
							$pagename = $this->context->getpagename($pageid);
							$pageimage = $this->context->getpageimage($pageid);
						?>
						<!-- page endorsement -->									
						<div class="travad-detailbox pageendorse">
							<div class="row">
								<div class="col l5 m5 s12 detail-part">
									<div class="frow">
										<label>Select a page to advert</label>
										<div class="sliding-middle-custom anim-area underlined fullwidth">
											<select class="select2" id="pageendorsenames" onchange="pageSelect('pageendorsenames','pageendorsecount','pageendorseimage','pageendorsetext','pe')">
												<?php
												if(empty($adpages)) {
													echo "<option>No Page</option>";
												} else {
													foreach($adpages as $key => $adpage) { 
														if($key == $pageid) {
															echo "<option value='".$key."' selected>".$adpage."</option>";
														} else {
															echo "<option value='".$key."'>".$adpage."</option>";
														}
													} 
												}
												?>
											</select>
										</div>
									</div>
									<div class="frow">
										<label>Catch Phrase <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th50">
											<textarea maxlength="70" class="materialize-textarea mb0 md_textarea descinput endorsement_phrase" placeholder="Slogan your brand in a few words" data-length="70" id="pageendorsecatch"><?=$adcatch?></textarea>
										</div>
									</div>
									<div class="frow">
										<label>Headline <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom underlined anim-area fullwidth">
											<input type="text" class="fullwidth endorsement_head" placeholder="Write a headline of your ad" id="pageendorseheader" onkeyup="adHeader('pageendorseheader')" value="<?=$adheadeline?>">
										</div>
									</div>	
									<div class="frow">
										<label>Text <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										<div class="sliding-middle-custom anim-area underlined fullwidth tt-holder th70">
											<textarea maxlength="140" class="materialize-textarea mb0 md_textarea descinput endorsement_text" placeholder="Create personalized text that easily identify your brand name or product" data-length="140" id="pageendorsetext"><?=$adtext?></textarea>
										</div>
									</div>	
								</div>
								<div class="col l7 m7 s12 preview-part">
									<div class="frow top-sec">
										<label>Advert Preview</label>														
										<div class="settings-icon">
											<div class="dropdown dropdown-custom dropdown-med resist">
							                  <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="xsetting_btn7<?=$rand?>"><i class="zmdi zmdi-more"></i></a>
							                  <ul id="xsetting_btn7<?=$rand?>" class="dropdown-content custom_dropdown">
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
											<div class="post-holder travad-box page-travad">
												<div class="post-topbar">
													<div class="post-userinfo">
														<div class="img-holder">
															<div id="profiletip-4" class="profiletipholder">
																<span class="profile-tooltip">
																	<img class="circle pageendorseimage" src="<?=$pageimage?>"/>
																</span>
															</div>
															
														</div>
														<div class="desc-holder">
															<a href="javascript:void(0)" class="pageendorsenames endorsement_create_value"><?=$pagename?></a>
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
																	<p class="pageendorsecatch endorsement_create_phrase"><?=$adcatch?></p>
																</div>	
																<div class="post-img-holder">
																	<div class="post-img one-img">
																		<div class="pimg-holder"><img class="pageendorse" src="<?=$adimage?>"/></div>
																	</div>
																</div>
																<div class="share-summery">											
																	<div class="travad-title pageendorseheader endorsement_create_head"><?=$adheadeline?></div>
																	<div class="travad-subtitle pageendorsetext endorsement_create_text"><?=$adtext?></div>
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
												<div class="content-box bshadow">

													<div class="cbox-desc">
														<div class="side-travad page-travad">
															<div class="travad-maintitle"><img class="pageendorseimage" src="<?=$pageimage?>"><h6 class="pageendorsenames endorsement_create_value"><?=$pagename?></h6><span>Sponsored</span></div>
															<div class="imgholder">
																<img class="pageendorse" src="<?=$adimage?>"/>
															</div>
															<div class="descholder">								
																<div class="travad-title pageendorseheader endorsement_create_main endorsement_create_head"><?=$adheadeline?></div>
																<div class="travad-subtitle pageendorsetext endorsement_create_text"><?=$adtext?></div>
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
																		<img class="circle pageendorseimage" src="<?=$pageimage?>"/>
																	</span>
																</div>
																
															</div>
															<div class="desc-holder">
																<a href="javascript:void(0)" class="pageendorsenames endorsement_create_value"><?=$pagename?></a>
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
																		<p class="pageendorsecatch endorsement_create_phrase"><?=$adcatch?></p>
																	</div>	
																	<div class="post-img-holder">
																		<div class="post-img one-img">
																			<div class="pimg-holder"><img class="pageendorse" src="<?=$adimage?>"/></div>
																		</div>
																	</div>
																	<div class="share-summery">											
																		<div class="travad-title pageendorseheader endorsement_create_head"><?=$adheadeline?></div>
																		<div class="travad-subtitle pageendorsetext endorsement_create_text"><?=$adtext?></div>
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
																		<img class="circle pageendorseimage" src="<?=$pageimage?>"/>
																	</span>
																</div>
																
															</div>
															<div class="desc-holder">
																<a href="javascript:void(0)" class="pageendorsenames endorsement_create_value"><?=$pagename?></a>
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
																		<p class="pageendorsecatch endorsement_create_phrase"><?=$adcatch?></p>
																	</div>	
																	<div class="post-img-holder">
																		<div class="post-img one-img">
																			<div class="pimg-holder"><img class="pageendorse" src="<?=$adimage?>"/></div>
																		</div>
																	</div>
																	<div class="share-summery">											
																		<div class="travad-title pageendorseheader endorsement_create_head"><?=$adheadeline?></div>
																		<div class="travad-subtitle pageendorsetext endorsement_create_text"><?=$adtext?></div>
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
					<?php } ?>
					</div>
				</div>
				<div class="adstep-holder hasDivider">
					<div class="adsec-divider"></div>
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
										<div class="detail-holder">
											<div class="sliding-middle-out anim-area underlined fullwidth dropdown782">
												<select data-fill="y" data-selectore="country123" data-action="country" class="country123 select2" style='width: 100%' multiple ='multiple' id='ads_loc' onchange='change_audience()'>
													<option value="" disabled selected>Choose location</option>
													<?php
													$modelcountrycode = ArrayHelper::map(CountryCode::find()->all(), 'country_name', 'country_name');
													foreach($modelcountrycode as $key => $smodelcountrycode) {
														if(trim($smodelcountrycode) != '') {	
															echo '<option value="'.$smodelcountrycode.'">'.$smodelcountrycode.'</options>';
														}
													}
													?>
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
												<div id="test-slider1"></div>
											</div>
										</div>
									</div>
									<div class="frow">
										<div class="caption-holder">
											<label>Language <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										</div> 
										<div class="detail-holder">
											<div class="sliding-middle-out anim-area underlined fullwidth dropdown782">
												<select data-fill="y" data-selectore="language123" data-action="language" class="language123 select2" style='width: 100%' multiple ='multiple' id='ads_lang' onchange='change_audience()'>
													<option value="" disabled selected>Choose language</option>
													<?php
													$modellang = ArrayHelper::map(Language::find()->all(), 'name', 'name');
													foreach($modellang as $key => $smodellang) {
														if(trim($smodellang) != '') {	
															echo '<option value="'.$smodellang.'">'.$smodellang.'</options>';
														}
													}
													?>
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
												<div class="h-checkbox entertosend leftbox">
													<input type="radio" id="All" class="All" name="adGender" value="All" <?php if(isset($ad['admale']) && !empty($ad['admale'] == 'male') && !empty($ad['adfemale'] == 'female')) { echo "checked"; } ?>/>
													<label for="All">All</label>
									            </div>															
											</li>
											<li>
												<div class="h-checkbox entertosend leftbox">
													<input type="radio" id="Male" class="Male" name="adGender"  value="Male" <?php if(isset($ad['admale']) && !empty($ad['admale'] == 'male')) { echo "checked"; } ?> >
													<label for="Male">Male</label>
									            </div>
											</li>
												<li>
													<div class="h-checkbox entertosend leftbox">
														<input type="radio" id="Female" class="Female" name="adGender" value="Female" <?php if(isset($ad['adfemale']) && !empty($ad['admale'] == 'female')) { echo "checked"; } ?> >
														<label for="Female">Female</label>
									            	</div>
												</li>
											</ul>
										</div>
									</div>
									<div class="frow">
										<div class="caption-holder">
											<label>Proficient <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										</div>
										<div class="detail-holder">
											<div class="sliding-middle-out anim-area underlined fullwidth dropdown782">
												<select data-fill="y" data-selectore="proficient123" data-action="proficient" class="proficient123 select2" style='width: 100%' multiple ='multiple' id='ads_pro' onchange='change_audience()'>
													<option value="" disabled selected>Choose proficient</option>
													<?php
													$modelpro = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
													foreach($modelpro as $key => $smodelpro) {
														if(trim($smodelpro) != '') {	
															echo '<option value="'.$smodelpro.'">'.$smodelpro.'</options>';
														}
													}
													?>
												</select>
											</div>
										</div>
									</div>
									<div class="frow">
										<div class="caption-holder">
											<label>Interest <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
										</div>
										<div class="detail-holder">
											<div class="sliding-middle-out anim-area underlined fullwidth dropdown782 ">
												<select data-fill="y" data-selectore="interest123" data-action="interest" class="interest123 select2" style='width: 100%' multiple ='multiple' id='ads_int' onchange='change_audience()'>
													<option value="" disabled selected>Choose interest</option>
													<?php
													$modelint = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
													foreach($modelint as $key => $smodelint) {
														if(trim($smodelint) != '') {
															echo '<option value="'.$smodelint.'">'.$smodelint.'</options>';
														}
													}
													?>
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
							         <h6>Audience Details</h6>
							         <div class="frow">
							            <div class="caption-holder"><label>Location</label></div>
							            <div class="detail-holder" id="eta_location">
							               <p></p>
							            </div>
							         </div>
							         <div class="frow">
							            <div class="caption-holder"><label>Age</label></div>
							            <div class="detail-holder" id="eta_age">
							               <p></p>
							            </div>
							         </div>
							         <div class="frow">
							            <div class="caption-holder"><label>Language</label></div>
							            <div class="detail-holder" id="eta_language">
							               <p></p>
							            </div>
							         </div>
							         <div class="frow">
							            <div class="caption-holder"><label>Gender</label></div>
							            <div class="detail-holder" id="eta_gender">
							               <p></p>
							            </div>
							         </div>
							      </div>
									<h6>Estimated Daily Reach</h6>
									<div class="frow">
										<div class="range-slider range-slider1">
							            <input type="text" class="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
							            <div id="test-slider"></div>
							         </div>
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
									<div class="sliding-middle-out anim-area underlined ">
										$<input type="text" class="budget-text" placeholder="" value="<?=$daily_budget?>" id="min_budget">
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
												  <input type="radio" name="radio1" id="daily" <?php if($ad['adruntype'] == 'daily'){?>checked="true"<?php } ?> value="startdate"/>
												  <div class="control__indicator"></div>
												</label>
												<p>Run my advert set contineously starting today until</p>
												<div class="sliding-middle-custom anim-area underlined adddate">
													<input id="daily_date" type="text" placeholder="End date" data-toggle="datepicker" readonly data-query="M" class="datepickerinput" <?php if($ad['adruntype'] == 'daily'){?>value="<?=date('M d, Y',$ad['adenddate'])?>"<?php } ?>>
												</div>
											</div>
										</li>
										<li>
											<div class="radio-holder">
												<label class="control control--radio">&nbsp;
												  <input type="radio" name="radio1" id="manual" <?php if($ad['adruntype'] == 'manual'){?>checked="true"<?php } ?> value="startenddate" />
												  <div class="control__indicator"></div>
												</label>
												<p>Set a starting date</p>
												<div class="sliding-middle-custom anim-area underlined adddate">
													<input type="text" placeholder="Start date" <?php if($ad['adruntype'] == 'manual'){?>value="<?=date('M d, Y',$ad['adstartdate'])?>"<?php } ?> data-toggle="datepicker" data-query="M" class="datepickerinput" id="startdate" readonly>
												</div>
												<span class="full-date">
												<p>and end date</p>
												<div class="sliding-middle-custom anim-area underlined adddate">
													<input type="text" placeholder="End date" <?php if($ad['adruntype'] == 'manual'){?>value="<?=date('M d, Y',$ad['adenddate'])?>"<?php } ?> data-toggle="datepicker" data-query="M" class="datepickerinput" id="enddate" readonly>
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

				<input type="hidden" name="adaction" value="edit" id="adaction"/>
				<input type="hidden" name="advrt_id" value="<?=$advrt_id?>" id="advrt_id"/>
				<input type="hidden" name="dif_amount" value="0" id="dif_amount"/>
				<div class="btn-holder">									
					<div class="dirinfo right">
						<a href="javascript:void(0)" onclick="closeEditAd()" class="btngen-center-align waves-effect waves-light" tabindex="1">Back</a>
						<a href="javascript:void(0)" id="goPayment" onclick="setVars(),ad_summary()" class="btngen-center-align payment_popup_ads waves-effect waves-light" tabindex="1">Save</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>




<?php include('../views/layouts/commonjs.php'); ?>

<script src="<?=$baseUrl?>/js/jquery-gauge.min.js" type="text/javascript"></script>
<script src='<?=$baseUrl?>/js/wNumb.min.js'></script>
<script src="<?=$baseUrl?>/js/jquery.cropit.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/edit-ads.js"></script>
<script>
    $(document).ready(function(){
    	$('#ads_loc').material_select($('#ads_loc').val([<?php echo $ads_loc; ?>]));
    	$('#ads_lang').material_select($('#ads_lang').val([<?php echo $ads_lang; ?>]));
    	$('#ads_pro').material_select($('#ads_pro').val([<?php echo $ads_pro; ?>]));
    	$('#ads_int').material_select($('#ads_int').val([<?php echo $ads_int; ?>]));

    	setTimeout(function(){ change_audience_guage(); },500);
    });
</script>

<?php exit;?>