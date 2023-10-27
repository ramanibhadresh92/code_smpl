<?php
use frontend\assets\AppAsset;
use frontend\models\Personalinfo;

$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');

?>
<div class="fake-title-area divided-nav">
	<ul class="tabs nav-custom-tabs text-right">
		<li class="tab"><a href="#destination-map"><i class="zmdi zmdi-pin"></i>Map</a></li>
		<li class="tab"><a href="#destination-list" onclick="destListing()"><i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>List</a></li>
	</ul>																
</div>
<div class="content-box bshadow">
	<div class="cbox-desc">
		<div class="tab-content mainboxtab <?php if($user_id == $wall_user_id){?>owner<?php } ?>">
			<?php if($user_id == $wall_user_id){ ?>
			<div class="edit-section clas_edit">
				<div class="dropdown dropdown-custom">
					<a href="javascript:void(0)" class="dropdown_text dropdown-button" data-activates="who_visited">
						<span class="sword">Places you want to visit</span> 
						<i class="zmdi zmdi-caret-down"></i>
					</a>
					<ul id="who_visited" class="dropdown-content custom_dropdown tabs">
						<li class="tab visited-dest">
							<a href="#visited-dest" class="visited-dest">Places you visited</a>
						</li>
						<li class="tab wish-dest active">
							<a href="#wish-dest" class="active">Places you want to visit</a>
						</li>
					</ul>
				</div>
				<div class="tab-content">
					<div class="tab-pane active in" id="wish-dest">
						<?php 
						if(!empty($destfuture)) { ?>
							<ul class="dest-list nice-scroll">
								<?php 
								foreach($destfuture as $destination) {
									if(!isset($destination['_id'])) {
										$destinationid = rand(1000,9999);
										$destinationname = str_replace("'","",$destination);
										$isDestination = false;
									} else {
										$destinationid = $destination['_id'];
										$destinationname = str_replace("'","",$destination['place']);
										$isDestination = true;
									}

									$count = substr_count($destinationname,",");
									if($count >= 1) {
										$placet = (explode(",",$destinationname));
										$placefirst = $placet[0];
										$placesecond = $placet[1];
										if(isset($placet[2]) && !empty($placet[2])) {
											$placesecond .=', '.$placet[2];
										}
									} else {
										$placet = (explode(",",$destinationname));
										$placefirst = $placet[0];
										$placesecond = '&nbsp;';
									}

									$destimage = $this->context->getplaceimage($destinationname);
									$time = time();
									$rand = rand(999, 999999);
									$getkey = $time.'_'.$rand;
									?>
									<li id="XHIL<?=$getkey?>">
										<div class="destili">
											<div class="imgholder himg-box">
												<img src="<?=$destimage?>" class="himg"/>
											</div>
											<div class="descholder">
												<h6>
													<a href="?r=places&p=<?=$destinationname?>" target="_blank"><?=$placefirst?></a>
													<span><?=$placesecond?></span>
												</h6>
												<span class="wantvisit">Want to visit</span>
												<?php if($isDestination) { ?>
												<a href="javascript:void(0)" onclick="delDest('<?=$destinationid?>', '#XHIL<?=$getkey?>')" class="cross">
													<i class="mdi mdi-close	"></i>
												</a>
												<?php } ?>
											</div>
										</div>
									</li>
									<?php 
								} 
								?>
							</ul>
						<?php
						} else { 
							$this->context->getnolistfound('novisitedplaces');
						} 
						?>
						<div class="clear"></div>
						<div class="add-dest">
							<div class="sliding-middle-custom anim-area underlined fullwidth locinput">
								<input data-query="M" id="future" class="validate valid getplacelocation" onfocus="filderMapLocationModal(this)" autocomplete="off" placeholder="Add place you want to visit" type="text">
							</div>
							<a href="javascript:void(0)" class="btn-custom waves-effect" onclick="addDest('future')">Add</a>
						</div>
					</div>

					<div class="tab-pane" id="visited-dest">
						<center>
							<div class="lds-css ng-scope"> 
								<div class="lds-rolling lds-rolling100"> 
									<div></div> 
								</div>
							</div>
						</center>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="tab-pane in active" id="destination-map">
				<div class="map-holder">
					<center>
						<div class="lds-css ng-scope"> 
							<div class="lds-rolling lds-rolling100"> 
								<div></div> 
							</div>
						</div>
					</center>
				</div>
			</div>
		</div>
	</div>
</div>
<?php exit();?>