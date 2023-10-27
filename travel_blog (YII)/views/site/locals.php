<?php 
use yii\helpers\Url;
if(!empty($getUsers)){
	$c = count($getUsers);
	if($c > 5){$c = 6;}
	for($i=0;$i<$c;$i++){
		$traveller_id = $getUsers[$i]['_id'];
		$link = Url::to(['userwall/index', 'id' => (string)$traveller_id]);
		$trvl_img = $this->context->getimage($traveller_id,'photo');
?>
			<div class="grid-box">
				<div class="connect-box">
					<div class="imgholder"><img src="<?=$trvl_img?>"/></div>
					<div class="descholder">
						<a href="<?=$link?>">											
							<span class="userlink"><?=$getUsers[$i]['fullname']?></span>
						</a>
					</div>
				</div>							
			</div>
	<?php 	} 
	} else { 
	?>
	<?php $this->context->getnolistfound('nolocalfound');?>
	<?php } ?>
		</div>
	</div>
</div>
<?php exit;?>