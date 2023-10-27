<?php 
use frontend\models\PostForm;
?>
<div class="trip-listing">
	<h5>Checkout Trip Experiences</h5>
	<?php if(empty($continents)){
		echo "No trip experience found";
	}else{
	?>
	<div class="trip-accordion">
		<?php foreach ($continents as $continent) {
			$countries = PostForm::find()->select(['country'])->where(['continent'=>$continent,'is_deleted'=>"0"])->distinct('country');
		?>	
		<ul class="collapsible" data-collapsible="accordion">
			<li>
			  <div class="collapsible-header"><?= $continent;?> Trips<i class="mdi mdi-menu-right"></i></div>
			  <div class="collapsible-body">
				<div class="trip-acccontent">
					<ul class="trip-sidelist">
						<?php foreach ($countries as $country){
							$ctotal = PostForm::find()->where(['is_trip'=>'1','is_deleted'=>"0",'country'=>$country])->count();
							?>
							<li><a href="javascript:void(0)" onclick="country_trip('<?= $country;?>','<?= $ctotal?>')"><?= $country;?> trips <span>( <?= $ctotal;?> )</span></a></li>
						<?php } ?>
					</ul>
				</div>
			  </div>
			</li>
		<?php } ?>
	</div>
	<?php }?>
</div>	
<?php exit();?>