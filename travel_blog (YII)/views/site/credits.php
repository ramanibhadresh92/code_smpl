<?php   
use frontend\assets\AppAsset;
use frontend\models\LoginForm;
use frontend\models\Credits;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');
$server_name = $_SERVER['SERVER_NAME']; 
if($server_name == "localhost"){
	$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/buycredits";
	$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/buycredits";
}
else{
	$surl = $server_name . "/frontend/web/index.php?r=vip/buycredits";
	$furl = $server_name . "/frontend/web/index.php?r=vip/buycredits";	
}

$totalcredits = Credits::usertotalcredits();
$total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
$total_len = strlen($total);
$total = str_split($total);

$record = LoginForm::find()->where(['email' => $session->get('email')])->one();
$fname = $record['fname'];  
$lname = $record['lname'];  
$email = $record['email'];  
$city = $record['city']; 

$this->title = 'Credits';

if(isset($credits_plans) && !empty($credits_plans))
{
	$amt = $credits_plans[1]['amount'];
	$credit_plan_id = $credits_plans[1]['_id'];
}

$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper  hidemenu-wrapper full-wrapper noopened-search creditpage show-sidebar">
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
		<div class="main-content sub-page credit-page main-page p-0">
			<div class="combined-column wide-open">
				<div class="content-box m-0">				
					<div class="cbox-desc">
						<div class="ribbon-section">
							<div class="ribbon-img"></div>
							<p>Iaminjapan credits are a virtual currency, <br />which can be used to buy various services on Iaminjapan</p>
						</div>
						<div class="container">
							<div class="credit-summery">
								<div class="credit-balance left">
									<h4>Your Iaminjapan Credit Balance</h4>
									<span class="badge credit_total">
										<?php
										if($total_len <= 1)
										{
											echo '<span>0</span>';
										}
										else if($total_len == 2)
										{
											echo '<span>0</span>';
										}	
										for($i = 0; $i< $total_len; $i++)
										{
											echo '<span>'.$total[$i].'</span>';
										}
									?>
									</span>
								</div>
								<div class="add-credit">
									<a href="javascript:void(0)" data-callpayment="CREDITUBI003322" onclick="callPaymentPop(this);" class="waves-effect waves-light btn">Add Credit</a> 
									<div class="clear"></div>
									<div class="dropdown dropdown-custom lmenu">
										<a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates='see_history_of_credits'>
											See History <i class="mdi mdi-chevron-down"></i>
										</a>
										<ul id='see_history_of_credits' class='dropdown-content'>
											<li class="active"><a href="<?php echo Yii::$app->urlManager->createUrl(['site/credits']); ?>">Credits Benifits</a></li>
											<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/creditshistory']); ?>">See History</a></li>
											<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/transfercredits']); ?>">Tranfer Credits</a></li>
										</ul>
									</div>
								</div>

							</div>
							<div class="credit-details">
								<div class="divider"></div>
							</div>
							<div class="credit-details">
								<h5>Credit Benifits</h5>
								<ul class="cbenifits-ul">										
									<li><span><i class="zmdi zmdi-check"></i></span>Boost your popularity</li>
									<li><span><i class="zmdi zmdi-check"></i></span>Send eGift for 100 credits</li>
									<li><span><i class="zmdi zmdi-check"></i></span>Transfer Credits to your connections</li>
									<li><span><i class="zmdi zmdi-check"></i></span>First in search list</li>
								</ul>
							</div>
							<div class="credit-details">
								<h5>Members can earn credits and increase their popularity by doing the following actions</h5>
								<ul class="cdetails-ul">
									<li><i class="zmdi zmdi-sign-in"></i>Member signup<span>Member initial signup, earns 20 credits</span></li>
									<li><i class="zmdi zmdi-plus-square"></i>Add to site<span>Create a collection, earns 3 credits</span></li>
									<li><i class="zmdi zmdi-camera-add"></i>Add Profile Photo<span>Add profile and cover photo, earns 10 credits</span></li>
									<li><i class="zmdi zmdi-copy"></i>Promote business<span>Create a business page, earn 5 credits</span></li>
									<li><i class="zmdi zmdi-account-add"></i>Connect invitation<span>Member accepts your invitation, earns 1 credit</span></li>
									<li><i class="zmdi zmdi-thumb-up"></i>Like<span>Like a page, earn 1 credit</span></li>
									<li><i class="zmdi zmdi-share"></i>Share an ad,<span>Share sponsored ad or page, earns 2 credits</span></li>
									<li><i class="zmdi zmdi-airplane"></i>Travel experience<span>Post your travel experience, earns 2 credits</span></li>
								</ul>
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


 
<div id="payment-popup" class="modal payment-popup fullpopup credit-payment-modal vivecre_payment_popup"></div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/credits.js"></script>
<?php $this->endBody() ?>  