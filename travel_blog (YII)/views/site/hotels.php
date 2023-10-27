<?php   
use frontend\assets\AppAsset;
use frontend\models\Credits;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
 
$totalcredits = Credits::usertotalcredits();
$total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
$this->title = 'Hotels';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?> 
<div class="page-wrapper  hidemenu-wrapper full-wrapper transheader-wrapper noopened-search transheadereffect transheadereffectall JIS3829 show-sidebar">
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
		<div class="main-content with-lmenu hotels-page main-page transheader-page">
			<div class="combined-column wide-open">
				<div class="content-box">				
					<div class="banner-section">
						<h4>Find your ideal hotel at lowest price</h4>
						<div class="search-whole hotels-search">
							<div class="frow searchrow">
								<div class="row">
									<div class="col l9 m9 s12 stext-holder">
										<div class="sliding-middle-out anim-area underlined location fullwidth">
											<input type="text" placeholder="Enter City i.e. London" id="hotellocationsearch" class="fullwidth getplacelocation placerestriction">
										</div>
									</div>
									<div class="col l3 m3 s12 sbtn-holder">
										<a href="javascript:void(0)" class="btn btn-primary"><i class="zmdi zmdi-search"></i><span>Search</span></a>
									</div>
								</div>
							</div>
							<div class="frow">
								<div class="row row-option">
									<div class="col l4 m4 s12 sbtm-holder">
										<div class="sliding-middle-out anim-area dateinput fullwidth">
											<input type="text" onkeydown="return false;" placeholder="Check in" class="form-control datepickerinput" data-toggle="datepicker" data-query="M" readonly>
										</div>
									</div>
									<div class="col l4 m4 s12 sbtm-holder">
										<div class="sliding-middle-out anim-area dateinput fullwidth">
											<input type="text" onkeydown="return false;" placeholder="Check out" class="form-control datepickerinput" data-toggle="datepicker" data-query="M" readonly>
										</div>
									</div>
									<div class="col l4 m4 s12 sdrop-holder">
										<div class="custom-drop">
											<div class="dropdown dropdown-custom dropdown-xsmall">
												<a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-rooms">
													<i class="mdi mdi-account"></i><span class="sword">Single Room</span> <span class="mdi mdi-menu-down right caret"></span>
												</a>
												<ul class="dropdown-content" id="dropdown-rooms">
													<li><a href="javascript:void(0)"><i class="mdi mdi-account"></i><span class="sword">Single Room</span></a></li>
													<li><a href="javascript:void(0)"><i class="mdi mdi-account-group"></i><span class="sword">Double Room</span></a></li>
												</ul>
											</div>
										</div>	
									
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="container">
						<div class="cbox-desc">
							<div class="outer-holder">
								<div class="row">
									<div class="search-area side-area">
										<a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)">
											<span class="desc-text"><i class="mdi mdi-menu-right"></i>Advanced Search</span>
											<span class="mbl-text"><i class="mdi mdi-tune grey-text mdi-20px"></i></span>
										</a>
										<div class="expandable-area">										
											<div class="content-box bshadow">
												<a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
													<img src="<?=$baseUrl?>/images/cross-icon.png"/>
												</a>
												<div class="cbox-title">
													Narrow your search results
												</div>
												<div class="cbox-desc">
													<div class="srow">	
														<h6>Hotel Class</h6>
														<ul>
															<li>
																<div class="entertosend leftbox">
																	<p>
																		<input type="checkbox" id="five_star" />
                                                                    	<label for="five_star">
                                                                        <span class="stars-holder">
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																		</span>
																		</label>
																	</p>
																</div>
															</li>
															<li>
																<div class="entertosend leftbox">
																	<p>
																		<input type="checkbox" id="four_star" />
                                                                    	<label for="four_star">
                                                                        <span class="stars-holder">
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																		</span>
																		</label>
																	</p>
																</div>
															</li>
															<li>
																<div class="entertosend leftbox">
																	<p>
																		<input type="checkbox" id="three_star" />
                                                                    	<label for="three_star">
                                                                        <span class="stars-holder">
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																		</span>
																		</label>
																	</p>
																</div>
															</li>
															<li>
																<div class="entertosend leftbox">
																	<p>
																		<input type="checkbox" id="two_star" />
                                                                    	<label for="two_star">
                                                                        <span class="stars-holder">
																			<i class="mdi mdi-star"></i>
																			<i class="mdi mdi-star"></i>
																		</span>
																		</label>
																	</p>
																</div>
															</li>
															<li>
																<div class="entertosend leftbox">
																	<p>
																		<input type="checkbox" id="one_star" />
                                                                    	<label for="one_star">
                                                                        <span class="stars-holder">
																			<i class="mdi mdi-star"></i>
																		</span>
																		</label>
																	</p>
																</div>
															</li>									
														</ul>
													</div>
													<div class="srow">	
														<h6>Guest Ratings</h6>
														<ul>
	                                                        <li>
	                                                            <div class="entertosend leftbox">
	                                                                <p>
	                                                                    <input type="checkbox" id="test6">
	                                                                    <label for="test6">
	                                                                        <span class="checks-holder">
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                        </span>
	                                                                    </label>
	                                                                </p>
	                                                            </div>
	                                                        </li>
	                                                        <li>
	                                                            <div class="entertosend leftbox">
	                                                                <p>
	                                                                    <input type="checkbox" id="test7">
	                                                                    <label for="test7">
	                                                                        <span class="checks-holder">
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                        </span>
	                                                                    </label>
	                                                                </p>
	                                                            </div>
	                                                        </li>
	                                                        <li>
	                                                            <div class="entertosend leftbox">
	                                                                <p>
	                                                                    <input type="checkbox" id="test8">
	                                                                    <label for="test8">
	                                                                        <span class="checks-holder">
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                        </span>
	                                                                    </label>
	                                                                </p>
	                                                            </div>
	                                                        </li>
	                                                        <li>
	                                                            <div class="entertosend leftbox">
	                                                                <p>
	                                                                    <input type="checkbox" id="test9">
	                                                                    <label for="test9">
	                                                                        <span class="checks-holder">
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                        </span>
	                                                                    </label>
	                                                                </p>
	                                                            </div>
	                                                        </li>
	                                                        <li>
	                                                            <div class="entertosend leftbox">
	                                                                <p>
	                                                                    <input type="checkbox" id="test10">
	                                                                    <label for="test10">
	                                                                        <span class="checks-holder">
	                                                                            <i class="zmdi zmdi-check-circle active"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                            <i class="zmdi zmdi-check-circle"></i>
	                                                                        </span>
	                                                                    </label>
	                                                                </p>
	                                                            </div>
	                                                        </li>
	                                                    </ul>
													</div>
													<div class="srow">
	                                                    <h6 class="mb-20">Nightly Price</h6>		
	                                                    <div class="rangeslider">
	                                                        <div id="price-slider" class="amount"></div> 
	                                                        <!-- Values -->									
	                                                        <div class="row mb-0 mt-10">
	                                                            <div class="range-value col s6">
	                                                                <span id="price-slider-min" class="min-value">$500</span>
	                                                            </div>
	                                                            <div class="range-value col s6 right-align">
	                                                                <span id="price-slider-max" class="max-value">$10,000</span>
	                                                            </div>
	                                                        </div>
	                                                    </div>
	                                                </div>
	                                                <div class="srow">
	                                                    <h6 class="mb-20">Distance from</h6>
	                                                    <div class="rangeslider">
	                                                        <div id="distance-slider" class="amount"></div>
	                                                        <div class="row mb-0 mt-10">
	                                                            <div class="range-value col s6">
	                                                                <span id="distance-slider-min" class="min-value">500km</span>
	                                                            </div>
	                                                            <div class="range-value col s6 right-align">
	                                                                <span id="distance-slider-max" class="max-value">10000km</span>
	                                                            </div>
	                                                        </div>
	                                                    </div>
	                                                </div>
													<div class="srow">	
														<h6>Amenities</h6>
														<ul class="ul-amenities">
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-spa.png"/><span>Spa</span></a>
															</li>	
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-beach.png"/><span>Beach</span></a>
															</li>
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-wifi.png"/><span>Wifi</span></a>
															</li>
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-breakfast.png"/><span>Breakfast</span></a>
															</li>
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-pool.png"/><span>Pool</span></a>
															</li>
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-spa.png"/><span>Spa</span></a>
															</li>	
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-beach.png"/><span>Beach</span></a>
															</li>
															<li>
																<a href="javascript:void(0)"><img src="<?=$baseUrl?>/images/amenity-breakfast.png"/><span>Breakfast</span></a>
															</li>
														</ul>
													</div>																						
													<div class="btn-holder">
														<a href="javascript:void(0)" class="btn-custom">Reset Filters</a>
													</div>
												</div>
											</div>
											<div class="content-box bshadow">
												<div class="cbox-title">
													Search Hotels
												</div>
												<div class="cbox-desc">												
													<div class="srow">	
														<h6>Search hotel by name</h6>												
														<div class="sliding-middle-out anim-area underlined fullwidth">
															<input type="text" placeholder="Enter hotel name" class="fullwidth">
														</div>
													</div>	
													<div class="srow">	
														<h6>Search hotel by address</h6>												
														<div class="sliding-middle-out anim-area underlined fullwidth">
															<input type="text" placeholder="Enter hotel address" class="fullwidth">
														</div>
													</div>	
													<div class="btn-holder">
														<a href="javascript:void(0)" class="btn-custom">Search</a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="tcontent-holder">
										 <div class="top-stuff">
											<div class="more-actions">
												<a href="javascript:void(0)" class="btn btn-primary">Go to map</a>
												<div class="divider"></div>
												<div class="sorting left">
													<label>Sort by</label>
													<div class="select-holder">
														<select class="select2">									
															<option>Pricing</option>
															<option>Ratings</option>
														</select>
													</div>
												</div>
											</div>
											<h6>1300 hotels found in <span>Japan</span></h6>
										</div>
										<div class="hotel-list">
											<div class="moreinfo-outer">
												<div class="places-content-holder">		
													<div class="list-holder">
														<div class="hotel-list">
															<ul>
																<li>
																	<div class="hotel-li expandable-holder dealli mobilelist">
																		<div class="summery-info">
																			<div class="imgholder"><img src="<?=$baseUrl?>/images/hotel-demo.png"/></div>
																			<div class="descholder">
																				<a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose'),setHideHeader(this,'hotels','hide')">
																					<h4>Hyatt Regency Japan Creek
																					</h4>
																					<div class="clear"></div>
																					<div class="reviews-link">
																						<span class="review-count">54 reviews</span>
																						<span class="checks-holder">
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<label>Excellent - 88/100</label>
																						</span>
																					</div>
																					<span class="address">Japan, Japan(Emirates), United Arab Emirates</span>
																					<span class="distance-info">2.2 miles to City center</span>
																					<span class="moredeals-link">More Info</span>
																				</a>
																				<div class="info-action">
																					<span class="stars-holder">
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																					</span>
																					<div class="clear"></div>
																					<span class="sitename">booking.com</span>
																					<div class="clear"></div>
																					<span class="price">JOD 184*</span>
																					<div class="clear"></div>
																					<a href="javascript:void(0)" class="deal-btn">Book Now <i class="mdi mdi-chevron-right"></i></a>
																				</div>
																			</div>														
																		</div>
																		<div class="expandable-area">
																			<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt'),setHideHeader(this,'hotels','show')"><i class="mdi mdi-close	"></i> Close</a>
																			<div class="clear"></div>
																			<div class="explandable-tabs">
																				<ul class="tabs subtab-menu">
																	                <li class="tab"><a class="active" href="#subtab-details">Details</a></li>
																	                <li class="tab"><a href="#subtab-reviews">Reviews</a></li>
																	                <li class="tab"><a data-which="photo" href="#subtab-photos" data-tab="subtab-photos">Photos</a></li>
																	                <li class="tab lst-li"><a href="#subtab-amenities">Amenities</a></li>
																	            </ul>
																				<div class="tab-content">
																					<div id="subtab-details" class="animated fadeInUp">
																						<div class="subdetail-box">
																							<div class="infoholder">
																								<div class="descholder">
																									<div class="more-holder">
																										<ul class="infoul">
																											<li>
																												<i class="zmdi zmdi-pin"></i>
																												132 Brick Lane | E1 6RU, Japan E1 6RU, Japan
																											</li>
																											<li>
																												<i class="mdi mdi-phone"></i>
																												+44 20 7247 8210
																											</li>
																											<li>
																												<i class="mdi mdi-earth"></i>
																												http://www.yourwebsite.com
																											</li>
																											<li>
																												<i class="mdi mdi-clock-outline"></i>
																												Mon-Fri : 12:00 PM - 10:00 AM
																											</li>
																											<li>
																												<i class="mdi mdi-certificate "></i>
																												Ranked #1 in Japan Hotels
																											</li>										
																										</ul>
																										<div class="tagging" onclick="explandTags(this)">
																											Popular with:
																											<span>point of interest</span>
																											<span>establishment</span>
																										</div>
																									</div>
																								</div>
																							</div>						
																						</div>
																					
																					</div>
																					<div id="subtab-reviews" class="animated fadeInUp">	
																						<div class="reviews-summery">
																							<div class="reviews-people">
																								<ul>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-3.png"/></div>
																											<div class="descholder">
																												<h6>Kelly Mark <span>about 2 weeks ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>We enjoyed the lounge and bar at the Ritz where you are offered many choices for drinks and some pretty elaborate looking dishes of food as well.</p>
																											</div>
																										</div>
																									</li>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-2.png"/></div>
																											<div class="descholder">
																												<h6>John Davior <span>about 8 months ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>If you want a fancy London experience than The Ritz is where you need to go! At least budget for High Tea!</p>
																											</div>
																										</div>
																									</li>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png"/></div>
																											<div class="descholder">
																												<h6>Joe Doe <span>about 11 months ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>I am not at all sure this is the best hotel in London, but it does deserve the reputation as one of the most glamourous.</p>
																											</div>
																										</div>
																									</li>
																									
																								</ul>
																							</div>
																						</div>
																					</div>
																					<div id="subtab-photos" class="tab-pane fade subtab-photos">
																						<div class="photo-gallery">
																							<div class="img-preview">
																								<img src="<?=$baseUrl?>/images/post-img1.jpg"/>
																							</div>
																							<div class="thumbs-img">
																								<ul>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box"><img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/></a></li> <li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box"><img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/></a></li>
																								</ul>
																							</div>
																						</div>
																					</div>
																					<div id="subtab-amenities" class="animated fadeInUp">
																					</div>
																				</div>
																			</div>
																		</div>
																	</div>
																</li>
																<li>
																	<div class="hotel-li expandable-holder dealli mobilelist">
																		<div class="summery-info">
																			<div class="imgholder"><img src="<?=$baseUrl?>/images/hotel-demo.png"/></div>
																			<div class="descholder">
																				<a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose'),setHideHeader(this,'hotels','hide')">
																					<h4>Hyatt Regency Japan Creek
																					</h4>				
																					<div class="clear"></div>
																					<div class="reviews-link">
																						<span class="review-count">54 reviews</span>
																						<span class="checks-holder">
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<label>Excellent - 88/100</label>
																						</span>
																					</div>
																					<span class="address">Japan, Japan(Emirates), United Arab Emirates</span>
																					<span class="distance-info">2.2 miles to City center</span>
																					<span class="moredeals-link">More Info</span>
																				</a>
																				<div class="info-action">
																					<span class="stars-holder">
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																					</span>
																					<div class="clear"></div>
																					<span class="sitename">booking.com</span>
																					<div class="clear"></div>
																					<span class="price">JOD 184*</span>
																					<div class="clear"></div>
																					<a href="javascript:void(0)" class="deal-btn waves-effect">Book Now <i class="mdi mdi-chevron-right"></i></a>
																				</div>
																			</div>														
																		</div>
																		<div class="expandable-area">
																			<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt'),setHideHeader(this,'hotels','show')"><i class="mdi mdi-close	"></i> Close</a>
																			<div class="clear"></div>
																			<div class="explandable-tabs">
																				<ul class="tabs subtab-menu">
																					<li class="tab"><a class="active" href="#subtab-details-first">Details</a></li>
																	                <li class="tab"><a href="#subtab-reviews-first">Reviews</a></li>
																	                <li class="tab"><a data-which="photo" href="#subtab-photos-first" data-tab="subtab-photos">Photos</a></li>
																	                <li class="tab lst-li"><a href="#subtab-amenities-first">Amenities</a></li>
																				</ul>
																				<div class="tab-content">
																					<div id="subtab-details-first" class="tab-pane fade active in">
																						<div class="subdetail-box">
																							<div class="infoholder">
																								<div class="descholder">
																									<div class="more-holder">
																										<ul class="infoul">
																											<li>
																												<i class="zmdi zmdi-pin"></i>
																												132 Brick Lane | E1 6RU, Japan E1 6RU, Japan
																											</li>
																											<li>
																												<i class="mdi mdi-phone"></i>
																												+44 20 7247 8210
																											</li>
																											<li>
																												<i class="mdi mdi-earth"></i>
																												http://www.yourwebsite.com
																											</li>
																											<li>
																												<i class="mdi mdi-clock-outline"></i>
																												Mon-Fri : 12:00 PM - 10:00 AM
																											</li>
																											<li>
																												<i class="mdi mdi-certificate "></i>
																												Ranked #1 in Japan Hotels
																											</li> 
																										</ul>
																										<div class="tagging" onclick="explandTags(this)">
																											Popular with:
																											<span>point of interest</span>
																											<span>establishment</span>
																										</div>
																									</div>
																								</div>
																							</div>						
																						</div>
																					</div>
																					 <div id="subtab-reviews-first" class="tab-pane fade animated fadeInUp">
																						<div class="reviews-summery">
																							<div class="reviews-people">
																								<ul>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-3.png"/></div>
																											<div class="descholder">
																												<h6>Kelly Mark <span>about 2 weeks ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>We enjoyed the lounge and bar at the Ritz where you are offered many choices for drinks and some pretty elaborate looking dishes of food as well.</p>
																											</div>
																										</div>
																									</li>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-2.png"/></div>
																											<div class="descholder">
																												<h6>John Davior <span>about 8 months ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>If you want a fancy London experience than The Ritz is where you need to go! At least budget for High Tea!</p>
																											</div>
																										</div>
																									</li>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png"/></div>
																											<div class="descholder">
																												<h6>Joe Doe <span>about 11 months ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>I am not at all sure this is the best hotel in London, but it does deserve the reputation as one of the most glamourous.</p>
																											</div>
																										</div>
																									</li>
																								</ul>
																							</div>
																						</div>
																					</div>
																					<div id="subtab-photos-first" class="tab-pane fade subtab-photos animated fadeInUp">
																						<div class="photo-gallery">
																							<div class="img-preview">
																								<img src="<?=$baseUrl?>/images/post-img1.jpg"/>
																							</div>
																							<div class="thumbs-img">
																								<ul>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box"><img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box"><img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/></a></li>
																								</ul>
																							</div>
																						</div>
																					</div>
																					<div id="subtab-amenities-first" class="tab-pane fade animated fadeInUp">
																					</div>
																				</div>
																			</div>
																		</div>
																	</div>
																</li>
																<li>
																	<div class="hotel-li expandable-holder dealli mobilelist">
																		<div class="summery-info">
																			<div class="imgholder"><img src="<?=$baseUrl?>/images/hotel-demo.png"/></div>
																			<div class="descholder">
																				<a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose'),setHideHeader(this,'hotels','hide')">
																					<h4>Hyatt Regency Japan Creek
																					</h4>				
																					<div class="clear"></div>
																					<div class="reviews-link">
																						<span class="review-count">54 reviews</span>
																						<span class="checks-holder">
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<i class="zmdi zmdi-check-circle active"></i>
																							<label>Excellent - 88/100</label>
																						</span>
																					</div>
																					<span class="address">Japan, Japan(Emirates), United Arab Emirates</span>
																					<span class="distance-info">2.2 miles to City center</span>
																					<span class="moredeals-link">More Info</span>
																				</a>
																				<div class="info-action">
																					<span class="stars-holder">
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																						<i class="mdi mdi-star active"></i>
																					</span>
																					<div class="clear"></div>
																					<span class="sitename">booking.com</span>
																					<div class="clear"></div>
																					<span class="price">JOD 184*</span>
																					<div class="clear"></div>
																					<a href="javascript:void(0)" class="deal-btn">Book Now <i class="mdi mdi-chevron-right"></i></a>
																				</div>
																			</div>														
																		</div>
																		<div class="expandable-area">
																			<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt'),setHideHeader(this,'hotels','show')"><i class="mdi mdi-close	"></i> Close</a>
																			<div class="clear"></div>
																			<div class="explandable-tabs">
																				<ul class="tabs subtab-menu">
																	                <li class="tab"><a class="active" href="#subtab-details-second">Details</a></li>
																	                <li class="tab"><a href="#subtab-reviews-second">Reviews</a></li>
																	                <li class="tab"><a data-which="photo" href="#subtab-photos-second" data-tab="subtab-photos">Photos</a></li>
																	                <li class="tab lst-li"><a href="#subtab-amenities-second">Amenities</a></li>
																	            </ul>
																				<div class="tab-content">
																					<div id="subtab-details-second" class="tab-pane fade active in animated fadeInUp">
																						<div class="subdetail-box">
																							<div class="infoholder">
																								<div class="descholder">
																									<div class="more-holder">
																										<ul class="infoul">
																											<li>
																												<i class="zmdi zmdi-pin"></i>
																												132 Brick Lane | E1 6RU, Japan E1 6RU, Japan
																											</li>
																											<li>
																												<i class="mdi mdi-phone"></i>
																												+44 20 7247 8210
																											</li>
																											<li>
																												<i class="mdi mdi-earth"></i>
																												http://www.yourwebsite.com
																											</li>
																											<li>
																												<i class="mdi mdi-clock-outline"></i>
																												Mon-Fri : 12:00 PM - 10:00 AM
																											</li>
																											<li>
																												<i class="mdi mdi-certificate "></i>
																												Ranked #1 in Japan Hotels
																											</li> 
																										</ul>
																										<div class="tagging" onclick="explandTags(this)">
																											Popular with:
																											<span>point of interest</span>
																											<span>establishment</span>
																										</div>
																									</div>
																								</div>
																							</div>						
																						</div>
																					</div>
																					<div id="subtab-reviews-second" class="tab-pane fade animated fadeInUp">
																						<div class="reviews-summery">
																							<div class="reviews-people">
																								<ul>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-3.png"/></div>
																											<div class="descholder">
																												<h6>Kelly Mark <span>about 2 weeks ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>We enjoyed the lounge and bar at the Ritz where you are offered many choices for drinks and some pretty elaborate looking dishes of food as well.</p>
																											</div>
																										</div>
																									</li>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-2.png"/></div>
																											<div class="descholder">
																												<h6>John Davior <span>about 8 months ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>If you want a fancy London experience than The Ritz is where you need to go! At least budget for High Tea!</p>
																											</div>
																										</div>
																									</li>
																									<li>
																										<div class="reviewpeople-box">
																											<div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png"/></div>
																											<div class="descholder">
																												<h6>Joe Doe <span>about 11 months ago</span></h6>
																												<div class="stars-holder">	
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/filled-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																													<img src="<?=$baseUrl?>/images/blank-star.png"/>
																												</div>
																												<div class="clear"></div>
																												<p>I am not at all sure this is the best hotel in London, but it does deserve the reputation as one of the most glamourous.</p>
																											</div>
																										</div>
																									</li>
																								</ul>
																							</div>
																						</div>
																					</div>
																					<div id="subtab-photos-second" class="tab-pane fade subtab-photos animated fadeInUp">
																						<div class="photo-gallery">
																						
																							<div class="img-preview">
																								<img src="<?=$baseUrl?>/images/post-img1.jpg"/>
																							</div>
																							<div class="thumbs-img">
																								<ul>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box"><img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img class="himg" src="<?=$baseUrl?>/images/post-img1.jpg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img2.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="vimg-box"><img src="<?=$baseUrl?>/images/post-img3.jpg" class="vimg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img4.jpg" class="himg"/></a></li>
																									<li><a href="javascript:void(0)" onclick="previewImage(this)" class="himg-box"><img src="<?=$baseUrl?>/images/post-img5.jpg" class="himg"/></a></li>
																								</ul>
																							</div>
																						</div>
																					</div>
																					<div id="subtab-amenities-second" class="tab-pane fade animated fadeInUp">
																					</div>
																				</div>
																			</div>
																		</div>
																	</div>
																</li>
															</ul>
															<div class="pagination">
																<div class="link-holder">
																	<a href="javascript:void(0)"><i class="mdi mdi-arrow-left-bold-circle"></i> Prev</a>
																</div>
																<div class="link-holder">
																	<a href="javascript:void(0)">Next <i class="mdi mdi-arrow-right-bold-circle"></i></a>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="moreinfo-box">
													<div class="fake-header">
														<div class="page-name"><a href="javascript:void(0)" onclick="closePlacesMoreInfo(this),setHideHeader(this,'hotels','show')"><i class="mdi mdi-arrow-left"></i>>Back to list</a></div>
													</div>														
													<div class="infoholder nice-scroll">
														<div class="imgholder"><img src="<?=$baseUrl?>/images/hotel1.png"/></div>
														<div class="descholder">
															<h4>The Guest House</h4>
															<div class="clear"></div>
															<div class="reviews-link">
																<span class="checks-holder">
																	<i class="mdi mdi-star active"></i>
																	<i class="mdi mdi-star active"></i>
																	<i class="mdi mdi-star active"></i>
																	<i class="mdi mdi-star active"></i>
																	<i class="mdi mdi-star"></i>
																	<label>34 Reviews</label>
																</span>
															</div>
															<span class="distance-info">Middle Eastem &amp; African, Mediterranean</span>
															<div class="clear"></div>
															<div class="more-holder">
																<ul class="infoul">
																	<li>
																		<i class="zmdi zmdi-pin"></i>
																		132 Brick Lane | E1 6RU, Japan E1 6RU, Japan
																	</li>
																	<li>
																		<i class="mdi mdi-phone"></i>
																		+44 20 7247 8210
																	</li>
																	<li>
																		<i class="mdi mdi-earth"></i>
																		http://www.yourwebsite.com
																	</li>
																	<li>
																		<i class="mdi mdi-clock-outline"></i>
																		Today, 12:00 PM - 12:00 AM
																	</li>
																	<li>
																		<i class="mdi mdi-certificate "></i>
																		Ranked #1 in Japan Hotels
																	</li>										
																</ul>
																<div class="tagging" onclick="explandTags(this)">
																	Popular with:
																	<span>Budget</span>
																	<span>Foodies</span>
																	<span>Family</span>
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
<script src='<?=$baseUrl?>/js/wNumb.min.js'></script>
<script type="text/javascript">
	$( window ).scroll(function() {
	  	$scrolledInt = $(window).scrollTop();
		if($scrolledInt <100) {
			$('.page-wrapper').addClass('transheadereffect');
		} else {
			$('.page-wrapper').removeClass('transheadereffect');
		}
	});
</script>
<?php $this->endBody() ?> 