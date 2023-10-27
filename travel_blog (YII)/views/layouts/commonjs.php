<?php
use frontend\assets\AppAsset; 
use frontend\models\UserSetting;
$asset = frontend\assets\AppAsset::register($this);
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<!-- START comman JS needed for all pages-->
<script type="text/javascript" src="<?=$baseUrl?>/js/modal.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/croppie.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/custom-functions.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/custom-handler.js"></script>     
<script type="text/javascript" src="<?=$baseUrl?>/js/tooltipster.bundle.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/jquery.nicescroll.min.js" charset="utf-8"></script>
<script type="text/javascript" src='https://www.google.com/recaptcha/api.js'></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/custom_user_modal.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/jquery.justifiedGallery.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/picturefill.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/lightgallery-all.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/custom_light_justify.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/chat.js"></script>
<script type="text/javascript" src='<?=$baseUrl?>/js/wNumb.min.js'></script> 
<script type="text/javascript" src="<?=$baseUrl?>/js/emoticons.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/custom-emotions.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/emostickers.js"></script> 
<script type="text/javascript" src="<?=$baseUrl?>/js/custom-emostickers.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/messages-function.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/messages-handler.js"></script> 
<script type="text/javascript" src="<?=$baseUrl?>/js/socket.io.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/datepicker.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/moment.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/daterangepicker.min.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/owl.carousel.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/nouislider.js"></script>

<!-- <script type="text/javascript" src="<?=$baseUrl?>/js/recaptcha.js"></script> -->
<!-- END comman Js needed for all pages -->
 