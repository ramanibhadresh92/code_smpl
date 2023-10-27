<?php

use frontend\assets\AppAsset;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="content-box hotels-page" id="rp_hotels_filter">
	<div class="search-area side-area">
		<a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)"><i class="mdi mdi-tune grey-text mdi-20px"></i></a>
		<div class="expandable-area">										
			<div class="content-box bshadow">
				<a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
					<img src="<?=$baseUrl?>/images/cross-icon.png"/>
				</a>
				<div class="cbox-title nborder">
					Narrow your search results
				</div>
				<div class="cbox-desc">
					<div class="srow">	
						<h6>Hotel Class</h6>
						<ul>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="hotelclasstest1">
									<label for="hotelclasstest1">
										<span class="stars-holder">
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="hotelclasstest2">
									<label for="hotelclasstest2">
										<span class="stars-holder">
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="hotelclasstest3">
									<label for="hotelclasstest3">
										<span class="stars-holder">
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>																
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="hotelclasstest4">
									<label for="hotelclasstest4">
										<span class="stars-holder">
											<i class="mdi mdi-star"></i>
											<i class="mdi mdi-star"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="hotelclasstest5">
									<label for="hotelclasstest5">
										<span class="stars-holder">
											<i class="mdi mdi-star"></i>																
										</span>
									</label>
								</div>
							</li>													
						</ul>
					</div>
					<div class="srow">	
						<h6>Guest Ratings</h6>
						<ul>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="guestratingstest1">
									<label for="guestratingstest1">
										<span class="checks-holder">
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="guestratingstest2">
									<label for="guestratingstest2">
										<span class="checks-holder">
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="guestratingstest3">
									<label for="guestratingstest3">
										<span class="checks-holder">
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle"></i>
											<i class="zmdi zmdi-check-circle"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="guestratingstest4">
									<label for="guestratingstest4">
										<span class="checks-holder">
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle"></i>
											<i class="zmdi zmdi-check-circle"></i>
											<i class="zmdi zmdi-check-circle"></i>
										</span>
									</label>
								</div>
							</li>
							<li>
								<div class="h-checkbox entertosend leftbox">
									<input type="checkbox" id="guestratingstest5">
									<label for="guestratingstest5">
										<span class="checks-holder">
											<i class="zmdi zmdi-check-circle active"></i>
											<i class="zmdi zmdi-check-circle"></i>
											<i class="zmdi zmdi-check-circle"></i>
											<i class="zmdi zmdi-check-circle"></i>
											<i class="zmdi zmdi-check-circle"></i>
										</span>
									</label>
								</div>
							</li>													
						</ul>
					</div> 
					<div class="srow row">	
						<h6>Nightly Price</h6>
						<div id="price-slider-min" class="left-align col s6 m6 l6 xl6">$500</div>
						<div id="price-slider-max" class="right-align col s6 m6 l6 xl6">$10,000</div>
						<div style="clear: both;"></div>
						<div id="price-slider" class="amount" style="margin-top: 10px;"></div> 
						<!-- <div id="nightlypriceslider" style="margin-top: 10px;"></div> -->
					</div>
					<div class="srow row">	
						<h6>Distance from</h6>	
						<div class="sliding-middle-out anim-area underlined fullwidth">
							<select class="select2">
								<option>City Center</option>
								<option>Palace</option>														
								<option>Bus Station</option>														
								<option>Railway Station</option>													
							</select>
						</div><br/><br/>
						<div id="distance-slider-min" class="left-align col s6 m6 l6 xl6">500km</div>
						<div id="distance-slider-max" class="right-align col s6 m6 l6 xl6">10000km</div>
						<div style="clear: both;"></div>
						<div id="distance-slider" class="amount" style="margin-top: 10px;"></div>
						<!-- <div id="distancefromslider" class="amount" style="margin-top: 10px;"></div> -->
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
						<a href="javascript:void(0)" class="btn btn-primary btn-md">Reset Filters</a>
					</div>
				</div>
			</div>
			<div class="content-box bshadow">
				<div class="cbox-title nborder">
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
						<input type="text" placeholder="Enter hotel address" class="materialize-textarea md_textarea item_address" id="placesearchhotel" data-query="M"  onfocus="filderMapLocationModal(this)" autocomplete='off'/>
					</div>	
					<div class="btn-holder">
						<a href="javascript:void(0)" class="btn btn-primary btn-md">Search</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="content-box">
	<div class="mbl-tabnav">
		<a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> <h6>Hotels</h6>
	</div>
	<div class="placesection redsection">											
		<div class="cbox-desc hotels-page np">
			<div class="tcontent-holder moreinfo-outer">
				<div id="all-hotels" class="top-stuff top-graybg">
					<div class="more-actions">
						<div class="sorting left">
							<label>Sort by</label>
							<div class="select-holder">
								<select class="select2">	
									<option>Pricing</option>
									<option>Ratings</option>
								</select>
							</div>
						</div>
						<ul class="tabs tabsnew text-right" style="width: auto; overflow: hidden;">
							<li><a href="javascript:void(0)" class="manageMap" onclick="openMapSection(this,'lodge')"><i class="zmdi zmdi-pin"></i> Map</a></li>
						</ul>														
					</div>
					<h6>Hotels found in <span><?=$placefirst?></span></h6>
				</div>
				<div class="places-content-holder">														
					<div class="map-holder">
						<iframe src="https://maps.google.com/maps?key=<?=$GApiKeyP?>&q=lodging+in+<?=$placetitle?>&output=embed" allowfullscreen="" width="600" height="450" frameborder="0"></iframe>
						<a href="javascript:void(0)" class="overlay" onclick="expandMap(this,'#all-hotels')"></a>
						<a href="javascript:void(0)" class="closelink" onclick="shrinkMap(this)"><i class="mdi mdi-close	"></i> Close</a>
					</div>
					<div class="list-holder">
						<div class="hotel-list">
							<ul>
							<?php if($ql == 'OK'){
								$hotel = isset($rs['results']) ? $rs['results'] : array();
								for($i=0;$i<20;$i++){
									if(isset($hotel[$i]['place_id']) && !empty($hotel[$i]['place_id'])){
									$placeid = $hotel[$i]['place_id'];
									$pieces = $hotel[$i]['types'];
									if(empty($hotel[$i]['photos'][0]['photo_reference'])) {
										$img = '';
										$imgclass = 'himg';
									} else {
										$ref = $hotel[$i]['photos'][0]['photo_reference'];
										$width = $hotel[$i]['photos'][0]['width'];
										$height = $hotel[$i]['photos'][0]['height'];
										$img = "https://maps.googleapis.com/maps/api/place/photo?maxheight=200&photoreference=$ref&key=$GApiKeyP";
										if($width > $height){$imgclass = 'himg';}
										else if($height > $width){$imgclass = 'vimg';}
										else{$imgclass = 'himg';}
									}
									
									$urlhotel="https://maps.googleapis.com/maps/api/place/details/json?placeid=$placeid&key=$GApiKeyP";
									$ch = curl_init();
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									curl_setopt($ch, CURLOPT_URL,$urlhotel);
									$result=curl_exec($ch);
									curl_close($ch);
									$rss = json_decode($result, true);
									$qll = $rss['status'];
									if(isset($rss['result']) && !empty($rss['result'])) {
										$hotell = $rss['result'];
										if(isset($hotell['address_components'][0]['long_name']) && !empty($hotell['address_components'][0]['long_name'])) {
											$shadr = $hotell['address_components'][0]['long_name'];
										} else {
											$shadr = 'Not Found';
										}
										if(isset($hotell['vicinity']) && !empty($hotell['vicinity'])) {
											$adr = $hotell['vicinity'];
										} else {
											$adr = 'Not Found';
										}
										if(isset($hotell['website']) && !empty($hotell['website'])) {
											$website = $hotell['website'];
										} else {
											$website = 'Not Found';
										}
										if(isset($hotell['international_phone_number']) && !empty($hotell['international_phone_number'])) {
											$ipn = $hotell['international_phone_number'];
										} else {
											$ipn = 'Not Found';
										}
									} else {
										$shadr = $adr = $website = $ipn = 'Not Found';
									}
							?>
	<li>
		<div class="hotel-li expandable-holder dealli mobilelist">
			<div class="summery-info">
				<div class="imgholder <?=$imgclass?>-box"><img src="<?=$img?>" class="<?=$imgclass?>"/></div>
				<div class="descholder">
					<a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose')">
						<h4><?=$hotel[$i]['name']?></h4>
						<div class="clear"></div>
						<div class="reviews-link">
							<span class="review-count"><?=rand(1,20);?> Reviews</span>
							<?php if(isset($hotel[$i]['rating']) && !empty($hotel[$i]['rating'])){ ?>
							<span class="checks-holder">
								<?php for($j=0;$j<5;$j++){ ?>
									<i class="mdi mdi-star <?php if($j < $hotel[$i]['rating']){ ?>active<?php } ?>"></i>
								<?php } ?>
							</span>
							<?php } ?>
						</div>
						<span class="address"><?=$shadr?></span>
						<span class="distance-info"><i class="mdi mdi-phone"></i> <?=$ipn?></span>
						<?php if(isset($pieces) && !empty($pieces)){ ?>
						<div class="more-holder">
							<div class="tagging" onclick="explandTags(this)">
								Popular with:
								<?php $healthy = array("restaurant", "hotel", "lodging");
								$pieces = str_replace($healthy, '', $pieces);
								foreach($pieces as $element) {
									if(isset($element) && !empty($element)) {
										echo "<span>".$element."</span> ";
									}
								} ?>
							</div>
						</div>
						<?php } ?>
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
						<span class="price">USD <?=rand(75,150)?>*</span>
						<div class="clear"></div>
						<a href="<?=$website?>" target="_new" class="deal-btn">Book Now <i class="mdi mdi-chevron-right"></i></a>
					</div>
				</div>
			</div>
			<div class="expandable-area">
				<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt')"><i class="mdi mdi-close	"></i> Close</a>
				<div class="clear"></div>
				<div class="explandable-tabs">
					<ul class="tabs tabsnew subtab-menu" style="width: 70% !important">
						<li class="tab"><a href="#subtab-details-<?=$i?>">Additional Info</a></li>
						<li class="tab"><a href="#subtab-reviews-<?=$i?>">Reviews</a></li>
						<li class="tab"><a data-which="photo" href="#subtab-photos-<?=$i?>" data-tab="subtab-photos">Photos</a></li>
						<li class="tab"><a href="#subtab-amenities-<?=$i?>">Amenities</a></li>
					</ul>
					<div class="tab-content">
						<div id="subtab-details-<?=$i?>" class="">
							<div class="subdetail-box">
								<div class="infoholder">
									<div class="descholder">
										<div class="more-holder">
											<ul class="infoul">
												<li>
													<i class="zmdi zmdi-pin"></i>
													<?=$adr?>
												</li>
												<li>
													<i class="mdi mdi-phone"></i>
													<?=$ipn?>
												</li>
												<li>
													<i class="mdi mdi-earth"></i>
													<?=$website?>
												</li>
											</ul>
											<?php if(isset($pieces) && !empty($pieces)){ ?>
											<div class="tagging" onclick="explandTags(this)">
												Popular with:
												<?php foreach($pieces as $element) {
													if(isset($element) && !empty($element)) {
														echo "<span>".$element."</span> ";
													}
												} ?>
											</div>
											<?php } ?>
										</div>
									</div>
								</div>						
							</div>
						</div>
						<div id="subtab-reviews-<?=$i?>" class="tab-pane fade">	
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
						<div id="subtab-photos-<?=$i?>" class="subtab-photos">
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
						<div id="subtab-amenities-<?=$i?>" class="tab-pane fade">
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
								<?php } } } else { ?>
								<?php $this->context->getnolistfound('nohotelsfound');?>
								<?php } ?>
							</ul>
							<div class="pagination&quot;">
								<?php if(isset($rs['next_page_token']) && !empty($rs['next_page_token'])) { ?>
								<div class="right">
									<a href="javascript:void(0)" onclick="displayplace('all','hotels','<?=$rs['next_page_token']?>');" class="btn btn-primary btn-sm">Next <i class="mdi mdi-arrow-right-bold-circle"></i></a>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit; ?>