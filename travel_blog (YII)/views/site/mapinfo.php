<?php
use backend\models\Googlekey;

$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<ul>
<?php if($ql == 'OK' && ($default == 'Hotels' || $default == 'Restaurants')){
	$hotel = $rs['results'];
	for($i=0;$i<20;$i++){ 
		if(isset($hotel[$i]['place_id']) && !empty($hotel[$i]['place_id'])){
		$placeid = $hotel[$i]['place_id'];
		$pieces = $hotel[$i]['types'];
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
		
		$urlhotel="https://maps.googleapis.com/maps/api/place/details/json?placeid=$placeid&key=$GApiKeyP";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$urlhotel);
		$result=curl_exec($ch);
		curl_close($ch);
		$rss = json_decode($result, true);
		$qll = $rss['status'];
		if(isset($rss['result']) && !empty($rss['result'])) {
			$hotell = $rss['result'];
			if(isset($hotell['vicinity']) && !empty($hotell['vicinity'])) {
				$adr = $hotell['vicinity'];
			} else {
				$adr = 'Not Found';
			}
			if(isset($hotell['website']) && !empty($hotell['website'])) {
				$website = $hotell['website'];
			} else {
				$website = 'Not Found';
			}
			if(isset($hotell['international_phone_number']) && !empty($hotell['international_phone_number'])) {
				$ipn = $hotell['international_phone_number'];
			} else {
				$ipn = 'Not Found';
			}
		} else {
			$adr = $website = $ipn = 'Not Found';
		}
		
		if(isset($pieces) && !empty($pieces)) { ?>
		<span class="dis-none" id="map_<?=$placeid?>">
			Popular with:
			<?php $healthy = array("restaurant", "hotel", "lodging");
			$pieces = str_replace($healthy, '', $pieces);
			foreach($pieces as $element) {
				if(isset($element) && !empty($element)) {
					echo "<span>".$element."</span> ";
				}
			} ?>
		</span>
		<?php if(isset($hotel[$i]['rating']) && !empty($hotel[$i]['rating'])){ ?>
		<span class="dis-none" id="map_rate_<?=$placeid?>">
			<?php for($j=0;$j<5;$j++){ ?>
				<i class="mdi mdi-star <?php if($j < $hotel[$i]['rating']){ ?>active<?php } ?>"></i>
			<?php } ?>
			<label>34 Reviews</label>
		</span>
		<?php } ?>
	<?php } ?>
	<li>
		<div class="hotel-li">
			<a href="javascript:void(0)" class="summery-info" onclick="openPlacesMoreInfo(this,'<?=$img?>','<?=$hotel[$i]['name']?>','<?=str_replace("'","\'",$adr)?>','<?=$website?>','<?=$ipn?>','<?=$placeid?>')">
				<div class="imgholder <?=$imgclass?>-box"><img src="<?=$img?>" class="<?=$imgclass?>"/></div>
				<div class="descholder">
					<h4><?=$hotel[$i]['name']?></h4>
					<div class="clear"></div>
					<div class="reviews-link">
						<?php if(isset($hotel[$i]['rating']) && !empty($hotel[$i]['rating'])){ ?>
						<span class="checks-holder">
							<?php for($j=0;$j<5;$j++){ ?>
								<i class="mdi mdi-star <?php if($j < $hotel[$i]['rating']){ ?>active<?php } ?>"></i>
							<?php } ?>
							<label>34 Reviews</label>
						</span>
						<?php } ?>
					</div>													
				</div>
			</a>											
		</div>
	</li>
	<?php } } } else { ?>
<div class="no-listcontent">
	No <?=$default?> found.
</div>
<?php } ?>
</ul>
<?php exit;?>