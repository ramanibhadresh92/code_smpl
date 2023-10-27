<?php

use frontend\assets\AppAsset;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="content-box">
	<div class="mbl-tabnav"> 
		<a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> <h6>Restaurants</h6>
	</div>
	<div class="placesection redsection">											
		<div class="cbox-desc hotels-page np">
			<div class="tcontent-holder moreinfo-outer">
				<div class="top-stuff top-graybg" id="all-restaurant">										
					<div class="more-actions">
						<ul class="text-right" style="width: auto; overflow: hidden;">
							<li><a href="javascript:void(0)" class="manageMap" onclick="openMapSection(this,'dine')"><i class="zmdi zmdi-pin"></i> Map</a></li>
						</ul>														
					</div>
					<h6>Restaurants found in <span><?=$placefirst?></span></h6>
				</div>
				<div class="places-content-holder">
					<div class="map-holder">
						<iframe src="https://maps.google.com/maps?key=<?=$GApiKeyP?>&q=restaurant+in+<?=$placetitle?>&output=embed" allowfullscreen="" width="600" height="450" frameborder="0"></iframe>
						<a href="javascript:void(0)" class="overlay" onclick="expandMap(this,'#all-restaurant')"></a>
						<a href="javascript:void(0)" class="closelink" onclick="shrinkMap(this)"><i class="mdi mdi-close	"></i> Close</a>
					</div>
					<div class="list-holder">
						<div class="hotel-list fullw-list">
							<ul>
							<?php if($ql == 'OK'){
								$hotel = $rs['results'];
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
												</div>
											</div>
											<div class="expandable-area">
												<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt')"><i class="mdi mdi-close	"></i> Close</a>
												<div class="clear"></div>
												<div class="explandable-tabs">
													<ul class="tabs tabsnew subtab-menu">
														<li class="tab"><a href="#subtab-reviews2-<?=$i?>">Reviews</a></li>
														<li class="tab"><a data-which="photo" href="#subtab-photos2-<?=$i?>" data-tab="subtab-photos">Photos</a></li>
														<li class="tab"><a href="#subtab-details2-<?=$i?>">Additional Info</a></li>
													</ul>
													<div class="tab-content">
														<div id="subtab-reviews2-<?=$i?>" class="tab-pane fade active in"> <div class="reviews-summery">
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
														<div id="subtab-photos2-<?=$i?>" class="tab-pane fade subtab-photos">
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
														<div id="subtab-details2-<?=$i?>" class="tab-pane fade">
															<div class="subdetail-box">
																<div class="infoholder">
																	<div class="descholder">
																		<div class="more-holder">
																			<ul class="infoul">
																				<li> <i class="zmdi zmdi-pin"></i> <?=$adr?> </li> 
																				<li> <i class="mdi mdi-phone"></i> <?=$ipn?> </li> 
																				<li><i class="mdi mdi-earth"></i><?=$website?></li>
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
													</div>
												</div>
											</div>
										
										</div>
									</li>
								<?php } } } else { ?>
							<?php $this->context->getnolistfound('norestaurantsfound');?>
							<?php } ?>
							</ul>
							<div class="pagination&quot;">
								<?php if(isset($rs['next_page_token']) && !empty($rs['next_page_token'])){ ?>
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
<?php exit;?>