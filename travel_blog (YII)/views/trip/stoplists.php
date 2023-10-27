<?php   
if(!empty($stops)){
$stop = explode('**',$stops['end_to']);
$stop = array_filter($stop);
$i = 1;
?>
<ul class="tripstops-list">
<?php foreach ($stop as $name) { ?>
	<li>
		<div class="tripstop hasdelete">
			<div class="title">
				<span class="numbering"><?=$i?></span>
				<h5><?=$name?></h5>
			</div>
			<div class="deletebtn">
				<a href="javascript:void(0)" onclick="deletetripplace('<?=$name?>','<?=$stops['_id']?>')" class="right deletelink redicon"><i class="zmdi zmdi-delete"></i></a>
			</div>
		</div>
	</li>
<?php $i++; } ?>
</ul>
<?php } ?>
<?php exit;?>