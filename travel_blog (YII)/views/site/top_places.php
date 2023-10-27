<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
$isMore = true;  
if(!empty($topplaces)) { 
$isMore = false;
if(count($topplaces)>3) { 
	$isMore = true; 
	array_pop($topplaces);
}
?>
<div class="tcontent-holder topplaces-section">
	<div class="cbox-title nborder title-1">
		Discover Top places		
		<div class="top-stuff right">	
			<a href="javascript:void(0)" class="viewall-link" onclick="topplacesPaginationClick();"><i class="glyphicon glyphicon-chevron-right"></i></a>
		</div>
	</div>
	<div class="cbox-desc">
		<div class="places-content-holder">														
			<div class="list-holder topplaces-list">
				<div class="row">
				<?php 
					foreach ($topplaces as $key => $topplace) {
						$name = ucfirst(strtolower($topplace['name']));
						$country = ucfirst(strtolower($topplace['country']));
						$category = $topplace['category'];
						$category = explode(",", $category);
						$profile = $topplace['profile'];
					?>
					<div class="col-sm-4 col-xs-12 fpb-holder">
						<div class="f-placebox">
							<a href="?r=places&p=<?=$name?> <?=$country?>">
							<div class="imgholder himg-box"><img src="<?=$baseUrl?>/images/topplaces/<?=$profile?>" class="himg"/></div></a>
							<div class="descholder">
								<a href="?r=places&p=<?=$name?> <?=$country?>"><h5><?=$name?><span><?=$country?></span></h5></a>	
								
								<div class="tags">
									<?php foreach ($category as $key => $value) { ?>
										<span><?=$value?></span>	
									<?php } ?>
								</div>					
								<a href="?r=places&p=<?=$name?> <?=$country?>" class="right btn btn-primary btn-sm white-text">Discover</a>
							</div>
							
						</div>
					</div>
					<?php 
					}

					if($isMore == true) { ?>
					<div class="col-sm-12 btn-holder text-center">
						<a href="javascript:void(0)" class="load-btn" onclick="topplacesPaginationClick();">More places <i class="mdi mdi-chevron-down"></i></a>
					</div>
					<?php 
					} ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php }
exit;