<?php
namespace frontend\models;
use Yii;
use frontend\assets\AppAsset;
use frontend\models\Blog;
use frontend\models\UserForm;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email'); 
$user_id =  (string)$session->get('user_id');
$blog = Blog::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
$image = '';
if(!empty($blog)) {
    $image = isset($blog['image']) ? $blog['image'] : ''; 
    $title = isset($blog['title']) ? $blog['title'] : ''; 
    $description = isset($blog['description']) ? $blog['description'] : ''; 
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
            <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" data-editid="<?=$id?>" onclick="editblog(this)">Done</a>
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

                                            <div class="frow nomargin new-post add-photo-block addphoto_lbl">
                                                <div class="caption-holder addphotolabel">
                                                    <label>Add photos</label>
                                                </div>
                                                <div class="detail-holder">
                                                    <div class="input-field ">
                                                        <div class="post-photos new_pic_add"> 
                                                            <div class="img-row layered">
                                                                <div class="img-box">
                                                                    <img src="<?=$image?>" class="thumb-image vimg">
                                                                    <div class="loader" style="display: none;"></div>
                                                                    <a href="javascript:void(0)" onclick="removePhotoFileblog('<?=$id?>', this)" class="removePhotoFileblog">
                                                                        <i class="mdi mdi-close"></i>
                                                                    </a>
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
    <a href="javascript:void(0)" class="btngen-center-align waves-effect" data-editid="<?=$id?>" onclick="editblog(this)">Update</a>
</div>
<?php 
exit;
