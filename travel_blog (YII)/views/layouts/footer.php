<?php
use frontend\assets\AppAsset;
$baseUrl = AppAsset::register($this)->baseUrl;
?>
<div class="footer-section">
	<div class="mobile-footer-arrow">
		<div class="mobile-footer">
			<div class="dropdown dropdown-custom mmenu text-center">
				<a href="<?php echo Yii::$app->urlManager->createUrl(['site/mainfeed']); ?>" class="dropdown-toggle"  role="button" aria-haspopup="true" aria-expanded="false">
				<img src="<?=$baseUrl?>/images/foot-home.png" />
					Home
				</a>					
			</div>
			<div class="dropdown dropdown-custom mmenu text-center">
	            <a href="<?php echo Yii::$app->urlManager->createUrl(['site/messages']); ?>" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
				<img src="<?=$baseUrl?>/images/chat-white.png" />
					Messenger
				</a>
			</div>
			<div class="dropdown dropdown-custom mmenu text-center">
				<a href="<?php echo Yii::$app->urlManager->createUrl(['whoisaround/index']); ?>" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
				<img src="<?=$baseUrl?>/images/foot-places.png" />
					People
				</a>					
			</div> 
			<div class="dropdown-button more_btn footer-menu dropdown-custom mmenu text-center" onclick="openalert()">
				<a href="javascript:void(0)">
				<img src="<?=$baseUrl?>/images/foot-more.png" />
					Alert
				</a>
			</div>		
		</div>
		<div class="master_alert">
		   <div class="littlemaster_alert">
		      <a href="<?php echo Yii::$app->urlManager->createUrl(['site/travpeople']); ?>">
		         <span class="icon default-icon">
		            <img src="<?=$baseUrl?>/images/friendreq-icon.png">
		         </span>Friend Requests
		      </a>
		   </div>
		   <div class="littlemaster_alert">
		      <a href="<?php echo Yii::$app->urlManager->createUrl(['site/messages']); ?>">
		         <span class="icon default-icon">
		            <img src="<?=$baseUrl?>/images/message-icon.png">
		         </span>Messages
		      </a>
		   </div>
		   <div class="littlemaster_alert">
		      <a href="<?php echo Yii::$app->urlManager->createUrl(['site/travnotifications']); ?>">
		         <span class="icon default-icon">
		            <img src="<?=$baseUrl?>/images/notification-icon.png">
		         </span>Notifications
		      </a>
		   </div>
		</div>
	</div>
	<div class="main-footer">
		<ul class="center-align">
			<li><a href="javascript:void(0)">About</a></li>
			<li><a href="javascript:void(0)">Privacy</a></li>
			<li><a href="javascript:void(0)">Invite</a></li>
			<li><a href="javascript:void(0)">Terms</a></li>
			<li><a href="javascript:void(0)">Contact Us</a></li>
			<li><a href="javascript:void(0)">Features</a></li>
			<li><a href="javascript:void(0)">Mobile</a></li>
			<li><a href="javascript:void(0)">Developers</a></li>
		</ul>
	</div>
</div>