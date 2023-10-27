<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use backend\models\Googlekey;
use frontend\models\Ghotels; 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Hotel Map';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
$place = Yii::$app->params['place'];
$placetitle = Yii::$app->params['placetitle'];
$placefirst = Yii::$app->params['placefirst'];
$title = 'hotels';
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper hidemenu-wrapper full-wrapper noopened-search JIS3829 hotellist-page map-wrapper"> 
    <div class="header-section">
        <?php include('../views/layouts/header.php'); ?>
    </div>
    <?php include('../views/layouts/menu.php'); ?>
    <div class="floating-icon">
	   <div class="scrollup-btnbox anim-side btnbox scrollup-float">
	      <div class="scrollup-button float-icon">
	         <span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span>
	      </div>
	   </div> 
	</div>
	<div class="clear"></div>
	<?php include('../views/layouts/leftmenu.php'); ?>
	<div class="fixed-layout">
	   <div class="main-content hotels-page main-page places-page pb-0">
	   		<div class="places-mapview hasfitler hotels-wrapper showMapView">
				<div class="search-area side-area map-searchfilter">
	               <div class="closemap center">
	                  <a href="hotels.php" class="btn-closemap"><i class="zmdi zmdi-close mdi-20px"></i> Close Map</a>
	               </div>
	               <div class="sidetitle">
	                  <a href="javascript:void(0)" class="expand-link" onclick="mng_filter_sort(this, 'sort')"><i class="mdi mdi-sort mdi-16px"></i> Sort</a>
	                  <a href="javascript:void(0)" class="expand-link" onclick="mng_filter_sort(this, 'filter')"><i class="mdi mdi-tune mdi-16px"></i>Filter</a>
	                  <a href="hotels.php" class="expand-link list"><i class="mdi mdi-format-list-bulleted mdi-16px"></i>List</a>
	               </div>
	               <div class="expandable-area">
	                  <div class="content-box bshadow">
	                     <a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
	                        <i class="mdi mdi-close"></i>
	                     </a>
	                     <div class="filter-sort">
	                        <div class="cbox-desc filter-sec">
	                           <div class="srow mb0">
	                              <h6 class="mb-20">Filter</h6>
	                           </div>
	                           <div class="srow mb0">
	                              <h6 class="mb-20">Your Budget</h6>
	                              <div class="range-slider price-slider">
	                                 <div id="price-slider"></div>
	                                 <!-- Values -->                                   
	                                 <div class="row mb-0 mt-10">
	                                    <div class="range-value col s6">
	                                       <span id="value-min">$0</span>
	                                    </div>
	                                    <div class="range-value col s6 right-align">
	                                       <span id="value-max">$5000</span>
	                                    </div>
	                                 </div>
	                              </div>
	                           </div>
	                           <div class="srow">
	                              <h6>Rate Options</h6>
	                              <ul>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="freeCan" />
	                                          <label for="freeCan">
	                                             <span class="stars-holder">Free Cancellation</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="reserve">
	                                          <label for="reserve">
	                                             <span class="stars-holder">Reserve now, pay at stay</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                              </ul>
	                           </div>
	                           <div class="srow traveler-rating-sec">
	                              <h6>Guest rating</h6>
	                              <ul>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" name="traveler-rating" id="test6">
	                                          <label for="test6">
	                                             <span class="checks-holder">
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                             </span>
	                                             <span class="filter-count">126</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" name="traveler-rating" id="test7">
	                                          <label for="test7">
	                                             <span class="checks-holder">
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <span>& up</span>
	                                             </span>
	                                             <span class="filter-count">345</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" name="traveler-rating" id="test8">
	                                          <label for="test8">
	                                             <span class="checks-holder">
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <span>& up</span>
	                                             </span>
	                                             <span class="filter-count">343</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" name="traveler-rating" id="test9">
	                                          <label for="test9">
	                                             <span class="checks-holder">
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <span>& up</span>
	                                             </span>
	                                             <span class="filter-count">653</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" name="traveler-rating" id="test10">
	                                          <label for="test10">
	                                             <span class="checks-holder">
	                                                <i class="mdi mdi-radiobox-marked active"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <i class="mdi mdi-radiobox-marked"></i>
	                                                <span>& up</span>
	                                             </span>
	                                             <span class="filter-count">234</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                              </ul>
	                           </div>
	                           <div class="srow">
	                              <h6>Hotel Class</h6>
	                              <ul>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="five_star" />
	                                          <label for="five_star">
	                                             <span class="stars-holder">5 stars</span>
	                                             <span class="filter-count">26</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="four_star">
	                                          <label for="four_star">
	                                             <span class="stars-holder">4 stars</span>
	                                             <span class="filter-count">23</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="three_star">
	                                          <label for="three_star">
	                                             <span class="stars-holder">3 stars</span>
	                                             <span class="filter-count">76</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="two_star">
	                                          <label for="two_star">
	                                             <span class="stars-holder">2 stars</span>
	                                             <span class="filter-count">89</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="checkbox" id="one_star">
	                                          <label for="one_star">
	                                             <span class="stars-holder">1 stars</span>
	                                             <span class="filter-count">12</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                              </ul>
	                           </div>
	                           <div class="srow">
	                              <h6>Amenities</h6>
	                              <ul class="ul-amenities">
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-spa.png" /><span>Spa</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-beach.png" /><span>Beach</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-wifi.png" /><span>Wifi</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-breakfast.png" /><span>Breakfast</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-pool.png" /><span>Pool</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-spa.png" /><span>Spa</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-beach.png" /><span>Beach</span></a>
	                                 </li>
	                                 <li>
	                                    <a href="javascript:void(0)"><img src="images/amenity-breakfast.png" /><span>Breakfast</span></a>
	                                 </li>
	                              </ul>
	                           </div>
	                           <div class="btn-holder">
	                              <a href="javascript:void(0)" class="btn-custom">Reset Filters</a>
	                           </div>
	                        </div>
	                        <div class="cbox-desc sort-sec">
	                           <div class="srow mb0">
	                              <h6 class="mb-20">Sort</h6>
	                           </div>
	                           <div class="srow traveler-rating-sec">
	                              <ul>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" class="with-gap" name="traveler-rating" id="test1">
	                                          <label for="test1">
	                                             <span class="checks-holder">Traveler Ranked</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" class="with-gap" name="traveler-rating" id="test2" checked>
	                                          <label for="test2">
	                                             <span class="checks-holder">Best Value</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" class="with-gap" name="traveler-rating" id="test3">
	                                          <label for="test3">
	                                             <span class="checks-holder">Price (low to high)</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                                 <li>
	                                    <div class="entertosend leftbox">
	                                       <p>
	                                          <input type="radio" class="with-gap" name="traveler-rating" id="test4">
	                                          <label for="test4">
	                                             <span class="checks-holder">Distance to city center</span>
	                                          </label>
	                                       </p>
	                                    </div>
	                                 </li>
	                              </ul>
	                           </div>
	                        </div>
	                     </div>
	                  </div>
	               </div>
	            </div>
				<div class="list-box moreinfo-outer">
					<div class="sidelist nice-scroll">
						<div class="hotels-page">
							<div class="hotel-list" id="mapinfo">
								<ul>									
								<?php 
								$rs = Ghotels::find()->asarray()->one();
								if(isset($rs['results']) && !empty($rs['results'])) {
									$hotel = isset($rs['results']) ? $rs['results'] : array();
									for($i=0;$i<20;$i++){
									    if(isset($hotel[$i]['place_id']) && !empty($hotel[$i]['place_id'])){
										    $place_id = $hotel[$i]['place_id'];
										    $pieces = $hotel[$i]['types'];
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

										    $name = $hotel[$i]['name'];
										    $rating = $hotel[$i]['rating'];
										    
										    ?>
											<li>
											  <div class="hotel-li">
											     <a href="javascript:void(0)" class="summery-info" onclick="openPlacesMoreInfo(this)">
											        <div class="imgholder himg-box">
														<img src="<?=$img?>" class="<?=$imgclass?>">
											        </div>
											        <div class="descholder">
											           <h4><?=$name?></h4>
											           <div class="clear"></div>
											           <div class="reviews-link">
															<?php if(isset($hotel[$i]['rating']) && !empty($hotel[$i]['rating'])){ ?>
																<span class="checks-holder">
																	<?php for($j=0;$j<5;$j++){ ?>
																	<i class="mdi mdi-radiobox-marked <?php if($j < $rating){ ?>active <?php } ?>"> </i>
																	<?php } ?>
											              			<label>34 Reviews</label>
																</span>
															<?php } ?>
											           </div>
											           <div class="hotel-price">
				                                          <span>JOD 184*</span>
				                                       </div>
				                                       <button class="btn-viewdet" onclick="openPlacesMoreInfo(this)">View Deal</button>
											        </div>
											     </a>
											  </div>
											</li>
											<?php
										}
									}
								}
								?>
								</ul>
							</div>
						</div>
					</div>                          
					<div class="moreinfo-box">
	                  <a href="javascript:void(0)" onclick="closePlacesMoreInfo(this)" class="backarrow"><i class="mdi mdi-arrow-left-bold-circle"></i></a>
	                  <div class="infoholder nice-scroll">
	                     <div class="imgholder"><img src="images/hotel1.png" /></div>
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
				<div class="map-box" id="mapdisplay">
					<div class="map-wrapper">
						<div id="map_canvas" class="mapping"></div>
					</div>
					<div class="overlay">
						<a href="javascript:void(0)" onclick="closeMapView()">Back to list view</a>
					</div>
				</div>
			</div> 
       </div>
    </div>
</div>  
<input type="hidden" id="mapinfolodge" value="0"/>
<script>
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
<script type="text/javascript" src="<?=$baseUrl?>/js/hotelmap.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$.ajax({
			type: 'POST',  
			url: '?r=site/getmapinfo',
			data: 'placetitle=Japan&type=lodge',
			success: function (data) {
				console.log(data);
				$("#mapinfo").html(data);
			}
		});
	}); 
</script>
<?php $this->endBody() ?> 