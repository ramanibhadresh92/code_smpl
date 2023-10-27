<?php   
use frontend\assets\AppAsset;
use frontend\models\Credits;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$totalcredits = Credits::usertotalcredits();
$total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
$total_len = strlen($total);
$total = str_split($total);
$userallcredits = Credits::usercreditshistory();
$this->title = 'Billing Info';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper  hidemenu-wrapper full-wrapper white-wrapper noopened-search show-sidebar">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
		
	<div class="floating-icon">
		<div class="scrollup-btnbox anim-side btnbox scrollup-float">
			<div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>          
		</div>            
	</div>
	<div class="clear"></div>
	<?php include('../views/layouts/leftmenu.php'); ?>
	<div class="fixed-layout ipad-mfix">
		<div class="main-content with-lmenu sub-page billinginfo-page main-page">
			<div class="combined-column wide-open">
				<div class="content-box">
					<div class="container">
						<div class="cbox-title nborder">
							<i class="zmdi zmdi-file-text"></i>
							Billing Information
						</div>
						<div class="cbox-desc">
							<div class="fake-title-area">
								<ul class="tabs">									
									<li class="active tab"><a href="#billinginfo-history" data-toggle="tab" aria-expanded="false">Purchase History</a></li>
									<li class="tab"><a href="#billinginfo-currency" data-toggle="tab" aria-expanded="false">Currency</a></li>
									<li class="tab"><a href="#billinginfo-auto" data-toggle="tab" aria-expanded="false">Auto-Recharge</a></li>
								</ul>
							</div>
							<div class="tab-content">
								<div class="tab-pane fade active in main-pane" id="billinginfo-history">								
									<div class="history-table">
									<?php 
										$date = date('d-m-Y');
										$time=strtotime($date);
										$current_month=date("F Y",$time);
										
										$year  = date("Y",$time);
										$month = date("m",$time);
										
										$date2 = mktime(0, 0, 0, $month, 1, $year);
										$last_month =  date("F Y", strtotime('-1 month', $date2));
										$next_month =  date("F Y", strtotime('+1 month', $date2));
										
									?>
										<div class="table-navigation">	
											<a href="javascript:void(0)" onclick="billing_info('<?=$last_month?>')" class="prev-month left"><i class="zmdi zmdi-chevron-left"></i><?=$last_month?></a>
											<span><?=$current_month?></span>
											<a href="javascript:void(0)" onclick="billing_info('<?=$next_month?>')" class="next-month right"><?=$next_month?><i class="zmdi zmdi mdi-chevron-right"></i></a>
										</div>
										<div class="table-responsive">
										  <table class="striped">
											<thead>
												<tr>
													<th>Date</th>
													<th>Order#</th>
													<th>Item</th>
													<th>Paid Via</th>
													<th>Amount</th>
													<th>Details</th>
													<th>Status</th>
												</tr>
											</thead>
											<tbody class="bill-info-table">
											<?php 
												if(empty($orderhistory))
												{
													echo '<tr><td colspan="7">';
													$this->context->getnolistfound('notransaction');
													echo '</td></tr>';
												}
												foreach($orderhistory as $order){
													$time=strtotime($order['current_date']);
													$month=date("F",$time);
													$day=date("d",$time);
													
													$order_type = '';
													$order_detail = '';

													if($order['order_type'] == 'joinvip') 
													{
														$order_type = 'VIP Member'; 
													}
													else if($order['order_type'] == 'buycredits') 
													{
														$order_type = 'Purchase Credits'; 
													} 
													else if($order['order_type'] == 'verify') 
													{ 
														$order_type = 'Verify Member'; 
													}

													if($order['order_type'] == 'joinvip') 
													{
														$order_detail =  $order['detail'] . ' Month'; 
													}
													else if($order['order_type'] == 'buycredits')
													{
														$order_detail =  $order['detail'] . ' Credits'; 
													}
													else if($order['order_type'] == 'verify') 
													{
														$order_detail =  '1 Year'; 
													}
													
												?>
													<tr>
														<td><?= $month .' '.$day;?></td>
														<td><?= $order['transaction_id'];?></td>
														<td><?=$order_type?></td>
														<td><?= $order['curancy'];?></td>
														<td><?= $order['amount'];?></td>
														<td><?= $order_detail?></td>
														<td class="btext"><?= $order['status'];?></td>
													</tr>
													<?php } ?>
											</tbody>
										  </table>
										</div>
									</div>
								</div>
								
								<div class="tab-pane fade main-pane" id="billinginfo-currency">
									<?php $this->context->getnolistfound('comingsoon'); ?>
								</div>
								
								<div class="tab-pane fade main-pane" id="billinginfo-auto">
									<?php $this->context->getnolistfound('comingsoon'); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="chatblock">
				<div class="float-chat anim-side">
					<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
					</div>
				</div>
			</div>
		</div>
	</div>  
	  <?php include('../views/layouts/footer.php'); ?>
</div>  
 
<script type="text/javascript">
/* site/billing */
function billing_info(month){
	if(month != '')
	{
		$.ajax({
			url: '?r=site/billinginfo',
			type: 'POST',
			data: 'month=' + month,
			success: function (data)
			{
				$('#billinginfo-history').html(data);
			}
		});
	}
}
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<?php $this->endBody() ?> 