<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;

class LocaldriverPostInviteMsgs extends ActiveRecord
{
    public static function collectionName()
    {
        return 'localdriver_post_invite_msgs';
    }

    public function attributes()
    {
        return ['_id', 'postinvite_id', 'from_id', 'to_id', 'message', 'created_at', 'updated_at', 'is_read'];
    }

    public function getlastmsg($postinvite_id, $uid) {
        $result = array();
        if($postinvite_id) {
            $resultInfo = LocaldriverPostInviteMsgs::find()->select(['message', 'created_at', 'from_id', 'is_read'])->where(['postinvite_id' => $postinvite_id])->orderby('_id DESC')->asarray()->one();
            if(!empty($resultInfo)) {
                return json_encode($resultInfo);
                exit;
            }

        }   
        return json_encode($result);
        exit;
    }

    public function fetchmsghistory($invitationId, $user_id) {
        $msgs = array();
        if($invitationId != '') {
            $inviteinfo = LocaldriverPostInvite::find()->where([(string)'_id' => $invitationId])->asarray()->one(); 
            if(!empty($inviteinfo)) {
                $from_id = $inviteinfo['user_id'];
                $post_id = $inviteinfo['post_id'];
                $inviteinfo['is_invitation_message'] = true;

                if($from_id == $user_id) {
                    $postInfo = LocaldriverPost::find()->select(['user_id'])->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->asarray()->one();
                    if(!empty($postInfo)) {
                        $to_id = $postInfo['user_id'];
                    }
                } else {
                    $to_id = $user_id;
                }

                $inviteinfo['from_id'] = $from_id;
                $msgs = LocaldriverPostInviteMsgs::find()->where(['postinvite_id' => $invitationId, 'from_id' => $from_id, 'to_id' => $to_id])->orwhere(['postinvite_id' => $invitationId, 'to_id' => $from_id, 'from_id' => $to_id])->orderby('_id DESC')->asarray()->all();
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
        $to_id = LocaldriverPostInvite::find()->select(['user_id', 'post_id'])->where([(string)'_id' => $id])->asarray()->one();
        if(!empty($to_id)) {
            $post_id = $to_id['post_id'];
            $to_id = $to_id['user_id'];
            if($to_id == $from_id) {
                
                $to_id = LocaldriverPost::find()->select(['user_id'])->where([(string)'_id' => $post_id])->andWhere(['not','flagger', "yes"])->asarray()->one();
                if(!empty($to_id)) {
                    $to_id = $to_id['user_id'];
                }
            }
        }

        if($from_id != $to_id) {

            // check is blocked....
            $ss = SecuritySetting::find()->where(['user_id' => $to_id])->asarray()->one();
            if(!empty($ss)) {
                $isBlockedRequest = isset($ss['localdriver_users_request_blocked']) ? $ss['localdriver_users_request_blocked'] : '';
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
            $Localdriver = new LocaldriverPostInviteMsgs();
            $Localdriver->postinvite_id = $id;
            $Localdriver->from_id = $from_id;
            $Localdriver->to_id = $to_id;
            $Localdriver->message = $message;
            $Localdriver->created_at = $created_at;
            $Localdriver->save();
            
            if ($created_at != '') {
                $created_at = date("Y/m/d H:i A", $created_at);       
            }

            $messageId = $Localdriver->_id;
                    
            $userinfo = UserForm::find()->select(['fullname', 'gender', 'photo', 'thumbnail'])->where([(string)'_id' => $user_id])->asarray()->one();
            $data =  array('status' => false);
            if(!empty($userinfo)) {
                $fullname = $userinfo['fullname'];
                $thumbnail = Yii::$app->GenCls->getimage($user_id, 'thumb');
                 $msgli ='<li id="message_'.$messageId.'">
                    <div class="img-holder"><img src="'.$thumbnail.'"/></div>
                    <div class="desc-holder">
                        <h5>'.$fullname.'</h5>
                        <div class="settings-icon custom-ul-drop">
                            <a class="dropdown-button more_btn " href="javascript:void(0)" data-activates="messageulid_'.$messageId.'">
                              <i class="zmdi zmdi-more"></i>
                            </a>
                            <ul id="messageulid_'.$messageId.'" class="dropdown-content custom_dropdown " >
                                <li><a href="javascript:void(0)" onclick="deletemessage(\''.$messageId.'\')">Delete this message</a></li>
                            </ul>
                            <!-- Dropdown Structure -->
                        </div>
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
            LocaldriverPostInviteMsgs::updateAll(['is_read' => true], ['postinvite_id' => $id]);
            return true;
            exit;
        }
        return false;
        exit;
    }  

    public function deleteoffer($id,$user_id) {
        if($id != '') {
            $LocaldriverPostInvite = LocaldriverPostInvite::findOne($id);
            $LocaldriverPostInvite->delete();

            $LocaldriverPostInviteMsgs = LocaldriverPostInviteMsgs::deleteAll(['postinvite_id' => $id]);
            return true;
            exit;
        }
        
        return false;
        exit;
    }  

   public function blockeventrequestuser($id, $user_id) {
        if($id) {
            $getUid = LocaldriverPostInviteMsgs::find()->select(['from_id', 'to_id'])->where(['postinvite_id' => $id, 'from_id' => $user_id])->orWhere(['postinvite_id' => $id, 'to_id' => $user_id])->asarray()->one();

            $fetchID = '';
            if(!empty($getUid)) {
                $fetchID = $getUid['from_id'];
                if($fetchID == $user_id) {
                    $fetchID = $getUid['to_id'];
                }
            } else {
                $getUid = LocaldriverPostInvite::find()->select(['user_id','post_id'])->where([(string)'_id' => $id])->asarray()->one();
                $id = isset($getUid['post_id']) ? $getUid['post_id'] : '';
                if(!empty($getUid)) {
                    $fetchID = $getUid['user_id'];
                }

                if($fetchID == '' || $fetchID == $user_id) {
                    $getUid = LocaldriverPost::find()->select(['user_id'])->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
                    if(!empty($getUid)) {
                        $fetchID = $getUid['user_id'];
                    }
                }
            }
            
            if(isset($fetchID) && $fetchID != '') {
                $getInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($getInfo)) {
                    $localdriver_users_request_blocked = isset($getInfo['localdriver_users_request_blocked']) ? $getInfo['localdriver_users_request_blocked'] : '';
                    if(!empty($localdriver_users_request_blocked)) {
                        $localdriver_users_request_blocked = explode(",", $localdriver_users_request_blocked);
                        if(!in_array($fetchID, $localdriver_users_request_blocked)) {
                            $localdriver_users_request_blocked[] = $fetchID;
                            $localdriver_users_request_blocked = implode(",", $localdriver_users_request_blocked);                            

                            $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                            $SecuritySetting->localdriver_users_request_blocked = $localdriver_users_request_blocked;
                            $SecuritySetting->update();
                            return true;
                            exit;
                        }
                    } else {
                        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                        $SecuritySetting->localdriver_users_request_blocked = $fetchID;
                        $SecuritySetting->update();
                        return true;
                        exit;
                    }

                } 
                else {
                    $SecuritySetting = new SecuritySetting();
                    $SecuritySetting->user_id = $user_id;
                    $SecuritySetting->localdriver_users_request_blocked = $fetchID;
                    $SecuritySetting->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }

    public function getLstMsg($id, $user_id) {
        if($id) {
            $getlastmsg = LocaldriverPostInviteMsgs::find()->select(['message', 'is_read', 'from_id'])->where(['postinvite_id' => $id])->orderby('_id DESC')->asarray()->one();

            if(!empty($getlastmsg)) {
                $message = $getlastmsg['message'];
                $sender = $getlastmsg['from_id'];
                $self = false;
                if($sender == $user_id) {
                    $self = true;
                }

                $is_read = isset($getlastmsg['is_read']) ? $getlastmsg['is_read'] : false;
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
