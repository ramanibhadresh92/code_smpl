<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
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
            $pagephotostatusvalue = 'checked';
            $pagephotodenyvalue = '';
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

        if($page_details['gen_post_review'] == 'on') {
            $pagepostrevvalue = 'checked';
        } else {
            $pagepostrevvalue = '';
        }

        if($page_details['gen_photos_review'] == 'on') {
            $pagepostrevvalue = 'checked';
        } else {
            $pagepostrevvalue = '';
        } 

        if($page_details['gen_reviews'] == 'on') { 
            $pagegen = 'on';
            $pageche = 'checked';
        } else {
            $pagegen = 'off';
            $pageche = '';
        }

        ?>
        
        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12"> 
                        <label>Page status</label>
                    </div>
                    <div class="col l7 m7 s12">
                        <select class="select2" id="page_pub_set_val">
                        <option value="publish" <?php if($page_details['is_deleted'] == '1'){?>selected=""<?php } ?>>Publish</option>
                        <option value="unpublish" <?php if($page_details['is_deleted'] != '1'){?>selected=""<?php } ?>>Unpublish</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Page posts</label>
                    </div>
                    <div class="col l9 m9 s12">
                        <div class="radio-options">
                            <div class="radio-holder">
                                <label class="control control--radio">Allow anyone to add posts to the page
                                  <input type="radio" name="radioPosts" <?=$pagepoststatusvalue?> value="allowPost"/>
                                  <div class="control__indicator"></div>
                                </label>
                            </div>
                            <div class="entertosend leftbox">
                                <input type="checkbox" name="reviewPost" id="reviewPost" <?=$pagepostrevvalue?>>
                                <label for="reviewPost" class="speciallabelchk">Review post by public before they are published to the page</label>
                            </div>
                            <div class="radio-holder">
                                <label class="control control--radio">Disable posts by public to the page
                                <input type="radio" name="radioPosts" <?=$pagepostdenyvalue?> value="denyPost"/>
                                  <div class="control__indicator"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>                                                                                                         
        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Page photos</label>
                    </div>
                    <div class="col l9 m9 s12">
                        <div class="radio-options">
                            <div class="radio-holder">
                                <label class="control control--radio">Allow anyone to add photos to the page
                                  <input type="radio" name="radioPhotos" <?=$pagephotostatusvalue?> value="allowPhotos"/>
                                  <div class="control__indicator"></div>
                                </label>
                            </div>      
                        <div class="entertosend leftbox">
                                <input type="checkbox" id="reviewPhotos" <?=$pagepostrevvalue?>>
                                <label for="reviewPhotos" class="speciallabelchk">Review post by public before they are published to the page</label>
                            </div>
                            <div class="radio-holder">
                                <label class="control control--radio">Disable photos by public to the page
                                <input type="radio" name="radioPhotos" <?=$pagephotodenyvalue?> value="denyPhotos"/>
                                  <div class="control__indicator"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="settings-group reviews-settings-group">
            <div class="row">
                <div class="col l3 m3 s12">
                    <label>Reviews</label>
                </div>
                <div class="col l7 m7 s12">
                    Reviews are turned <span id="revstatus"><?=$pagegen?></span>
                </div>
                <div class="col l2 m2 s12 btn-holder">
                    <div class="pull-right linkholder"> 
                        <div class="switch">
                            <label>
                            <input id="rwbtn_switch" class="cmn-toggle cmn-toggle-round" type="checkbox" <?=$pageche?>>
                            <span class="lever"></span>
                            </label>
                        </div>  
                        <input type="hidden" id="review_switch_value" value="<?=$pagegen?>" />
                    </div>
                </div>
            </div>
        </div>                                  
        
        <div class="settings-group page-filteration-settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Page filteration</label>
                    </div>
                    <div class="col l7 m7 s12">
                        <div class="sliding-middle-custom anim-area underlined fullwidth">
                            <div class="sliding-middle-custom anim-area underlined fullwidth">
                            <input type="text" placeholder="Add words to block" id="pgfltr" value="<?=$page_details['gen_page_filter']?>"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>                                                  
        <div class="settings-group download-page-settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <span><label>Download page</label></span>
                    </div>
                    <div class="col l7 m7 s12">
                        <span><b>Download a copy of your page photo</b></span><br/>
                        <p class="downloadp">Get a copy of your page photos.</p>
                    </div>
                    <div class="col l2 m2 s12">
                        <div class="pull-right settings-btn">       
                            <a class="btngen-center-align waves-effect" tabindex="1" onclick="download(this)">Download</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l3 m3 s12">
                        <label>Remove page</label>
                    </div>
                    <div class="col l9 m9 s12">
                        Deleting your page will remove all your posts, photos, events and other information of your page permenantly. There is no way to recover the page again.
                    </div>
                    <div class="col l12 m12 s12">
                        <div class="pull-right settings-btn">       
                            <a class="btngen-center-align waves-effect" tabindex="1" onclick="pagedelete('<?=$page_id?>')">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-group">
            <div class="edit-mode">
                <div class="row">
                    <div class="col l12 m12 s12">
                        <div class="pull-right settings-btn">                                   
                           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_bp_general_cl(this, false)">Cancel</a>                                    
                           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_bp_general_cl(this, true)">Save</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
exit;