<?php  
use yii\helpers\Url; 
use frontend\assets\AppAsset;
use frontend\models\LocaldriverPost;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Local Dine';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();

$LocaldriverPost = LocaldriverPost::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
$post_user_id = $LocaldriverPost['user_id'];
$images = isset($LocaldriverPost['images']) ? $LocaldriverPost['images'] : '';
$images = explode(',', $images);
$defaultImages = array($baseUrl.'/images/driver-detail1.jpg', $baseUrl.'/images/driver-detail2.jpg', $baseUrl.'/images/driver-detail3.jpg');
 
$filter_images = array_merge($images, $defaultImages);
$filter_images = array_values(array_filter($filter_images));

$name = $this->context->getuserdata($post_user_id,'fullname');
$vehicletype = $LocaldriverPost['vehicletype'];
$onboard = $LocaldriverPost['onboard'];
$rate = $LocaldriverPost['rate'];
$vehiclecapacity = $LocaldriverPost['vehiclecapacity'];
$restriction = $LocaldriverPost['restriction'];
$describeyourtalent = $LocaldriverPost['describeyourtalent'];
$activity = $LocaldriverPost['activity'];
$activity = str_replace(',', ', ', $activity);

$post_u_fullname = $this->context->getuserdata($post_user_id,'fullname');
$post_u_thumb = $this->context->getimage($post_user_id,'thumb');

$isvisible = 'no';

if(empty($user_id)) {
	$isvisible = 'yes';
} else {
	if($user_id == $post_user_id) {
		$isvisible = 'yes';
	}
}
?>

<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper  localdriver-detail-page"> 
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
		<div>
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout unsetmargin">
		      <div class="collection-page event-detail-page pb-0 m-t-50">
		         <div class="combined-column wide-open main-page full-page">
		            <div class="width-100 m-top">
		                  <div class="collection-gallery-wrapper">
		                     <div class="collection-container">
		                        <div class="row mx-0">
		                           <div class="collection-gallery">
		                              <div class="collection-card">
		                                 <div class="collection-card-body">  
		                                    <a href="">
		                                       <div class="collection-card-inner">
		                                          <div class="collection-card-left">
		                                             <img role="presentation" class="" src="<?=$filter_images[0]?>" alt="">
		                                          </div> 
		                                          <div class="collection-card-middle">
		                                             <img role="presentation" class="" src="<?=$filter_images[1]?>" alt="">
		                                          </div>
		                                          <div class="collection-card-right">
		                                             <div class="img-right-top">
		                                                <img role="presentation" class="" src="<?=$filter_images[2]?>" alt="">
		                                             </div>
		                                          </div>  
		                                       </div>
		                                    </a>
		                                 </div>
		                              </div> 
		                           </div>
		                        </div>
		                     </div>
		                  </div>
		                  <div class="event-info-wrapper">
		                     <div class="container">
		                        <div class="row mx-0">
		                           <div class="col m8 s12">
		                              <div class="event-title-container">
		                                 <div class="row mx-0 valign-wrapper">
		                                    <div class="left">
		                                       <!-- <h1 class="event-title">Meet Your Driver</h1> -->
		                                       <div class="people-box">
		                                          <div class="img-holder">
		                                          	 <img src="<?=$post_u_thumb?>">
		                                          </div>
		                                          <div class="desc-holder">
		                                             <a href="javascript:void(0)" class="userlink"><?=$post_u_fullname?></a>
		                                          </div>
		                                       </div>
		                                    </div> 
		                                    <div class="right ml-auto">
		                                    	<?php if($isvisible == 'yes') { ?>
		                                    	<a href="javascript:void(0)" class="waves-effect waves-theme <?=$checkuserauthclass?>" onclick="editpostpopupopen('<?=$id?>', this)"><i class="mdi mdi-pencil mdi-20px"></i></a>
		                                    	<?php } ?>
		                                    </div>
		                                 </div>
		                              </div>
		                              <div class="detail-title-container">
		                                 <h3 class="event-detail-title">Meet your driver</h3>
		                              </div>
		                              <div class="info-container">
		                                 <p><?=$describeyourtalent?></p>
		                              </div>
		                              <div class="info-list full-width-list">
		                                 <ul>
		                                    <li>
		                                       <h5>Vehicle Type</h5>
		                                       <p><?=$vehicletype?></p>
		                                    </li>
		                                    <li>
		                                       <h5>On-board</h5>
		                                       <p><?=$onboard?></p>
		                                    </li>
		                                    <li>
		                                       <h5>Vehicle capacity</h5>
		                                       <p><?=$vehiclecapacity?></p>
		                                    </li>
		                                    <li>
		                                       <h5>Activities</h5>
		                                       <p><?=$activity?></p>
		                                    </li>
		                                    <li>
		                                       <h5>Restriction</h5>
		                                       <p><?=$restriction?></p>
		                                    </li>
		                                 </ul>
		                              </div>
		                              <div class="photo-section mt-20">
		                                 <div class="row mx-0 valign-wrapper">
		                                    <div class="left">
		                                       <h5>PHOTOS</h5>
		                                    </div>
		                                    <div class="right ml-auto">
		                                       <a href="javascript:void(0)" class="<?=$checkuserauthclass?>" onclick="uploadphotoslocaldriver('<?=$id?>', this)">+ Upload</a>
		                                    </div>
		                                 </div>
		                                 <div class="row mt-10">
		                                 	<?php
		                                 	foreach ($images as $images_s) { ?>
			                                    <div class="col s3 photobox">
			                                    	<img role="presentation" class="" src="<?=$images_s?>" alt="">
			                                    	<?php if($isvisible == 'yes') { ?>
		                                    		<i class="mdi mdi-delete photosdelete" onclick="delupdpht(this)"></i>
		                                    		<?php } ?>
			                                    </div>
		                                 	<?php } ?>
		                                 </div>
		                              </div>
		                           </div>
		                           <div class="col m4 s12">
		                              <div class="event-right-wrapper">
		                                 <div class="booking-form-section">
		                                    <div class="price-container">
		                                       <span><span class="price"> <?=$rate?> </span> per day</span>
		                                    </div>
		                                    <div class="ddl-select">
		                                       <label>Date</label>
		                                       <select class="select2" tabindex="-1" >
		                                          <option>Saturday 06/01/2019</option>
		                                          <option>Saturday 06/01/2019</option>
		                                       </select>
		                                    </div>
		                                    <div class="ddl-select">
		                                       <label>Number of guests</label>
		                                       <select class="select2" tabindex="-1" >
		                                          <option>1 guest</option>
		                                          <option>2 guests</option>
		                                       </select>
		                                    </div>
		                                    <div class="personal-message ddl-select">
		                                       <label>Personal Message</label>
		                                       <textarea class="materialize-textarea md_textarea item_tagline" placeholder="Hi... Profile and experience look wonderful! I will be in town  for a few days and i m wondering if you could host me Thank you!"></textarea>
		                                    </div>
		                                    <div class="btn-sec">
		                                       <a class="waves-effect waves-light btn" href="javascript:void(0)">Message to Driver</a>
		                                    </div>
		                                 </div>
		                                 <div class="contact-host valign-wrapper">
		                                    <span><i class="mdi mdi-comment-outline"></i> Questions? </span>
		                                    <a href="">Contact the driver</a>
		                                    <span class="right ml-auto"><i class="mdi mdi-chevron-right mdi-17px"></i></span>
		                                 </div>
		                                 <div class="save-wishlist">
		                                    <p class="text-center m-0">
		                                       <span class="icon-heart"><i class="mdi mdi-heart mdi-20px"></i></span>
		                                       <a href="">Save to your wishlist</a>
		                                    </p>
		                                 </div>
		                                 <div class="request-work">
		                                    <h6>How requesting works...</h6>
		                                    <p>
		                                       <i class="mdi mdi-calendar"></i>
		                                       <span>Suggest a date for your trip to the driver. Select how many guests you would like to bring.</span>
		                                    </p>
		                                    <p>
		                                       <i class="mdi mdi-account-multiple-outline mdi-17px"></i></i>
		                                       <span>After clicking "Message to driver", the host will then message you about availabilty. You will not be charged to send a request. </span>
		                                    </p>
		                                 </div>  
		                              </div>
		                           </div>
		                        </div>
		                     </div>
		                     <?php include('reviews.php'); ?>
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
	
	<!-- create driver profile modal -->
	<div id="editLocalDriverModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

	<!-- create driver profile modal -->
	<div id="uploadphotoslocaldriver" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

	<div id="localdriver_review" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post set_re_height compose_newreview_popup"></div>
	
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

    <?php include('../views/layouts/commonjs.php'); ?>
    
    <script src="<?=$baseUrl?>/js/post.js"></script>
    <script src="<?=$baseUrl?>/js/localdriver.js"></script>
<?php $this->endBody() ?> 