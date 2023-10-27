<?php

use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<!--preferences modal-->
<div id="preference_modal" class="modal preference_modal">
	<div class="modal_header">
		<button class="close_btn custom_modal_close_btn close_modal waves-effect">
		  <i class="material-icons material_close">close</i>
		</button>
		<h3>Preferences</h3>
	</div>
	<div class="post-loader text-center">
		<img src="<?=$baseUrl?>/images/loader1.svg"  />
	</div>
</div>