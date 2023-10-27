<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Camping';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper place-wrapper mainfeed-page hidemenu-wrapper show-sidebar camping-page"> 
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?> 
        </div>
        <?php include('../views/layouts/menu.php'); ?>
        <div class="floating-icon">
		   <div class="scrollup-btnbox anim-side btnbox scrollup-float">
		      <div class="scrollup-button float-icon">
		         <span class="icon-holder ispan">
		            <i class="mdi mdi-arrow-up-bold-circle"></i>
		         </span>
		      </div>
		   </div>
		</div>
		<div class="clear"></div>
		<div>
			<?php include('../views/layouts/leftmenu.php'); ?>
			   <div class="fixed-layout ipad-mfix">
		      <div class="content-box nbg">
		         <div class="hcontent-holder home-section gray-section tours-page tours dine-local dine-inner-pages">
		            <div class="container mt-10">
		               <div class="tours-section">
		               	  <div class="row mx-0 valign-wrapper label-head">
		                     <div class="py-20 left">
		                        <h3 class="heading-inner">CAMPING</h3>
		                        <p class="para-inner">Enjoy a camping experience with locals</p>
		                     </div>
		                     <div class="ml-auto">
		                        <a href="javascript:void(0)" class="createcamping <?=$checkuserauthclass?>">
		                           <span class="hidden-sm">BECOME</span> HOST
		                        </a>
		                     </div>
		                  </div>
		                  <div class="row">
		                     <?php include('post.php'); ?>
		                  </div>
		                  <div class="new-post-mobile clear createcamping <?=$checkuserauthclass?>">
		                     <a href="javascript:void(0)" class="popup-window" ><i class="mdi mdi-account"></i></a>
		                  </div>
		               </div>
		            </div>
		         </div>
		      </div>
		   </div>
		</div>
    </div>  
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
	<!-- camp create modal -->
	<div id="campCreateModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

    <div id="compose_tool_box" class="modal compose_tool_box post-popup custom_modal main_modal">
    </div> 
	 
	<div id="composeeditpostmodal" class="modal compose_tool_box edit_post_modal post-popup main_modal custom_modal compose_edit_modal">
    </div>
	
	<div id="sharepostmodal" class="modal sharepost_modal post-popup main_modal custom_modal">
	</div>
	
	<!-- Post detail modal -->
	<div id="postopenmodal" class="modal modal_main compose_tool_box custom_modal postopenmodal_main postopenmodal_new">	
	</div>
	
	<!--post comment modal for xs view-->
	<div id="comment_modal_xs" class="modal comment_modal_xs">
	</div>  
    <div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
    <?php include('../views/layouts/addpersonmodal.php'); ?>

    <?php include('../views/layouts/custom_modal.php'); ?>
    <?php include('../views/layouts/editphotomadol.php'); ?> 
    
   
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="edit-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="userwall_tagged_users" class="modal modalxii_level1">
		<div class="content_header">
			<button class="close_span waves-effect">
				<i class="mdi mdi-close mdi-20px"></i>
			</button>
			<p class="selected_photo_text"></p>
			<a href="javascript:void(0)" class="chk_person_done_new done_btn focoutTRV03 action_btn">Done</a>
		</div>
		<nav class="search_for_tag">
			<div class="nav-wrapper">
			  <form>
			    <div class="input-field">
			      <input id="tagged_users_search_box" class="search_box" type="search" required="">
			        <label class="label-icon" for="tagged_users_search_box">
			          <i class="zmdi zmdi-search mdi-22px"></i>
			        </label>
			      </div>
			  </form>
			</div>
		</nav>
		<div class="person_box"></div>
	</div>
	
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

    <?php include('../views/layouts/commonjs.php'); ?>
	<script src="<?=$baseUrl?>/js/post.js"></script>
    <script src="<?=$baseUrl?>/js/camping.js"></script>
<?php $this->endBody() ?> 



