<?php 
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');
$this->title = 'Tours';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
	<div class="page-wrapper  menutransheader-wrapper">
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
		<div class="fixed-layout">
			<div class="main-content with-lmenu transheader-page tours-page main-page">
				<div class="combined-column wide-open">
					<div class="content-box bshadow">				
						<div class="banner-section">
							<h4>Tours, sighting, activities and thing to do</h4>						
							<div class="search-whole">
								<label>Find an attraction</label>
								<div class="sliding-middle-out anim-area underlined whiten">
									<select id="chooseCountry" class="select2 chooseCountry">
										<option>India</option>
										<option>Canada</option>
										<option>China</option>
									</select>
								</div>
								<div class="sliding-middle-out anim-area underlined whiten">
									<select id="chooseCity" class="select2 chooseCity">
										<option>City-1</option>
										<option>City-2</option>
										<option>City-3</option>
									</select>
								</div>
								<a href="javascript:void(0)" class="btn btn-primary">Go!</a>
							</div>
						</div>
						<div class="cbox-desc">
							<div class="tours-section">
								<h4>Lyon</h4>
								<div class="row">	
									<div class="col-lg-4 col-sm-4 col-xs-12 tour-holder">
										<a class="tour-box" href="javascript:void(0)">
											<span class="imgholder"><img src="<?=$baseUrl?>/images/tours1.jpg"/></span>
											<span class="descholder">
												<span class="head6">Private &amp; custom tours</span>
												<span class="head5">Beaujolais Half Day Wine Tasting Tour</span>
												<span class="info">
													<span class="ratings">
														<label>45 Reviews</label>
														<span class="clear"></span>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
													</span>
													<span class="pricing">
														<span class="currency">From usd</span>
														<span class="amount">$234.23</span>
													</span>
												</span>
											</span>
										</a>
									</div>
									<div class="col-lg-4 col-sm-4 col-xs-12 tour-holder">
										<a class="tour-box" href="javascript:void(0)">
											<span class="imgholder"><img src="<?=$baseUrl?>/images/tours2.jpg"/></span>
											<span class="descholder">
												<span class="head6">Private &amp; custom tours</span>
												<span class="head5">Beaujolais Half Day Wine Tasting Tour</span>
												<span class="info">
													<span class="ratings">
														<label>45 Reviews</label>
														<span class="clear"></span>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
													</span>
													<span class="pricing">
														<span class="currency">From usd</span>
														<span class="amount">$234.23</span>
													</span>
												</span>
											</span>
										</a>
									</div>
									<div class="col-lg-4 col-sm-4 col-xs-12 tour-holder">
										<a class="tour-box" href="javascript:void(0)">
											<span class="imgholder"><img src="<?=$baseUrl?>/images/tours3.jpg"/></span>
											<span class="descholder">
												<span class="head6">Private &amp; custom tours</span>
												<span class="head5">Beaujolais Half Day Wine Tasting Tour</span>
												<span class="info">
													<span class="ratings">
														<label>45 Reviews</label>
														<span class="clear"></span>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
													</span>
													<span class="pricing">
														<span class="currency">From usd</span>
														<span class="amount">$234.23</span>
													</span>
												</span>
											</span>
										</a>
									</div>
								</div>
							</div>
							<div class="tours-section">
								<h4>Brussels</h4>
								<div class="row">	
									<div class="col-lg-4 col-sm-4 col-xs-12 tour-holder">
										<a class="tour-box" href="javascript:void(0)">
											<span class="imgholder"><img src="<?=$baseUrl?>/images/tours4.jpg"/></span>
											<span class="descholder">
												<span class="head6">Private &amp; custom tours</span>
												<span class="head5">Beaujolais Half Day Wine Tasting Tour</span>
												<span class="info">
													<span class="ratings">
														<label>45 Reviews</label>
														<span class="clear"></span>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
													</span>
													<span class="pricing">
														<span class="currency">From usd</span>
														<span class="amount">$234.23</span>
													</span>
												</span>
											</span>
										</a>
									</div>
									<div class="col-lg-4 col-sm-4 col-xs-12 tour-holder">
										<a class="tour-box" href="javascript:void(0)">
											<span class="imgholder"><img src="<?=$baseUrl?>/images/tours5.jpg"/></span>
											<span class="descholder">
												<span class="head6">Private &amp; custom tours</span>
												<span class="head5">Beaujolais Half Day Wine Tasting Tour</span>
												<span class="info">
													<span class="ratings">
														<label>45 Reviews</label>
														<span class="clear"></span>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
													</span>
													<span class="pricing">
														<span class="currency">From usd</span>
														<span class="amount">$234.23</span>
													</span>
												</span>
											</span>
										</a>
									</div>
									<div class="col-lg-4 col-sm-4 col-xs-12 tour-holder">
										<a class="tour-box" href="javascript:void(0)">
											<span class="imgholder"><img src="<?=$baseUrl?>/images/tours6.jpg"/></span>
											<span class="descholder">
												<span class="head6">Private &amp; custom tours</span>
												<span class="head5">Beaujolais Half Day Wine Tasting Tour</span>
												<span class="info">
													<span class="ratings">
														<label>45 Reviews</label>
														<span class="clear"></span>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
														<i class="mdi mdi-star"></i>
													</span>
													<span class="pricing">
														<span class="currency">From usd</span>
														<span class="amount">$234.23</span>
													</span>
												</span>
											</span>
										</a>
									</div>
								</div>
							</div>
						
						</div>
					</div>
				</div>
			<?php include('../views/layouts/ads.php'); ?>
			</div>
		</div>
		<?php include('../views/layouts/footer.php'); ?>
	</div>	
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

	<?php include('../views/layouts/commonjs.php'); ?>
	<?php $this->endBody() ?> 
