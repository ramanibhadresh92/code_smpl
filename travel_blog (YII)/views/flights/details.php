<?php  
use frontend\assets\AppAsset;
use backend\models\Googlekey;
use frontend\models\PlaceReview; 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Flights';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper full-wrapper noopened-search JIS3829 hotellist-page hotellist-page flight-page">
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
     <div class="main-content with-lmenu hotels-page  main-page transheader-page">
        <div class="combined-column hotels-wrapper wide-open">
           <div class="content-box">
              <div class="banner-section">
                 <div class="search-whole hotels-search container mt-0">
                    <div class="frow">
                       <div class="row">
                          <div class="trip-type inline-radio">
                             <input name="tripType" checked="" type="radio" class="with-gap" id="round" value="round">
                             <label for="round">Round-trip</label>
                             <input name="tripType" type="radio" class="with-gap" id="oneway" value="oneway">
                             <label for="oneway">One-way</label>
                             <input name="tripType" type="radio" class="with-gap" id="multi" value="multi">
                             <label for="multi">Multi-destination</label>     
                          </div>
                       </div>
                       <div class="clear"></div>
                       <div class="row row-option">
                          <div class="cols12">
                             <div class="col l3 m3 s6">
                                <div class="sliding-middle-out input-field anim-area where-from dateinput fullwidth">
                                   <input type="text" placeholder="Where from?" class="form-control check-in" >
                                </div>
                             </div>
                             <div class="col l3 m3 s6">
                                <div class="sliding-middle-out input-field anim-area where-to dateinput fullwidth">
                                   <input type="text" placeholder="Where to?" class="form-control check-in">
                                </div>
                             </div>
                             <div class="col l3 m3 s6">
                                <div class="sliding-middle-out input-field anim-area dateinput fullwidth">
                                   <input type="text" placeholder="Departing - Returning" class="form-control check-out dept-return" data-query="M" data-toggle="datepicker" readonly>
                                </div>
                             </div>
                             <div class="col l3 m3 s6">
                                <div class="custom-drop">
                                   <div class="dropdown input-field dropdown-custom dropdown-xsmall">
                                      <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-rooms">
                                      <i class="mdi mdi-account"></i><span class=>1 Adult</span> <span class="mdi mdi-menu-down right caret"></span>
                                      </a>
                                      <ul class="dropdown-content" id="dropdown-rooms">
                                         <li><a href="javascript:void(0)"><span class="">1 Adult</a></li>
                                         <li><a href="javascript:void(0)"><i class=”mdi mdi-account-group”></i></i><span class="">2 Adults</span></a></li>
                                      </ul>
                                   </div>
                                </div>
                             </div>
                          </div>
                       </div>
                       <!-- <div class="row filter-map"> 
                          <a href="javascript:void(0)" class="col s6 toggle-map" onclick="toggleDiv('div1', 'hotelId')"><i class="mdi mdi-map-marker"></i>Map</a>
                          <a href="javascript:void(0)" class="col s6 toggle-nearhotels" onclick="toggleDiv('div2', 'hotelId')"><i class="mdi mdi-hotel"></i>Near Hotels</a>
                       </div>
                       <div class="row m-0 map-mobile toggle-maphotels" id="div1">
                          <div class="map-holder">
                             <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3110.3465133386144!2d-9.167423685010494!3d38.77868997958898!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd193295d5b45545%3A0x3f9e7b6a5f00e12c!2sPerta!5e0!3m2!1sen!2sin!4v1481089901870" width="600" height="450" frameborder="0" allowfullscreen></iframe>
                          </div>
                       </div> -->
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
                                   <div class="cbox-desc">
                                      <div class="srow">
                                         <h6>Stops</h6>
                                         <ul>
                                            <li>
                                               <div class="entertosend leftbox">
                                                  <p>
                                                     <input type="checkbox" id="nonstop" />
                                                     <label for="nonstop">
                                                        <span class="stars-holder">nonstop</span>
                                                     </label>
                                                  </p>
                                               </div>
                                            </li>
                                            <li>
                                               <div class="entertosend leftbox">
                                                  <p>
                                                     <input type="checkbox" id="stop1">
                                                     <label for="stop1">
                                                        <span class="stars-holder">up to 1 stop</span>
                                                        <span class="filter-count">$763</span>
                                                     </label>
                                                  </p>
                                               </div>
                                            </li>
                                            <li>
                                               <div class="entertosend leftbox">
                                                  <p>
                                                     <input type="checkbox" id="stops">
                                                     <label for="stops">
                                                        <span class="stars-holder">up to stops</span>
                                                        <span class="filter-count">$804</span>
                                                     </label>
                                                  </p>
                                               </div>
                                            </li>
                                         </ul>
                                      </div>
                                      <div class="srow mb0">
                                         <h6 class="mb-10">Takeoff from AMM</h6>
                                         <p class="mt-0 mb-15">Thu 1:40a to Thu 11:55p</p>
                                         <div class="range-slider price-slider">
                                            <div id="takeoff-slider"></div>
                                            <!-- Values -->                                   
                                            <div class="row mb-0 mt-10">
                                               <div class="range-value col s6">
                                                  <!-- <span id="value-min">$0</span> -->
                                               </div>
                                               <div class="range-value col s6 right-align">
                                                  <!-- <span id="value-max">$5000</span> -->
                                               </div>
                                            </div>
                                         </div>
                                      </div>
                                      <div class="srow">
                                         <h6>Airlines</h6>
                                         <ul>
                                            <li>
                                               <div class="entertosend leftbox">
                                                  <p>
                                                     <input type="checkbox" id="airlines1">
                                                     <label for="airlines1">
                                                        <span class="stars-holder">Etihad Airways</span>
                                                        <span class="filter-count">$763</span>
                                                     </label>
                                                  </p>
                                               </div>
                                            </li>
                                            <li>
                                               <div class="entertosend leftbox">
                                                  <p>
                                                     <input type="checkbox" id="airlines2">
                                                     <label for="airlines2">
                                                        <span class="stars-holder">Austrian Airlines</span>
                                                        <span class="filter-count">$804</span>
                                                     </label>
                                                  </p>
                                               </div>
                                            </li>
                                            <li>
                                               <div class="entertosend leftbox">
                                                  <p>
                                                     <input type="checkbox" id="airlines3">
                                                     <label for="airlines3">
                                                        <span class="stars-holder">United Airlines</span>
                                                        <span class="filter-count">$967</span>
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
                                            <option>Best</option>
                                            <option>Cheapest first</option>
                                            <option>Fastest first</option>
                                         </select>
                                      </div>
                                   </div>
                                </div>
                                <h6>1300 flights found in <span>Japan</span></h6>
                             </div>
                             <div class="flight-tabs">
                                <div class="row">
                                   <div class="col s12">
                                      <ul class="tabs">
                                         <li class="tab col s4">
                                            <a class="active" href="#bestTab" data-toggle="tab" aria-expanded="true">
                                               <div class="tab-wrapper">
                                                  <p>Best</p>
                                                  <span class="amount">£772</span>
                                                  <p class="">22h 38 <span>(average)</span></p>
                                               </div>
                                            </a>
                                         </li>
                                         <li class="tab col s4">
                                            <a href="#cheapestTab" data-toggle="tab" aria-expanded="false">
                                               <div class="tab-wrapper">
                                                  <p>Cheapest</p>
                                                  <span class="amount">£772</span>
                                                  <p class="">22h 38 <span>(average)</span></p>
                                               </div>
                                            </a>
                                         </li>
                                         <li class="tab col s4">
                                            <a href="#fastestTab" data-toggle="tab" aria-expanded="false">
                                               <div class="tab-wrapper">
                                                  <p>Fastest</p>
                                                  <span class="amount">£12,66</span>
                                                  <p class="">16h 38 <span>(average)</span></p>
                                               </div>
                                            </a>
                                         </li>
                                      </ul>
                                   </div>
                                   <div class="tab-content">
                                      <div class="tab-pane fade main-pane col s12 active in" id="bestTab">
                                         <div class="row mx-0">
                                            <div class="view-items">
                                               <div class="ticket-wrapper">
                                                  <div class="flightsticket-container">
                                                     <div class="flightsticket-link">
                                                        <div class="ticket-notches">
                                                           <div class="ticket-paper">
                                                              <div class="flightsticket-bodycontainer">
                                                                 <div class="ticketbody-container">
                                                                    <div class="ticketbody-legscontainer">
                                                                       <div class="legdetails-container ist">
                                                                          <div class="logoimage-container">
                                                                             <div class="leglogo-legimage">
                                                                                <div class="bpk-image">
                                                                                   <img class="" alt="Emirates" src="//www.skyscanner.net/images/airlines/small/QR.png">
                                                                                </div>
                                                                             </div>
                                                                             <span class="airline-name">
                                                                                Qatar Airways
                                                                             </span>
                                                                             <div class="clear"></div>
                                                                             <span class="airline-number">
                                                                                650 - 750
                                                                             </span>
                                                                          </div>
                                                                          <div class="leginfo">
                                                                             <div class="leginfo_route">
                                                                                <span class="leginfo-time">
                                                                                   <div>
                                                                                      <span class="bpktext-text">21:35</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpktext-base">
                                                                                   <span class="leginfo-routepartial">AMM</span>
                                                                                </span>
                                                                             </div>
                                                                             <div class="leginfo-stopscontainer">
                                                                                <span class="bpktext-duration">25h 20</span>
                                                                                <ul class="leginfo-stopLine">
                                                                                   <li class="leginfo_stopdot"></li>
                                                                                   <svg version="1.1" id="Layer_1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve" class="leginfo_planeend"><path fill="#898294" d="M3.922,12h0.499c0.181,0,0.349-0.093,0.444-0.247L7.949,6.8l3.233-0.019C11.625,6.791,11.989,6.44,12,6 c-0.012-0.44-0.375-0.792-0.818-0.781L7.949,5.2L4.866,0.246C4.77,0.093,4.602,0,4.421,0L3.922,0c-0.367,0-0.62,0.367-0.489,0.71 L5.149,5.2l-2.853,0L1.632,3.87c-0.084-0.167-0.25-0.277-0.436-0.288L0,3.509L1.097,6L0,8.491l1.196-0.073 C1.382,8.407,1.548,8.297,1.632,8.13L2.296,6.8h2.853l-1.716,4.49C3.302,11.633,3.555,12,3.922,12"></path></svg>
                                                                                </ul>
                                                                                <div class="leginfo-stopslabelcontainer">
                                                                                   <span class="leginfo_stopslabelred">1 stop</span>
                                                                                   <div><span class="leginfo_stopstation">DXB</span></span></div>
                                                                                </div>
                                                                             </div>
                                                                             <div class="leginfo-routepartialarrive">
                                                                                <span class="leginfo-routepartialtime">
                                                                                   <div>
                                                                                      <span class="bpktext-text">14:55</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpk-text-base">
                                                                                   <span class="">ORD</span>
                                                                                </span>
                                                                             </div>
                                                                          </div>
                                                                       </div>
                                                                       <div class="legdetails-container">
                                                                          <div class="logoimage-container">
                                                                             <div class="leglogo-legimage">
                                                                                <div class="bpk-image">
                                                                                   <img class="" alt="Emirates" src="//www.skyscanner.net/images/airlines/small/QR.png">
                                                                                </div>
                                                                             </div>
                                                                             <span class="airline-name">
                                                                                Qatar Airways
                                                                             </span>
                                                                             <div class="clear"></div>
                                                                             <span class="airline-number">
                                                                                650 - 750
                                                                             </span>
                                                                          </div>
                                                                          <div class="leginfo">
                                                                             <div class="leginfo_route">
                                                                                <span class="leginfo-time">
                                                                                   <div>
                                                                                      <span class="bpktext-text">21:35</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpktext-base">
                                                                                   <span class="leginfo-routepartial">AMM</span>
                                                                                </span>
                                                                             </div>
                                                                             <div class="leginfo-stopscontainer">
                                                                                <span class="bpktext-duration">25h 20</span>
                                                                                <ul class="leginfo-stopLine">
                                                                                   <li class="leginfo_stopdot"></li>
                                                                                   <svg version="1.1" id="Layer_1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve" class="leginfo_planeend"><path fill="#898294" d="M3.922,12h0.499c0.181,0,0.349-0.093,0.444-0.247L7.949,6.8l3.233-0.019C11.625,6.791,11.989,6.44,12,6 c-0.012-0.44-0.375-0.792-0.818-0.781L7.949,5.2L4.866,0.246C4.77,0.093,4.602,0,4.421,0L3.922,0c-0.367,0-0.62,0.367-0.489,0.71 L5.149,5.2l-2.853,0L1.632,3.87c-0.084-0.167-0.25-0.277-0.436-0.288L0,3.509L1.097,6L0,8.491l1.196-0.073 C1.382,8.407,1.548,8.297,1.632,8.13L2.296,6.8h2.853l-1.716,4.49C3.302,11.633,3.555,12,3.922,12"></path></svg>
                                                                                </ul>
                                                                                <div class="leginfo-stopslabelcontainer">
                                                                                   <span class="leginfo_stopslabelred">1 stop</span>
                                                                                   <div><span class="leginfo_stopstation">DXB</span></span></div>
                                                                                </div>
                                                                             </div>
                                                                             <div class="leginfo-routepartialarrive">
                                                                                <span class="leginfo-routepartialtime">
                                                                                   <div>
                                                                                      <span class="bpktext-text">14:55</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpk-text-base">
                                                                                   <span class="">ORD</span>
                                                                                </span>
                                                                             </div>
                                                                          </div>
                                                                       </div>
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                           </div>
                                                           <div class="border-line"></div>
                                                           <div class="ticket-stub">
                                                              <div class="ticketstub-horizontalstubcontainer">
                                                                 <div class="hotel-rates">
                                                                    <a href="">
                                                                       <span class="h-name">Booking.com</span>
                                                                       <span class="h-price">$ 29,510</span>
                                                                    </a>
                                                                    <a href="javascript:void(0)" data-activates="moreHotelRates1" class="dropdown-button more-h-rates"><i class="mdi mdi-chevron-down"></i></a>
                                                                    <ul id="moreHotelRates1" class="dropdown-content custom_dropdown">
                                                                       <li>
                                                                          <a href="">
                                                                             <span class="provider-name">Expedia.com</span>
                                                                             <span class="price">$ 257 </span>
                                                                          </a>
                                                                       </li>
                                                                       <li>
                                                                          <a href="">
                                                                             <span class="provider-name">Priceline.com</span>
                                                                             <span class="price">$ 245 </span>
                                                                          </a>
                                                                       </li>
                                                                    </ul>
                                                                 </div>
                                                                 <div class="">
                                                                    <div class="price-mainpriceContainer">
                                                                       <span class="">£772</span>
                                                                    </div>
                                                                 </div>
                                                                 <button type="button" class="ticketstub-ctaButton">Select</button>
                                                                 <div class="flight-amenities mt-10">
                                                                    <i class="mdi mdi-wifi"></i>
                                                                    <i class="mdi mdi-play-circle"></i>
                                                                    <i class="mdi mdi-usb"></i>
                                                                    <i class="mdi mdi-wifi"></i>
                                                                    <i class="mdi mdi-play-circle"></i>
                                                                    <i class="mdi mdi-usb"></i>
                                                                 </div>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div>
                                                  </div>
                                               </div>
                                               <div class="ticket-wrapper">
                                                  <div class="flightsticket-container">
                                                     <div class="flightsticket-link">
                                                        <div class="ticket-notches">
                                                           <div class="ticket-paper">
                                                              <div class="flightsticket-bodycontainer">
                                                                 <div class="ticketbody-container">
                                                                    <div class="ticketbody-legscontainer">
                                                                       <div class="legdetails-container ist">
                                                                          <div class="logoimage-container">
                                                                             <div class="leglogo-legimage">
                                                                                <div class="bpk-image">
                                                                                   <img class="" alt="Emirates" src="//www.skyscanner.net/images/airlines/small/QR.png">
                                                                                </div>
                                                                             </div>
                                                                             <span class="airline-name">
                                                                                Qatar Airways
                                                                             </span>
                                                                             <div class="clear"></div>
                                                                             <span class="airline-number">
                                                                                650 - 750
                                                                             </span>
                                                                          </div>
                                                                          <div class="leginfo">
                                                                             <div class="leginfo_route">
                                                                                <span class="leginfo-time">
                                                                                   <div>
                                                                                      <span class="bpktext-text">21:35</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpktext-base">
                                                                                   <span class="leginfo-routepartial">AMM</span>
                                                                                </span>
                                                                             </div>
                                                                             <div class="leginfo-stopscontainer">
                                                                                <span class="bpktext-duration">25h 20</span>
                                                                                <ul class="leginfo-stopLine">
                                                                                   <li class="leginfo_stopdot"></li>
                                                                                   <svg version="1.1" id="Layer_1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve" class="leginfo_planeend"><path fill="#898294" d="M3.922,12h0.499c0.181,0,0.349-0.093,0.444-0.247L7.949,6.8l3.233-0.019C11.625,6.791,11.989,6.44,12,6 c-0.012-0.44-0.375-0.792-0.818-0.781L7.949,5.2L4.866,0.246C4.77,0.093,4.602,0,4.421,0L3.922,0c-0.367,0-0.62,0.367-0.489,0.71 L5.149,5.2l-2.853,0L1.632,3.87c-0.084-0.167-0.25-0.277-0.436-0.288L0,3.509L1.097,6L0,8.491l1.196-0.073 C1.382,8.407,1.548,8.297,1.632,8.13L2.296,6.8h2.853l-1.716,4.49C3.302,11.633,3.555,12,3.922,12"></path></svg>
                                                                                </ul>
                                                                                <div class="leginfo-stopslabelcontainer">
                                                                                   <span class="leginfo_stopslabelred">1 stop</span>
                                                                                   <div><span class="leginfo_stopstation">DXB</span></span></div>
                                                                                </div>
                                                                             </div>
                                                                             <div class="leginfo-routepartialarrive">
                                                                                <span class="leginfo-routepartialtime">
                                                                                   <div>
                                                                                      <span class="bpktext-text">14:55</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpk-text-base">
                                                                                   <span class="">ORD</span>
                                                                                </span>
                                                                             </div>
                                                                          </div>
                                                                       </div>
                                                                       <div class="legdetails-container">
                                                                          <div class="logoimage-container">
                                                                             <div class="leglogo-legimage">
                                                                                <div class="bpk-image">
                                                                                   <img class="" alt="Emirates" src="//www.skyscanner.net/images/airlines/small/QR.png">
                                                                                </div>
                                                                             </div>
                                                                             <span class="airline-name">
                                                                                Qatar Airways
                                                                             </span>
                                                                             <div class="clear"></div>
                                                                             <span class="airline-number">
                                                                                650 - 750
                                                                             </span>
                                                                          </div>
                                                                          <div class="leginfo">
                                                                             <div class="leginfo_route">
                                                                                <span class="leginfo-time">
                                                                                   <div>
                                                                                      <span class="bpktext-text">21:35</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpktext-base">
                                                                                   <span class="leginfo-routepartial">AMM</span>
                                                                                </span>
                                                                             </div>
                                                                             <div class="leginfo-stopscontainer">
                                                                                <span class="bpktext-duration">25h 20</span>
                                                                                <ul class="leginfo-stopLine">
                                                                                   <li class="leginfo_stopdot"></li>
                                                                                   <svg version="1.1" id="Layer_1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 12 12" enable-background="new 0 0 12 12" xml:space="preserve" class="leginfo_planeend"><path fill="#898294" d="M3.922,12h0.499c0.181,0,0.349-0.093,0.444-0.247L7.949,6.8l3.233-0.019C11.625,6.791,11.989,6.44,12,6 c-0.012-0.44-0.375-0.792-0.818-0.781L7.949,5.2L4.866,0.246C4.77,0.093,4.602,0,4.421,0L3.922,0c-0.367,0-0.62,0.367-0.489,0.71 L5.149,5.2l-2.853,0L1.632,3.87c-0.084-0.167-0.25-0.277-0.436-0.288L0,3.509L1.097,6L0,8.491l1.196-0.073 C1.382,8.407,1.548,8.297,1.632,8.13L2.296,6.8h2.853l-1.716,4.49C3.302,11.633,3.555,12,3.922,12"></path></svg>
                                                                                </ul>
                                                                                <div class="leginfo-stopslabelcontainer">
                                                                                   <span class="leginfo_stopslabelred">1 stop</span>
                                                                                   <div><span class="leginfo_stopstation">DXB</span></span></div>
                                                                                </div>
                                                                             </div>
                                                                             <div class="leginfo-routepartialarrive">
                                                                                <span class="leginfo-routepartialtime">
                                                                                   <div>
                                                                                      <span class="bpktext-text">14:55</span>
                                                                                   </div>
                                                                                </span>
                                                                                <span class="bpk-text-base">
                                                                                   <span class="">ORD</span>
                                                                                </span>
                                                                             </div>
                                                                          </div>
                                                                       </div>
                                                                    </div>
                                                                 </div>
                                                              </div>
                                                           </div>
                                                           <div class="border-line"></div>
                                                           <div class="ticket-stub">
                                                              <div class="ticketstub-horizontalstubcontainer">
                                                                 <div class="hotel-rates">
                                                                    <a href="">
                                                                       <span class="h-name">Booking.com</span>
                                                                       <span class="h-price">$ 29,510</span>
                                                                    </a>
                                                                    <a href="javascript:void(0)" data-activates="moreHotelRates2" class="dropdown-button more-h-rates"><i class="mdi mdi-chevron-down"></i></a>
                                                                    <ul id="moreHotelRates2" class="dropdown-content custom_dropdown">
                                                                       <li>
                                                                          <a href="">
                                                                             <span class="provider-name">Expedia.com</span>
                                                                             <span class="price">$ 257 </span>
                                                                          </a>
                                                                       </li>
                                                                       <li>
                                                                          <a href="">
                                                                             <span class="provider-name">Priceline.com</span>
                                                                             <span class="price">$ 245 </span>
                                                                          </a>
                                                                       </li>
                                                                    </ul>
                                                                 </div>
                                                                 <div class="">
                                                                    <div class="price-mainpriceContainer">
                                                                       <span class="">£772</span>
                                                                    </div>
                                                                 </div>
                                                                 <button type="button" class="ticketstub-ctaButton">Select</button>
                                                                 <div class="flight-amenities mt-10">
                                                                    <i class="mdi mdi-wifi"></i>
                                                                    <i class="mdi mdi-play-circle"></i>
                                                                    <i class="mdi mdi-usb"></i>
                                                                    <i class="mdi mdi-wifi"></i>
                                                                    <i class="mdi mdi-play-circle"></i>
                                                                    <i class="mdi mdi-usb"></i>
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
                                      <div class="tab-pane fade main-pane dis-none" id="cheapestTab">
                                         <div class="row mx-0">    
                                            2
                                         </div>
                                      </div>
                                      <div class="tab-pane fade main-pane general-yours dis-none" id="fastestTab">
                                         <div class="row mx-0">
                                            3
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

<div id="datepickerDropdown" class="modal tbpost_modal modal-datepicker modalxii_level1 nice-scroll">
    <div class="content_header">
        <button class="close_span waves-effect">
        <i class="mdi mdi-close mdi-20px material_close resetdatepicker"></i>
        </button>
        <p class="selected_photo_text">Select Date</p>
        <a href="javascript:void(0)" class="done_btn action_btn closedatepicker">Done</a>
    </div> 
    <div class="modal-content">
      <div id="datepickerBlock"></div>
    </div>
</div>   


<input type="hidden" name="pagename" id="pagename" value="feed" />
<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>
<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/post.js"></script>
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
    
   $('.dept-return').daterangepicker();
</script>
<?php $this->endBody() ?> 