<?php   
use yii\helpers\Url;
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');
$this->title = 'Advert Manager';
$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<script type="text/javascript" src="<?=$baseUrl?>/js/advertisement.js"></script>

<div class="page-wrapper hidemenu-wrapper adpage full-wrapper noopened-search show-sidebar show-sidebar">
    <div class="header-section">
        <?php include('../views/layouts/header.php'); ?>
    </div>
    <div class="floating-icon">
        <div class="scrollup-btnbox anim-side btnbox scrollup-float">
            <div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>			
        </div>        
    </div>
    <div class="clear"></div>
	<?php include('../views/layouts/leftmenu.php'); ?>
	<div class="fixed-layout ipad-mfix">
		<div class="main-content advert-page main-page full-page">
			<div class="combined-column wide-open">
				<div class="travadvert-banner">					
					<div class="overlay"></div>
					<div class="banner-section">
						<div class="container">
							<h4>Advertise on Iaminjapan</h4>
							<p>Create self service advert using our Advert Manager</p>
							<div class="btn-holder pad-b">
								<a href="<?php echo Url::to(['create']);?>" class="btn btn-primary btn-md btn-white waves-effect waves-light">Create Advert</a>
								<a href="<?php echo Url::to(['manage']);?>" class="btn btn-primary btn-md waves-effect waves-light">Manage Advert</a>
							</div>
						</div>
					</div>
				</div>
				<div class="travadvert-content">
					<div class="container">
						<h4>Boost your business sale with us!</h4>
						<h6>Advertising on Iaminjapan helps you drive valuable traffic to your business</h6>
						<div class="travad-services">
							<div class="row">
								<div class="col l3 m3 s2 sbox-holder">
									<div class="servicebox">
										<img src="<?=$baseUrl?>/images/brand-icon.png"/>
										<h5><span>Raise</span>Brand Awareness</h5>
									</div>
								</div>
								<div class="col l3 m3 s2 sbox-holder">
									<div class="servicebox">
										<img src="<?=$baseUrl?>/images/customized-icon.png"/>
										<h5><span>Create</span>Customized Ad</h5>
									</div>
								</div>
								<div class="col l3 m3 s2 sbox-holder">
									<div class="servicebox">
										<img src="<?=$baseUrl?>/images/webconvert-icon.png"/>
										<h5><span>Boost</span>Website Conversion</h5>
									</div>
								</div>
								<div class="col l3 m3 s2 sbox-holder">
									<div class="servicebox">
										<img src="<?=$baseUrl?>/images/engagement-icon.png"/>
										<h5><span>Increase</span>Page Engagement</h5>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="travadvert-details">
						<div class="container">
							<h4>Create easily effective and customized ads using our self service Advert Manager</h4>
							<h6>Try our self service advertisement</h6>
							<div class="details">
								<div class="detail-box pad-left">
										<div class="imgholder">
												<img src="<?=$baseUrl?>/images/addetail-brand.png"/>
										</div>
										<div class="descholder">
												<h5><span>Raise</span>Brand Awareness</h5>
												<ul>
														<li>
																<span><i class="mdi mdi-chevron-right"></i></span>
																Place your product or brand name in front of many interested people to highlight your product
														</li>
														<li>
																<span><i class="mdi mdi-chevron-right"></i></span>
																Raise awareness and increase your product or brand name exposure
														</li>
														<li>
																<span><i class="mdi mdi-chevron-right"></i></span>
																Create personalized text that easily identify your brand name or product
														</li>										
												</ul>
												<p>Pay per click or enagement, free impresion</p>
												<p>Measure and optimized your advert campaigns performance using your advert manager dashboard</p>
												<p>Ad available on desktop, notepad and mobile</p>
										</div>
								</div>
								<div class="detail-box pad-left">
									<div class="imgholder">
										<img src="<?=$baseUrl?>/images/addetail-engagement.png"/>
									</div>
									<div class="descholder">
										<h5><span>Increase</span>Page engagement</h5>
										<ul>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Get new likes to your page
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Get people to share your page
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Get feedback from people comments on your page posts
											</li>										
										</ul>
										<p>Monitor your performance with Real-Time analytics</p>
										<p>Measure and optimized your advert campaigns performance using your advert manager dashboard</p>
										<p>Launch your advert campaign in minutes</p>
									</div>
								</div>
								<div class="detail-box pad-left">
									<div class="imgholder">
										<img src="<?=$baseUrl?>/images/addetail-customad.png"/>
									</div>
									<div class="descholder">
										<h5><span>Create</span>Customised Ads</h5>
										<ul>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Create customized ad with headline, description and image
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Choose your target audience and drive the right traffic to your website
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Display your website URL on your ad and generate quilty leads
											</li>										
										</ul>
										<p>Pay per click or engagement, free impresison</p>
										<p>Measure and optimized your advert campaigns performance using your advert manager dashboard</p>
										<p>Ad available on desktop, notepad and mobile</p>
									</div>
								</div>
								<div class="detail-box pad-left">
									<div class="imgholder">
										<img src="<?=$baseUrl?>/images/addetail-webconvert.png"/>
									</div>
									<div class="descholder">
										<h5><span>Boost</span>Website Conversion</h5>
										<ul>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Choose from a selection of action button to use for your ad
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Get people to take action on your ad and get your visitors to do what you want them to do
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Create customized ad with headline,  description and image
											</li>										
										</ul>
										<p>Monitor your performance with Real-Time analytics</p>
										<p>Measure and optimized your advert campaigns performance using your advert manager dashboard</p>
										<p>Launch your advert campaign in minutes</p>
									</div>
								</div>
								<div class="detail-box pad-left">
									<div class="imgholder">
										<img src="<?=$baseUrl?>/images/addetail-engagement.png"/>
									</div>
									<div class="descholder">
										<h5><span>Aquire</span>Page Endorsement</h5>
										<ul>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Invite people to endorse your page
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Powerful ad that drive people to your page
											</li>
											<li>
												<span><i class="mdi mdi-chevron-right"></i></span>
												Highly endorsed page are more popular
											</li>										
										</ul>
										<p>Monitor your performance with Real-Time analytics</p>
										<p>Measure and optimized your advert campaigns performance using your advert manager dashboard</p>
										<p>Launch your advert campaign in minutes</p>
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
