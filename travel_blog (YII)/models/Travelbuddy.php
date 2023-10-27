<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
 
use yii\helpers\ArrayHelper;
use frontend\models\UserForm;
use frontend\models\Personalinfo;
use frontend\models\Travelbuddytrip;
use frontend\models\Travelbuddytripinvite;
use frontend\models\Verify;
use frontend\models\Connect;

class Travelbuddy extends ActiveRecord
{
    public static function collectionName()
    {
        return 'travelbuddy';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'type', 'hostavailability', 'hostservices', 'personalmessage', 'is_hide', 'is_save', 'created_at', 'updated_at'];
    }

    public function gethostinginfo($id)
    {
        $info = Travelbuddy::find()->where(['user_id' => (string)$id])->one();
        if(empty($info)) {
            $Travelbuddy = new Travelbuddy();
            $Travelbuddy->user_id = (string)$id;
            $Travelbuddy->type = 'buddy';
            $Travelbuddy->created_at = strtotime("now");
            $Travelbuddy->save();

            $info = Travelbuddy::find()->where(['user_id' => (string)$id])->one();
        }
        return $info;
    }

    public function updatehostinginfo($post, $id)
    {   
        $HostingAvailability = isset($post['HostingAvailability']) ? $post['HostingAvailability'] : '';
        $PersonalMessage = isset($post['PersonalMessage']) ? $post['PersonalMessage'] : '';
        $HostServices = isset($post['HostServices']) ? $post['HostServices'] : '';

        if($HostingAvailability == 'AG' || $HostingAvailability == 'NAG') {
            $hosting = Travelbuddy::find()->where(['user_id' => (string)$id])->asarray()->one();
            if(!empty($hosting)) {
                $hosting = Travelbuddy::findOne(['user_id' => (string)$id]);
                if($HostingAvailability == 'NAG') {
                    $hosting->type = 'buddy';
                    $hosting->hostavailability = 'NAG';
                    $hosting->hostservices = '';
                    $hosting->personalmessage = $PersonalMessage;
                    $hosting->updated_at = strtotime("now");
                    $hosting->save();
                    return true;
                    exit;
                } else {                
                    $hosting->type = 'host';
                    $hosting->hostavailability = $HostingAvailability;
                    $hosting->hostservices = $HostServices;
                    $hosting->personalmessage = $PersonalMessage;
                    $hosting->updated_at = strtotime("now");
                    $hosting->save();
                    return true;
                    exit;
                }
            } else { 
                $hosting = new Travelbuddy();
                $hosting->user_id = (string)$id;
                $hosting->type = 'host';
                $hosting->hostavailability = $HostingAvailability;
                $hosting->hostservices = $HostServices;
                $hosting->personalmessage = $PersonalMessage;
                $hosting->created_at = strtotime("now");
                $hosting->save();
                return true;
                exit;
            }
        }

        return false;
        exit;
    }

    public function search($post, $user_id)
    {   
        $travelInfo = isset($post['travelInfo']) ? $post['travelInfo'] : array();
        $age = $post['age'];
        $hasPhoto = '';
        $verified = '';
        $minValue = '';
        $maxValue = '';
        $lookingfor = $post['lookingfor'];

        if(!empty($travelInfo)) {
            if(in_array('hasphoto', $travelInfo)) { $hasPhoto = 'Y'; }
            if(in_array('verified', $travelInfo)) { $verified = 'Y'; }
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

        if($lookingfor == 'buddy') {
            $userdata = Travelbuddytrip::find()->orderBy('_id DESC')->asarray()->all();
        } else {
            $userdata = Travelbuddy::find()->where(['type' => 'host'])->orderBy('_id DESC')->asarray()->all();
        }

          
        $assetsPath = '../../vendor/bower/travel/images/';


        $newuserdata = [];
        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $postUId = $value['user_id'];
                $postId = (string)$value['_id']; 
                $value['is_saved'] = false;

                $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($ssInfo)) {
                    $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                    if($isgenblocked) {
                        $isgenblocked = explode(",", $isgenblocked);
                        if(!empty($isgenblocked)) {
                            if(in_array($postUId, $isgenblocked)) { 
                                continue;
                            }
                        }
                    }
                    
                    $isblocked = isset($ssInfo['travelbuddy_users_blocked']) ? $ssInfo['travelbuddy_users_blocked'] : '';
                    if($isblocked) {
                        $isblocked = explode(",", $isblocked);
                        if(!empty($isblocked)) {
                            if(in_array($postUId, $isblocked)) { 
                                continue;
                            }
                        }
                    }

                    // check event is block or delete or not.
                    $isDeleted = isset($ssInfo['travelbuddy_posts_delete']) ? $ssInfo['travelbuddy_posts_delete'] : '';
                    if($isDeleted) {
                        $isDeleted = explode(",", $isDeleted);
                        if(!empty($isDeleted)) {
                            if(in_array($postId, $isDeleted)) {
                                continue;
                            }
                        }
                    }           

                    // check event is save or not...
                    $isSaved = isset($ssInfo['travelbuddy_posts_save']) ? $ssInfo['travelbuddy_posts_save'] : '';
                    if($isSaved) {
                        $isSaved = explode(",", $isSaved);
                        if(!empty($isSaved)) {
                            if(in_array($postId, $isSaved)) {
                                $value['is_saved'] = true;
                            }
                        }
                    }
                }

                // check is_veryfied.
                $is_verified = Verify::find()->where(['user_id' => $postUId,'status' => '1'])->one();
                if($is_verified) {
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

                // check for search destination...
                $city = '';
                $country = '';

                if(!empty($recommandDest)) {
                    if($lookingfor == 'host') {
                        $city = isset($userinfo['city']) ? $userinfo['city'] : '';
                        $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                        $existDest = $city . ',' .$country;
                    } else {
                        $existDest = isset($value['address']) ? $value['address'] : ''; 
                    }

                    if($existDest != '') {
                        $existDest = strtolower($existDest);
                        $existDest = str_replace(" ", ",", $existDest);
                        $existDest = str_replace(",,", ",", $existDest);
                        $existDest = str_replace(" ", ",", $existDest);
                        $existDest = explode(",", strtolower($existDest));
                        $existDest = array_filter($existDest);
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
            
                // check for travel info 
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

                // check post is receive my invitation or not..
                $is_invited = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id' => $postId])->asarray()->one();
                if(!empty($is_invited)) {
                    $value['is_invited'] = true;
                    $value['invitedInfo'] = $is_invited;
                }

                $iInvite = Travelbuddytripinvite::find()->where(['user_id' => $user_id, 'trip_id' => $postId])->count();
                if($iInvite>0) {
                    $value['iInvite'] = true;
                }    

                if(!empty($userinfo)) {
                    // check for Last Login
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

                    // check for JOin Data
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
                        $birthDate = isset($userinfo['birth_date']) ? (string)$userinfo['birth_date'] : '';
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
                    
                    $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                    $city = isset($userinfo['city']) ? $userinfo['city'] : '';
                    $value['city'] = $city;
                    $value['country'] = $country;
                    $fullname = $userinfo['fullname'];
                    $value['fullname'] = $fullname;  
                    $thumbnail = Yii::$app->GenCls->getimage($postUId,'thumb');
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

    public function getCountTBFromBuddy($user_id) {
        if(isset($user_id)) {
            $id = (string)$user_id;
            $iDsBulk = array();
            $frdList = Connect::find()->select(['from_id', 'to_id'])->Where(['to_id' => $id, 'status'=>'1'])->asarray()->all();

            if(!empty($frdList)) {
                foreach ($frdList as $key => $value) {
                    $oId = $value['from_id'];
                    if($id == $oId) {
                        $oId = $value['to_id'];
                    }

                    $iDsBulk[] = $oId;
                }

                if(!empty($iDsBulk)) {
                    $TBId1 = ArrayHelper::map(Travelbuddy::find()->select(['user_id'])->where(['type' => 'buddy'])->andWhere(['in', 'user_id', $iDsBulk])->asarray()->all(), 'user_id', 'user_id');

                    $TBId2 = ArrayHelper::map(Travelbuddytrip::find()->select(['user_id'])->where(['in', 'user_id', $iDsBulk])->asarray()->all(), 'user_id', 'user_id');

                    $TBC = count(array_unique(array_merge($TBId1, $TBId2), SORT_REGULAR));
                    if($TBC >0) {
                        return $TBC;
                        exit;
                    } else {
                        return 0;
                        exit;
                    }
                }
            }
        }

        return 0;
        exit;
    }
    
    public function getCountTBFromHost($user_id) {
        if(isset($user_id)) {
            $id = (string)$user_id;
            $iDsBulk = array();
            $frdList = Connect::find()->select(['from_id', 'to_id'])->Where(['to_id' => $id, 'status'=>'1'])->asarray()->all();

            if(!empty($frdList)) {
                foreach ($frdList as $key => $value) {
                    $oId = $value['from_id'];
                    if($id == $oId) {
                        $oId = $value['to_id'];
                    }

                    $iDsBulk[] = $oId;
                }

                if(!empty($iDsBulk)) {
                    return Travelbuddy::find()->where(['type' => 'host'])->andWhere(['in', 'user_id', $iDsBulk])->count();
                    exit;
                }
            }
        }

        return 0;
        exit;
    }
	
	public function getTravelbuddy()
	{
		return Travelbuddy::find()->select(['user_id'])->orderBy(['created_at'=>SORT_DESC])->limit(4)->offset(0)->all();
	}
	
}