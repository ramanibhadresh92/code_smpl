<?php
/* @var $this \yii\web\View */
/* @var $content string */
use frontend\assets\AppAsset; 
use frontend\models\UserForm;
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$user_id = (string)$session->get('user_id');
$email = $session->get('email');

$checkuserauthclass = 'checkuserauthclassg';
if(isset($user_id) && $user_id != '') {
    $checkuserauthclass = UserForm::isUserExistByUid($user_id);
}

$userinfo = array('id' => (string)$user_id);
?> 
<link href="<?=$baseUrl?>/css/emoticons.css" rel="stylesheet">
<link href="<?=$baseUrl?>/css/emostickers.css" rel="stylesheet">

<div class="float-chat anim-side">
	<?php if($checkuserauthclass != 'checkuserauthclassg' && $checkuserauthclass != 'checkuserauthclassnv') { ?>
	<div class="chat-button float-icon"><span class="icon-holder">icon</span></div>
	<div class="chat-section data-loading">
		<a href="javascript:void(0)" class="close-chat"><i class="mdi mdi-close mdi-20px"></i>`</a>
		<div class="loading-holder">
			<div class="chat-tabs">						
				<div class="chat-controls actions">
					<div class="chat-search"> 								
						<input type="text" class="chatwallsearch" placeholder="search a keyword">				
						<div class="btn-holder">
							<a href="javascript:void(0)"><i class="zmdi zmdi-search"></i></a>
							<a onclick="openChatSearch(this,'close')" href="javascript:void(0)"><i class="mdi mdi-close"></i></a>
						</div>
					</div>		
					<div class="dropdown dropdown-custom">		
						<a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="search_online">
							<i class="zmdi zmdi-more"></i>
						</a>
						<ul id="search_online" class="dropdown-content custom_dropdown search_online_new">
							<li>
								<ul  class="tabs">
									<li class="tab active"><a href="#chat-connections" data-toggle="tab" aria-expanded="false" onclick="openChatSearch(this,'close'),callBuddies();">Buddies</a></li>
									<li class="tab"><a href="#chat-online" data-toggle="tab" aria-expanded="true" onclick="openChatSearch(this,'close'),callOnlineUsers()">Online</a></li>
									<li class="tab callRecentMessagesUsers"><a href="#chat-recent" data-toggle="tab" aria-expanded="false" onclick="openChatSearch(this,'close');">Recent</a></li>
								</ul>
							</li>
							<li><a href="javascript:void(0)" data-toggle="tab" aria-expanded="false" onclick="openChatSearch(this,'open')" href="javascript:void(0)">Search</a></li>
						</ul>		
					</div>
				</div>	
				<div class="tab-content">
					<div class="tab-pane fade connectionschat-pane active in" id="chat-connections">
						<span class="ctitle">Buddies</span>
						<div class="nice-scroll chatlist-scroll">
						</div>
					</div>
					<div class="tab-pane fade recentchat-pane" id="chat-recent">
						<span class="ctitle">Recent</span>
						<div class="nice-scroll recentchat-scroll">
						</div>
					</div>
					<div class="tab-pane fade chat_cont_new left" id="chat-online">
						<span class="ctitle">Online</span>
						<div class="nice-scroll recentchat-scroll">
						</div>
					</div>
				</div>
		   </div>
	   </div>
	   <div class="chat-window">
			<a href="javascript:void(0)" class="backChatList" onclick="closeChatboxes()"><i class="mdi mdi-menu-left"></i> Back to list</a>
			<ul class="mainul">
			</ul>
		</div>	
	</div>
	<?php } else { ?>
	<div class="chat-button float-icon <?=$checkuserauthclass?> directcheckuserauthclass"><span class="icon-holder">icon</span></div>
	<?php } ?>
</div>
<style>
	mark { 
	  padding: 0;
	  background-color: red;
	}
</style> 
<script src="<?=$baseUrl?>/js/chat.js" type="text/javascript"></script> 
<script src="<?=$baseUrl?>/js/messages-handler.js"></script>
<!-- 
<script src="<?=$baseUrl?>/js/emoticons.js"></script>
<script src="<?=$baseUrl?>/js/custom-emotions.js"></script>
<script src="<?=$baseUrl?>/js/emostickers.js"></script> 
<script src="<?=$baseUrl?>/js/custom-emostickers.js"></script>

<script src="<?=$baseUrl?>/js/socket.io.js"></script>
<script src="<?=$baseUrl?>/js/messages-function.js"></script>

<script type="text/javascript">
	var data2 = <?php echo (isset($userinfo) && !empty($userinfo)) ? json_encode($userinfo) : '';?>;
    if(data2) {
        if (window.location.href.indexOf("localhost") > -1) {
            var socket = io('http://localhost:3000'); ////////// LOCAL
        } else {
            var socket = io('54.190.17.213:3000'); ////////// LIVE
        }
        socket.emit('userInfo', data2);
    }
</script>
-->
<?php 
exit;

