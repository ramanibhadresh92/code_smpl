<?php  
 use yii\helpers\Url;
use frontend\assets\AppAsset;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Restaurants';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
$place = Yii::$app->params['place'];
$placetitle = Yii::$app->params['placetitle'];
$placefirst = Yii::$app->params['placefirst'];
$title = 'restaurants';
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
	                  <a href="restaurants.php" class="btn-closemap"><i class="zmdi zmdi-close mdi-20px"></i> Close Map</a>
	               </div>
	               <div class="sidetitle">
	                  <a href="javascript:void(0)" class="expand-link" onclick="mng_filter_sort(this, 'sort')"><i class="mdi mdi-sort mdi-16px"></i> Sort</a>
	                  <a href="javascript:void(0)" class="expand-link" onclick="mng_filter_sort(this, 'filter')"><i class="mdi mdi-tune mdi-16px"></i>Filter</a>
	                  <a href="restaurants.php" class="expand-link list"><i class="mdi mdi-format-list-bulleted mdi-16px"></i>List</a>
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
							<div class="hotel-list" id="mapinfo"></div>
						</div>
					</div>                          
					<div class="moreinfo-box"> 
						<a href="javascript:void(0)" onclick="closePlacesMoreInfo(this)" class="backarrow"><i class="mdi mdi-arrow-left-bold-circle"></i></a>
						<div class="infoholder nice-scroll">
							<div class="imgholder"><img id="placeid_image" src=""/></div>
							<div class="descholder">
								<h4 id="placeid_name"></h4>
								<div class="clear"></div>
								<div class="reviews-link">
									<span class="checks-holder" id="placeid_rate"></span>
								</div>
								<span class="distance-info">Middle Eastem &amp; African, Mediterranean</span>
								<div class="clear"></div>
								<div class="more-holder">
									<ul class="infoul">                                                
										<li>
											<i class="zmdi zmdi-pin"></i>
											<span id="placeid_adr"></span>
										</li>
										<li>
											<i class="mdi mdi-phone"></i>
											<span id="placeid_phone"></span>
										</li>
										<li>
											<i class="mdi mdi-earth"></i>
											<span id="placeid_site"></span>
										</li>
									</ul>       
									<div class="tagging" onclick="explandTags(this)" id="placeid_tag">
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
<input type="hidden" id="mapinfodine" value="0"/>
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
<script type="text/javascript">
	$(document).ready(function() {
		$.ajax({
			type: 'POST',  
			url: '?r=site/getmapinfo',
			data: 'placetitle=Japan&type=dine',
			success: function (data) {
				console.log(data);
				$("#mapinfo").html(data);
			}
		});
	}); 
</script>
<?php $this->endBody() ?> 