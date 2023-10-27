<?php
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\mongodb\ActiveRecord;
use backend\models\Googlekey;
use frontend\models\LoginForm;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$temporary_u_id = (string)$session->get('temporary_u_id');
$result = LoginForm::find()->where(['_id' => $temporary_u_id])->one();

if(empty($result)) {
   $url = Yii::$app->urlManager->createUrl(['site/mainfeed']);
   Yii::$app->getResponse()->redirect($url);
}
$fullname = $result['fullname'];
$thumb = $this->context->getimage($temporary_u_id,'thumb');
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>	

<div class="page-wrapper  completeprof-wrapper complete-profile">
	<div class="header-section">
		<?php include('../views/layouts/header.php'); ?>
	</div>
   <div id="Complete_Pro" class="modal tbpost_modal custom_modal split-page complete-popup open">
      <div class="modal_content_container">
         <div class="modal_content_child modal-content">
            <a type="button" class="item_done crop_done waves-effect hidden_close_span custom_close" href="javascript:void(0)"></a>
            <div class="custom_modal_content modal_content" id="createpopup">
               <div class="comp_popup profile-tab">
                  <div class="comp_popup_box detail-box">
                     <div class="content-holder main-holder">
                        <div class="summery">
                           <div class="dsection bborder expandable-holder expanded">
                              <div class="form-area expandable-area">
                                 <div class="combined-column wide-open">
                                    <div class="complete-profile-page">
                                       <div class="complete-content">
                                          <div class="signup-part">
                                          	 <div class="signup-box " id="create-account">
                                                <div class="complete-profile-header">
                                                   <h5 class="m-0">Welcome <?=$fullname?></h5>
                                                   <p><i>Let's added email to your profile</i></p>
                                                   <img src="<?=$thumb?>" class="center-block circle" alt="img">
                                                </div>
                                                <div class="box-content">
                                                   <div class="bc-row">
                                                      <div class="bc-component">
                                                         <div class="sliding-middle-out anim-area underlined">
                                                         	<input id="email" class=""  name="email" placeholder="Enter email id." type="text">
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="nextholder center">
                                                   <a href="javascript:void(0)" class="profilebtn waves-effect" onclick="sbmtemail()">Continue</a>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
	<?php include('../views/layouts/footer.php'); ?>	
</div>	

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>	
<script type="text/javascript" src="<?=$baseUrl?>/js/complete-profile.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/loginsignup.js"></script>
<script type="text/javascript">
   $(document).ready(function() {
      $('#Complete_Pro').modal('open');
   })

   function sbmtemail() {
      var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
      var $email = $('#email').val();
      if ($email == null || $email == undefined || $email.length == 0) {
         Materialize.toast('Please enter email.', 2000, 'red');
         return false;
      }

      if(!emailReg.test($email)) {
         Materialize.toast('Please enter valid email.', 2000, 'red');
         return false;
      }

      $.ajax({
          type: 'POST',
          url: "?r=site/acntemladded",
          data: {email: $email},
          success: function (data) {
            if(data == 'yes') {
               Materialize.toast('Verification link will be sent to your email, Also check with spam.', 2000, 'green'); 
            } else {
               Materialize.toast('Something went wrong.', 2000, 'red'); 
            }
          }
      });
   }
</script>
<?php $this->endBody() ?> 

