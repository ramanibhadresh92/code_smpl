<?php
use frontend\assets\AppAsset;
use frontend\models\Verify;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');  
$isVerify = Verify::isVerify($user_id);
?>
<div class="content-box bshadow <?php if($isVerify){ ?>greenbox<?php } ?>" id="get_verified">					
    <div class="cbox-desc">
        <h6><img src="<?=$baseUrl?>/images/badge-icon.png"/>Get Verified</h6>
        <p>Verified members find more hosts.</p>
        <a href="<?php echo Yii::$app->urlManager->createUrl(['site/verifyme']); ?>">Learn More <i class="mdi mdi-menu-right"></i></a>
    </div>
</div>