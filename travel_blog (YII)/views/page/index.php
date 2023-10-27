<?php  
use frontend\assets\AppAsset;
use frontend\models\BusinessCategory; 
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status'); 
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Pages';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();

$business_cat = BusinessCategory::find()->all();
?>

<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper  gen-pages "> 
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <?php include('../views/layouts/menu.php'); ?>
        <div class="floating-icon">
		   <div class="scrollup-btnbox anim-side btnbox scrollup-float">
		      <div class="scrollup-button float-icon">
		         <span class="icon-holder ispan">
		            <i class="mdi mdi-arrow-up-bold-circle"></i>
		         </span>
		      </div>
		   </div>
		</div>
		<div class="clear"></div>
		<div class="container page_container pages_container">
			<?php include('../views/layouts/leftmenu.php'); ?> 
			<div class="fixed-layout ipad-mfix">
				<input type="hidden" id="baseurl" value="<?=$baseUrl?>">
				<div class="with-lmenu pages-page main-page grid-view general-page business-pages">
					<div class="combined-column combined_md_column mx-auto float-none wide-open">
						<div class="content-box nbg">
							<div class="cbox-desc md_card_tab">
								<div class="fake-title-area divided-nav mobile-header">
									<ul class="tabs">									
										<li class="tab col s3 allpages"><a href="#pages-suggested" class="active">Suggested</a></li>
										<li class="tab col s3 likedpages"><a href="#pages-liked">Liked</a></li>
										<li class="tab col s3 mypages"><a href="#pages-yours">Yours</a></li>
									</ul>
								</div>
								<div class="tab-content view-holder grid-view">
									<div class="tab-pane fade in active main-pane" id="pages-suggested">
										<div class="pages-list generalbox-list all-list page_search">
											<div class="clear"></div>
											<div class="row">
												<center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
											</div>
										</div>
									</div>
									<div class="tab-pane fade main-pane" id="pages-liked">
										<div class="pages-list generalbox-list all-list animated fadeInUp">
											<div class="clear"></div> 
											<div class="row">
											</div>
										</div>
									</div> 
									<div class="tab-pane fade main-pane" id="pages-yours">
										<div class="pages-list generalbox-list admin-list animated fadeInUp">
											<div class="clear"></div>
											<div class="row">
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
    </div>  
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
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
    <div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
    <?php include('../views/layouts/addpersonmodal.php'); ?>

    <?php include('../views/layouts/custom_modal.php'); ?>
    <?php include('../views/layouts/editphotomadol.php'); ?> 
    
   
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="edit-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="add_page_modal" class="modal add-item-popup custom_md_modal" style="z-index: 1001; display: none; opacity: 0; transform: scaleX(0.7); top: 8%;">
	  <div class="modal_content_container">
		<div class="modal_content_child modal-content">
		  <div class="popup-title ">
			<button class="hidden_close_span close_span">
				<i class="mdi mdi-close compose_discard_popup"></i>
			</button>			 
			<h3>Create a page</h3>
			<span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
			<a type="button" class="item_done crop_done hidden_close_span close_modal" href="javascript:void(0)" >Done</a>
		  </div>
		 <input type="hidden" id="pageid" value="">
			<div class="main-pcontent">
			  <form class="add-item-form">
				<div class="frow frowfull">
				  <div class="crop-holder" id="image-cropper">
					<div class="cropit-preview"></div>
					<div class="main-img">
					  <img src="<?=$baseUrl?>/images/additem-collections.png" class="ui-corner-all"/>
					</div>
					<div class="main-img1">
					  <img id="imageid" draggable="false"/>
					</div>
					<div class="btnupload custom_up_load" id="upload_img_action">
					  <div class="fileUpload">
						<i class="mdi mdi-camera">camera_alt</i>
						<input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
					  </div>
					</div>
					<a  href="javascript:void(0)" class="btn btn-save image_save_btn image_save" style="display:none;">
					  <span class="mdi mdi-check"></span>
					</a>
					<a id="removeimg" href="javascript:void(0)" class="collection_image_trash image_trash">
					  <i class="mdi mdi-close" aria-hidden="true"></i>
					</a>
				  </div>
				</div>
				<div class="sidepad">
				  
				  <div class="frow">
					<input id="page_title" type="text" class="validate item_title" placeholder="Page title" />					
				  </div>
				  <div class="frow">
					<select id="pageCatDrop1">
					<option value="">Page services</option>
					<?php 
					foreach($business_cat as $business_cat2)
					{
					?>
					<option value="<?=$business_cat2['name']?>"><?=$business_cat2['name']?></option>
					<?php 	
					}
					?>
					</select>
				  </div>
				  <div class="frow">
					<textarea class="materialize-textarea mb0 md_textarea item_tagline" placeholder="Short description of your page" id="pageshort1" ></textarea>
					<span class="char-limit">0/80</span>								
				  </div>
				  <div class="frow">
					<textarea type="text" placeholder="Tell people more about the page" class="materialize-textarea md_textarea item_about" id="pagedesc1"></textarea>
				  </div>
				  <div class="frow">

				  <input id="placelocationsearch" type="text" class="validate item_title" placeholder="Bussines address 'City/Country'" data-query="M"  onfocus="filderMapLocationModal(this)" autocomplete='off'/>
				  </div>
				  <div class="frow">
					<input type="text" class="validate item_title" placeholder="List your external website, if you have one" id="pagesite1"/>
				  </div>
				  <div class="frow">
						<span class="icon-span"><input type="radio" id="agreeemailpage" name="verify-radio"></span>
						<p>Veryfiy ownership by sending  a text message to following email</p>
					</div>
					<div class="frow">
						<input type="text" placeholder="Your company email address" id="busemail1">
					</div>													
					<div class="frow">
						<input type="checkbox" id="agreeemailpage1"/>
						<label for="agreeemailpage1" >I verify that I am the official representative of this entity and have the right to act on behalf of my entity in the creation of this page.</label>						
					</div>				
				</div>
			  </form>
			</div>
		  
		</div>
	  </div>
	  <div class="additem_modal_footer modal-footer">		
		  <div class="frow nbm">
			  <div class="btn-holder">
			  	<span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
				<a href="javascript:void(0)" class="post_cancel_btn close_modal open_discard_modal">Cancel</a>
				<a href="javascript:void(0)" onclick="createpage()" class="post_cancel_btn create_btn">Create</a>
			  </div>		
		  </div>
	  </div>
	</div>
	

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

<script> 
var data1 = '';
var baseUrl ='<?php echo (string) $baseUrl; ?>';
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/jquery.cropit.js"></script>
<!-- <script type="text/javascript" src="<?=$baseUrl?>/js/custom-cropper.js"></script> -->
<script type="text/javascript" src="<?=$baseUrl?>/js/travpages.js"></script>
