<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="post-list">
	<?php 
	if((count($yourtripexps)) == 0) {
		$this->context->getnolistfound('notripexperienceaddedbyyou');
	}
	
	$templp = 1;
	foreach($yourtripexps as $post) {
		$existing_posts = '1';
		$cls = '';
		if(count($post)==$templp) 
		{
		  $cls = 'lazyloadscroll'; 
		}

		$postid = (string)$post['_id'];
		$postownerid = (string)$post['post_user_id'];
		$postprivacy = $post['post_privacy'];

		$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
		if($isOk == 'ok2389Ko') {
			$this->context->display_last_post((string)$postid, $existing_posts, '', $cls);
		}
		$templp++;
	}
	?>
</div>
<div class="clear"></div>
<center><div class="lds-css ng-scope page-post-lazyload dis-none"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
<?php exit;?>
