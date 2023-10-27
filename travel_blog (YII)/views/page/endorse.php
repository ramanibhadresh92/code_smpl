<?php 
use yii\helpers\Url;
use frontend\models\PageEndorse;
?>
<div class="combined-column">
	<div class="content-box bshadow">
		<div class="cbox-title hidetitle-mbl">
			<i class="zmdi zmdi-thumb-up"></i>
			Endorsement
		</div>
		<div class="cbox-desc">								
			<div class="fullwidth text-center">
				<div class="endorsement-holder">
					<div class="esection">
						<h4>Services and Attributes</h4>
						<div class="service-holder">
							<ul class="mainul">													
								<?php $defaultendorse=array("0"=>"Customer Support","1"=>"Business Attitude","3"=>"Refund","4"=>"Information Technology","5"=>"Project Management");
									foreach($defaultendorse as $x=>$x_value){
										$pageendorse = $x_value;
										$encount = PageEndorse::getPEcount($pageid,$pageendorse);
										$enusers = PageEndorse::getPEusers($pageid,$pageendorse);
										$enexist = PageEndorse::getPEexist($pageid,$pageendorse);
										if($enexist){$disicon = 'minus';}
										else{$disicon = 'plus';}
								?>
								<li>
									<div class="service-box">
										<div class="servicename">
											<div class="count <?=$pageid.'_'.$pageendorse?>"><?=$encount?></div>
											<span><?=$pageendorse?></span>
										</div>
										<div class="servicedetail">
											<a href="javascript:void(0)" class="endorselink" onclick="manageEndorsement(this,'<?=$userimg?>','<?=$pageendorse?>')"><i class="mdi mdi-<?=$disicon?>"></i></a>
											<ul>
												<?php if($enexist){ $link = Url::to(['userwall/index', 'id' => $user_id]); ?>
													<li class="active"><a href="<?=$link?>" title="<?= $this->context->getuserdata($user_id,'fullname');?>"><img src="<?= $userimg;?>"></a></li>
												<?php } ?>
												<?php foreach($enusers as $enuser){ $link = Url::to(['userwall/index', 'id' => $enuser['user_id']]); ?>
													<li><a href="<?=$link?>" title="<?= $this->context->getuserdata($enuser['user_id'],'fullname');?>"><img src="<?= $this->context->getimage($enuser['user_id'],'thumb');?>"></a></li>
												<?php } ?>
											</ul>
											<a href="#endorsepeople-popup" class="popup-modal you-endorse" onclick="listUserEndorse('<?=$pageendorse?>','<?=$baseUrl?>')"><i class="mdi mdi-menu-right"></i></a>
										</div>
									</div>
								</li>
								<?php } ?>
								<?php foreach($pageendorselidt as $pageendorse){
									$encount = PageEndorse::getPEcount($pageid,$pageendorse);
									$enusers = PageEndorse::getPEusers($pageid,$pageendorse);
									$enexist = PageEndorse::getPEexist($pageid,$pageendorse);
									if($enexist){$disicon = 'minus';}
									else{$disicon = 'plus';}
									$pgend = str_replace(' ', '', $pageendorse);
								?>
								<li class="delete_<?=$pgend?>">
									<div class="service-box">
										<div class="servicename">
											<div class="count <?=$pageid.'_'.$pageendorse?>"><?=$encount?></div>
											<span><?=$pageendorse?></span>
										</div>
										<div class="servicedetail">
											<a href="javascript:void(0)" class="endorselink" onclick="manageEndorsement(this,'<?=$userimg?>','<?=$pageendorse?>')"><i class="mdi mdi-<?=$disicon?>"></i></a>
											<ul>
												<?php if($enexist){ $link = Url::to(['userwall/index', 'id' => $user_id]); ?>
													<li class="active"><a href="<?=$link?>" title="<?= $this->context->getuserdata($user_id,'fullname');?>"><img src="<?= $userimg;?>"></a></li>
												<?php } ?>
												<?php foreach($enusers as $enuser){ $link = Url::to(['userwall/index', 'id' => $enuser['user_id']]); ?>
													<li><a href="<?=$link?>" title="<?= $this->context->getuserdata($enuser['user_id'],'fullname');?>"><img src="<?= $this->context->getimage($enuser['user_id'],'thumb');?>"></a></li>
												<?php } ?>
											</ul>
											<a href="#endorsepeople-popup" class="popup-modal you-endorse" onclick="listUserEndorse('<?=$pageendorse?>')"><i class="mdi mdi-menu-right"></i></a>
										</div>
									</div>
								</li>
								<?php } ?>
							</ul>
						</div>
					</div>
					<?php if($pageinfo['created_by'] == $user_id){ ?>
					<div class="esection">
						<div class="expandable-holder">
							<h4><a href="javascript:void(0)" onclick="mng_expandable(this)" class="expand-link">Services and Attributes Settings <i class="mdi mdi-menu-right"></i></a></h4>
							<div class="expandable-area">
								<div class="graybox">
									<ul>
										<li>
											<a href="javascript:void(0)">
											<input type="checkbox" id="pgendorse" <?php if(isset($pageinfo['pgendorse']) && $pageinfo['pgendorse']=='on'){ ?>checked="checked"<?php } ?> type="checkbox">
											<label for="pgendorse">Include page in endorsement suggestions to people who viewed my profile</label>
											</a>
										</li>
										<li>
											<a href="javascript:void(0)">
											<input type="checkbox" id="pgmail" <?php if(isset($pageinfo['pgmail']) && $pageinfo['pgmail'] == 'on'){ ?>checked="checked"<?php } ?> type="checkbox">
											<label for="pgmail">Send Admin notifications when someone endorse my page</label>
											</a>
										</li>
									</ul>
									<div class="btn-holder">
										<a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="mng_expandable(this)">Cancel</a>
										<a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="saveEndorse(this)">Save</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
					<div class="esection">
						<div class="expandable-holder">
							<h4><a href="javascript:void(0)" onclick="mng_expandable(this)" class="expand-link">Add <?php if($pageinfo['created_by'] == $user_id){ ?>and Remove<?php } ?> Endorsement <i class="mdi mdi-menu-right"></i></a></h4>
							<div class="expandable-area">
								<div class="graybox">
									<div class="endorsement-settings">
										<div class="add-endorsement">
											<div class="sliding-middle-custom anim-area underlined">
												<input class="fullwidth" placeholder="Type Endorsement" type="text" id="addendorse">
												<div class="suggestion-box bshadow">	
													<div class="suggestion-list nice-scroll" style="overflow-y: hidden;" tabindex="6">
														<ul>
															<li>Customer Support</li>
															<li>Business Attitude</li>
															<li>Refund</li>
															<li>Information Technology</li>
															<li>Project Management</li>
														</ul>
													</div>
												<div id="ascrail2008" class="nicescroll-rails nicescroll-rails-vr" style="width: 8px; z-index: 2; background: rgba(255, 255, 255, 0.6) none repeat scroll 0% 0%; cursor: default; position: absolute; top: 0px; left: -8px; height: 0px; display: none;"><div style="position: relative; top: 0px; float: right; width: 8px; height: 0px; background-color: rgb(187, 187, 187); border: 0px solid rgb(255, 255, 255); background-clip: padding-box; border-radius: 0px;" class="nicescroll-cursors"></div></div></div>
											</div>	
											<a href="javascript:void(0)" class="waves-effect" onclick="addEndorse()">Add</a>																		
										</div>
										<?php if($pageinfo['created_by'] == $user_id){ ?>
										<div class="servicelist">
											<?php foreach($pageendorselidt as $pageendorse){
												$encount = PageEndorse::getPEcount($pageid,$pageendorse);
												$pgend = str_replace(' ', '', $pageendorse);
											?>
											<div class="servicetag delete_<?=$pgend?>">
												<span class="tagcount"><?=$encount?></span>
												<span class="tagname"><?=$pageendorse?></span>
												<a href="javascript:void(0)" onclick="removeEndorse('<?=$pageendorse?>','<?=$pgend?>')"><i class="mdi mdi-close	"></i></a>
											</div>
											<?php } ?>
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
<?php exit;?>