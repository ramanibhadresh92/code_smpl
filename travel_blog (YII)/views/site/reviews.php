<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="reviews-summery">
	<div class="reviews-add">
		<?php if(!empty($getpplacereviews)) { ?>
		<div class="stars-holder">	
			<?php
			for($i=0;$i<5;$i++)
			{ if($i < $avgcnt){ $d = 'filled'; } else { $d = 'blank'; } ?>
				<img src="<?=$baseUrl?>/images/<?=$d?>-star.png"/>
			<?php } ?>
		</div>
		<?php } ?> 
		<p>What do you think about this place?</p>
	</div>
	<div class="reviews-people">
<?php if(!empty($getpplacereviews)) { ?>
	<ul>
	<?php foreach($getpplacereviews as $post) { ?>
			<li>
				<div class="reviewpeople-box">
					<div class="imgholder"><img src="<?=$this->context->getimage($post['user']['_id'],'thumb');?>"/></div>
					<div class="descholder">
						<h6><?=$post['user']['fullname']?> <span><?=Yii::$app->EphocTime->time_elapsed_A(time(),$post['post_created_date'])?></span></h6>
						<div class="stars-holder">
							<?php for($i=0;$i<5;$i++)
							{ if($i < $post['placereview']){ $d = 'filled'; } else { $d = 'blank'; } ?>
								<img src="<?=$baseUrl?>/images/<?=$d?>-star.png"/>
							<?php } ?>
							<p onclick="openDirectTab('places-reviews')"><?=$post['post_text']?></p>
						</div>
					</div>
				</div>
			</li>
	<?php } ?>
	</ul>
<?php
} else {
	$this->context->getnolistfound('becomefirstforplacereview');
	//echo '<div class="no-listcontent">Become a first to review for '.$placefirst.' place</div>';
} ?>
	</div>
</div>
<?php exit; ?>