<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\mongodb\ActiveRecord;
use frontend\models\Vip;

class UserForm extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user';
    }

    public function attributes()
    {
        return ['_id', 'fb_id', 'username', 'fname','lname','fullname', 'password', 'con_password', 'pwd_changed_date', 'email','alternate_email','photo','thumbnail','profile_picture','cover_photo', 'birth_date','gender','created_date','updated_date','created_at','updated_at','status','phone','isd_code','country','city','citylat','citylong','captcha','vip_flag','reference_user_id','member_type','last_login_time','forgotcode','forgotpassstatus','lat','long','login_from_ip','last_time','last_logout_time','point_total','birth_date_privacy', 'gender_privacy','user_status_sentence'];
    }
   
    public function getConnections()
    {
        return $this->hasMany(Connect::className(), ['from_id' => '_id']);
    }
    
    public function getSavedPosts()
    {
        return $this->hasMany(UserForm::className(), ['user_id' => '_id']);
    }
    
    public function getPosts()
    {
        return $this->hasMany(PostForm::className(), ['post_user_id' => '_id']);
    }
  
    public function getUserlikes()
    {
        return $this->hasMany(Like::className(), ['user_id' => '_id']);
    }
    
    public function getUsercomment()
    {
        return $this->hasMany(Comment::className(), ['user_id' => '_id']);
    }
    
	public function isUserExist($email)
    {
        return  UserForm::find()->where(['email' => $email])->one();
    }

    public function isUserExistBYFBID($fb_id)
    {
        return  UserForm::find()->where(['fb_id' => $fb_id])->one();
    }
  
    public function getLastInsertedRecord($email)
    {
        return UserForm::find()->select(['_id'])->where(['email' => $email])->asarray()->one();
    }

    public function getSelectedUsersData($uids)
    {
        $data =  UserForm::find()->where(['in',(string)'_id', $uids])->andWhere(['status'=>'1'])->asArray()->all();
        return $data;

    }

    public function getUserBasicWithINI($ids) {
        $data = UserForm::find()->select(['fname', 'lname'])->where(['in', '_id', $ids])->asArray()->all();
        $li = '';
        if(!empty($data)) {
            foreach ($data as $key => $singleData) {
                $id = (string)$singleData['_id'];
                $fname = $singleData['fname'];
                $lname = $singleData['lname'];
                $name = $fname .' '.$lname;
                $image = Yii::$app->GenCls->getimage($id,'thumb');

                $li .='<span><a href="/iaminjapan-code/frontend/web/index.php?r=userwall%2Findex&amp;id='.$id.'"><img title="'.$name.'" src="'.$image.'"></a></span>';
            }
        }

        return $li;
    }
     
    public function isUserExistByUid($user_id) {
    	$data = UserForm::find()->where([(string)'_id' => (string)$user_id])->asarray()->one();
    	if(!empty($data)) {
    		$status = (isset($data['status']) && $data['status'] == '1') ? '' : 'checkuserauthclassnv';
    		return $status;
    	} else {
    		return 'checkuserauthclassg';	
    	}
    }

    public function getUserName($id) {
        $data = UserForm::find()->select(['fullname'])->where([(string)'_id' => $id])->asarray()->one();

        if(!empty($data)) {
            $thumb = Yii::$app->GenCls->getimage($id,'thumb');
            $data['thumb'] = $thumb;
            $data['id'] = $id;
            
            return json_encode($data, true);
        }
    }

    public function getIsvip()
    {
        return $this->hasOne(Vip::className(), [(string)'user_id' => (string)'_id']);   
    }
    public function getlocation($user_id)
    {
        $data = UserForm::find()->select(['city', 'country'])->where([(string)'_id' => $user_id])->asarray()->one();  
        if(!empty($data)) {
            $address = trim($data['city'] .', '.$data['country']);
            $address = explode(",", $address);
            $address = array_filter($address);
            if(count($address) >1) {
                $first = reset($address);
                $last = end($address);
                $address = $first.', '.$last;
            } else {
                $address = implode(", ", $address);
            }
            return $address;
        }
        return 'London';
    }

    public function getUserNames($ids) {
        $data = UserForm::find()->select(['fullname'])->where(['in', (string)'_id', $ids])->asarray()->all();

        $result = array();
        if(!empty($data)) {
            foreach ($data as $key => $singleData) {
                $result[] = $singleData['fullname'];
            }
        }
        return json_encode($result, true);
    }

    public function stropheloginchk() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $result = array('status' => false);
        
        if(isset($user_id) && $user_id != '') {
            $data = UserForm::find()->where([(string)'_id' => $user_id])->asarray()->one();
            if(!empty($data)) {
                if(isset($data['status']) && $data['status'] == '1') {
                    $pwd = $data['password'];
                    $result['status'] = true;  
                    $result['id'] = $user_id;  
                    $result['pwd'] = $pwd;  
                } else {
                    $result['kaaran'] = 'IU';
                }
            } else {
                $result['kaaran'] = 'G';
            }
        } else {
            $result['kaaran'] = 'G';
        }

        return json_encode($result, true);
    }

    public function isJSON($string){
       return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    public function headerCustomArray($key) {
        $resultArray = array('getRDataClass' => '','getRDataLink' => '','getRDataOnClick' => '','getRDataMblMenBtn' => '','getRDataMblMenBtn2' => '','getRDatapageNameLabel' => '', 'commonhtml' => '<a href="javascript:void(0)" class="mbl-menuicon1 waves-effect waves-theme"><i class="mdi mdi-menu"></i></a>','chat_icon' => '../../vendor/bower/travel/images/chat-black.png');

        if(isset($key) && $key != '') {
            $customArray = array(
                'site/accountsettings' => 
                    array(
                        'class' => 'topicon',
                        'link' => 'javascript:void(0)',
                        'onclick' => "resetInnerPage('settings','show')",
                        'atag' => '<a href="javascript:void(0)" class="mbl-menuicon1 waves-effect waves-theme"><i class="mdi mdi-menu"></i></a>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'pageNameLabel' => 'Basic Information'
                    )
                ,'userwall/index' =>
                    array(
                        'class' => 'topicon',
                        'link' => 'javascript:void(0)',
                        'onclick' => "resetInnerPage('wall','show')",
                        'atag' => '<a href="javascript:void(0)" class="mbl-menuicon1 waves-effect waves-theme"><i class="mdi mdi-menu"></i></a>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'pageNameLabel' => 'Wall',
                    )
                ,'site/transfercredits' =>
                    array(
                        'link' => '?r=site/credits',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'site/creditshistory' =>
                    array(
                        'link' => '?r=site/credits',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'collection/detail' =>
                    array(
                        'link' => '?r=collection'     
                    )
                ,'ads/manage' =>
                    array(
                        'class' => 'topicon',
                        'link' => '?r=ads',
                        'onclick' => "resetInnerPage('wall','show')",
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'pageNameLabel' => 'Basic Information'
                    )
                ,'trip' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'camping' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'camping/detail' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'tours' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'homestay' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'homestay/detail' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'localdine' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'localdine/detail' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'site/restaurantlist' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'site/restaurantmap' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'site/hotellist' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'site/hotelmap' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'cityguide' =>
                    array(
                        'class' => 'topicon'
                    )
                ,'reviews' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'tripstory' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'blog' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'page/detail' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>'
                    )
                ,'site/credits' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'site/travnotifications' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'atag' => '<a href="javascript:void(0)" class="mbl-menuicon1 waves-effect waves-theme"><i class="mdi mdi-menu"></i></a>',
                    )
                ,'site/addvip' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'site/verifyme' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'site/billing' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'discussion' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'photostream' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'tips' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'questions' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'collections' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'locals' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'collections/details' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'blog/detail' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'travellers' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'localguide' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'localguide/detail' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'localdriver' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'page' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'localdriver/detail' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'ads' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'ads/create' =>
                    array(
                        'class' => 'topicon',
                        'link' => '?r=ads',
                        'onclick' => "resetInnerPage('wall','show')",
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'site/hotels' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                        'chat_icon' => '../../vendor/bower/travel/images/chat-white.png'
                    )
                ,'site/messages' =>
                    array(
                        'class' => 'topicon',
                        'mblmenbtn' => '<div class="mobile-menu"><a href="javascript:void(0)" class="waves-effect waves-theme"><i class="mdi mdi-menu"></i></a></div>'
                    )
                ,'flights' =>
                    array(
                        'chat_icon' => '../../vendor/bower/travel/images/chat-white.png'
                    )  
                ,'flights/details' =>
                    array(
                    )  
                ,'site/mainfeed' =>
                    array(
                        'class' => 'topicon',
                        'icon' => '<i class="mdi mdi-menu"></i>',
                    )      
            );

            if(array_key_exists($key, $customArray)) {            
                $resultArray['getRDataClass'] = isset($customArray[$key]['class']) ? $customArray[$key]['class'] : '';
                $resultArray['getRDataLink'] = isset($customArray[$key]['link']) ? $customArray[$key]['link'] : 'javascript:void(0)';
                $resultArray['getRDataOnClick'] = isset($customArray[$key]['onclick']) ? $customArray[$key]['onclick'] : '';
                $resultArray['getRDataMblMenBtn'] = isset($customArray[$key]['mblmenbtn']) ? $customArray[$key]['mblmenbtn'] : '';
                $resultArray['getRDataMblMenBtn2'] = isset($customArray[$key]['mblmenbtn2']) ? $customArray[$key]['mblmenbtn2'] : '';
                $resultArray['getRDatapageNameLabel'] = isset($customArray[$key]['pageNameLabel']) ? $customArray[$key]['pageNameLabel'] : ''; 
                $icon = isset($customArray[$key]['icon']) ? $customArray[$key]['icon'] : '<i class="mdi mdi-arrow-left"></i>'; 
                $resultArray['chat_icon'] = isset($customArray[$key]['chat_icon']) ? $customArray[$key]['chat_icon'] : '../../vendor/bower/travel/images/chat-black.png'; 
                $html1 = isset($customArray[$key]['atag']) ? $customArray[$key]['atag'] : '';

                /*if($key != 'site/messages' && $key != 'tips' && $key != 'localguide' && $key != 'localdriver') {
                    $resultArray['commonhtml'] = $html1."<div class='gotohome'><a href=".$resultArray['getRDataLink']." onclick=".$resultArray['getRDataOnClick'].">".$icon."</a></div>";
                }*/
            } 
        } else {
            $resultArray['commonhtml'] = "<div class='gotohome'><a href=''><i class='mdi mdi-arrow-left'></i></a></div>";
        }
        return $resultArray;
    }
} 