<?php 
use yii\helpers\ArrayHelper;
use frontend\assets\AppAsset;
use frontend\models\UserPhotos;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$baseUrl = AppAsset::register($this)->baseUrl;
$htmlView = '';
$isDisplay = false;
if($type == 'wall' || $type == 'page') {
	$isDisplay = true;
	$dataReport = ArrayHelper::map(UserPhotos::getAlbumsName($id), function($data) { return (string)$data['_id'];}, 'album_title');
	if(!empty($dataReport)) {
		$htmlView = '<select id="ablumnamedropdown">';
		foreach ($dataReport as $key => $dataReportSingle) {
			$htmlView .= '<option value="'.$key.'">'.$dataReportSingle.'</option>';	
		}
		$htmlView .= '</select>';
	}
}
?> 
<div class="modal_header">
	<button class="close_btn custom_modal_close_btn close_modal">
	  <i class="mdi mdi-close mdi-20px	"></i>
	</button>
	<h3>Add photos</h3>
</div>
<div class="custom_modal_content modal_content">
	<div class="content-holder nsidepad nbpad">
		<form class="add-album-form">
			<?php if($isDisplay) { ?>
				<div class="frow">
					<div class="caption-holder">
						<label class="left">Choose Album</label>
					</div>
					<br/>
					<div class="detail-holder">
						<div class="input-field">
							<?=$htmlView?>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="post-photos modal-album">
				<div class="img-row nice-scroll">						
					<div class="img-box">
						<div class="custom-file addimg-box">
							<div class="addimg-icon">
								<i class="zmdi zmdi-plus zmdi-hc-lg"></i>
							</div>
							<input class="upload custom-upload remove-custom-upload" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true" type="file">
						</div>
					</div>
				</div>
			</div>
			<div class="frow stretch-side">
				<div class="btn-holder">
					<a href="javascript:void(0)" class="post_btn active_post_btn modal_modal m-0" onclick="addAlbumImages()" data-class="addbtn">Add</a>
				</div>				
			</div>				
		</form>
	</div>
</div>
<?php 
exit;