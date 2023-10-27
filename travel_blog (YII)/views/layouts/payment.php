<?php   
use yii\widgets\ActiveForm;
use frontend\assets\AppAsset;
use frontend\models\LoginForm;
use frontend\models\Vip;
use frontend\models\SecuritySetting;
use frontend\models\UserForm;
use backend\models\AddcreditsPlans;
use frontend\models\Credits;
use frontend\models\Verify;
use backend\models\AddvipPlans;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');

$request = Yii::$app->request;
$suceess_message = $request->get('success');

$server_name = $_SERVER['SERVER_NAME']; 
if($server_name == "localhost"){
	if($callfrom == 'ads') {
		$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=ads/successads";
		$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=ads/successads";
	} else if($callfrom == 'credits') {
		$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/buycredits";
		$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/buycredits";
	} else if($callfrom == 'verify') {
		$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=site/verifyme";
		$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=site/verifyme";
	} else {
		$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/successvip";
		$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/failvip";
	}
} else {
	if($callfrom == 'ads') {
		$surl = $server_name . "/frontend/web/index.php?r=ads/successads";
		$furl = $server_name . "/frontend/web/index.php?r=ads/successads";
	} else if($callfrom == 'credits') {
		$surl = $server_name . "/frontend/web/index.php?r=vip/buycredits";
		$furl = $server_name . "/frontend/web/index.php?r=vip/buycredits";
	} else if($callfrom == 'verify') {
		$surl = $server_name . "/frontend/web/index.php?r=site/verifyme";
		$furl = $server_name . "/frontend/web/index.php?r=site/verifyme";
	} else {
		$surl = $server_name . "/frontend/web/index.php?r=vip/successvip";
		$furl = $server_name . "/frontend/web/index.php?r=vip/failvip";
	}
}


$record = LoginForm::find()->where(['email' => $session->get('email')])->one();
$fname = $record['fname'];  
$lname = $record['lname'];  
$email = $record['email'];  
$city = $record['city'];  
$ulcls = '';
$onclick1 = '';
$id1 = '';
$id2 = '';
$_plan_id = '';
$extra_cls = '';
$extra_cls1 = '';
if($callfrom == 'vip') {
	$model = new \backend\models\AddvipPlans;
	$vip_plans = AddvipPlans::getVipPlans();
	if(count($vip_plans)==0)
	{
		$vipplans=array("10"=>"1","9"=>"3","8"=>"6","7"=>"12");
		foreach($vipplans as $x=>$x_value)
		{
		  $model->amount = (int)$x;
		  $model->months = (int)$x_value;
		  $model->insert();
		}
	}
	$vip_plans = $model->getVipPlans();
	if(isset($vip_plans) && !empty($vip_plans))
	{
		$amt = $vip_plans[1]['amount'] * $vip_plans[1]['months'];
		$_plan_id = $vip_plans[1]['_id'];
	}

	$rightLaps = array('<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Earn Points</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Discount on Ads</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Share Plans</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Automatic Updates</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>VIP and Verified Badge</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Get Listed  First</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Send Message</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Message Read</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Profile viewer</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Business Listed First</h6></div> </div> </li>', '<li> <div class="vip-info"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>Premium Support</h6></div> </div> </li>', '<li> <div class="vip-info onlycap"> <div class="iconholder"><i class="zmdi zmdi-check"></i></div> <div class="descholder"> <h6>For only $9 monthly</h6> </div> </div> </li>');
	$popupLabel = 'VIP Membership';
	$subheader = 'Enjoy various exclusive perks and boost your popularity';
	$infoheader = 'Check our VIP plans';
	$desc1 = 'Automatically renew my plan on expiration ';
	$action = 'vip/success-vip-card';
	$alt1 = 'Iaminjapan VIP';
	$id1 = 'selected_vip_plan';
	$ulcls = '';
	$extra_cls = 'vip-page';
	$planLbl = 'Iaminjapan VIP Plan';
	$extra_cls1 = 'vip_plan_amount';
} else if($callfrom == 'credits') {
	$ulcls = 'checkul';
	$model = new AddcreditsPlans();
	$credits_plans = $model->getCreditsPlans();
	if(count($credits_plans)==0)
	{
		$creplans=array("500"=>"5","1150"=>"10","1800"=>"15","2500"=>"20","3250"=>"25");
		foreach($creplans as $x=>$x_value)
		{
			  $model->credits = (int)$x;
			  $model->amount = (int)$x_value;
			  $model->insert();
		}
	}
	$credits_plans = $model->getCreditsPlans();	

	if(isset($credits_plans) && !empty($credits_plans))
	{
		$amt = $credits_plans[1]['amount'];
		$_plan_id = $credits_plans[1]['_id'];
	}

	$totalcredits = Credits::usertotalcredits();
	$total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
	$total_len = strlen($total);
	$total = str_split($total);

	$rightLaps = array('<li> <i class="zmdi zmdi-check"></i> First in search list 100 credits/day </li>', '<li> <i class="zmdi zmdi-check"></i> Send eGift for 100 credits </li>', '<li> <i class="zmdi zmdi-check"></i> Transfer credits to your connections </li>', '<li> <i class="zmdi zmdi-check"></i> Boost my popularity 100 credits/day </li>');
	$popupLabel = 'Iaminjapan Credits';
	$subheader = 'Virtual currency which can be used to buy various services on Iaminjapan';
	$infoheader = 'Top up now - The more credits you buy, the cheaper they are :';
	$desc1 = 'Topup my iaminjapan credits automatically when my balance falls below 100 credits. To disable auto-topup, please uncheck the box ';
	$action = 'vip/success-credits-card';
	$alt1 = 'Iaminjapan Credits';
	$onclick1 = 'onclick="buy_credits()"';
	$id1 = 'buy_credits';
	$planLbl = 'Iaminjapan Credit Plan';
	$id2 = 'credit_amount';
	$extra_cls1 = 'credit_amount';
} else if($callfrom == 'verify') {
	$ulcls = 'imgul';
	$model = new \backend\models\AddverifyPlans;
	$verify_plans = $model->getVerifyPlans();
	if(count($verify_plans)==0)
	{
		$verifyplans=array("1"=>"12","1"=>"6");
		foreach($verifyplans as $x=>$x_value)
		{
			  $model->amount = (int)$x;
			  $model->months = (int)$x_value;
			  $model->insert();
		}
	}

	$this->title = 'VERIFY';
	$isverify = Verify::isVerify($user_id);
	if($isverify)
	{
		$ended_date = Verify::End_date_Verify($user_id); 
		$ended_date = strtotime($ended_date);
		$day=date("d",$ended_date);
		$month = date("F",$ended_date);
		$year=date("Y",$ended_date); 
	}
	if(isset($verify_plans) && !empty($verify_plans))
	{
		$amt = $verify_plans[0]['amount'] * $verify_plans[0]['months'];
		$_plan_id = $verify_plans[0]['_id'];
	}

	$rightLaps = array('<li> <img src="'.$baseUrl.'/images/connect.png"><br/>Connect Faster </li>', '<li> <img src="'.$baseUrl.'/images/earnverified.png"><br/>Earn Verified Check </li>');
	$popupLabel = 'Get verified and establish trust';
	$subheader = 'Verified people connect with travellers faster';
	$infoheader = 'Choose the package that suits you best';
	$desc1 = 'Automatically renew my verification on expiration ';
	$action = 'vip/success-verify-card';
	$alt1 = 'Iaminjapan Verify';
	$onclick1 = 'onclick="verifyme()"';
	$id1 = 'selected_verify_plan';
	$planLbl = 'Iaminjapan Verify User';
	$extra_cls1 = 'verify_plan_amount';
} else {
	$rightLaps = array();
	$popupLabel = 'Advert Payment';
	$subheader = 'Please select your preferred payment method';
	$infoheader = '';
	$desc1='';
	$action = 'ads/success-card';
	$alt1 = '';
	$id1 = 'buy_ads';
	$planLbl = 'Iaminjapan Credit Plan';
	$id2 = 'ads_amount';
	$amt = '2';
}
?>
<div class="credit-modal-content wow zoomIn  animated" data-wow-duration="1200ms" data-wow-delay="500ms">
	<div class="popup-title credit">
		<h3><?=$popupLabel?></h3>
		<h6><?=$subheader?></h6> 
		<a class="close-popup modal-action modal-close waves-effect" href="javascript:void(0)">
			<i class="mdi mdi-close"></i>
		</a>
	</div>
	<div class="popup-content"> 
		<div class="payment" id="tabs">
			<div class="payment-tab">
				<ul class="tabs">
					<li class="tab"><a href="#payment-paypal"><span class="icon"><img src="<?=$baseUrl?>/images/payment-paypal.png"/></span>Paypal</a></li>
					<li class="tab"><a href="#payment-creditcard"><span class="icon"><img src="<?=$baseUrl?>/images/payment-creditcard.png"/></span>Credit Card</a></li>
					<?php if($callfrom != 'ads') { ?>
					<li class="tab"><a href="#payment-mobile"><span class="icon"><img src="<?=$baseUrl?>/images/payment-mobile.png"/></span>Mobile Pay</a></li>
					<?php } ?>
				</ul>
			</div>
			<div class="payment-info">
				<div class="tab-content">
					<?php if($callfrom != 'ads') { ?>
					<div class="module-info">
						<ul class="checkul">
							<?php
							foreach ($rightLaps as $srightLaps) {
								echo $srightLaps;
							}
							?>
						</ul>
					</div>
				<?php } ?>
					<div class="common-info">
						<?php if($callfrom == 'vip') { ?>
							<h6>Check our VIP plans</h6>
							<div class="vip-page">
								<div class="vip-package">
									<ul class="vip_plan">
									<?php $i = 0; ?>
									<?php foreach($vip_plans as $vip_plan) { ?>
										<li data-viplan="<?=$vip_plan['_id'];?>">
											<div class="month-radio">
												<label class="control control--radio"><?= $vip_plan['months'];?> 
												<?php 
												if($vip_plan['months'] <= 1) {
													echo 'Month';
												} else {
													echo 'Months';
												}
												?>
												<input type="radio" name="radio" <?php if($i==1){ echo 'checked';}?>/>
												  <div class="control__indicator"></div>
												</label>
											</div>
											<div class="month-save">
												<?php 
												if(isset($vip_plan['percentage']) && !empty($vip_plan['percentage'])) { ?>
													Save <span><?=$vip_plan['percentage']?>%</span>
												<?php 
												} else {
													echo '&nbsp;';	
												}
												?>
											</div>
											<div class="month-package">						US $<?= number_format($vip_plan['amount'],2);?>/month
											</div>
											<div class="month-special">
											<?php 
											if($vip_plan['plan_type'] == 'Most Popular') {
											?> <span class="popular">Most Popular</span>
											<?php } else if($vip_plan['plan_type'] == 'Popular') {
											?>	
												<span class="popular">Popular</span>
											<?php } else if($vip_plan['plan_type'] == 'Best Value') {?>
												<span class="value">Best Value</span>
											<?php }
											?>	
											</div>
										</li>
									<?php $i++; } ?>
									</ul>
								</div>
							</div>		
						<?php } else if($callfrom == 'credits') { ?>
							<h6>Top up now - The more credits you buy, the cheaper they are :</h6>
							<div class="vip-page">
								<div class="vip-package">
									<ul class="credit_plan">
									<?php $c = 0; ?>
									<?php foreach($credits_plans as $credits_plan){ ?>
										<li data-creditplan="<?=$credits_plan['_id'];?>">
											<div class="month-radio">
												<label class="control control--radio"><?= $credits_plan['credits'];?> 
											<?php if($credits_plan['credits'] <= 1){
											echo 'credit';
											}
											else {
												echo 'credits';
											}
											?>
												  <input type="radio" name="radio" <?php if($c==1){ echo 'checked';}?>/>
												  <div class="control__indicator"></div>
												</label>
											</div>
											<div class="month-save">
											<?php 
												if(isset($credits_plan['percentage']) && !empty($credits_plan['percentage']))
												{
												?>Save <span>$<?=$credits_plan['percentage']?></span>
												<?php 
												} 
												else 
												{
													echo '&nbsp;';	
												}
												?>
												
											</div>
											<div class="month-package">
												US $<?= number_format($credits_plan['amount'],2);?>
											</div>
											<div class="month-special">
											<?php 
											if($credits_plan['plan_type'] == 'Most Popular') 
											{
											?>	
												<span class="popular">Most Popular</span>
											<?php } else if($credits_plan['plan_type'] == 'Popular') 
											{
											?>	
												<span class="popular">Popular</span>
											<?php } else if($credits_plan['plan_type'] == 'Best Value')
											{
											?>
												<span class="value">Best Value</span>
											<?php 
											}
											else{
											}	
											?>	
											</div>
										</li>
										<?php $c++; } ?>
									</ul>
								</div>
							</div>
						<?php } else if($callfrom == 'verify') { ?>
							<h6>Choose the package that suits you best</h6>
					
							<div class="vip-page">
								<div class="vip-package">
									<ul class="verify_plan">
									<?php $i = 0; ?>
									<?php foreach($verify_plans as $verify_plan){ ?>
										<li data-verifyplan="<?=$verify_plan['_id'];?>">
											<div class="month-radio">
												<label class="control control--radio"><?= $verify_plan['months'];?> 
											<?php if($verify_plan['months'] <= 1) {
													echo 'Year';
												} else {
													echo 'Years';
												}
											?>
												  <input type="radio" name="radio" <?php if($i==0){ echo 'checked';}?>/>
												  <div class="control__indicator"></div>
												</label>
											</div>
											<div class="month-save">
											<?php 
												if(isset($verify_plan['percentage']) && !empty($verify_plan['percentage']))
												{
												?>Save <span><?=$verify_plan['percentage']?>%</span>
												<?php 
												} 
												else 
												{
													echo '&nbsp;';	
												}
												?>
												
											</div>
											<div class="month-package">
												US $<?= number_format($verify_plan['amount'],2);?>/year
											</div>
											<div class="month-special">
											<?php 
											if($verify_plan['plan_type'] == 'Most Popular') 
											{
											?>	
												<span class="popular">Most Popular</span>
											<?php } else if($verify_plan['plan_type'] == 'Popular') 
											{
											?>	
												<span class="popular">Popular</span>
											<?php } else if($verify_plan['plan_type'] == 'Best Value')
											{
											?>
												<span class="value">Best Value</span>
											<?php 
											}
											else
											{
												
											}	
											
											?>	
											</div>
										</li>
										<?php $i++; } ?>
										
									</ul>
								</div>
							</div>
						<?php } else { ?>
							<h4>Your Cart</h4>
							<div class="frow">
								<div class="caption-holder">
									<label>Ad Name</label>
								</div>
								<div class="detail-holder">
									<p id="ad_name"></p>
								</div>
							</div>
							<div class="frow">
								<div class="caption-holder">
									<label>Ad Duration <a href="javascript:void(0)" class="simple-tooltip"><i class="zmdi zmdi-help"></i></a></label>
								</div>
								<div class="detail-holder" id="ad_duration"> </div>
							</div>
							<div class="frow">
								<div class="caption-holder">
									<label>Daily Budget <a href="javascript:void(0)" class="simple-tooltip"><i class="zmdi zmdi-help"></i></a></label>
								</div>
								<div class="detail-holder">
									<p id="daily_budget"></p>
								</div>
							</div> 
							<div class="clear"></div>
							<div class="divider"></div>
							<div class="frow">
								<div class="caption-holder">
									<label>Total Cost <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="zmdi zmdi-help"></i></a></label>
								</div>
								<div class="detail-holder">
									<span class="green-t" id="total_budget"></span>
								</div>
							</div>
						<?php } ?>					
					</div>
					<div id="payment-paypal" class="payment-content center-align active in">
					<?php
						$paypal_url='https://www.sandbox.paypal.com/cgi-bin/webscr'; // Test Paypal API URL
						$paypal_id='adel.merchant@123.com'; // Business email ID
						?>
						
						<!-- 
						if($callfrom == 'ads') { 
							<a href="javascript:void(0)" id="pay1" class="m-t-50 paypal_btn paypal-btn dis-none" onclick="buy_ads()"><img src="<?=$baseUrl?>/images/paypal_btn.png"/></a>
							
							<input id="pay2" type="button" onclick="buy_ads2()" value="Publish Advert" class="btn btn-primary btn-xl dis-none"  name="button"> 
						} 
						-->

						<form id="myformsubmit" action="<?=$paypal_url?>" method="post" name="frmPayPal1">
							<?php if($callfrom == 'ads') { ?>
								<input id="pay1" style="display: none;" type="image" onclick="buy_ads()" value="pay now" class="paypal-btn" src="<?=$baseUrl?>/images/blue-paypal-btn.png" border="0" name="submit" alt="Iaminjapan Credits" >
								
								<input id="pay2" style="display: none;" type="button" onclick="buy_ads2()" value="Publish Advert" class="btn btn-primary btn-xl capitalize paypal-btn"  name="button">
							<?php } else { ?>
								<input type="image" value="pay now" class="paypal-btn" <?=$onclick1?> src="<?=$baseUrl?>/images/blue-paypal-btn.png" border="0" name="submit" alt="<?=$alt1?>" >
							<?php } ?>
						
							<div class="paypal-info">
								<input type="hidden" id="<?=$id1?>" value="<?=$_plan_id?>">
								<input type="hidden" name="business" value="<?=$paypal_id?>">
								<input type="hidden" name="cmd" value="_xclick">
								<input type="hidden" name="item_name" value="<?=$planLbl?>">
								<input type="hidden" name="first_name" value="<?=$fname?>">
								<input type="hidden" name="last_name" value="<?=$lname?>">
								<input type="hidden" name="email" value="<?=$email?>">
								<input type="hidden" name="userid" value="1">
								<input type="hidden" name="amount" value="<?=$amt?>" id="<?=$id2?>" class="<?=$extra_cls1?>">
								<input type="hidden" name="cpp_header_image" value="<?=$baseUrl?>/images/black-logo.png">
								<input type="hidden" name="no_shipping" value="1">
								<input type="hidden" name="currency_code" value="USD">
								<input type="hidden" name="handling" value="0">
								<input type="hidden" name="cancel_return" value="http://<?=$furl?>">
								<input type="hidden" name="return" value="http://<?=$surl?>">
								<div class="clear"></div>

									<?php if($callfrom != 'ads') { ?>
									<div class="leftbox">
										<input type="checkbox" id="test1" />
											<label for="test1"><?=$desc1?>
											<a href="javascript:void(0)"> <i class="zmdi zmdi-help"></i> </a>
										</label>
									</div>
								<?php } ?>
									<div class="clear"></div> 
									<div class="expandable-holder conditions">
										<p>Your paypal account will be charged US $9.99.
											<span class="expand-link invertsign" onclick="mng_expandable(this,'mainright');"> Services conditions <i class="mdi mdi-menu-right"></i></span>
										</p>
										<div class="gray-area expandable-area" style="margin-right: 30px;">
											<p>You’ll receive monthly statements and can choose to pay in full or over time (interest charges may apply). At any time you can choose to make a one time payment or schedule automatic payments towards your balance due from either your PayPal balance or your linked bank account conveniently on PayPal.com.</p>
										</div>	
									</div>
							</div>
							
						</form>
					</div> 
					<div id="payment-creditcard" class="payment-content center-align">
						<div class="credit-card">
						<?php $form = ActiveForm::begin(['action' => [$action],'options' => ['method' => 'post','id'=>'paymentForm']]) ?>
						
						 <input type="hidden" name="card_type" id="card_type" value=""/>
							<div class="card-frame">
								<div class="crow">
									<label>Name on the card</label>
									 <input type="text" placeholder="Codex World" id="name_on_card" name="name_on_card" class="cardname">
								</div>
								<div class="row">
									<div class="col m6 s12">
										<div class="crow">
											<label>Card Number</label>
											<input type='text' placeholder="1234 5678 9012 3456" id="card_number" name="card_number">
										</div>
									</div>
									<div class="col m6 s12">
										<div class="crow">
											<label>Expiry Date</label>
											<div class="row">
												<div class="col m6 s6">
													<input type="text" placeholder="MM" class="minput" maxlength="5" id="expiry_month" name="expiry_month">
												</div>
												<div class="col m6 s6">
													<input type="text" placeholder="YYYY" class="yinput" maxlength="5" id="expiry_year" name="expiry_year">
													<input type="hidden" name="amount" value="<?=$amt?>" class="vip_plan_amount">
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="crow">
									<label>CVV/CVC</label>
									 <input type="text" placeholder="123" maxlength="3" id="cvv" name="cvv" class="cardcvv">
								</div>
							</div>
						
						</div>

						<!-- <input id="pay1" style="display: none;" type="image" onclick="buy_ads()" value="pay now" class="paypal-btn" src="<?=$baseUrl?>/images/blue-paypal-btn.png" border="0" name="submit" alt="Iaminjapan Credits" >
						paybycard.png -->
						
						<input type="image" name="card_submit" id="cardSubmitBtn" value="Pay by card" src="<?=$baseUrl?>/images/paybycard.png" class="paybycard">
						
					<?php ActiveForm::end() ?>
					</div>
					<?php if($callfrom != 'ads') { ?>
					<div id="payment-mobile" class="payment-content center-align">
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php if($callfrom == 'ads') { ?>
	<input type="hidden" id="calculated" name="calculated">
	<input type="hidden" id="benifit_amount" name="benifit_amount">
	<?php } ?>
	</div>
</div>
<?php 
exit ?>