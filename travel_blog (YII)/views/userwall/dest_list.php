<?php
use frontend\assets\AppAsset;
use frontend\models\PostForm;
use frontend\models\PhotoForm;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
	<div class="gloader"><img src="<?=$baseUrl?>/images/loading.gif" class="g-loading"/></div>
	<div id="destigrid">
		<?php array_push($dest,$place);
			foreach($dest as $destination) {
			if(!isset($destination['_id']))
			{
				$destinationid = rand(1000,9999);
				$destinationname = str_replace("'","",$place);
			}
			else
			{
				$destinationid = $destination['_id'];
				$destinationname = str_replace("'","",$destination['place']);
			}
			$count = substr_count($destinationname,",");
			if($count >= 1)
			{
				$placet = (explode(",",$destinationname));
				$placefirst = $placet[0];
				$placesecond = $placet[1];
				if(isset($placet[2]) && !empty($placet[2]))
				{
					$placesecond .=', '.$placet[2];
				}
			}
			else
			{
				$placet = (explode(",",$destinationname));
				$placefirst = $placet[0];
				$placesecond = '&nbsp;';
			}
			$getpplacereviews = PostForm::getPlaceReviewsCount($destinationname,'reviews');
			$getpplacereasks = PostForm::getPlaceReviewsCount($destinationname,'ask');
			$getpplacerephotos = PhotoForm::getTotalPics($destinationname);
			$getpplacetips = PostForm::getPlaceReviewsCount($destinationname,'tip');
			$destimage = $this->context->getplaceimage($destinationname);
		?>
		<div class="wcard">
			<div class="desc-holder">
				<div class="sumbox">
					Reviews<span><?=$getpplacereviews?></span>
				</div>
				<div class="sumbox">
					Ask<span><?=$getpplacereasks?></span>
				</div>
				<div class="sumbox">
					Photos<span><?=$getpplacerephotos?></span>
				</div>
				<div class="sumbox">
					Tips<span><?=$getpplacetips?></span>
				</div>
			</div>
			<div class="img-holder">
				<a href="?r=places&p=<?=$destinationname?>" target="_blank">
					<img alt="" src="<?=$destimage?>">
				</a>
				<div class="ititle">
					<div class="ititle-icon"><i class="mdi-map"></i></div>
					<div class="ititle-text">
						<?=$placefirst?>
						<span>
							<?=$placesecond?>
						</span>
					</div>
					<a class="ititle-extra" href="javascript:void(0)"></a>
				</div>
			</div>
			
			<div class="img-holder dis-none" data-id="strcube">
				<a href="?r=places&p=" target="_blank">
					<img alt="" src="<?=$destimage?>">
				</a>
				<div class="ititle">
					<div class="ititle-icon"><i class="mdi-map"></i></div>
					<div class="ititle-text">
						
						<span>
							
						</span>
					</div>
					<a class="ititle-extra" href="javascript:void(0)"><i class="zmdi zmdi-pin"></i></a>
				</div>
			</div>
			
		</div>
		<?php } ?>
	</div>
<?php exit();?>