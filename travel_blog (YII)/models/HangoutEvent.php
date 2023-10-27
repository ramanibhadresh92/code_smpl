<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\Personalinfo;
use frontend\models\SecuritySetting;
use frontend\models\Verify;
use frontend\models\HangoutEventInvite;
use frontend\models\Travelbuddytripinvite;
use frontend\models\TravelSavePost;
use backend\models\HangoutEventName;

class HangoutEvent extends ActiveRecord
{
    public static function collectionName()
    {
        return 'hangout_event';
    }

    public function attributes()
    {
         return ['_id', 'user_id', 'evtname', 'evtprofile', 'evtlocation', 'evtdate', 'evttime', 'evtdesc', 'created_at', 'updated_at'];
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

    public function createevent($post, $user_id)
    {  
        $evtid = (isset($post['evtid']) && $post['evtid']) ? $post['evtid'] : '';        
        $evtinfo = HangoutEventName::find()->where([(string)'_id' => $evtid])->asarray()->one();
        $date = time();
        if(!empty($evtinfo)) {
            $evtname = $evtinfo['name'];
            $evtprofile = $evtinfo['profile'];
            $evtlocation = $post['evtlocation'];
            //$evtvenue = $post['evtvenue'];
            $evtdate = $post['evtdate'];
            $evttime = $post['evttime']; 
            $evtdesc = $post['evtdesc'];

            if($evtname != '' || $evtprofile != '' || $evtlocation != '' || $evtdate != '' || $evttime != '') {
                $HangoutEvent = new HangoutEvent();
                $HangoutEvent->user_id = $user_id;
                $HangoutEvent->evtname = $evtname;
                $HangoutEvent->evtprofile = $evtprofile;
                $HangoutEvent->evtlocation = $evtlocation;
                $HangoutEvent->evtdate = $evtdate;
                $HangoutEvent->evttime = $evttime;
                $HangoutEvent->evtdesc = $evtdesc;
                $HangoutEvent->created_at = strtotime("now");
                if($HangoutEvent->save()) {
                    $eventCreatedAt = HangoutEvent::find()->select(['user_id'])->where(['user_id' => $user_id])->orderby('_id DESC')->asarray()->one();
                    $notification = new Notification();
                    $notification->hangout_id = (string)$eventCreatedAt['_id'];
                    $notification->post_owner_id = $eventCreatedAt['user_id'];
                    $notification->notification_type = 'addposthangout';
                    $notification->is_deleted = '0';
                    $notification->status = '1';
                    $notification->created_date = "$date";
                    $notification->updated_date = "$date";
                    $notification->insert();
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

    public function myEventsPlans($user_id) {
        $userdata = HangoutEvent::find()->where(['user_id' => $user_id])->orderBy('_id DESC')->asarray()->all();
        if(!empty($userdata)) {
            return json_encode($userdata, true);
            exit;
        }      
    } 

    public function fetcheditevent($id, $user_id)
    {
        $result = HangoutEvent::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->asarray()->one();
        return json_encode($result, true);
        exit;
    }

    public function editeventsave($post, $user_id)
    {   
        
        if(isset($post['id']) && $post['id'] != '') {
            $evtid = (isset($post['evtid']) && $post['evtid']) ? $post['evtid'] : '';
            $evtlocation = (isset($post['evtlocation']) && $post['evtlocation']) ? $post['evtlocation'] : '';
            $evtdate = (isset($post['evtdate']) && $post['evtdate']) ? $post['evtdate'] : '';
            $evttime = (isset($post['evttime']) && $post['evttime']) ? $post['evttime'] : '';
            $evtdesc = (isset($post['evtdesc']) && $post['evtdesc']) ? $post['evtdesc'] : '';

            if($evtid != '' || $evtlocation != '' || $evtdate != '' || $evttime != '') {
                $eventName = HangoutEventName::find()->where([(string)'_id' => $evtid])->asarray()->one();
                if(!empty($eventName)) {
                    $id = $post['id'];
                    $name = $eventName['name'];
                    $eventProfile = $eventName['profile'];
                    
                    $HangoutEvent = HangoutEvent::find()->where([(string)'_id' => $id])->one();
                    $HangoutEvent->evtname = $name;
                    $HangoutEvent->evtprofile = $eventProfile;
                    $HangoutEvent->evtlocation = $evtlocation;
                    //$HangoutEvent->evtvenue = $evtvenue;
                    $HangoutEvent->evtdate = $evtdate;
                    $HangoutEvent->evttime = $evttime;
                    $HangoutEvent->evtdesc = $evtdesc;
                    $HangoutEvent->updated_at = strtotime("now");
                    if($HangoutEvent->save()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function deletemyevent($id, $user_id) {
        if($id) {
            $data = HangoutEvent::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->asarray()->one();
            if(!empty($data)) {

                $data = HangoutEventInvite::find()->where(['event_id' => $id])->asarray()->all();
                if(!empty($data)) {
                    foreach ($data as $key => $sdata) {
                        $getInviteId = (string)$sdata["_id"];
                        HangoutEventInviteMsgs::deleteAll(['event_request_id' => $getInviteId]);
                    }
                }
                HangoutEventInvite::deleteAll(['event_id' => $id]);
                $data = HangoutEvent::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->one();
                $data->delete();
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            }
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;  
    }

    public function recentEventsPlans($user_id,$start=0) {
		if($start == '') {
			$start = 0;
		} 
        $userdata = HangoutEvent::find()->orderBy('_id DESC')->limit(12)->offset($start)->asarray()->all();
        $newuserdata = [];
        if(!empty($userdata)) {
            $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
            $savedata = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();
            foreach ($userdata as $key => $value) {
                $id = $value['user_id'];
                $eventid = (string)$value['_id'];
                $value['is_saved'] = false;

                if(!empty($ssInfo)) {
                    $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                    if($isgenblocked) {
                        $isgenblocked = explode(",", $isgenblocked);
                        if(!empty($isgenblocked)) {
                            if(in_array($id, $isgenblocked)) { 
                                continue;
                            }
                        }
                    }

                    $isblocked = isset($ssInfo['hangout_users_blocked']) ? $ssInfo['hangout_users_blocked'] : '';
                    if($isblocked) {
                        $isblocked = explode(",", $isblocked);
                        if(!empty($isblocked)) {
                            if(in_array($id, $isblocked)) {
                                continue;
                            }
                        }
                    }

                    $isDeleted = isset($ssInfo['hangout_posts_delete']) ? $ssInfo['hangout_posts_delete'] : '';
                    if($isDeleted) {
                        $isDeleted = explode(",", $isDeleted);
                        if(!empty($isDeleted)) {
                            if(in_array($eventid, $isDeleted)) {
                                continue;
                            }
                        }
                    }                               
                }
               
                if(!empty($savedata)) {
                    $isSaved = isset($savedata['hangout_save_posts']) ? $savedata['hangout_save_posts'] : '';
                    if($isSaved) {
                        $isSaved = explode(",", $isSaved);
                        if(!empty($isSaved)) {
                            if(in_array($eventid, $isSaved)) {
                                $value['is_saved'] = true;
                            }
                        }
                    }
                }

                $isInvite = HangoutEventInvite::find()->where(['user_id' => $user_id, 'event_id' => $eventid])->count();
                if($isInvite >0) {
                    $value['is_invited'] = true;
                }
                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo'])->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($userinfo))
                {   
                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $thumbnail = Yii::$app->GenCls->getimage($id, 'thumb');
                    $value['profile'] = $thumbnail;

                }  else {
                    continue;
                }
                $newuserdata[] = $value;             
            }
        }
        return json_encode($newuserdata);
        exit;
    }

    public function recentEventsPlansnew($user_id, $address='', $start, $limit) {
        $userdata = HangoutEvent::find()->where(['like','evtlocation', $address])->andWhere(['not','user_id', $user_id])->orderBy('_id DESC')->limit($limit)->offset($start)->asarray()->all();
        
        $newuserdata = [];
        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $id = $value['user_id'];
                $eventid = (string)$value['_id'];
                $value['is_saved'] = false;
                $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($ssInfo)) {
                    $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                    if($isgenblocked) {
                        $isgenblocked = explode(",", $isgenblocked);
                        if(!empty($isgenblocked)) {
                            if(in_array($eventid, $isgenblocked)) { 
                                continue;
                            }
                        }
                    }

                    $isblocked = isset($ssInfo['hangout_users_blocked']) ? $ssInfo['hangout_users_blocked'] : '';
                    if($isblocked) {
                        $isblocked = explode(",", $isblocked);
                        if(!empty($isblocked)) {
                            if(in_array($id, $isblocked)) {
                                continue;
                            }
                        }
                    }

                    $isDeleted = isset($ssInfo['hangout_posts_delete']) ? $ssInfo['hangout_posts_delete'] : '';
                    if($isDeleted) {
                        $isDeleted = explode(",", $isDeleted);
                        if(!empty($isDeleted)) {
                            if(in_array($eventid, $isDeleted)) {
                                continue;
                            }
                        }
                    }           

                    $isSaved = isset($ssInfo['hangout_posts_save']) ? $ssInfo['hangout_posts_save'] : '';
                    if($isSaved) {
                        $isSaved = explode(",", $isSaved);
                        if(!empty($isSaved)) {
                            if(in_array($eventid, $isSaved)) {
                                $value['is_saved'] = true;
                            }
                        }
                    }
                }

                $isInvite = HangoutEventInvite::find()->where(['user_id' => $user_id, 'event_id' => $eventid])->count();
                if($isInvite >0) {
                    $value['is_invited'] = true;
                }
                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo'])->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($userinfo))
                {   
                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $thumbnail = Travelbuddytripinvite::getimage($id, 'thumb');
                    $value['profile'] = $thumbnail;
                }  else {
                    continue;
                }
                $newuserdata[] = $value;             
            }
        }
        return json_encode($newuserdata);
        exit;
    }

    public function selectedrecord($id, $address, $user_id='') {
        $newuserdata = [];
        
        if('all' == strtolower(trim($id))) {
            $userdata = HangoutEvent::find()->where(['like','evtlocation', $address])->orderBy('_id DESC')->asarray()->all();
        } else {
            $userdata = HangoutEvent::find()->where(['like','evtlocation', $address])->andWhere([(string)'_id' => $id])->orderBy('_id DESC')->asarray()->all();
        }
        
        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $id = $value['user_id'];
                $eventid = (string)$value['_id'];
                $value['is_saved'] = false;

                $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($ssInfo)) {
                    $isblocked = isset($ssInfo['hangout_users_blocked']) ? $ssInfo['hangout_users_blocked'] : '';
                    if($isblocked) {
                        $isblocked = explode(",", $isblocked);
                        if(!empty($isblocked)) {
                            if(in_array($id, $isblocked)) {
                                continue;
                            }
                        }
                    }

                    $isDeleted = isset($ssInfo['hangout_posts_delete']) ? $ssInfo['hangout_posts_delete'] : '';
                    if($isDeleted) {
                        $isDeleted = explode(",", $isDeleted);
                        if(!empty($isDeleted)) {
                            if(in_array($eventid, $isDeleted)) {
                                continue;
                            }
                        }
                    }           

                     $isSaved = isset($ssInfo['hangout_posts_save']) ? $ssInfo['hangout_posts_save'] : '';
                    if($isSaved) {
                        $isSaved = explode(",", $isSaved);
                        if(!empty($isSaved)) {
                            if(in_array($eventid, $isSaved)) {
                                $value['is_saved'] = true;
                            }
                        }
                    }
                }

                $isInvite = HangoutEventInvite::find()->where(['user_id' => $user_id, 'event_id' => $eventid])->count();
                if($isInvite >0) {
                    $value['is_invited'] = true;
                }
                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo'])->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($userinfo))
                {   
                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $thumbnail = Yii::$app->GenCls->getimage($id, 'thumb');
                    $value['profile'] = $thumbnail;
                }  else {
                    continue;
                }
                $newuserdata[] = $value;             
            }
        }
        return json_encode($newuserdata);
        exit;
    }

    public function savedeventlist($user_id) {
        if($user_id) { 
            $savedIds = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($savedIds)) {
                if(isset($savedIds['hangout_save_posts']) && $savedIds['hangout_save_posts'] != '') {
                    $savedIds = explode(",", $savedIds['hangout_save_posts']);
                    if(!empty($savedIds)) {
                        $userdata = HangoutEvent::find()->where(['in', (string)'_id', $savedIds])->orderBy('_id DESC')->asarray()->all();
                        $newuserdata = [];
                        if(!empty($userdata)) {
                            $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                            $savedata = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();
                            foreach ($userdata as $key => $value) {
                                $id = $value['user_id'];
                                $eventid = (string)$value['_id'];
                                $value['is_saved'] = false;

                                if(!empty($ssInfo)) {
                                    $isblocked = isset($ssInfo['hangout_users_blocked']) ? $ssInfo['hangout_users_blocked'] : '';
                                    if($isblocked) {
                                        $isblocked = explode(",", $isblocked);
                                        if(!empty($isblocked)) {
                                            if(in_array($id, $isblocked)) {
                                                continue;
                                            }
                                        }
                                    }

                                    // check event is block or delete or not.
                                    $isDeleted = isset($ssInfo['hangout_posts_delete']) ? $ssInfo['hangout_posts_delete'] : '';
                                    if($isDeleted) {
                                        $isDeleted = explode(",", $isDeleted);
                                        if(!empty($isDeleted)) {
                                            if(in_array($eventid, $isDeleted)) {
                                                continue;
                                            }
                                        }
                                    }           
                                }

                                if(!empty($savedata)) {
                                    $isSaved = isset($savedata['hangout_save_posts']) ? $savedata['hangout_save_posts'] : '';
                                    if($isSaved) {
                                        $isSaved = explode(",", $isSaved);
                                        if(!empty($isSaved)) {
                                            if(in_array($eventid, $isSaved)) {
                                                $value['is_saved'] = true;
                                            }
                                        }
                                    }
                                }

                                $isInvite = HangoutEventInvite::find()->where(['user_id' => $user_id, 'event_id' => $eventid])->count();
                                if($isInvite >0) {
                                    $value['is_invited'] = true;
                                }

                                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo'])->where([(string)'_id' => $id])->asarray()->one();
                                if(!empty($userinfo))
                                {   
                                    $fullname = $userinfo['fullname']; 
                                    $value['fullname'] = $fullname;
                                    $thumbnail = Yii::$app->GenCls->getimage($id, 'thumb');
                                    $value['profile'] = $thumbnail;

                                } else {
                                    continue;
                                }
                                $newuserdata[] = $value;             
                            }
                        }

                        return json_encode($newuserdata);
                        exit;
                    }
                }
            }
        }
    }

    public function getUserInfo($user_id) {
        $userdata = UserForm::find()->select(['fullname'])->where([(string)'_id' => (string)$user_id])->asarray()->one();
        return $userdata;
    }

    public function saveevent($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $saveevents = isset($getInfo['hangout_posts_save']) ? $getInfo['hangout_posts_save'] : '';
                if(!empty($saveevents)) {
                    $saveevents = explode(",", $saveevents);
                    if(!in_array($id, $saveevents)) {
                        $saveevents[] = $id;
                    } 
                    $saveevents = implode(",", $saveevents);
                } else {
                    $saveevents = $id;
                }

                $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                $SS->hangout_posts_save = $saveevents;
                $SS->save();
                return true;
                exit;
            } else {
                $SS = new SecuritySetting();
                $SS->user_id = $user_id;
                $SS->hangout_posts_save = $id;
                $SS->save();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }

    public function deleteevent($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $deleteevent = isset($getInfo['hangout_posts_delete']) ? $getInfo['hangout_posts_delete'] : '';
                if(!empty($deleteevent)) {
                    $deleteevent = explode(",", $deleteevent);
                    if(!in_array($id, $deleteevent)) {
                        $deleteevent[] = $id;
                    }
                    $deleteevent = implode(",", $deleteevent);
                } else {
                    $deleteevent = $id;
                }

                $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                $SS->hangout_posts_delete = $deleteevent;
                $SS->save();
                return true;
                exit;
            } else {
                $SS = new SecuritySetting();
                $SS->user_id = $user_id;
                $SS->hangout_posts_delete = $id;
                $SS->save();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }
    
     public function blockuserevent($id, $user_id)
    {
        if($id) {
            $uid = HangoutEvent::find()->select(['user_id'])->where([(string)'_id' => $id])->asarray()->one();
            if(!empty($uid)) {
                    $uid = $uid['user_id'];
                $getInfo = SecuritySetting::find()->select(['hangout_users_blocked'])->where(['user_id' => (string)$user_id])->asarray()->one();
                if(!empty($getInfo)) {
                    $bockusr = isset($getInfo['hangout_users_blocked']) ? $getInfo['hangout_users_blocked'] : '';
                    if(!empty($bockusr)) {
                        $bockusr = explode(",", $bockusr);
                        if(!in_array($uid, $bockusr)) {
                            $bockusr[] = $uid;
                        }
                        $bockusr = implode(",", $bockusr);
                    } else {
                        $bockusr = $uid;
                    }

                    $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $SS->hangout_users_blocked = $bockusr;
                    $SS->save();
                    return true;
                    exit;
                } else {
                    $SS = new SecuritySetting();
                    $SS->user_id = $user_id;
                    $SS->hangout_users_blocked = $uid;
                    $SS->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }


    public function unsaveevent($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $saveevents = isset($getInfo['hangout_posts_save']) ? $getInfo['hangout_posts_save'] : '';
                if(!empty($saveevents)) {
                    $saveevents = explode(",", $saveevents);

                    if(in_array($id, $saveevents)) {
                        $key = array_search($id, $saveevents);
                        if($key >= 0) {
                            unset($saveevents[$key]);
                        }
                    }
                    $saveevents = implode(",", $saveevents);
                    $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $SS->hangout_posts_save = $saveevents;
                    $SS->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }

    public function invite($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $saveevents = isset($getInfo['hangout_posts_save']) ? $getInfo['hangout_posts_save'] : '';
                if(!empty($saveevents)) {
                    $saveevents = explode(",", $saveevents);

                    if(in_array($id, $saveevents)) {
                        $key = array_search($id, $saveevents);
                        if($key >= 0) {
                            unset($saveevents[$key]);
                        }
                    }
                    $saveevents = implode(",", $saveevents);
                    $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $SS->hangout_posts_save = $saveevents;
                    $SS->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }

    public function search($post, $user_id='')
    {   
        $hangoutInfo = isset($post['hangoutInfo']) ? $post['hangoutInfo'] : array();
        $age = $post['age'];
        $hasPhoto = '';
        $verified = '';
        $minValue = '';
        $maxValue = '';

        if(!empty($hangoutInfo)) {
            if(in_array('hasphoto', $hangoutInfo)) { $hasPhoto = 'Y'; }
            if(in_array('verified', $hangoutInfo)) { $verified = 'Y'; }
        }

        if(!empty($age)) {
            $minValue = $age['minValue'];
            $maxValue = $age['maxValue'];
        }

        $gender = isset($post['gender']) ? $post['gender'] : array();
        $recommandDest = isset($post['recommandDest']) ? $post['recommandDest'] : array();
        if($recommandDest != '') {
            $recommandDest = strtolower($recommandDest);
            $recommandDest = str_replace(" ", ",", $recommandDest);
            $recommandDest = str_replace(",,", ",", $recommandDest);
            $recommandDest = str_replace(" ", ",", $recommandDest);
            $recommandDest = explode(",", $recommandDest);
            $recommandDest = array_map('trim', $recommandDest);
        }

        $language = isset($post['language']) ? $post['language'] : array();
        $recommadLastLogin = isset($post['lastLogin']) ? $post['lastLogin'] : '';
        $recommadJoinDate = isset($post['joinDate']) ? $post['joinDate'] : '';
        
        $userdata = HangoutEvent::find()->orderBy('_id DESC')->asarray()->all();
        $newuserdata = [];
          
        $assetsPath = '../../vendor/bower/travel/images/';

        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $postUId = $value['user_id'];
                $postId = (string)$value['_id'];
                $value['is_saved'] = false;

                $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($ssInfo)) {
                    $isblocked = isset($ssInfo['hangout_users_blocked']) ? $ssInfo['hangout_users_blocked'] : '';
                    if($isblocked) {
                        $isblocked = explode(",", $isblocked);
                        if(!empty($isblocked)) {
                            if(in_array($postUId, $isblocked)) { 
                                continue;
                            }
                        }
                    }

                    $isDeleted = isset($ssInfo['hangout_posts_delete']) ? $ssInfo['hangout_posts_delete'] : '';
                    if($isDeleted) {
                        $isDeleted = explode(",", $isDeleted);
                        if(!empty($isDeleted)) {
                            if(in_array($postId, $isDeleted)) {
                                continue;
                            }
                        }
                    }           

                    $isSaved = isset($ssInfo['hangout_posts_save']) ? $ssInfo['hangout_posts_save'] : '';
                    if($isSaved) {
                        $isSaved = explode(",", $isSaved);
                        if(!empty($isSaved)) {
                            if(in_array($postId, $isSaved)) {
                                $value['is_saved'] = true;
                            }
                        }
                    }
                }

                $is_verified = Verify::find()->where(['user_id' => $postUId,'status' => '1'])->one();
                if(!empty($is_verified)) {
                    $value['is_verified']  = '<span class="verified"><img src="'.$assetsPath.'verified-icon.png"></span>';
                } else {
                    $value['is_verified'] = '';
                }

                if(!empty($gender)) {
                    $userinfo = UserForm::find()->where([(string)'_id' => $postUId])->asarray()->one();
                    if(isset($userinfo['gender'])) {
                        if(!in_array($userinfo['gender'], $gender)) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                } else {
                    $userinfo = UserForm::find()->where([(string)'_id' => $postUId])->asarray()->one();
                }

                $city = '';
                $country = '';

                if(!empty($recommandDest)) {
                    $existDest = isset($value['evtlocation']) ? $value['evtlocation'] : ''; 
                    if($existDest != '') {
                        $existDest = strtolower($existDest);
                        $existDest = str_replace(" ", ",", $existDest);
                        $existDest = str_replace(",,", ",", $existDest);
                        $existDest = str_replace(" ", ",", $existDest);
                        $existDest = explode(",", $existDest);
                        $existDest = array_map('trim', $existDest);
                        foreach ($recommandDest as $key => $singlerecommandDest) {
                            if(!in_array($singlerecommandDest, $existDest)) {
                                continue 2;
                            }
                        }
                    } else {
                        continue;
                    }
                }

                if($verified == 'Y') { if($value['is_verified'] != 'Y') { continue; } }
                
                $language = array_filter($language);
                if(!empty($language)) {
                    $personalinfo = Personalinfo::find()->where(['user_id' => $user_id])->asarray()->one();
                    if(!empty($personalinfo) && isset($personalinfo['language'])) {
                        $pInfoLanguage = $personalinfo['language'];
                        $pInfoLanguage = array_map('trim', explode(',', $pInfoLanguage));
                        $checklanguage = count(array_intersect($pInfoLanguage, $language));
                        if($checklanguage <= 0) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }                

                $is_invited = HangoutEventInvite::find()->where(['user_id' => $user_id, 'event_id' => $postId])->asarray()->one();
                if(!empty($is_invited)) {
                    $value['is_invited'] = true;
                    $value['invitedInfo'] = $is_invited;
                }
        
                if(!empty($userinfo)) {
                    if($recommadLastLogin != 'NT') {
                        $lastLogin = isset($userinfo['last_login_time']) ? $userinfo['last_login_time'] : '';
                        if($lastLogin) {
                            $today = time();
                            $datediff = $today - $lastLogin;
                            $diff = floor($datediff / (60 * 60 * 24));
                            if($recommadLastLogin == 'MORE') {
                                if($diff <= 365) {
                                    continue;
                                }
                            } else if($recommadLastLogin == '365') {
                                if($diff > 365) {
                                    continue;
                                }
                            } else if($recommadLastLogin == '30') {
                                if($diff > 30) {
                                    continue;
                                }
                            } else if($recommadLastLogin == '7') {
                                if($diff > 7) {
                                    continue;
                                }
                            } else if($recommadLastLogin == '1') {
                                if($diff > 1) {
                                    continue;
                                }
                            }
                        } else {
                            continue;
                        }
                    }

                    if($recommadJoinDate != 'NT') {
                        $joinDate = $userinfo['created_date'];
                        $today = time();
                        $datediff = $today - $joinDate;
                        $diff = floor($datediff / (60 * 60 * 24));

                        if($recommadJoinDate == 'MORE') {
                            if($diff <= 365) {
                                continue;
                            }
                        } else if($recommadJoinDate == '365') {
                            if($diff > 365) {
                                continue;
                            }
                        } else if($recommadJoinDate == '30') {
                            if($diff > 30) { 
                                continue;
                            }
                        } else if($recommadJoinDate == '7') {
                            if($diff > 7) {
                                continue;
                            }
                        } else if($recommadJoinDate == '1') {
                            if($diff > 1) {
                                continue;
                            }
                        }
                    }


                    if(isset($age) && !empty($age)) {
                        $birthDate = isset($userinfo['birth_date']) ? $userinfo['birth_date'] : '';
                        if($birthDate != '') {
                            if (strpos($birthDate, '/') !== false) {
                                $birthDate = explode("/", $birthDate);
                            } else if(strpos($birthDate, '-') !== false) {
                                $birthDate = explode("-", $birthDate);
                            }

                            if(count($birthDate) == 3) {
                                $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")) ? ((date("Y") - $birthDate[2]) - 1) : (date("Y") - $birthDate[2]);
                                if(!($age >= $minValue) && ($age <= $maxValue)) {
                                    continue;
                                }
                            } else {
                                    continue;
                            }
                        }
                    }

                    $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                    if($totalconnections>0) {
                        $value['totalconnections'] = $totalconnections;
                    } else {
                        $value['totalconnections'] = 0;
                    }

                    $value['city'] = $city;
                    $value['country'] = $country;
                    $fullname = $userinfo['fullname'];
                    $value['fullname'] = $fullname;
                    $thumbnail = Yii::$app->GenCls->getimage($postUId, 'thumb');
                    $value['profile'] = $thumbnail; 
                    $newuserdata[] = $value;     
                }   
            }

            return json_encode(array_values($newuserdata));
            exit;
        }
        return json_encode($newuserdata);
        exit;
    }
    public function putmap($id) {
        $evtInfo = HangoutEvent::find()->select(['evtlocation'])->where([(string)'_id' => $id])->asarray()->one();
        if(!empty($evtInfo)) {
            $evtlocation = $evtInfo['evtlocation'];

            $resultArray = array('status' => true, 'location' => $evtlocation);
            return json_encode($resultArray, true);
            exit;
        }

        $resultArray = array('status' => false);
        return json_encode($resultArray, true);
        exit;
    }
   
	public function getTotalhangout(){
		return HangoutEvent::find()->count();
	}
}