<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\Personalinfo;
use frontend\models\SecuritySetting;
use frontend\models\HangoutEvent;
use frontend\models\HangoutEventInviteMsgs;

class HangoutEventInvite extends ActiveRecord
{
    public static function collectionName()
    {
        return 'hangout_event_invite';
    }

    public function attributes()
    {
         return ['_id', 'user_id', 'event_id', 'message', 'is_read', 'created_at'];
    }

    public function invite($id, $msg, $user_id) {
        $isExists = HangoutEventInvite::find()->where(['user_id' => $user_id, 'event_id' => $id])->count();
        if($isExists <= 0) {
            $date = time();
            $HangoutEventInvite = new HangoutEventInvite();
            $HangoutEventInvite->user_id = $user_id;
            $HangoutEventInvite->event_id = $id;
            $HangoutEventInvite->message = $msg;
            $HangoutEventInvite->created_at = time();
            if($HangoutEventInvite->save()) {
                $eventCreatedAt = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $id])->one();
                $notification = new Notification();
                $notification->hangout_id = (string)$eventCreatedAt['_id'];
                $notification->hangout_invited_to_id = $eventCreatedAt['user_id'];
                $notification->hangout_invited_from_id = $user_id;
                $notification->notification_type = 'invited_for_hangout';
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = "$date";
                $notification->updated_date = "$date";
                $notification->insert();
                $result = array('status' => true);
                return json_encode($result, true);
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
    }

    public function requestslist($user_id) {
        if($user_id) {
            $resultSet = array();
            $newIDS = array();
            $IDS = ArrayHelper::map(HangoutEvent::find()->select(['user_id'])->where(['user_id' => $user_id])->asarray()->all(), function($scope) { return (string)$scope['_id'];}, 'user_id');

            if(!empty($IDS)) {
                $IDS = array_keys($IDS);
            }

            $events = HangoutEventInvite::find()->where(['in', 'event_id', $IDS])->orwhere(['user_id' => $user_id])->orderby('_id DESC')->asarray()->all();
              
            $assetsPath = '../../vendor/bower/travel/images/';


            if(!empty($events)) {
                foreach ($events as $key => $event) {
                    $requestId = (string)$event['_id']->{'$id'};
                    $uid = $event['user_id'];
                    $eventId = $event['event_id'];

                    if($uid == $user_id) {
                        $otheId = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $eventId])->asarray()->one();
                        if(!empty($otheId)) {
                            $uid = $otheId['user_id'];
                        }
                    }

                    $requestblockedlist = SecuritySetting::find()->where(['user_id' => $user_id])->asArray()->one();
                    if(!empty($requestblockedlist)) {
                        $isBlockedRequest = isset($requestblockedlist['request_filter']) ? $requestblockedlist['request_filter'] : '';
                        if($isBlockedRequest) {
                            $isBlockedRequest = explode(",", $isBlockedRequest);
                            if(!empty($isBlockedRequest)) {
                                if(in_array($requestId, $isBlockedRequest)) {
                                    continue;
                                }
                            }
                        }
    
                        $isBlockedUsers = isset($requestblockedlist['hangout_users_blocked']) ? $requestblockedlist['hangout_users_blocked'] : '';
                        if($isBlockedUsers) {
                            $isBlockedUsers = explode(",", $isBlockedUsers);
                            if(!empty($isBlockedUsers)) {
                                if(in_array($uid, $isBlockedUsers)) {
                                    continue;
                                }
                            }
                        }
                    }

                    $event_info = HangoutEvent::find()->where([(string)'_id' => $eventId])->asarray()->one();
                    if(!empty($event_info)) {
                        $event['event_info'] = $event_info;
                    }

                    $eventinfolastmsg = HangoutEventInviteMsgs::getLastMessage($requestId, $user_id);
                    if(!empty($eventinfolastmsg)) {
                        $event['is_last'] = $eventinfolastmsg;
                    }

                    if($uid != '') {
                        $userinfo = UserForm::find()->select(['thumbnail', 'photo', 'fullname', 'country', 'gender'])->where([(string)'_id' => $uid])->asarray()->one();
                        if(!empty($userinfo)) {
                            $thumbnail = '';    
                            if(isset($userinfo['photo']) && substr($userinfo['photo'],0,4) == 'http') {
                                    $thumbnail = $userinfo['photo'];
                            } else if(isset($userinfo['thumbnail']) && $userinfo['thumbnail'] != '' && file_exists('profile/'.$userinfo['thumbnail'])) {
                                    $thumbnail = "profile/".$userinfo['thumbnail'];
                            } else if(isset($userinfo['gender'])) {
                                     $thumbnail = $assetsPath.$userinfo['gender'].".jpg";
                            } else {
                                    $thumbnail = $assetsPath."Male.jpg";
                            }

                            $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                            $userinfo['country'] = $country;
                            $userinfo['profile'] = $thumbnail;
                            $event['userinfo'] = $userinfo;
                            $resultSet[] = $event;                            
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
            
            $inviteEvent = HangoutEventInvite::find()->where([(string)'_id' => $invitationid])->asarray()->one();
            if(!empty($inviteEvent)) {
                $uid = $inviteEvent['user_id'];
                $event_id = $inviteEvent['event_id'];
                if($uid != '') {
                    if($uid == $user_id) {
                        $otheId = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $event_id])->asarray()->one();
                        if(!empty($otheId)) {
                            $uid = $otheId['user_id'];
                        }
                    }

                    $userinfo = UserForm::find()->where([(string)'_id' => $uid])->asarray()->one();
                    if(!empty($userinfo)) { 
                        $thumbnail = '';    
                          
                        $assetsPath = '../../vendor/bower/travel/images/';

                        if(isset($userinfo['photo']) && substr($userinfo['photo'],0,4) == 'http') {
                                $thumbnail = $userinfo['photo'];
                        } else if(isset($userinfo['thumbnail']) && $userinfo['thumbnail'] != '' && file_exists('profile/'.$userinfo['thumbnail'])) {
                                $thumbnail = "profile/".$userinfo['thumbnail'];
                        } else if(isset($userinfo['gender'])) {
                                 $thumbnail = $assetsPath.$userinfo['gender'].".jpg";
                        } else {
                                $thumbnail = $assetsPath."Male.jpg";
                        }
                        //$thumbnail = Yii::$app->GenCls->getimage($uid, 'thumb');
                        $userinfo['photo'] = $thumbnail;
                        $totalconnections = Connect::find()->where(['to_id' => (string)$uid, 'status' => '1'])->count();
                        if($totalconnections<=0) {
                            $totalconnections = 0;
                        } 
                        $userinfo['totalconnections'] = $totalconnections;

                        $pinfo = Personalinfo::find()->where(['user_id' => $uid])->asarray()->one();
                        if($pinfo) {
                            $userinfo = array_merge($userinfo, $pinfo);
                        }
                        $inviteEvent['userinfo'] = $userinfo;

                        $event_info = HangoutEvent::find()->where([(string)'_id' => $event_id])->asarray()->one();
                        if(!empty($event_info)) {
                            $event_info['message'] = $inviteEvent['message'];
                            $inviteEvent['event_info'] = $event_info;
                        }
                        $resultSet = $inviteEvent;
                    }
                }
            }

            return json_encode($resultSet);
            exit;
        }
    }
}