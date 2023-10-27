<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class CommunicationSettings extends ActiveRecord
{
    public static function collectionName()
    {
        return 'communication_settings';
    }

    public function attributes()
    {
        return ['_id', 'user_id','is_received_message_tone_on', 'is_new_message_display_preview_on', 'communication_label', 'show_away', 'is_send_message_on_enter', 'updated_at'];
    }

    public function communicationsettings() {
    	$session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $isNew = false;
        if(isset($user_id) && $user_id != '') {
	    	if(isset($_POST) && !empty($_POST)) {
	    		
	    		$is_received_message_tone_on = $_POST['is_received_message_tone_on'];
			    $is_new_message_display_preview_on = $_POST['is_new_message_display_preview_on'];
			    if(isset($_POST['communication_label'])) {
			    	$communication_label = $_POST['communication_label'];
			    }
			    $show_away = $_POST['show_away'];
			    $is_send_message_on_enter = $_POST['is_send_message_on_enter'];

			    $data = CommunicationSettings::find()->where(['user_id' => $user_id])->one();
			    if(empty($data)) {
			    	$isNew = true;
			    	$data = new CommunicationSettings();
			    	$data->user_id = $user_id;
			    }

			    $data->is_received_message_tone_on = $is_received_message_tone_on;
			    $data->is_new_message_display_preview_on = $is_new_message_display_preview_on;
			    if(isset($_POST['communication_label'])) {
			    	$data->communication_label = $communication_label;
			    }
			    $data->show_away = $show_away;
			    $data->is_send_message_on_enter = $is_send_message_on_enter;

			    if($isNew) {
			    	$data->save();
			    } else {
					$data->update();
			    }

			    return true;
	    	}
	    }
    }
} 