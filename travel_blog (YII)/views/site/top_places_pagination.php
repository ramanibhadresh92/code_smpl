<?php 
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
if(!empty($topplaces)) { ?>
<div class='carousel carousel-albums slide' id='topplace_carousel'>
	<div class='carousel-inner'>
	<?php  
	$i=1; 
	foreach ($topplaces as $key => $topplace) {
		$name = ucfirst(strtolower($topplace['name']));
		$country = ucfirst(strtolower($topplace['country']));
		$category = $topplace['category'];
		$category = explode(",", $category);
		$profile = $topplace['profile'];
		if($i == 1) { ?>
		<div class='item active'>
		<?php } else { ?>
		<div class='item'>
		<?php } ?>
			<div class='col-sm-4 col-xs-12 fpb-holder'>
				<div class='f-placebox'>
					<a href="?r=places&p=<?=$name?> <?=$country?>">
					<div class="imgholder himg-box"><img src="<?=$baseUrl?>/images/topplaces/<?=$profile?>" class="himg"/></div>
					</a>
					<div class='descholder'>
					<a href="?r=places&p=<?=$name?> <?=$country?>"><h5><?=$name?><span><?=$country?></span></h5></a>
					<div class="tags">
					<?php foreach ($category as $key => $value) { ?>
						<span><?=$value?></span>	
					<?php } ?>
					</div>					
					<a href="?r=places&p=<?=$name?> <?=$country?>" class="right btn btn-primary btn-sm">Discover</a>
					</div>
				</div>
			</div>
		</div>
	<?php
	$i++; 
	}
	?>
	</div>
	<a class='left carousel-control' href='#topplace_carousel' data-slide='prev'><i class='glyphicon glyphicon-chevron-left'></i></a>
	<a class='right carousel-control' href='#topplace_carousel' data-slide='next'><i class='glyphicon glyphicon-chevron-right'></i></a>
</div>
<?php } 
exit;