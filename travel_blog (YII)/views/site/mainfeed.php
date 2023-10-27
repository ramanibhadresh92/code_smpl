<?php  
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use frontend\models\TopPlaces;
use frontend\models\Attractions;
use frontend\models\Tours;
use frontend\models\LocalguidePost;
use frontend\models\Travelbuddytripinvite;
use frontend\models\HangoutEvent;
use frontend\models\Travelbuddytrip;
use frontend\models\PostForm;
use frontend\models\Connect;
use frontend\assets\AppAsset;
use frontend\models\Gallery;
use frontend\models\Like;
use frontend\models\Comment;
use frontend\models\LoginForm;
use frontend\models\Localdine;
use frontend\models\Homestay;
use frontend\models\Grestaurants;
use frontend\models\Ghotels; 
use frontend\models\Gsimilardestinations;
use frontend\models\Gpopularnearby;
use backend\models\Googlekey;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Home';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();

$sendlocation = urlencode($placetitle);
$place = str_replace("'","\'",$place);
$place = str_replace("  "," ",$place);

$placetitle = str_replace("'","",$placetitle);
$placefirst = str_replace("'","\'",$placetitle);
$placefirst = explode(",", $placefirst);
$placefirst = $placefirst[0];
$pcount = substr_count($placetitle,",");

$placetitleLabel = '';     
if($placetitle != '') {
    $placetitleLabel = explode(",", $placetitle);
    if(count($placetitleLabel) >1) {
        $first = reset($placetitleLabel);
        $last = end($placetitleLabel);
        $placetitleLabel = $first.', '.$last;
    } else {
        $placetitleLabel = implode(", ", $placetitleLabel);
    }
}
$searchWord = str_replace(" ", "+", $placetitle);
$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');
?>
<script src="<?=$baseUrl?>/js/chart.js"></script>
	<div class="page-wrapper place-wrapper home-index mainfeed-page homeindex-page">
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <div class="index-page">
        	<?php include("../views/layouts/menu.php"); ?>
        </div> 
        <div class="menu-wrap">
		   <div class="menu-sec h-search">
		      	<img src="<?=$baseUrl?>/images/homepage-bg.jpg" alt="header img">
		      	<div class="search-box">
		           <div class="box-content">
		               <div class="bc-row home-search">
		                  <div class="row mx-0">
		                  	<div class="was-in-country dropdown782">
		                        <a href="https://visitjapan.ae" target="_blank"><span>Visit Japan</span></a>
		                        <a href="javascript:void(0)" class="compose_visitcountryAction"><i class="zmdi zmdi-chevron-down"></i></a>
		                    </div>
		                  </div>
		               </div>
		           </div>
		        </div> 
		   </div>
		</div> 
		<div class="nav-sec menu-box">
		   	<?php include("../views/layouts/menubox.php"); ?>
		</div>
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
			<div class="fixed-layout"> 
				<div class="main-content main-page places-page japan-page pb-0 mt-0">
				    <div class="combined-column wide-open main-page full-page">
			            <div class="tablist sub-tabs">
			                <ul class="tabs tabs-fixed-width text-menu left tabsnew">
			                    <li class="tab"><a tabname="Wall" href="#places-all"></a></li>
			                </ul>
			            </div>
				        <div class="places-content places-all">       
							<div class="container">	
								<div class="places-column m-top">
				                  	<div class="">
					                    <div id="" class="placesall-content bottom_tabs">
											<div class="content-box">
<div id="popular-hotels-list" class="placesection yellowsection">
	<?php
	$hotelRs = Ghotels::find()->asarray()->one();
	if(isset($hotelRs['results']) && !empty($hotelRs['results'])) {
		$hotelQl = $hotelRs['status']; 
		if($hotelQl == 'OK') {
			$hotel = isset($hotelRs['results']) ? $hotelRs['results'] : array();
			if(!empty($hotel)) { 
			$isEmptyHotel = false;	
			?>    
			<div class="tcontent-holder">
				<div id="hotel-title" class="cbox-title m-t nborder">
					<div class="valign-wrapper left">
						<img src="<?=$baseUrl?>/images/lodgeicon-sm.png"/>
						<span><span>Popular</span> Hotels in <?=$placefirst?></span>
					</div>
					<div class="top-stuff right">	 
						<div class="more-actions">
							<a href="<?php echo Url::to(['site/hotellist']); ?>" class="viewall-link clicable">View All</a>
						</div>                                 
					</div>                                               
				</div>
				<div class="cbox-desc">
					<div class="places-content-holder">			
						<div class="list-holder">
							<div class="row">
								<?php 
								$hotel__KS = 1;
								$start_hotel = 1;
								foreach ($hotel as $hotel_s) {
									if(isset($hotel_s['place_id']) && !empty($hotel_s['place_id'])){
										if($start_hotel >3) {
											break;
										}
										$place_id = $hotel_s['place_id']; 
										$file = $place_id.'.jpg';
										$storage_path = 'uploads/gimages/';
										$img = '';
										$imgclass = 'himg';
										if(file_exists($storage_path.$file)) {
											$img = $storage_path.$file;
											list($width, $height, $type, $attr) = getimagesize($img);
											if($width > $height){$imgclass = 'himg';}
											else if($height > $width){$imgclass = 'vimg';}
											else{$imgclass = 'himg';}
										} else {
											continue;
										}

										$col_x1 = '';
										/*if($start_hotel == 2) { 
											$col_x1 = 'third-col';
										}*/

										$KSOW = '';
										if($hotel__KS > 2) {
											$KSOW = 'hide-on-small-only';
										}

										$start_hotel++;
										?>
										<div class="col m4 s12 pb pb-holder <?=$col_x1?> <?=$KSOW?>">
											<div class="placebox">
												<a href="<?php echo Url::to(['site/hotellist']); ?>">
													<div class="imgholder <?=$imgclass?>-box">
														<img src="<?=$img?>" class="<?=$imgclass?>"/>
														<div class="overlay"></div>
													</div>
													<div class="descholder">
														<h5><?=$hotel_s['name']?></h5>
														<?php if(!empty($hotel_s['rating'])){ ?>
														<span class="ratings">
															<?php for($j=0;$j<5;$j++){ ?>
																<i class="mdi mdi-star <?php if($j < $hotel_s['rating']){ ?>active<?php } ?>"></i>
															<?php } ?>
															<label><?=$hotel_s['user_ratings_total']?> Reviews</label>
														</span>
														<?php } ?>
														<div class="tags">
														<?php 
														$pieces = $hotel_s['types'];
														foreach ($pieces as $s_pieces) {
															echo "<span>".$s_pieces."</span> ";
														}
														?>
														</div>
													</div>
												</a>
											</div>
										</div>
									<?php 
									$hotel__KS++;
									} 
								}?>
							</div>
						</div>
					</div>
				</div>
			</div>											
		<?php } 
		}
	} 
	?>
</div>

<div id="popular-rest-list" class="placesection redsection">
	<?php
	$isEmptyRestaurants = true;
	$restaurantsRs = Grestaurants::find()->asarray()->one();
	if(isset($restaurantsRs['results']) && !empty($restaurantsRs['results'])) {
		$restaurantsQl = $restaurantsRs['status']; 
		if($restaurantsQl == 'OK') {
			$restaurants = isset($restaurantsRs['results']) ? $restaurantsRs['results'] : array();
			if(!empty($restaurants)) { 
			$isEmptyRestaurants = false;
			?>
			<div class="tcontent-holder">
				<div class="cbox-title nborder" id="restaurant-title">
					<div class="valign-wrapper left">
					<img src="<?=$baseUrl?>/images/dineicon-sm.png"/>
					<span><span>Popular</span> Restaurants in <?=$placefirst?></span>
					</div>
					<div class="top-stuff right">			
						<a href="<?php echo Url::to(['site/restaurantlist']); ?>" class="viewall-link clicable">View All</a>		
					</div>
				</div>
				<div class="cbox-desc">
					<div class="places-content-holder">
						<div class="list-holder">
							<div class="row">
								<?php 
								$restaurants__KS = 1;
								$start_restaurants = 1;

								foreach ($restaurants as $restaurants_s) {
									if(isset($restaurants_s['place_id']) && !empty($restaurants_s['place_id'])){
									if($start_restaurants >3) {
										break;
									}
									$place_id = $restaurants_s['place_id']; 
									$file = $place_id.'.jpg';
									$storage_path = 'uploads/gimages/';
									$img = '';
									$imgclass = 'himg';
									if(file_exists($storage_path.$file)) {
										$img = $storage_path.$file;
										list($width, $height, $type, $attr) = getimagesize($img);
										if($width > $height){$imgclass = 'himg';}
										else if($height > $width){$imgclass = 'vimg';}
										else{$imgclass = 'himg';}
									} else {
										continue;
									}

									$KSOW = '';
									if($restaurants__KS > 2) {
										$KSOW = 'hide-on-small-only';
									}

									$start_restaurants++;					

									$col_x1 = '';
									/*if($start_restaurants == 2) { 
										$col_x1 = 'third-col';
									}*/				
								?>
								<div class="col m4 s12 pb-holder <?=$KSOW?> <?=$col_x1?>">
									<div class="placebox">
										<a href="<?php echo Url::to(['site/restaurantlist']); ?>">
											<div class="imgholder <?=$imgclass?>-box"><img src="<?=$img?>" class="<?=$imgclass?>"/><div class="overlay"></div></div>
											<div class="descholder">
												<h5><?=$restaurants_s['name']?></h5>
												<?php if(!empty($restaurants_s['rating'])){ ?>
												<span class="ratings">
													<?php for($j=0;$j<5;$j++){ ?>
														<i class="mdi mdi-star <?php if($j < $restaurants_s['rating']){ ?>active<?php } ?>"></i>
													<?php } ?>
													<label><?=$restaurants_s['user_ratings_total']?> Reviews</label>
												</span>
												<?php } ?>
												<div class="tags">
												<?php 
												$pieces = $restaurants_s['types'];
												foreach ($pieces as $s_pieces) {
													echo "<span>".$s_pieces."</span> ";
												}
												?>
												</div>
											</div>
										</a>
									</div>
								</div>
								<?php 
								$restaurants__KS++;
								} } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } 
		}
	} ?>
</div>

<div id="popular-attr-list" class="placesection bluesection">
	<?php
	$attractionSort = 'PriceUSD';
	if($pcount > 0) {
		$attractionCountry = $placefirst; 
		$attractionType = 'City';
	} else {
		$attractionCountry = '';
		$attractionType = 'Country';
	}
	$isEmptyPopularAttractions = true;
	$popularAttractions = Tours::getTodos($placefirst, $attractionCountry, $attractionType, $attractionSort);
	if(!empty($popularAttractions)) { ?>
	<div class="tcontent-holder">
		<div class="cbox-title nborder" id="todo-title">
			<div class="valign-wrapper left">
			<img src="<?=$baseUrl?>/images/todoicon-sm.png"/>
			<span><span>Popular</span> Attractions in <?=$placefirst?></span>
			</div>
			<div class="top-stuff right">										
				<a href="<?php echo Yii::$app->urlManager->createUrl(['tours']); ?>" class="viewall-link">View All</a>
			</div>                              
		</div>
		<div class="cbox-desc">
			<div class="places-content-holder">
				<div class="list-holder">
					<div class="row">
					<?php $i= 1; 
					$Attractions__KS = 1;
					foreach($popularAttractions as $popularAttraction) {
						$isEmptyPopularAttractions = false;
						if($i <= 3) { 

						$KSOW = '';
						if($Attractions__KS > 2) {
							$KSOW = 'hide-on-small-only';
						}
						?>
						<div class="col m4 s12 pb-holder <?php if($i == 3){ ?>third-col<?php } ?>">
							<div class="placebox">
								<a href="<?=$popularAttraction['ProductURL']?>" target="_new">
									<div class="imgholder himg-box"><img src="<?=str_replace('/graphicslib','/graphicslib/thumbs674x446/',$popularAttraction['ProductImage'])?>" class="himg"/><div class="overlay"></div></div>
									<div class="descholder">
										<h5 title="<?=$popularAttraction['ProductName']?>"><?=$popularAttraction['ProductName']?></h5>
										<span class="ratings">
											<?php for($j=0;$j<5;$j++){ ?>
												<i class="mdi mdi-star <?php if($j < $popularAttraction['AvgRating']){ ?>active<?php } ?>"></i>
											<?php } ?>
											<label>45 Reviews</label>
										</span>
										<div class="tags">
											<?php 
											$pieces = explode(", ", str_replace(' & ',', ',$popularAttraction['Group1']));
											foreach($pieces as $element) {
												echo "<span>".$element."</span> ";
											} ?>
										</div>
									</div>
								</a>
							</div>
						</div>
						<?php 
						$Attractions__KS++;
						$i++;
						} 
					} ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>

<div id="popular-guides-list" class="placesection local-guide-sec"> 
	<?php
		$localGuideData = LocalguidePost::recentLocalguidePostsnew($user_id, $placetitle, 0, 5);
		$localGuideData = json_decode($localGuideData, true);
		if(!empty($localGuideData)) { ?>
			<div class="tcontent-holder mb-20">
				<div class="cbox-title nborder title-1">
				    <div class="valign-wrapper left mb-10">
				       <a href="javascript:void(0)">
				          <span class="heading-icon"><i class="mdi mdi-account"></i></span>
				       </a>
				       <span>Local guide in <?=$placefirst?></span> 
				    </div>
				    <?php if(count($localGuideData)>4) { 
						array_pop($localGuideData);
					?>
				    <div class="top-stuff right">
				       <a href="?r=localguide/index&id=all&address=<?=$sendlocation?>" class="viewall-link" onclick="openDirectTab('places-guides')">View All</a>
				    </div>
				    <?php } ?>
				    <p>Hire a local guide and enjoy tour experience</p>
				</div>
					<div class="cbox-desc tour-guides">
					<div class="places-content-holder">
						<div class="list-holder guidebox-list">
							<div class="row">

								<?php 
								$guidebox = 1;
								$cls = '';
								$localGuideData__KS = 1;
								foreach ($localGuideData as $key => $guide) {
									$idArray = array_values($guide['_id']);
									$guideid = $idArray[0];
									//$guideid = (string)$guide['_id']['$id'];
									$guideuserid = $guide['user_id'];
									$link = Url::to(['userwall/index', 'id' => (string)$guideuserid]);
									$img = Travelbuddytripinvite::getimage($guideuserid,'photo');
									$totalinvited = $guide['totalinvited']; 
									if($guidebox == count($localGuideData)) {
										$cls = 'last_guide';
									}

									$KSOW = '';
									if($localGuideData__KS > 2) {
										$KSOW = 'hide-on-small-only';
									}
								?>
								<div class="col l3 m3 s6 <?=$cls?> <?=$localGuideData__KS?>">
									<a href="?r=localguide/index&id=<?=$guideid?>&address=<?=$sendlocation?>" class="viewall-link">
										<div class="guide-box">
											<div class="imgholder">
												<img src="<?=$img?>" class="circle"/>
											</div>
											<div class="descholder">
												<h3><?=$guide['fullname'];?></h3>
												<p>Guide for <?=$placefirst?></p>
												<span class="stars-holder yellow-stars">
													<i class="mdi mdi-star active"></i>
													<i class="mdi mdi-star active"></i>
													<i class="mdi mdi-star active"></i>
													<i class="mdi mdi-star active"></i>
													<i class="mdi mdi-star"></i>
													<label><?php
													if($totalinvited > 1) {
														echo $totalinvited.' Refers';
													} else {
														echo $totalinvited.' Refer';
													}?>
													</label>
												</span>
											</div>
										</div>
									</a>
								</div>
								<?php 
								$guidebox++;
								$localGuideData__KS++; 
								} ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?> 
</div> 

<div id="popular-dests-list" class="placesection destinations-sec">
	<?php
	$cities = Gpopularnearby::find()->asarray()->one();
	$cities = $cities["results"];
	?>
	<div class="tcontent-holder">
		<div class="cbox-title nborder title-1">
            <div class="valign-wrapper left">
               <a href="javascript:void(0)">
                  <span class="heading-icon"><i class="mdi mdi-map-marker"></i></span>
               </a>
               <span>Similar Destinations</span> 
            </div>
         </div>
		<div class="cbox-desc">
			<div class="places-content-holder">
				<div class="list-holder">
					<div class="row">
					<?php if(!empty($cities)) { 
					array_shift($cities); 
					$i= 1; 
					$cities__KS = 1;
					foreach($cities as $city) {
						if($i <= 3) {
							$tempplacetitle = $cityname = $city[1].', '.$city[3];
							$link = "?r=places&p=".$cityname; 
							$tempplacetitle = str_replace(' ','+',$tempplacetitle);
							//$img = $this->context->getplaceimage($tempplacetitle);
							$img = $city[1].'.jpg';
							$gettracount = count(Travelbuddytrip::gettripplaecsdata($cityname, $cityname, $user_id));
							$getpplacereviewscount = PostForm::getPlaceReviewsCount($cityname,'reviews');
							$getpplacetipscount = PostForm::getPlaceReviewsCount($cityname,'tip');
							$pcount = substr_count($place,",");
							if($pcount > 0) {
								$placet = (explode(", ",$place));
								$placecountry = $placet[1];
								$type = 'City';
							} else {
								$placecountry = '';
								$type = 'Country';
							}
							$thingscount = Tours::getTodos($placefirst,$placecountry,$type,'PriceUSD');
							$KSOW = '';
							if($cities__KS > 2) {
								$KSOW = 'hide-on-small-only';
							}
						?>
						<div class="col m4 s6 fpb-holder <?=$KSOW?>">
							<div class="f-placebox destibox">
								<a href="<?=$link?>" target="_new">
									<div class="imgholder himg-box">
										<img src="<?=$baseUrl?>/images/nearby/<?=$img?>" class="himg"/>
										<div class="overlay"></div>
									</div>
									<div class="descholder">
										<h5><?=$cityname;?></h5>
										<ul>
											<li onclick="getallitem('hotels', '<?=$tempplacetitle?>', '<?=$cityname?>', 'empty', 'lodge');"><a aria-expanded="false" data-toggle="tab" href="#places-lodge"><i class="mdi mdi-menu-right"></i>Hotels</a></li>
											<li onclick="getallitem('rest', '<?=$tempplacetitle?>', '<?=$cityname?>', 'empty', 'dine');">
											<a aria-expanded="false" data-toggle="tab" href="#places-dine"><i class="mdi mdi-menu-right"></i>Restaurants</a></li>
											<li><a href="javascript:void(0)" onclick="nearbycitieslocal('<?=$city[1]?>', '<?=$tempplacetitle?>')"><i class="mdi mdi-menu-right"></i></i>Locals</a></li>
										</ul>
									</div>
								</a>
							</div>
						</div>
						<?php 
							$cities__KS++;
							$i++;
							} 
							}
						} else { ?>
						<div class="col m4 s6 fpb-holder">
							<?php $this->context->getnolistfound('nocityfound');?>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>			
</div>

<?php
$gallery = Gallery::find()->where(['type' => 'places'])->andWhere(['not','flagger', "yes"])->limit(10)->asarray()->all();  
if(!empty($gallery)) { ?>
<div class="placesection pop-photos-sec">
  <div class="tcontent-holder topattractions-section">
     <div class="cbox-title nborder title-1">
        <div class="valign-wrapper left">
           <a href="javascript:void(0)">
              <span class="heading-icon"><i class="mdi mdi-file-image"></i></span>
           </a>
           <span>Popular Photos</span> 
        </div>
        <div class="top-stuff right">
           <div class="more-actions">
              <a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>" class="viewall-link clicable">View All</a>
           </div>
        </div>
     </div>
     <div class="cbox-desc">
        <div class="places-content-holder">
           <div class="list-holder topplaces-list">
              	<!-- New Slider-->
               <div class="row">
                 	<div class="gallery-content">
	                	<div id="placebox"class="lgt-gallery-photo lgt-gallery-justified home-justified-gallery">
						<?php
						$gallery__KS = 1;
						foreach($gallery as $gallery_item) {
				            $hideids = isset($gallery_item['hideids']) ? $gallery_item['hideids'] : '';
				            $hideids = explode(',', $hideids);

				            if(isset($user_id) && trim($user_id) != '') {
				                if(in_array($user_id, $hideids)) {
					                continue;
					            }
					        }

				            $galimname = $gallery_item['image'];
				            if(file_exists($galimname)) {
				                $gallery_item_id = $gallery_item['_id'];
				                $eximg = $galimname;
				                $inameclass = preg_replace('/\\.[^.\\s]{3,4}$/', '', $galimname);
				                
				                $picsize = $imgclass = '';
				                $like_count = Like::getLikeCount((string)$gallery_item_id);
				                $comments = Comment::getAllPostLikeCount((string)$gallery_item_id);
				                $title = $gallery_item['title'];
				                
				                if(isset($user_id) && trim($user_id) != '') {
					                $like_active = Like::find()->where(['post_id' => (string) $gallery_item_id,'status' => '1','user_id' => (string) $user_id])->one();
					                if(!empty($like_active)) {
					                    $like_active = 'active';
					                } else {
					                    $like_active = '';
					                }
					            } else {
					                $like_active = '';
					            }
				                
				                $time = Yii::$app->EphocTime->comment_time(time(),$gallery_item['created_at']);
				                $puserid = (string)$gallery_item['user_id'];
				                
				                $puserdetails = LoginForm::find()->where(['_id' => $puserid])->one();
				                if(isset($user_id) && trim($user_id) != '') {
					                if($puserid != $user_id) {
					                    $galusername = ucfirst($puserdetails['fname']) . ' ' . ucfirst($puserdetails['lname']);
					                    $isOwner = false;
					                } else {
					                    $galusername = 'You';
					                    $isOwner = true;
					                }
					            } else {
					            	$galusername = 'You';
					                $isOwner = true;
					            }
				                
				                $like_buddies = Like::getLikeUser($inameclass .'_'. $gallery_item['_id']);
				                $newlike_buddies = array();
				                foreach($like_buddies as $like_buddy) {
				                    $newlike_buddies[] = ucwords(strtolower($like_buddy['fullname']));
				                }
				                $newlike_buddies = implode('<br/>', $newlike_buddies);  

				                $val = getimagesize($eximg);
				                $picsize .= $val[0] .'x'. $val[1] .', ';
				                if($val[0] > $val[1]) {
				                    $imgclass = 'himg';
				                } else if($val[1] > $val[0]) {
				                    $imgclass = 'vimg';
				                } else {
				                    $imgclass = 'himg';
				                }
				                
				                $isEmpty = false;
				                $KSOW = '';
								if($gallery__KS > 2) {
									$KSOW = 'hide-on-small-only';
								}

				                ?> 
				                <div data-src="<?=$eximg?>" class="allow-gallery <?=$KSOW?>" data-sizes="<?=$gallery_item_id?>|||Gallery">
				                    <img class="himg" src="<?=$eximg?>"/>
				                    <?php if($isOwner) { ?> 
				                    <a href="javascript:void(0)" class="removeicon prevent-gallery" data-id="<?=$gallery_item_id?>" onclick="removepic(this)"><i class="mdi mdi-delete"></i></a>
				                    <?php } ?>   
				                    <div class="caption">
				                        <div class="left">
				                            <span class="title"><?=$title?> ( <?=$time?> )</span> <br>
				                            <span class="attribution">By <?=$galusername?></span>
				                        </div>
				                        <div class="right icons">
				                            <a href="javascript:void(0)" class="prevent-gallery like custom-tooltip pa-like liveliketooltip liketitle_<?=$gallery_item_id?> <?=$like_active?>" onclick="doLikeAlbumbImages('<?=$gallery_item_id?>');" data-title="<?=$newlike_buddies?>">
				                                <i class="mdi mdi-thumb-up-outline mdi-15px"></i>
				                            </a>
			                                <?php if($like_count >0) { ?>
			                                    <span class="likecount_<?=$gallery_item_id?> lcount"><?=$like_count?></span>
			                                <?php } else { ?>
			                                    <span class="likecount_<?=$gallery_item_id?> lcount"></span>
			                                <?php } ?>
				                            
				                            <a href="javascript:void(0)" class="prevent-gallery waves-effect">
				                                <i class="mdi mdi-comment-outline mdi-15px cmnt"></i>
				                            </a>
			                                <?php if($comments > 0){ ?>
			                                    <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"><?=$comments?></span>
			                                <?php } else { ?>
			                                    <span class="lcount commentcountdisplay_<?=$gallery_item_id?>"></span>
			                                <?php } ?>
				                        </div>
				                    </div>
				                </div>
				            <?php 
				            $gallery__KS++;
				            } 
				        } ?>
						</div>
                 	</div>
              	</div>
              <!--New Slider-->
           </div>
        </div>
     </div>
  </div>
</div>
<?php } ?>

<?php
$Localdine = Localdine::find()->Where(['not','flagger', "yes"])->limit(3)->asarray()->all();
$isEmpty = true;
if(!empty($Localdine)) { ?>
<div class="placesection loc-dine-sec localdine-page">
      <div class="tcontent-holder topattractions-section">
         <div class="cbox-title nborder title-1">
           <div class="valign-wrapper left">
              <a href="javascript:void(0)">
                 <span class="heading-icon"><i class="mdi mdi-basecamp"></i></span>
              </a>
              <span>Locals Dine</span> 
           </div>
         </div>
         <div class="cbox-desc pt-0">
            <div class="places-content-holder">
               <div class="list-holder topplaces-list">
                  <!-- New Slider-->
                  <div class=" mx-0"> 
                     <div class="hcontent-holder home-section gray-section tours-page tours dine-local japan pt-0">
                        <div class="">
                           <div class="tours-section pt-0">
                              <div class="row">
                              	<?php
                              	foreach ($Localdine as $Localdine_s) { 
									$localdine_id = (string)$Localdine_s['_id'];
									$localdine_user_id = $Localdine_s['user_id'];
									$localdine_title = $Localdine_s['title'];
									$localdine_cuisine = $Localdine_s['cuisine'];
									$localdine_min_guests = $Localdine_s['min_guests'];
									$localdine_max_guests = $Localdine_s['max_guests'];
									$localdine_description = $Localdine_s['description'];
									$localdine_dish_name = $Localdine_s['dish_name'];
									$localdine_summary = $Localdine_s['summary'];
									$localdine_meal = $Localdine_s['meal'];
									$localdine_currency = $Localdine_s['currency'];
									$localdine_whereevent = $Localdine_s['whereevent'];
									$localdine_images = $Localdine_s['images'];
									$localdine_images = explode(',', $localdine_images);
									$localdine_images = array_values(array_filter($localdine_images));
									$main_image = $localdine_images[0];
									$created_at = $Localdine_s['created_at'];
									$profile = $this->context->getimage($localdine_user_id,'thumb');
									$localdine_u_name = $this->context->getuserdata($localdine_user_id,'fullname');
									$isEmpty = false;
									?>
	                                 <div class="col l4 m6 s12 wow slideInLeft">
	                                    <div class="tour-box">
	                                       <span class="imgholder">
	                                          <a href="<?php echo Url::to(['localdine/detail', 'id' => $localdine_id]); ?>">
								                  <img src="<?=$main_image?>">
								               </a>
	                                          <div class="price-tag">
	                                          	<span>
		                                          	<?php
													if(array_key_exists($localdine_currency, $currency_icon)) {
													 echo $currency_icon[$localdine_currency].$localdine_meal;
													}
													?>
												</span>
											   </div>
	                                       </span>
	                                       <span class="descholder">
	                                          <a href="">
	                                            <img src="<?=$profile?>" alt="">
	                                          </a>
	                                          <small class="dine-hosttext">Hosted by <a dir="auto" href=""><?=$localdine_u_name?></a> in Amsterdam</small>
	                                          <div class="dine-eventtags">
	                                             <div class="tag-inner">Dinner</div>
	                                          </div>
	                                          <a class="dine-eventtitle" dir="auto" href=""><?=$localdine_title?></a>
	                                          <div class="dine-rating pt-20 center">
	                                             <i class="mdi mdi-star"></i>
	                                             <i class="mdi mdi-star"></i>
	                                             <i class="mdi mdi-star"></i>
	                                             <i class="mdi mdi-star"></i>
	                                             <i class="mdi mdi-star"></i>
	                                          </div>
	                                       </span>
	                                    </div>
	                                 </div>
	                                <?php
	                            }
	                            ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <!--New Slider-->
               </div>
            </div>
         </div>
      </div>
</div>
<?php 
}
?>

<?php
$Homestay = Homestay::find()->Where(['not','flagger', "yes"])->limit(3)->asarray()->all();
if(!empty($Homestay)) { 
?>
<div class="placesection homestay-sec homestay-page">
  <div class="tcontent-holder topattractions-section">
     <div class="cbox-title nborder title-1">
       <div class="valign-wrapper left">
          <a href="javascript:void(0)">
             <span class="heading-icon"><i class="mdi mdi-hotel"></i></span>
          </a>
          <span>Homestay</span> 
       </div>
     </div>
     <div class="cbox-desc pt-0">
        <div class="places-content-holder">
           <div class="list-holder topplaces-list">
              <!-- New Slider-->
              <div class=" mx-0">
                 <div class="hcontent-holder home-section gray-section tours-page tours dine-local japan pt-0">
                    <div class="">
                       <div class="tours-section pt-0">
                          <div class="row">
                          	<?php
                          	foreach ($Homestay as $key => $Homestay_s) { 
								$postId = (string)$Homestay_s['_id'];	
								$postUId = $Homestay_s['user_id'];	
								$title = $Homestay_s['title'];	
								$property_type = $Homestay_s['property_type'];	
								$guests_room_type = $Homestay_s['guests_room_type'];	
								$bath = $Homestay_s['bath'];	
								$guest_type = $Homestay_s['guest_type'];	 
								$homestay_location = $Homestay_s['homestay_location'];
								$currency = strtoupper($Homestay_s['currency']);	
								$adult_guest_rate = $Homestay_s['adult_guest_rate'];	
								$description = $Homestay_s['description'];	
								$rules = $Homestay_s['rules'];	
								$services = '';
								$images = $Homestay_s['images'];	
								$images = explode(',', $images);
								$images = array_values(array_filter($images));
								$main_image = $images[0];
								$profile = $this->context->getimage($postUId,'thumb');
								$name = $this->context->getuserdata($postUId,'fullname');
								?>
                             <div class="col l4 m6 s12 wow slideInLeft ">
                                <div class="tour-box">
                                   <span class="imgholder">
                                    <a href="<?php echo Url::to(['homestay/detail', 'id' => $postId]); ?>">
										<img src="<?=$main_image?>">
										<div class="price-tag">
											<span>
												<?php
												if(array_key_exists($currency, $currency_icon)) {
													echo $currency_icon[$currency].$adult_guest_rate;
												}
												?>
											</span>
										</div>
									</a>
								   </span>
                                   <span class="descholder">
                                      <a href="javascript:void(0)">
								         <img src="<?=$profile?>" alt="">
								      </a>
                                      <small class="dine-hosttext">Hosted by <a dir="auto" href=""><?=$name?></a> in <?=$homestay_location?></small>
								      <div class="dine-eventtags">
								      	<div class="tag-inner">one room <?=$bath?> bath</div>
								      </div>
								      <a class="dine-eventtitle" dir="auto" href=""><?=$title?></a>
                                      <div class="dine-rating center">
                                         <i class="mdi mdi-star"></i>
                                         <i class="mdi mdi-star"></i>
                                         <i class="mdi mdi-star"></i>
                                         <i class="mdi mdi-star"></i>
                                         <i class="mdi mdi-star"></i>
                                      </div>
                                   </span>
                                </div>
                             </div>
                            <?php
                        	}
                        	?>
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
              <!--New Slider-->
           </div>
        </div>
     </div>
  </div>
</div>
<?php
}
?>

<div id="popular-dests-list" class="placesection cities-sec">
	<?php
	$cities = Gpopularnearby::find()->asarray()->one();
	$cities = $cities["results"];
	?>
	<div class="tcontent-holder">
		<div class="cbox-title nborder title-1">
            <div class="valign-wrapper left">
              <a href="javascript:void(0)">
                 <span class="heading-icon"><i class="mdi mdi-map-marker-radius"></i></span>
              </a>
              <span><span>Popular</span> nearby cities</span> 
	        </div>
	    </div>
		<div class="cbox-desc p-t-0">
			<div class="places-content-holder">
				<div class="list-holder">
					<div class="row">
					<?php if(!empty($cities)) { 
					array_shift($cities); 
					$i= 1; 
					$cities__KS = 1;
					foreach($cities as $city) {
						if($i <= 3) {
							$tempplacetitle = $cityname = $city[1].', '.$city[3];
							$link = "?r=places&p=".$cityname;
							$tempplacetitle = str_replace(' ','+',$tempplacetitle);
							//$img = $this->context->getplaceimage($tempplacetitle);
							$img = $city[1].'.jpg';
							$gettracount = count(Travelbuddytrip::gettripplaecsdata($cityname, $cityname, $user_id));
							$getpplacereviewscount = PostForm::getPlaceReviewsCount($cityname,'reviews');
							$getpplacetipscount = PostForm::getPlaceReviewsCount($cityname,'tip');
							$pcount = substr_count($place,",");
							if($pcount > 0) {
								$placet = (explode(", ",$place));
								$placecountry = $placet[1];
								$type = 'City';
							} else {
								$placecountry = '';
								$type = 'Country';
							}
							$thingscount = Tours::getTodos($placefirst,$placecountry,$type,'PriceUSD');

							$KSOW = '';
							if($cities__KS > 2) {
								$KSOW = 'hide-on-small-only';
							}
						?>
						<div class="col m4 s6 fpb-holder <?=$KSOW?>">
							<div class="f-placebox destibox">
								<a href="<?=$link?>" target="_new">
									<div class="imgholder himg-box">
										<img src="<?=$baseUrl?>/images/nearby/<?=$img?>" class="himg"/>
										<div class="overlay"></div>
									</div>
									<div class="descholder">
										<h5><?=$cityname;?></h5>
										<ul>
											<li onclick="getallitem('hotels', '<?=$tempplacetitle?>', '<?=$cityname?>', 'empty', 'lodge');"><a aria-expanded="false" data-toggle="tab" href="#places-lodge"><i class="mdi mdi-menu-right"></i>Hotels</a></li>
											<li onclick="getallitem('rest', '<?=$tempplacetitle?>', '<?=$cityname?>', 'empty', 'dine');">
											<a aria-expanded="false" data-toggle="tab" href="#places-dine"><i class="mdi mdi-menu-right"></i>Restaurants</a></li>
											<li><a href="javascript:void(0)" onclick="nearbycitieslocal('<?=$city[1]?>', '<?=$tempplacetitle?>')"><i class="mdi mdi-menu-right"></i></i>Locals</a></li>
										</ul>
									</div>
								</a>
							</div>
						</div>
						<?php 
							$cities__KS++;
							$i++;
							} }
						} else { ?>
						<div class="col m4 s6 fpb-holder">
							<?php $this->context->getnolistfound('nocityfound');?>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>			
</div>
					                        </div>
					                        <?php include('../views/layouts/footer.php'); ?>
					                    </div>

					                    <div id="places-lodge" class="placeslodge-content subtab top_tabs dis-none"></div>
										
										<div id="places-dine" class="placesdine-content subtab top_tabs dis-none"> </div>

										<div id="places-todo" class=" placestodo-content subtab top_tabs dis-none"> </div>	
				                  	</div>
				                </div>
								<?php include('../views/layouts/gen_wall_col.php'); ?>
							</div>
						</div>
				    </div>
				</div>
			</div>
		</div>
		
    </div>  
 
<input type="hidden" name="pagename" id="pagename" value="feed" />
<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
<div id="compose_discus" class="modal compose_tool_box post-popup custom_modal main_modal new-wall-post set_re_height compose_discus_popup loadermodal"></div>

<script>
var data1 = '';
var place = "<?php echo (string)$place?>";
var placetitle = "<?php echo (string)$placetitle?>";
var placefirst = "<?php echo (string)$placefirst?>";
var baseUrl = "<?php echo (string)$baseUrl; ?>";
var lat = "<?php echo $lat; ?>";
var lng = "<?php echo $lng; ?>";
</script>
<?php include('../views/layouts/commonjs.php'); ?>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyP?>&libraries=places&callback=initAutocomplete"></script>
<script src="<?=$baseUrl?>/js/jquery.mousewheel.min.js"></script>
<script src="<?=$baseUrl?>/js/waterfall-light.js" type="text/javascript" charset="utf-8"></script>
<script src='<?=$baseUrl?>/js/wNumb.min.js'></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/connect.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/post.js"></script>
<script type="text/javascript" src="<?=$baseUrl?>/js/index.js"></script>
<?php $this->endBody() ?> 