<?php 

use frontend\assets\AppAsset;
use frontend\models\LoginForm;
use frontend\models\Verify;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;

$server_name = $_SERVER['SERVER_NAME']; 
if($server_name == "localhost"){
	$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=site/verifyme";
	$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=site/verifyme";
}
else{
	$surl = $server_name . "/frontend/web/index.php?r=site/verifyme";
	$furl = $server_name . "/frontend/web/index.php?r=site/verifyme";
}

$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$record = LoginForm::find()->where(['email' => $session->get('email')])->one();
$fname = $record['fname'];  
$lname = $record['lname'];  
$email = $record['email'];  
$city = $record['city'];  

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
	$verify_plan_id = $verify_plans[0]['_id'];
}
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper  hidemenu-wrapper full-wrapper white-wrapper noopened-search verifypage show-sidebar">
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
	
		<div class="main-content with-lmenu sub-page verify-page main-page">			
			<div class="combined-column wide-open">
				<div class="content-box">
					<div class="banner-section">
						<div class="container">
							<h4>Get verified and establish trust</h4>						
							<p>Verified people connect with travellers faster</p>
						</div>
					</div>
					<div class="container">
						<div class="cbox-desc">
							<div class="row">
								<div class="col l6 m12 s12">
									<div class="verify-content">
										<h5>Why Verify?</h5>
										<ul>
											<li>
												<div class="verify-info">
													<div class="iconholder"><img src="<?=$baseUrl?>/images/connect.png"/></div>
													<div class="descholder">
														<h6>Connect Faster</h6>
														<p>Verified members can connect with others at a faster rate</p>
													</div>
												</div>
											</li>
											<li>
												<div class="verify-info">
													<div class="iconholder"><img src="<?=$baseUrl?>/images/earnverified.png"/></div>
													<div class="descholder">
														<h6>Earn Verified check</h6>
														<p>Verified will be noticed, travelers look to see if you are verified</p>
													</div>
												</div>
											</li>
											<li>
												<div class="verify-info">
													<div class="iconholder"><img src="<?=$baseUrl?>/images/verifieduser.png"/></div>
													<div class="descholder">
														<p class="darktext">"while I was considering travel buddy, verification was the first thing I looked at."</p>
														<a href="javascript:void(0)">- Carolina Brain</a>
													</div>
												</div>
											</li>
										</ul>
									</div>
								</div>
								<div class="col l6 m12 s12">
									<div class="gray-section">
										<?php if($isverify){ ?>
										<h4 class="greentext">You are now a Verified user</h4>
										<p>Just $10 a year earns you trust with iaminjapan travellers</p>
										<img src="<?=$baseUrl?>/images/verify-icon-green.png"/>
										<?php if(isset($ended_date) && !empty($ended_date)) { ?> 
										<p>Your verify membership will expire on <?=$month?>, <?=$day?> <?=$year?></p>
									
										<?php } ?>
										<?php } else { ?>
										<h4>Verify with Iaminjapan Now</h4>
										<p>Just $10 a year earns you trust with iaminjapan travellers</p>
										<img src="<?=$baseUrl?>/images/verify-icon.png"/>
										<a href="javascript:void(0)" data-callpayment="VERIFYUBI003322" onclick="callPaymentPop(this);" class="waves-effect waves-light btn">Get Verified</a>
										<?php } ?>
										
									</div>							
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

<div id="payment-popup" class="modal credit-payment-modal payment-popup fullpopup vivecre_payment_popup"></div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/verify.js"></script>