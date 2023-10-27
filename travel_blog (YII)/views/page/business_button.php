<?php 
use frontend\models\Connect;
use frontend\models\UserForm;
use frontend\models\Page;

$session = Yii::$app->session;
$user_id = (string) $session->get('user_id');   

$page_details = Page::Pagedetails($pageid);

$bsnesbtn = isset($page_details['bsnesbtn']) ? $page_details['bsnesbtn'] : ''; 
$bsnesbtnvalue = isset($page_details['bsnesbtnvalue']) ? $page_details['bsnesbtnvalue'] : ''; 

$phoneinfo = '';
$mailinfo = '';
$urlinfo = '';

if($bsnesbtn=="Call Now" || $bsnesbtn=="Contact Us"){
   $phoneinfo = $bsnesbtnvalue;
} else if($bsnesbtn=="Send Email" || $bsnesbtn=="Send Message"){        
   $mailinfo = $bsnesbtnvalue;
} else {
   $urlinfo = $bsnesbtnvalue;
}
$selectedOptions = array('Call Now', 'Book Now', 'Contact Us', 'Send Message', 'Show Now', 'Sign Up', 'Watch Video', 'Send Email', 'Learn More');

?>
<div class="modal_content_container">
      <div class="modal_content_child modal-content">
         <div class="popup-title ">
            <button class="hidden_close_span close_span waves-effect">
            <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
            </button>			
            <h3>Edit Action Button</h3>
            <a type="button" class="item_done crop_done waves-effect hidden_close_span custom_close waves-effect" href="javascript:void(0)" >Done</a>
         </div>
         <div class="custom_modal_content modal_content" id="createpopup">
            <div class="ablum-yours profile-tab">
               <div class="ablum-box detail-box">
                  <div class="content-holder main-holder">
                     <div class="summery">
                        <div class="dsection bborder expandable-holder expanded">
                           <div class="form-area expandable-area">
                              <form class="ablum-form">
                                 <div class="popup-content">
                                    <div class="businessbtn-content">
                                       <p>Add a button to your page that takes people directly to your website.</p>
                                       <div class="frow">
                                          <label class="margin_b5">Choose a button</label>				
                                          <div class="fullwidth popup_select_button">
                                             <select class="select2 fullwidth" id="businessbtn-type" onchange="setAdditionalInfo()">
                                                <?php
                                                    foreach ($selectedOptions as $selectedOptions1) {
                                                         $selected = '';
                                                         if($selectedOptions1 == $bsnesbtn) {
                                                            $selected = 'selected';
                                                         }
                                                         echo '<option '.$selected.'>'.$selectedOptions1.'</option>';
                                                      }  
                                                ?>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="additional-info">
                                          <div class="frow phone-info info dis-none">
                                             <label>Phone Number</label>				
                                             <div class="sliding-middle-custom anim-area underlined fullwidth">
                                                <input type="text" placeholder="Add phone number" class="fullwidth" id="phone-infox" value="<?=$phoneinfo?>">
                                             </div>
                                          </div>
                                          <div class="frow url-info info dis-none">
                                             <label>Website URL</label>				
                                             <div class="sliding-middle-custom anim-area underlined fullwidth">
                                                <input type="text" placeholder="Add URL" class="fullwidth" id="url-infox" value="<?=$urlinfo?>">
                                             </div>
                                          </div>
                                          <div class="frow mail-info info dis-none">
                                             <label>Email address</label>				
                                             <div class="sliding-middle-custom anim-area underlined fullwidth">
                                                <input type="text" placeholder="Enter email address" class="fullwidth" id="mail-infox" value="<?=$mailinfo?>">
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
   <div class="additem_modal_footer modal-footer">
      <a class="btngen-center-align  close_modal open_discard_modal waves-effect" href="javascript:void(0)">Cancel</a>
      <a class="btngen-center-align waves-effect" href="javascript:void(0)" onclick="updateBusinessButton()">Save</a>
   </div>
<?php exit;?>