<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Blog; 
use frontend\models\BlogComments; 
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
$user_profile = $this->context->getimage($user_id,'thumb');
$Blog = Blog::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
$Blog_id = (string)$Blog['_id'];
$Blog_uid = $Blog['user_id'];
$title = $Blog['title'];
$image = $Blog['image'];
$description = $Blog['description'];
$created_at = $Blog['created_at'];
$BlogComments = BlogComments::find()->where(['blog_id' => $id])->asarray()->all();
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper "> 
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>,
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
		      <div class="content-box nbg">
		      	<div class="hcontent-holder home-section gray-section tours-page tours dine-local dine-inner-pages blog-detail-page">
		            <div class="container mt-10">
		               <div class="tours-section">
							<div class="row mx-0 valign-wrapper">
								<div class="py-20 left">
		                     		<h3 class="headline"><?=$title?></h3>
								</div>
								<div class="ml-auto">
							    	<a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>" class="upload-gallery-blog"><i class="zmdi zmdi-chevron-left"></i>&nbsp;&nbsp;Back to Blog</a>
							 	</div>
							</div>
							<div class="row mx-0">
			                     <div class="col s8">
			                        <div class="blog-left">
			                           <div class="blog-img">
			                              <img src="<?=$image?>">
			                           </div>
			                           <div class="blog-text">
			                              <p><?=$description?></p>
			                           </div>
			                           <div class="comment-sec">
			                              <div class="comment-count"><h4><?=count($BlogComments)?> Comments</h4></div>
			                              <div class="addnew-comment valign-wrapper ">
			                                 <div class="img-holder">
			                                    <a href="javascript:void(0)">
			                                       <img class="circle" src="profile/<?=$user_profile?>">
			                                    </a>
			                                 </div>
			                                 <div class="desc-holder">
			                                    <div class="cmntarea focused">
			                                       <textarea data-adaptheight class="materialize-textarea data-adaptheight" id="blog_cmt_txtarea" placeholder="Add a comment..."></textarea>
			                                    </div>
			                                 </div>
			                              </div>
			                              <div class="comment-list">
			                                 <ul>
			                                 	<?php
			                                 	foreach ($BlogComments as $BlogComments_s) {
			                                 		$cmt = $BlogComments_s['comment'];
			                                 		$cmtid = (string)$BlogComments_s['_id'];
			                                 		$cmtusrid = $BlogComments_s['user_id'];
			                                 		$cmtusrthmb = $this->context->getuserdata($cmtusrid,'thumbnail');
			                                 		$cmtusrnm = $this->context->getuserdata($cmtusrid,'fullname');
			                                 		$created_at = $BlogComments_s['created_at'];
			                                 		$created_at = date("M d, Y", $created_at); 
			                                 		?>
												    <li>
				                                       <div class="ranker-box">
				                                          <div class="img-holder">
				                                             <img src="profile/<?=$cmtusrthmb?>">
				                                          </div>
				                                          <div class="desc-holder">
				                                             <a href="javascript:void(0)" class="userlink"><?=$cmtusrnm?></a>
				                                             <span class="comment-date"><?=$created_at?></span>
				                                             <span class="info"><?=$cmt?></span>
				                                          </div>
				                                       </div>
				                                    </li>
			                                 		<?php
			                                 	}
			                                 	?>
			                                    
			                                 </ul>
			                              </div>
			                           </div>
			                        </div>
			                     </div>
			                     <div class="col s4">
			                        <div class="blog-right">
			                           <div class="side-blogs-list">
			                              <ul>
			                              	<?php
			                              	$recent_blogs = Blog::find()->where(['not',(string)'_id', (string)$id])->andWhere(['not','flagger', "yes"])->asarray()->limit(3)->all();
			                              	if(!empty($recent_blogs)) {
			                              		foreach ($recent_blogs as $recent_blogs_s) {
			                              			$recent_blogs_s_id = (string)$recent_blogs_s['_id'];
			                              			$recent_blogs_s_img = $recent_blogs_s['image'];
			                              			$recent_blogs_s_name = $recent_blogs_s['title'];
			                              			?>
													<li>
														<a href="<?php echo Url::to(['blog/detail', 'id' => $recent_blogs_s_id]); ?>">
														<img src="<?=$recent_blogs_s_img?>" alt="">
														<h5><?=$recent_blogs_s_name?></h5>
														</a>
													</li>
			                              			<?php
			                              		}
			                              	} else {
			                              		?>
			                              			<div class="post-holder bshadow">      
										                <div class="joined-tb">
										                    <i class="mdi mdi-file-outline"></i>        
										                    <p>No recent Blogs found.</p>
										                </div>    
										            </div>
			                              		<?php
			                              	}
			                              	?>
			                              </ul>
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
    <div id="compose_mapmodal" class="modal map_modal compose_inner_modal modalxii_level1 map_modalUniq">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
    <?php include('../views/layouts/addpersonmodal.php'); ?>

    <?php include('../views/layouts/custom_modal.php'); ?>
    <?php include('../views/layouts/editphotomadol.php'); ?> 
    
   
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="edit-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup blog-popup"></div>

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
    <script src="<?=$baseUrl?>/js/blog.js"></script>
    
<?php $this->endBody() ?> 