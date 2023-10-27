<?php   
use frontend\assets\AppAsset;
use frontend\models\Connect;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');

$this->title = 'Trip Exprience';
$user_tags =  Connect::getuserConnections($user_id);
$usrfrdlist = array();
foreach($user_tags AS $ud) {
	if(isset($ud['userdata']['fullname']) && $ud['userdata']['fullname'] != '') {
	    $id = (string)$ud['userdata']['_id'];
	    $fbid = isset($ud['userdata']['fb_id']) ? $ud['userdata']['fb_id'] : '';
	    $dp = $this->context->getimage($ud['userdata']['_id'],'thumb');
	    $nm = $ud['userdata']['fullname'];
	    $usrfrdlist[] = array('id' => $id, 'fbid' => $fbid, 'name' => $nm, 'text' => $nm, 'thumb' => $dp);
	}
} 
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<div class="page-wrapper  gen-pages hidemenu-wrapper show-sidebar">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
	<div class="floating-icon">
		<div class="scrollup-btnbox anim-side btnbox scrollup-float">
			<div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>			
		</div>		
	</div>
	<div class="clear"></div>
	<div class="container page_container">
		<?php include('../views/layouts/leftmenu.php'); ?>
		<div class="fixed-layout ipad-mfix hide-addflow">
			<div class="main-content with-lmenu general-page generaldetails-page tripexperience-page main-page split-page">
				<div class="combined-column">
					<div class="content-box">
						<div class="cbox-title nborder">
							<i class="mdi mdi-file-document"></i>
							Trip Experience
						</div>
						<div class="right-tabs pagetabs">
							<ul class="tabs">								
								<li class="tab alltrip active"><a href="#tripexperience-all" data-toggle="tab" aria-expanded="true">All</a></li>
								<li class="tab yourtrip"><a href="#tripexperience-yours" data-toggle="tab" aria-expanded="false">Yours</a></li>
							</ul>
						</div>						
						<div class="cbox-desc">
							<div class="tab-content view-holder">
								<div class="tripexperience-details page-details">
									<div class="row">
										<div class="tripexperience-summery gdetails-summery">
											<div class="main-info">
												<a href="javascript:void(0)" class="expand-link mbl-filter-icon main-icon" onclick="mbl_mng_drop_searcharea(this,'searcharea1')"><i class="mdi mdi-tune grey-text mdi-20px"></i></a>
												<div class="search-area side-area main-search" id="searcharea1">
													<a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)">Advanced Search</a>
													<div class="expandable-area">
														<a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
															<img src="<?=$baseUrl?>/images/cross-icon.png"/>
															<span>DONE</span>
														</a>

														<div class="trip-search">
															<h6>Find trip experiences</h6>
															<div class="sliding-middle-out anim-area underlined fullwidth">
																<input type="text" data-query="M" placeholder="Search by city or country" id="trip_cur_loc" onfocus="filderMapLocationModal(this)" autocomplete='off'>
																<a href="javascript:void(0)"  class="searchbtn" onclick="callSearchLog();"><i class="zmdi zmdi-search"></i></a>
															</div>
														</div> 
														<div class="continent">
														</div>
													</div>
												</div>
											</div>
											<div class="sideboxes">
												<?php include('../views/layouts/recently_joined.php'); ?>
												<div class="content-box bshadow">
													<div class="cbox-desc">
														<div class="side-travad brand-travad">
															<div class="travad-maintitle">Best coffee in the world!</div>
															<div class="imgholder">
																<img src="<?=$baseUrl?>/images/brand-p.jpg">
															</div>
															<div class="descholder">								
																<div class="travad-subtitle">We just get new starbucks coffee that is double in caffine that everybody is calling it a boost!</div> 
																<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn">Explore</a>
															</div>
														</div>
													</div>					
												</div>
												<div class="content-box bshadow">	
													<div class="side-travad action-travad">						
														<div class="travad-maintitle"><span class="iholder"><i class="mdi mdi-account-group"></i></span><h6>Heal Well</h6><span class="adtext">Sponsored</span></div>
														<div class="imgholder">
															<img src="<?=$baseUrl?>/images/groupad-actionvideo.jpg"/>
														</div>
														<div class="descholder">															
															<div class="travad-title">Medical Research Methodolgy</div>
															<div class="travad-subtitle">Checkout the new video on our website exploring the latest techniques of medicine research</div>										
														<a href="javascript:void(0)" class="btn btn-primary btn-sm adbtn">Learn More</a>
														</div>
													</div>						
												</div>
												<?php include('../views/layouts/travads.php'); ?>	
											</div> 
										</div>
										<div class="post-column">									
											<div class="tab-content">
												<div class="tab-pane fade main-pane active in" id="tripexperience-all" ></div>
												<div class="tab-pane fade main-pane" id="tripexperience-yours">
													<?php /*
													<div class="new-post compose-box" id="viacall_new_post">
														<?= \Yii::$app->view->renderFile('@app/views/layouts/postblock.php'); ?> 
													</div> */?>
													<input type="hidden" id="pagename" value="tripexperience"/>
													<div class="new-post base-newpost">
														<form action="">
															<div class="npost-content">
																<div class="post-mcontent">
																	<i class="mdi mdi-pencil-box-outline main-icon"></i>
																	<div class="desc">									
																		<div class="input-field comments_box">
																			<input placeholder="Create trip experience" type="text" class="validate commentmodalAction_form" />
																		</div>
																	</div>
																</div>
															</div>				
															
														</form>
														<div class="overlay <?=$checkuserauthclass?>" id="composetoolboxAction"></div>
													</div>
													<div id="yourtripdata">
													</div>

												</div>
											</div>										
										</div>										
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="c_click" tabname=""></div>
				<div id="chatblock">
						<div class="float-chat anim-side">
							<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
							</div>
						</div>
				</div>
			</div>
			<div class="new-post-mobile clear">
				<a class="popup-window composetoolboxAction" href="javascript:void(0)"><i class="mdi mdi-pencil"></i></a>
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
 
<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
	<?php include('../views/layouts/mapmodal.php'); ?>
</div>
<?php include('../views/layouts/addpersonmodal.php'); ?>
<?php include('../views/layouts/editphotomadol.php'); ?>

<?php include('../views/layouts/custom_modal.php'); ?>
  
<script>
var data1=<?php echo json_encode($usrfrdlist); ?>;
var baseUrl ='<?php echo (string) $baseUrl; ?>';
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>

<script type="text/javascript" src="<?=$baseUrl?>/js/tripexperience.js"></script>
<script src="<?=$baseUrl?>/js/post.js"></script>
