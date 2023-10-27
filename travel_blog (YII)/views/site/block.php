<?php   
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\LoginForm;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

if(isset($_GET['blockid']) && !empty($_GET['blockid']))
{
    $blockbyid = (string)$_GET['blockid'];
    $blockbyuserdetails = LoginForm::find()->where(['_id' => $blockbyid])->one();
    if($blockbyuserdetails)
    {
        $bltitle = 'Blocked by '.$blockbyuserdetails['fullname'];
        $blc = $blockbyuserdetails['fullname']."'s";
        $bllast = 'by '.$blockbyuserdetails['fullname'];
    }
    else
    {
        $bltitle = 'Block';
        $blc = 'user';
        $bllast = '';
    }
}
$this->title = 'Block';
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
	<?php include('../views/layouts/leftmenu.php'); ?>
	
	<div class="main-content with-lmenu">
		<div class="combined-column">
			<div class="content-box bshadow">
				<?php if(isset($_GET['blockid']) && !empty($_GET['blockid'])){ ?>
				<div class="cbox-title">						
					<?=$bltitle?>
				</div>
				<div class="cbox-desc">
					<div class="connections-grid">
						<div class="row">
							<div class="no-listcontent">
								You are not allowed to see the <?=$blc?> wall as you are blocked<?=$bllast?>.
							</div>
						</div>
					</div>
				</div>
				<?php } else { ?>
				<div class="cbox-title">						
					Block
				</div>
				<div class="cbox-desc">
					<div class="connections-grid">
						<div class="row">
							<?php $this->context->getnolistfound('notallowtoviewwall'); ?>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<div id="chatblock">
						<div class="float-chat anim-side">
							<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
							</div>
						</div>
					</div>
	</div>
	<?php include('../views/layouts/footer.php'); ?>
</div>  
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<?php $this->endBody() ?> 