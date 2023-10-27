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
<div class="page-wrapper full-wrapper noopened-search JIS3829 hotellist-page hotellist-page flight-page find-flight">
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
                       <div class="header-container">
                          <h3 class="header-text">Find the best flight</h3>
                       </div>
                       <div class="form-container">
                          <div class="row">
                             <div class="trip-type inline-radio">
                                <input name="tripType" checked="" type="radio" class="with-gap" id="round" value="round">
                                <label for="round">Round-trip</label>
                                <input name="tripType" type="radio" class="with-gap" id="oneway" value="oneway">
                                <label for="oneway">One-way</label>    
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
                          <div class="clear"></div>
                          <div class="row">
                             <div class="col s6 nonstop">
                                <p class="mt-0">
                                   <input type="checkbox" id="nonStop" />
                                   <label for="nonStop">
                                      <span class="stars-holder">Prefer nonstop</span>
                                   </label>
                                </p>
                             </div>
                             <div class="col s6">
                                <button class="btn-findflight">Find flights</button>
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