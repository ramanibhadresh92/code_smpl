<?php

use frontend\assets\AppAsset;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id'); 
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="formtitle"><h4>Close Account</h4></div>
<div class="close-account-change">
	<div class="top-close-head">
		<h3>We can't close your account yet</h3>
	</div>
	<div class="new-closeaccount">
		<p>You need to be resolved some issues before we can close your account. Once you've resolved the issue, please retry closing your account from your setting page.</p>
	</div>
	<ul class="close-ul">
		<?php if(isset($data['myads']) && $data['myads'] == 'yes') { ?>
		<li>You have an active ad campaign. You'll need to <a href="?r=ads/manage">deactivate it through advert manager</a></li>
		<?php } ?>
		<?php if(isset($data['page']) && !empty($data['page'])) { ?>
		<li>
			<span>You admin the following pages. You will to need delete it or promote another admin throught page setting</span>
			<ul>
				<?php
				$pages = $data['page'];
				foreach ($pages as $key => $page) {
					echo '<li><a href="?r=page/index&id='.$key.'"><i class="mdi mdi-chevron-right"></i> '.$page.'</a></li>';
				}
				?>
			</ul>
		</li>
		<?php } ?>
	</ul>
	<div class="clear"></div>
</div>
<?php
exit;?>							