<?php   
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use frontend\models\LoginForm;
use frontend\models\Vip;
use backend\models\Googlekey;
 
$request = Yii::$app->request;
$suceess_message = $request->get('success');

$server_name = $_SERVER['SERVER_NAME']; 
if($server_name == "localhost"){
	$surl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/successvip";
	$furl = $server_name . "/iaminjapan-code/frontend/web/index.php?r=vip/failvip";	
} else {
	$surl = $server_name . "/frontend/web/index.php?r=vip/successvip";
	$furl = $server_name . "/frontend/web/index.php?r=vip/failvip";
}

$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$record = LoginForm::find()->where(['email' => $session->get('email')])->one();
$fname = $record['fname'];  
$lname = $record['lname'];  
$email = $record['email'];  
$city = $record['city'];  

$this->title = 'VIP';

$isvip = Vip::isVip($user_id);

if($isvip){
	$end_date = Vip::find()->select(['ended_date'])->where(['user_id' => (string)$user_id,'status' => '1'])->orderBy(['joined_date'=>SORT_DESC])->asarray()->one();
	 $vip_end_date = $end_date['ended_date'];
	if(is_numeric($vip_end_date) && $vip_end_date != 0)
	{
		$vip_end_date = date('d-m-Y',$vip_end_date);	
	}
	$vip_end_date = strtotime($vip_end_date);
	$day=date("d",$vip_end_date);
	$month = date("F",$vip_end_date);
	$year=date("Y",$vip_end_date);
}
else
{
	$vip_end_date = '0';
}

if(isset($vip_plans) && !empty($vip_plans))
{
	$amt = $vip_plans[1]['amount'] * $vip_plans[1]['months'];
	$vip_plan_id = $vip_plans[1]['_id'];
}
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper  hidemenu-wrapper full-wrapper white-wrapper noopened-search vippage show-sidebar">
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
	
		<div class="main-content with-lmenu sub-page vip-page main-page">			
			<div class="combined-column wide-open">
				<div class="content-box">			
					<div class="banner-section text-center">
						<div class="container">
							<h4>Become a VIP member today to unlock premium feature!</h4>						
							<p>The easiest way to get instant access to the privieges below is to upgrade to VIP membership</p> 
							<?php if(!($isvip)){?>
								<div class="vip-rate">
									<p>For only $9 monthly</p>
									
									<a href="javascript:void(0)" data-callpayment="VIPUBI003322" onclick="callPaymentPop(this);" class="waves-effect waves-light btn">Join VIP</a>
								</div>
							<?php } else { ?>
								<div class="already-member">
									<h6>You are a VIP member!</h6>
									<p>Your membership expires on <span><?=$month?>, <?=$day?> <?=$year?></span></p>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="container">
						<div class="cbox-desc">
							<div class="vip-content">
								<ul>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-layers zmdi-hc-fw"></i></div>
											<div class="descholder">
												<h6>Earn Points</h6>
												<p>"Earn 100 iaminjapan credit points"</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-tag-more zmdi-hc-fw" ></i></div>
											<div class="descholder">
												<h6>Discount on Ads</h6>
												<p>"get 20% discount on  ads or sponsored ads"</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-share zmdi-hc-fw" ></i></div>
											<div class="descholder">
												<h6>Share Plans</h6>
												<p>Automatically share your travel plans with the people who are traveling to the same destination</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-refresh-alt"></i></div>
											<div class="descholder">
												<h6>Automatic Updates</h6>
												<p>Get updates about people traveling to the same place as you or  people traveling to your hometown.</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-badge-check"></i></div>
											<div class="descholder">
												<h6>VIP and Verified Badge</h6>
												<p>VIP member photo will display verified badge,  verified people connect much faster</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-format-list-bulleted"></i></div>
											<div class="descholder">
												<h6>Get Listed  First</h6>
												<p>Your name and photo will be listed first on the many iaminjapan listing</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-mail-send"></i></div>
											<div class="descholder">
												<h6>Send Message</h6>
												<p>Send message to none  connect members Inbox</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-comment-text-alt"></i></div>
											<div class="descholder">
												<h6>Message Read</h6>
												<p>Check if message you sent have been read</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-eye"></i></div>
											<div class="descholder">
												<h6>Profile viewer</h6>
												<p>Find out who looks at your profile</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-case"></i></div>
											<div class="descholder">
												<h6>Business Listed First</h6>
												<p>Your business pages or collections will listed first</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info">
											<div class="iconholder"><i class="zmdi zmdi-headset"></i></div>
											<div class="descholder">
												<h6>Premium Support</h6>
												<p>Access VIP only chat and email support</p>
											</div>
										</div>
									</li>
									<li>
										<div class="vip-info onlycap">
											<div class="iconholder"><i class="zmdi zmdi-money"></i></div>
											<div class="descholder">
												<h6>For only $9 monthly</h6>
											</div>
										</div>
									</li>
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
	
<div id="payment-popup" class="modal credit-payment-modal payment-popup fullpopup vivecre_payment_popup"> </div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/joinvip.js"></script>

<?php $this->endBody() ?> 