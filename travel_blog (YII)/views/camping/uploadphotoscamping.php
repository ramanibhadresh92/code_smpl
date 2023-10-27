<?php   
use yii\helpers\Url; 
use frontend\models\Camping;
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$isEmpty = true; 

$Camping = Camping::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
if(!empty($Camping)) { 
  $camping_id = (string)$Camping['_id'];
  $camping_uid = $Camping['user_id'];
  $camping_title = $Camping['title'];
  $camping_min_guests = $Camping['min_guests'];
  $camping_max_guests = $Camping['max_guests'];
  $camping_rate = $Camping['rate'];
  $camping_currency = $Camping['currency'];
  $camping_description = $Camping['description'];
  $camping_location = $Camping['location'];
  $camping_telephone = $Camping['telephone'];
  $camping_email = $Camping['email'];
  $camping_website = $Camping['website'];
  $camping_period = $Camping['period'];
  $camping_services = $Camping['services'];
  $camping_services = explode(',', $camping_services);
  $camping_images = $Camping['images'];
  $camping_images = explode(',', $camping_images);
  $camping_images = array_values(array_filter($camping_images));
  
  if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
  <div class="modal_content_container">
    <div class="modal_content_child modal-content">
       <div class="popup-title ">
          <button class="hidden_close_span close_span waves-effect">
          <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
          </button>         
          <h3>Edit camp with locals detail</h3>
          <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
          <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="uploadphotoscampingsave('<?=$camping_id?>', this)">Done</a>
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
                                     <div class="frow">
                                        <div class="caption-holder">
                                           <label>Title</label>
                                        </div>
                                        <div class="detail-holder">
                                          <span><?=$camping_title?></span>
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
                                              <span><?=$camping_min_guests?></span>
                                           </div>
                                        </div>
                                        <div class="col s6">
                                           <div class="frow pl-5 dropdown782">
                                              <div class="caption-holder">
                                                 <label>Max guests</label>
                                              </div>
                                              <span><?=$camping_max_guests?></span>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                     <div class="frow">
                                        <div class="caption-holder">
                                           <div class="row">
                                              <div class="col s4">
                                                 <label>Rate per adult guest</label>
                                              </div>
                                              <div class="col s6">
                                                 <div class="detail-holder">
                                                  <span><?=$camping_rate?></span>
                                                 </div>
                                              </div>
                                              <div class="col s2">
                                                 <div class="input-field dropdown782 mt-0">
                                                  <span><?=$camping_currency?></span>
                                                 </div>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                     <div class="frow">
                                        <div class="caption-holder">
                                           <label>Describe your camp</label>
                                        </div>
                                        <div class="detail-holder">
                                          <span><?=$camping_description?></span>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="row">
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pr-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>Camp Location</label>
                                              </div>
                                              <div class="detail-holder">
                                                <span><?=$camping_location?></span>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pl-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>Telephone</label>
                                              </div>
                                              <div class="detail-holder">
                                                <span><?=$camping_telephone?></span>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="row">
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pr-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>E-mail</label>
                                              </div>
                                              <div class="detail-holder">
                                                <span><?=$camping_email?></span>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                     <div class="col s6">
                                        <div class="fulldiv mobile275 pl-5">
                                           <div class="frow">
                                              <div class="caption-holder">
                                                 <label>Website</label>
                                              </div>
                                              <div class="detail-holder">
                                                <span><?=$camping_website?></span>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                     <div class="frow">
                                        <div class="caption-holder">
                                           <label>Opening period</label>
                                        </div>
                                        <div class="detail-holder">
                                          <span><?=$camping_period?></span>
                                        </div>
                                     </div>
                                  </div>
                                  <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder mb-10">
                                         <label>Services</label>
                                      </div>
                                      <div class="detail-holder" id="editcamping_services">
                                         <?php if(in_array('Waste tank discharge', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Waste tank discharge" title="Waste tank discharge" src="<?=$baseUrl?>/images/services-icon/1.png">
                                         </a>
                                         <?php } ?>


                                         <?php if(in_array('Public lavatory', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Public lavatory" title="Public lavatory" src="<?=$baseUrl?>/images/services-icon/2.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Walking path', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Walking path" title="Walking path" src="<?=$baseUrl?>/images/services-icon/3.png">
                                         </a>
                                         <?php } ?>

                                         <?php if(in_array('Swimming pool', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Swimming pool" title="Swimming pool" src="<?=$baseUrl?>/images/services-icon/4.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Fishing permits', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Fishing permits" title="Fishing permits" src="<?=$baseUrl?>/images/services-icon/5.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Cooking facilities', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Cooking facilities" title="Cooking facilities" src="<?=$baseUrl?>/images/services-icon/6.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Sports hall', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Sports hall" title="Sports hall" src="<?=$baseUrl?>/images/services-icon/7.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Washing machine', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Washing machine" title="Washing machine" src="<?=$baseUrl?>/images/services-icon/8.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Hot pot', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Hot pot" title="Hot pot" src="<?=$baseUrl?>/images/services-icon/9.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Sports field', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Sports field" title="Sports field" src="<?=$baseUrl?>/images/services-icon/10.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Shower', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Shower" title="Shower" src="<?=$baseUrl?>/images/services-icon/11.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Golf course', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Golf course" title="Golf course" src="<?=$baseUrl?>/images/services-icon/12.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Sauna', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Sauna" title="Sauna" src="<?=$baseUrl?>/images/services-icon/13.png">
                                         </a>
                                         <?php } ?>
                                         
                                         <?php if(in_array('Play ground', $camping_services)) { ?>
                                         <a href="javascript:void(0)" class="check-image">
                                            <img alt="Play ground" title="Play ground" src="<?=$baseUrl?>/images/services-icon/14.png">
                                         </a>
                                         <?php } ?>
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
                                     <p class="photolabelinfo">Please add three cover photos for your camp profile</p>
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
    <a href="javascript:void(0)" class="btngen-center-align waves-effect"  onclick="uploadphotoscampingsave('<?=$camping_id?>', this)">Publish</a>
  </div>
<?php } 
}
exit; ?> 
