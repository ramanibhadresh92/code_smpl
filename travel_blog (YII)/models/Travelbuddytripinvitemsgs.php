<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Travelbuddy; 
use frontend\models\Travelbuddytrip;

class Travelbuddytripinvitemsgs extends ActiveRecord
{
    public static function collectionName()
    {
        return 'travelbuddy_trip_invite_msgs';
    }

    public function attributes()
    {
        return ['_id', 'invitationId', 'from_id', 'to_id', 'type', 'message', 'created_at', 'updated_at', 'is_read'];
    }

    public function getlastmsg($invitationId, $uid) {
        $result = array();
        if($invitationId) {
            $resultInfo = Travelbuddytripinvitemsgs::find()->select(['message', 'created_at', 'from_id', 'is_read'])->where(['invitationId' => $invitationId])->orderby('_id DESC')->asarray()->one();
            if(!empty($resultInfo)) {
                return json_encode($resultInfo);
                exit;
            }

        }   
        return json_encode($result);
        exit;
    }

    public function getsomemsgs($invitationId, $from_id, $to_id) {
        $information = array();
        $msgli = '';
        if($from_id != $to_id) {
            $data =  Travelbuddytripinvitemsgs::find()->where(['invitationId' => $invitationId, 'from_id' => $from_id, 'to_id' => $to_id])->orwhere(['invitationId' => $invitationId, 'from_id' => $to_id, 'to_id' => $from_id])->limit(10)->asarray()->all();
            if(!empty($data)) {
                foreach ($data as $key => $single) {
                    $realfrom_id = $single['from_id'];                   
                    $userinfo = UserForm::find()->select(['fullname', 'thumbnail', 'gender', 'city', 'country', 'photo'])->where([(string)'_id' => $realfrom_id])->asarray()->one();
                    if(!empty($userinfo)) {
                        $thumbnail = Yii::$app->GenCls->getimage($realfrom_id, 'thumb');
                        $fullname = $userinfo['fullname'];
                        $message = $single['message'];
                        $created_at = $single['created_at'];

                        $msgli .='<li>
                                    <div class="img-holder"><img src="'.$thumbnail.'"/></div>
                                    <div class="desc-holder">
                                        <h5>'.$fullname.'</h5>
                                        <span class="timestamp">'.$created_at.'</span>
                                        <div class="offer-msg">
                                            <p>'.$message.'</p>
                                        </div>
                                    </div>
                                </li>';
                    }
                }
                $information = array('status' => true, 'data' => $msgli);
                return $information;
                exit;
            }
        }
        $information = array('status' => false);
        return $information;
        exit;
    }

    public function sendmessage($post, $user_id)
    {
        $uniqId = rand(9999, 99999) . time();
        $from_id = $user_id;
        $id = $post['invitationId'];
        $to_id = Travelbuddytripinvite::find()->select(['user_id', 'trip_id'])->where([(string)'_id' => $id])->asarray()->one();
        if(!empty($to_id)) {
            $post_id = $to_id['trip_id'];
            $to_id = $to_id['user_id'];
            if($to_id == $from_id) {
                $tempto_id = '';
                $to_id = Travelbuddytrip::find()->select(['user_id'])->where([(string)'_id' => $post_id])->asarray()->one();
                if(!empty($to_id)) {
                    $tempto_id = $to_id['user_id'];
                }

                if($tempto_id == '' || $tempto_id == $from_id) {
                    $to_id = Travelbuddy::find()->select(['user_id'])->where([(string)'_id' => $post_id])->asarray()->one();
                    if(!empty($to_id)) {
                        $tempto_id = $to_id['user_id'];
                    }                    
                }

                $to_id = $tempto_id;
            }
        }


        if($from_id != $to_id) {
            $ss = SecuritySetting::find()->where(['user_id' => $to_id])->asarray()->one();
            if(!empty($ss)) {
                $isBlockedRequest = isset($ss['travelbuddy_users_request_blocked']) ? $ss['travelbuddy_users_request_blocked'] : '';
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
            $Travelbuddytripinvitemsgs = new Travelbuddytripinvitemsgs();
            $Travelbuddytripinvitemsgs->to_id = $to_id;
            $Travelbuddytripinvitemsgs->from_id = $from_id;
            $Travelbuddytripinvitemsgs->message = $message;
            $Travelbuddytripinvitemsgs->invitationId = $id;
            $Travelbuddytripinvitemsgs->created_at = $created_at;
            $Travelbuddytripinvitemsgs->save();

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
                        <div class="settings-icon custom-ul-drop">
                            <a class="dropdown-button more_btn " href="javascript:void(0)" data-activates="'.$uniqId.'">
                              <i class="zmdi zmdi-more"></i>
                            </a>
                            <ul id="'.$uniqId.'" class="dropdown-content custom_dropdown">
                                <li><a href="javascript:void(0)" onclick="deletemessage(\''.$Travelbuddytripinvitemsgs->_id.'\')">Delete this message</a></li>
                            </ul>
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

    public function getDefaulttwomsg($inviteid) 
    {
        $data =  array('status' => false);
        $msgli = ''; 
        if($inviteid != '') {

            $tripinfo = Travelbuddytripinvite::find()->select(['user_id', 'message', 'created_at'])->where([(string)'_id' => $inviteid])->asarray()->one();
            if(!empty($tripinfo)) {
                $user_id = $tripinfo['user_id'];
                $message = $tripinfo['message'];
                $created_at = $tripinfo['created_at'];
                if ($created_at != '') {
                    $created_at = date("Y/m/d H:i A", $created_at);       
                }
                
                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $user_id])->asarray()->one();
                if(!empty($userinfo)) {
                    $thumbnail = Yii::$app->GenCls->getimage($user_id, 'thumb');
                    $profilepic = $thumbnail;
                    $fullname = $userinfo['fullname'];
                    $msgli .='<li>
                        <div class="img-holder"><img src="'.$profilepic.'"/></div>
                        <div class="desc-holder">
                            <h5>'.$fullname.'</h5>                            
                            <span class="timestamp">'.$created_at.'</span>
                            <div class="offer-msg">
                                <p>'.$message.'</p>
                            </div>
                        </div>
                    </li>';
                }
            }


            $tripinfo = Travelbuddytripinvite::find()->select(['user_id', 'trip_id'])->where([(string)'_id' => $inviteid])->asarray()->one();
            if(!empty($tripinfo)) {
                $tripid = $tripinfo['trip_id'];
                     // check user hst..
                $tripinfo = Travelbuddytrip::find()->where([(string)'_id' => $tripid])->asarray()->one();
                if(!empty($tripinfo)) {
                    $user_id = $tripinfo['user_id'];
                    $location = $tripinfo['address'];
                    $message = $tripinfo['abouttrip'];
                    $arrival = $tripinfo['arriving'];
                    if($arrival != '') {
                            $arrival = date("d M Y", strtotime($arrival));
                        }
                    $departure = $tripinfo['leaving'];
                    if($departure != '') {
                        $departure = date("d M Y", strtotime($departure));
                    }

                    $userinfo = UserForm::find()->select(['photo', 'fullname'])->where([(string)'_id' => $user_id])->asarray()->one();
                    if(!empty($userinfo)) {
                        $profilepic = isset($userinfo['photo']) ? $userinfo['photo'] : 'travebuddy-img-2.jpg';
                        $fullname = $userinfo['fullname'];
                        $msgli .='<li>
                            <div class="img-holder"><img src="profile/'.$profilepic.'"/></div>
                            <div class="desc-holder">
                                <h5>'.$fullname.'</h5>                            
                                <span class="timestamp">5 hrs ago</span>
                                <div class="offer-msg">
                                    <h5>Travel Buddy <span>to</span> '.$location.' <span>for</span></h5>
                                    <h6>'.$arrival.' - '.$departure.'</h6>
                                    <p>'.$message.'</p>
                                </div>
                            </div>
                        </li>';
                    }
                } 
            }
            
            return $msgli;
            exit;
        } 
        return $msgli;
        exit;
    }

    public function fetchmsghistory($invitationId, $user_id) {
        $msgs = array();
        if($invitationId != '') {
            $inviteinfo = Travelbuddytripinvite::find()->where([(string)'_id' => $invitationId])->asarray()->one();
            if(!empty($inviteinfo)) {
                $from_id = $inviteinfo['user_id'];
                $trip_id = $inviteinfo['trip_id'];
                $inviteinfo['is_invitation_message'] = true;
                if($from_id == $user_id) {
                    $tripinfo = Travelbuddytrip::find()->select(['user_id'])->where([(string)'_id' => $trip_id])->asarray()->one();
                    if(!empty($tripinfo)) {
                        $to_id = $tripinfo['user_id'];
                    } else {
                        $tripinfo = Travelbuddy::find()->select(['user_id'])->where([(string)'_id' => $trip_id])->asarray()->one();
                        if(!empty($tripinfo)) {
                            $to_id = $tripinfo['user_id'];
                        }
                    } 
                } else {
                    $to_id = $user_id;
                }

                $inviteinfo['from_id'] = $from_id;
                $msgs = Travelbuddytripinvitemsgs::find()->where(['invitationId' => $invitationId, 'from_id' => $from_id, 'to_id' => $to_id])->orwhere(['invitationId' => $invitationId, 'to_id' => $from_id, 'from_id' => $to_id])->orderby('_id DESC')->asarray()->all();
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

    public function deleteoffer($id,$user_id) {
        if($id != '') {
            $Travelbuddy = Travelbuddytripinvite::findOne($id);
            $Travelbuddy->delete();

            $Travelbuddy = Travelbuddytripinvitemsgs::deleteAll(['invitationId' => $id]);
            return true;
            exit;
        }
        
        return false;
        exit;
    }  

    public function invitationisread($id) {
        if($id) {
            $Travelbuddy = Travelbuddytripinvite::findOne($id);
            $Travelbuddy->is_read = true;
            $Travelbuddy->save();

            Travelbuddytripinvitemsgs::updateAll(['is_read' => true], ['invitationId' => $id]);
            return true;
            exit;
        }
        
        return false;
        exit;
    }  

    public function getLstMsg($id, $user_id) {
        if($id) {
            $getlastmsg = Travelbuddytripinvitemsgs::find()->select(['message', 'is_read', 'from_id'])->where(['invitationId' => $id, 'to_id' => $user_id])->orwhere(['invitationId' => $id, 'from_id' => $user_id])->orderby('_id DESC')->asarray()->one();

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
