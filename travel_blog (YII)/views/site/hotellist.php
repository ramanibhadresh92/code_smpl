<?php  
use yii\helpers\Url; 
use frontend\assets\AppAsset;
use frontend\models\Ghotels; 
use backend\models\Googlekey;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Hotels';
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
<script src=" 
  <?=$baseUrl?>/js/chart.js">
</script>
<div class="page-wrapper full-wrapper noopened-search JIS3829 hotellist-page hotellist-page">
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
  <?php include('../views/layouts/leftmenu.php'); ?>
  <div class="fixed-layout">
     <div class="main-content with-lmenu hotels-page main-page transheader-page">
        <div class="combined-column hotels-wrapper wide-open">
           <div class="content-box">
              <div class="banner-section">
                 <div class="search-whole hotels-search container">
                    <div class="frow">
                       <div class="row row-option">
                          <div class="col m3 s12 map-desktop">
                             <div class="map-holder">
                                <div id="hotelmaplink"> 
                                  <a href="<?=Url::to(['site/hotelmap'])?>"><i class="mdi mdi-map-marker"></i> View map</a>
                                </div>
                                <iframe width="720" height="600" src="https://maps.google.com/maps?width=720&amp;height=600&amp;hl=en&amp;coord=<?=$lat?>,<?=$lng?>&amp;q=+(japan)&amp;ie=UTF8&amp;t=&amp;z=12&amp;iwloc=B&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"><a href="https://www.maps.ie/coordinates.html">latitude longitude finder</a></iframe>
                             </div>
                          </div>
                          <div class="col m9 s12 filter-inputs">
                             <h3 class="hotels-hdng">Japan Hotels</h3>
                             <p class="filters-price">Lowest prices for</p>  
                             <div class="col l4 m4 s12">
                                <div class="sliding-middle-out input-field anim-area dateinput fullwidth">
                                   <input type="text" placeholder="— / — / —" class="form-control datepickerinput check-in" data-query="M" data-toggle="datepicker" readonly>
                                   <label for="first_name" class="active">Check In</label>
                                </div>
                             </div>
                             <div class="col l4 m4 s12">
                                <div class="sliding-middle-out input-field anim-area dateinput fullwidth">
                                   <input type="text" placeholder="— / — / —" class="form-control datepickerinput check-out" data-query="M" data-toggle="datepicker" readonly>
                                   <label for="first_name" class="active">Check Out</label>
                                </div>
                             </div>
                             <div class="col l4 m4 s12">
                                <div class="custom-drop">
                                   <div class="dropdown input-field dropdown-custom dropdown-xsmall">
                                      <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-rooms">
                                      <i class="mdi mdi-account"></i><span class="sword">Single Room</span> <span class="mdi mdi-menu-down right caret"></span>
                                      </a>
                                      <label for="first_name" class="active">Guests</label>
                                      <ul class="dropdown-content" id="dropdown-rooms">
                                         <li><a href="javascript:void(0)"><span class="sword">Single Room</span></a></li>
                                         <li><a href="javascript:void(0)"><i class=”mdi mdi-account-group”></i></i><span class="sword">Double Room</span></a></li>
                                      </ul>
                                   </div>
                                </div>
                             </div>
                          </div>
                       </div>
                       <div class="row filter-map">
                          <a href="javascript:void(0)" class="col s12 toggle-map" onclick="toggleDiv('div1', 'hotelId')"><i class="mdi mdi-map-marker"></i>Map</a>
                       </div>
                       <div class="row m-0 map-mobile toggle-maphotels" id="div1">
                          <div class="map-holder">
                             <iframe width="720" height="600" src="https://maps.google.com/maps?width=720&amp;height=600&amp;hl=en&amp;coord=<?=$lat?>,<?=$lng?>&amp;q=+(japan)&amp;ie=UTF8&amp;t=&amp;z=12&amp;iwloc=B&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"><a href="https://www.maps.ie/coordinates.html">latitude longitude finder</a></iframe>
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
              <div class="container">
                 <div class="cbox-desc">
                    <div class="outer-holder">
                       <div class="row">
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
                                   <!-- <div class="cbox-title">
                                      Narrow your search results
                                   </div> -->
                                   <div class="cbox-desc">
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
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-spa.png" /><span>Spa</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-beach.png" /><span>Beach</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-wifi.png" /><span>Wifi</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-breakfast.png" /><span>Breakfast</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-pool.png" /><span>Pool</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-spa.png" /><span>Spa</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-beach.png" /><span>Beach</span></a>
                                            </li>
                                            <li>
                                               <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-breakfast.png" /><span>Breakfast</span></a>
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
                                <h6>1300 hotels found in <span><?=$placefirst?></span></h6>
                             </div>
                             <div class="hotel-list">
                                <div class="moreinfo-outer">
                                   <div class="places-content-holder">
                                      <div class="list-holder">
                                         <div class="hotel-list">
<ul>
<?php 
  /*$ch = curl_init(); 
  curl_setopt($ch, CURLOPT_URL, 'https://maps.googleapis.com/maps/api/place/textsearch/json?key='.$GApiKeyP.'&query=hotels+in+'.$placeapi.'&type=lodging&next_page_token='.$tk.'');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  $output = curl_exec($ch);   
  $rs = json_decode($output, true);*/

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
            if(isset($hotell['address_components'][0]['long_name']) && !empty($hotell['address_components'][0]['long_name'])) {
                $shadr = $hotell['address_components'][0]['long_name'];
            }

            if(isset($hotell['vicinity']) && !empty($hotell['vicinity'])) {
                $adr = $hotell['vicinity'];
            }
            if(isset($hotell['website']) && !empty($hotell['website'])) {
                $website = $hotell['website'];
            }
            if(isset($hotell['international_phone_number']) && !empty($hotell['international_phone_number'])) {
                $ipn = $hotell['international_phone_number'];
            }
        }
        ?>
    <li>
      <div class="hotel-li expandable-holder dealli mobilelist">
      <div class="summery-info">
      <div class="imgholder <?=$imgclass?>-box">
        <!-- <div class="owl-carousel owl-theme owl-hotels">
          <div class="item"> -->
            <img src="<?=$img?>" class="<?=$imgclass?>">
          <!-- </div>
                                                              </div> -->
      </div>
      <div class="descholder">
        <a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose')">
          <h4>
            <?=$hotel[$i]['name']?>
          </h4>
          <p class="hotel-location">Japan</p>
          <div class="clear"></div>
          <div class="reviews-link">
            <span class="review-count">
              <?=rand(1,20);?> Reviews
                                                    
            </span>
            <?php if(isset($hotel[$i]['rating']) && !empty($hotel[$i]['rating'])){ ?>
            <span class="checks-holder">
              <?php for($j=0;$j<5;$j++){ ?>
                <i class="mdi mdi-radiobox-marked <?php if($j < $hotel[$i]['rating']){ ?>active <?php } ?>"> </i>
                <?php } ?>
              </span>
              <?php } ?>
            </div>
            <div class="hotel-amenities">
               <ul class="ul-amenities mb0">
                  <li>
                     <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-spa.png" /><span>Spa</span></a>
                  </li>
                  <li>
                     <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-beach.png" /><span>Beach</span></a>
                  </li>
                  <li>
                     <a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-wifi.png" /><span>Wifi</span></a>
                  </li>
                  <li>
                     <a href="javascript:void(0)" data-activates="moreAmenities<?=$place_id?>" class="dropdown-button more-amenities"><i class="zmdi zmdi-more"></i></a>
                     <ul id="moreAmenities<?=$place_id?>" class="dropdown-content custom_dropdown">
                        <div class="amenities-list">
                           <ul class="mb0">
                              <li><i class="mdi mdi-wifi"></i>Free Internet Access</li>
                              <li><i class="mdi mdi-wifi"></i>Free Internet Access</li>
                              <li><i class="mdi mdi-wifi"></i>Free Internet Access</li>
                              <li><i class="mdi mdi-wifi"></i>Free Internet Access</li>
                              <li><i class="mdi mdi-wifi"></i>Free Internet Access</li>
                              <li><i class="mdi mdi-wifi"></i>Free Internet Access</li>
                           </ul>
                        </div>
                     </ul>
                  </li>
               </ul>
            </div>
            <div class="hotel-rates">
               <a href="javascript:void(0)">
                  <span class="h-name">Hotels.com</span>
                  <span class="h-price">PKR 29,510</span>
               </a>
               <a href="javascript:void(0)">
                  <span class="h-name">Booking.com</span>
                  <span class="h-price">PKR 29,510</span>
               </a>
               <a href="javascript:void(0)" data-activates="moreHotelRates1" class="dropdown-button more-h-rates"><i class="mdi mdi-chevron-down"></i></a>
               <ul id="moreHotelRates1" class="dropdown-content custom_dropdown">
                  <li>
                     <a href="javascript:void(0)">
                        <span class="price">USD 257 </span>
                        <span class="provider-name">Hotels.com</span>
                     </a>
                  </li> 
                  <li>
                     <a href="javascript:void(0)">
                        <span class="price">USD 245 </span>
                        <span class="provider-name">Preffered Hotel</span>
                     </a>
                  </li>
               </ul>
            </div>
            <!-- <span class="address">Dubai, Dubai(Emirates), United Arab Emirates</span>
            <span class="distance-info">2.2 miles to City center</span> -->
            <!-- <span class="moredeals-link">More Info</span> -->
          </a>
          <div class="info-action">
            <span class="stars-holder">
              <i class="mdi mdi-star active"></i>
              <i class="mdi mdi-star active"></i>
              <i class="mdi mdi-star active"></i>
              <i class="mdi mdi-star active"></i>
              <i class="mdi mdi-star active"></i>
            </span>
            <div class="clear"></div>
            <span class="sitename">booking.com</span>
            <div class="clear"></div>
            <span class="price">USD                  
              <?=rand(75,150)?>*                
            </span>
            <div class="clear"></div>
            <a href="<?=$website?>" target="_new" class="deal-btn">Book Now
              <i class="mdi mdi-chevron-right"></i>
            </a>
            <div class="hotels-merchandise">
               <span><i class="mdi mdi-check"></i>Free cancellation</span>
               <span><i class="mdi mdi-check"></i>Reserve now, pay at stay</span>
            </div>
            <div class="hotel-website">
               <a href="javascript:void(0)">
                  <i class="zmdi zmdi-globe-alt"></i>
                  <span>Visit hotel website
                  <i class="zmdi zmdi-arrow-right-top"></i></span>
               </a>
            </div>
          </div>
        </div>
      </div>
      <div class="expandable-area">
        <a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt')">
          <i class="mdi mdi-close "></i> Close                                          
        </a>
        <div class="clear"></div>
        <div class="explandable-tabs">
          <ul class="tabs tabsnew subtab-menu" style="width: 70% !important">
            <li class="tab">
              <a href="#subtab-details-<?=$i?>">Additional Info </a>
            </li>
            <li class="tab">
              <a href="#subtab-reviews-<?=$i?>">Reviews</a>
            </li>
            <li class="tab">
              <a data-which="photo" href="#subtab-photos-<?=$i?>" data-tab="subtab-photos">Photos</a>
            </li>
            <li class="tab">
              <a href="#subtab-amenities-<?=$i?>">Amenities</a>
            </li>
          </ul>
          <div class="tab-content">
            <div id="subtab-details-<?=$i?>" class="">
              <div class="subdetail-box">
                <div class="infoholder">
                  <div class="descholder">
                    <div class="more-holder">
                      <?php if($shadr != '' || $adr != '' || $website != '' || $ipn != '') { ?>
                      <ul class="infoul">
                        <?php if($adr != '') { ?>
                        <li>
                          <i class="zmdi zmdi-pin"></i>
                          <?=$adr?>
                        </li>
                        <?php } ?>
                        
                        <?php if($ipn != '') { ?>
                        <li>
                          <i class="mdi mdi-phone"></i>
                          <?=$ipn?>
                        </li>
                        <?php } ?>
                        
                        <?php if($website != '') { ?>
                        <li>
                          <i class="mdi mdi-earth"></i>
                          <?=$website?>
                        </li>
                        <?php } ?>
                      </ul>
                      <?php } ?>
                      <?php if(isset($pieces) && !empty($pieces)){ ?>
                      <div class="tagging" onclick="explandTags(this)"> Popular with: 
                      <?php 
                        foreach($pieces as $element) {
                          if(isset($element) && !empty($element)) {
                            echo "<span>".$element."</span> "; 
                          } 
                        } 
                      ?>
                      </div>
                      <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div id="subtab-reviews-<?=$i?>" class="tab-pane fade">
              <div class="reviews-summery">
                <div class="reviews-people">
                  <ul>
                    <li>
                      <div class="reviewpeople-box">
                        <div class="imgholder">
                          <img src="<?=$baseUrl?>/images/people-3.png" />
                        </div>
                        <div class="descholder">
                          <h6>Kelly Mark                            
                            <span>about 2 weeks ago</span>
                          </h6>
                          <div class="stars-holder">
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/blank-star.png" />
                            <img src="<?=$baseUrl?>/images/blank-star.png" />
                          </div>
                          <div class="clear"></div>
                          <p>We enjoyed the lounge and bar at the Ritz where you are offered many choices for drinks and some pretty elaborate looking dishes of food as well.</p>
                        </div>
                      </div>
                    </li>
                    <li>
                      <div class="reviewpeople-box">
                        <div class="imgholder">
                          <img src="<?=$baseUrl?>/images/people-2.png" />
                        </div>
                        <div class="descholder">
                          <h6>John Davior
                            <span>about 8 months ago</span>
                          </h6>
                          <div class="stars-holder">
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/blank-star.png" />
                          </div>
                          <div class="clear"></div>
                          <p>If you want a fancy London experience than The Ritz is where you need to go! At least budget for High Tea!</p>
                        </div>
                      </div>
                    </li>
                    <li>
                      <div class="reviewpeople-box">
                        <div class="imgholder">
                          <img src="<?=$baseUrl?>/images/people-1.png" />
                        </div>
                        <div class="descholder">
                          <h6>Joe Doe
                            <span>about 11 months ago</span>
                          </h6>
                          <div class="stars-holder">
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/filled-star.png" />
                            <img src="<?=$baseUrl?>/images/blank-star.png" />
                            <img src="<?=$baseUrl?>/images/blank-star.png" />
                          </div>
                          <div class="clear"></div>
                          <p>I am not at all sure this is the best hotel in London, but it does deserve the reputation as one of the most glamourous.</p>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div id="subtab-photos-<?=$i?>" class="subtab-photos">
              <div class="photo-gallery">
                <div class="img-preview">
                  <img src="<?=$baseUrl?>/images/post-img1.jpg" />
                  </div>
                  <div class="thumbs-img">
                    <ul>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img class="himg" src="
                        <?=$baseUrl?>/images/post-img1.jpg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img2.jpg" class="himg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img3.jpg" class="vimg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img4.jpg" class="himg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img5.jpg" class="himg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img class="himg" src="
                        <?=$baseUrl?>/images/post-img1.jpg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img2.jpg" class="himg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img3.jpg" class="vimg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img4.jpg" class="himg"/>
                        </a>
                      </li>
                      <li>
                        <a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box">
                        <img src="
                        <?=$baseUrl?>/images/post-img5.jpg" class="himg"/>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div id="subtab-amenities-<?=$i?>" class="tab-pane fade">
              </div>
            </div>
          </div>
        </div>
      </div>
    </li>
    <?php 
    } 
  } } else { ?>
  <?php $this->context->getnolistfound('nohotelsfound');?>
  <?php } ?>
</ul>
                                            <div class="pagination">
                                               <div class="link-holder">
                                                  <a href="javascript:void(0)"><i class="mdi mdi-arrow-left-bold-circle"></i> Prev</a>
                                               </div>
                                               <?php if(isset($rs['next_page_token']) && !empty($rs['next_page_token'])) { ?>
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
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=
  <?=$GApiKeyL?>&libraries=places&callback=initAutocomplete">
</script>
<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="
  <?=$baseUrl?>/js/tours.js">
</script>
<script type="text/javascript">
   $(".owl-hotels").owlCarousel({
 
      navigation : true, // Show next and prev buttons
      slideSpeed : 300,
      paginationSpeed : 400,
      singleItem:true,
      loop: true,
      dots: false,
      nav: true,
      navText:["<i class='mdi mdi-chevron-left'></i>","<i class='mdi mdi-chevron-right'></i>"],
      //"singleItem:true" is a shortcut for:
      items : 1, 
      itemsDesktop : false,
      itemsDesktopSmall : false,
      itemsTablet: false,
      itemsMobile : false
 
   });

   if($('#price-slider').length) {
         var slider = document.getElementById('price-slider');
       noUiSlider.create(slider, {start: [0, 5000],
       connect: true,  
       step: 1,
       orientation: 'horizontal', // 'horizontal' or 'vertical'
       range: {'min': 0,'max': 5000 },
       format: wNumb({ decimals: 0})
       });
    }   
</script>

