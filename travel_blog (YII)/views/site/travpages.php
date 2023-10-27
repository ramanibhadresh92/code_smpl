<?php
use frontend\assets\AppAsset;
use frontend\models\BusinessCategory;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
 
$this->title = 'Pages';
$business_cat = BusinessCategory::find()->all();
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?> 
<link href="<?=$baseUrl?>/css/animate.css" rel="stylesheet">
<div class="page-wrapper ">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
	<div class="floating-icon">
		<div class="scrollup-btnbox anim-side btnbox scrollup-float">
			<div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>
		</div>		
	</div>
	<div class="clear"></div>   
	<div class="container page_container pages_container">
		<?php include('../views/layouts/leftmenu.php'); ?>
		<div class="fixed-layout ipad-mfix"> 
			<input type="hidden" id="baseurl" value="<?=$baseUrl?>">
			<div class="main-content with-lmenu pages-page main-page grid-view general-page">
				<div class="combined-column comsepcolumn">
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
				<div id="chatblock">
					<div class="float-chat anim-side">
						<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include('../views/layouts/footer.php'); ?>
</div>

<!--add page modal-->
	<div id="add_page_modal" class="modal add-item-popup custom_md_modal dropdownheight145">
	  <div class="modal_content_container">
		<div class="modal_content_child modal-content">
		  <div class="popup-title ">
			<button class="hidden_close_span close_span waves-effect">
				<i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
			</button>			 
			<h3>Create a page</h3>
			<a type="button" class="item_done crop_done waves-effect hidden_close_span close_modal" href="javascript:void(0)" >Done</a>
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
						<i class="zmdi zmdi-hc-lg zmdi-camera"></i>
						<input type="file" name="filupload" id="crop-file" class="upload cropit-image-input" />
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
				<div class="sidepad">
				  
				  <div class="frow">
					<input id="page_title" type="text" class="validate item_title" placeholder="Page title" />					
				  </div>
				  <div class="frow dropdown782">
					<select id="pageCatDrop1" class="pageservices" data-fill="n" data-action="pageservices" data-selectore="pageservices">
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

				  <input id="placelocationsearch" type="text" class="validate item_title" placeholder="Bussines address 'City/Country'" data-query="all"  onfocus="filderMapLocationModal(this)" autocomplete='off'/>
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
						<input type="checkbox" id="agreeemailpage1" />
	                     <label for="agreeemailpage1">I verify that I am the official representative of this entity and have the right to act on behalf of my entity in the creation of this page.</label>						
					</div>				
				</div>
			  </form>
			</div>
		  
		</div>
	  </div>
	  <div class="valign-wrapper additem_modal_footer modal-footer">		
  		<a href="javascript:void(0)" class="btngen-center-align  close_modal open_discard_modal waves-effect">Cancel</a>
		<a href="javascript:void(0)" onclick="createpage()" class="btngen-center-align waves-effect">Create</a>
	  </div>
	</div>
	
	<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
		<?php include('../views/layouts/mapmodal.php'); ?>
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
