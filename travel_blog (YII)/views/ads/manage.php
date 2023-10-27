<?php   
use yii\helpers\Url;
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 

$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');
$this->title = 'Manage Ads';
$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<link href="<?=$baseUrl?>/css/jquery-gauge.css" type="text/css" rel="stylesheet">	
<div class="page-wrapper hidemenu-wrapper adpage full-wrapper noopened-search loaded show-sidebar">
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
		<div class="main-content advert-page manageadvert-page">
			<div class="edit-travad"></div>
			<div class="combined-column wide-open main-page full-page">
				<div class="travadvert-banner">					
					<div class="overlay"></div>
					<div class="banner-section">
						<div class="container">
							<h4>Advertise on Iaminjapan</h4>
							<p>Create self service advert using our Advert Manager</p>							
						</div>
					</div>
				</div>
				<div class="manageadvert-content">
					<div class="container">
						<div class="fullwidth">
                     <div class="col l12">
   							<div class="left">
   								<h5 class="pad_algin">Account <span><?= $this->context->getuserdata($uid,'fullname');?></span></h5>
   							</div>
   							<div class="create-btn right">
   								<a href="<?php echo Url::to(['create']);?>" class="btn-custom"><i class="mdi mdi-plus"></i> Advert</a>
   							</div>
                     </div>
						</div>
					</div>
					<div class="manageadvert-details text-left">
						<div class="container">
							<div class="row">
								<div class="col l12 m12 width100">
									<h4 class="text-left adman_head">Your Advertisements</h4>
                           <div class="scroll_table">
   									<div class="table-responsive" id="ads-list"></div>
                           </div>
								</div> 
							</div>
						</div>
						<div class="sub-content adstats"></div>
					</div>
				</div>
			</div>		
		</div>
	</div>
	<?php include('../views/layouts/footer.php'); ?>
</div>

<div id="payment_popup" class="credit-payment-modal modal compose_inner_modal modalxii_level1 payment-popup fullpopup dis-none-popup">
</div>


<script>
var baseUrl = "<?php echo (string)$baseUrl; ?>";
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/jquery-gauge.min.js" type="text/javascript"></script>
<script src="<?=$baseUrl?>/js/jquery.cropit.js"></script>
<script src='<?=$baseUrl?>/js/wNumb.min.js'></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/advertisement.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/manage-ads.js"></script>
