<?php
use frontend\assets\AppAsset;
use backend\models\Googlekey;

$baseUrl = AppAsset::register($this)->baseUrl;
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
<div class="content-box ">
	<div class="mbl-tabnav">
		<a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-arrow-left"></i></a> <h6>To Do</h6>
	</div>
	<div class="placesection bluesection">
		<div class="cbox-desc hotels-page np">
			<div class="tcontent-holder moreinfo-outer">
				<div class="top-stuff top-graybg" id="all-todo">										
					<div class="more-actions">
						<?php $total_things = count($todos);
						if($total_things > 0){?>
						<div class="sorting left" style="margin-right: 0px !important;">
							<label>Sort by</label>
							<div class="select-holder">
								<select class="select2" id="thingsdropchange">
									<option <?php if($token == 'empty'){?>selected="true"<?php } ?>value="empty">Pricing</option>
									<option <?php if($token == 'ratings'){?>selected="true"<?php } ?>value="ratings">Ratings</option>
								</select>
							</div>
						</div>
						<?php } ?>				
					</div>
					<h6><?=$total_things;?> things to do in <span><?=$placefirst?></span></h6>
				</div>
				<div class="places-content-holder">														
					<div class="map-holder">
						<iframe src="https://maps.google.com/maps?key=AIzaSyARKq2MczJRfp75UjoXjCBFo7QQn0RPFSE&q=park+in+<?=$placetitle?>&output=embed" width="600" height="450" frameborder="0" allowfullscreen></iframe>
						<a href="javascript:void(0)" class="overlay" onclick="expandMap(this,'#all-todo')"></a>
						<a href="javascript:void(0)" class="closelink" onclick="shrinkMap(this)"><i class="mdi mdi-close	"></i> Close</a>
					</div>
					<div class="list-holder">
						<div class="hotel-list">
							<ul>
							<?php if(!empty($todos)){ $i=1; foreach($todos as $todo){ ?>
<li class="attractionbox">
	<div class="hotel-li expandable-holder dealli mobilelist">
		<div class="summery-info">
			<div class="imgholder himg-box"><img src="<?=str_replace('/graphicslib','/graphicslib/thumbs674x446/',$todo['ProductImage'])?>" class="himg"/></div>
			<div class="descholder">
				<a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose')">
					<h4 title="<?=$todo['ProductName']?>"><?=$todo['ProductName']?></h4>
					<div class="clear"></div>
					<div class="reviews-link">
						<span class="review-count"><?=(10*$todo['AvgRating']);?> reviews</span>
						<span class="checks-holder">
							<?php for($j=0;$j<5;$j++){ ?>
								<i class="mdi mdi-star <?php if($j < $todo['AvgRating']){ ?>active<?php } ?>"></i>
							<?php } ?>
						</span>
					</div>
					<!-- <span class="address"><?=$todo['Commences'];?></span> -->
					<span class="distance-info">
						<p class="dpara"><i class="mdi mdi-format-quote-open"></i><?=$todo['ProductText'];?></p>
					</span>
					<div class="more-holder">
						<div class="tagging" onclick="explandTags(this)">
							Popular with:
							<?php 
							$pieces = explode(", ", str_replace(' & ',', ',$todo['Group1']));
							foreach($pieces as $element) {
								echo "<span>".$element."</span> ";
							} ?>
						</div>
					</div>
				</a>
				<div class="info-action">
					<span class="duration"><?=$todo['Duration'];?></span>
					<div class="clear"></div>
					<span class="price">USD <?=$todo['PriceUSD']?>*</span>
					<div class="clear"></div>
					<a href="<?=$todo['ProductURL']?>" target="_new" class="booknow-btn">Book Now <i class="mdi mdi-chevron-right"></i></a>
				</div>
			</div>
		</div>
		<div class="expandable-area">
			<a href="javascript:void(0)" class="shrink-link" onclick="mng_expandable(this,'closeIt')"><i class="mdi mdi-close	"></i> Close</a>
			<div class="clear"></div>
			<div class="explandable-tabs">
				<ul class="tabs tabsnew subtab-menu">
					<li class="tab"><a href="#subtab-details1-<?=$i?>">Details</a></li>
					<li class="tab"><a data-which="photo" href="#subtab-photos1-<?=$i?>" data-tab="subtab-photos">Photos</a></li>
					<li class="tab"><a href="#subtab-reviews1-<?=$i?>">Reviews</a></li>
				</ul>
				<div class="tab-content">
					<div id="subtab-details1-<?=$i?>" class="tab-pane fade active in">
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
											</li> </ul>
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
					<div id="subtab-photos1-<?=$i?>" class="tab-pane fade subtab-photos">
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
					<div id="subtab-reviews1-<?=$i?>" class="tab-pane fade">	
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
				</div>
			</div>
		</div>
	</div>
</li>
							<?php $i++; } } else { ?>
							<?php $this->context->getnolistfound('nothingsfound');?>
							<?php } ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit;?>