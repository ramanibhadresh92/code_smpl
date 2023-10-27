<?php
namespace frontend\models;
use Yii;
use frontend\assets\AppAsset;
use frontend\models\Gallery;
use frontend\models\UserForm;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email'); 
$user_id =  (string)$session->get('user_id');
$gallery = Gallery::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
$html = '<span class="tagged_person_name userwall_tagged_users">Add people to tagged connections</span>';

if(!empty($gallery)) {
    $image = isset($gallery['image']) ? $gallery['image'] : ''; 
    $title = isset($gallery['title']) ? $gallery['title'] : ''; 
    $description = isset($gallery['description']) ? $gallery['description'] : ''; 
    $location = isset($gallery['location']) ? $gallery['location'] : ''; 
    $visible_to = isset($gallery['visible_to']) ? $gallery['visible_to'] : '';
    $tagged_connections = isset($gallery['tagged_connections']) ? $gallery['tagged_connections'] : ''; 
    $tagged_connections = explode(',', $tagged_connections);
    $tagged_connections = array_values(array_filter($tagged_connections));

    if(!empty($tagged_connections)) {
        $result = UserForm::getUserNames($tagged_connections);
        $result = json_decode($result, true);
        $namesString = '';

        if(!empty($result)) {
            $i = 1;
            foreach ($result as $key => $singleresult) {
                if($i>1) {
                    $namesString .= $singleresult.'<br/>';
                }
                $i++;
            }

            if (count($result) > 1) {
                $t = count($result) - 1;
                if($t>1) {
                    $t = $t . ' Others';
                } else {
                    $t = '1 Other';
                }

                $html = "<span class='tagged_person_name userwall_tagged_users'>&nbsp;".$result[0]."</span><span>&nbsp;and&nbsp;</span><span class='tagged_person_name userwall_tagged_users liveliketooltip' data-title='".$namesString."'>".$t."</span>";
            } else if (count($result) == 1) {
                $html = "<span class='tagged_person_name userwall_tagged_users'>".$result[0]."</span>";
            }
        }
    }
}
?>
<div class="modal_content_container" data-editid="<?=$id?>">
    <div class="modal_content_child modal-content">
        <div class="popup-title">
            <button class="hidden_close_span close_span waves-effect">
                <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
            </button>
            <h3>Edit photo details</h3>
            <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
            <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" data-editid="<?=$id?>" onclick="tempedit(this)">Done</a>
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
                                                            <label>Photo title</label>
                                                        </div>
                                                        <div class="detail-holder">
                                                            <div class="input-field">
                                                                <input type="text" placeholder="Photo title" class="upload-popupJIDS-phototitleedit fullwidth locinput title" value="<?=$title?>" />
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
                                                                <textarea id="Collection_tagline" class="upload-popupJIDS-descriptionedit materialize-textarea mb0 md_textarea item_tagline description" placeholder="Tell people about the photo"><?=$description?></textarea>
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
                                                                <input type="text" placeholder="Where was it taken?" class="upload-popupJIDS-locationedit fullwidth locinput location" data-query="all" onfocus="filderMapLocationModal(this)" id="createlocation" autocomplete="off" value="<?=$location?>"/>
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
                                                                <?=$html?>
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
										                           <span class="upload-popupJIDS-visibletoedit mdi-14px"><?=$visible_to?></span>
										                           <i class="zmdi zmdi-caret-down"></i>
										                        </a>
										                    </div>
														</div>
													</div>
												</div>
											</div>

                                            <div class="frow nomargin new-post add-photo-block addphoto_lbl">
                                                <div class="caption-holder addphotolabel">
                                                    <label>Add photos</label>
                                                </div>
                                                <div class="detail-holder">
                                                    <div class="input-field ">
                                                        <div class="post-photos new_pic_add">
                                                            <div class="img-row layered">
                                                                <div class="img-box">
                                                                    <div class="custom-file addimg-box add-photo ablum-add" style="background-image: url('<?=$image?>'), linear-gradient( rgba(0, 0, 0, 0.5)  , rgba(0, 0, 0, 0.5) )">
                                                                        <span class="icont">+</span>
                                                                        <br><span class="">Update photo</span>
                                                                        <div class="addimg-icon">
                                                                        </div>
                                                                        <input class="upload edit-gallery-file-upload" id="edit-gallery-file-upload" title="Choose a file to upload" type="file">
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
    <a href="javascript:void(0)" class="btngen-center-align waves-effect" data-editid="<?=$id?>" onclick="tempedit(this)">Update</a>
</div>
<?php 
exit;
