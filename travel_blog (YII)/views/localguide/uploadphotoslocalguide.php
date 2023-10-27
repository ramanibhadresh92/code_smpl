<?php   
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\assets\AppAsset;
use backend\models\LocalguideActivity;
use frontend\models\Language;
use frontend\models\LocalguidePost;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');

$baseUrl = AppAsset::register($this)->baseUrl;
$languages = Language::languages();
$post = LocalguidePost::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

if(isset($post) && !empty($post)) {
	$id = (string)$post['_id'];
	$activity = isset($post['activity']) ? $post['activity'] : '';
	$description = $post['description'];
	$credentials = $post['credentials'];
	$restriction = $post['restriction'];
	$language = $post['language'];
	$guideFee = $post['guideFee'];
	$images = $post['images'];
	$images = explode(',', $images);
	$licensed = isset($post['licensed']) ? $post['licensed'] : 'no';
	$staticFeesArray = array('' => 'Negotiable', '5' => '$5 per hour', '10' => '$10 per hour', '15' => '$15 per hour', '20' => '$20 per hour', '25' => '$25 per hour', '30' => '$30 per hour', '35' => '$35 per hour');
?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Upload guide photos</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="uploadphotoslocalguidesave('<?=$id?>')">Done</a>
     </div>
     <div class="custom_modal_content modal_content" id="createpopup">
        <div class="ablum-yours profile-tab">
           <div class="ablum-box detail-box">
              <div class="content-holder main-holder">
                 <div class="summery">
                    <div class="dsection bborder expandable-holder expanded">
                       <div class="form-area expandable-area">
                          <form class="ablum-form">
                             <div class="form-box">
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s12">
                                         <div class="frow">
                                            <div class="caption-holder">
                                               <label>Activities that I can be hired for*</label>
                                            </div>
                                            <div class="detail-holder custom-hireaguide dropdown782">
                                              <span><?=$activity?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder  mb-5">
                                         <label>Are you licensed guide</label>
                                      </div>
                                      <div class="detail-holder">
                                         <div class="detail-holder inline-radio">
                                            <span><?=$licensed?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Description</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$description?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Credentials</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$credentials?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Restriction</label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$restriction?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s12">
                                         <div class="frow">
                                            <div class="caption-holder">
                                               <label>Language spoken</label>
                                            </div>
                                            <div class="detail-holder">
                                              <span><?=$language?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <div class="row">
                                            <div class="col l3 m4 s12">
                                               <label>My Fees*</label>
                                            </div>
                                            <div class="col l6 m8 s12">
                                                <span><?=$guideFee?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="frow nomargin new-post">
                                   <div class="caption-holder">
                                      <label>Awesome photos help guests want to hire you</label>
                                   </div>
                                   <div class="detail-holder">
                                      <div class="input-field ">
                                         <div class="post-photos new_pic_add">
                                            <div class="img-row">
												                        <div class="img-box">
	                                                  <div class="custom-file addimg-box add-photo ablum-add">
	                                                     <span class="icont">+</span><br><span class="">Upload photo</span>
	                                                     <div class="addimg-icon">
	                                                     </div>
	                                                     <input class="upload custom-upload remove-custom-upload" title="Choose a file to upload" required="" data-class=".post-photos .img-row" multiple="true" type="file">
	                                                  </div>
	                                               </div>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                   <p class="photolabelinfo">Please add three cover photos for your profile</p>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="uploadphotoslocalguidesave('<?=$id?>')">Publish</a>
</div>
<?php }  
exit;