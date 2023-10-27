<?php
use frontend\assets\AppAsset; 
use yii\widgets\ActiveForm;
use frontend\models\LoginForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');
$email = $session->get('email');
?>
<link href="<?=$baseUrl?>/css/custom-croppie.css" rel="stylesheet">
<div class="popup-title">          
	<a class="close_span waves-effect" href="javascript:void(0)">
		<i class="mdi mdi-close mdi-20px"></i>
	</a>
</div> 
<div class="modal-content">
	<div class="home-content">					
		<div class="container">
			<div class="homebox login-box animated wow zoomIn" data-wow-duration="1200ms" data-wow-delay="500ms">
                <div class="sociallink-area">
                	<a  id="FacebookBtn" href="javascript:void(0)" class="fb-btn white-text"><span><i class="mdi mdi-facebook"></i></span>Connect with Facebook</a>
                </div>
                <div class="sociallink-area">                   
                   <a id="GoogleBtn" href="javascript:void(0)" class="fb-btn google-connect white-text"><span><i class="mdi mdi-google"></i></span>Connect with Google</a>
                </div>
                <div class="sociallink-area">                   
                   <a href="javascript:void(0)" class="fb-btn instagram-connect white-text"><span><i class="mdi mdi-instagram"></i></span>Connect with Instagram</a>
                </div>
            </div>
		</div>
	</div>
</div>
<script type="text/javascript" src="<?=$baseUrl?>/js/loginsignup.js"></script>
<?php 
exit;