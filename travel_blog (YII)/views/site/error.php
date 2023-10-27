<?php   
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$uid = (string)$session->get('user_id');
if($uid)
{
$this->title = 'Iaminjapan';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="page-wrapper ">
    <div class="header-section">
        <?php include('../views/layouts/header.php'); ?>
    </div>
    <div class="clear"></div>
    <?php include('../views/layouts/leftmenu.php'); ?>
    <div class="main-content text-center">
        <div class="feedback-box bshadow">
            <h5>Oops! Something went wrong.<br/><br/>Please check the URL as it may be broken, or the page may have been removed.</h5>
        </div>
    </div>
    <?php include('../views/layouts/footer.php'); ?>
</div>	

<?php $this->endBody(); }
else {
return $this->context->goHome();
} ?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>