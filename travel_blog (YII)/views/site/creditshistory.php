<?php   
use frontend\assets\AppAsset;
use frontend\models\Credits;
use frontend\models\Page;
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
$this->title = 'Credit History';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper  hidemenu-wrapper full-wrapper white-wrapper noopened-search creditpage show-sidebar">
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
					<div class="cbox-desc nobg">
						<div class="ribbon-section">
							<div class="ribbon-img"></div>
							<p>Iaminjapan credits are virtual currency, <br />which can be used to buy extended services on Iaminjapan</p>
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
									<?php /*
									<a href="<?php echo Yii::$app->urlManager->createUrl(['site/credits']); ?>" class="btn green-styled-btn">Add Credit</a>
									*/ ?>										
									<div class="clear"></div>
									<div class="dropdown dropdown-custom lmenu">
										<a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates='credits_update_dropdown'>
											See History<i class="mdi mdi-chevron-down"></i>
										</a>
										<ul id="credits_update_dropdown" class="dropdown-content">
											<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/credits']); ?>">Credits Benifits</a></li>
											<li class="active"><a href="<?php echo Yii::$app->urlManager->createUrl(['site/creditshistory']); ?>">See History</a></li>
											<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/transfercredits']); ?>">Tranfer Credits</a></li>
										</ul>
									</div>
								</div>
							</div>
							<div class="credit-updates">
								<div class="section-title">
									<h5>You have <span><?=count($userallcredits);?></span> credits updates</h5>
									<a href="javascript:void(0)"><i class="mdi mdi-email"></i> ignore all</a>
								</div>
								<ul class="cupdates-ul">
								<?php 
									$lok = 1;
									
									foreach($userallcredits as $userallcredit) {

									if(isset($userallcredit['joined_date']->sec)) {
										$joined_date = $userallcredit['joined_date']->sec;
									} else {
										$joined_date = (string)$userallcredit['joined_date'];
									}
									
									$time = Yii::$app->EphocTime->time_elapsed_A(time(), $joined_date);
									$lok++;

									if($userallcredit['credits_desc']=='purchasecredits')
									{
										$status = 'bought';
										$title = 'Purchased';
										$class = 'credit-buy';
									}
									else if($userallcredit['credits_desc']=='usedcredits' || $userallcredit['credits_desc']=='transfercredits')
									{
										$status = 'used';
										$title = 'Used';
										$class = 'credit-used';
										$userallcredit['credits'] = str_replace('-','',$userallcredit['credits']);
									}
									else if($userallcredit['credits_desc']=='signup')
									{
										$status = 'earned';
										$title = 'Eared';
										$class = 'credit-compli';
									}
									else
									{
										$status = 'earned';
										$title = 'Great';
										$class = 'credit-compli';
									}
									if($userallcredit['credits_desc']=='purchasecredits')
									{
										$desc = 'You have purchased '.$userallcredit['credits'].' credits';
									}
									else if($userallcredit['credits_desc']=='usedcredits')
									{
										$desc = 'You have used '.$userallcredit['credits'].' credits';
									}
									else if($userallcredit['credits_desc']=='pinimage')
									{
										$pin_user_name = explode("::",$userallcredit['detail']);
										$pin_user_name = $pin_user_name[0];
										$pin_user_name = $this->context->getuserdata($pin_user_name,'fullname');
										$desc = 'Pinning the image by '.$pin_user_name;
									}
									else if($userallcredit['credits_desc']=='signup')
									{
										$desc = 'By becoming a new member in iaminjapan';
									}
									else if($userallcredit['credits_desc']=='profilephoto')
									{
										$desc = 'For adding the profile photo';
									}
									else if($userallcredit['credits_desc']=='addconnect')
									{
										$connect_name = $this->context->getuserdata($userallcredit['detail'],'fullname');
										$desc = 'Become a connect with '.$connect_name;
									}
									else if($userallcredit['credits_desc']=='pagelike')
									{
										$page_details = Page::Pagedetails($userallcredit['detail']);
										$desc = 'Liking the business page '.$page_details['page_name'];
									}
									else if($userallcredit['credits_desc']=='join_vip')
									{
										$desc = 'On becoming VIP member';
									}
									else if($userallcredit['credits_desc']=='sharepost')
									{
										$connect_name = $this->context->getuserdata($userallcredit['detail'],'fullname');
										$desc = 'Sharing your post by '.$connect_name;
									}
									else if($userallcredit['credits_desc']=='newtheme')
									{
										$desc = 'By applying new theme';
									}
									else if($userallcredit['credits_desc']=='addcollection')
									{
										$desc = 'By creating new collection';
									}
									else if($userallcredit['credits_desc']=='transfercredits')
									{
										//$connect_name = $this->context->getuserdata($userallcredit['detail'],'fullname');
										$desc = 'By transferring credits to ' . $userallcredit['detail'];
									}
									else if($userallcredit['credits_desc']=='addpage')
									{
										$desc = 'By creating new page';
									}
									else
									{
										//$desc = 'Earned';
										$desc = '';
									}
									?>
									<li>
										<div class="cupdates-box">
											<div class="imgholder"><img src="<?=$baseUrl?>/images/credit-img.png"/></div>
											<div class="descholder">
												<div class="desc">
													<h5 class="<?=$class?>"><?=$title?></h5>
													<p>You <?=$status?> <?=$userallcredit['credits']?> credit<?php if($userallcredit['credits']>1){?>s<?php } ?> <?=$desc?></p>
												</div>
												<div class="timestamp">
													<?=$time?>
													<i class="mdi mdi-email"></i>
												</div>
											</div>
										</div>
									</li>
									<?php 
									}

									if(count($userallcredits) == 0) {
									 	$this->context->getnolistfound('nocredithistory');
									} ?>
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

<div id="payment-popup" class="modal credit-payment-modal payment-popup fullpopup vivecre_payment_popup"></div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<?php $this->endBody() ?> 