<?php 
use frontend\assets\AppAsset; 
use yii\helpers\Url;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="content-box">
	<div class="mbl-tabnav">
		<a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> <h6>Travellers</h6>
	</div>
	<div class="cbox-title nborder">
		People travelling to <?=$placefirst?> <span class="lt"></span>
	</div>
	<div class="cbox-desc person-list">
		<div class="row">
			<?php 
				$this->context->getUserGridLayout($getUsers, 'traveller');
			?> 
		</div>
	</div>
</div>
<?php exit;?>		
	
