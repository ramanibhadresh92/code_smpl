<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Camping;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Camping';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
$Camping = Camping::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
$camping_id = (string)$Camping['_id'];
$camping_uid = $Camping['user_id'];
$camping_title = $Camping['title'];
$camping_minguests = $Camping['min_guests'];
$camping_maxguests = $Camping['max_guests'];
$camping_rate = $Camping['rate'];
$camping_currency = $Camping['currency'];
$camping_description = $Camping['description'];
$camping_location = $Camping['location'];
$camping_telephone = $Camping['telephone'];
$camping_email = $Camping['email'];
$camping_website = $Camping['website'];
$camping_period_s = $Camping['period_s'];
$camping_period_e = $Camping['period_e'];
//check date is valid with range or not
$firstdate_filter = '';
$seconddate_filter = '';
$dated = '';

if($camping_period_s != '' && $camping_period_e != '') {
  $firstdate = trim($camping_period_s);
  $seconddate = trim($camping_period_e);

  $f_date_box = array_map('trim', explode('-', $firstdate));

  if(count($f_date_box) == 3) {
      if(checkdate($f_date_box[0], $f_date_box[1], $f_date_box[2])) {
          $firstdate_filter = $f_date_box[0].'/'.$f_date_box[1];
      }
  }

  if($firstdate_filter != '') {
      $s_date_box = array_map('trim', explode('-', $seconddate));

      if(count($s_date_box) == 3) {
  	      if(checkdate($s_date_box[0], $s_date_box[1], $s_date_box[2])) {
              $seconddate_filter = $s_date_box[0].'/'.$s_date_box[1];
          }
      }
  }
}

if($firstdate_filter != '' && $seconddate_filter != '') {
  $dated = $firstdate_filter .' - '.$seconddate_filter;
}

$camping_services = $Camping['services'];
$camping_services = explode(',', $camping_services);
$camping_images = $Camping['images'];
$default_images = array($baseUrl.'/images/home-tour1.jpg', $baseUrl.'/images/home-tour1.jpg', $baseUrl.'/images/home-tour1.jpg');
$camping_images = explode(',', $camping_images);
$images = array_merge($camping_images, $default_images);
$images = array_values(array_filter($images));
$url = '../web/uploads/camping/'; 
$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');

$isvisible = 'no';

if(empty($user_id)) {
	$isvisible = 'yes';
} else {
	if($user_id == $camping_uid) {
		$isvisible = 'yes';
	}
}
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper place-wrapper camping-page camping-detail-page"> 
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
	                                             <img role="presentation" class="" src="<?=$images[0]?>" alt="">
	                                          </div> 
	                                          <div class="collection-card-middle">
	                                             <img role="presentation" class="" src="<?=$images[1]?>" alt="">
	                                          </div>
	                                          <div class="collection-card-right">
	                                             <div class="img-right-top">
	                                                <img role="presentation" class="" src="<?=$images[2]?>" alt="">
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
	                              	 <div class="row mx-0 valign-wrapper camping-title">
	                                    <h1 class="event-title"><?=$camping_title?></h1>
	                                    <div class="camping-edit right">
		                                    <div class="right ml-auto">
		                                       <a href="javascript:void(0)" data-editid="<?=$camping_id?>" class="editcamping waves-effect waves-theme <?=$checkuserauthclass?>"><i class="mdi mdi-pencil mdi-20px"></i></a>
		                                    </div>
		                                </div>
	                                 </div>
	                              </div>
	                              <div class="detail-title-container">
	                                 <h3 class="event-detail-title">Camping Detail</h3>
	                              </div>
	                              <div class="info-container">
	                                 <p><?=$camping_description?></p>
	                              </div>
	                              <div class="info-list">
	                                 <ul>
	                                    <li>
	                                       <h5>gps point</h5>
	                                       <p>N65*36 W23*58</p>
	                                    </li>
	                                    <li>
	                                       <h5>telephone</h5>
	                                       <p><?=$camping_telephone?></p>
	                                    </li>
	                                    <li>
	                                       <h5>e-mail</h5>
	                                       <p><?=$camping_email?></p>
	                                    </li>
	                                    <li>
	                                       <h5>website</h5>
	                                       <p><?=$camping_website?></p>
	                                    </li>
	                                    <li>
	                                       <h5>opening period</h5>
	                                       <p><?=$dated?></p>
	                                    </li>
	                                    <li class="services">
	                                       <h5 class="mb-10">services</h5>
	                                       <?php if(in_array('spa', $camping_services)) { ?>
	                                       	<a href="javascript:void(0)">
												<img alt="spa" title="spa" src="<?=$baseUrl?>/images/amenity-spa.png">
												<span>Spa</span>
											</a>
											<?php } ?>
											
											<?php if(in_array('beach', $camping_services)) { ?>
											<a href="javascript:void(0)">
												<img alt="beach" title="beach" src="<?=$baseUrl?>/images/amenity-beach.png">
												<span>Beach</span>
											</a>
											<?php } ?>
											
											<?php if(in_array('wifi', $camping_services)) { ?>
											<a href="javascript:void(0)">
												<img alt="wifi" title="wifi" src="<?=$baseUrl?>/images/amenity-wifi.png">
												<span>Wifi</span>
											</a>
											<?php } ?>
											
											<?php if(in_array('breakfast', $camping_services)) { ?>
											<a href="javascript:void(0)">
												<img alt="breakfast" title="breakfast" src="<?=$baseUrl?>/images/amenity-breakfast.png">
												<span>Breakfast</span>
											</a>
											<?php } ?>
											
											<?php if(in_array('pool', $camping_services)) { ?>
											<a href="javascript:void(0)">
												<img alt="pool" title="pool" src="<?=$baseUrl?>/images/amenity-pool.png">
												<span>Pool</span>
											</a>
											<?php } ?>
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
											<a href="javascript:void(0)" class="<?=$checkuserauthclass?>" onclick="uploadphotoscamping('<?=$id?>', this)">+ Upload</a>
											<?php } ?>
	                                    </div>
	                                 </div>
	                                 <div class="row mt-10">
	                                 	<?php foreach ($camping_images as $images_s) { ?>
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
													if(array_key_exists($camping_currency, $currency_icon)) {
														echo $currency_icon[$camping_currency].$camping_rate;
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
	                                       <textarea class="materialize-textarea md_textarea item_tagline" placeholder="Hi... Camp had facilities look wonderful! I will be in town  for a few days and i m wondering if you could host me Thank you!"></textarea>
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

<input type="hidden" name="pagename" id="pagename" value="feed" />
<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />

<!-- edit data modal -->
<div id="campEditModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
</div>

<div id="camping_review" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post set_re_height compose_newreview_popup"></div>

<div id="uploadphotosCampingModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
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
<script src="<?=$baseUrl?>/js/camping.js"></script>
<?php $this->endBody() ?> 