<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="post-list">
	<?php 
	if((count($alltripexps)) == 0)
	{
		$this->context->getnolistfound('notripexperiencefound');
	}
	$templp = 1;
	$lp = 1; 
	foreach($alltripexps as $post)
	{ 
		$existing_posts = '1';
		$cls = '';
		if(count($post)==$templp) {
		  $cls = 'lazyloadscroll'; 
		}

		$postid = (string)$post['_id'];
		$postownerid = (string)$post['post_user_id'];
		$postprivacy = $post['post_privacy'];
 
		$isOk = $this->context->filterDisplayLastPost($postid, $postownerid, $postprivacy);
		if($isOk == 'ok2389Ko') {
			if(($lp%8) == 0) {
				$ads = $this->context->getad(true); 
				if(isset($ads) && !empty($ads))
				{
					$ad_id = (string) $ads['_id'];	
					$this->context->display_last_post($ad_id, $existing_posts, '', $cls);
					$lp++; 
				} else {
					$lp++;
				}
			} else {
				$this->context->display_last_post($postid, $existing_posts, '', $cls);
				$lp++;	
			}						
		}
		$templp++;
	}?>
</div>
<div class="clear"></div>
<center><div class="lds-css ng-scope page-post-lazyload dis-none"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
<?php exit;?>
										