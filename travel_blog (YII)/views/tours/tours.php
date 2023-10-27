<?php  
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Tours';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
 
<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper full-wrapper noopened-search JIS3829 tourlist-page"> 
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
		<div class="main-content with-lmenu transheader-page tours-page main-page places-page pb-0">
			<div class="combined-column wide-open">
				<div class="content-box">
					<div class="banner-section">
						<h4>Tours, sighting, activities and thing to do</h4>
					</div>
					<div class="row mx-0 filter-map"> 
		                <a href="javascript:void(0)" class="col s12 toggle-map" onclick="toggleDiv('div1', 'resId')"><i class="mdi mdi-map-marker"></i>Map</a>
		             </div>
		             <div class="row m-0 map-mobile toggle-maphotels" id="div1">
		                <div class="map-holder">
		                   <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3110.3465133386144!2d-9.167423685010494!3d38.77868997958898!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd193295d5b45545%3A0x3f9e7b6a5f00e12c!2sPerta!5e0!3m2!1sen!2sin!4v1481089901870" width="600" height="450" frameborder="0" allowfullscreen></iframe>
		                </div>
		            </div>
					<div class="places-content places-all">
               			<div class="container cshfsiput">
               				<div class="places-column cshfsiput m-top">
               					<div id="places-lodge" class="placeslodge-content places-discussion-main top_tabs">
                        			<div class="content-box">
<div class="placesection bluesection">
	<div class="cbox-desc hotels-page np">
		<div class="tcontent-holder moreinfo-outer">
			<div class="top-stuff top-graybg" id="all-todo">
				<div class="more-actions">
					<div class="sorting left">
						<label>Sort by</label>
						<div class="select-holder">
							<select class="select2" tabindex="-1" >
								<option>Shows</option>
								<option>Siting</option>
								<option>Attractions</option>
							</select>
						</div>
					</div>
				</div>
				<h6>300 things to do in <span>Japan</span></h6>
			</div>
	    	<div class="places-content-holder">
	       		<div class="list-holder">
	          		<div class="hotel-list" id="tours-list"><ul></ul>
	             	</div>
	            </div>
	        </div>
	    </div>
	</div>
</div>


                        			</div>
                        		</div>
               				</div>
               				<?php include('../views/layouts/gen_wall_col.php'); ?>
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