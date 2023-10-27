<?php

use frontend\assets\AppAsset;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="tcontent-holder">
	<div class="cbox-title nborder" id="restaurant-title">
		<img src="<?=$baseUrl?>/images/dineicon-sm.png"/>
		Popular Restaurants in <?=$placefirst?>
		<div class="top-stuff right">										
			<a href="javascript:void(0)" class="viewall-link allrest" onclick="openDirectTab('places-dine')">View All</a>
			<div class="more-actions">
				<ul class="tabs nav-custom-tabs text-right">
					<li class="tab"><a href="javascript:void(0)" onclick="openMapSection(this,'dine')"><i class="zmdi zmdi-pin"></i> Map</a></li>
					<li class="tab"><a href="javascript:void(0)" onclick="openListSection(this)"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i> List</a></li>
				</ul>
			</div>														
		</div>
	</div>
	<div class="cbox-desc"> 
		<div class="places-content-holder">
			<div class="list-holder">
				<div class="row">
					<?php if($ql == 'OK'){
						$hotel = $rs['results'];
						for($i=0;$i<$count;$i++){
							if(isset($hotel[$i]['place_id']) && !empty($hotel[$i]['place_id'])) {
							if(empty($hotel[$i]['photos'][0]['photo_reference'])) {
								$img = '';
								$imgclass = 'himg';
							} else {
								$ref = $hotel[$i]['photos'][0]['photo_reference'];
								$width = $hotel[$i]['photos'][0]['width'];
								$height = $hotel[$i]['photos'][0]['height'];
								$img = "https://maps.googleapis.com/maps/api/place/photo?maxheight=200&photoreference=$ref&key=$GApiKeyP";
								if($width > $height){$imgclass = 'himg';}
								else if($height > $width){$imgclass = 'vimg';}
								else{$imgclass = 'himg';}
							}
					?>
					<div class="col-sm-4 col-xs-12 pb-holder <?php if($i == 2){ ?>third-col<?php } ?>">
						<div class="placebox">
							<a href="javascript:void(0)">
								<div class="imgholder <?=$imgclass?>-box"><img src="<?=$img?>" class="<?=$imgclass?>"/><div class="overlay"></div></div>
								<div class="descholder">
									<h5><?=$hotel[$i]['name']?></h5>
									<?php if(!empty($hotel[$i]['rating'])){ ?>
									<span class="ratings">
										<?php for($j=0;$j<5;$j++){ ?>
											<i class="mdi mdi-star <?php if($j < $hotel[$i]['rating']){ ?>active<?php } ?>"></i>
										<?php } ?>
									</span>
									<?php } ?>
									<div class="tags">
										<?php 
										$pieces = $hotel[$i]['types'];
										$healthy = array("point_of_interest", "establishment", "restaurant", "hotel", "lodging");
										$pieces = str_replace($healthy, '', $pieces);
										foreach($pieces as $element) {
											if(isset($element) && !empty($element)) {
												echo "<span>".$element."</span> ";
											}
										}
										?>
									</div>
								</div>
							</a>
						</div>
					</div>
					<?php } } } else { ?>
					<div class="col-lg-12">
						<?php $this->context->getnolistfound('norestaurantfound');?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit; ?>