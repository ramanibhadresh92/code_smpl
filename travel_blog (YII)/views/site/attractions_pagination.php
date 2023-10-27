<?php 
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
if(!empty($attractions)) { ?>
<div class='carousel carousel-albums slide' id='topattr_carousel'>
	<div class='carousel-inner'>
	<?php  
	$i=1; 
	foreach ($attractions as $key => $attraction) {
		$name = ucfirst(strtolower($attraction['name']));
		$city = ucfirst(strtolower($attraction['city']));
		$peak_season = $attraction['peak_season'];
		$profile = $attraction['profile'];
		$visitors = $attraction['visitors'];
		$img_box_cls = '';
		$img_cls = '';
		if(file_exists($baseUrl."/images/attractions/$profile")) {
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
		if($i == 1) { ?>
		<div class='item active'>
		<?php } else { ?>
		<div class='item'>
		<?php } ?>
			<div class="col-sm-4 col-xs-12 fpb-holder">
				<div class="f-placebox">
					<a href="javascript:void(0)" class="load-btn" onclick="getTourlistparticular('<?=$city?>', '<?=$name?>');">
					<div class="imgholder <?=$img_box_cls?>">
						<img src="<?=$baseUrl?>/images/attractions/<?=$profile?>" class="<?=$img_cls?>"/>
					</div>
					</a>
					<div class="descholder">
						<a href="javascript:void(0)" class="load-btn" onclick="getTourlistparticular('<?=$city?>', '<?=$name?>');"><h5><?=$name?><span><?=$city?></span></h5></a>						
						<div class="tags">
							<p><strong>Peak Time :</strong> <?=$peak_season?></p>
							<p><strong>Visitors :</strong> <?=$visitors?></p>
						</div>					
						<a href="javascript:void(0)" onclick="getTourlistparticular('<?=$city?>', '<?=$name?>');" class="right btn btn-primary btn-sm">Visit</a>
					</div>
				</div>
			</div>
		</div>
	<?php
	$i++; 
	}
	?>
	</div>
	<a class='left carousel-control' href='#topattr_carousel' data-slide='prev'><i class='glyphicon glyphicon-chevron-left'></i></a>
	<a class='right carousel-control' href='#topattr_carousel' data-slide='next'><i class='glyphicon glyphicon-chevron-right'></i></a>
</div>
<?php } 
exit;