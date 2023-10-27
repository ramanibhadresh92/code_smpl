<?php  
use frontend\assets\AppAsset;
use frontend\models\Grestaurants; 
use backend\models\Googlekey;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Restaurants';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);

$place = Yii::$app->params['place'];
$placetitle = Yii::$app->params['placetitle'];
$placefirst = Yii::$app->params['placefirst'];
$lat = Yii::$app->params['lat'];
$lng = Yii::$app->params['lng'];
$count = 'all';
$placeapi = str_replace(' ','+',$placetitle);
$tk = $ql = $rs = '';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper hidemenu-wrapper full-wrapper noopened-search JIS3829 restaurants-page">
    <div class="header-section">
        <?php include('../views/layouts/header.php'); ?>
    </div>
    <?php include('../views/layouts/menu.php'); ?>
    <div class="menu-wrap tb-pages">
	   <div class="menu-sec">
	      <div class="search-box">
	           <div class="box-content">
	               <div class="bc-row home-search">
	                   <div class="row mx-0">
	                       <h4>Restaurants in Japan</h4>
	                   </div>
	               </div>
	           </div>
	        </div>
	   </div>
	</div>
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
	<div class="container page_container">
	    <?php include('../views/layouts/leftmenu.php'); ?>
	    <div class="fixed-layout">
	    	<div class="main-content main-page places-page hotels-page japan-page">
	    		<div class="combined-column wide-open restaurants-wrapper main-page full-page">
	    			<div class="places-content places-all">
	    				<div class="container cshfsiput">
               <div class="outer-holder hotels-page">
                     <div class="row mx-0 filter-map"> 
                        <a href="javascript:void(0)" class="col s12 toggle-map" onclick="toggleDiv('div1', 'resId')"><i class="mdi mdi-map-marker"></i>Map</a>
                     </div>
                     <div class="row m-0 map-mobile toggle-maphotels" id="div1">
                        <div class="map-holder">
                           <div id="hotelmaplink">
                               <a href="restaurantmap.php"><i class="mdi mdi-map-marker"></i> View map</a>
                           </div>
                           <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3110.3465133386144!2d-9.167423685010494!3d38.77868997958898!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd193295d5b45545%3A0x3f9e7b6a5f00e12c!2sPerta!5e0!3m2!1sen!2sin!4v1481089901870" width="600" height="450" frameborder="0" allowfullscreen></iframe>
                        </div>
                     </div>
                     <div class="row mx-0">
                        <div class="search-area side-area">
                           <a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)">
                           <span class="desc-text"><i class="mdi mdi-menu-right"></i>Advanced Search</span>
                           <span class="mbl-text"><i class="mdi mdi-tune mdi-20px grey-text"></i></span>
                           </a>
                           <div class="expandable-area">
                              <div class="content-box bshadow">
                                 <a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
                                 <i class="mdi mdi-close"></i>
                                 </a>
                                 <div class="cbox-desc">
                                    <div class="map-holder map-desktop">
                                       <div id="hotelmaplink">
                                           <a href="restaurantmap.php"><i class="mdi mdi-map-marker"></i> View map</a>
                                       </div>
                                       <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3110.3465133386144!2d-9.167423685010494!3d38.77868997958898!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd193295d5b45545%3A0x3f9e7b6a5f00e12c!2sPerta!5e0!3m2!1sen!2sin!4v1481089901870" width="600" height="450" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                    <div class="srow mt-10">
                                       <h6>Establishment Type</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="rest" />
                                                   <label for="rest">
                                                      <span class="stars-holder">Restaurants</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="qbytes">
                                                   <label for="qbytes">
                                                      <span class="stars-holder">Quick Bites</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="dessert">
                                                   <label for="dessert">
                                                      <span class="stars-holder">Dessert</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow traveler-rating-sec">
                                       <h6>Reservations</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="onres" />
                                                   <label for="onres">
                                                      <span class="stars-holder">Online Reservations</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow mt-10">
                                       <h6>Cuisines & Dishes</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="cafe" />
                                                   <label for="cafe">
                                                      <span class="stars-holder">Cafe</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="british">
                                                   <label for="british">
                                                      <span class="stars-holder">British</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="italian">
                                                   <label for="italian">
                                                      <span class="stars-holder">Italian</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow">
                                       <h6>Dietary Restrictions</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="vegfr" />
                                                   <label for="vegfr">
                                                      <span class="stars-holder">Vegetarian Friendly</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="glfree">
                                                   <label for="glfree">
                                                      <span class="stars-holder">Gluten Free Options</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="vegop">
                                                   <label for="vegop">
                                                      <span class="stars-holder">Vegan Options</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="halal">
                                                   <label for="halal">
                                                      <span class="stars-holder">Halal</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow">
                                       <h6>Meals</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Breakfast" />
                                                   <label for="Breakfast">
                                                      <span class="stars-holder">Breakfast</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Brunch">
                                                   <label for="Brunch">
                                                      <span class="stars-holder">Brunch</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Lunch">
                                                   <label for="Lunch">
                                                      <span class="stars-holder">Lunch</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Dinner">
                                                   <label for="Dinner">
                                                      <span class="stars-holder">Dinner</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow">
                                       <h6>Price</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="CheapEats" />
                                                   <label for="CheapEats">
                                                      <span class="stars-holder">Cheap Eats</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Mid-range">
                                                   <label for="Mid-range">
                                                      <span class="stars-holder">Mid-range</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="FineDining">
                                                   <label for="FineDining">
                                                      <span class="stars-holder">Fine Dining</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow">
                                       <h6>Neighborhoods</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Chelsea" />
                                                   <label for="Chelsea">
                                                      <span class="stars-holder">Chelsea</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Covent Garden">
                                                   <label for="Covent Garden">
                                                      <span class="stars-holder">Covent Garden</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow">
                                       <h6>Restaurant features</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="AcceptsCreditCards" />
                                                   <label for="AcceptsCreditCards">
                                                      <span class="stars-holder">Accepts Credit Cards</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Buffet">
                                                   <label for="Buffet">
                                                      <span class="stars-holder">Buffet</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Delivery">
                                                   <label for="Delivery">
                                                      <span class="stars-holder">Delivery</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="srow">
                                       <h6>Good for</h6>
                                       <ul>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Barscene" />
                                                   <label for="Barscene">
                                                      <span class="stars-holder">Bar scene</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Businessmeetings">
                                                   <label for="Businessmeetings">
                                                      <span class="stars-holder">Business meetings</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                          <li>
                                             <div class="entertosend leftbox">
                                                <p>
                                                   <input type="checkbox" id="Familieswithchildren">
                                                   <label for="Familieswithchildren">
                                                      <span class="stars-holder">Families with children</span>
                                                   </label>
                                                </p>
                                             </div>
                                          </li>
                                       </ul>
                                    </div>
                                    <div class="btn-holder">
                                       <a href="javascript:void(0)" class="btn-custom">Reset Filters</a>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="tcontent-holder">
                           <div class="top-stuff">
                              <div class="more-actions">
                                 <div class="sorting left">
                                    <label>Sort by</label>
                                    <div class="select-holder">
                                       <select class="select2" tabindex="-1" >
                                          <option>Pricing</option>
                                          <option>Ratings</option>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <h6>1300 Restaurants found in <span>Japan</span></h6>
                           </div>
		                   <div class="hotel-list">
                              <div class="moreinfo-outer">
                                 <div class="places-content-holder">
                                    <div class="list-holder">
                                       <div class="hotel-list">
                                          <ul>
											<?php
											$rs = Grestaurants::find()->asarray()->one();
											if(isset($rs['results']) && !empty($rs['results'])) {
												$hotel = $rs['results'];
												for($i=0;$i<20;$i++){
													if(isset($hotel[$i]['place_id']) && !empty($hotel[$i]['place_id'])) {
														$pieces = $hotel[$i]['types'];
														$place_id = $hotel[$i]['place_id']; 
														$file = $place_id.'.jpg';
														$storage_path = 'uploads/gimages/';
														$img = '';
														$imgclass = 'himg';
														if(file_exists($storage_path.$file)) {
															$img = $storage_path.$file;
															list($width, $height, $type, $attr) = getimagesize($img);
															if($width > $height){$imgclass = 'himg';}
															else if($height > $width){$imgclass = 'vimg';}
															else{$imgclass = 'himg';}
														} else {
															continue;
														}
														$urlhotel="https://maps.googleapis.com/maps/api/place/details/json?placeid=$place_id&key=$GApiKeyP";
														$ch = curl_init();
														curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
														curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
														curl_setopt($ch, CURLOPT_URL,$urlhotel);
														$result=curl_exec($ch);
														curl_close($ch);
														$rss = json_decode($result, true);
														$shadr = $adr = $website = $ipn = '';
														if(isset($rss['result']) && !empty($rss['result'])) {
															$hotell = $rss['result'];
															$ipn = '';
															if(isset($hotell['address_components'][0]['long_name']) && !empty($hotell['address_components'][0]['long_name'])) {
																$shadr = '<span class="address">'.$hotell['address_components'][0]['long_name'].'</span>';
															}
															if(isset($hotell['vicinity']) && !empty($hotell['vicinity'])) {
																$adr = $hotell['vicinity'];
															}
															if(isset($hotell['website']) && !empty($hotell['website'])) {
																$website = $hotell['website'];
															}
															if(isset($hotell['international_phone_number']) && !empty($hotell['international_phone_number'])) {
																$ipn = '<span class="distance-info"><i class="mdi mdi-phone"></i>'.$hotell['international_phone_number'].'</span>';
															}
														}

														$user_ratings_total = isset($hotel[$i]['user_ratings_total']) ? $hotel[$i]['user_ratings_total'] : '';
														$formatted_address = isset($hotel[$i]['formatted_address']) ? $hotel[$i]['formatted_address'] : '';
														$rating = isset($hotel[$i]['rating']) ? $hotel[$i]['rating'] .' / 5' : '';
														?>
											  <li>
											    <div class="hotel-li expandable-holder dealli mobilelist">
											      <div class="summery-info">
											        <div class="imgholder <?=$imgclass?>-box">
											          <img src="<?=$img?>" class="<?=$imgclass?>"/>
											        </div>
											        <div class="descholder">
											          <a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose')">
											            <h4>
											              <?=$hotel[$i]['name']?>
											            </h4>
											            <div class="clear"></div>
											            <div class="reviews-link">
											              <?php if($user_ratings_total != '') {	?>
											              <span class="review-count"><?=$user_ratings_total?> reviews</span>
											          	  <?php } ?>
											              <?php if(isset($hotel[$i]['rating']) && !empty($hotel[$i]['rating'])){ ?>
											              <span class="checks-holder">
											                <?php for($j=0;$j<5;$j++){ ?>
											                <i class="zmdi zmdi-check-circle <?php if($j < $hotel[$i]['rating']){ ?>active<?php } ?>">
											                </i>
											                <?php } ?>
											                <?php if($rating != '') { ?>
											                <label>Excellent - <?=$rating?></label>
											            	<?php } ?>
											              </span>
											              <?php } ?>
											            </div>
											            <?php if($formatted_address != '') { ?>
											            <span class="address"><?=$formatted_address?></span>
											        	<?php } ?>
											            <span class="res-phone">
											              <i class="mdi mdi-phone">
											              </i>
											              999-999-999
											            </span>
											            <div class="more-holder">
											              <div class="tagging" onclick="explandTags(this)">
											                Popular with:
											                <span>point of interest
											                </span>
											                <span>establishment
											                </span>
											              </div>
											            </div>
											          </a>
											        </div>
											      </div>
											      <div class="expandable-area">
											      	<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt'),setHideHeader(this,'hotels','show')"><i class="mdi mdi-close"></i> Close</a>
											        <div class="clear">
											        </div>
											        <div class="explandable-tabs">
											          <ul class="tabs tabsnew subtab-menu">
											            <li class="tab">
											              <a class="active" href="#subtab-details2-<?=$i?>">Details
											              </a>
											            </li>
											            <li class="tab">
											              <a href="#subtab-reviews2-<?=$i?>">Reviews
											              </a>
											            </li>
											            <li class="tab">
											              <a data-which="photo" href="#subtab-photos2-<?=$i?>" data-tab="subtab-photos">Photos
											              </a>
											            </li>
											            <li class="tab lst-li">
											              <a href="#subtab-amenities2-<?=$i?>">Amenities
											              </a>
											            </li>
											          </ul>
											          <div class="tab-content">
											            <div id="subtab-details2-<?=$i?>" class="animated fadeInUp">
											              <div class="subdetail-box">
											                <div class="infoholder">
											                  <div class="descholder">
											                    <div class="more-holder">
											                      <ul class="infoul">
											                        <li> 
											                          <i class="zmdi zmdi-pin">
											                          </i> 
											                          <?=$adr?> 
											                        </li> 
											                        <li> 
											                          <i class="mdi mdi-phone">
											                          </i> 
											                          <?=$ipn?> 
											                        </li> 
											                        <li>
											                          <i class="mdi mdi-earth">
											                          </i>
											                          <?=$website?>
											                        </li>
											                        <li>
											                          <i class="mdi mdi-clock-outline">
											                          </i>
											                          Mon-Fri : 12:00 PM - 10:00 AM
											                        </li>
											                        <li>
											                          <i class="mdi mdi-certificate ">
											                          </i>
											                          Ranked #1 in Japan Hotels
											                        </li>
											                      </ul>
											                      <?php if(isset($pieces) && !empty($pieces)){ ?>
											                      <div class="tagging" onclick="explandTags(this)">
											                        Popular with:
											                        <?php foreach($pieces as $element) {
																	if(isset($element) && !empty($element)) {
																	echo "<span>".$element."</span> ";
																	}
																	} ?>
											                      </div>
											                      <?php } ?>
											                    </div>
											                  </div>
											                </div>						
											              </div>
											            </div>
											            <div id="subtab-reviews2-<?=$i?>" class="animated fadeInUp"> 
											              <div class="reviews-summery">
											                <div class="reviews-people">
											                  <ul>
											                    <li>
											                      <div class="reviewpeople-box">
											                        <div class="imgholder">
											                          <img src="<?=$baseUrl?>/images/people-3.png"/>
											                        </div>
											                        <div class="descholder">
											                          <h6>Kelly Mark 
											                            <span>about 2 weeks ago
											                            </span>
											                          </h6>
											                          <div class="stars-holder">	
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/blank-star.png"/>
											                            <img src="<?=$baseUrl?>/images/blank-star.png"/>
											                          </div>
											                          <div class="clear">
											                          </div>
											                          <p>We enjoyed the lounge and bar at the Ritz where you are offered many choices for drinks and some pretty elaborate looking dishes of food as well.
											                          </p>
											                        </div>
											                      </div>
											                    </li>
											                    <li>
											                      <div class="reviewpeople-box">
											                        <div class="imgholder">
											                          <img src="<?=$baseUrl?>/images/people-2.png"/>
											                        </div>
											                        <div class="descholder">
											                          <h6>John Davior 
											                            <span>about 8 months ago
											                            </span>
											                          </h6>
											                          <div class="stars-holder">	
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/blank-star.png"/>
											                          </div>
											                          <div class="clear">
											                          </div>
											                          <p>If you want a fancy London experience than The Ritz is where you need to go! At least budget for High Tea!
											                          </p>
											                        </div>
											                      </div>
											                    </li>
											                    <li>
											                      <div class="reviewpeople-box">
											                        <div class="imgholder">
											                          <img src="<?=$baseUrl?>/images/people-1.png"/>
											                        </div>
											                        <div class="descholder">
											                          <h6>Joe Doe 
											                            <span>about 11 months ago
											                            </span>
											                          </h6>
											                          <div class="stars-holder">	
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/filled-star.png"/>
											                            <img src="<?=$baseUrl?>/images/blank-star.png"/>
											                            <img src="<?=$baseUrl?>/images/blank-star.png"/>
											                          </div>
											                          <div class="clear">
											                          </div>
											                          <p>I am not at all sure this is the best hotel in London, but it does deserve the reputation as one of the most glamourous.
											                          </p>
											                        </div>
											                      </div>
											                    </li>
											                  </ul>
											                </div>
											              </div>
											            </div>
											            <div id="subtab-photos2-<?=$i?>" class="subtab-photos animated fadeInUp">
											              <div class="photo-gallery">			
											                <div class="img-preview">
											                  <img src="<?=$baseUrl?>/images/post-img1.jpg"/>
											                </div>
											                <div class="thumbs-img">
											                  <ul>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box">
											                        <img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box">
											                        <img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/>
											                      </a>
											                    </li>
											                    <li>
											                      <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
											                        <img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/>
											                      </a>
											                    </li>
											                  </ul>
											                </div>
											              </div>
											            </div>
											            <div id="subtab-amenities2-<?=$i?>" class=" animated fadeInUp">
											              <ul class="ul-amenities tab-amenities">
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-spa.png">
											                    <span>Spa
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-beach.png">
											                    <span>Beach
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-wifi.png">
											                    <span>Wifi
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-breakfast.png">
											                    <span>Breakfast
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-pool.png">
											                    <span>Pool
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-spa.png">
											                    <span>Spa
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-beach.png">
											                    <span>Beach
											                    </span>
											                  </a>
											                </li>
											                <li>
											                  <a href="javascript:void(0)">
											                    <img src="<?=$baseUrl?>/images/amenity-breakfast.png">
											                    <span>Breakfast
											                    </span>
											                  </a>
											                </li>
											              </ul>
											            </div>
											          </div>
											        </div>
											      </div>
											    </div>
											  </li>
											  <?php } } } else { ?>
											  <?php $this->context->getnolistfound('norestaurantsfound');?>
											  <?php } ?>
											</ul>
                                          <div class="pagination">
                                             <div class="link-holder">
                                                <a href="javascript:void(0)"><i class="mdi mdi-arrow-left-bold-circle"></i> Prev</a>
                                             </div>
                                             <?php if(isset($rs['next_page_token']) && !empty($rs['next_page_token'])){ ?>
                                             <div class="link-holder">
                                             	<a href="javascript:void(0)" onclick="displayplace('all','hotels','<?=$rs['next_page_token']?>');">Next <i class="mdi mdi-arrow-right-bold-circle"></i></a>
                                             </div>
                                             <?php } ?>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="moreinfo-box">
                                    <div class="fake-header">
                                       <!--<div class="page-name">Back to list</div>-->
                                       <div class="page-name"><a href="javascript:void(0)" onclick="closePlacesMoreInfo(this),setHideHeader(this,'hotels','show')"><i class="mdi mdi-arrow-left"></i>Back to list</a></div>
                                    </div>
                                    <div class="infoholder nice-scroll">
                                       <div class="imgholder"><img src="<?=$baseUrl?>/images/hotel1.png" /></div>
                                       <div class="descholder">
                                          <h4>The Guest House</h4>
                                          <div class="clear"></div>
                                          <div class="reviews-link">
                                             <span class="checks-holder">
                                             <i class="mdi mdi-star active"></i>
                                             <i class="mdi mdi-star active"></i>
                                             <i class="mdi mdi-star active"></i>
                                             <i class="mdi mdi-star active"></i>
                                             <i class="mdi mdi-star"></i>
                                             <label>34 Reviews</label>
                                             </span>
                                          </div>
                                          <span class="distance-info">Middle Eastem &amp; African, Mediterranean</span>
                                          <div class="clear"></div>
                                          <div class="more-holder">
                                             <ul class="infoul">
                                                <li>
                                                   <i class="zmdi zmdi-pin"></i>
                                                   132 Brick Lane | E1 6RU, Japan E1 6RU, Japan
                                                </li>
                                                <li>
                                                   <i class="mdi mdi-phone"></i>
                                                   +44 20 7247 8210
                                                </li>
                                                <li>
                                                   <i class="mdi mdi-earth"></i>
                                                   http://www.yourwebsite.com
                                                </li>
                                                <li>
                                                   <i class="mdi mdi-clock-outline"></i>
                                                   Today, 12:00 PM - 12:00 AM
                                                </li>
                                                <li>
                                                   <i class="mdi mdi-certificate "></i>
                                                   Ranked #1 in Japan Hotels
                                                </li>
                                             </ul>
                                             <div class="tagging" onclick="explandTags(this)">
                                                Popular with:
                                                <span>Budget</span>
                                                <span>Foodies</span>
                                                <span>Family</span>
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
        	</div>
        </div>
    </div>
</div>  
<script type="text/javascript">
var data1 = '';
var place = "<?php echo (string)$place?>";
var placetitle = "<?php echo (string)$placetitle?>";
var placefirst = "<?php echo (string)$placefirst?>";
var baseUrl = "<?php echo (string)$baseUrl; ?>";
var lat = "<?php echo $lat; ?>";
var lng = "<?php echo $lng; ?>";
</script>	
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>
<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/tours.js"></script>
<?php $this->endBody() ?> 