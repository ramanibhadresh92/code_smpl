<?php if(!empty($getpics)) {
		$ctr = 1;
		foreach($getpics as $getpic){
			if($ctr < 7){
			$time = Yii::$app->EphocTime->comment_time(time(),$getpic['created_date']);
			$eximgs = explode(',',$getpic['image'],-1);
			$added_by = $getpic['user_id'];
			foreach ($eximgs as $eximg) {
		?>
		<div class="grid-box">
			<div class="destination-box">
				<div class="imgholder"><img src="../web/uploads/placephotos/<?=$added_by.'/'.$eximg?>"></div>
			</div>
		</div>
	<?php $ctr++; } } } } else { ?>
	<div class="no-listcontent">
		No photos found.
	</div>
	<?php } ?>
		</div>
	</div>
</div>
<?php exit;?>