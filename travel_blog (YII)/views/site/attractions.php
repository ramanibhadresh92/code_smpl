<?php 
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$isMore = true;  
if(!empty($attractions)) { 
$isMore = false;
if(count($attractions)>3) { 
	$isMore = true;
	array_pop($attractions);
}
?>
<div class="tcontent-holder topattractions-section">
	<div class="cbox-title nborder title-1">
		Top Attractions		
		<div class="top-stuff right">	
			<a href="javascript:void(0)" class="viewall-link" onclick="replaceWithSlider('top-attractions')"><i class="glyphicon glyphicon-chevron-right"></i></a>
		</div>		
	</div>
	<div class="cbox-desc">
		<div class="places-content-holder">														
			<div class="list-holder topplaces-list">
				<div class="row">
				<?php 
					foreach ($attractions as $key => $attraction) {
						$name = ucfirst(strtolower($attraction['name']));
						$city = ucfirst(strtolower($attraction['city']));
						$peak_season = $attraction['peak_season'];
						$profile = $attraction['profile'];
						$visitors = $attraction['visitors'];
						$img_box_cls = '';
						$img_cls = '';
						if(file_exists($baseUrl."/images/attractions/.$profile")) {
							$val = getimagesize($baseUrl.'/images/attractions/'.$profile);
							if($val[0] > $val[1]) {
                            	$img_box_cls = 'vimg-box';
								$img_cls = 'vimg';
                            } else if($val[1] > $val[0]) {
                            	$img_box_cls = 'himg-box';
								$img_cls = 'himg';
                            } else {
                            	$img_box_cls = 'vimg-box';
								$img_cls = 'vimg';
                            }
						}
					?>
					<div class="col-sm-4 col-xs-12 fpb-holder">
						<div class="f-placebox">
							<a href="javascript:void(0)" class="load-btn" onclick="getTourlistparticular('<?=$city?>', '<?=$name?>');"><div class="imgholder <?=$img_box_cls?>"><img src="<?=$baseUrl?>/images/attractions/<?=$profile?>" class="<?=$img_cls?>"/></div></a>
							<div class="descholder">
								<a href="javascript:void(0)" class="load-btn" onclick="getTourlistparticular('<?=$city?>', '<?=$name?>');"><h5><?=$name?><span><?=$city?></span></h5></a>
								<div class="tags">
									<p><strong>Peak Time :</strong> <?=$peak_season?></p>
									<p><strong>Visitors :</strong> <?=$visitors?></p>
								</div>					
								<a href="javascript:void(0)" onclick="getTourlistparticular('<?=$city?>', '<?=$name?>');" class="right btn btn-primary btn-sm white-text">Visit</a>
							</div>
							
						</div>
					</div>
					<?php }
					if($isMore == true) { ?>
					<div class="col-sm-12 btn-holder text-center">
						<a href="javascript:void(0)" class="load-btn" onclick="attractionsPaginationClick();">More Attractions <i class="mdi mdi-chevron-down"></i></a>
					</div>
					<?php } ?> 
				</div>
			</div>
		</div>
	</div>
</div>
<?php }
exit;