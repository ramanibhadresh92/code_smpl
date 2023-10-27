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
$this->title = 'Transfer Credits';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<style type="text/css">
	@font-face {
  font-family: 'MyWebFont';
  src:  url('myfont.woff2') format('woff2'),
        url('myfont.woff') format('woff');
}
</style>
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
										<?php /*  
										<a href="<?php echo Yii::$app->urlManager->createUrl(['site/addcredits']); ?>" class="waves-effect waves-light btn modal-trigger">Add Credit</a> */ ?>
										<a href="javascript:void(0)" data-callpayment="CREDITUBI003322" onclick="callPaymentPop(this);" class="waves-effect waves-light btn">Add Credit</a> 
										<div class="clear"></div>  
										<div class="dropdown dropdown-custom lmenu">
											<a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates='add_creadit_dropdownxx'>
								 				Tranfer Credits<i class="mdi mdi-chevron-down"></i>
											</a>
											<ul id="add_creadit_dropdownxx" class="dropdown-content">
												<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/credits']); ?>">Credits Benifits</a></li>
												<li class="active"><a href="<?php echo Yii::$app->urlManager->createUrl(['site/creditshistory']); ?>">See History</a></li>
												<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/transfercredits']); ?>">Tranfer Credits</a></li>
											</ul>
connections
										</div>										
									</div>
								</div>	

								<div class="credit-details text-center search-holder-connections main-sholder-connections cretranXHIL213">
				                    <h5>Send Iaminjapan Credit to your  and family member</h5>
				                    <div class="search-area">
				                        <div class="find-connect search-section-connections">
				                           <form class="mui-form--inline">
												<div class="mui-textfield">
													<input type="text" id="connect_name" class="addrole-name search-input-connections" placeholder="Search your connections">
													<!-- <div id="transfercreditsUI"> </div> -->
												</div>
												<button class="waves-effect waves-light btn"><i class="zmdi zmdi-search"></i></button>
												<div class="search-result search-connections-result">
													<div class="sresult-list nice-scroll">
														<ul id="transfercreditsUI"></ul>
													</div>
												</div> 
				                           </form>
				                        </div>
				                        <div class="credit-transfer">
				                           <form class="mui-form--inline">
				                              <div class="mui-textfield">
				                                 <input type="text" placeholder="Enter Credits" id="amount">
				                              </div>
				                              <button class="waves-effect waves-light btn sendbutton">
				                              	<a href="javascript:void(0)" onclick="transfer()">Send</a>
				                              </button>
				                           </form>
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


<div id="payment-popup" class="modal credit-payment-modal payment-popup fullpopup"></div>

<?php $this->endBody() ?> 

<script>
	var data1 = <?php echo json_encode($usrfrdlist); ?>;
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/transfercredits.js"></script>