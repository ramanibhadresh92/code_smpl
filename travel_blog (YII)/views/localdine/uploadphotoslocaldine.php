<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Localdine;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Local Dine';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();

$Localdine = Localdine::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
$id = (string)$Localdine['_id'];
$user_id = $Localdine['user_id'];
$title = $Localdine['title'];
$event_type = $Localdine['event_type'];
$cuisine = $Localdine['cuisine'];
$min_guests = $Localdine['min_guests'];
$max_guests = $Localdine['max_guests'];
$description = $Localdine['description'];
$dish_name = $Localdine['dish_name'];
$summary = $Localdine['summary'];
$meal = $Localdine['meal'];
$currency = $Localdine['currency'];
$whereevent = $Localdine['whereevent'];
$images = $Localdine['images'];
$images = explode(',', $images);
$images = array_values(array_filter($images));
$created_at = $Localdine['created_at'];
$u_name = $this->context->getuserdata($user_id,'fullname');
$u_image = $this->context->getuserdata($user_id,'thumbnail');
?>
<?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { 
?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Dine with locals detail</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="uploadphotoslocaldinesave('<?=$id?>', this);">Done</a>
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
                                      <div class="col s6">
                                         <div class="frow dropdown782">
                                            <div class="caption-holder">
                                               <label>Event type</label>
                                            </div>
                                            <span><?=$event_type?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s6">
                                         <div class="frow dropdown782">
                                            <div class="caption-holder">
                                               <label>Cuisine</label>
                                            </div>
                                            <span><?=$cuisine?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="row">
                                      <div class="col s6">
                                         <div class="frow pr-5 dropdown782">
                                            <div class="caption-holder">
                                               <label>Min guests</label>
                                            </div>
                                            <span><?=$min_guests?></span>
                                         </div>
                                      </div>
                                      <div class="col s6">
                                         <div class="frow pl-5 dropdown782">
                                            <div class="caption-holder">
                                               <label>Max guests</label>
                                            </div>
                                            <span><?=$max_guests?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="">
                                      <div class="frow">
                                         <div class="caption-holder">
                                            <label>Event title</label>
                                         </div>
                                         <div class="detail-holder">
                                          <span><?=$title?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <label>Event description </label>
                                      </div>
                                      <div class="detail-holder">
                                        <span><?=$description?></span>
                                      </div>
                                   </div>
                                </div>
                                <div class="add-dish">

                                  <?php
                                  for ($i=0; $i < count($dish_name); $i++) { 
                                    $karant_dish_name = $dish_name[$i];
                                    $karant_summary = $summary[$i];
                                  ?>
                                  <div class="dish-wrapper dishblock">
                                    <div class="fulldiv mobile275">
                                      <div class="frow">
                                         <div class="caption-holder">
                                            <label>Dish name</label>
                                         </div>
                                         <div class="detail-holder">
                                          <span><?=$karant_dish_name?></span>
                                         </div>
                                      </div>
                                    </div>
                                    <div class="fulldiv">
                                       <div class="frow">
                                          <div class="caption-holder mb0">
                                             <label>Summary</label>
                                          </div>
                                          <div class="detail-holder">
                                            <span><?=$karant_summary?></span>
                                          </div>
                                       </div> 
                                    </div>
                                  </div>
                                  <?php
                                  } 
                                  ?>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder">
                                         <div class="row">
                                            <div class="col s4">
                                               <label>Guest pays per meal</label>
                                            </div>
                                            <div class="col s6">
                                               <div class="detail-holder">
                                                <span><?=$meal?></span>
                                               </div>
                                            </div>
                                            <div class="col s2">
                                              <span><?=$currency?></span>
                                            </div>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="">
                                      <div class="frow">
                                         <div class="caption-holder">
                                            <label>Where you will host this event</label>
                                         </div>
                                         <div class="detail-holder">
                                          <span><?=$whereevent?></span>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="frow nomargin new-post">
                                   <div class="caption-holder">
                                      <label>Awesome photos help guests want to join up</label>
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
                                   <p class="photolabelinfo">Please add three cover photos for your local dine profile</p>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="uploadphotoslocaldinesave('<?=$id?>', this);">Publish</a>
</div>
<?php } ?>
<?php exit; ?> 