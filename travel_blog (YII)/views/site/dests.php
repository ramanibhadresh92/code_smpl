<?php
use frontend\models\PostForm;
use frontend\models\Tours;
$geocode = file_get_contents('http://gd.geobytes.com/GetNearbyCities?radius=1000&Latitude='.$lat.'&Longitude='.$lng.'&limit=4');
$cities = json_decode($geocode, true);
?>
<div class="tcontent-holder">
	<div class="cbox-title nborder title-1">
		Popular nearby cities
	</div>
	<div class="cbox-desc p-t-0">
		<div class="places-content-holder">
			<div class="list-holder">
				<div class="row">
				<?php if(!empty($cities)) { 
				array_shift($cities); 
				$i= 1; 
				foreach($cities as $city) {
					if($i <= 3) {
						$tempplacetitle = $cityname = $city[1].', '.$city[3];
						$link = "?r=places&p=".$cityname;
						$tempplacetitle = str_replace(' ','+',$tempplacetitle);
						$img = $this->context->getplaceimage($tempplacetitle);
						$getpplacereviewscount = PostForm::getPlaceReviewsCount($cityname,'reviews');
						$getpplacetipscount = PostForm::getPlaceReviewsCount($cityname,'tip');
						$pcount = substr_count($place,",");
						if($pcount > 0) {
							$placet = (explode(", ",$place));
							$placecountry = $placet[1];
							$type = 'City';
						} else {
							$placecountry = '';
							$type = 'Country';
						}
						$thingscount = Tours::getTodos($placefirst,$placecountry,$type,'PriceUSD');
					?>
					<div class="col m4 s6 fpb-holder">
						<div class="f-placebox destibox">
							<a href="<?=$link?>" target="_new">
								<div class="imgholder himg-box">
									<img src="<?=$img?>" class="himg"/>
									<div class="overlay"></div>
								</div>
								<div class="descholder">
									<h5><?=$cityname;?></h5>
									<ul>
										<li onclick="getallitem('hotels', '<?=$tempplacetitle?>', '<?=$cityname?>', 'empty', 'lodge');"><a aria-expanded="false" data-toggle="tab" href="#places-lodge"><i class="mdi mdi-menu-right"></i>Hotels</a></li>
										<li onclick="getallitem('rest', '<?=$tempplacetitle?>', '<?=$cityname?>', 'empty', 'dine');">
										<a aria-expanded="false" data-toggle="tab" href="#places-dine"><i class="mdi mdi-menu-right"></i>Restaurants</a></li>
										<li><a href="javascript:void(0)" onclick="nearbycitieslocal('<?=$city[1]?>', '<?=$tempplacetitle?>')"><i class="mdi mdi-menu-right"></i></i>Locals</a></li>
									</ul>
								</div>
							</a>
						</div>
					</div>
					<?php } $i++;}
					} else { ?>
					<<div class="col m4 s6 fpb-holder">
						<?php $this->context->getnolistfound('nocityfound');?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>