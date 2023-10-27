<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use frontend\models\LoginForm;
use frontend\models\CountryCode;
use frontend\models\UserSetting;
use frontend\models\Personalinfo;
use frontend\models\SecuritySetting;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email'); 
$user_id = (string)$session->get('user_id');
$result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();

$restricted_list = (isset($result_security['restricted_list']) && !empty($result_security['restricted_list'])) ? $result_security['restricted_list'] : '';
$restricted_list = explode(',', $restricted_list);
$restricted_list = array_values(array_filter($restricted_list));   
$restricted_list_label = SecuritySetting::getFullnamesWithToolTip($restricted_list, 'restricted_list_label');

$blocked_list = (isset($result_security['blocked_list']) && !empty($result_security['blocked_list'])) ? $result_security['blocked_list'] : '';
$blocked_list = explode(',', $blocked_list);
$blocked_list = array_values(array_filter($blocked_list));   
$blocked_list_label = SecuritySetting::getFullnamesWithToolTip($blocked_list, 'blocked_list_label');


$message_filtering = (isset($result_security['message_filtering']) && !empty($result_security['message_filtering'])) ? $result_security['message_filtering'] : '';
$message_filtering = explode(',', $message_filtering);
$message_filtering = array_values(array_filter($message_filtering));   
$message_filter_label = SecuritySetting::getFullnamesWithToolTip($message_filtering, 'message_filter_label');

$request_filter = (isset($result_security['request_filter']) && !empty($result_security['request_filter'])) ? $result_security['request_filter'] : '';
$request_filter = explode(',', $request_filter);
$request_filter = array_values(array_filter($request_filter));   
$request_filter_label = SecuritySetting::getFullnamesWithToolTip($request_filter, 'request_filter_label');
?>
<!-- Restricted List -->
<li>
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 ">
					<div class="caption">
						<label>Restricted List</label>
					</div>
				</div>
				<div class="col s12 m9 l9 htmlblockput restricted_list_label" id="restricted_list_label">
					<?=$restricted_list_label?>
				</div>				
			</div>	
		</div>
	</div>
</li>

<!-- Blocked List -->
<li>
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3">
					<div class="caption">
						<label>Blocked List</label>
					</div>
				</div>
				<div class="col s12 m9 l9 blocked_list_label" id="blocked_list_label">
					<?=$blocked_list_label?>
				</div>
			</div>	
		</div>
	</div>
</li>

<!-- Message Filtering -->
<li>
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 ">
					<div class="caption">
						<label>Message filter</label>
					</div>
				</div>
				<div class="col s12 m9 l9 message_filter_label" id="message_filter_label">
					<?=$message_filter_label?>		
				</div>					
			</div>	
		</div>
	</div>
</li>

<li>
	<div class="settings-group">
		<div class="edit-mode">
			<div class="row">
				<div class="col s12 m3 l3 ">
					<div class="caption">
						<label>Request filter</label>
					</div>
				</div>
				<div class="col s12 m9 l9 request_filter_label" id="request_filter_label">	
					<?=$request_filter_label?>			
				</div>					
			</div>	
		</div>
	</div>
</li>

<li>
    <div class="personal-info fullwidth edit-mode">
        <div class="right">                                   
           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_act_blocking_cl(false)">Cancel</a>                                    
           <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_act_blocking_cl(true)">Save</a>
        </div>
    </div>
</li>
<?php
exit;