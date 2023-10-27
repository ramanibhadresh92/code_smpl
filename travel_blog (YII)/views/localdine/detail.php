<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Localdine;
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

$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');

	$Localdine = Localdine::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
	$id = (string)$Localdine['_id'];
	$post_user_id = $Localdine['user_id'];
	$title = $Localdine['title'];
	$event_type = $Localdine['event_type'];
	$cuisine = $Localdine['cuisine'];
	$min_guests = $Localdine['min_guests'];
	$max_guests = $Localdine['max_guests'];
	if($min_guests == 1 && $max_guests == 1) {
		$guests = 1;
	} else {
		$guests = $min_guests .' to ' . $max_guests;
	}
	$description = $Localdine['description'];
	$dish_name = $Localdine['dish_name'];
	$summary = $Localdine['summary'];
	$meal = $Localdine['meal'];
	$currency = $Localdine['currency'];
	$whereevent = $Localdine['whereevent'];
	$images = $Localdine['images'];
	$images = explode(',', $images);
	$images = array_values(array_filter($images));
	$main_image = $images[0];
	$created_at = $Localdine['created_at'];

	$default_images = array($baseUrl.'/images/dine-detail1.jpg', $baseUrl.'/images/dine-detail2.jpg', $baseUrl.'/images/dine-detail3.jpg');
	$images_combo = array_merge($images, $default_images);
	$images_combo = array_values(array_filter($images_combo));
	$u_name = $this->context->getuserdata($post_user_id,'fullname');
	$u_image = $this->context->getimage($post_user_id,'thumb');
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
    <div class="page-wrapper place-wrapper localdine-page detail_page localdine-detail-page"> 
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
			      <div class="collection-page event-detail-page pb-0">
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
			                                             <img role="presentation" class="" src="<?=$images_combo[0]?>" alt="">
			                                          </div> 
			                                          <div class="collection-card-middle">
			                                             <img role="presentation" class="" src="<?=$images_combo[1]?>" alt="">
			                                          </div>
			                                          <div class="collection-card-right">
			                                             <div class="img-right-top">
			                                                <img role="presentation" class="" src="<?=$images_combo[2]?>" alt="">
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
			                                 <div class="row mx-0 valign-wrapper localdine-title">
			                                    <h1 class="event-title"><?=strtoupper($title)?></h1>
			                                 </div>
		                                    <div class="row mx-0 valign-wrapper">
		                                       <div class="event-icons">
		                                          <span class="valign-wrapper"><i class="mdi mdi-image-filter-drama mdi-30px"></i> <?=ucfirst(strtolower($event_type))?></span>
		                                          <span class="valign-wrapper"><i class="mdi mdi-blur-radial mdi-30px"></i> <?=$cuisine?></span>
		                                          <span class="valign-wrapper"><i class="mdi mdi-account-multiple-outline mdi-30px"></i> <?=$guests?></span>
		                                       </div>
		                                       <div class="localdine-edit right">
			                                    	<div class="right ml-auto">
			                                       	<?php if($isvisible == 'yes') { ?>
			                                          <a href="javascript:void(0)" data-editid="<?=$id?>" class="editlocaldine waves-effect waves-theme <?=$checkuserauthclass?>"><i class="mdi mdi-pencil mdi-20px"></i></a>
			                                       	<?php } ?>
			                                       </div>
			                                    </div>
		                                    </div>
			                              </div>
			                              <div class="detail-title-container">
			                                 <h3 class="event-detail-title">Experieence Description</h3>
			                                 <div class="people-box">
			                                    <div class="img-holder">
			                                       <img src="<?=$u_image?>">
			                                    </div> 
			                                    <div class="desc-holder">
			                                       <a href="javascript:void(0)" class="userlink"><?=$u_name?></a>
			                                    </div>
			                                 </div>
			                              </div>
			                              <div class="info-container">
			                                 <p><?=$description?></p>
			                              </div>
			                              <div class="info-list full-width-list">
			                                 <ul>
			                                    <li>
			                                       <h5>Event Type</h5>
			                                       <p><?=$event_type?></p>
			                                    </li>
			                                    <li>
			                                       <h5>Cuisine</h5>
			                                       <p><?=$cuisine?></p>
			                                    </li>
			                                    <li>
			                                       <h5>Min Guests</h5>
			                                       <p><?=$min_guests?></p>
			                                    </li>

			                                    <li>
			                                       <h5>Max Guests</h5>
			                                       <p><?=$max_guests?></p>
			                                    </li>
			                                    <li>
			                                       <h5>Dish Name</h5>
			                                       <?php
			                                       for ($i=0; $i < count($dish_name); $i++) { 
			                                       	 $CU_dish_name = $dish_name[$i];
			                                       	 $CU_summary = $summary[$i];
			                                       	 ?>
													 <h6><?=$CU_dish_name?></h6> 	
			                                       	 <p><?=$CU_summary?></p>
			                                       	 <?php
			                                       }
			                                       ?>
			                                    </li>
			                                    <li>
			                                       <h5>Where Event</h5>
			                                       <p><?=$whereevent?></p>
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
													<a href="javascript:void(0)" class="uploadphotolocaldine <?=$checkuserauthclass?>" data-editid="<?=$id?>">+ Upload</a>
													<?php } ?>
			                                    </div>
			                                 </div>
			                                 <div class="row mt-10">
			                                 	<?php foreach ($images as $images_s) { ?>
			                                    	<div class="col s3 photobox">
			                                    		<img role="presentation" src="<?=$images_s?>" alt="">
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
			                                        <span>
			                                       		<span class="price"> 
			                                       			<?php
															if(array_key_exists($currency, $currency_icon)) {
																echo $currency_icon[$currency].$meal;
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
			                                       <textarea class="materialize-textarea md_textarea item_tagline" placeholder="Hi... Profile and experience look wonderful! I will be in town  for a few days and i m wondering if you could host me Thank you! Adel"></textarea>
			                                    </div>
			                                    <div class="btn-sec">
			                                       <a class="waves-effect waves-light btn" href="javascript:void(0)">Message to Host</a>
			                                    </div>
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
			                                       <span>Suggest a date for a meal to the host. Select how many guests you would like to bring.</span>
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
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
	<!-- edit data modal -->
	<div id="dineEditModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

    <div id="compose_tool_box" class="modal compose_tool_box post-popup custom_modal main_modal">
    </div> 

    <div id="localdine_review" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post set_re_height compose_newreview_popup"></div>

	<div id="uploadphotosLocaldineModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
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
    <script src="<?=$baseUrl?>/js/localdine.js"></script>
<?php $this->endBody() ?> 