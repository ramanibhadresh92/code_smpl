<?php  
use frontend\assets\AppAsset;
 use frontend\models\Tours;
use backend\models\Googlekey;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'Attractions';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);

$place = Yii::$app->params['place'];
$placetitle = Yii::$app->params['placetitle'];
$placefirst = Yii::$app->params['placefirst'];
$lat = Yii::$app->params['lat'];
$lng = Yii::$app->params['lng'];
$count = 'all';
$placeapi = str_replace(' ','+',$placetitle);
$tk = $ql = $rs = '';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<script src="<?=$baseUrl?>/js/chart.js"></script>
<div class="page-wrapper place-wrapper hidemenu-wrapper full-wrapper noopened-search JIS3829 attractionlist-page"> 
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
		   <div class="main-content hotels-page main-page places-page pb-0">
		      <div class="combined-column wide-open">
		         <div class="content-box">
		            <div class="banner-section">
		               <h4>Attractions</h4>
		            </div>
                    <div class="places-content places-all">
                       <div class="container cshfsiput">
                          <div class="places-column cshfsiput m-top">
                             <div>
                                <div id="places-lodge" class="placeslodge-content subtab places-discussion-main top_tabs">
									<div class="content-box">
										<div class="mbl-tabnav"> 
											<a href="javascript:void(0)"><i class="mdi mdi-arrow-left"></i></a> <h6>Attractions</h6>
										</div>
										<div class="placesection redsection">											
											<div class="cbox-desc hotels-page np">
												<div class="tcontent-holder moreinfo-outer">
													<div class="top-stuff top-graybg" id="all-restaurant">		<h6>Attractions found in <span><?=$placefirst?></span></h6>
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
<?php
$attractions = Tours::find()->where(['City' => 'japan'])->orwhere(['City' => 'Japan'])->asarray()->all();
$isemptyattractions = true;
if(!empty($attractions)) {
 	foreach ($attractions as $attractions_s) {
		$City = $attractions_s['City'];
		$Country = $attractions_s['Country'];
 		$img = $attractions_s['ProductImage'];
		$imgclass = 'himg';
		$name = $attractions_s['ProductName'];
		$Introduction = $attractions_s['Introduction'];
		$rank = $attractions_s['Special'];
		$categories = array();
        $categories[] = isset($attractions_s['Category1']) ? $attractions_s['Category1'] : '';
        $categories[] = isset($attractions_s['Category2']) ? $attractions_s['Category2'] : '';
        $categories[] = isset($attractions_s['Category3']) ? $attractions_s['Category3'] : '';
        $categories = array_values(array_filter($categories));
		$isemptyattractions = false;
		?>
		<li>
			<div class="hotel-li expandable-holder dealli mobilelist">
				<div class="summery-info">
					<div class="imgholder <?=$imgclass?>-box"><img src="<?=$img?>" class="<?=$imgclass?>"/></div>
					<div class="descholder">
						<a href="javascript:void(0)" class="expand-link" onclick="mng_expandable(this,'hasClose')">
							<h4><?=$name?></h4>
							<div class="clear"></div>
							<span class="rank"><?=$rank?></span>
							<span class="address"><?=$City?>, <?=$Country?></span>
							<?php if(!empty($categories)) { ?>
							<div class="more-holder">
								<div class="tagging" onclick="explandTags(this)">
									Categories:
									<?php
									foreach($categories as $categories_s) {
										if(trim($categories_s) != '') {
											echo "<span>".$categories_s."</span> ";
										}
									} ?>
								</div>
							</div>
							<?php } ?>
						</a>
					</div>
				</div>
			</div>
		</li>
	<?php } 
} 

if($isemptyattractions) { 
	$this->context->getnolistfound('norestaurantsfound');
} 
?>
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
                                </div>
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
<script type="text/javascript">
	var data1 = '';
	var place = "<?php echo (string)$place?>";
	var placetitle = "<?php echo (string)$placetitle?>";
	var placefirst = "<?php echo (string)$placefirst?>";
	var baseUrl = "<?php echo (string)$baseUrl; ?>";
	var lat = "<?php echo $lat; ?>";
	var lng = "<?php echo $lng; ?>";
</script>	
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>
<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/tours.js"></script>
<?php $this->endBody() ?> 