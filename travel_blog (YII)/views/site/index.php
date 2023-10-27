<?php
use yii\helpers\Url;
use frontend\assets\AppAsset;
use yii\widgets\ActiveForm;
use yii\mongodb\ActiveRecord;
use yii\validators\Validator;
use yii\helpers\ArrayHelper;

use frontend\models\LoginForm;
use frontend\models\Slider;
use frontend\models\Gallery;
use frontend\models\Like;
use frontend\models\Comment;
use frontend\models\NotificationSetting;
use frontend\models\SecuritySetting;
use frontend\models\Localdine;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;

$session = Yii::$app->session;
$error = $session->get('loginerror');
$email = '';
$currency_icon = array('USD' =>'<i class="mdi mdi-currency-usd"></i>', 'EUR' =>'<i class="mdi mdi-currency-eur"></i>', 'YEN' =>'<i class="mdi mdi-currency-cny"></i>', 'CAD' =>'Can<i class="mdi mdi-currency-usd"></i>', 'AUE' =>'AUE');
if(isset($_GET['email']) && !empty($_GET['email']) )
{
	
$notify = new NotificationSetting();
$notify2 = $notify->notification2();
$security_settings = new SecuritySetting();
$security_settings2 = $security_settings->security2();
   
$email = $_GET['email'];
$email =  base64_decode(strrev($email));
$session->set('email',$email);

$update = LoginForm::find()->where(['email' => $email])->one();

$session->set('user_id',$update['_id']);

$update->status = '1';
$update->update();
 
 $admin_email = 'adelhasanat@yahoo.com';
	   
 $user = LoginForm::find()->where(['email' => $email])->one();

$username = $user->username;
$fname = $user->fname;
$lname = $user->lname;
$phone = $user->phone;
$gender = $user->gender;
$city = $user->city;
$country = $user->country;
	
 /* Mail To Admin*/
	try {
	$mailCompose2 = Yii::$app->mailer->compose()
	->setFrom(array('csupport@iaminjapan.com' => 'Iaminjapan Team'))
	->setTo($email)
	->setSubject('Iaminjapan- Registerd User Information')
	->setHtmlBody('<html> <head> <meta charset="utf-8" /> <title>I am in Japan</title> </head> <body style="margin:0;padding:0;"> <div style="text-align: left;font-size: 12px;margin:10px 0 0;color:#666;"> Dear '.$fname.',<br/><br/> We are very happy to have you as a valued member of Iaminjapan community. Our aim is to connect each other socially and have Travel benefits.<br/>  Start enjoying being part of our community, share your experience with community and explore the world at finger tip.<br/>  Note: Privacy is important to us; Therefore, we will not sell, rent or give any information from our community to anyone. <br/> To keep Iaminjapan safe, fun and very respectable. Here are some social safety tips: <br/>	 <ul style="padding:0 0 0 12px;margin:10px 0 5px;"> <li style="margin:0 0 5px;"> <b>Report abuse:</b>  Please report anything inappropriate, immediate action will be taken. </li><li style="margin:0 0 5px;"> <b>Block people:</b> If someone is bothering you, block them! They wont be able to contact you again </li><li style="margin:0 0 5px;"> <b>Public posts:</b> Dont post anything youll regret. Dont give out personal information.</li><li style="margin:0 0 5px;"><b>Post Decent Content:</b> Absolutely no adult or semi adult photos or videos are permitted on this site. Therefore, please do not try to upload, link and share any adult or semi-adult images. No gambling, drugs, adult or alcoholic ads are allowed. Harassment, inappropriate behaviors or solicitation are not permitted on this site.</br> Software and the management monitor the site, any inappropriate activities </br> will not be tolerated and will result in banning your name, email address </br> and  IP address permanently. So please be social and thank you for your cooperation. </li></ul></br> <b>Have fun and stay safe!</b><br/> Thank you for registering.</br></br><span style="font-size:10px;">If you have any question/suggestion, feel free to write us at <a href="javascript:void(0)">helpdesk@iaminjapan.com</a></span></div></body></html>')
	->send();                    

	$url = Yii::$app->urlManager->createUrl(['site/index']);
	Yii::$app->getResponse()->redirect($url); 
	} 
	catch (ErrorException $e) 
	{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
}

elseif(isset($_GET['id']) && !empty($_GET['id']))
{
    $userid = $_GET['id'];
    $update = LoginForm::find()->where(['_id' => $userid])->one();
    
    if(!empty($update))
    {
      $email = $update->email;
      $session = Yii::$app->session;
      $session->set('user_id',$userid);
    }
    echo $email = $update->email;
}

$asset = frontend\assets\AppAsset::register($this);
$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
    <script>
    $(document).ready(function(){
      var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
           if (sParameterName[0] === 'enc') {
               $(".login-part").css("display", "none");
               $(".forgot-part").css("display", "block");
               $(".forgot-part .forgot-box").css("display", "none");
               $(".forgot-part #fp-step-3").css("display", "block");
            }
        }
    });
    </script>
	<link href="<?=$baseUrl?>/css/custom-croppie.css" rel="stylesheet">
	<!-- 
	<link href="<?=$baseUrl?>/css/emoticons.css" rel="stylesheet">
	<link href="<?=$baseUrl?>/css/emostickers.css" rel="stylesheet"> -->
	<div class="home-page bodydup pageloader">
		<div id="loader-wrapper" class="home_loader">
			<div class="loader-logo">
	            <div class="lds-css ng-scope">
	               <div class="lds-rolling lds-rolling100">
	                  <div></div>
	               </div>
	            </div>
	         </div> 
			<div class="loader-text">Please wait...</div>
			<div class="loader-section section-left"></div>
			<div class="loader-section section-right"></div>
		</div>
			
		<div class="home-wrapper">
			<header>
				<div class="home-container">
					<div class="mobile-menu topicon">
						<a href="javascript:void(0)" class="mbl-menuicon"><i class="mdi mdi-menu"></i></a>
					</div>
					<div class="hlogo-holder">
						<a class="home-logo" href="<?php echo Yii::$app->urlManager->createUrl(['site/index']); ?>"><img src="<?=$baseUrl?>/images/home-logo.png"></a>            						
					</div> 
					<ul class="home-menu">
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>">Japan</a></li>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['discussion']); ?>">Discussion</a></li>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>">Photostream</a></li>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>">Blog</a></li>
						<li><a href="<?php echo Yii::$app->urlManager->createUrl(['home']); ?>"><i class="zmdi zmdi-plus-square pr-5"></i> Advert</a></li>
					</ul>
					<div class="head-right">
	                  <div class="search-part">
	                     <a class="homebtn" onclick="flipSectionTo('login');" href="javascript:void(0)">
	                     <i class="mdi mdi-lock"></i>
	                     <span>Login</span> 
	                     </a>
	                  </div>
	                  <div class="signup-part">
	                     <a class="homebtn" onclick="flipSectionTo('login');" href="javascript:void(0)">
	                     <i class="mdi mdi-lock"></i>
	                     <span>Login</span>
	                     </a>
	                  </div>
	                  <div class="login-part">
	                     <a class="homebtn" onclick="flipSectionTo('search');" href="javascript:void(0)">
	                     <i class="mdi mdi-close"></i>
	                     <span>Close</span>
	                     </a>
	                  </div>
	                  <div class="forgot-part">
	                     <a class="homebtn" onclick="flipSectionTo('login');" href="javascript:void(0)">
	                     <i class="mdi mdi-lock"></i>
	                     <span>Login</span>
	                     </a>
	                  </div>
	               </div>
				</div>	
			</header>
			
			<div class="sidemenu-holder m-hide">
				<div class="sidemenu">
					<a href="javascript:void(0)" class="closemenu waves-effect waves-theme"><i class="mdi mdi-close"></i></a>				
					<div class="side-user">							
						Check this out
					</div> 
					<div class="sidemenu-ul">
						<ul class="large-menuicons">
							<li><a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>"><i class="zmdi zmdi-home"></i> Japan</a></li>
							<li><a href="<?php echo Yii::$app->urlManager->createUrl(['discussion']); ?>"><i class="zmdi zmdi-account"></i> Discussion</a></li>
							<li><a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>"><i class="zmdi zmdi-image"></i> Photostream</a></li>
							<li><a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>"><i class="zmdi zmdi-blogger"></i> Blog</a></li>
							<li><a href="<?php echo Yii::$app->urlManager->createUrl(['ads']); ?>"><i class="mdi mdi-plus"></i> Advert</a></li>
						</ul>
					</div>
				</div>
			</div>
			
			<div class="hcontent-holder banner-section">
				<span class="overlay"></span>
				<div class="home-content">
					<div class="search-part homel-part">						
						<div class="container">
							<div class="search-box">								
								
								<div class="box-content">
									<div class="bc-row home-search">
										<div class="row">	
											<div class="slide">
												<div class="slider-item">
													<h3 class="main-header-text">WATCH</h3>
													<p class="homeslider-para">Live show from home</p>
													<div class="homeslider-btns">
			                                          	<a class="waves-effect waves-light active" href="<?php echo Yii::$app->urlManager->createUrl(['watch']); ?>">Watch</a>
			                                          	<a class="waves-effect waves-light" href="<?php echo Yii::$app->urlManager->createUrl(['virtualtours']); ?>">Tour</a>
			                                          	<a class="waves-effect waves-light" href="<?php echo Yii::$app->urlManager->createUrl(['todo']); ?>">To Do</a>
			                                       	</div>
												</div>
												<div class="slider-item">
													<h3 class="main-header-text">TOUR</h3>
													<p class="homeslider-para">Experience the best virtual tour</p>
													<div class="homeslider-btns">
														<a class="waves-effect waves-light" href="<?php echo Yii::$app->urlManager->createUrl(['watch']); ?>">Watch</a>
														<a class="waves-effect waves-light active" href="<?php echo Yii::$app->urlManager->createUrl(['virtualtours']); ?>">Tour</a>
														<a class="waves-effect waves-light " href="<?php echo Yii::$app->urlManager->createUrl(['todo']); ?>">To Do</a>
													</div>

												</div>
												<div class="slider-item">
													<h3 class="main-header-text">TO DO</h3>
													<p class="homeslider-para">Enjoy an online experience with locals</p>
													<div class="homeslider-btns">
														<a class="waves-effect waves-light" href="<?php echo Yii::$app->urlManager->createUrl(['watch']); ?>">Watch</a>
														<a class="waves-effect waves-light" href="<?php echo Yii::$app->urlManager->createUrl(['virtualtours']); ?>">Tour</a>
														<a class="waves-effect waves-light active" href="<?php echo Yii::$app->urlManager->createUrl(['todo']); ?>">To Do</a>
													</div>
												</div>
											</div>
											<div class="homeslider-text">
												<p>Stay connected with us, from home.</p>
											</div>
					             		</div>
									</div>
								</div>
							</div>
						</div>
					</div>				
					<div class="login-part homel-part login-sec">	
						<div class="hidden_header">
							<div class="content_header">
								<button class="close_span cancel_poup waves-effect" onclick="flipSectionTo('search');">
									<i class="mdi mdi-close mdi-20px"></i>
								</button>
								<p class="modal_header_xs">Login</p>
							</div>
						</div>					
						<div class="container">
							<div class="homebox login-box animated wow zoomIn" data-wow-duration="1200ms" data-wow-delay="500ms">
		                        <div class="sociallink-area">
		                        	<a  id="FacebookBtn" href="javascript:void(0)" class="fb-btn white-text"><span><i class="mdi mdi-facebook"></i></span>Connect with Facebook</a>
		                        </div>
		                        <div class="sociallink-area">                   
		                           <a id="GoogleBtn" href="javascript:void(0)" class="fb-btn google-connect white-text"><span><i class="mdi mdi-google"></i></span>Connect with Google</a>
		                        </div>
		                        <div class="sociallink-area">                   
		                           <a href="/iaminjapancode/frontend/web?r=site/auth&amp;authclient=facebook" class="fb-btn instagram-connect white-text"><span><i class="mdi mdi-instagram"></i></span>Connect with Instagram</a>
		                        </div>
		                    </div>
						</div>
					</div>
				</div>
			</div>
			<video autoplay loop class="video-background" muted plays-inline>
		        <source src="<?=$baseUrl?>/images/japantour.mp4" type="video/mp4">
		    </video>
		

		<div class="home-section socially-connected upload-photos">
			<div class="container">
			    <div class="section home-row2">
			        <div class="home-title">
			        	<h4>Upload Photos</h4>
	                    <p>Upload and share your photo to the world</p>
					</div>
					<div class="socials-section">
						<div class="row">
	                        <div class="gallery-content">
	                           <div class="lgt-gallery-photo lgt-gallery-justified home-justified-gallery">
	                            	<?php
	                            	$gallery = Gallery::find()->where(['type' => 'places'])->andWhere(['not','flagger', "yes"])->limit(10)->asarray()->all(); 
	                            	$isEmpty = true;
									foreach($gallery as $gallery_item) {
										$galimname = $gallery_item['image'];
									    if(file_exists($galimname)) {
									        $gallery_item_id = $gallery_item['_id'];
									        $eximg = $galimname;
									        $inameclass = preg_replace('/\\.[^.\\s]{3,4}$/', '', $galimname);
									        
									        $picsize = $imgclass = '';
									        $like_count = Like::getLikeCount((string)$gallery_item_id);
									        $comments = Comment::getAllPostLikeCount((string)$gallery_item_id);
									        $title = $gallery_item['title'];
									        $like_active = '';
									        
									        $time = Yii::$app->EphocTime->comment_time(time(),$gallery_item['created_at']);
									        $puserid = (string)$gallery_item['user_id'];
									        
									        $puserdetails = LoginForm::find()->where(['_id' => $puserid])->one();
								            $galusername = ucfirst($puserdetails['fname']) . ' ' . ucfirst($puserdetails['lname']);
								            $isOwner = false;
									        
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
									        ?> 
									        <div data-src="<?=$eximg?>" class="allow-gallery" data-sizes="<?=$gallery_item_id?>|||Gallery">
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
									    } 
									} ?> 
	                           </div>
	                        </div>
							<?php if(!$isEmpty) { ?>
	                        <div class="text-center">
	                           <a href="<?php echo Yii::$app->urlManager->createUrl(['photostream']); ?>" class="btn-custom mb-10 mt-20 white-text">More Photos</a>
	                        </div>
	                    	<?php } ?>
                     	</div>
					</div>
				</div>
			</div>
		</div> 
		
		<div class="japan-wall">
			<div class="hcontent-holder home-section info-section info1-section japan-japan">
				<div class="container">
					<div class="info-area">
						<div class="row">	
							<div class="col m6 s12 wow slideInLeft">
						        <div class="home-title">
						        	<h4>I am in Japan</h4>			
								</div>
								<p class="para-japan">
	                              I am in Japan Initiative”. Our goal is to create “Japan Photo Gallery” with a million photos. We are looking for photos from the beginning of the exploration of Japan until now. In hope to be listed in 2025 Guinness Book of World Records. The photos collected will be reviewed by an editorial and the best five photos will enter a contest 
	                           </p>
								<a href="<?php echo Yii::$app->urlManager->createUrl(['collections']); ?>" class="btn-custom">More from Japan</a>
							</div>						
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="hcontent-holder home-section info-section info2-section japan-japan">
            <div class="container">
               <div class="info-area">
                  <div class="row">
                     <div class="col m6 s12 right japan">
                        <div class="home-title">
                           <h4>Japan Blogs</h4>
                        </div>
                        <p class="para-japan">The entrance to Japan is a long, winding sandstone canyon known as the Siq (about 2km). There are minor carvings spotted here and there throughout the Siq, but the most impressive sights are the colorful and unusual sandstone patterns in the rock walls. There are also remains of terracotta pipes built into the sides of the canyon that were used in Roman times to carry water.
                           <br /><br />
                           Upon exiting the Siq, visitors can view the jaw-dropping grandeur of the Treasury (al-Khazneh in Arabic). Be sure to note the urn atop the Treasury structure.
                        </p>
                        <a href="<?php echo Yii::$app->urlManager->createUrl(['blog']); ?>" class="btn-custom">More Japan Blogs</a>
                     </div>
                  </div>
               </div>
            </div>
        </div>
		
		<div class="home-section socially-connected">
            <div class="container">
               <div class="section home-row2">
                  <div class="home-title">
                     <h4>Connection</h4>
                     <p>Learn about your travel destination from other travellers</p>
                  </div>
                  <!--   Icon Section-->
                  <div class="socials-section socially-connected">
                     <div class="row">
                        <div class="col s12 m4 wow animated zoomIn" data-wow-delay="500ms" data-wow-duration="1200ms">
                           <div class="icon-block">
                              <img src="<?=$baseUrl?>/images/social-discover.png" class="center-block" alt="img 1">
                              <div class="descholder">
                                 <h3 class="font-25">Discover</h3>
                                 <h6>What to do</h6>
                                 <p>Find out what to do before you head on your travel destination</p>
                              </div>
                           </div>
                        </div>
                        <div class="col s12 m4 wow animated zoomIn" data-wow-delay="500ms" data-wow-duration="1200ms">
                           <div class="icon-block">
                              <img src="<?=$baseUrl?>/images/social-share.png" class="center-block" alt="img 1">
                              <div class="descholder">
                                 <h3 class="font-25">SHARE</h3>
                                 <h6>YOUR EXPERIENCES</h6>
                                 <p>Share your trip experience and photos with the rest of the travel community</p>
                              </div>
                           </div>
                        </div>
                        <div class="col s12 m4 hidden-sm wow animated zoomIn" data-wow-delay="500ms" data-wow-duration="1200ms">
                           <div class="icon-block">
                              <img src="<?=$baseUrl?>/images/social-meet.png" class="center-block" alt="img 1">
                              <div class="descholder">
                                 <h3 class="font-25">MEET</h3>
                                 <h6>LIKE-MINDED PEOPLE</h6>
                                 <p>Meet locals or travellers and get recommendations from locals</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
        </div>

		<div class="hcontent-holder home-section state-section row-icon">
			<div class="container">
				<div class="state-area">
					<div class="row center">
						<div class="col m3 s6 wow bounceInUp" data-wow-duration="0.7s" data-wow-delay="0ms">
							<div class="iconholder">
								<i class="zmdi zmdi-accounts"></i>
							</div>
							<div class="descholder">
								<h3><?=$total_users?></h3>
								<p>Travellers</p>
							</div>
						</div>
						<div class="m3 col s6 wow bounceInUp" data-wow-duration="0.7s" data-wow-delay="3ms">
							<div class="iconholder">
								<i class="mdi mdi-clipboard"></i>
							</div>
							<div class="descholder">
								<h3><?=$total_posts?></h3>
								<p>Guides</p>
							</div>
						</div>
						<div class="m3 col s6 wow bounceInUp" data-wow-duration="0.7s" data-wow-delay="6ms">
							<div class="iconholder">
								<i class="mdi mdi-file-image"></i>
							</div>
							<div class="descholder">
								<h3><?=$total_photos?></h3>
								<p>Photos</p>
							</div>
						</div>
						<div class="m3 col s6 wow bounceInUp" data-wow-duration="0.7s" data-wow-delay="9ms">
							<div class="iconholder">
								<i class="zmdi zmdi-check zmdi-hc-lg"></i>
							</div>
							<div class="descholder">
								<h3></h3>
								<p>Locals</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="hcontent-holder home-section gray-section tours-page tours dine-local localdine-page">
            <div class="container mt-10">
               <div class="home-title">
                  <h4>Dine with Locals</h4>
                  <p class="mt-15">Local cooking offered to travellers by local hosts all over the world. Many of us travel because we want to experience locals culture, dine with local, eat local food and meet local people. Local hosts share meals and at the same time they share insights to each other's cultures and lives.</p>
               </div>
               <div class="tours-section">
                  <div class="row">
                  	<?php
                  	$Localdine = Localdine::find()->Where(['not','flagger', "yes"])->limit(3)->asarray()->all();
                  	if(!empty($Localdine)) { 
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
					} 
					?>
                  </div>
               </div>
            </div>
        </div>
		
		<div class="hcontent-holder home-section info-section info2-section japan-japan stay-withlocal">
            <div class="container">
               <div class="info-area">
                  <div class="row">
                     <div class="col m6 s12 right japan">
                        <div class="home-title">
                           <h4>Stay with Locals</h4> 
                        </div>
                        <p class="para-japan">Quality and affordability value accommodation option for short or long term stays. Every home has a host present and they do more than just hand over keys. They'll help you settle into life in a new place. stay with locals provide a truly affordable and safe way to stay with locals, get to know and meet locals, learn a new language, cooking and about cultures.
                        <br /><br />
                        Stay with locals will Immerse you in local culture when you are traveling. If you want avoid overcrowded tourist attractions and stay in a way too expensive hotel. then stay with locals is for you!!
                        </p>
                        <a href="<?php echo Yii::$app->urlManager->createUrl(['homestay']); ?>" class="btn-custom">More stay with Locals Profiles</a>
                     </div>
                  </div>
               </div>
            </div>
        </div>

        <div class="china-wall">
            <div class="hcontent-holder home-section info-section info1-section camp-local china-japan">
               <div class="container">
                  <div class="info-area">
                     <div class="row">
                        <div class="col m6 s12 wow slideInLeft">
                           <div class="home-title">
                              <h4>Camp with Locals</h4>
                           </div>
                           <p class="para-china">Camping season is nearly upon us and you can enjoy some days and nights under the stars with family and friends in the natural wilderness.In addition, you can enjoy a ra
                              nge of activities and approaches to outdoor accommodation such as canoeing, climbing, fishing, and hunting.
                           <br /><br />
                           YOUR NEXT CAMPING ADVENTURE AWAITS YOU.
                           </p>
                           <a href="<?php echo Yii::$app->urlManager->createUrl(['camping']); ?>" class="btn-custom white-text">More stay with Locals Profiles</a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
        </div>
		
		<?php if(isset($LocalGuid) && !empty($LocalGuid)) { ?>
        <div class="hcontent-holder home-section gray-section guide local-guide">
			<div class="container mt-10">
				<div class="home-title">
					<h4>Local Guide</h4>
                  	<p class="mt-15">Find a guide to let you see the real beauties of the place you are visiting</p>
				</div>
				<div class="localguide-area pt-20">
					<div class="row">
					<?php
					foreach($LocalGuid as $LocalGuid1)
					{
						if(!(isset($LocalGuid1['_id']) && !empty($LocalGuid1['_id']))) {
							continue;
						}

						$idArray = array_values($LocalGuid1['_id']);
						$uid = $idArray[0];
						//$uid = (string)$LocalGuid1['_id']['$id'];
						$guide_id = $LocalGuid1['guide_id'];
						$user_img = $this->context->getimage($uid,'photo');
						if(isset($LocalGuid1['country']) && !empty($LocalGuid1['country']))
						{
							$cntry = $LocalGuid1['country'];
						}
						else
						{
							$cntry = '';
						}
					?>
						<div class="col m4 s12 wow slidInLeft" data-wow-duration="2s" data-wow-delay="3ms" >
							<a href="?r=localguide/index&id=<?=$guide_id?>&address=<?=$cntry?>">
								<div class="localguide-box">
									<div class="imgholder">
										<img src="<?=$user_img?>"/>
										<div class="overlay">
											<span class="licensed-span">Licensed</span>
										</div>
									</div>
									<div class="descholder">
										<h3 style="color: black !important;"><?=$LocalGuid1['fullname']?></h3>
										<p><?=$cntry?></p>									
									</div>
								</div>
							</a>
						</div>
					<?php } ?>
					</div>
					<center><a href="?r=localguide/index" class="btn-custom mb-10">See more guides that you may hire</a></center>
				</div>
			</div>
		</div>
		<?php } ?>
 
		<?php if(isset($tourslist) && !empty($tourslist)) { ?>
		<div class="hcontent-holder home-section gray-section tours-page tours">
			<div class="container mt-10">
		        <div class="home-title">
		        	<h4>Japan Tours</h4>
                  	<p class="mt-15">Tours, things to do, sightseeing&nbsp;tours, day trips and more powered by&nbsp;Viator.</p>
				</div>
				<div class="tours-section">
					<div class="row">
						<?php foreach($tourslist as $tours) { ?>	
						<a href="<?=$tours['ProductURL']?>" target="_new">
							<div class="col m4 s12 wow slideInLeft">
								<div class="tour-box">
									<span class="imgholder"><img src="<?=str_replace('/graphicslib','/graphicslib/thumbs674x446/',$tours['ProductImage'])?>"/></span>
									<span class="descholder">
										<span class="head6"><?=$tours['Group1']?></span>
	                    				<span class="head5"><?=$tours['ProductName']?></span>
										<span class="info">
											<span class="ratings">
												<label>45 Reviews</label>
                           						<span class="clear"></span>
												<img src="<?=$tours['AvgRatingStarURL']?>">
					                        </span>
											<span class="pricing">
												<span class="currency">From USD</span>
	                            				<span class="amount">$<?=$tours['PriceUSD']?></span>
											</span>

										</span>
									</span>
								</div>
							</div>
						</a>
						<?php } ?>
					</div>
					<a href="?r=tours" class="btn-custom">
						Checkout more tours and activities
					</a>
				</div>
			</div>
		</div>
		<?php } ?>	

		<div class="japan-siq">
            <div class="hcontent-holder home-section gray-section news-section">
               <div class="container">
                  <div class="feedback-area">
                     <div class="row">
                        <div class="col m4 s12 wow slideInUp">
                           <div class="feedbackbox">
                              <div class="imgholder">
                                 <img src="<?=$baseUrl?>/images/feedback-1.png"/>
                              </div>
                              <div class="descholder">
                                 <p>Travel is all about new experiences. No matter where you're going, Touristlink gives you opportunity to get a real feel of the culture. Meet up with a local for a coffee or beer,</p>
                              </div>
                           </div>
                        </div>
                        <div class="col m4 s12 wow slideInUp">
                           <div class="feedbackbox">
                              <div class="imgholder">
                                 <img src="<?=$baseUrl?>/images/feedback-2.PNG"/>
                              </div>
                              <div class="descholder">
                                 <p>Travel is all about new experiences. No matter where you're going, Touristlink gives you opportunity to get a real feel of the culture. Meet up with a local for a coffee or beer,</p>
                              </div>
                           </div>
                        </div>
                        <div class="col m4 s12 wow slideInUp hidden-xs">
                           <div class="feedbackbox">
                              <div class="imgholder">
                                 <img src="<?=$baseUrl?>/images/feedback-3.PNG"/>
                              </div>
                              <div class="descholder">
                                 <p>Travel is all about new experiences. No matter where you're going, Touristlink gives you opportunity to get a real feel of the culture. Meet up with a local for a coffee or beer,</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="hcontent-holder home-section map-section">
            	<iframe width="720" height="600" src="https://maps.google.com/maps?width=720&amp;height=600&amp;hl=en&amp;coord=30.3285,35.4444&amp;q=+(japan)&amp;ie=UTF8&amp;t=&amp;z=12&amp;iwloc=B&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"><a href="https://www.maps.ie/coordinates.html">latitude longitude finder</a></iframe>
            </div>
            <footer class="Palsout-footer">
               <div class="footer-cols">
                  <div class="container">
                     <div class="row">
                        <div class="col m6 s12">
                           <h5>Email</h5>
                           <i class="mdi mdi-email white-text"></i>
                           <p>
                              General: <a href="mailto:office@yoursite.com" title="">office@Iaminjapan.com</a>
                              <br />
                              Support: <a href="mailto:support@example.com" title="">support@Iaminjapan.com</a>
                           </p>
                        </div>
                        <div class="col m6 s12">
                           <h5 class="">Follow</h5>
                           <i class="mdi mdi-facebook"></i> &nbsp; &nbsp;
                           <i class="mdi mdi-instagram"></i>
                           <p>
                              <a href="www.facebook.com" target="_blank" title="">Find us on Facebook</a>
                              <br />
                              <a href="www.instagram.com" target="_blank" title="">Get us on Instagram</a>
                           </p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="copyright">
                  <div class="container">
                     <p>&copy; Iaminjapan 2019. All rights reserved.</p>
                  </div>
               </div>
            </footer>
        </div>
	</div>
	
	<div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1 map_modalUniq">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
	 <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

	<?php include('../views/layouts/commonjs.php'); ?>
	<script src="<?=$baseUrl?>/js/loginsignup.js" type="text/javascript"></script>
	<script src="<?=$baseUrl?>/js/wow.js"></script>
	<script type="text/javascript" src="<?=$baseUrl?>/js/homepage.js"></script>
	<script type="text/javascript" src="<?=$baseUrl?>/js/text-slider.js"></script>
	<script type="text/javascript">
		// js to initialize justified gallery
   	setTimeout(
    function() 
    {
		$('.home-justified-gallery').justifiedGallery({
			lastRow: 'nojustify',
			rowHeight: 220,
			maxRowHeight: 220,
			margins: 10,
			sizeRangeSuffixes: {
			     lt100: '_t',
			     lt240: '_m',
			     lt320: '_n',
			     lt500: '',
			     lt640: '_z',
			     lt1024: '_b'
			}
	    });

		$('.home-justified-gallery').css('opacity', '1');

    }, 8000);

    $('.slide').textSlider({
		timeout: 9000,
		slideTime: 750,
		loop: 1
    });
	</script>
    