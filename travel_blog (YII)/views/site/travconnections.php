<?php 
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use frontend\models\Connect;
use frontend\models\SecuritySetting;
use frontend\models\PostForm;
use frontend\models\MuteConnect;
use frontend\models\BlockConnect;
use frontend\models\Vip;
use frontend\models\Verify;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');
$posts = PostForm::getUserPost($user_id);
$pending_requests = Connect::connectPendingRequests();
$this->title = 'Search Results';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper ">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
	<div class="floating-icon">
		<div class="scrollup-btnbox anim-side btnbox scrollup-float">
			<div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>          
		</div>            
	</div>
	<div class="clear"></div>
	<div class="container page_container">
		<?php include('../views/layouts/leftmenu.php'); ?>
		<div class="fixed-layout">
			<div class="main-content with-lmenu sub-page peopleknow-page">
				<div class="combined-column">
					<div class="content-box bshadow">
						<div class="cbox-title">						
						   Search Result
						</div>
						<div class="cbox-desc">
							<div class="connections-grid freq">
								<div class="row">
									<input type="hidden" name="login_id" id="login_id" value="<?php echo $session->get('user_id');?>">
									<?php
									$this->context->getUserGridLayout2($connections, 'travconnections');
									?>	
								</div>
							</div> 
						</div>
					</div>
				</div>
				<input type="hidden" id="suggestconnectid" value="<?=$user_id?>">
				<div id="chatblock">
						<div class="float-chat anim-side">
							<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
							</div>
						</div>
					</div>
			</div>
		</div>
	</div>	
	<?php include('../views/layouts/footer.php'); ?>
</div>  

<div id="suggest-connections" class="modal tbpost_modal custom_modal split-page main_modal"></div>
<script>
var baseUrl ='<?php echo (string) $baseUrl; ?>';
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/connect.js"></script>
<?php $this->endBody() ?> 
