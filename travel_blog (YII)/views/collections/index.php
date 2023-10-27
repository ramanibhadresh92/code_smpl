<?php  
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Collections;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Collections';
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
		<div>
			<?php include('../views/layouts/leftmenu.php'); ?>
		    <div class="fixed-layout">
			   <div class="main-content main-page places-page photostream-page collection-page pb-0 m-t-50">
			      <div class="combined-column wide-open main-page full-page">
		            <div class="tablist sub-tabs">
		               <ul class="tabs tabs-fixed-width text-menu left tabsnew">
		                  <li class="tab"><a tabname="Wall" href="#places-all"></a></li>
		               </ul>
		            </div>
			         <div class="places-content places-all">
			            <div class="container cshfsiput cshfsi">
			               <div class="places-column cshfsiput cshfsi width-100 m-top">
			                  <div class="tab-content">
			                     <div id="places-photos" class="placesphotos-content subtab bottom_tabs">
			                        <div class="content-box">
			                           <div class="mbl-tabnav">
			                              <a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> 
			                              <h6>Photos</h6>
			                           </div>
			                           <div class="collection-gallery-wrapper">
			                              <div class="cbox-desc">
			                                 <div class="left">
			                                    <h3 class="heading-inner mt-0">PHOTO COLLECTIONS</h3>
			                                    <p class="para-inner">Place your photos and share with other</p>
			                                 </div>
			                                 <div class="cbox-title right">
			                                    <a href="javascript:void(0)" class="right-link"></a>
			                                    <div class="right po_asb">
			                                       <form>
			                                          <div class="custom-file upload-gallery_cols <?=$checkuserauthclass?>">
			                                          	<i class="mdi mdi-plus-box"></i>
			                                          	<div class="title">Collection</div>
			                                          </div>
			                                       </form>
			                                    </div>
			                                 </div>

											 <div class="collection-container">
<div class="row">
	<?php
	$isEmpty = true;
	$collections = Collections::find()->andWhere(['not','flagger', "yes"])->asarray()->all();
	foreach ($collections as $collections_s) {
		$collectionsId = (string)$collections_s['_id'];
		$collectionsUId = (string)$collections_s['user_id'];
		$collectionsImg = $collections_s['image'];
		$collectionsImg = explode(',', $collectionsImg);
		$collectionsImg = array_filter($collectionsImg);
		$collectionsName = $collections_s['title'];
		if($collectionsImg >1) {
			$totalphotos = count($collectionsImg).' photos';
		} else {
			$totalphotos = count($collectionsImg).' photo';
		}

		if($user_id == $collectionsUId) {
			$isOwner = 'yes';
		} else {
			$isOwner = 'no';
		}
		$createdBy = $this->context->getuserdata($collectionsUId,'fullname'); 
		$isEmpty = false;
		?>
	       <div class="col m4 s12">
	          <div class="collection-gallery">
	             <div class="collection-card">
	             	<?php
					if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') {	
						if($isOwner == 'yes') {
					?>			
	             	<a href="javascript:void(0)" class="removeicon prevent-gallery edit-gallery_cols" data-editid="<?=$collectionsId?>"><i class="zmdi zmdi-edit"></i></a>
	             	<a href="javascript:void(0)" class="deleteicon prevent-gallery" data-deleteid="<?=$collectionsId?>" onclick="deletecollection(this)"><i class="zmdi zmdi-delete"></i></a>
	             	<?php } } ?>
	                <div class="collection-card-body">
	                   <a href="<?php echo Url::to(['collections/details', 'id' => $collectionsId]); ?>">
	                      <div class="collection-card-inner">
								<?php if(count($collectionsImg) >= 4) { ?>
								<div class="collection-card-left">
									<div class="img-left-top">
									   <img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[0]?>" alt="">
									</div>
									<div class="img-left-below">
										<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[1]?>" alt="">
									</div>
								</div>
								<div class="collection-card-right">
									<div class="img-right-top">
										<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[2]?>" alt="">
									</div>
									<div class="img-right-below">
										<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[3]?>" alt="">
									</div>
								</div>
								<?php } else if(count($collectionsImg) == 3) { ?>
								<div class="collection-card-left">
									<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[0]?>" alt="">
								</div>
								<div class="collection-card-right">
									<div class="img-right-top">
										<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[1]?>" alt="">
									</div>
									<div class="img-right-below">
										<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[2]?>" alt="">
									</div>
								</div>
								<?php } else if(count($collectionsImg) == 2) { ?>
								<div class="collection-card-left">
									<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[0]?>" alt="">
								</div>
								<div class="collection-card-right">
									<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[1]?>" alt="">
								</div>
								<?php } else if(count($collectionsImg) == 1) { ?>
								<div class="collection-card-middle">
									<img role="presentation" class="Kh8bw _2zEKz" src="<?=$collectionsImg[0]?>" alt="">
								</div>
								<?php } ?>
	                      </div>
	                      <div class="collection-card-title">
	                         <div class="collection-title-inner"><?=$collectionsName?></div>
	                      </div>
	                   </a>
	                   <div class="collection-card-info">
	                      <?=$totalphotos?>
	                      <span>Curated by <?=$createdBy?></span>
	                   </div>
	                </div>
					<?php if($status == '10') { ?>  
						<a href="javascript:void(0)" class="dropdown-toggle dropdown-button prevent-gallery collectionsflagger" data-activates='<?=$collectionsId?>' data-id='<?=$collectionsId?>'>
                        	<i class="mdi mdi-flag"></i>
                        </a>
                        <ul id='<?=$collectionsId?>' class="dropdown-content">
                            <li class="prevent-gallery"> <a href="javascript:void(0)" data-id="<?=$collectionsId?>" data-module="collections" onclick="flagpost(this)">Flag post</a> </li>
                        </ul>
                    <?php } ?>
	             </div>
	          </div>
	       </div>
	    <?php
	}
	
	if($isEmpty) { ?>
	<div class="joined-tb">
	    <i class="mdi mdi-file-outline"></i>
	    <p>No collections found.</p>
	</div>
	<?php } ?>
</div>
											 </div>
											 <div class="new-post-mobile clear upload-gallery_cols <?=$checkuserauthclass?>">
		                                       <a href="javascript:void(0)" class="popup-window" ><i class="mdi mdi-plus"></i></a>
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
    
   
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="edit-collection-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

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
    <script src="<?=$baseUrl?>/js/collections.js"></script>
<?php $this->endBody() ?> 