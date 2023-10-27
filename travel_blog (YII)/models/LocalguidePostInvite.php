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
use frontend\models\LocalguidePost;
use frontend\models\Travelbuddytripinvite;

class LocalguidePostInvite extends ActiveRecord
{
    public static function collectionName()
    {
        return 'localguide_post_invite';
    }

    public function attributes()
    {
         return ['_id', 'user_id', 'post_id', 'arrival_date', 'departure_date', 'message', 'created_at'];
    }

    public function sendinvitepost($post, $user_id) {
        if(!empty($post)) {
            $postId = isset($post['id']) ? $post['id'] : '';
            $arrival = isset($post['arrival']) ? $post['arrival'] : '';
            $departure = isset($post['departure']) ? $post['departure'] : '';
            $message = isset($post['message']) ? $post['message'] : '';
            $date = time();
            if($postId != '' || $arrival != '' || $departure != '' || $message) {
                $isExists = LocalguidePostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->count();
                if($isExists <= 0) {
                    $LocalguidePostInvite = new LocalguidePostInvite();
                    $LocalguidePostInvite->user_id = $user_id;
                    $LocalguidePostInvite->post_id = $postId;
                    $LocalguidePostInvite->arrival_date = $arrival;
                    $LocalguidePostInvite->departure_date = $departure;
                    $LocalguidePostInvite->message = $message;
                    $LocalguidePostInvite->created_at = time();
                    if($LocalguidePostInvite->save()) {
                        $eventCreatedAt = LocalguidePost::find()->select(['user_id'])->where([(string)'_id' => $postId])->andWhere(['not','flagger', "yes"])->one();
                        $notification = new Notification();
                        $notification->localguide_id = "$postId";
                        $notification->localguide_invited_to_id = $eventCreatedAt['user_id'];
                        $notification->localguide_invited_from_id = $user_id;
                        $notification->notification_type = 'invited_for_localguide';
                        $notification->is_deleted = '0';
                        $notification->status = '1';
                        $notification->created_date = "$date";
                        $notification->updated_date = "$date";
                        $notification->insert();
                    }
                    $result = array('status' => true);
                    return json_encode($result, true);
                    exit;
                } else {
                    $LocalguidePostInvite = LocalguidePostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->one();
                    $LocalguidePostInvite->arrival_date = $arrival;
                    $LocalguidePostInvite->departure_date = $departure;
                    $LocalguidePostInvite->message = $message;
                    $LocalguidePostInvite->created_at = time();
                    if($LocalguidePostInvite->save()) {
                        $eventCreatedAt = LocalguidePost::find()->select(['user_id'])->where([(string)'_id' => $postId])->andWhere(['not','flagger', "yes"])->one();
                        $notification = new Notification();
                        $notification->localguide_id = "$postId";
                        $notification->localguide_invited_to_id = $eventCreatedAt['user_id'];
                        $notification->localguide_invited_from_id = $user_id;
                        $notification->notification_type = 'invited_for_localguide';
                        $notification->is_deleted = '0';
                        $notification->status = '1';
                        $notification->created_date = "$date";
                        $notification->updated_date = "$date";
                        $notification->insert();
                    }
                    $result = array('status' => true);
                    return json_encode($result, true);
                    exit;
                }
                
            }
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function requestslist($user_id) {
        if($user_id) {
            $resultSet = array();
            $newIDS = array();

            // get ids..
            $IDS = ArrayHelper::map(LocalguidePost::find()->select(['user_id'])->where(['user_id' => $user_id])->andWhere(['not','flagger', "yes"])->asarray()->all(), function($scope) { return (string)$scope['_id'];}, 'user_id');

            if(!empty($IDS)) {
                $IDS = array_keys($IDS);
            }

            $posts = LocalguidePostInvite::find()->where(['in', 'post_id', $IDS])->orwhere(['user_id' => $user_id])->orderby('_id DESC')->asarray()->all();

            if(!empty($posts)) {
                foreach ($posts as $key => $post) {
                    $requestId = (string)$post['_id']->{'$id'};
                    $postUId = $post['user_id'];
                    $postId = $post['post_id'];

                    if($postUId == $user_id) {
                        // Invitation Info...
                        $otheId = LocalguidePost::find()->select(['user_id'])->where([(string)'_id' => $postId])->andWhere(['not','flagger', "yes"])->asarray()->one();
                        if(!empty($otheId)) {
                            $postUId = $otheId['user_id'];
                        }
                    }

                    // check this request is Deleted or not..
                    $requestblockedlist = SecuritySetting::find()->where(['user_id' => $user_id])->asArray()->one();
                    if(!empty($requestblockedlist)) {
                        $isBlockedRequest = isset($requestblockedlist['localguide_users_request_blocked']) ? $requestblockedlist['localguide_users_request_blocked'] : '';
                        if($isBlockedRequest) {
                            $isBlockedRequest = explode(",", $isBlockedRequest);
                            if(!empty($isBlockedRequest)) {
                                if(in_array($postUId, $isBlockedRequest)) {
                                    continue;
                                }
                            }
                        }
    
                        $isBlockedUsers = isset($requestblockedlist['localguide_users_blocked']) ? $requestblockedlist['localguide_users_blocked'] : '';
                        if($isBlockedUsers) {
                            $isBlockedUsers = explode(",", $isBlockedUsers);
                            if(!empty($isBlockedUsers)) {
                                if(in_array($postUId, $isBlockedUsers)) {
                                    continue;
                                }
                            }
                        }
                    }

                    // Invitation Info...
                    $post_info = LocalguidePostInvite::find()->where([(string)'post_id' => $postId])->asarray()->one();
                    if(!empty($post_info)) {
                        $post['post_info'] = $post_info;
                    }

                    // get last messages...
                    $postinfolastmsg = LocalguidePostInviteMsgs::getlastmsg($requestId, $user_id);
                    if(!empty($postinfolastmsg)) {
                        $post['is_last'] = $postinfolastmsg;
                    }

                    if($postUId != '') {
                        $userinfo = UserForm::find()->select(['thumbnail', 'fullname', 'country', 'gender'])->where([(string)'_id' => $postUId])->asarray()->one();
                        if(!empty($userinfo)) {
                            $thumbnail = Travelbuddytripinvite::getimage($postUId, 'thumb');
                            $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                            $userinfo['country'] = $country;
                            $userinfo['profile'] = $thumbnail;
                            $post['userinfo'] = $userinfo;
                            $resultSet[] = $post;                            
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
            $inviteEvent = LocalguidePostInvite::find()->where([(string)'_id' => $invitationid])->asarray()->one();
            if(!empty($inviteEvent)) {
                $postUId = $inviteEvent['user_id'];
                $postId = $inviteEvent['post_id'];
                if($postUId != '') {
                    if($postUId == $user_id) {
                        // Invitation Info...
                        $otheId = LocalguidePost::find()->select(['user_id'])->where([(string)'_id' => $postId])->andWhere(['not','flagger', "yes"])->asarray()->one();
                        if(!empty($otheId)) {
                            $postUId = $otheId['user_id'];
                        }
                    }

                    $userinfo = UserForm::find()->where([(string)'_id' => $postUId])->asarray()->one();
                    if(!empty($userinfo)) {
                        $thumbnail = Travelbuddytripinvite::getimage($postUId, 'thumb');
                        $userinfo['photo'] = $thumbnail;
                        $totalconnections = Connect::find()->where(['to_id' => (string)$postUId, 'status' => '1'])->count();
                        if($totalconnections<=0) {
                            $totalconnections = 0;
                        } 
                        $userinfo['totalconnections'] = $totalconnections;

                        //personal info

                        $pinfo = Personalinfo::find()->where(['user_id' => $postUId])->asarray()->one();
                        if($pinfo) {
                            $userinfo = array_merge($userinfo, $pinfo);
                        }
                        $inviteEvent['userinfo'] = $userinfo;

                        // Invitation Info...
                        $post_info = LocalguidePostInvite::find()->where(['post_id' => $postId])->asarray()->one();
                        if(!empty($post_info)) {
                            $inviteEvent['post_info'] = $post_info;
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