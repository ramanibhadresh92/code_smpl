<?php
	if($title == 'hotels') {
		$link = Url::to(['site/hotellist']);
	} else if($title == 'restaurants') {
		$link = Url::to(['site/restaurantlist']);
	} else if($title == 'park') {
		$link = 'javascript:void(0)';
	}
?>
<iframe src="https://maps.google.it/maps?q=<?=$title?>+in+<?=$placetitle?>&output=embed" width="600" height="450" frameborder="0" allowfullscreen></iframe>
<div class="overlay">
	<a href="<?=$link?>">Back to list view</a>
</div> 
<?php exit;?>