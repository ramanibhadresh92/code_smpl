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
        if($page_details['gen_photos'] == 'allowPhotos') {
            $pagephotosstatus = 'Public can add photos to the page';
            $pagephotostatusvalue = 'checked';$pagephotodenyvalue = '';
        } else {
            $pagephotosstatus = 'Public can\'t add photos to the page';
            $pagephotostatusvalue = '';
            $pagephotodenyvalue = 'checked';
        }
 
        if($page_details['gen_post'] == 'allowPost') {
            $pagepoststatus = 'Public can add posts to the page';
            $pagepoststatusvalue = 'checked';
            $pagepostdenyvalue = '';
        } else {
            $pagepoststatus = 'Public can\'t add posts to the page';
            $pagepoststatusvalue = '';
            $pagepostdenyvalue = 'checked';
        }

        $pgfltr = isset($page_details['gen_page_filter']) ? $page_details['gen_page_filter'] : '';
        /*$pgfltr = explode(",", $pgfltr);
        $pgfltr = array_filter(array_values($pgfltr));*/

        if($pgfltr != '') {
            $pgfltrlabel = $pgfltr;
        } else {
            $pgfltrlabel = 'No words or phrases are blocked by the page';
        }

        ?>
        <div class="settings-group editicon2">
            <div class="normal-mode">
               <div class="row"> 
                  <div class="col l12 m12 s12">
                     <div class="pull-right linkholder">
                        <a href="javascript:void(0)" class="editiconCircleEffect waves-effect waves-theme" onclick="open_edit_bp_general(this)"><i class="zmdi zmdi-edit mdi-22px"></i></a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
        <div class="settings-group">
                <div class="normal-mode">                                   
                    <div class="row">
                        <div class="col l3 m3 s12">
                            <label>Page status</label>
                        </div>
                        <div class="col l9 m9 s12">
                            <?php 
                            if(isset($page_details['is_deleted']) && $page_details['is_deleted'] == '1') {
                                $page_publish = 'Page published';   
                            } else {
                                $page_publish = 'Page unpublished'; 
                            }
                            ?>
                            <span id="gen_publish"><?=$page_publish?></span>
                        </div>
                    </div>      
                </div>  
            </div>
            <div class="settings-group">
                <div class="normal-mode">                                   
                    <div class="row">
                        <div class="col l3 m3 s12">
                            <label>Page posts</label> 
                        </div>
                        <div class="col l9 m9 s12">
                            <span id="pagepost_value"><?=$pagepoststatus?></span>
                        </div>
                    </div>
                </div>
            </div>             
            <div class="settings-group">
                <div class="normal-mode">                                   
                    <div class="row">
                        <div class="col l3 m3 s12">
                            <label>Page photos</label>
                        </div>
                        <div class="col l9 m9 s12">
                            <span id="pagephotos_value"><?=$pagephotosstatus?></span>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="settings-group">
                <div class="normal-mode">                                   
                    <div class="row">
                        <div class="col l3 m3 s12">
                            <label>Page filteration</label>
                        </div>
                        <div class="col l9 m9 s12"> 
                            <?=$pgfltrlabel?>
                        </div>
                    </div>
                </div>
            </div>                                                  
            <div class="settings-group">
                <div class="normal-mode">                                   
                    <div class="row">
                        <div class="col l3 m3 s12">
                            <label>Download page</label>
                        </div>
                        <div class="col l9 m9 s12">
                            Download page
                        </div>
                    </div>
                </div>
            </div>
            <div class="settings-group">
                <div class="normal-mode">                                   
                    <div class="row">
                        <div class="col l3 m3 s12">
                            <label>Remove page</label>
                        </div>
                        <div class="col l9 m9 s12">
                            Delete your page
                        </div>
                    </div>
                </div>
            </div>
        <?php

    }
?>

<?php
exit;