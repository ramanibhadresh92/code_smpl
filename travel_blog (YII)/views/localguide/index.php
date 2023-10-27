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
$this->title = 'Local Guide';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper  mainfeed-page"> 
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
		<div class="container page_container">
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout ipad-mfix">
		      <div class="main-content main-page places-page localguide-page pb-0 m-t-50 with-lmenu general-page generaldetails-page split-page m-hide">
		         <div class="combined-column mx-auto float-none">
		            <div class="content-box">
		               <div class="cbox-desc">
		                  <div class="tab-content view-holder">
		                     <div class="local-heading">
		                        <div class="row mx-0 valign-wrapper">
		                           <div class="left">
		                              <h3 class="heading-inner">LOCAL GUIDES <span class="lt">(<?=count($posts)?>)</span></h3>
		                              <p class="para-inner">Hire a local guide and enjoy tour</p>
		                           </div>
		                           <div class="right ml-auto">
		                           		<a href="javascript:void(0)" class="composeTbPostActionlocalguide createGuideAction <?=$checkuserauthclass?>">
		                           			<span class="hidden-sm">BECOME</span> GUIDE
		                           		</a>
		                           </div>
		                        </div>
		                     </div>
		                     <div class="page-details travelbuddy-details">
		                        <div class="row">
		                           	<div class="travelbuddy-summery gdetails-summery">
		                              	<?php include('searchbox.php'); ?>
		                              	<?php include('sidebox.php'); ?>
		                           	</div>
		                            <?php include('post.php'); ?>
		                        </div>
		                     </div>
		                  </div>
		               </div>
		            </div>
		         </div>
		      </div>
			  <div class="new-post-mobile clear composeTbPostActionlocalguide createGuideAction <?=$checkuserauthclass?>">
				<a href="javascript:void(0)" class="popup-window" ><i class="mdi mdi-account"></i></a>
			  </div>
		   </div>
		</div>
    </div>  
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
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

    <?php include('../views/layouts/addpersonmodal.php'); ?>

    <?php include('../views/layouts/custom_modal.php'); ?>
    <?php include('../views/layouts/editphotomadol.php'); ?> 
    
    <div id="compose_tb_post" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose hire-a-popup"> </div>
 
	<div id="compose_mapmodal" class="modal map_modal compose_inner_modal modalxii_level1 map_modalUniq">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>

	<div id="edit_tb_post" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose hire-a-popup"> </div>

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
	
	<!-- create guide profile modal -->
	<div id="createLocalGuideModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

	<div id="editLocalGuideModal" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose event-detail-modal">
	</div>

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>
	<?php include('../views/layouts/commonjs.php'); ?>
    <script src="<?=$baseUrl?>/js/post.js"></script>
    <script src="<?=$baseUrl?>/js/localguide.js"></script>
<?php $this->endBody() ?> 