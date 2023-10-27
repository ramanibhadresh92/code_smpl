<?php  
use yii\helpers\Url; 
use frontend\assets\AppAsset;
use frontend\models\Homestay;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Homestay';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
$post = Homestay::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();

$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');
	
if(!empty($post)) { 
	$postId = (string)$post['_id'];	
	$postUId = $post['user_id'];	
	$title = $post['title'];	
	$property_type = $post['property_type'];	
	$guests_room_type = $post['guests_room_type'];	
	$bath = $post['bath'];	
	$guest_type = $post['guest_type'];	
	$guest_type = explode(',', $guest_type);
	$guest_type = array_values(array_filter($guest_type));
	$homestay_facilities = $post['homestay_facilities'];	
	$homestay_facilities = explode(',', $homestay_facilities);
	$homestay_facilities = array_values(array_filter($homestay_facilities));
	$homestay_location = $post['homestay_location'];	
	$adult_guest_rate = $post['adult_guest_rate'];	
	$currency = strtoupper($post['currency']);	
	$description = $post['description'];	
	$rules = $post['rules'];	
	$images = isset($post['images']) ? $post['images'] : '';
	$images = explode(',', $images);
	$images = array_values(array_filter($images));
	$defaultImages = array($baseUrl.'/images/real-estate.jpg', $baseUrl.'/images/real-estate1.jpg', $baseUrl.'/images/real-estate2.jpg');

	$filter_images = array_merge($images, $defaultImages);
	$filter_images = array_values(array_filter($filter_images));

	$profile = $this->context->getuserdata($postUId,'thumbnail');
	$name = $this->context->getuserdata($postUId,'fullname');

	$isvisible = 'no';

	if(empty($user_id)) {
		$isvisible = 'yes';
	} else {
		if($user_id == $postUId) {
			$isvisible = 'yes';
		}
	}
	?>
	<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper place-wrapper hidemenu-wrapper homestay-page homestay-detail-page"> 
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
				                                             <img role="presentation" src="<?=$filter_images[0]?>" alt="">
				                                          </div> 
				                                          <div class="collection-card-middle">
				                                             <img role="presentation" src="<?=$filter_images[1]?>" alt="">
				                                          </div>
				                                          <div class="collection-card-right">
				                                             <div class="img-right-top">
				                                                <img role="presentation" src="<?=$filter_images[2]?>" alt="">
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
				                                 <div class="row mx-0 valign-wrapper homestay-title">
				                                    <h1 class="event-title">
				                                       <?=$title?>
				                                    </h1> 
				                                    <div class="homestay-edit right">
				                                       <div class="right ml-auto">
				                                       	<?php if($isvisible == 'yes') { ?>
				                                          <a href="javascript:void(0)" data-editid="<?=$postId?>" class="homestayEditAction waves-effect waves-theme <?=$checkuserauthclass?>"><i class="mdi mdi-pencil mdi-20px"></i></a>
				                                       	<?php } ?>
				                                       </div>
				                                    </div>
				                                 </div>
				                              </div>
				                              <div class="detail-title-container">
				                                 <h3 class="event-detail-title">Homestay Detail</h3>
				                                 <div class="people-box">
				                                    <div class="img-holder">
				                                       <img src="<?=$baseUrl?>/images/people-2.png">
				                                    </div>
				                                    <div class="desc-holder">
				                                       <a href="javascript:void(0)" class="userlink">Adel Ahasanat</a>
				                                    </div>
				                                 </div>
				                              </div>
				                              <span class="private-room"> 
		                                          <i class="mdi mdi-home mdi-17px"></i>
		                                          <?=ucfirst(strtolower($guests_room_type))?> in <?=strtolower($property_type)?> with <?=strtolower($bath)?> bath
		                                       </span>
				                              <div class="info-container">
				                                 <p><?=$description?></p>
				                              </div>
				                              <div class="event-title-container row mx-0 border-none">
				                                <div class="event-icons">
				                                    <h5 class="valign-wrapper">Welcomes</h5>
				                                    <span class="valign-wrapper"><i class="mdi mdi-check mdi-20px"></i> Males</span>
				                                    <span class="valign-wrapper"><i class="mdi mdi-check mdi-20px"></i> Females</span>
				                                    <span class="valign-wrapper"><i class="mdi mdi-check mdi-20px"></i> Couples</span>
				                                    <span class="valign-wrapper"><i class="mdi mdi-check mdi-20px"></i> Families</span>
				                                    <span class="valign-wrapper"><i class="mdi mdi-check mdi-20px"></i> Students</span>
				                                 </div>
				                              </div>
				                              <div class="info-list full-width-list">
				                                 <ul>
				                                    <li>
				                                       <h5>House Rules</h5>
				                                       <p><?=$rules?></p>
				                                    </li> 
				                                    <li>
				                                       <h5>House Facilities</h5>
				                                       <div class="row facilities-row mx-0">
														<?php
														foreach ($homestay_facilities as $key => $Services_s) {
														 $icon = strtolower($Services_s);
														 $icon = $icon.'.png';
														 $icon = $baseUrl.'/images/services-icon/'.$icon;
														 if(!file_exists($_SERVER['DOCUMENT_ROOT'].$icon)) {
														    continue;
														 }

														 $label = ucwords(strtolower($Services_s));
														 $alt = $key;
														 ?>
														 <div class="col s2 center-align">
														   <img src="<?=$icon?>">
														   <div><?=$label?></div>
														 </div>
														 <?php
														}
														?>				                                       </div>
				                                    </li>
				                                    <li class="services">
				                                       <h5 class="mb-10">services</h5>
				                                       <a href="javascript:void(0)"><img alt="spa" title="spa" src="<?=$baseUrl?>/images/amenity-spa.png"><span>Spa</span></a>
				                                       <a href="javascript:void(0)"><img alt="beach" title="beach" src="<?=$baseUrl?>/images/amenity-beach.png"><span>Beach</span></a>
				                                       <a href="javascript:void(0)"><img alt="wifi" title="wifi" src="<?=$baseUrl?>/images/amenity-wifi.png"><span>Wifi</span></a>
				                                       <a href="javascript:void(0)"><img alt="breakfast" title="breakfast" src="<?=$baseUrl?>/images/amenity-breakfast.png"><span>Breakfast</span></a>
				                                       <a href="javascript:void(0)"><img alt="pool" title="pool" src="<?=$baseUrl?>/images/amenity-pool.png"><span>Pool</span></a>
				                                    </li>

				                                 </ul>
				                              </div>
				                              <div class="photo-section mt-20">
				                                 <div class="row mx-0 valign-wrapper">
				                                    <div class="left">
				                                       <h5>PHOTOS</h5>
				                                    </div>
				                                    <div class="right ml-auto">
				                                    	<?php if($isvisible == 'yes') { ?>
														<a href="javascript:void(0)" class="<?=$checkuserauthclass?>" onclick="uploadphotoshomestay('<?=$id?>', this)">+ Upload</a>
														<?php } ?>
				                                    </div>
				                                 </div>
				                                 <div class="row mt-10">
				                                 	<?php foreach ($images as $images_s) { ?>
				                                    	<div class="col s3"><img role="presentation" class="" src="<?=$images_s?>" alt=""></div>
				                                 	<?php } ?>
				                                 </div>
				                              </div>
				                           </div>
				                           <div class="col m4 s12">
				                              <div class="event-right-wrapper">
				                                 <div class="booking-form-section">
				                                    <div class="price-container">
				                                       <span>
				                                       		<span class="price"> 
				                                       			<?php
																if(array_key_exists($currency, $currency_icon)) {
																	echo $currency_icon[$currency].$adult_guest_rate;
																}
				                                       			?>
				                                       		</span> 
				                                       			per night
				                                       	</span>
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
				                                       <textarea class="materialize-textarea md_textarea item_tagline" placeholder="The space and facilities look wonderful! I will be in town for a few days and i m wondering if you could host me Thank you!"></textarea>
				                                    </div>
				                                    <div class="btn-sec">
				                                       <a class="waves-effect waves-light btn" href="javascript:void(0)">Message to Host</a>
				                                    </div>
				                                 </div>
				                                 <div class="contact-host valign-wrapper">
				                                    <span><i class="mdi mdi-comment-outline"></i> Questions? </span>
				                                    <a href="">Contact the host</a>
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
				                                       <span>Suggest a date for your stay to the host. Select how many guests you would like to bring.</span>
				                                    </p>
				                                    <p>
				                                       <i class="mdi mdi-account-multiple-outline mdi-17px"></i></i>
				                                       <span>After clicking "Message to host", the host will then message you about availabilty. You will not be charged to send a request. </span>
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
<?php } ?> 
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
	<div id="homestay_review" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post set_re_height compose_newreview_popup"></div>

	<!-- edit data modal -->
	<div id="homestayEditModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

	<div id="uploadphotosHomestayModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
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
	
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

    <?php include('../views/layouts/commonjs.php'); ?>
    
    <script src="<?=$baseUrl?>/js/post.js"></script>
   <script type="text/javascript" src="<?=$baseUrl?>/js/homestay.js"></script>
<?php $this->endBody() ?> 