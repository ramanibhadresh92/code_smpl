<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\models\Page;
use frontend\models\SecuritySetting;

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
        $blk_restrct_list_array = array();
        $blk_restrct_list = isset($page_details['blk_restrct_list']) ? $page_details['blk_restrct_list'] : '';
        if($blk_restrct_list != '') {
            $blk_restrct_list_array = explode(",", $blk_restrct_list);
        }
        $blk_restrct_list_array = array_values(array_filter($blk_restrct_list_array)); 
        $blk_restrct_list_arraylabel = SecuritySetting::getFullnamesWithToolTip($blk_restrct_list_array, 'page_restricted_list');


        $blk_block_list_array = array();
        $blk_block_list = isset($page_details['blk_block_list']) ? $page_details['blk_block_list'] : '';
        if($blk_block_list != '') {
            $blk_block_list_array = explode(",", $blk_block_list);
        }
        $blk_block_list_array = array_values(array_filter($blk_block_list_array)); 
        $blk_block_list_arraylabel = SecuritySetting::getFullnamesWithToolTip($blk_block_list_array, 'page_block_list');


        $blk_msg_filtering_array = array();
        $blk_msg_filtering = isset($page_details['blk_msg_filtering']) ? $page_details['blk_msg_filtering'] : '';
        if($blk_msg_filtering != '') {
            $blk_msg_filtering_array = explode(",", $blk_msg_filtering);
        }
        $blk_msg_filtering_array = array_values(array_filter($blk_msg_filtering_array)); 
        $blk_msg_filtering_arraylabel = SecuritySetting::getFullnamesWithToolTip($blk_msg_filtering_array, 'page_block_message_list');
        ?>
        
        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Restricted List</label>
                    </div>
                    <div class="col s12 m7 l8 htmlblockput page_restricted_list" id="page_restricted_list">
                        <?=$blk_restrct_list_arraylabel?>
                    </div> 
                </div>
            </div>
        </div>
        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Blocked List</label>
                    </div>
                    <div class="col l9 m9 s12 htmlblockput page_block_list" id="page_block_list">
                        <?=$blk_block_list_arraylabel?>
                    </div>
                </div>
            </div>
        </div>                          
        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">                  
                    <div class="col l3 m3 s12">
                        <label>Messages filtering</label>
                    </div>
                    <div class="col l9 m9 s12 htmlblockput page_block_message_list" id="page_block_message_list">
                        <?=$blk_msg_filtering_arraylabel?>
                    </div>
                </div>
            </div>
        </div> 

        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l12 m12 s12">
                        <div class="pull-right settings-btn">                                   
                           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_bp_blocking_cl(this, false)">Cancel</a>                                    
                           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_bp_blocking_cl(this, true)">Save</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>          
        <?php
    }
exit;