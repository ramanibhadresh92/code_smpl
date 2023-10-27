<?php
use yii\helpers\Url;
?>
<div class="cbox-title">
	<span><?= $this->context->getuserdata($wall_user_id,'fullname');?>'s</span>Ranking
</div>
<div class="cbox-desc">
	<div class="databox">
		<div class="meter-holder">
			<div class="gauge3 ranking-meter"></div>
			<h6><span>Points</span> <?= $point_total;?></h6>
		</div>
	</div>										
</div>
<div class="division"></div>

<div class="cbox-title">
	Top Rankers
</div>
<div class="cbox-desc">							
	<div class="resizable-holder sidelist">
		<div class="resizable">
			<ul class="rankers-list">
			<?php
				$i = 1;
				foreach($users as $user){
					if($user['point_total']){
						$user_id =  $user['_id'];
			?>	
					<li>
						<div class="ranker-box">
							<div class="img-holder"><img src="<?= $this->context->getimage($user_id,'photo');?>"/></div>
							<div class="desc-holder">
								<a href="<?php $ids = $user_id; echo Url::to(['userwall/index', 'id' => "$ids"]); ?>"><?= $this->context->getuserdata($user_id,'fullname');?></a>	
								<span class="info"><?= $this->context->getuserdata($user_id,'city');?></span>
							</div>								
						</div>							
					</li>
			<?php
					$i++;
					}
				}
			?>	
			</ul>
		</div>
		<div class="right resize-link" onclick="openResizable(this)"><a href="javascript:void(0)">View All</a></div>
	</div>
</div>
<script>
$('.gauge3').gauge({
	values: {
		0 : '0%',
		25: '25% - 75%',
		100: '100%'
	},
	colors: {
		0 : '#ED1C24',
		25 : '#39B54A',
		75: '#FDD914'
	},
	angles: [
		180,
		360
	],
	lineWidth: 20,
	arrowWidth: 10,
	arrowColor: '#ccc',
	inset:true,

	value: '<?= $percent;?>'
});
</script>

<?php exit();?>