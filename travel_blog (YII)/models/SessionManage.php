<?php 
namespace frontend\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use yii\mongodb\ActiveRecord;

class SessionManage extends ActiveRecord implements IdentityInterface
{ 
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    
    public static function collectionName()
    {
        return 'user';
    }

    public function attributes()
    {
        return ['_id', 'fb_id', 'username', 'fname','lname','fullname', 'password', 'con_password', 'pwd_changed_date', 'email','alternate_email','photo','thumbnail','cover_photo', 'birth_date','gender','created_date','updated_date','created_at','updated_at','status','phone','isd_code','country','country_code','city','captcha','member_type','last_login_time','forgotcode','forgotpassstatus','lat','long','login_from_ip'];
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
                [['email', 'password'], 'required', 'on' => 'login'],
                 [['fname', 'lname','fullname', 'email', 'password', 'con_password', 'birth_date', 'gender','created_date','updated_date','status'], 'required', 'on' => 'signup'],
                [['con_password'], 'compare', 'compareAttribute' => 'password', 'on' => 'signup'],
                [['email'], 'required', 'on' => 'forgot'],
                [['fname', 'lname', 'email', 'password', 'birth_date', 'gender','phone','country','city','captcha'], 'required', 'on' => 'profile'],       
                [['photo'], 'image', 'extensions' => ['png', 'jpg', 'gif','jpeg'], 'on' => 'profile_picture'],
                ['captcha', 'captcha', 'on' => 'profile'],
        ];
    }
}
