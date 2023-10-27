<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Blog;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Blog';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();

$Blog = Blog::find()->Where(['not','flagger', "yes"])->asarray()->all();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper  blog-page"> 
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
		<div class="container page_container pages_container">
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout ipad-mfix">
		      	<div class="content-box nbg noboxshadow">
			         <div class="hcontent-holder home-section gray-section tours-page tours dine-local dine-inner-pages blog-page">
			            <div class="container mt-10">
			               <div class="tours-section">
			               	  <div class="row mx-0 valign-wrapper label-head">
			                     <div class="py-20 left">
			                        <h3 class="heading-inner">BLOGS</h3>
			                        <p class="para-inner">Create blog or add comments to blogs</p>
			                     </div>
			                     <div class="ml-auto">
			                        <a href="javascript:void(0)" class="upload-gallery-blog <?=$checkuserauthclass?>"><i class="mdi mdi-plus-box"></i> BLOG</a>
			                     </div>
			                  </div>
			                  <div class="row">
				                  <ul class="collection">
				                  	<?php
				                  	$isEmpty = true;
				                  	if(!empty($Blog)) {
				                  		foreach ($Blog as $Blog_s) {
				                  			$isEmpty = false;
				                  			$Blog_s_id = (string)$Blog_s['_id'];
				                  			$Blog_s_uid = $Blog_s['user_id'];
				                  			$title = $Blog_s['title'];
				                  			$image = $Blog_s['image']; 
				                  			$description = $Blog_s['description'];
				                  			$created_at = $Blog_s['created_at'];
				                  			$unm = $this->context->getuserdata($Blog_s_uid,'fullname');
				                  			?>

						                     <li class="collection-item avatar">
						                        <img src="<?=$image?>" alt="">
						                        <?php if($status == '10') { ?>  
					                                <a href="javascript:void(0)" class="dropdown-toggle dropdown-button prevent-gallery blogflagger" data-activates='<?=$Blog_s_id?>' data-id='<?=$Blog_s_id?>'>
							                        <i class="mdi mdi-flag"></i>
							                        </a>
							                        <ul id='<?=$Blog_s_id?>' class="dropdown-content">
							                            <li class="prevent-gallery"> <a href="javascript:void(0)"data-id="<?=$Blog_s_id?>" data-module="blog" onclick="flagpost(this)">Flag post</a> </li>
							                        </ul>
						                        <?php } else  { ?>
							                        <?php if($Blog_s_uid == $user_id) { ?>
							                        	<i class="zmdi zmdi-edit edit-gallery-blog" data-editid="<?=$Blog_s_id?>"></i>
		                        						<i class="zmdi zmdi-delete" data-deleteid="<?=$Blog_s_id?>" onclick="deleteblog(this)"></i>
		                        					<?php } ?>
		                        				<?php } ?>
						                     	<a href="<?php echo Url::to(['blog/detail', 'id' => $Blog_s_id]); ?>">
						                        <span class="title"><?=$title?></span>
						                        <p><?=$description?></p>
						                        <span class="secondary-content">6h</span>
						                        <span class="post-by"><?=$unm?></span>
						                    	</a>
						                        <div class="social-icons">
						                           <a href="javascript: void(0)" class=""><i class="mdi mdi-thumb-up-outline mdi-15px" onclick="toggleIcons(this)"></i><span class="lcount">7</span></a>
						                           <a href="javascript: void(0)" class="cmnt"><i class="mdi mdi-comment-outline mdi-15px"></i></a>
						                        </div>
						                     </li>
				                  			<?php
				                  		}
				                  	}

				                  	if($isEmpty) { ?>
								    <div class="joined-tb">
								        <i class="mdi mdi-file-outline"></i>
								        <p>No blog found.</p>
								    </div>
									<?php } ?>
				                    </ul>
				              </div>
			                  <div class="new-post-mobile clear createGuideAction">
			                     <a href="javascript:void(0)" class="popup-window" ><i class="mdi mdi-blogger"></i></a>
			                  </div>
			               </div>
			               <div class="new-post-mobile clear upload-gallery-blog <?=$checkuserauthclass?>">
							   <a href="javascript:void(0)"><i class="mdi mdi-plus"></i></a>
							</div>
			            </div>
			         </div>
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
    <div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
    <?php include('../views/layouts/addpersonmodal.php'); ?>

    <?php include('../views/layouts/custom_modal.php'); ?>
    <?php include('../views/layouts/editphotomadol.php'); ?> 
    
   
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup blog-popup"></div>

	<div id="edit--popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose uploadgallery-gallery-popup blog-popup"></div>

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
	<script>
		var data1 = '';
		var place = "<?php echo (string)$place?>";
		var placetitle = "<?php echo (string)$placetitle?>";
		var placefirst = "<?php echo (string)$placefirst?>";
		var baseUrl = "<?php echo (string)$baseUrl; ?>";
		var lat = "<?php echo $lat; ?>";
		var lng = "<?php echo $lng; ?>";
	</script>	
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

    <?php include('../views/layouts/commonjs.php'); ?>
    
    <script src="<?=$baseUrl?>/js/post.js"></script>
    <script src="<?=$baseUrl?>/js/blog.js"></script>
<?php $this->endBody() ?> 