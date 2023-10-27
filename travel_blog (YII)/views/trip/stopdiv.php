<?php   
if(!empty($stops)){
$stop = explode('**',$stops['end_to']);
$stop = array_filter($stop);
?>

	<label>Add</label>
	<select id="afterstop" class="dis-none">
		<?php 
		$i = 1;
		foreach ($stop as $name) { 
			if($i == 1) {
			?>
			<option value="BEFORE--<?=$name?>">Before <?=$name?></option>
			<option value="AFTER--<?=$name?>">After <?=$name?></option>
			<?php
			} else { ?>
			<option value="AFTER--<?=$name?>">After <?=$name?></option>
		<?php }
		$i++;
		} ?>
	</select>
<?php } ?>
<?php exit;?>