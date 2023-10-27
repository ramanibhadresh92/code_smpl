<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\SecuritySetting;
use frontend\models\HangoutEvent;
use frontend\models\HangoutEventInvite;

class HangoutEventInviteMsgs extends ActiveRecord
{
     public static function collectionName()
    {
        return 'hangout_event_invite_msgs';
    }

    public function attributes()
    {
        return ['_id', 'event_request_id', 'from_id', 'to_id', 'message', 'is_read', 'created_at'];
    }

    public function fetchmsghistory($invitationId, $user_id) {
        $msgs = array();
        if($invitationId != '') {
            $inviteinfo = HangoutEventInvite::find()->where([(string)'_id' => $invitationId])->asarray()->one();
            if(!empty($inviteinfo)) {
                $from_id = $inviteinfo['user_id'];
                $event_id = $inviteinfo['event_id'];
                if($from_id == $user_id) {
                    $eventInfo = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $event_id])->asarray()->one();
                    if(!empty($eventInfo)) {
                        $to_id = $eventInfo['user_id'];
                    }
                } else {
                    $to_id = $user_id;
                }

                $inviteinfo['from_id'] = $from_id;
                $msgs = HangoutEventInviteMsgs::find()->where(['event_request_id' => $invitationId, 'from_id' => $from_id, 'to_id' => $to_id])->orwhere(['event_request_id' => $invitationId, 'to_id' => $from_id, 'from_id' => $to_id])->orderby('_id DESC')->asarray()->all();
                $msgs[] = $inviteinfo;
                if(!empty($msgs)) {
                    return json_encode($msgs, true);
                    exit;
                }
            }
        }
        return json_encode($msgs, true);
        exit;
    }  

    public function sendmessage($post, $user_id)
    {
        $from_id = $user_id;
        $id = $post['invitationId'];
        $to_id = HangoutEventInvite::find()->select(['user_id', 'event_id'])->where([(string)'_id' => $id])->asarray()->one();
        if(!empty($to_id)) {
            $event_id = $to_id['event_id'];
            $to_id = $to_id['user_id'];
            if($to_id == $from_id) {
                
                $to_id = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $event_id])->asarray()->one();
                if(!empty($to_id)) {
                    $to_id = $to_id['user_id'];
                }
            }
        }

        if($from_id != $to_id) {
            $ss = SecuritySetting::find()->where(['user_id' => $to_id])->asarray()->one();
            if(!empty($ss)) {
                $isBlockedRequest = isset($ss['hangout_users_request_blocked']) ? $ss['hangout_users_request_blocked'] : '';
                if($isBlockedRequest != '') {
                    $isBlockedRequest = explode(",", $isBlockedRequest);
                    if(in_array($user_id, $isBlockedRequest)) {
                        $data = array('status' => false, 'reason' => 'block');
                        return json_encode($data, true);
                        exit;
                    }
                }
            }

            $message = $post['message'];
            $created_at = time();
            $Localguide = new HangoutEventInviteMsgs();
            $Localguide->event_request_id = $id;
            $Localguide->from_id = $from_id;
            $Localguide->to_id = $to_id;
            $Localguide->message = $message;
            $Localguide->created_at = $created_at;
            $Localguide->save();
            
            if ($created_at != '') {
                $created_at = date("Y/m/d H:i A", $created_at);       
            }
                    
            $userinfo = UserForm::find()->select(['fullname', 'gender', 'photo', 'thumbnail'])->where([(string)'_id' => $user_id])->asarray()->one();
            $data =  array('status' => false);
            if(!empty($userinfo)) {
                $fullname = $userinfo['fullname'];
                $thumbnail = Yii::$app->GenCls->getimage($user_id, 'thumb');
                $msgli ='<li>
                    <div class="img-holder"><img src="'.$thumbnail.'"/></div>
                    <div class="desc-holder">
                        <h5>'.$fullname.'</h5>
                        <span class="timestamp">'.$created_at.'</span>
                        <div class="offer-msg">
                            <p>'.$message.'</p>
                        </div>
                    </div>
                </li>';

                $data = array('status' => true, 'data' => $msgli);
                return json_encode($data, true);
                exit;
            }
        }
        $data = array('status' => false);
        return json_encode($data, true);
        exit;
    }


    public function invitationisread($id) {
        if($id) {
            HangoutEventInviteMsgs::updateAll(['is_read' => true], ['event_request_id' => $id]);
            return true;
            exit;
        }
        return false;
        exit;
    }  

    public function deleteoffer($id,$user_id) {
        if($id != '') {
            $Hangout = HangoutEventInvite::findOne($id);
            $Hangout->delete();

            $Hangout = HangoutEventInviteMsgs::deleteAll(['event_request_id' => $id]);
            return true;
            exit;
        }
        
        return false;
        exit;
    }  

   public function blockeventrequestuser($id, $user_id) {
        if($id) {
             $getUid = HangoutEventInviteMsgs::find()->select(['from_id', 'to_id'])->where(['event_request_id' => $id, 'from_id' => $user_id])->orWhere(['event_request_id' => $id, 'to_id' => $user_id])->asarray()->one();

            $fetchID = '';
            if(!empty($getUid)) {
                $fetchID = $getUid['from_id'];
                if($fetchID == $user_id) {
                    $fetchID = $getUid['to_id'];
                }
            } else {
                $getUid = HangoutEventInvite::find()->select(['user_id','event_id'])->where([(string)'_id' => $id])->asarray()->one();
            
                $id = isset($getUid['event_id']) ? $getUid['event_id'] : '';
                if(!empty($getUid)) {
                    $fetchID = $getUid['user_id'];
                }

                if($fetchID == '' || $fetchID == $user_id) {
                    $getUid = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $id])->asarray()->one();
                
                    if(!empty($getUid)) {
                        $fetchID = $getUid['user_id'];
                    }
                }
            }

            if(isset($fetchID) && $fetchID != '') {
                $getInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($getInfo)) {
                    $hangout_users_request_blocked = isset($getInfo['hangout_users_request_blocked']) ? $getInfo['hangout_users_request_blocked'] : '';
                    if(!empty($hangout_users_request_blocked)) {
                        $hangout_users_request_blocked = explode(",", $hangout_users_request_blocked);
                        if(!in_array($fetchID, $hangout_users_request_blocked)) {
                            $hangout_users_request_blocked[] = $fetchID;
                            $hangout_users_request_blocked = implode(",", $hangout_users_request_blocked);                            

                            $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                            $SecuritySetting->hangout_users_request_blocked = $hangout_users_request_blocked;
                            $SecuritySetting->update();
                            return true;
                            exit;
                        }
                    } else {
                        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                        $SecuritySetting->hangout_users_request_blocked = $fetchID;
                        $SecuritySetting->update();
                        return true;
                        exit;
                    }

                } 
                else {
                    $SecuritySetting = new SecuritySetting();
                    $SecuritySetting->user_id = $user_id;
                    $SecuritySetting->hangout_users_request_blocked = $fetchID;
                    $SecuritySetting->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }

    public function getLastMessage($id, $user_id) {
        if($id) {
            $getlastmsg = HangoutEventInviteMsgs::find()->where(['event_request_id' => $id])->orderby('_id DESC')->asarray()->one();

            if(!empty($getlastmsg)) {
                $message = $getlastmsg['message'];
                $sender = $getlastmsg['from_id'];
                if($sender == $user_id) {
                    $self = true;
                } else {
                    $self = false;
                }

                $is_read = isset($getlastmsg['is_read']) ? $getlastmsg['is_read'] : false;
                $result = array('status' => true, 'message' => $message, 'is_read' => $is_read, 'self' => $self);
                return json_encode($result, true);
                exit;
            } else {
                $getlastmsg = HangoutEventInvite::find()->where([(string)'_id' => $id])->asarray()->one();
                $getUserId = $getlastmsg['user_id'];
                $is_read = isset($getlastmsg['is_read']) ? $getlastmsg['is_read'] : false;
                $message = $getlastmsg['message'];
                if($getUserId == $user_id) {
                    $self = true;
                } else {
                    $self = false;
                }

                $result = array('status' => true, 'message' => $message, 'is_read' => $is_read, 'self' => $self);
                return json_encode($result, true);
                exit;
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }
}