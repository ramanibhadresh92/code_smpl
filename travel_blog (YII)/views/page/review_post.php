<?php if($type == 'posts'){ ?>
<h4><i class="zmdi zmdi-file-text"></i> Review Posts</h4>
<?php } else { ?>
<h4><i class="zmdi zmdi-image-o"></i> Review Photos</h4>
<?php } ?>
<div class="content-box">
	<ul class="reviews-list">
		<?php
			if(empty($reviewdposts))
			{
				echo '<div class="no-listcontent">
					No '.$type.' exist for review now.
				</div>';
			}
			else
			{
				foreach($reviewdposts as $reviewdpost){
				$postid = $reviewdpost['_id'];
				$this->context->display_review_post($postid);
			}
		} ?>
	</ul>
</div>
<?php exit;?>