<?php   
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$this->title = 'Trip';
$getstartpoints = '';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper  trip-wrapper full-wrapper hidemenu-wrapper transheadereffect show-sidebar">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
	<div class="floating-icon">
		<div class="scrollup-btnbox anim-side btnbox scrollup-float">
			<div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>			
		</div>	
	</div>
	<div class="clear"></div>
	<?php include('../views/layouts/leftmenu.php'); ?>
	<div class="fixed-layout ipad-mfix">
		<div class="main-content trip-page p-0">
			<div class="trip-mapview">
				<div class="tripmenu">
					<ul>
						<li class="active"><a href="javascript:void(0)" onclick="showLayer(this,'alltrip')">Trips</a></li>
						<li><a href="javascript:void(0)" onclick="printTrip()">Print</a></li>
						<li><a href="javascript:void(0)" onclick="emailTrip()">Email</a></li>
					</ul>
				</div>
				<div class="desc-box">
					<div class="side-section">
						<div id="trip-list" class="section-layer front"></div>
						<div id="trip-view" class="section-layer"></div>
						<div id="trip-edit" class="section-layer"></div>
						<div id="trip-new" class="section-layer"></div>
						<div id="trip-bookmark" class="section-layer"></div>
						<div id="notes" class="section-sublayer"></div>
						<div id="bookmarks" class="section-sublayer"></div>
					</div>
				</div>
				<div class="map-box">
					<div id="trip-map" class="map-container"></div>
					<div class="map-filter">
						<div class="map-icons">
							<a href="javascript:void(0)" title="Lodge" onclick="tripAcco('hotels')" ><span class="icon lodge-icon"><img src="<?=$baseUrl?>/images/lodge-icon.png"></span></a>
							<a href="javascript:void(0)" onclick="tripAcco('restaurants')" title="Dine"><span class="icon dine-icon"><img src="<?=$baseUrl?>/images/dine-icon.png"></span></a>
							<a href="javascript:void(0)" onclick="tripAcco('travel_agency')" title="Todo"><span class="icon todo-icon"><img src="<?=$baseUrl?>/images/todo-icon.png"></span></a>
						</div>
						<a href="javascript:void(0)" class="backroute" onclick="BackTripRouteMap(this,'mobile')">Back to trip route</a>
						<div class="map-drop" id="mobiledrops"></div>
					</div>
					<div class="mobile-comp">
						<a href="javascript:void(0)" onclick="closeDetailTripMap(this)">Back to detail view</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php include('../views/layouts/footer.php'); ?>
	<input type="hidden" id="tripid" value="empty"/>
	<input type="hidden" id="triptype" value="all"/>
	<input type="hidden" id="pagename" value="trip"/>
	<input type="hidden" id="tripplace" value="empty"/>
	<div id="tripprint"/></div>
</div>



<div id="modal1" class="modal">
    <div class="modal-content">
      <h4>Modal Header</h4>
      <p>A bunch of text</p>
    </div>
    <div class="modal-footer">
      <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Agree</a>
    </div>
</div>

<div id="sharepostmodal" class="modal sharepost_modal post-popup main_modal custom_modal">
</div>

<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">	 
	<?php include('../views/layouts/mapmodal.php'); ?>
</div>


<script>
var baseUrl = "<?php echo (string)$baseUrl; ?>";
var place = "<?php echo (string)$place; ?>";
var tripid = $("#tripid").val();
var triptype = $("#triptype").val();
var $start = '';
var $end = '';
var $getstartpoints =  "<?php echo (string)$getstartpoints; ?>";
var data1='';
</script> 
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/trip.js"></script>
