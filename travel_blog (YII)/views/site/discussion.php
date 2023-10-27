<?php
use yii\helpers\Url;
?>
<ul>
<?php 
if(!empty($getpplacereviews)) {
	foreach($getpplacereviews as $post) { ?>
		<li>
			<img src="<?=$this->context->getimage($post['user']['_id'],'thumb');?>"/>
			<h6><span>Discussion by</span> <?=$post['user']['fullname']?></h6>
			<p>
				<?=$post['post_text']?>
				<a href="<?php $postid = $post['_id']; echo Url::to(['site/travpost', 'postid' => "$postid"]);?>" class="arrow-more"><i class="mdi mdi-arrow-right-bold-circle-outline"></i></a>
			</p>
		</li>
<?php 
	}
} else {
	$this->context->getnolistfound('becomefirstforplacediscussion');
} ?>
</ul>
<?php exit;?>