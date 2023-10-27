<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Messages;
class StarMessages extends ActiveRecord
{
    public static function collectionName()
    {
        return 'message_star';
    }

    public function attributes()
    {
        return ['_id', 'starmessage_user_id', 'starmessage_star_ids', 'starmessage_created_at', 'starmessage_updated_at'];
    }
	
	public function addstarmessage($new, $user_id)
    {
    	$StarMessages = StarMessages::find()->where(['starmessage_user_id' => $user_id])->one();
    	if(!empty($StarMessages)) {
			$StarMessagesIDS = isset($StarMessages['starmessage_star_ids']) ? $StarMessages['starmessage_star_ids'] : '';
			$exist = explode(',', $StarMessagesIDS);

			$ISOA = array_intersect($exist, $new);

			if(!empty($ISOA)) {
				$combine = array_diff($exist, $new);
				$combine = array_values(array_filter($combine));
				$combine = implode(",", $combine);
				$StarMessages->starmessage_star_ids = $combine;
				$StarMessages->update();

				$result = array('status' => true, 'label' => 'Unsaved');
				return json_encode($result, true);
			} else {
				$combine = array_merge($exist, $new);
				$combine = array_values(array_filter($combine));
				$combine = implode(",", $combine);
				$StarMessages->starmessage_star_ids = $combine;
				$StarMessages->update();

				$result = array('status' => true, 'label' => 'Saved');
				return json_encode($result, true);
			}
    	} else {
    		$new = array_values(array_filter($new));
    		$ids = implode(',', $new);
	    	$StarMessages = new StarMessages();
	    	$StarMessages->starmessage_user_id = $user_id;
	    	$StarMessages->starmessage_star_ids = $ids;
	    	$StarMessages->starmessage_created_at = time();
	    	$StarMessages->save();
	    	
	    	$result = array('status' => true, 'label' => 'Saved');
	    	return json_encode($result, true);
	    }

	    $result = array('status' => false);
	    return json_encode($result, true);
	}

	public function addstarmessagebulk($new, $user_id)
    {
    	$StarMessages = StarMessages::find()->where(['starmessage_user_id' => $user_id])->one();
    	if(!empty($StarMessages)) {
			$StarMessagesIDS = isset($StarMessages['starmessage_star_ids']) ? $StarMessages['starmessage_star_ids'] : '';
			$exist = explode(',', $StarMessagesIDS);
			$new = array_values(array_filter($new));

			$combine = array_merge($exist, $new);
			$combine = array_values(array_filter($combine));
			$combine = array_unique($combine);
			$combine = implode(",", $combine);
				
			$StarMessages->starmessage_star_ids = $combine;
			$StarMessages->update();
			
			$result = array('status' => true, 'label' => 'Saved');
			return json_encode($result, true);
    	} else {
    		$new = array_values(array_filter($new));
    		$ids = implode(',', $new);
	    	$StarMessages = new StarMessages();
	    	$StarMessages->starmessage_user_id = $user_id;
	    	$StarMessages->starmessage_star_ids = $ids;
	    	$StarMessages->starmessage_created_at = time();
	    	$StarMessages->save();
	    	
	    	$result = array('status' => true, 'label' => 'Saved');
	    	return json_encode($result, true);
	    }

	    $result = array('status' => false);
	    return json_encode($result, true);
	}

	public function unsavedmessage($id, $user_id)
    {
    	$StarMessages = StarMessages::find()->where(['starmessage_user_id' => $user_id])->one();
    	if(!empty($StarMessages)) {
			$StarMessagesIDS = isset($StarMessages['starmessage_star_ids']) ? $StarMessages['starmessage_star_ids'] : '';
			$exist = explode(',', $StarMessagesIDS);
			
			if (($key = array_search($id, $exist)) !== false) {
			    unset($exist[$key]);
			}

			$exist = array_values(array_filter($exist));
			$exist = array_unique($exist);
			$exist = implode(",", $exist);
				
			$StarMessages->starmessage_star_ids = $exist;
			$StarMessages->update();
			
			$result = array('status' => true, 'label' => 'Unsaved');
			return json_encode($result, true);
    	} 

	    $result = array('status' => false);
	    return json_encode($result, true);
	}

	public function getallsavedmsg($user_id) {
		$StarMessages = StarMessages::find()->where(['starmessage_user_id' => $user_id])->one();
    	if(!empty($StarMessages)) {
			$StarMessagesIDS = isset($StarMessages['starmessage_star_ids']) ? $StarMessages['starmessage_star_ids'] : '';
			$StarMessagesIDS = explode(',', $StarMessagesIDS);
			$StarMessagesIDS = array_values(array_filter($StarMessagesIDS));

			if(!empty($StarMessagesIDS)) {
				$data = Messages::find()->where(['in',(string)'_id', $StarMessagesIDS])->asarray()->all();
				if(!empty($data)) {
					foreach ($data as $singleData) {
					$messageID = (string)$singleData['_id'];
					if($user_id == $singleData['from_id']) {
						$otherID = $singleData['to_id'];
					} else{
						$otherID = $singleData['from_id'];
					}
					$thumbnail = Yii::$app->GenCls->getimage($otherID,'thumb');
					$fullname = Yii::$app->GenCls->getuserdata($otherID,'fullname');
					$time = (string)$singleData['created_at']->sec;
					$rand = rand(999, 9999);
					$uniqID = $rand.'_'.$time.'_'.$messageID;
					$time = date('d M, Y', $time);
					$type = $singleData['type'];
					$message = $singleData['reply'];
					?>
					<div class="msg_division ">
					    <span class="msg_profile">
					    	<img src="<?=$thumbnail?>" />
					    </span>
					    <div class="msg_info">
					        <span class="person_send"> <?=$fullname?> </span>
					        <span class="link_dot"></span>
					        <span class="person_receive"> You </span>
					        <div class="settings-icon saved_msg_setting_icon">
					            <a class="dropdown-button" href="javascript:void(0)" data-activates="<?=$uniqID?>">
					            	<i class="zmdi zmdi-more"></i>
					            </a>
					            <ul id="<?=$uniqID?>" class="dropdown-content custom_dropdown">
					                <li> <a>Download</a> </li>
					                <li> <a>Forward message</a> </li>
					                <li data-id="<?=$messageID?>" onclick="unsavedmessage(this)"> <a>Unsaved message</a> </li>
					                <li data-id="<?=$messageID?>" onclick="deletemessage_sm(this)"> <a>Delete message</a> </li>
					            </ul>
					        </div>
					        <span class="day_time"><?=$time?></span>
					        <div class="msg_div_border">
					        	<div class="msg_text msg_content">
								<?php
								if($type == 'text') {
									echo $message;
								} else if($type == 'image') {
									echo '<img src="'.$message.'">';
								}
								?>
					           </div>
					        </div>
					    </div>
					</div>
					<?php
					}
				}
			}

		}
	}

	public function getparticularusersavedmsg($id, $user_id) {
		$StarMessages = StarMessages::find()->where(['starmessage_user_id' => $user_id])->one();
    	if(!empty($StarMessages)) {
			$StarMessagesIDS = isset($StarMessages['starmessage_star_ids']) ? $StarMessages['starmessage_star_ids'] : '';
			$StarMessagesIDS = explode(',', $StarMessagesIDS);
			$StarMessagesIDS = array_values(array_filter($StarMessagesIDS));

			if(!empty($StarMessagesIDS)) {
				$data = Messages::find()->where(['in',(string)'_id', $StarMessagesIDS])->asarray()->all();
				if(!empty($data)) {
					foreach ($data as $singleData) {
					$messageID = (string)$singleData['_id'];
					if($user_id == $singleData['from_id']) {
						$otherID = $singleData['to_id'];
					} else{
						$otherID = $singleData['from_id'];
					}

					if($otherID != $id) {
						continue;
					}
					
					$thumbnail = Yii::$app->GenCls->getimage($otherID,'thumb');
					$fullname = Yii::$app->GenCls->getuserdata($otherID,'fullname');
					$time = (string)$singleData['created_at']->sec;
					$rand = rand(999, 9999);
					$uniqID = $rand.'_'.$time.'_'.$messageID;
					$time = date('d M, Y', $time);
					$type = $singleData['type'];
					$message = $singleData['reply'];
					?>
					<div class="msg_division ">
					    <span class="msg_profile">
					    	<img src="<?=$thumbnail?>" />
					    </span>
					    <div class="msg_info">
					        <span class="person_send"> <?=$fullname?> </span>
					        <span class="link_dot"></span>
					        <span class="person_receive"> You </span>
					        <div class="settings-icon saved_msg_setting_icon">
					            <a class="dropdown-button" href="javascript:void(0)" data-activates="<?=$uniqID?>">
					            	<i class="zmdi zmdi-more"></i>
					            </a>
					            <ul id="<?=$uniqID?>" class="dropdown-content custom_dropdown">
					                <li> <a>Download</a> </li>
					                <li> <a>Forward message</a> </li>
					                <li data-id="<?=$messageID?>" onclick="unsavedmessage(this)"> <a>Unsaved message</a> </li>
					                <li data-id="<?=$messageID?>" onclick="deletemessage_sm(this)"> <a>Delete message</a> </li>
					            </ul>
					        </div>
					        <span class="day_time"><?=$time?></span>
					        <div class="msg_div_border">
					        	<div class="msg_text msg_content">
								<?php
								if($type == 'text') {
									echo $message;
								} else if($type == 'image') {
									echo '<img src="'.$message.'">';
								}
								?>
					           </div>
					        </div>
					    </div>
					</div>
					<?php
					}
				}
			}

		}
	}
}