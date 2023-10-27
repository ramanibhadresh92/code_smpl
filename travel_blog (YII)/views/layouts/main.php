<?php
use frontend\assets\AppAsset; 
use frontend\models\UserSetting;

$asset = frontend\assets\AppAsset::register($this);
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$thumb = $session->get('thumb');
$fullname = $session->get('fullname');
$_SESSION['loadedAds'] = array();
$theme = 'theme-color';

$cuser_email = $this->context->getuserdata($user_id,'email');
$cuser_fullname = $this->context->getuserdata($user_id,'fullname');
$cuser_thumb = $this->context->getimage($user_id, 'thumb');
$cuser_country = $this->context->getuserdata($user_id,'country');
$userinfo = array('id' => (string)$user_id, 'email'=> $cuser_email, 'fullname' => $cuser_fullname, 'thumb' => $cuser_thumb, 'country' => $cuser_country);
$controllerID = Yii::$app->controller->id;
$controllerActionID = Yii::$app->controller->action->id;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<meta name='B-verify' content='f6ff550e35a415473235fe823b67cd1a97675712' />
    <link rel="icon" href="<?=$baseUrl?>/images/favicon.ico">
    <title>I am in Japan</title>	
	<link href="<?=$baseUrl?>/css/materialize.css" rel="stylesheet">
    <link href="<?=$baseUrl?>/css/material-design-iconic-font.css" rel="stylesheet">    
    <link href="<?=$baseUrl?>/css/materialdesignicons.min.css" rel="stylesheet">    
    <link href="<?=$baseUrl?>/css/tooltipster.bundle.min.css" rel="stylesheet">
    <link href="<?=$baseUrl?>/css/tooltipster-sideTip-borderless.min.css" rel="stylesheet">
    <!-- <link href="<?=$baseUrl?>/css/font-awesome.css" rel="stylesheet"> -->
    <link href="<?=$baseUrl?>/css/template.css" rel="stylesheet">
    <link href="<?=$baseUrl?>/css/themes.css" rel="stylesheet">  
    <link href="<?=$baseUrl?>/css/all-ie-only.css" rel="stylesheet" type="text/css" media="screen"/> 
    <link href="<?=$baseUrl?>/css/master-responsive.css" rel="stylesheet">
    
    <script src="<?=$baseUrl?>/js/jquery.min.js"></script> 
    <script src="<?=$baseUrl?>/js/jquery-ui.js"></script>
    <script src="<?=$baseUrl?>/js/materialize.js" type="text/javascript" charset="utf-8" ></script>    

    <link href="<?=$baseUrl?>/css/justifiedGallery.css" rel="stylesheet" >
    <link href="<?=$baseUrl?>/css/lightgallery.css" rel="stylesheet">
    <link href="<?=$baseUrl?>/css/lg-transitions.css" rel="stylesheet">
    <link href="<?=$baseUrl?>/css/demo-cover.css" type="text/css" media="screen" rel="stylesheet" />
    <link href="<?=$baseUrl?>/css/datepicker.min.css" type="text/css" media="screen" rel="stylesheet" />
    <link href="<?=$baseUrl?>/css/daterangepicker.css" type="text/css" media="screen" rel="stylesheet" />
    <link href="<?=$baseUrl?>/css/owl.carousel.css" type="text/css" media="screen" rel="stylesheet" />
    <link href="<?=$baseUrl?>/css/nouislider.css" type="text/css" media="screen" rel="stylesheet" />

    <?php $this->head() ?>
    </head>
    
    <body class="<?=$theme?>">
        <!-- IE lower version notice -->
        <div class="ienotice">
            <div class="notice-holder">
                <h5>Iaminjapan can be best viewed in IE 9 or greater</h5>
                <br />
                <a href="https://www.microsoft.com/en-in/download/internet-explorer.aspx">Update your browser here</a>
            </div>
        </div>
        <!-- end IE lower version notice -->

        <script type="text/javascript">
        var thumb = '<?=isset($thumb) ? $thumb : '';?>';
        var fullname = '<?=isset($fullname) ? $fullname : '';?>';
        var postNumber = 1;
        var currentUserScoketId = '';
        var $getrecentmessageusersid = [];
        var addUserForAccountSettingsArray = [];
        var addUserForAccountSettingsArrayTemp = [];
        
        var customArray = [];
        var customArrayTemp = [];
        
        var $isMapLocationId = '';
        var socket = '';
        var user_id = '<?=$user_id?>';
        var $baseUrl = '<?=$baseUrl?>';
        var $assetsPath = '../../vendor/bower/travel';
        </script>

        <?php $this->beginBody() ?>
            <?=$content?>
        <?php $this->endBody() ?>
        <script type="text/javascript">
            var data2 = <?php echo (isset($userinfo) && !empty($userinfo)) ? json_encode($userinfo) : '';?>;
            if(data2) {
                if (window.location.href.indexOf("localhost") > -1) {
                    var socket = io('http://localhost:4000'); ////////// LOCAL
                } else {
                    var socket = io('https://www.iaminjapan.com:4000'); ////////// LIVE
                } 
                socket.emit('userInfo', data2);  
            } 
        </script>
        
        <?php if($controllerActionID != 'site/messages') { 
            include('../views/layouts/leftsearchbar.php');
        } ?>

        <div id="login_modal" class="modal login-popup home-page popup-area login_modal_general">
            <center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
        </div>

        <!-- open" style="z-index: 1019;display: block;opacity: 1;transform: scaleX(1) translateY(-50%);top: 50%;width: 12%;background: black;" -->
        <div id="receiveCallBox" class="modal">
            <div id="callReceived" style="width: 12%; float: left; margin: 29px 35px; cursor: pointer; hover: red;">
                <i class="zmdi zmdi-phone" style="color: green;"></i>
            </div>
            <div id="callRejected" style="width: 12%; float: left; margin: 30px 16px; cursor: pointer; ">
                <i class="mdi mdi-close	-circle" style="color: red;"></i>
            </div>
        </div>

        <div id="Complete_loged" class="modal custom_modal complete-popup complete-loged">
                <div class="modal_content_container">
                    <div class="modal_content_child modal-content">
                        <div class="custom_modal_content modal_content" id="createpopup">
                            <div class="comp_popup profile-tab">
                                <div class="comp_popup_box detail-box">
                                    <div class="content-holder main-holder">
                                        <div class="summery">
                                            <div class="complete-profile-page">
                                                <div class="signup-part">
                                                    <div class="complete-profile-header" id="wlcmsrnbx">
                                                    </div>
                                                    <div class="box-content">
                                                        <center><div class="lds-css ng-scope"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        
        <div id="custom_dropdown_modal" class="modal tbpost_modal custom_dropdown_modal"></div>
        
        <div id="datepickerDropdown" class="modal tbpost_modal modal-datepicker modalxii_level1 nice-scroll">
            <div class="content_header">
                <button class="close_span waves-effect">
                <i class="mdi mdi-close mdi-20px material_close resetdatepicker"></i>
                </button>
                <p class="selected_photo_text">Select Date</p>
                <a href="javascript:void(0)" class="done_btn action_btn closedatepicker">Done</a>
            </div>
            <div class="modal-content">
              <div id="datepickerBlock"></div>
            </div>
        </div>   
        
        <!--attachment modal-->
        <div id="compose_visitcountry" class="modal compose_inner_modal modalxii_level1 visit-country">
            <?php include('../views/layouts/compose_visitcountry.php'); ?>
        </div>

        <div id="compose_iwasincountry" class="modal compose_inner_modal modalxii_level1 visit-country">
            <?php include('../views/layouts/compose_iwasincountry.php'); ?>
        </div>

        <div id="privacymodal" class="modal compose_inner_modal modalxii_level1"></div>
        <?php include('../views/layouts/discardmodal.php'); ?>
        <?php include('../views/layouts/reportmodal.php'); ?>
    </body>
</html>