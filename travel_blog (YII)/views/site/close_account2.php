<?php 

use frontend\assets\AppAsset;
use frontend\models\Connect;
use frontend\models\UserForm;
$session = Yii::$app->session;
$user_id = (string)$session->get('user_id'); 
$baseUrl = AppAsset::register($this)->baseUrl;

$frdlist = Connect::getmyconnectcloseaccount();
$userdata = UserForm::getUserName($user_id);
$userdata = json_decode($userdata, true);
?>
<div class="formtitle"><h4>Close Account</h4></div>
	<div class="close-account-change">
		<div class="top-close-head">
			<h3><?=$userdata['fullname']?>, we're sorry to see you go</h3>
		</div>
		<div class="new-closeaccount">
			<p>Once you close your account? You'll lose your connections, messages, posts and comments</p>
		</div>
		<?php if(count($frdlist) >0) { 
			if(count($frdlist) == 1) {
				$label = $frdlist[0]['fullname'];
			} else if(count($frdlist) == 2) {
				$label = $frdlist[0]['fullname'] . ' and ' .$label = $frdlist[1]['fullname'];
			} else if(count($frdlist) == 3) {
				$label = $frdlist[0]['fullname'] .', '.$label = $frdlist[1]['fullname'].' and ' .$label = $frdlist[2]['fullname'];
			} else if(count($frdlist) > 3) {
				$label = $frdlist[0]['fullname'] .', '.$label = $frdlist[1]['fullname'].' and ' .$label = $frdlist[2]['fullname'] .'...';
			}
		?>
		<div class="closeaccountsection">

			<p class="cl-head">Don't lose touch with your <?=count($frdlist)?> connections like <?=$label?></p>
			
			<ul class="row roe-close">
				<?php 
				$loop = 1;
				foreach ($frdlist as $key => $connect) {
					$fullname = $connect['fullname'];
					$thumb = $connect['thumb'];
					if(isset($connect['city']) && $connect['city'] != '') {
						$location = $connect['city'];
					} else if(isset($connect['country']) && $connect['country'] != '') {
						$location = $connect['country'];
					} else {
						$location = '';
					}
					?>

					<li class="col m4 s12 l4">	
						<a class="invitelike-friend invitelike-connect" href="javascript:void(0)">
							<span class="imgholder"><img src="<?=$thumb?>"></span>
							<span class="descholder">
								<h6><?=$fullname?></h6>
								<p><?=$location?></p>
							</span>
						</a>
					</li>
				<?php 
					if($loop == 3) {
						break;
					}

					$loop++;
				} ?>
			</ul>
		</div>
		<?php } ?>

		<ul class="settings-ul basicinfo-ul">
			<!-- reason of leaving -->
			<li>
				<div class="settings-group">
					<div class="normal-mode">
						<div class="row">
							<div class="col s12 m3 l2 caption-holder">
								<div class="caption">
									<label>Reason of Leaving</label>
								</div>
							</div>
							<div class="col s12 m9 l10 detail-holder">	
								<div class="info">
									<div class="radio-holder">
									<input name="group1" type="radio" id="test1" />
									<label for="test1">This is temporary, I will be back.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder"> 
									<input name="group1" type="radio" id="test2" />
									<label for="test2">I don't understand how to use Iaminjapan.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder"> 
									<input name="group1" type="radio" id="test3" />
									<label for="test3">My account was hacked.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder">
									<input name="group1" type="radio" id="test4" />
									<label for="test4">I spent too much time using Iaminjapan.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder">
									<input name="group1" type="radio" id="test5" />
									<label for="test5">I get too many emails, invitations, and requests from Iaminjapan.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder">
									<input name="group1" type="radio" id="test6" />
									<label for="test6">I have a privacy concern.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder">
									<input name="group1" type="radio" id="test7" />
									<label for="test7">I have another Iaminjapan account.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder">
									<input name="group1" type="radio" id="test8" />
									<label for="test8">I don't find Iaminjapan useful.
									</label>
									</div>													
									<div class="clear"></div>
									<div class="radio-holder" onclick="showother()">
									<input name="group1" type="radio" id="test9" />
									<label for="test9">Other
									</label>
									</div>													
																					
								</div>
							</div>											
						</div>
					</div>
				</div>
			</li>
			
			<!-- action required -->
			<li class="why-cloing">
				<div class="settings-group">
					<div class="normal-mode">
						<div class="row">
							<div class="col s12 m12 l12 detail-holder">
								<div class="info">
									<div class="sliding-middle-custom anim-area underlined fullwidth">
										<textarea class="materialize-textarea mb0 md_textarea descinput" id="reason_input" placeholder="Tell us why you're closing your account:"></textarea>
									</div>
								</div>
							</div>											
						</div>
					</div>
				</div>
			</li>
			
			
		</ul>
		<div class="clear"></div>
		
		<div class="subsetting-area">
			<ul class="settings-ul basicinfo-ul">
				<li>
					<div class="btn-holder right">						
						<a href="javascript:void(0)" onclick="hidewebcam();" class="btn-custom">Cancel</a>	
						<a href="javascript:void(0)" onclick="close_account()" class="btn-custom">Close Account
						</a>					
					</div>
				</li>
			</ul> 
		</div>
		
	</div>
<?php
exit;?>							