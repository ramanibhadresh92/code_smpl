<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use frontend\models\Page;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$url = $_SERVER['HTTP_REFERER'];
$urls = explode('&',$url);
$url = explode('=',$urls[1]);
$page_id = $url[1];

$page_details = Page::Pagedetails($page_id);
    if(!empty($page_details)) {
        ?>
        <div class="settings-group editicon2">
            <div class="normal-mode">
               <div class="row"> 
                  <div class="col l12 m12 s12">
                     <div class="pull-right linkholder">
                        <a href="javascript:void(0)" class="editiconCircleEffect waves-effect waves-theme" onclick="open_edit_bp_blocking(this)"><i class="zmdi zmdi-edit mdi-22px"></i></a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
        <div class="settings-group">
            <div class="normal-mode">                                   
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Restricted List</label>
                    </div>
                    <div class="col l8 m7 s12">
                        People on this list cannot engage with the page
                    </div>
                    <div class="col l1 m2 s1 btn-holder editicon2">
                        <div class="pull-right linkholder">
                            <a href="javascript:void(0)" class="editiconCircleEffect waves-effect waves-theme" onclick="open_edit_bp_blocking(this)"><i class="zmdi zmdi-edit mdi-22px"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="settings-group">
            <div class="normal-mode">                                   
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Blocked List</label>
                    </div>
                    <div class="col l9 m9 s12">
                        People on this list cannot view the page
                    </div>
                </div>
            </div>
        </div>                          
        <div class="settings-group">
            <div class="normal-mode">                                   
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Messages filtering</label>
                    </div>
                    <div class="col l9 m9 s12">
                        People on this list will not be able to send the page any messages
                    </div>  
                </div>  
            </div>
        </div>
    <?php

    }
?>

<?php
exit;