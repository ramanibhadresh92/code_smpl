<ul>
<?php if(!empty($getpplacereviews)) {
	foreach($getpplacereviews as $post) { ?>
			<li>
				<img src="<?=$this->context->getimage($post['user']['_id'],'thumb');?>"/>
				<h6><?=$post['user']['fullname']?></h6>
				<p onclick="openDirectTab('places-ask')">
					<?=$post['post_text']?>
				</p>
			</li>
	<?php }
} else { ?>
	<?php $this->context->getnolistfound('becomefirstforask'); ?>
<?php } ?>
</ul>
<?php exit; ?>