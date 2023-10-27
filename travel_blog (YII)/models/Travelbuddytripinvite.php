<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Personalinfo; 
use frontend\models\Travelbuddy;
use frontend\models\Travelbuddytrip;
use frontend\models\Travelbuddytripinvitemsgs;
use frontend\models\SecuritySetting;
use frontend\models\Connect;
use frontend\models\Notification;

class Travelbuddytripinvite extends ActiveRecord
{
    public static function collectionName()
    {
        return 'travelbuddy_trip_invite';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'trip_id', 'type', 'arrival_date', 'departure_date', 'total_travellers', 'message', 'created_at', 'updated_at', 'is_read'];
    }

    public function checkInviteType($tripid, $user_id) {
        $result = array();
        if($tripid) {
            $triptype = Travelbuddytrip::find()->select(['lookingfor', 'user_id'])->where([(string)'_id' => $tripid])->asarray()->one();
            if(!empty($triptype)) {
                $uid = $triptype['user_id'];
                if($uid == $user_id) {
                    $result  = array('status' => false, 'is_self' => true);
                    return json_encode($result);
                    exit;
                }

                $triptype = isset($triptype['lookingfor']) ? $triptype['lookingfor'] : '';
                if($triptype == 'buddy') {
                    $type = 'BB';
                } else if($triptype == 'host') {
                    $type = 'HB';
                }
            } else {
                $triptype = Travelbuddy::find()->where([(string)'_id' => $tripid])->one();
                if(!empty($triptype)) {
                    $uid = $triptype['user_id'];
                    if($uid == $user_id) {
                        $result  = array('status' => false, 'is_self' => true);
                        return json_encode($result);
                        exit;
                    }

                    $type = 'BH';
                }
            }

            $result  = array('status' => true, 'type' => $type);
            return json_encode($result);
            exit;
        }

        $result  = array('status' => false);
        return json_encode($result);
        exit;
    }

    public function saveinvitetrip($post, $user_id) {
        $result = array();
        if(isset($post) &&!empty($post)) {

            $id = $post['id'];
             $date = time();
            $arriving = isset($post['arriving']) ? $post['arriving'] : '';
            $departure = isset($post['departure']) ? $post['departure'] : '';;
            $totaltraveller = isset($post['totaltraveller']) ? $post['totaltraveller'] : '';
            $type = isset($post['type']) ? $post['type'] : '';
            $message = $post['message'];

            if($type) {
                $is_exist = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id'=> $id])->count();
                if( $is_exist >0 ){
                    $Travelbuddy = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id'=> $id])->one();
                    $Travelbuddy->type = $type;
                    $Travelbuddy->arrival_date = $arriving;
                    $Travelbuddy->departure_date = $departure;
                    $Travelbuddy->total_travellers = $totaltraveller;
                    $Travelbuddy->message = $message;
                    $Travelbuddy->updated_at = strtotime("now");
                    $Travelbuddy->update(); 
                } else {
                    $Travelbuddy = new Travelbuddytripinvite();
                    $Travelbuddy->user_id = $user_id;
                    $Travelbuddy->trip_id = $id;
                    $Travelbuddy->type = $type;
                    $Travelbuddy->arrival_date = $arriving;
                    $Travelbuddy->departure_date = $departure;
                    $Travelbuddy->total_travellers = $totaltraveller;
                    $Travelbuddy->message = $message;
                    $Travelbuddy->created_at = strtotime("now");
                    if($Travelbuddy->save()) {
                        $eventCreatedAt = Travelbuddytrip::find()->select(['user_id'])->where([(string)'_id' => $id])->one();
                        $notification = new Notification();
                        $notification->travelbuddy_id = "$id";
                        $notification->travelbuddy_invited_id = $eventCreatedAt['user_id'];
                        $notification->user_id = "$user_id";
                        $notification->notification_type = 'invitetravelbuddy';
                        $notification->is_deleted = '0';
                        $notification->status = '1';
                        $notification->created_date = "$date";
                        $notification->updated_date = "$date";
                        $notification->insert();
                    }
                }

                $count = Travelbuddytripinvite::find()->where(['trip_id' => $id])->count();
                if(empty($count)) {
                    $count = 0;
                }
                
                $result = array('status' => true, 'count' => $count);
                return json_encode($result);
                exit;
            }
        }

        $result = array('status' => false);
        return json_encode($result);
        exit;
    }


    public function saveinvitetripfindbyid($tripid, $user_id) {
        $information = array();                                                                          
        if($tripid) {
            $info = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id' => $tripid])->asarray()->one();
            if(!empty($info)) {
                $information = $info;
                $count = Travelbuddytripinvite::find()->where(['trip_id' => $tripid])->count();
                $information['totalinvited'] = $count;
            }
        }
        return $information;
        exit;
    }


    public function offerlist($user_id) {
        if($user_id) {
            $resultSet = array();
            $idsBox = array();

            $tripsids = Travelbuddytrip::find()->select([(string)'_id'])->where(['user_id' => $user_id])->asarray()->all();
            foreach ($tripsids as $key => $value) {
                $id = (string)$value['_id'];
                if($id) {
                    $idsBox[] = $id;
                }
            }

            $tripsids = Travelbuddy::find()->select([(string)'_id'])->where(['user_id' => $user_id])->asarray()->all();
            foreach ($tripsids as $key => $value) {
                $id = (string)$value['_id'];
                if($id) {
                    $idsBox[] = $id;
                }
            }
            
            $inviteTrips = Travelbuddytripinvite::find()->where(['in', 'trip_id', $idsBox])->orwhere(['user_id' => $user_id])->asarray()->all();
            if(!empty($inviteTrips)) {
                foreach ($inviteTrips as $key => $inviteTrip) {
                    $invitationId = (string)$inviteTrip['_id']->{'$id'};
                    $uid = $inviteTrip['user_id'];

                    // check this user is blocked or not for requests...
                    $inviteblockedusers = SecuritySetting::find()->select(['travelbuddy_users_request_blocked'])->where(['user_id' => $user_id])->asArray()->one();
                    if(!empty($inviteblockedusers)) {
                        $isblocked = isset($inviteblockedusers['travelbuddy_users_request_blocked']) ? $inviteblockedusers['travelbuddy_users_request_blocked'] : '';
                        if($isblocked) {
                            $isblocked = explode(",", $isblocked);
                            if(!empty($isblocked)) {
                                if(in_array($uid, $isblocked)) {
                                    continue;
                                }
                            }
                        }
                    }

                    $type = $inviteTrip['type'];
                    $tripid = (string)$inviteTrip['trip_id'];
                     
                    if($uid == $user_id) {
                        $temptripinfo = Travelbuddytrip::find()->select(['user_id'])->where([(string)'_id' => $tripid])->asarray()->one();
                        if(!empty($temptripinfo)) {
                            if(!empty($temptripinfo)) {
                                $uid = (string)$temptripinfo['user_id'];
                            }   
                        } else {
                            $temptripinfo = Travelbuddy::find()->select(['user_id'])->where([(string)'_id' => $tripid])->asarray()->one();
                            if(!empty($temptripinfo)) {
                                $uid = (string)$temptripinfo['user_id'];
                            }   
                        }
                    }

                    if($uid != '') { 
                        $userinfo = UserForm::find()->select(['fullname', 'thumbnail', 'gender', 'city', 'country', 'photo'])->where([(string)'_id' => $uid])->asarray()->one();
                        $thumbnail = Travelbuddytripinvite::getimage($uid, 'thumb');
                        $userinfo['photo'] = $thumbnail;
                        if(!empty($userinfo)) {
                            $inviteTrip['userinfo'] = $userinfo;
                            //get trip or host info...............
                            if($type == 'BH') {
                               $tripinfo = Travelbuddy::find([(string)'_id' => $tripid])->asArray()->one();
                               if(!empty($tripinfo)) {
                                    $teuid = $tripinfo['user_id'];
                                    //check last message is availalbe in list..
                                    $is_last = Travelbuddytripinvitemsgs::getlastmsg($invitationId, $user_id);
                                    $is_last = json_decode($is_last, true);
                                    if(!empty($is_last)) {
                                        $teuid = $is_last['from_id'];
                                        $message = $is_last['message'];
                                        $created_at = $is_last['created_at'];
                                        $is_read = isset($is_last['is_read']) ? $is_last['is_read'] : false;
                                        $inviteTrip['is_read'] = $is_read;
                                        $inviteTrip['user_id'] = $teuid;
                                        $inviteTrip['message'] = $message;
                                        $inviteTrip['created_at'] = $created_at;
                                    }
                                    $inviteTrip['tripinfo'] = $tripinfo;
                                    $resultSet[] = $inviteTrip;
                               }
                            } else {
                                $tripinfo = Travelbuddytrip::find([(string)'_id' => $tripid])->asArray()->one();
                                if(!empty($tripinfo)) {
                                    $teuid = $tripinfo['user_id'];
                                    //check last message is availalbe in list..
                                    $is_last = Travelbuddytripinvitemsgs::getlastmsg($invitationId, $user_id);
                                    $is_last = json_decode($is_last, true);
                                    if(!empty($is_last)) {
                                        $teuid = $is_last['from_id'];
                                        $message = $is_last['message'];
                                        $created_at = $is_last['created_at'];
                                        $is_read = isset($is_last['is_read']) ? $is_last['is_read'] : false;
                                        $inviteTrip['is_read'] = $is_read;
                                        $inviteTrip['user_id'] = $teuid;
                                        $inviteTrip['message'] = $message;
                                        $inviteTrip['created_at'] = $created_at;
                                    }
                                    $inviteTrip['tripinfo'] = $tripinfo;
                                    $resultSet[] = $inviteTrip;
                                }
                            }
                        }
                    }
                }
            }
            return json_encode($resultSet);
            exit;
        }
    } 

    public function invitationdetail($invitationid, $user_id) {
        if($invitationid) {
            $resultSet = array();
            $inviteTrip = Travelbuddytripinvite::find()->where([(string)'_id' => $invitationid])->asarray()->one();
            if(!empty($inviteTrip)) {
                $uid = $inviteTrip['user_id'];
                if($uid != '') {
                    $type = $inviteTrip['type'];
                    $tripid = $inviteTrip['trip_id'];
                    $userinfo = UserForm::find()->where([(string)'_id' => $uid])->asarray()->one();
                    $thumbnail = Travelbuddytripinvite::getimage($uid, 'thumb');
                    $userinfo['photo'] = $thumbnail;
                    if(!empty($userinfo)) {
                        $totalconnections = Connect::find()->where(['to_id' => (string)$uid, 'status' => '1'])->count();
                        if($totalconnections<=0) {
                            $totalconnections = 0;
                        } 
                        $userinfo['totalconnections'] = $totalconnections;

                        //personal info

                        $pinfo = Personalinfo::find()->where(['user_id' => $uid])->asarray()->one();
                        if($pinfo) {
                            $userinfo = array_merge($userinfo, $pinfo);
                        }
                        $inviteTrip['userinfo'] = $userinfo;

                        //get trip or host info...............
                        if($type == 'BH') {
                           $tripinfo = Travelbuddy::find([(string)'_id' => $tripid])->asArray()->one();
                           if(!empty($tripinfo)) {
                                $inviteTrip['tripinfo'] = $tripinfo;
                                $resultSet = $inviteTrip;
                           }
                        } else {
                            $tripinfo = Travelbuddytrip::find([(string)'_id' => $tripid])->asArray()->one();
                            if(!empty($tripinfo)) {
                                $inviteTrip['tripinfo'] = $tripinfo;    
                                $resultSet = $inviteTrip;
                            }
                        }
                    }
                }
            }

            return json_encode($resultSet);
            exit;
        }
    }
    
    public function getimage($userid,$type)
    {
        $resultimg = LoginForm::find()->where(['_id' => $userid])->one();
        
        if(substr($resultimg['photo'],0,4) == 'http')
        {
            if($type == 'photo')
            {
                $dp = $resultimg['photo'];
            }
            else
            {
                $dp = $resultimg['thumbnail'];
            }
        }
        else
        {
              
            $assetsPath = '../../vendor/bower/travel/images/';

            if(isset($resultimg['thumbnail']) && !empty($resultimg['thumbnail']) && file_exists('profile/'.$resultimg['thumbnail']))
            {
                $dp = "profile/".$resultimg['thumbnail'];
            }
            else if(isset($resultimg['gender']) && !empty($resultimg['gender']) && file_exists($assetsPath.$resultimg['gender'].'.jpg'))
            {
                $dp = $assetsPath.$resultimg['gender'].'.jpg';
            }
            else
            {
                $dp = $assetsPath."DefaultGender.jpg";
            }
        }
        return $dp;
    }
}