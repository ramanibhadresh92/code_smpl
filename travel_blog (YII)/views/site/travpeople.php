<?php   
/* @var $this \yii\web\View */
/* @var $content string */

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use frontend\assets\AppAsset;
use frontend\models\Connect;
use frontend\models\SecuritySetting;
use frontend\models\LoginForm;
use frontend\models\Personalinfo;
use frontend\models\Verify;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id');
$this->title = 'Trav Connections';
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>
    <div class="page-wrapper ">
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <div class="floating-icon">
            <div class="scrollup-btnbox anim-side btnbox scrollup-float">
                <div class="scrollup-button float-icon"><span class="icon-holder ispan"><i class="mdi mdi-arrow-up-bold-circle"></i></span></div>          
            </div>            
        </div>
        <div class="clear"></div>
        <div class="container page_container">
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout">
				<div class="main-content with-lmenu sub-page peopleknow-page">
					<div class="combined-column">
						<span class="mob-title">Suggested connections</span>
						<div class="content-box bshadow">
							<div class="cbox-title">						
								Suggested connections
							</div>
							<div class="cbox-desc">
								<div class="connections-grid suggetionbox">
									<div class="row">
										<?php
											if(!empty($suggetion_requests) && count($suggetion_requests)>0) {
												foreach($suggetion_requests as $suggetion_request){

												$suggest_user_id = (string) $suggetion_request['connect_id'];
												$is_connect = Connect::find()->where(['from_id' => (string)$user_id, 'to_id' => (string)$suggest_user_id, 'status' => '1'])->one();	
												$is_connect_request_sent = Connect::find()->where(['from_id' => (string)$user_id, 'to_id' => (string)$suggest_user_id, 'status' => '0'])->one();
												$sugby = LoginForm::find()->where(['_id' => $suggetion_request['user_id']])->one();
												$isverify = Verify::isVerify($suggest_user_id);

												$frdetails = LoginForm::find()->where(['_id' => $suggetion_request['connect_id']])->one();
												$city = isset($frdetails['city']) ? $frdetails['city'] : '';
								                $country  = isset($frdetails['country']) ? $frdetails['country'] : '';
								                $address = $city.', '.$country;
								                $address = trim($address);
								                $address = explode(",", $address);
								                $address = array_filter($address);
								                $addressLabel = '&nbsp;';
								                if(count($address) >1) {
								                    $first = reset($address);
								                    $last = end($address);
								                    $addressLabel = 'Lives in ' . $first.', '.$last;
								                } else if(count($address) == 1) {
								                    $addressLabel = 'Lives in '. implode(", ", $address);
								                } else {
								                    $personalinfo = Personalinfo::find()->where(['user_id' => $suggest_user_id])->asArray()->one();
								                    if(!empty($personalinfo)) {
								                        $personalinfo = $personalinfo['occupation'];
								                        $personalinfo = explode(',', $personalinfo);
								                        $personalinfo = array_values($personalinfo);
								                        if(count($personalinfo) >2) {
								                            $tempCount = count($personalinfo) - 1;
								                            $tempNames = array_slice($personalinfo, 1);

								                            $addressLabel = $personalinfo[0] . ' and <a href="javascript:void(0)" class="liveliketooltip" data-title="'.implode('<br/>', $tempNames).'">'.(count($personalinfo) - 1).' others</a>';     
								                        } else if (count($personalinfo) >1) {
								                            $addressLabel = $personalinfo[0] .' and ' . $personalinfo[1];  
								                        } else if (count($personalinfo) == 1) { 
								                            $addressLabel = $personalinfo[0];  
								                        }
								                    }
								                }

												$frnd_img = $this->context->getimage($frdetails['_id'],'thumb');

												$form = ActiveForm::begin(['id' => 'view_connect_request','options'=>['onsubmit'=>'return false;',], ]); 
												//$mutual_ctr = $model_connect->mutualconnectcount($frdetails['_id']);

												$connectid = (string)$frdetails['_id'];
												$ctr = Connect::mutualconnectcount($connectid);
								                $result_security = SecuritySetting::find()->where(['user_id' => $connectid])->asarray()->one();
								                $connect_list = isset($result_security['connect_list']) ? $result_security['connect_list'] : '';
								                $mutualLabel =  '';
								                $totalconnections = Connect::find()->where(['to_id' => (string)$connectid, 'status' => '1'])->count();
								                if($connect_list == 'Public') {
								                    if($totalconnections>1) {
								                        $mutualLabel = $totalconnections .' Connections';
								                    } else if($totalconnections == 1) {
								                        $mutualLabel = '1 Connect';
								                    }
								                } else if($connect_list == 'Private') {
								                    if($ctr >0) {
								                        $mutualLabel =  $ctr.' Mutual Connections';
								                    }
								                } else if($connect_list == 'Connections') {
								                    if(!empty($isconnect)) {
								                        if($totalconnections>1) {
								                            $mutualLabel = $totalconnections .' Connections';
								                        } else if($totalconnections == 1) {
								                            $mutualLabel = '1 Connect';
								                        }   
								                    } else {
								                        if($ctr >0) {
								                            $mutualLabel =  $ctr.' Mutual Connections';
								                        }   
								                    }
								                }
										?>
										<div class="grid-box" id="request_<?php echo $frdetails['_id'];?>">
											<input type="hidden" name="to_id" id="to_id" value="<?php echo $frdetails['_id'];?>">
											<div class="connect-box">
												<div class="imgholder online-img">
													<img src="<?= $frnd_img?>"/>
													<?php if($isverify) { ?>
													<span class="online-mark"><i class="zmdi zmdi-check"></i></span>
													<?php } ?>
												</div>
												<div class="descholder">
													<a href="<?php $id = $frdetails['_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>" class="userlink">
														<span><?php echo $frdetails['fullname'];?></span>
													</a>
													<p class="designation">Suggested by <?=$sugby['fullname']?></p>
													<?php  if(!$is_connect) { ?>
													<?php  if(!$is_connect_request_sent) { ?>
													<div class="btn-area travconnections_<?=$frdetails['_id']?>">
														<a href="javascript:void(0)" class="gray-text-555">
															<i class="mdi mdi-account-plus people_<?php echo $frdetails['id'];?>" onclick="addConnect('<?=$frdetails['id']?>')"></i>
															<i class="mdi mdi-account-minus dis-none sendmsg_<?php echo $frdetails['id'];?>" onclick="removeConnect('<?=$frdetails['_id']?>','<?=$user_id?>','<?=$frdetails['_id']?>', 'cancle_connect_request')"></i>
														</a>
														<a href="javascript:void(0)" class="tb-pyk-remove"></a>
													</div>
													<?php } else { ?>
													<div class="btn-area travconnections_<?=$frdetails['_id']?>">
														<a href="javascript:void(0)" class="gray-text-555">
															<i class="mdi mdi-account-plus dis-none people_<?php echo $frdetails['id'];?>" onclick="addConnect('<?=$frdetails['id']?>')"></i>
															<i class="mdi mdi-account-minus sendmsg_<?php echo $frdetails['id'];?>" onclick="removeConnect('<?=$frdetails['_id']?>','<?=$user_id?>','<?=$frdetails['_id']?>','cancle_connect_request')"></i>
														</a>
														<a href="javascript:void(0)" class="tb-pyk-remove"></a>
													</div>
													<?php } ?>
													<?php } else { ?>
													<div class="btn-area travconnections_<?=$frdetails['_id']?>">
														<div class="showlabel showlabel_<?php echo $frdetails['_id'];?>">
															<div class="dropdown dropdown-custom req-btn" onclick="fetchconnectmenu('<?=$frdetails['_id']?>')">
															 <a href="javascript:void(0)" class="dropdown-toggle gray-text-555" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
															<i class="mdi mdi-chevron-down"></i>
															 </a>
															 <ul class="dropdown-menu fetchconnectmenu">
															
															</ul>
														   </div>
														</div>
													</div>
													<?php } ?>	
												</div>																					
											</div>
										</div>
										<?php } } else { ?>
										<?php $this->context->getnolistfound('nosuggetionforyou'); ?>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
						<span class="mob-title">Connect requests</span>
						<div class="content-box bshadow">
							<div class="cbox-title">
								Respond to your connect request
							</div>
							<div class="cbox-desc">
								<div class="connections-grid freq">
									<div class="row">
										<?php 
											if(!empty($pending_requests) && count($pending_requests)>0) {
												foreach($pending_requests as $pending_request){ 
												$isverify = Verify::isVerify($pending_request['userdata']['_id']);
												$mutual_ctr = $model_connect->mutualconnectcount($pending_request['userdata']['_id']);
												$frnd_img = $this->context->getimage($pending_request['userdata']['_id'],'thumb');

										$form = ActiveForm::begin(['id' => 'view_connect_request','options'=>['onsubmit'=>'return false;',],]); ?>
										<div class="grid-box" id="request_<?php echo $pending_request['_id'];?>">
											<input type="hidden" name="to_id" id="to_id" value="<?php echo $pending_request['_id'];?>">
											<div class="connect-box">
												<div class="imgholder online-img">
													<img src="<?= $frnd_img?>"/>
													<?php if($isverify) { ?>
													<span class="online-mark"><i class="zmdi zmdi-check"></i></span>
													<?php } ?>
												</div>
												<div class="descholder">
													<a href="<?php $id = $pending_request['userdata']['_id']; echo Url::to(['userwall/index', 'id' => "$id"]); ?>" class="userlink">
														<span><?php echo $pending_request['userdata']['fname']." ".$pending_request['userdata']['lname'];?></span>
													</a>                                           
													<span class="info"><?=$pending_request['userdata']['city']?></span>
													<span class="info mutual"><?php if($mutual_ctr > 0){?><?php echo $mutual_ctr;?> Mutual Connections<?php }else{echo "No Mutual Connect";} ?></span>
													<span class="requestsent acceptmsg_<?php echo $pending_request['from_id'];?>" class="request-accept dis-none"></span>
													
													<div class="dropdown dropdown-custom mbl-menu">
													  <a href="javascript:void(0)" class="dropdown-toggle gray-text-555" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
														<i class="mdi mdi-chevron-down"></i>
													  </a>
													  <ul class="dropdown-menu">
													  	<li><a class="btn btn-primary btn-sm accept-connect" href="javascript:void(0)">Accept</a></li>
														<li><a class="btn btn-primary btn-sm delete-connect btn-gray" href="javascript:void(0)">Delete</a></li>
													  </ul>
													</div>
													
													<div class="btn-area btns-holder desc-menu">
														<div class="req-btn accept_<?php echo $pending_request['from_id'];?>">
															<button class="btn btn-primary btn-sm delete-connect btn-gray" onclick="removeConnect('<?=$pending_request['_id']?>','<?=$pending_request['from_id']?>','<?=$pending_request['to_id']?>','deleteRequest')">Delete</button>
															<button class="btn btn-primary btn-sm accept-connect" onclick="acceptConnectRequest('<?php echo $pending_request['from_id'];?>','<?php echo $pending_request['to_id'];?>')">Confirm</button>
														</div>
														<div class="showlabel showlabel_<?php echo $pending_request['from_id'];?>">
															<div class="dropdown dropdown-custom req-btn" onclick="fetchconnectmenu('<?=$pending_request['from_id']?>')">
															 <a href="javascript:void(0)" class="dropdown-toggle gray-text-555" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
															<i class="mdi mdi-chevron-down"></i>
															 </a>
															 <ul class="dropdown-menu fetchconnectmenu">
															</ul>
														   </div>
														</div>
													</div>
												</div>																					
											</div>
										</div>
										<?php } } else { ?>
										<?php $this->context->getnolistfound('nonewconnectrequest'); ?>
										<?php } ?>
									</div>
								</div>
							</div>
						</div>
						<span class="mob-title">People you may know</span>
						<div class="content-box bshadow">
							<div class="cbox-title">						
								People you may know
							</div> 
							<div class="cbox-desc">
								<div class="connections-grid">
									<div class="row">
										<input type="hidden" name="login_id" id="login_id" value="<?php echo $session->get('user_id');?>">
										<?php
											$this->context->getUserGridLayout2($connections, 'travpeople'); 
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="chatblock">
						<div class="float-chat anim-side">
							<div class="chat-button float-icon directcheckuserauthclass" onclick="getchatcontent();"><span class="icon-holder">icon</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	  
          <?php include('../views/layouts/footer.php'); ?>
    </div>  

<div id="suggest-connections" class="modal tbpost_modal custom_modal split-page main_modal"></div>
<script>
var baseUrl ='<?php echo (string) $baseUrl; ?>';
</script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

<?php include('../views/layouts/commonjs.php'); ?>
<script type="text/javascript" src="<?=$baseUrl?>/js/connect.js"></script>
<?php $this->endBody() ?> 