<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$email = $session->get('email'); 
$user_id =  (string)$session->get('user_id');
$currency_array = array("USD", "EUR", "YEN", "CAD", "AUE");
$guests_array = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59", "60", "61", "62", "63", "64", "65", "66", "67", "68", "69", "70", "71", "72", "73", "74", "75", "76", "77", "78", "79", "80", "81", "82", "83", "84", "85", "86", "87", "88", "89", "90", "91", "92", "93", "94", "95", "96", "97", "98", "99", "100");

if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title ">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Create camp with locals detail</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="createcampingsave(this)">Done</a>
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
                                         <div class="input-field">
                                            <input type="text" placeholder="Experience title: camping under the moon" class="fullwidth locinput " id="camping_title"/>
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
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="campMinGuest" id="camping_minguests">
                                               <span>1</span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="campMinGuest" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown guest-ddl">
                                              <?php foreach ($guests_array as $guests_array_s) { ?>
                                                <li>
                                                  <a href="javascript:void(0)"><?=$guests_array_s?></a>
                                                </li> 
                                              <?php } ?>
                                            </ul>
                                         </div>
                                      </div>
                                      <div class="col s6">
                                         <div class="frow pl-5 dropdown782">
                                            <div class="caption-holder">
                                               <label>Max guests</label>
                                            </div>
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="campMaxGuest" id="camping_maxguests">
                                               <span>1</span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="campMaxGuest" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown guest-ddl">
                                              <?php foreach ($guests_array as $guests_array_s) { ?>
                                                <li>
                                                  <a href="javascript:void(0)"><?=$guests_array_s?></a>
                                                </li> 
                                              <?php } ?>
                                            </ul>
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
                                                  <div class="input-field">
                                                     <input type="text" placeholder="20" class="fullwidth input-rate" id="camping_rate"/>
                                                  </div>
                                               </div>
                                            </div>
                                            <div class="col s2">
                                              <a class="dropdown_text dropdown-button currency_drp" href="javascript:void(0)" data-activates="chooseCurrency" id="camping_currency">
                                                 <span class="currency_label">USD</span>
                                                 <i class="zmdi zmdi-caret-down"></i>
                                              </a>
                                              <ul id="chooseCurrency" class="dropdown-privacy dropdown-content custom_dropdown guest-ddl">
                                                <?php foreach ($currency_array as $s8032n) { ?>
                                                 <li>
                                                    <a href="javascript:void(0)"><?=$s8032n?></a>
                                                 </li>
                                                <?php  } ?>
                                              </ul>
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
                                         <div class="input-field">
                                            <textarea placeholder="Tell people about your camp" class="fullwidth locinput " id="camping_description"></textarea>
                                         </div>
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
                                               <div class="input-field">
                                                  <input type="text" placeholder="Enter city name" class="fullwidth locinput" data-query="all" onfocus="filderMapLocationModal(this)" id="camping_location"/>
                                               </div>
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
                                               <div class="input-field">
                                                  <input type="text" placeholder="Enter telephone number" class="fullwidth locinput " id="camping_telephone"/>
                                               </div>
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
                                               <div class="input-field">
                                                  <input type="text" placeholder="Enter email address" class="fullwidth locinput " id="camping_email"/>
                                               </div>
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
                                               <div class="input-field">
                                                  <input type="text" placeholder="Enter website url" class="fullwidth locinput " id="camping_website"/>
                                               </div>
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
                                         <div class="input-field">
                                            <input type="text" class="fullwidth locinput" placeholder="28/5 - 15/9" id="camping_period" name="datetimes"/>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="fulldiv">
                                   <div class="frow">
                                      <div class="caption-holder mb-10">
                                         <label>Services</label>
                                      </div>
                                      <div class="detail-holder" id="camping_services">
                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="spa" title="spa" src="<?=$baseUrl?>/images/amenity-spa.png">
                                         </a>

                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="beach" title="beach" src="<?=$baseUrl?>/images/amenity-beach.png">
                                         </a>

                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="wifi" title="wifi" src="<?=$baseUrl?>/images/amenity-wifi.png">
                                         </a>

                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="breakfast" title="breakfast" src="<?=$baseUrl?>/images/amenity-breakfast.png">
                                         </a>

                                         <a href="javascript:void(0)" class="check-image">
                                            <div class="image-select"></div>
                                            <img alt="pool" title="pool" src="<?=$baseUrl?>/images/amenity-pool.png">
                                         </a>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect"  onclick="createcampingsave(this)">Publish</a>
</div>
<?php } ?>
<?php exit; ?> 
