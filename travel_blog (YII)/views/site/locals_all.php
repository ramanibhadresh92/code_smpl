<?php 
use frontend\assets\AppAsset; 
use yii\helpers\Url;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$baseUrl = AppAsset::register($this)->baseUrl;
?>          
<div class="content-box">
	<div class="mbl-tabnav">
		<a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> <h6>Locals</h6>
	</div>
	<div class="cbox-title nborder">
		<?=$placefirst?> Locals <span class="lt totallocalcounter"></span>
	</div>
	<div class="cbox-desc person-list">
		<div class="row">
		<?php
		$this->context->getUserGridLayout($getUsers, 'local');
		?>
		</div>
	</div>
</div>
<?php exit;?>		
	
