<ul>
<?php if(!empty($getpplacereviews)) {
	foreach($getpplacereviews as $post) { ?>
		<li>
			<img src="<?=$this->context->getimage($post['user']['_id'],'thumb');?>"/>
			<h6><span>Tip by</span> <?=$post['user']['fullname']?></h6>
			<p> 
				<?=$post['post_text']?> 
				<a href="javascript:void(0)" onclick="openDirectTab('places-tip')" class="arrow-more"><i class="mdi mdi-arrow-right-bold-circle-outline"></i></a>
			</p>
		</li>
<?php }
} else {
	$this->context->getnolistfound('becomefirstfortip');
} ?>
</ul>
<?php exit;?>