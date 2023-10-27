<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\UserForm; 
use frontend\models\Personalinfo; 
use frontend\models\Travelbuddy;
use frontend\models\Travelbuddytripinvite;
use frontend\models\Travelbuddytripinvitemsgs;
use frontend\models\SecuritySetting;
use frontend\models\Connect;
use frontend\models\Verify;
use frontend\models\TravelSavePost;

class Travelbuddytrip extends ActiveRecord
{
    public static function collectionName()
    {
        return 'travelbuddy_trip';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'address', 'arriving', 'leaving', 'lookingfor', 'from_destination', 'abouttrip', 'countperson', 'created_at', 'updated_at'];
    }

    public function getdefaultinfo($user_id)
    {
        $userdata = UserForm::find()->select(['fullname', 'country', 'gender', 'birth_date'])->where(['_id' => $user_id])->asarray()->one();
        if(!empty($userdata)) {
            $Personalinfo = Personalinfo::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            $birth_date = isset($userdata['birth_date']) ? $userdata['birth_date'] : '';
            $Personalinfo['age'] = '';
            if($birth_date != '') {
                $year = date("Y", strtotime($birth_date));
                $age = date("Y") - $year;
                if($age >0) {
                    $Personalinfo['age'] = $age;
                }
            }
            
            $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
            if(!empty($Personalinfo)) {


                $about = isset($Personalinfo['about']) ? trim($Personalinfo['about']) : '';
                $Personalinfo['about'] = $about;
                $Personalinfo['count'] = $totalconnections;

                $userdata = array_merge($userdata, $Personalinfo);
            } else {
                $temparray = array(
                    "about" => "",
                    "education" => "",
                    "occupation" => "",
                    "language" => "",
                    "gender" => "",
                    "count" => $totalconnections
                );
                $userdata = array_merge($userdata, $temparray);
            }
        }
        return json_encode($userdata, true);
        exit;
    }

    public function createtrip($post, $user_id)
    {   
        if(!empty($post)) {
            $date = time();
            $address = (isset($post['address']) && $post['address']) ? $post['address'] : '';
            $arriving = (isset($post['arriving']) && $post['arriving']) ? $post['arriving'] : '';
            $leaving = (isset($post['leaving']) && $post['leaving']) ? $post['leaving'] : '';
            $lookingfor = (isset($post['lookingfor']) && $post['lookingfor']) ? $post['lookingfor'] : '';
            $abouttrip = (isset($post['abouttrip']) && $post['abouttrip']) ? $post['abouttrip'] : '';
            $countperson = (isset($post['countperson']) && $post['countperson']) ? $post['countperson'] : '';

            if($address != '' || $arriving != '' || $leaving != '' || $lookingfor != '' || $countperson != '') {

                $isCity = UserForm::find()->select(['city', 'country'])->where([(string)'_id' => $user_id])->asarray()->one();

                $from_destination = '';
                if(!empty($isCity)) {
                    $from_destination = isset($isCity['city']) ? $isCity['city'] : '';
                }

                $Travelbuddy = new Travelbuddytrip();
                $Travelbuddy->user_id = $user_id;
                $Travelbuddy->address = $address;
                $Travelbuddy->arriving = $arriving;
                $Travelbuddy->leaving = $leaving;
                $Travelbuddy->lookingfor = $lookingfor;
                $Travelbuddy->from_destination = $from_destination;
                $Travelbuddy->abouttrip = $abouttrip;
                $Travelbuddy->countperson = $countperson;
                $Travelbuddy->created_at = strtotime("now");
                if($Travelbuddy->save()) {
                    $eventCreatedAt = Travelbuddytrip::find()->select(['user_id'])->where(['user_id' => $user_id])->orderby('_id DESC')->asarray()->one();
                    if(!empty($eventCreatedAt)) {
                        $notification = new Notification();
                        $notification->travelbuddy_trip_id = (string)$eventCreatedAt['_id'];
                        $notification->user_id = "$user_id";
                        $notification->notification_type = 'addtravelbuddytrip';
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
                    $result = array('status' => false);
                    return json_encode($result, true);
                    exit;
                }
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    } 


    public function edittrip($post, $user_id)
    {   
        $fetchUpdateRecord = array();
        $address = isset($post['address']) ? $post['address'] : '';
        $arriving = isset($post['arriving']) ? $post['arriving'] : '';
        $leaving = isset($post['leaving']) ? $post['leaving'] : '';
        $lookingfor = isset($post['lookingfor']) ? $post['lookingfor'] : '';
        $abouttrip = isset($post['abouttrip']) ? $post['abouttrip'] : '';
        $countperson = isset($post['countperson']) ? $post['countperson'] : '';

        if($address != '' || $arriving != '' || $leaving != '' || $lookingfor != '' || $countperson != '') {
            $id = $post['id'];
            $Travelbuddy = Travelbuddytrip::findOne($id);
            $Travelbuddy->address = $address;
            $Travelbuddy->arriving = $arriving;
            $Travelbuddy->leaving = $leaving;
            $Travelbuddy->lookingfor = $lookingfor;
            $Travelbuddy->abouttrip = $abouttrip;
            $Travelbuddy->countperson = $countperson;
            $Travelbuddy->updated_at = strtotime("now");
            if($Travelbuddy->save()) {
                $fetchUpdateRecord = Travelbuddytrip::find()->where([(string)'_id' => (string)$id])->asarray()->one();
                return json_encode($fetchUpdateRecord, true);
                exit;
            }
        }

        return json_encode($fetchUpdateRecord, true);
        exit;
    }

    public function getmytrips($user_id)
    {
        $userdata = Travelbuddytrip::find()->where(['user_id' => (string)$user_id])->orderby('_id DESC')->asarray()->all();
        return json_encode($userdata, true);
    }

    public function gettrip($id)
    { 
        $userdata = Travelbuddytrip::find()->where([(string)'_id' => $id])->asarray()->one();
        return json_encode($userdata, true);
    }
    
    public function recentTravelbuddyPlans($user_id='',$start=0) {
		
		if($start == '') {
			$start = 0;
		} 
		
        $userdata = Travelbuddytrip::find()->orderBy('_id DESC')->limit(12)->offset($start)->asarray()->all();
		  
        $assetsPath = '../../vendor/bower/travel/images/';
        $newuserdata = [];
        if(!empty($userdata)) {
            // check this plan user is blocked or not.
            $SSINFO = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();

            // check event is save or not...
            $savedata = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();

            foreach ($userdata as $key => $value) {
                $id = $value['user_id'];
                $tripid = (string)$value['_id'];

                if(isset($user_id) && $user_id != '') {
                    if(!empty($SSINFO)) {
                        if(isset($SSINFO['blocked_list']) && $SSINFO['blocked_list'] != '') {
                            $isUserGenBlock = $SSINFO['blocked_list'];
                            $isUserGenBlock = explode(",", $isUserGenBlock);
                            if(!empty($isUserGenBlock)) {
                                if(in_array($id, $isUserGenBlock)) {
                                    continue;
                                }
                            }
                        }

                        if(isset($SSINFO['travelbuddy_users_blocked']) && $SSINFO['travelbuddy_users_blocked'] != '') {
                            $isUserBlock = $SSINFO['travelbuddy_users_blocked'];
                            $isUserBlock = explode(",", $isUserBlock);
                            if(!empty($isUserBlock)) {
                                if(in_array($id, $isUserBlock)) {
                                    continue;
                                }
                            }
                        }

                        if(isset($SSINFO['travelbuddy_posts_delete']) && $SSINFO['travelbuddy_posts_delete'] != '') {
                            $is_hide = $SSINFO['travelbuddy_posts_delete'];
                            $is_hide = explode(",", $is_hide);
                            if(!empty($is_hide)) {
                                if(in_array($tripid, $is_hide)) {
                                    continue;
                                }
                            }
                        }
                    }
                    
                    if(!empty($savedata)) {
                        $isSaved = isset($savedata['travelbuddy_save_post']) ? $savedata['travelbuddy_save_post'] : '';
                        if($isSaved) {
                            $isSaved = explode(",", $isSaved);
                            if(!empty($isSaved)) {
                                if(in_array($tripid, $isSaved)) {
                                    $value['is_saved'] = true;
                                }
                            }
                        }
                    }

                    $iInvite = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id' => $tripid])->count();
                    if($iInvite>0) {
                        $value['iInvite'] = true;
                    }
                }

                // check is_veryfied.
                $is_verified = Verify::find()->where(['user_id' => $id,'status' => '1'])->one();
                if($is_verified) {
                    $value['is_verified']  = '<span class="verified"><img src="'.$assetsPath.'verified-icon.png"></span>';
                } else {
                    $value['is_verified'] = '';
                }
                 
                // get total count invitations............
                $count = Travelbuddytripinvite::find()->where(['trip_id' => $tripid])->count();
                $value['totalinvited'] = $count;

            
                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country'])->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($userinfo)) 
                {
                    $fullname = $userinfo['fullname'];
                    $value['fullname'] = $fullname;
                    $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                    $value['country'] = $country;
                    $thumbnail = Yii::$app->GenCls->getimage($id,'thumb');
                    $value['profile'] = $thumbnail;
                   
                    $newuserdata[] = $value;             
                }  
            }
        }
        return json_encode($newuserdata, true);
        exit;
    }

    public function getUserInfo($user_id)
    {
        $userdata = UserForm::find()->select(['fullname'])->where([(string)'_id' => (string)$user_id])->asarray()->one();
        return $userdata;
    }

    public function savetrip($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                if(isset($getInfo['travelbuddy_posts_save']) && $getInfo['travelbuddy_posts_save']) {
                    $saveposts =$getInfo['travelbuddy_posts_save'];
                    $saveposts = explode(",", $saveposts);
                    if(in_array($id, $saveposts)) {
                        $key = array_search($id, $saveposts);
                        if($key >= 0) {
                            unset($saveposts[$key]);
                        }
                    }
                    $saveposts[] = $id;
                    $saveposts = implode(",", $saveposts);
                } else {
                    $saveposts = $id;
                }

                $SecuritySetting = SecuritySetting::find()->where(['user_id' => (string)$user_id])->one();
                $SecuritySetting->travelbuddy_posts_save = $saveposts;
                $SecuritySetting->update();
                return true;
                exit;
            } 
            else {
                $SecuritySetting = new SecuritySetting();
                $SecuritySetting->user_id = (string)$user_id;
                $SecuritySetting->travelbuddy_posts_save = $id;
                $SecuritySetting->save();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }

     public function hidetrip($id, $user_id) {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($getInfo)) {
                if(isset($getInfo['travelbuddy_posts_delete']) && $getInfo['travelbuddy_posts_delete'] != '') {
                    $hideposts = $getInfo['travelbuddy_posts_delete'];
                    $hideposts = explode(",", $hideposts);
                    if(in_array($id, $hideposts)) {
                        $key = array_search($id, $hideposts);
                        if($key >= 0) {
                            unset($hideposts[$key]);
                        }
                    }
                    $hideposts[] = $id;
                    $hideposts = implode(",", $hideposts);
                } else {
                    $hideposts = $id;
                }

                $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                $SecuritySetting->travelbuddy_posts_delete = $hideposts;
                $SecuritySetting->update();
                return true;
                exit;
            } 
            else {
                $SecuritySetting = new SecuritySetting();
                $SecuritySetting->user_id = $user_id;
                $SecuritySetting->travelbuddy_posts_delete = $id;
                $SecuritySetting->save();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }

    public function blockplanuser($id, $user_id) {
        if($id) {
            $travelinfo = Travelbuddytrip::find()->select(['user_id'])->where([(string)'_id' => $id])->asarray()->one();
            if(!empty($travelinfo)) {
                $newid = $travelinfo['user_id'];
            } else {
                $travelinfo = Travelbuddy::find()->select(['user_id'])->where([(string)'_id' => $id])->asarray()->one();
                $newid = $travelinfo['user_id'];
            }

            if(isset($newid) && $newid != '') {
                $getInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($getInfo)) {
                    $blocked_list_travelplan = isset($getInfo['travelbuddy_users_blocked']) ? $getInfo['travelbuddy_users_blocked'] : '';
                    if(!empty($blocked_list_travelplan)) {

                        $blocked_list_travelplan = explode(",", $blocked_list_travelplan);
                        if(!in_array($newid, $blocked_list_travelplan)) {
                            $blocked_list_travelplan[] = $newid;
                            $blocked_list_travelplan = implode(",", $blocked_list_travelplan);                            

                            $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                            $SecuritySetting->travelbuddy_users_blocked = $blocked_list_travelplan;
                            $SecuritySetting->update();
                            return true;
                            exit;
                        }
                    } else {
                        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                        $SecuritySetting->travelbuddy_users_blocked = $newid;
                        $SecuritySetting->update();
                        return true;
                        exit;
                    }

                } 
                else {
                    $SecuritySetting = new SecuritySetting();
                    $SecuritySetting->user_id = $user_id;
                    $SecuritySetting->travelbuddy_users_blocked = $newid;
                    $SecuritySetting->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }

    public function blockthisuser($newid, $user_id) { 
        $result = false;
        if(isset($newid) && $newid != '') {
            $getInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $blocked_list_travelplan = isset($getInfo['blocked_list']) ? $getInfo['blocked_list'] : '';
                if(!empty($blocked_list_travelplan)) {

                    $blocked_list_travelplan = explode(",", $blocked_list_travelplan);
                    if(!in_array($newid, $blocked_list_travelplan)) {
                        $blocked_list_travelplan[] = $newid;
                        $blocked_list_travelplan = implode(",", $blocked_list_travelplan);                            

                        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                        $SecuritySetting->blocked_list = $blocked_list_travelplan;
                        $SecuritySetting->update();
                        $result = true;
                    }
                } else {
                    $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $SecuritySetting->blocked_list = $newid;
                    $SecuritySetting->update();
                    $result = true;
                }
            } 
            else {
                $SecuritySetting = new SecuritySetting();
                $SecuritySetting->user_id = $user_id;
                $SecuritySetting->blocked_list = $newid;
                $SecuritySetting->save();
                $result = true;
            }

            $getanotherInfo = SecuritySetting::find()->where(['user_id' => $newid])->asarray()->one();
            if(!empty($getanotherInfo)) {
                $blocked_list_travelplan = isset($getanotherInfo['blocked_list']) ? $getanotherInfo['blocked_list'] : '';
                if(!empty($blocked_list_travelplan)) {

                    $blocked_list_travelplan = explode(",", $blocked_list_travelplan);
                    if(!in_array($user_id, $blocked_list_travelplan)) {
                        $blocked_list_travelplan[] = $user_id;
                        $blocked_list_travelplan = implode(",", $blocked_list_travelplan);                            

                        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $newid])->one();
                        $SecuritySetting->blocked_list = $blocked_list_travelplan;
                        $SecuritySetting->update();
                        $result = true;
                    }
                } else {
                    $SecuritySetting = SecuritySetting::find()->where(['user_id' => $newid])->one();
                    $SecuritySetting->blocked_list = $user_id;
                    $SecuritySetting->update();
                    $result = true;
                }
            } 
            else {
                $SecuritySetting = new SecuritySetting();
                $SecuritySetting->user_id = $newid;
                $SecuritySetting->blocked_list = $user_id;
                $SecuritySetting->save();
                $result = true;
            }
        }
        
        return $result;
        exit;
    }

    public function blockuserrequests($id, $user_id) {
        if($id) {
            $getUid = Travelbuddytripinvitemsgs::find()->select(['from_id', 'to_id'])->where(['invitationId' => $id, 'from_id' => $user_id])->orWhere(['invitationId' => $id, 'to_id' => $user_id])->asarray()->one();

            $fetchID = '';
            if(!empty($getUid)) {
                $fetchID = $getUid['from_id'];
                if($fetchID == $user_id) {
                    $fetchID = $getUid['to_id']; 
                }
            } else {
                $getUid = Travelbuddytripinvite::find()->select(['user_id','trip_id'])->where([(string)'_id' => $id])->asarray()->one();
                $id = isset($getUid['trip_id']) ? $getUid['trip_id'] : '';
                if(!empty($getUid)) {
                    $fetchID = $getUid['user_id'];
                }

                $tempfetchID = '';
                if($fetchID == '' || $fetchID == $user_id) {
                    $getUid = Travelbuddytrip::find()->select(['user_id'])->where([(string)'_id' => $id])->asarray()->one();
                    if(!empty($getUid)) {
                        $tempfetchID = $getUid['user_id'];
                    }
                }

                if($tempfetchID == ''  || $tempfetchID == $user_id) {
                    $getUid = Travelbuddy::find()->select(['user_id'])->where([(string)'_id' => $id])->asarray()->one();
                    if(!empty($getUid)) {
                        $tempfetchID = $getUid['user_id'];
                    }   
                }

                $fetchID = $tempfetchID;
            }


            if(isset($fetchID) && $fetchID != '') {
                $getInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($getInfo)) {
                    $travelbuddy_users_request_blocked = isset($getInfo['travelbuddy_users_request_blocked']) ? $getInfo['travelbuddy_users_request_blocked'] : '';
                    if(!empty($travelbuddy_users_request_blocked)) {
                        $travelbuddy_users_request_blocked = explode(",", $travelbuddy_users_request_blocked);
                        if(!in_array($fetchID, $travelbuddy_users_request_blocked)) {
                            $travelbuddy_users_request_blocked[] = $fetchID;
                            $travelbuddy_users_request_blocked = implode(",", $travelbuddy_users_request_blocked);                            

                            $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                            $SecuritySetting->travelbuddy_users_request_blocked = $travelbuddy_users_request_blocked;
                            $SecuritySetting->update();
                            return true;
                            exit;
                        }
                    } else {
                        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                        $SecuritySetting->travelbuddy_users_request_blocked = $fetchID;
                        $SecuritySetting->update();
                        return true;
                        exit;
                    }

                } 
                else {
                    $SecuritySetting = new SecuritySetting();
                    $SecuritySetting->user_id = $user_id;
                    $SecuritySetting->travelbuddy_users_request_blocked = $fetchID;
                    $SecuritySetting->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }
           
    public function unhidetrip($id, $user_id) {
        if($id) {
            $getInfo = Travelbuddy::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $hideposts = isset($getInfo['is_hide']) ? $getInfo['is_hide'] : '';
                if(!empty($hideposts)) {
                    $hideposts = explode(",", $hideposts);
                    if(in_array($id, $hideposts)) {
                        $key = array_search($id, $hideposts);
                        if($key >= 0) {
                            unset($hideposts[$key]);
                        }
                    }
                    
                    $hideposts = implode(",", $hideposts);

                    $Travelbuddy = Travelbuddy::find()->where(['user_id' => $user_id])->one();
                    $Travelbuddy->is_hide = $hideposts;
                    $Travelbuddy->update();
                    return true;
                    exit;
                }
            } 
        }
        return false;
        exit;
    } 

    public function removetrip($id, $user_id) {
        if($id) {
            $saveposts = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();            
            if(!empty($saveposts)) {
                if(isset($saveposts['travelbuddy_posts_save']) && $saveposts['travelbuddy_posts_save'] != '') {
                    $saveposts = $saveposts['travelbuddy_posts_save'];
                    $saveposts = explode(",", $saveposts);
                    if(in_array($id, $saveposts)) {
                        $key = array_search($id, $saveposts);
                        if($key >= 0) {
                            unset($saveposts[$key]);
                        }
                    }

                    $saveposts = implode(",", $saveposts);
                    $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $SecuritySetting->travelbuddy_posts_save = $saveposts;
                    $SecuritySetting->update();
                    return true;
                    exit;
                }
            } 
        }
        return false;
        exit;
    } 

    public function deletetrip($id, $user_id) {
        if($id) {
            $getInfo = Travelbuddytrip::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $getInfo = Travelbuddytrip::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->one();
                $getInfo->delete();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }

    public function deletemytrip($id, $user_id) {
        if($id) {
                $data = Travelbuddytripinvite::find()->where(['trip_id' => $id])->asarray()->all();
                if(!empty($data)) {
                    foreach ($data as $key => $sdata) {
                        $getInviteId = (string)$sdata["_id"];
                        Travelbuddytripinvitemsgs::deleteAll(['invitationId' => $getInviteId]);
                    }
                }

                Travelbuddytripinvite::deleteAll(['trip_id' => $id]);

                $data = Travelbuddytrip::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->one();
                if(!empty($data)) {
                    $data->delete();
                }

                $data = Travelbuddy::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->one();
                if(!empty($data)) {
                    $data->delete();
                }
                
                $result = array('status' => true);
                return json_encode($result, true);
        }
        
        $result = array('status' => false);
        return json_encode($result, true);
    }

     public function savetriplist($user_id) {        
        $newuserdata = [];
        $savetrip = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();
          
        $assetsPath = 'images/';

        if(!empty($savetrip)) {
            if(isset($savetrip['travelbuddy_save_post']) && $savetrip['travelbuddy_save_post'] != '') {
                $savetrip = explode(",", $savetrip['travelbuddy_save_post']);
                if(!empty($savetrip)) {
                    $result = Travelbuddytrip::find()->where(['in', '_id', $savetrip])->asarray()->all();                    
                    if(!empty($result)) {
                        $SSINFO = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                        foreach ($result as $key => $value) {
                            $id = $value['user_id'];
                            $tripid = (string)$value['_id'];
                            $value['is_saved'] = true;

                            // check this plan user is blocked or not.
                            if(!empty($SSINFO)) {
                                if(isset($SSINFO['blocked_list']) && $SSINFO['blocked_list'] != '') {
                                    $isUserGenBlock = $SSINFO['blocked_list'];
                                    $isUserGenBlock = explode(",", $isUserGenBlock);
                                    if(!empty($isUserGenBlock)) {
                                        if(in_array($id, $isUserGenBlock)) {
                                            continue;
                                        }
                                    }
                                }
                                
                                if(isset($SSINFO['travelbuddy_users_blocked']) && $SSINFO['travelbuddy_users_blocked'] != '') {
                                    $isUserBlock = $SSINFO['travelbuddy_users_blocked'];
                                    $isUserBlock = explode(",", $isUserBlock);
                                    if(!empty($isUserBlock)) {
                                        if(in_array($id, $isUserBlock)) {
                                            continue;
                                        }
                                    }
                                }

                                if(isset($SSINFO['travelbuddy_posts_delete']) && $SSINFO['travelbuddy_posts_delete'] != '') {
                                    $is_hide = $SSINFO['travelbuddy_posts_delete'];
                                    $is_hide = explode(",", $is_hide);
                                    if(!empty($is_hide)) {
                                        if(in_array($tripid, $is_hide)) {
                                            continue;
                                        }
                                    }
                                }
                            }

                            // check is_veryfied.
                            $is_verified = Verify::find()->where(['user_id' => $id,'status' => '1'])->one();
                            if($is_verified) {
                                $value['is_verified']  = '<span class="verified"><img src="'.$assetsPath.'verified-icon.png"></span>';
                            } else {
                                $value['is_verified'] = '';
                            }
                            
                            // get total count invitations............
                            $count = Travelbuddytripinvite::find()->where(['trip_id' => $tripid])->count();
                            $value['totalinvited'] = $count;

                            $iInvite = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id' => $tripid])->count();
                            if($iInvite>0) {
                                $value['iInvite'] = true;
                            }

                            $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $id])->asarray()->one();
                            if(!empty($userinfo))
                            {
                                $fullname = $userinfo['fullname'];
                                $value['fullname'] = $fullname;
                                $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                                $value['country'] = $country;
                                $city = isset($userinfo['city']) ? $userinfo['city'] : '';
                                $value['city'] = $city;
                                $thumbnail = Yii::$app->GenCls->getimage($id, 'thumb');
                                $value['profile'] = $thumbnail;

                                $userinfoPersonalinfo = Personalinfo::find()->where(['user_id' => $id])->asarray()->one();
                                if(isset($userinfoPersonalinfo) && !empty($userinfoPersonalinfo)) {
                                    unset($userinfoPersonalinfo['_id']);
                                    $value = array_merge($value, $userinfoPersonalinfo);
                                }


                                $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                                if($totalconnections >= 0) {
                                    $value['totalconnections'] = $totalconnections;
                                }
                            }  
                            $newuserdata[] = $value;              
                        }
                    }  
                }
            }
        }
        return json_encode($newuserdata);
        exit;
    }
 
    public function hidetriplist($user_id)
    {
        $newuserdata = [];
        $hidetrip = Travelbuddy::find()->select(['is_hide'])->where(['user_id' => (string)$user_id])->asarray()->one();
        if($hidetrip) {
            $hidetrip = isset($hidetrip['is_hide']) ? $hidetrip['is_hide'] : '';
            if($hidetrip) {
                $hidetrip = explode(",", $hidetrip);
                if(!empty($hidetrip)) {
                    $result = Travelbuddytrip::find()->where(['in', '_id', $hidetrip])->asarray()->all();
                     if(!empty($result)) {
                        foreach ($result as $key => $value) {
                            $id = $value['user_id'];
                            $userinfo = UserForm::find()->where([(string)'_id' => $id])->asarray()->one();
                            if(!empty($userinfo))
                            {
                                $userinfoPersonalinfo = Personalinfo::find()->where(['user_id' => $id])->asarray()->one();

                                $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                                if($totalconnections >= 0) {
                                    $userinfo['totalconnections'] = $totalconnections;
                                }

                                if(isset($userinfoPersonalinfo) && !empty($userinfoPersonalinfo)) {
                                    $userinfo = array_merge($userinfo, $userinfoPersonalinfo);
                                }
                                $value['userinfo'] = $userinfo;
                            }
                            $newuserdata[] = $value;                
                        }
                    }

                    $result = Travelbuddy::find()->where(['in', '_id', $hidetrip])->asarray()->all();
                     if(!empty($result)) {
                        foreach ($result as $key => $value) {
                            $id = $value['user_id'];
                        
                            $userinfo = UserForm::find()->where([(string)'_id' => $id])->asarray()->one();
                            if(!empty($userinfo))
                            {
                                $userinfoPersonalinfo = Personalinfo::find()->where(['user_id' => $id])->asarray()->one();

                                $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                                if($totalconnections >= 0) {
                                    $userinfo['totalconnections'] = $totalconnections;
                                }

                                if(isset($userinfoPersonalinfo) && !empty($userinfoPersonalinfo)) {
                                    $userinfo = array_merge($userinfo, $userinfoPersonalinfo);
                                }
                                $value['userinfo'] = $userinfo;
                            } 
                            $newuserdata[] = $value;             
                        }
                    }

                }
            }
        }
        return json_encode($newuserdata);
        exit;
    }
	
	public function gettripplaecsdata($placetitle,$placefirst, $user_id)
    {
        $placetitle = explode(",", $placetitle);

        if(count($placetitle) == 1) {
            $getUsers = Travelbuddytrip::find()
            ->select(['user_id'])
            ->where(['like','address', $placetitle[0]])
            ->andwhere(['not', 'user_id', (string)$user_id])
            ->asarray()
            ->distinct('user_id');
        } else if(count($placetitle) == 2) {
            $getUsers = Travelbuddytrip::find()
            ->select(['user_id'])
            ->where(['like','address', $placetitle[0]])
            ->orwhere(['like','address', $placetitle[1]])
            ->andwhere(['not', 'user_id', (string)$user_id])
            ->asarray()
            ->all();
        } else if(count($placetitle) == 3) {
            $getUsers = Travelbuddytrip::find()
            ->select(['user_id'])
            ->where(['like','address', $placetitle[0]])
            ->orwhere(['like','address', $placetitle[1]])
            ->orwhere(['like','address', $placetitle[2]])
            ->andwhere(['not', 'user_id', (string)$user_id])
            ->asarray()
            ->all();
        } else {
            $getUsers = Travelbuddytrip::find()
            ->select(['user_id'])
            ->where(['like','address', $placetitle[0]])
            ->andwhere(['not', 'user_id', (string)$user_id])
            ->asarray()
            ->distinct('user_id');
        }
        
        return $getUsers;
	}
	
	public function getAllTravellers()
    {
        return Travelbuddytrip::find()->all();
    }
	
	public function getTravelbuddyTrips()
	{
		$record =  ArrayHelper::map(Travelbuddytrip::find()->orderBy(['created_at'=>SORT_DESC])->all(), function($data) { return $data['user_id'];}, function($data) { return $data;});
		
		$vip_array = array();
    	$verify_array = array();
    	$normal_array = array();
		foreach($record as $record2)
		{
			$data = LoginForm::find()->select(['fname', 'lname', 'fullname', 'photo', 'thumbnail', 'gender', 'city', 'country', 'vip_flag'])->Where(['_id' => $record2['user_id']])->orderBy(['created_date'=>SORT_DESC])->asarray()->one();
			$verify = Verify::find()->where(['user_id' => (string) $record2['user_id'],'status' => "1"])->one();
			if(isset($data['vip_flag']) && $data['vip_flag'] != '0' && $data['vip_flag'] != '') {
			
				$vip_array[] = $data;
			}
			else if(!empty($verify)){
				$verify_array[] = $data;
			}
			else{
				$normal_array[] = $data;
			}
		}
			$data = array_merge($vip_array, $verify_array, $normal_array);
			$data = array_slice($data, 0, 4);
			return json_encode($data, true); 
	}
	
}