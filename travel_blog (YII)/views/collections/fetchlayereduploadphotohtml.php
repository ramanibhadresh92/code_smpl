<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$email = $session->get('email'); 
$user_id =  (string)$session->get('user_id');
?>
<div class="modal_content_container">
    <div class="modal_content_child modal-content">
        <div class="popup-title">
            <button class="hidden_close_span close_span waves-effect">
                <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
            </button>
            <h3>Create collection</h3>
            <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
            <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="temp_ps()">Done</a>
        </div>

        <div class="custom_modal_content modal_content" id="createpopup">
            <div class="ablum-yours profile-tab">
                <div class="ablum-box detail-box">
                    <div class="content-holder main-holder">
                        <div class="summery">
                            <div class="dsection bborder expandable-holder expanded">
                                <div class="form-area expandable-area">
                                    
                                    <form class="ablum-form" id="layeredform">
                                        <div class="form-box">
                                            <div class="fulldiv">
                                                <div class="half">
                                                    <div class="frow phototitle_lbl">
                                                        <div class="caption-holder">
                                                            <label>Collection title</label>
                                                        </div>
                                                        <div class="detail-holder">
                                                            <div class="input-field">
                                                                <input type="text" placeholder="Photo title" class="upload-popupJIDS-phototitle fullwidth locinput " />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fulldiv w-100">
                                                <div class="half">
                                                    <div class="frow something_lbl">
                                                        <div class="caption-holder">
                                                            <label>Say something about it</label>
                                                        </div>
                                                        <div class="detail-holder">
                                                            <div class="input-field">
                                                                <textarea id="Collection_tagline" class="upload-popupJIDS-description materialize-textarea mb0 md_textarea item_tagline" placeholder="Tell people about the photo"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="fulldiv">
                                                <div class="half">
                                                    <div class="frow location_lbl">
                                                        <div class="caption-holder">
                                                            <label>Location</label>
                                                        </div>
                                                        <div class="detail-holder">
                                                            <div class="input-field">
                                                                <input type="text" placeholder="Where was it taken?" class="upload-popupJIDS-location fullwidth locinput" data-query="all" onfocus="filderMapLocationModal(this)" id="createlocation" autocomplete="off" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                    		<div class="fulldiv">
                                    			<div class="half">
                                    				<div class="frow tagged_frd_lbl">
                                    					<div class="caption-holder">
                                    						<label>Tagged connections</label>
                                    					</div>
                                    					<div class="detail-holder">
                                    						<div class="input-field userwall_tagged_usersblock">
                                                                <span class="tagged_person_name userwall_tagged_users">Add people to tagged connections</span>
                                    						</div>
                                    					</div>
                                    				</div>
                                    			</div>
                                    		</div>

                                    		<div class="fulldiv w-100 visibleto">
                                    			<div class="half">
                                    				<div class="frow visible_lbl">
                                    					<div class="caption-holder">
                                    						<label>Visible to</label>
                                    						<div class="right mt-5">
                                                                <a class="dropdown-button normalpostcreateprivacylabel" href="javascript:void(0)" onclick="privacymodal(this)" data-modeltag="normalpostcreateprivacylabel" data-label="normalpost" data-fetch="no">
                                    	                           <span class="upload-popupJIDS-visibleto mdi-14px"> Privacy </span>
                                    	                           <i class="zmdi zmdi-caret-down"></i>
                                    	                        </a>
                                    	                    </div>
                                    					</div>
                                    				</div>
                                    			</div>
                                    		</div>

                                            <div class="frow nomargin new-post addphoto_lbl">
                                                <div class="caption-holder addphotolabel">
                                                    <label>Add photos</label>
                                                </div>
                                                <div class="detail-holder">
                                                    <div class="input-field ">
                                                        <div class="post-photos new_pic_add">
                                                            <div class="img-row">
                                                                <div class="img-box">
                                                                    <div class="custom-file addimg-box add-photo ablum-add">
                                                                        <span class="icont">+</span>
                                                                        <br><span class="">Upload photo</span>
                                                                        <div class="addimg-icon">
                                                                        </div>
                                                                        <input class="upload custom-upload remove-custom-upload" title="Choose a file to upload" required="" data-class=".post-photos .img-row" type="file">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="valign-wrapper additem_modal_footer modal-footer">
    <span class="desktop_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
    <a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal waves-effect">Cancel</a>
    <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="temp_ps()">Upload</a>
</div>
<?php 
exit;
