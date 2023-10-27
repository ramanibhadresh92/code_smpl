<?php 
if(!empty($stops)){
$stop = explode('**',$stops['end_to']);
$stop = array_filter($stop);
?>
<div class="sliding-middle-out anim-area underlined">
	<select class="select2" id="mapdropplace" onchange="changePlaceTripPlace()">
		<?php foreach ($stop as $name) { ?>
		<option value="<?=$name?>"><?=$name?></option>
		<?php } ?>
	</select>
</div>
<?php } ?>
<?php exit;?>