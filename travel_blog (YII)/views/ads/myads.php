<?php   

use frontend\assets\AppAsset;
use frontend\models\Order;

$baseUrl = AppAsset::register($this)->baseUrl;

$date = time("Y-m-d");
?>
<table class="table-bordered">
	<?php if(!empty($myads)){ ?>
	<thead>
		<tr>
			<th>Status</th>
			<th class="text-left">Ad sets</th>
			<th>Ad Schedule</th>
			<th>Edit</th>
			<th>Delete</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($myads as $ad){
		$adid = $ad['_id'];
		$adruntype = $ad['adruntype'];
		if($adruntype == 'daily')
		{
			if(isset($ad['adstartdate']) && !empty($ad['adstartdate']))
			{
				$start = date('M d, Y', $ad['adstartdate']);
				$end = date('M d, Y', $ad['adenddate']);
			}
			else
			{
				$start = date('M d, Y', $ad['post_created_date']);
				$end = 'Today';
			}			
		}
		else
		{
			$start = date('M d, Y', $ad['adstartdate']);
			$end = date('M d, Y', $ad['adenddate']);
		}
		if($ad['is_ad'] == '1'){$gen = 'on';$che = '';}
		else{$gen = 'off';$che = 'disabled';}

		$isDirectPublish = isset($ad['direct_publish']) ? $ad['direct_publish'] : 'no';
	?>
		<tr id="hide_<?=$adid?>">
		<?php 
			$order = array();
			if($isDirectPublish != 'yes') {
				$order = Order::find()->where(['detail' => (string) $adid,'status' => 'Completed'])->one();
			}
			$expire = $ad['adenddate'];
			$today_time = strtotime($date);
			$expire_time = strtotime($expire);
			
		?>
			<td>
				<div class="switch">
					<label>
					<?php if(!empty($order) || $isDirectPublish == 'yes') { ?>
					<input id="ad_<?=$adid?>" data-adid="<?=$adid?>" <?=$che?> type="checkbox">
					<?php } else { ?>
					<input id="ad_<?=$adid?>" data-adid="<?=$adid?>" <?=$che?> type="checkbox" disabled="true">
					<?php } ?>
					<span class="lever"></span>
					</label>
				</div>
				<input type="hidden" id="ad_value_<?=$adid?>" value="<?=$gen?>" />
			</td>
			<td><a href="javascript:void(0)" onclick="openManageAdDetails(this)" data-adid="<?=$adid?>"  class="adlink"><?=$ad['adname']?><span><?=$this->context->getAdType($ad['adobj'])?></span></a></td>
			<td><?=$start?> - <?=$end?></td>
			<td><a href="javascript:void(0)" onclick="openEditAd('<?=$adid?>')"><i class="zmdi zmdi-edit zmdi-18"></i></a></td>
			<td><a href="javascript:void(0)" onclick="deleteAd('<?=$user_id?>','<?=$adid?>')"><i class="zmdi zmdi-delete zmdi-hc-fw zmdi-18"></i></a></td>
			<input type="hidden" name="spent" id="spent" />
		</tr>
	<?php } ?> </tbody> <?php } else { ?>
	<?php $this->context->getnolistfound('noadcreated');?>
	<?php } ?>
</table>
<script>
var baseUrl = "<?php echo (string)$baseUrl; ?>";
</script>

<script type="text/javascript" src="<?=$baseUrl?>/js/my-ads.js"></script>

<?php exit;?>