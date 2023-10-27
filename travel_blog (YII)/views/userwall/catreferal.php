<?php 
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$url = $_SERVER['HTTP_REFERER'];
$urls = explode('&',$url);
$url = explode('=',$urls[1]);
$wall_user_id = $url[1];
?>		

<div class="post-list post-referal" data-id="dels">
	<?php 
	if((count($referals)) == 0)
	{
		$this->context->getnolistfound('noreferencefound');
	}
	$lp = 1; 
	foreach($referals as $referal)
	{ 
		$existing_posts = '1';
		$cls = '';
		if(count($referals)==$lp) {
		  $cls = 'lazyloadscroll'; 
		}
		$this->context->display_last_referal($referal['_id'],$existing_posts, '', $cls);
		$lp++;
	}?>
</div>
<div class="clear"></div>
<?php exit();?>				