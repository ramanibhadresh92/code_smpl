<?php 
use yii\helpers\Url;
use frontend\models\LoginForm;
if(isset($getUsers) && !empty($getUsers)) { 
	$getUsers = array_slice($getUsers, 0, 6);
	foreach($getUsers as $getUser) {
		if(isset($getUser['user_id'])) {
			$traveller_id = $getUser['user_id'];
		} else {
			$traveller_id = $getUser;
		}
		$getUser = LoginForm::find()->where(['_id' => "$traveller_id"])->one();
		$link = Url::to(['userwall/index', 'id' => (string)$traveller_id]);
		$trvl_img = $this->context->getimage($traveller_id,'photo');
		?>
		<div class="grid-box">   
			<div class="connect-box">
				<div class="imgholder"><img src="<?=$trvl_img?>"/></div>
				<div class="descholder">
					<a href="<?=$link?>">											
						<span class="userlink"><?=$getUser['fullname']?></span>
					</a>
				</div>
			</div>							
		</div>
<?php } } else { ?>
	<?php $this->context->getnolistfound('notravellerfound');?>
	<?php } ?>
		</div>
	</div>
</div>
<?php exit;?>