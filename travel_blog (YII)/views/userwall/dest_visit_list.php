<?php
use frontend\assets\AppAsset;
use frontend\models\Personalinfo;
$baseUrl = AppAsset::register($this)->baseUrl;
$visit_cities = '';
$visited_cities = '';
if(!empty($destpast)) { ?>
<ul class="dest-list nice-scroll">
	<?php
	//array_push($destpast,$place);
	if(isset($visited_str) && !empty($visited_str)) {
		$destpast = array_merge($destpast,$visited_str);
	}

	foreach($destpast as $destination) {
		if(!isset($destination['_id'])) {
			$destinationid = rand(1000,9999);
			$destinationname = str_replace("'","",$destination);
		} else {
			$destinationid = $destination['_id'];
			$destinationname = str_replace("'","",$destination['place']);
		}

		$count = substr_count($destinationname,",");
		if($count >= 1) {
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = $placet[1];
			if(isset($placet[2]) && !empty($placet[2]))
			{
				$placesecond .=', '.$placet[2];
			}
		} else {
			$placet = (explode(",",$destinationname));
			$placefirst = $placet[0];
			$placesecond = '&nbsp;';
		}
		$destimage = $this->context->getplaceimage($destinationname);
		$getdestlatlng = $this->context->getlatlng($destinationname);
		$visited_cities .=  "['".$destinationname."',".$getdestlatlng.",'".$destimage."'],";
		$time = time();
		$rand = rand(999, 999999);
		$getkey = $time.'_'.$rand;
		?>
		<li id="XHIL<?=$getkey?>">
			<div class="destili">
				<div class="imgholder himg-box">
					<img src="<?=$destimage?>" class="himg"/>
				</div>
				<div class="descholder">
					<h6><a href="?r=places&p=<?=$destinationname?>" target="_blank"><?=$placefirst?></a><span><?=$placesecond?></span></h6>
					<span class="beenthere">Been there</span>
					<?php if(isset($destination['_id'])) { ?>
					<a href="javascript:void(0)" onclick="delDest('<?=$destinationid?>', '#XHIL<?=$getkey?>')" class="cross">
						<i class="mdi mdi-close	"></i>
					</a>
					<?php } ?>
				</div>
			</div>
		</li>
		<?php 
	} 
	$visited_cities = substr($visited_cities,0,-1); ?>
</ul>
<?php } ?>
<div class="clear"></div>
<div class="add-dest">
	<div class="sliding-middle-custom anim-area underlined fullwidth locinput">
	<input data-query="M" id="past" class="validate getplacelocation" onfocus="filderMapLocationModal(this)" autocomplete="off" placeholder="Add place you have been to" type="text">
	</div>

	<a href="javascript:void(0)" class="btn-custom waves-effect" onclick="addDest('past')">Add</a>
</div>
<?php exit();?>