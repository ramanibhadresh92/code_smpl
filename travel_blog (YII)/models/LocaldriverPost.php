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
use frontend\models\Verify;
use frontend\models\LocaldriverPostInvite;
use frontend\models\Travelbuddytripinvite;
use frontend\models\TravelSavePost;

class LocaldriverPost extends ActiveRecord
{
    public static function collectionName()
    {
        return 'localdriver_post';
    }

    public function attributes()
    {
         return ['_id', 'user_id', 'images', 'vehicletype', 'onboard', 'vehiclecapacity', 'restriction', 'describeyourtalent', 'activity', 'rate', 'created_at', 'updated_at','flagger', 'flagger_date', 'flagger_by'];
    }

    public function getUserInfo($user_id) {
       $userdata = UserForm::find()->select(['fullname'])->where([(string)'_id' => (string)$user_id])->asarray()->one();
        return $userdata;
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

        public function createpost($post, $files, $user_id)
    {  
        $activities = array("Touring", "Site Seeing", "Parks", "Museum", "Beaches", "Showing the city", "Outdoor event");
        $rates = array("$35", "$40", "$45", "$50", "$55", "$60", "$65", "$70", "$75", "$80", "$90", "$100");
        $localdriver_vehicletype = (isset($post['localdriver_vehicletype']) && $post['localdriver_vehicletype']) ? $post['localdriver_vehicletype'] : '';
        $localdriver_onboard = (isset($post['localdriver_onboard']) && $post['localdriver_onboard']) ? $post['localdriver_onboard'] : '';
        $localdriver_vehiclecapacity = (isset($post['localdriver_vehiclecapacity']) && $post['localdriver_vehiclecapacity']) ? $post['localdriver_vehiclecapacity'] : '';
        $localdriver_restriction = (isset($post['localdriver_restriction']) && $post['localdriver_restriction']) ? $post['localdriver_restriction'] : '';
        $localdriver_describeyourtalent = (isset($post['localdriver_describeyourtalent']) && $post['localdriver_describeyourtalent']) ? $post['localdriver_describeyourtalent'] : '';
        $localdriver_activity = (isset($post['localdriver_activity']) && $post['localdriver_activity']) ? $post['localdriver_activity'] : '';
        $localdriver_rate = (isset($post['localdriver_rate']) && $post['localdriver_rate']) ? $post['localdriver_rate'] : '';
        $localdriver_images = $files['localdriver_images'];

        $url = '../web/uploads/localdriver/';

        if(isset($localdriver_images['name']) && !empty($localdriver_images['name'])) {
            $imgArray = array();
            for ($i=0; $i < count($localdriver_images['name']); $i++) { 
                $date = time();
                $name = $localdriver_images['name'][$i];
                $tmp_name = $localdriver_images['tmp_name'][$i];
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $time = time();
                $uniqid = uniqid();
                $gen_name = $time.$uniqid.'.'.$extension;
                move_uploaded_file($tmp_name, $url . $date . $gen_name);
                $img = $url . $date . $gen_name;
                $imgArray[] = $img;
            }

            $LocaldriverPost = new LocaldriverPost();
            $LocaldriverPost->user_id = $user_id;
            $LocaldriverPost->vehicletype = $localdriver_vehicletype;
            $LocaldriverPost->onboard = $localdriver_onboard;
            $LocaldriverPost->vehiclecapacity = $localdriver_vehiclecapacity;

            if(in_array($localdriver_activity, $activities)) {
                $LocaldriverPost->activity = $localdriver_activity;
            } else {
                $LocaldriverPost->activity = '';
            }

            if(in_array($localdriver_rate, $rates)) {
                $LocaldriverPost->rate = $localdriver_rate;
            } else {
                $LocaldriverPost->rate = '';
            }
            
            $LocaldriverPost->restriction = $localdriver_restriction;
            $LocaldriverPost->describeyourtalent = $localdriver_describeyourtalent;
            $LocaldriverPost->images = implode(",", $imgArray);
            $LocaldriverPost->created_at = strtotime("now");
            if($LocaldriverPost->save()) {
                $eventCreatedAt = LocaldriverPost::find()->select(['user_id'])->where(['user_id' => $user_id])->andWhere(['not','flagger', "yes"])->orderby('_id DESC')->asarray()->one();
                if(!empty($eventCreatedAt)) {
                    $notification = new Notification();
                    $notification->localdriver_id = (string)$eventCreatedAt['_id'];
                    $notification->post_owner_id = "$user_id";
                    $notification->notification_type = 'addpostlocaldriver';
                    $notification->is_deleted = '0';
                    $notification->status = '1';
                    $notification->created_date = $date;
                    $notification->updated_date = $date;
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

            $result = array('status' => false);
            return json_encode($result, true);
            exit;
        }
    }  

    public function editpost($post, $files, $user_id)
    {  
        $activities = array("Touring", "Site Seeing", "Parks", "Museum", "Beaches", "Showing the city", "Outdoor event");
        $rates = array("$35", "$40", "$45", "$50", "$55", "$60", "$65", "$70", "$75", "$80", "$90", "$100");
        $localdriver_id = $post['id'];
        $LocaldriverPost = LocaldriverPost::find()->where([(string)'_id' => $localdriver_id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($LocaldriverPost)) {
            $localdriver_vehicletype = (isset($post['localdriver_vehicletype']) && $post['localdriver_vehicletype']) ? $post['localdriver_vehicletype'] : '';
            $localdriver_onboard = (isset($post['localdriver_onboard']) && $post['localdriver_onboard']) ? $post['localdriver_onboard'] : '';
            $localdriver_vehiclecapacity = (isset($post['localdriver_vehiclecapacity']) && $post['localdriver_vehiclecapacity']) ? $post['localdriver_vehiclecapacity'] : '';
            $localdriver_restriction = (isset($post['localdriver_restriction']) && $post['localdriver_restriction']) ? $post['localdriver_restriction'] : '';
            $localdriver_describeyourtalent = (isset($post['localdriver_describeyourtalent']) && $post['localdriver_describeyourtalent']) ? $post['localdriver_describeyourtalent'] : '';
            $localdriver_activity = (isset($post['localdriver_activity']) && $post['localdriver_activity']) ? $post['localdriver_activity'] : '';
            $localdriver_rate = (isset($post['localdriver_rate']) && $post['localdriver_rate']) ? $post['localdriver_rate'] : '';
            $localdriver_images = $files['localdriver_images'];

            $url = '../web/uploads/localdriver/';

            $old_images = $LocaldriverPost['images'];
            $old_images = explode(',', $old_images);
            $old_images = array_values(array_filter($old_images));

            $url = '../web/uploads/localdriver/';

            $localdriver_images = isset($files['localdriver_images']) ? $files['localdriver_images'] : array();
            if(isset($localdriver_images['name']) && !empty($localdriver_images['name'])) {
                for ($i=0; $i < count($localdriver_images['name']); $i++) { 
                    $date = time();
                    $name = $localdriver_images['name'][$i];
                    $tmp_name = $localdriver_images['tmp_name'][$i];
                    $extension = pathinfo($name, PATHINFO_EXTENSION);
                    $time = time();
                    $uniqid = uniqid();
                    $gen_name = $time.$uniqid.'.'.$extension;
                    move_uploaded_file($tmp_name, $url . $date . $gen_name);
                    $img = $url . $date . $gen_name;
                    $old_images[] = $img;
                }
            }

            $images = array_slice($old_images, 0, 3);

            $LocaldriverPost->user_id = $user_id;
            $LocaldriverPost->vehicletype = $localdriver_vehicletype;
            $LocaldriverPost->onboard = $localdriver_onboard;
            $LocaldriverPost->vehiclecapacity = $localdriver_vehiclecapacity;
            if(in_array($localdriver_activity, $activities)) {
                $LocaldriverPost->activity = $localdriver_activity;
            } else {
                $LocaldriverPost->activity = '';
            }

            if(in_array($localdriver_rate, $rates)) {
                $LocaldriverPost->rate = $localdriver_rate;
            } else {
                $LocaldriverPost->rate = '';
            }
            $LocaldriverPost->restriction = $localdriver_restriction;
            $LocaldriverPost->describeyourtalent = $localdriver_describeyourtalent;
            $LocaldriverPost->images = implode(",", $images);
            $LocaldriverPost->updated_at = strtotime("now");
            if($LocaldriverPost->update()) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function uploadphotoslocaldriversave($post, $files, $user_id)
    {  
        $localdriver_id = $post['id'];
        $LocaldriverPost = LocaldriverPost::find()->where([(string)'_id' => $localdriver_id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($LocaldriverPost)) {
            $localdriver_images = $files['localdriver_images'];
            $url = '../web/uploads/localdriver/';
            $old_images = $LocaldriverPost['images'];
            $old_images = explode(',', $old_images);
            $old_images = array_values(array_filter($old_images));
            $localdriver_images = isset($files['localdriver_images']) ? $files['localdriver_images'] : array();
            if(isset($localdriver_images['name']) && !empty($localdriver_images['name'])) {
                for ($i=0; $i < count($localdriver_images['name']); $i++) { 
                    $date = time();
                    $name = $localdriver_images['name'][$i];
                    $tmp_name = $localdriver_images['tmp_name'][$i];
                    $extension = pathinfo($name, PATHINFO_EXTENSION);
                    $time = time();
                    $uniqid = uniqid();
                    $gen_name = $time.$uniqid.'.'.$extension;
                    move_uploaded_file($tmp_name, $url . $date . $gen_name);
                    $img = $url . $date . $gen_name;
                    $old_images[] = $img;
                }
            }

            $LocaldriverPost->images = implode(",", $old_images);
            $LocaldriverPost->updated_at = strtotime("now");
            if($LocaldriverPost->update()) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }  

    public function myposts($user_id) {
        $userdata = LocaldriverPost::find()->where(['user_id' => $user_id])->andWhere(['not','flagger', "yes"])->orderBy('_id DESC')->asarray()->all();
        $newuserdata = [];
        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $postUId = $value['user_id'];
                $postId = (string)$value['_id'];
                $value['language'] = '';
              
                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $postUId])->asarray()->one();
                if(!empty($userinfo))
                {   
                    if($user_id != '') {
                        $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                        if($totalconnections>0) {
                            $value['totalconnections'] = $totalconnections;
                        } else {
                            $value['totalconnections'] = 0;
                        }
                    } else {
                        $value['totalconnections'] = 0;
                    }

                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $country = isset($userinfo['country']) ? $userinfo['country'] : ''; 
                    $value['country'] = $country;
                    $city = isset($userinfo['city']) ? $userinfo['city'] : ''; 
                    $value['city'] = $city;
                    $thumbnail = Yii::$app->GenCls->getimage($user_id,'thumb');
                    $value['profile'] = $thumbnail;

                    // get personal info...
                    if($user_id != '') {
                        $pInfo = Personalinfo::find()->select(['language'])->where(['user_id' => $user_id])->asarray()->one();
                        if(!empty($pInfo)) {
                            $pInfoLanguage = $pInfo['language'];
                            $value['language'] = $pInfoLanguage;
                        }
                    }
                }  
                $newuserdata[] = $value;             
            }
        }
        return json_encode($newuserdata);
        exit;
    }

    public function recentLocaldriverPosts($user_id='') {		
		$userdata = LocaldriverPost::find()->Where(['not','flagger', "yes"])->orderBy('_id DESC')->asarray()->all();
        $newuserdata = [];
        if(!empty($userdata)) {
            $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
            
            // check event is save or not...
            $savedata = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();

            foreach ($userdata as $key => $value) {
                $postUId = $value['user_id'];
                $postId = (string)$value['_id'];
                $value['is_saved'] = false;
                $value['language'] = '';

                // check this Event user or event is blocked or not or event is event save .
                if($user_id != '') {
                    if(!empty($ssInfo)) {
                        // check user is blocked or not
                        $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                        if($isgenblocked) {
                            $isgenblocked = explode(",", $isgenblocked);
                            if(!empty($isgenblocked)) {
                                if(in_array($postUId, $isgenblocked)) { 
                                    continue;
                                }
                            }
                        }

                        if(isset($ssInfo['localdriver_users_blocked']) && isset($ssInfo['localdriver_users_blocked']) != '') {
                            $isblocked = $ssInfo['localdriver_users_blocked'];
                            $isblocked = explode(",", $isblocked);
                            if(!empty($isblocked)) {
                                if(in_array($postUId, $isblocked)) {
                                    continue;
                                }
                            }
                        }

                        // check event is block or delete or not.
                        if(isset($ssInfo['localdriver_posts_delete']) && isset($ssInfo['localdriver_posts_delete']) != '') {
                            $isDeleted = $ssInfo['localdriver_posts_delete'];
                            $isDeleted = explode(",", $isDeleted);
                            if(!empty($isDeleted)) {
                                if(in_array($postId, $isDeleted)) {
                                    continue;
                                }
                            }
                        }           
                    }

                    if(!empty($savedata)) {
                        $isSaved = isset($savedata['localdriver_save_posts']) ? $savedata['localdriver_save_posts'] : '';
                        if($isSaved) {
                            $isSaved = explode(",", $isSaved);
                            if(!empty($isSaved)) {
                                if(in_array($postId, $isSaved)) {
                                    $value['is_saved'] = true;
                                }
                            }
                        }
                    }

                    // check post is receive my invitation or not..
                    $is_invited = LocaldriverPostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->asarray()->one();
                    if(!empty($is_invited)) {
                        $value['is_invited'] = true;
                        $value['invitedInfo'] = $is_invited;
                    }
                }

                // get total count invitations............
                $count = LocaldriverPostInvite::find()->where(['post_id' => $postId])->count();
                $value['totalinvited'] = $count;

                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $postUId])->asarray()->one();
                if(!empty($userinfo))
                {   
                    if($user_id != '') {
                        $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                        if($totalconnections>0) {
                            $value['totalconnections'] = $totalconnections;
                        } else {
                            $value['totalconnections'] = 0;
                        }
                    } else {
                        $value['totalconnections'] = 0;
                    }

                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $country = isset($userinfo['country']) ? $userinfo['country'] : ''; 
                    $value['country'] = $country;
                    $city = isset($userinfo['city']) ? $userinfo['city'] : ''; 
                    $value['city'] = $city;
                    $thumbnail = Yii::$app->GenCls->getimage($postUId, 'thumb');
                    $value['profile'] = $thumbnail;

                    // get personal info...
                    if($user_id != '') {
                        $pInfo = Personalinfo::find()->select(['language'])->where(['user_id' => $user_id])->asarray()->one();
                        if(!empty($pInfo)) {
                            $pInfoLanguage = $pInfo['language'];
                            $value['language'] = $pInfoLanguage;
                        }
                    }
                } else {
                    continue;
                }
                $newuserdata[] = $value;             
            }
        }
        return $newuserdata;
        exit;
    }

    public function selectedrecord($id, $address, $user_id='') {
        $newuserdata = [];
        
        if('all' == strtolower(trim($id))) {
            $filterids = ArrayHelper::map(UserForm::find()->where(['like', 'city', $address])->asarray()->all(), function($data) { return (string)$data['_id'];}, '1');
            $filterids = array_keys($filterids);
            $userdata = LocaldriverPost::find()->where(['in','user_id', $filterids])->andWhere(['not','flagger', "yes"])->orderBy('_id DESC')->asarray()->all();
        } else {
            $userdata = LocaldriverPost::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->orderBy('_id DESC')->asarray()->all();
        }
                
        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {

                $postUId = $value['user_id'];
                $postId = (string)$value['_id'];
                $value['is_saved'] = false;
                $value['language'] = '';

                // check this Event user or event is blocked or not or event is event save .
                if($user_id != '') {
                    $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                    if(!empty($ssInfo)) {
                        // check user is blocked or not
                        $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                        if($isgenblocked) {
                            $isgenblocked = explode(",", $isgenblocked);
                            if(!empty($isgenblocked)) {
                                if(in_array($postUId, $isgenblocked)) { 
                                    continue;
                                }
                            }
                        }

                        if(isset($ssInfo['localdriver_users_blocked']) && isset($ssInfo['localdriver_users_blocked']) != '') {
                            $isblocked = $ssInfo['localdriver_users_blocked'];
                            $isblocked = explode(",", $isblocked);
                            if(!empty($isblocked)) {
                                if(in_array($postUId, $isblocked)) {
                                    continue;
                                }
                            }
                        }

                        // check event is block or delete or not.
                        if(isset($ssInfo['localdriver_posts_delete']) && isset($ssInfo['localdriver_posts_delete']) != '') {
                            $isDeleted = $ssInfo['localdriver_posts_delete'];
                            $isDeleted = explode(",", $isDeleted);
                            if(!empty($isDeleted)) {
                                if(in_array($postId, $isDeleted)) {
                                    continue;
                                }
                            }
                        }           

                        // check event is save or not...
                        if(isset($ssInfo['localdriver_posts_save']) && isset($ssInfo['localdriver_posts_save']) != '') {
                            $isSaved = $ssInfo['localdriver_posts_save'];
                            $isSaved = explode(",", $isSaved);
                            if(!empty($isSaved)) {
                                if(in_array($postId, $isSaved)) {
                                    $value['is_saved'] = true;
                                }
                            }
                        }
                    }

                    // check post is receive my invitation or not..
                    $is_invited = LocaldriverPostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->asarray()->one();
                    if(!empty($is_invited)) {
                        $value['is_invited'] = true;
                        $value['invitedInfo'] = $is_invited;
                    }
                }

                // get total count invitations............
                $count = LocaldriverPostInvite::find()->where(['post_id' => $postId])->count();
                $value['totalinvited'] = $count;

                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $postUId])->asarray()->one();
                if(!empty($userinfo))
                {   
                    if($user_id != '') {
                        $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                        if($totalconnections>0) {
                            $value['totalconnections'] = $totalconnections;
                        } else {
                            $value['totalconnections'] = 0;
                        }
                    } else {
                        $value['totalconnections'] = 0;
                    }

                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $country = isset($userinfo['country']) ? $userinfo['country'] : ''; 
                    $value['country'] = $country;
                    $city = isset($userinfo['city']) ? $userinfo['city'] : ''; 
                    $value['city'] = $city;
                    $thumbnail = Yii::$app->GenCls->getimage($postUId, 'thumb');
                    $value['profile'] = $thumbnail;

                    // get personal info...
                    if($user_id != '') {
                        $pInfo = Personalinfo::find()->select(['language'])->where(['user_id' => $user_id])->asarray()->one();
                        if(!empty($pInfo)) {
                            $pInfoLanguage = $pInfo['language'];
                            $value['language'] = $pInfoLanguage;
                        }
                    }
                } else {
                    continue;
                }
                $newuserdata[] = $value;             
            }
        }

        return json_encode($newuserdata, true);
        exit;
    }

    public function recentLocaldriverPostsnew($user_id='', $address, $start, $limit) {
        $filterids = ArrayHelper::map(UserForm::find()->where(['like', 'city', $address])->asarray()->all(), function($data) { return (string)$data['_id'];}, '1');
        $filterids = array_keys($filterids);
        $userdata = LocaldriverPost::find()->where(['in','user_id', $filterids])->andWhere(['not','flagger', "yes"])->orderBy('_id DESC')->limit($limit)->offset($start)->asarray()->all();
        $newuserdata = [];
        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $postUId = $value['user_id'];
                $postId = (string)$value['_id'];
                $value['is_saved'] = false;
                $value['language'] = '';

                // check this Event user or event is blocked or not or event is event save .
                if($user_id != '') {
                    $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                    if(!empty($ssInfo)) {
                        // check user is blocked or not
                        $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                        if($isgenblocked) {
                            $isgenblocked = explode(",", $isgenblocked);
                            if(!empty($isgenblocked)) {
                                if(in_array($postUId, $isgenblocked)) { 
                                    continue;
                                }
                            }
                        }

                        if(isset($ssInfo['localdriver_users_blocked']) && isset($ssInfo['localdriver_users_blocked']) != '') {
                            $isblocked = $ssInfo['localdriver_users_blocked'];
                            $isblocked = explode(",", $isblocked);
                            if(!empty($isblocked)) {
                                if(in_array($postUId, $isblocked)) {
                                    continue;
                                }
                            }
                        }

                        // check event is block or delete or not.
                        if(isset($ssInfo['localdriver_posts_delete']) && isset($ssInfo['localdriver_posts_delete']) != '') {
                            $isDeleted = $ssInfo['localdriver_posts_delete'];
                            $isDeleted = explode(",", $isDeleted);
                            if(!empty($isDeleted)) {
                                if(in_array($postId, $isDeleted)) {
                                    continue;
                                }
                            }
                        }           

                        // check event is save or not...
                        if(isset($ssInfo['localdriver_posts_save']) && isset($ssInfo['localdriver_posts_save']) != '') {
                            $isSaved = $ssInfo['localdriver_posts_save'];
                            $isSaved = explode(",", $isSaved);
                            if(!empty($isSaved)) {
                                if(in_array($postId, $isSaved)) {
                                    $value['is_saved'] = true;
                                }
                            }
                        }
                    }

                    // check post is receive my invitation or not..
                    $is_invited = LocaldriverPostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->asarray()->one();
                    if(!empty($is_invited)) {
                        $value['is_invited'] = true;
                        $value['invitedInfo'] = $is_invited;
                    }
                }

                // get total count invitations............
                $count = LocaldriverPostInvite::find()->where(['post_id' => $postId])->count();
                $value['totalinvited'] = $count;

                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $postUId])->asarray()->one();
                if(!empty($userinfo))
                {   
                    if($user_id != '') {
                        $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                        if($totalconnections>0) {
                            $value['totalconnections'] = $totalconnections;
                        } else {
                            $value['totalconnections'] = 0;
                        }
                    } else {
                        $value['totalconnections'] = 0;
                    }

                    $fullname = $userinfo['fullname']; 
                    $value['fullname'] = $fullname;
                    $country = isset($userinfo['country']) ? $userinfo['country'] : ''; 
                    $value['country'] = $country;
                    $city = isset($userinfo['city']) ? $userinfo['city'] : ''; 
                    $value['city'] = $city;
                    $thumbnail = Travelbuddytripinvite::getimage($postUId, 'thumb');
                    $value['profile'] = $thumbnail;

                    // get personal info...
                    if($user_id != '') {
                        $pInfo = Personalinfo::find()->where(['user_id' => $user_id])->asarray()->one();
                        if(!empty($pInfo)) {
                            $pInfoLanguage = isset($pInfo['language']) ? $pInfo['language'] : '';
                            $value['language'] = $pInfoLanguage;
                        }
                    }
                } else {
                    continue;
                }
                $newuserdata[] = $value;             
            }
        }
        return json_encode($newuserdata);
        exit;
    }

    public function savepost($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $saveposts = isset($getInfo['localdriver_posts_save']) ? $getInfo['localdriver_posts_save'] : '';
                if(!empty($saveposts)) {
                    $saveposts = explode(",", $saveposts);
                    if(!in_array($id, $saveposts)) {
                        $saveposts[] = $id;
                    }
                    $saveposts = implode(",", $saveposts);
                } else {
                    $saveposts = $id;
                }

                $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                $SS->localdriver_posts_save = $saveposts;
                $SS->save();
                return true;
                exit;
            } else {
                // first insert user info for security setting...
                $SS = new SecuritySetting();
                $SS->user_id = $user_id;
                $SS->localdriver_posts_save = $id;
                $SS->save();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }

    public function deletepost($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $deleteposts = isset($getInfo['localdriver_posts_delete']) ? $getInfo['localdriver_posts_delete'] : '';
                if(!empty($deleteposts)) {
                    $deleteposts = explode(",", $deleteposts);
                    if(!in_array($id, $deleteposts)) {
                        $deleteposts[] = $id;
                    }
                    $deleteposts = implode(",", $deleteposts);
                } else {
                    $deleteposts = $id;
                }

                $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                $SS->localdriver_posts_delete = $deleteposts;
                $SS->save();
                return true;
                exit;
            } else {
                // first insert user info for security setting...
                $SS = new SecuritySetting();
                $SS->user_id = $user_id;
                $SS->localdriver_posts_delete = $id;
                $SS->save();
                return true;
                exit;
            }
        }
        return false;
        exit;
    }
    
     public function blockuserpost($id, $user_id)
    {
        if($id) {
            // get user id from event then block that users..
            $uid = LocaldriverPost::find()->select(['user_id'])->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->asarray()->one();
            if(!empty($uid)) {
                $uid = $uid['user_id'];
                $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
                if(!empty($getInfo)) {
                    $bockusr = isset($getInfo['localdriver_users_blocked']) ? $getInfo['localdriver_users_blocked'] : '';
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
                    $SS->localdriver_users_blocked = $bockusr;
                    $SS->save();
                    return true;
                    exit;
                } else {
                    // first insert user info for security setting...
                    $SS = new SecuritySetting();
                    $SS->user_id = $user_id;
                    $SS->localdriver_users_blocked = $uid;
                    $SS->save();
                    return true;
                    exit;
                }
            }
        }
        return false;
        exit;
    }

    public function savedpostlist($user_id) {
        if($user_id) {
            // check event is save or not...
            $savedata = TravelSavePost::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($savedata)) {
                if(isset($savedata['localdriver_save_posts']) && $savedata['localdriver_save_posts'] != '') {
                    $savedIds = explode(",", $savedata['localdriver_save_posts']);
                    if(!empty($savedata)) {
                        $userdata = LocaldriverPost::find()->where(['in', (string)'_id', $savedata])->andWhere(['not','flagger', "yes"])->orderBy('_id DESC')->asarray()->all();
                        $newuserdata = [];
                        if(!empty($userdata)) {
                            
                            $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                            
                            foreach ($userdata as $key => $value) {
                                $postUId = $value['user_id'];
                                $postId = (string)$value['_id'];
                                $value['language'] = '';
                                $value['is_saved'] = true;
                                // check this Event user or event is blocked or not or event is event save .
                                if(!empty($ssInfo)) {
                                    // check user is blocked or not
                                    $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                                    if($isgenblocked) {
                                        $isgenblocked = explode(",", $isgenblocked);
                                        if(!empty($isgenblocked)) {
                                            if(in_array($postUId, $isgenblocked)) { 
                                                continue;
                                            }
                                        }
                                    }

                                    if(isset($ssInfo['localdriver_users_blocked']) && isset($ssInfo['localdriver_users_blocked']) != '') {
                                        $isblocked = $ssInfo['localdriver_users_blocked'];
                                        $isblocked = explode(",", $isblocked);
                                        if(!empty($isblocked)) {
                                            if(in_array($postUId, $isblocked)) {
                                                continue;
                                            }
                                        }
                                    }

                                    // check event is block or delete or not.
                                    if(isset($ssInfo['localdriver_posts_delete']) && isset($ssInfo['localdriver_posts_delete']) != '') {
                                        $isDeleted = $ssInfo['localdriver_posts_delete'];
                                        $isDeleted = explode(",", $isDeleted);
                                        if(!empty($isDeleted)) {
                                            if(in_array($postId, $isDeleted)) {
                                                continue;
                                            }
                                        }
                                    }           
                                }

                                // check post is receive my invitation or not..
                                $is_invited = LocaldriverPostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->asarray()->one();
                                if(!empty($is_invited)) {
                                    $value['is_invited'] = true;
                                    $value['invitedInfo'] = $is_invited;
                                }

                                                 // get total count invitations............
                                $count = LocaldriverPostInvite::find()->where(['post_id' => $postId])->count();
                                $value['totalinvited'] = $count;

                                $userinfo = UserForm::find()->select(['fullname','thumbnail', 'gender', 'photo', 'country', 'city'])->where([(string)'_id' => $postUId])->asarray()->one();
                                if(!empty($userinfo))
                                {   

                                    $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
                                    if($totalconnections>0) {
                                        $value['totalconnections'] = $totalconnections;
                                    } else {
                                        $value['totalconnections'] = 0;
                                    }
                                    $fullname = $userinfo['fullname']; 
                                    $value['fullname'] = $fullname;
                                    $country = isset($userinfo['country']) ? $userinfo['country'] : ''; 
                                    $value['country'] = $country;
                                    $city = isset($userinfo['city']) ? $userinfo['city'] : ''; 
                                    $value['city'] = $city;
                                    $thumbnail = Yii::$app->GenCls->getimage($postUId, 'thumb');
                                    $value['profile'] = $thumbnail;

                                    // get personal info...

                                    $pInfo = Personalinfo::find()->select(['language'])->where(['user_id' => $user_id])->asarray()->one();
                                    if(!empty($pInfo)) {
                                        $pInfoLanguage = $pInfo['language'];
                                        $value['language'] = $pInfoLanguage;
                                        
                                    }
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

    public function unsavepost($id, $user_id)
    {
        if($id) {
            $getInfo = SecuritySetting::find()->where(['user_id' => (string)$user_id])->asarray()->one();
            if(!empty($getInfo)) {
                $saveposts = isset($getInfo['localdriver_posts_save']) ? $getInfo['localdriver_posts_save'] : '';
                if(!empty($saveposts)) {
                    $saveposts = explode(",", $saveposts);

                    if(in_array($id, $saveposts)) {
                        $key = array_search($id, $saveposts);
                        if($key >= 0) {
                            unset($saveposts[$key]);
                        }
                    }
                    $saveposts = implode(",", $saveposts);
                    $SS = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $SS->localdriver_posts_save = $saveposts;
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
        $localdriverInfo = isset($post['localdriverInfo']) ? $post['localdriverInfo'] : array();
        $age = $post['age'];
        $hasPhoto = '';
        $verified = '';
        $minValue = '';
        $maxValue = '';

        if(!empty($localdriverInfo)) {
            if(in_array('hasphoto', $localdriverInfo)) { $hasPhoto = 'Y'; }
            if(in_array('verified', $localdriverInfo)) { $verified = 'Y'; }
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

        $userdata = LocaldriverPost::find()->Where(['not','flagger', "yes"])->orderBy('_id DESC')->asarray()->all();
        $newuserdata = [];
          
        $assetsPath = '../../vendor/bower/travel/images/';

        if(!empty($userdata)) {
            foreach ($userdata as $key => $value) {
                $postUId = $value['user_id'];
                $postId = (string)$value['_id'];
                $value['is_saved'] = false;
                $value['language'] = '';

                $ssInfo = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
                if(!empty($ssInfo)) {
                    // check user is blocked or not
                    $isgenblocked = isset($ssInfo['blocked_list']) ? $ssInfo['blocked_list'] : '';
                    if($isgenblocked) {
                        $isgenblocked = explode(",", $isgenblocked);
                        if(!empty($isgenblocked)) {
                            if(in_array($postUId, $isgenblocked)) { 
                                continue;
                            }
                        }
                    }
                    
                    $isblocked = isset($ssInfo['localdriver_users_blocked']) ? $ssInfo['localdriver_users_blocked'] : '';
                    if($isblocked) {
                        $isblocked = explode(",", $isblocked);
                        if(!empty($isblocked)) {
                            if(in_array($postUId, $isblocked)) { 
                                continue;
                            }
                        }
                    }

                    // check event is block or delete or not.
                    $isDeleted = isset($ssInfo['localdriver_posts_delete']) ? $ssInfo['localdriver_posts_delete'] : '';
                    if($isDeleted) {
                        $isDeleted = explode(",", $isDeleted);
                        if(!empty($isDeleted)) {
                            if(in_array($postId, $isDeleted)) {
                                continue;
                            }
                        }
                    }           

                    // check event is save or not...
                    $isSaved = isset($ssInfo['localdriver_posts_save']) ? $ssInfo['localdriver_posts_save'] : '';
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

                 // get total count invitations............
                $count = LocaldriverPostInvite::find()->where(['post_id' => $postId])->count();
                $value['totalinvited'] = $count;
                
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
                $city = isset($userinfo['city']) ? $userinfo['city'] : '';
                $country = isset($userinfo['country']) ? $userinfo['country'] : '';
                if(!empty($recommandDest)) {
                    $existDest = strtolower($city .','.$country);
                    if($existDest != '') {
                        $existDest = str_replace(" ", ",", $existDest);
                        $existDest = str_replace(",,", ",", $existDest);
                        $existDest = str_replace(" ", ",", $existDest);
                        $existDest = explode(",", strtolower($existDest));
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
                    $generateLangBulk = array();
                    $personalinfo = Personalinfo::find()->where(['user_id' => $user_id])->asarray()->one();
                    if(!empty($personalinfo) && isset($personalinfo['language'])) {
                        $generateLangBulk = $personalinfo['language'];
                        $generateLangBulk = array_map('trim', explode(',', $generateLangBulk));
                    }

                    $checklanguage = count(array_intersect($generateLangBulk, $language));
                    if($checklanguage <= 0) {
                        continue;
                    }
                }                

                // check post is receive my invitation or not..
                $is_invited = LocaldriverPostInvite::find()->where(['user_id' => $user_id, 'post_id' => $postId])->asarray()->one();
                if(!empty($is_invited)) {
                    $value['is_invited'] = true;
                    $value['invitedInfo'] = $is_invited;
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

                    
                    $pInfo = Personalinfo::find()->where(['user_id' => $user_id])->asarray()->one();
                    if(!empty($pInfo)) {
                        $pInfoLanguage = isset($pInfo['language']) ? $pInfo['language'] : '';
                        $value['language'] = $pInfoLanguage;
                    }
                    $newuserdata[] = $value;     
                }   
            }

            return json_encode(array_values($newuserdata));
            exit;
        }
        return json_encode($newuserdata);
        exit;
    }

    
	public function getTotalguide()
	{
		return LocaldriverPost::find()->Where(['not','flagger', "yes"])->count();
	}

    public function deletemyevent($id, $user_id) {
        if($id) {
            $data = LocaldriverPost::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->asarray()->one();
            if(!empty($data)) {

                $data = LocaldriverPostInvite::find()->where(['post_id' => $id])->asarray()->all();
                if(!empty($data)) {
                    foreach ($data as $key => $sdata) {
                        $getInviteId = (string)$sdata["_id"];
                        LocaldriverPostInviteMsgs::deleteAll(['postinvite_id' => $getInviteId]);
                    }
                }


                LocaldriverPostInvite::deleteAll(['post_id' => $id]);

                $data = LocaldriverPost::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
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
	
	public function getLocalGuid()
	{
		$record =  ArrayHelper::map(LocaldriverPost::find()->Where(['not','flagger', "yes"])->orderBy(['created_at'=>SORT_DESC])->all(), function($data) { return $data['user_id'];}, function($data) { return $data;});

        $vip_array = array();
        $verify_array = array();
        $normal_array = array();
        foreach($record as $record2)
        {
            $data = LoginForm::find()->select(['fullname', 'photo', 'thumbnail', 'gender', 'city', 'country', 'vip_flag'])->Where([(string)'_id' => (string)$record2['user_id']])->orderBy(['created_date'=>SORT_DESC])->asarray()->one();
            if(!empty($data)) {
                $verify = Verify::find()->where(['user_id' => (string) $record2['user_id'],'status' => "1"])->one();
                $data['guide_id'] = (string)$record2['_id'];
                if(isset($data['vip_flag']) && $data['vip_flag'] != '0' && $data['vip_flag'] != '') {
                    $vip_array[] = $data;
                } else if(!empty($verify)) {
                    $verify_array[] = $data;
                } else {
                    $normal_array[] = $data;
                }
            }
        }

        $data = array_merge($vip_array, $verify_array, $normal_array);
        $data = array_slice($data, 0, 3);

		return json_encode($data, true); 
	}

    public function gettrip($id, $user_id)
    { 
        $userdata = LocaldriverPost::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->asarray()->one();
        return json_encode($userdata, true);
    }
}