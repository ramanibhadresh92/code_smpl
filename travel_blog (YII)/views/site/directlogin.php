<?php  
use frontend\assets\AppAsset; 
use yii\widgets\ActiveForm;
use frontend\models\LoginForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');
$email = $session->get('email');
?>
<?php include('../views/layouts/commonjs.php'); ?>
<script src="<?=$baseUrl?>/js/loginsignup.js" type="text/javascript"></script>
<script type="text/javascript">
	$(document).ready(function() {
		devlogin();
	});
</script>