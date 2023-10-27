<?php 
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;

$event_type_array = array("Aperitif", "Breakfast", "Brunch", "Cooking class", "Dinner", "Food tour", "Lunch", "Tasting", "Tea time", "Picnic");
$cuisine_array = array("African", "American", "Antique", "Asian", "Barbecue", "Basque", "Belgian", "Brazilian", "British", "Cajun & Creole", "Cambodian", "Caribbean", "Catalan", "Chilean", "Chinese", "Creole", "Danish", "Dutch", "Eastern Europe", "European", "French", "Fusion", "German", "Greek", "Hawaiian", "Hungarian", "Icelandic", "Indian", "Indonesian", "Irish", "Italian", "Jamaican", "Japanese", "Korean", "Kurdish", "Latin American", "Malay", "Malaysian", "Mediterranean", "Mexican", "Middle Eastern", "Nepalese", "Nordic", "North African", "Organic", "Other", "Persian", "Peruvian", "Philippine", "Portuguese", "Russian", "Sami", "Scandinavian", "Seafood", "Singaporean", "South American", "Southern & Soul", "Spanish", "Sri Lankan", "Thai", "Turkish", "Vietnamese");
$guests_array = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59", "60", "61", "62", "63", "64", "65", "66", "67", "68", "69", "70", "71", "72", "73", "74", "75", "76", "77", "78", "79", "80", "81", "82", "83", "84", "85", "86", "87", "88", "89", "90", "91", "92", "93", "94", "95", "96", "97", "98", "99", "100");
$currency_array = array("USD", "EUR", "YEN", "CAD", "AUE");

if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { 
?>
<div class="modal_content_container">
  <div class="modal_content_child modal-content">
     <div class="popup-title">
        <button class="hidden_close_span close_span waves-effect">
        <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
        </button>         
        <h3>Dine with locals detail</h3>
        <span class="mobile_loader loaderball"><img src="<?=$baseUrl?>/images/home-loader.gif"/></span>
        <a type="button" class="item_done crop_done hidden_close_span custom_close waves-effect" href="javascript:void(0)" onclick="createlocaldinesave();">Done</a>
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
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="dineFish" id="event_type">
                                               <span>Choose type</span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="dineFish" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown">
                                              <?php foreach ($event_type_array as $event_type_s) { ?>
                                                <li>
                                                  <a href="javascript:void(0)"><?=$event_type_s?></a>
                                                </li>
                                              <?php } ?>
                                            </ul>
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
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="dineCuisine" id="cuisine">
                                               <span>Choose Cuisine</span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="dineCuisine" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown">
                                              <?php foreach ($cuisine_array as $cuisine_array_s) { ?>
                                                <li>
                                                  <a href="javascript:void(0)"><?=$cuisine_array_s?></a>
                                                </li>
                                              <?php } ?>
                                            </ul>
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
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="dineMinGuest" id="minguests">
                                               <span>1</span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="dineMinGuest" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown guest-ddl">
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
                                            <a class="dropdown_text dropdown-button-left" href="javascript:void(0)" data-activates="dineMaxGuest" id="maxguests">
                                               <span>1</span>
                                               <i class="zmdi zmdi-caret-down"></i>
                                            </a>
                                            <ul id="dineMaxGuest" class="dropdown-privacy dropdown-content custom_dropdown select-dropdown guest-ddl">
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
                                   <div class="">
                                      <div class="frow">
                                         <div class="caption-holder">
                                            <label>Event title</label>
                                         </div>
                                         <div class="detail-holder">
                                            <div class="input-field">
                                              <textarea placeholder="Event title: Grilled fish with family" class="fullwidth locinput " id="title"></textarea>
                                            </div>
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
                                         <div class="input-field">
                                            <textarea placeholder="Describe your experience" class="fullwidth locinput " id="description"></textarea>
                                         </div>
                                      </div>
                                   </div>
                                </div>
                                <div class="add-dish">
                                  <div class="dish-wrapper dishblock">
                                    <div class="fulldiv mobile275">
                                        <div class="frow">
                                           <div class="caption-holder">
                                              <label>Dish name</label>
                                           </div>
                                           <div class="detail-holder">
                                              <div class="input-field">
                                                <textarea placeholder="i.e Appetiser, Main Dish, Dessert" class="fullwidth locinput dishname"></textarea>
                                              </div>
                                           </div>
                                        </div>
                                     </div>
                                    <div class="fulldiv">
                                       <div class="frow">
                                          <div class="caption-holder mb0">
                                             <label>Summary</label>
                                          </div>
                                          <div class="detail-holder">
                                             <div class="input-field">
                                                <textarea class="materialize-textarea md_textarea item_tagline summary" placeholder="Tell your guest what you are cooking. Detail description get the most guests joining up!"></textarea>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                  </div>
                                  <div class="fulldiv">
                                    <div class="frow">
                                       <div class="detail-holder">
                                          <a href="javascript:void(0)" id="addDish"><i class="mdi mdi-plus"></i> Add Dish</a>
                                       </div>
                                    </div>
                                 </div>
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
                                                  <div class="input-field">
                                                     <input type="text" placeholder="20" class="fullwidth input-rate" id="meal"/>
                                                  </div>
                                               </div>
                                            </div>
                                            <div class="col s2">
                                              <a class="dropdown_text dropdown-button currency_drp" href="javascript:void(0)" data-activates="chooseCurrency" id="currency">
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
                                   <div class="">
                                      <div class="frow">
                                         <div class="caption-holder">
                                            <label>Where you will host this event</label>
                                         </div>
                                         <div class="detail-holder">
                                            <div class="input-field">
                                               <input type="text" placeholder="Enter city name" class="fullwidth locinput" data-query="all" onfocus="filderMapLocationModal(this)" id="whereevent"/>
                                            </div>
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
  <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="createlocaldinesave();">Publish</a>
</div>
<?php } ?>
<?php exit; ?> 