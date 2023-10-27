<?php 
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\LoginForm;

$baseUrl = AppAsset::register($this)->baseUrl;

/*Session Data*/ 
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id'); 
//$wall_user_id = (string) $request->get('id');
$user_status = (string)$session->get('status');
use frontend\models\Notification;
$resultimg = LoginForm::find()->where(['_id' => $wall_user_id])->one();
$img = $this->context->getimage($wall_user_id,'photo');
$time = time();
$rand = rand(9999, 999999);
$uniqId = $time . $rand;
?>		
	<div class="general-page generaldetails-page refers-page main-page">
		<div class="combined-column">
			<div class="content-box">
				<div class="cbox-title nborder hidetitle-mbl">   
					<i class="zmdi zmdi-thumb-up"></i> 
					References<span class="count reflivecount"><?= $total_referal;?></span>
					<div class="right-tabs noborder refers-see">								
						<select class="cat_filter" onchange="getNewVal(this);">	  
							<option value= "All" selected>All</option>
						    <option value= "Personal">Personal</option>
							<option value= "Traveller">Travellers</option>
							<option value= "Host">Hosts</option>
							<option value= "Positive">Positive</option>
							<option value= "Negative">Negative</option>
						</select>
					</div>						
				</div>
				<div class="cbox-desc">
					<div class="tab-content view-holder">
						<div class="refers-details">
							<div class="row mainrow">
								<div class="refers-summery gdetails-summery">
									<div class="search-area side-area main-search">
										<a href="javascript:void(0)" class="expand-link" onclick="mng_drop_searcharea(this)"><i class="mdi mdi-menu-right"></i>Profile Details</a>
										<div class="expandable-area">
											<a href="javascript:void(0)" class="closearea" onclick="mng_drop_searcharea(this)">
												<i class="mdi mdi-close	"></i>
											</a>
											<div class="user-profile">
												<div class="desc-holder">
													<div class="img-holder"><img src="<?=$img?>"/></div>
													<div class="content-area">
														<h4><?= $this->context->getuserdata($wall_user_id,'fullname');?></h4>
														<p><?= $this->context->getuserdata($wall_user_id,'city');?></p>
														<h5>Partially Verified <a href="javascript:void(0)" class="simple-tooltip" title="some intro text goes here<br />some intro text goes here"><i class="mdi mdi-information"></i></a></h5>
														<div class="row-sec">
															<div class="inforow">
																<span class="<?php if(!$isVerify){ echo "no-";}?>verified"><i class="fa <?php if($isVerify){ echo "fa-check-circle";}else{echo "mdi mdi-minus-circle";}?>"></i> Payment <?php if(!$isVerify){ echo "not";}?> verified</span>
															</div>	
															<div class="inforow">
																<span class="<?php if($user_status == 0){echo "no-";}?>verified"><i class="fa <?php if($user_status == 0){echo "fa-minus-circle";} else { echo "fa-check-circle";}?>"></i> Email verified</span>
															</div>
															<div class="inforow dis-none">
																<span class="<?php if($user_status == 0){echo "no-";}?>verified"><i class="fa <?php if($user_status == 0){echo "fa-minus-circle";} else { echo "fa-check-circle";}?>"></i> Email verified</span>
															</div>
															<div class="inforow">
																<span class="<?php if(!$isvip){ echo "no-";}?>verified"><i class="fa <?php if($isvip){ echo "fa-check-circle";}else{echo "mdi mdi-minus-circle";}?>"></i> VIP verified</span>
															</div>	
														</div>
													</div>
												</div>	
											</div>
										<div class="content-box">
											<div class="cbox-title">
												Recent references
											</div>
											<div class="cbox-desc">
												<div class="likes-summery">
													<div class="connect-likes">
														<h5><a href="javascript:void(0)"><?= count($crntmonth_referals);?> people</a> referred <span class="pname"><?= $this->context->getuserdata($wall_user_id,'fullname');?></span></h5>
														<ul>
															<?php 
															foreach($crntmonth_referals as $crntmonth_referal)
															{ ?>
																<li><a href="<?php $id = $crntmonth_referal; echo Url::to(['userwall/index', 'id' => "$id"]); ?>"><img src="<?= $this->context->getimage($crntmonth_referal,'thumb');?>"/></a></li>
															<?php }?>
														</ul>										
													</div>
													
													<div class="connect-likes dis-none" data-id="strcube">
														<h5><a href="javascript:void(0)">5 people</a> referred <span class="pname">name</span></h5>
														<ul>
															
																<li><a href="javascript:void(0)"><img src=""/></a></li>
														</ul>										
													</div>
													
													<div class="state-row">
														<p><span><?= $lastmonth_referal;?> people</span> last month</p>
													</div>
													<div class="state-row">
														<p><span class="reflivecount"><?= $total_referal;?></span><span> total</span> references</p>
													</div>
													
													<div class="connect-likes dis-none" data-id="strcube">
														<h5><a href="javascript:void(0)">5 people</a> referred <span class="pname">name</span></h5>
														<ul>
															
																<li><a href="javascript:void(0)"><img src=""/></a></li>
														</ul>										
													</div>             
													
													<?php if($user_id == $wall_user_id)
													{ ?>
														<div class="invite-likes">
															<p>Invite people in contact with you for referral</p>
															<div class="invite-holder">
																<form onsubmit="return false;">
																	<div class="tholder">
																		<div class="sliding-middle-custom anim-area underlined">
																			<input class= "referal_search" type="text" placeholder="Type a friend's name" id="refral_connections"/>
																			<a href="javascript:void(0)" onclick="removeinvitesearchinput(this),Refer_Connect_Serach();"><img src="<?=$baseUrl?>/images/cross-icon.png"/></a>
																		</div>
																	</div>
																</form>
																<div class="list-holder refral_connections">
																	<?php if($connections){?>
																	<ul>
																		<?php foreach($connections as $connect)
																		{ 
																		$connectid = $connect['to_id'];
																		?>
																		<li>
																			<div class="invitelike-friend invitelike-connect">
																				<div class="imgholder"><img src="<?= $this->context->getimage($connectid,'thumb');?>"/></div>
																				<div class="descholder">
																					<h6><?= $this->context->getuserdata($connectid,'fullname');?></h6>
																					<?php 

																					$invitaionsent = Notification::find()->where(['status' => '1', 'from_connect_id' => "$connectid", 'user_id' => "$user_id", 'notification_type' => 'invitereferal'])->one();
																					if($invitaionsent) {?>
																						<div class="btn-holder referal_invited_<?= $connectid;?>">
																						<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>
																						</div>
																					<?php 
																					}else {
																					?>
																					<div class="btn-holder referal_invite_<?= $connectid;?>">
																						<a href="javascript:void(0)" onclick="sendinvitereferal('<?=$connectid?>')" class="btn-invite">Invite</a>
																					</div>
																					<?php }?>
																					<div class="dis-none btn-holder referal_invited_<?= $connectid;?>">
																						<label class="infolabel"><i class="zmdi zmdi-check"></i> Invited</label>
																					</div>
																				</div>
																			</div>
																		</li>
																		<?php }?>
																	</ul>
																	<?php } else {?>
																	<?php $this->context->getnolistfound('noconnectfound'); ?>
																	<?php }?>		
																</div>
															</div>
														</div>
													<?php } ?>
												</div>
											</div>
										</div>
										</div>
									</div>
								</div>
								<div class="post-column">									
									<?php if($user_id != $wall_user_id)
									{ ?>
									<div class="new-post base-newpost">
                            			<form action="">
                            				<div class="npost-content">
                            					<div class="post-mcontent">
                            						<i class="mdi mdi-pencil-box-outline main-icon"></i>
                            							<div class="desc">
                            							<div class="input-field comments_box">
                            							<input placeholder="Write your reference for <?= $this->context->getuserdata($wall_user_id,'fullname');?>" class="validate commentmodalAction_form referencePostContent" type="text">							
                            							</div>
                            							</div>
                            					</div>
                            				</div>				
                            			</form>
                                    </div>		
									<?php }?>
									<div class="tab-content" id="tab-catreferal">											
										<div class="post-referal">
											<?php 
											if((count($referals)) == 0)
											{
												$this->context->getnolistfound('noreferencefound');
											}
											$lp = 1; 
											foreach($referals as $referal)
											{ 
												/*$existing_posts = '1';
												$cls = '';
												if(count($referals)==$lp) {
												  $cls = 'lazyloadscroll'; 
												}*/ 

												$this->context->display_last_referal($referal['_id']);
												$lp++;
											}?>
										</div>
										<div class="clear"></div>
										<center><div class="lds-css ng-scope dis-none"> <div class="lds-rolling lds-rolling100"> <div></div> </div></div></center>
									</div>										
								</div>										
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="<?=$baseUrl?>/js/referal.js"></script>

<?php exit();?>				