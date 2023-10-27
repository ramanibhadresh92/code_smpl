<?php 
namespace frontend\models;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use yii\mongodb\ActiveRecord;

class NotificationSetting extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function collectionName()
    {
        return 'notification_setting';
    }

     public function attributes()
    {
        return ['_id','user_id','connect_activity','email_on_account_issues','member_activity',
                'connect_activity_on_user_post','non_connect_activity','connect_request',
                'e_card','member_invite_on_meeting','question_activity','credit_activity',
                'sound_on_notification','sound_on_message','created_date','modified_date',
				'ip','created_by','modified_by','is_deleted','is_like','is_comment','is_share',
				'follow_collection','share_collection','add_trip_by_connect','invited_for_trip'];
    }
    
    public function getPosts()
    {
        return $this->hasMany(PostForm::className(), ['post_user_id' => '_id']);
    }
    
    public function rules()
    {
        return [
                [['connect_activity'], 'required'],            
        ];
	}
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();     
        return $scenarios;
    }
    
    public function notification()
    {
		$session = Yii::$app->session;
		$email = $session->get('email'); 
		$user_id = (string) $session->get('user_id');
       
		$notification = NotificationSetting::find()->where(['user_id' => $user_id])->one();

		 if(!empty($notification))
		 {
			$notification->connect_activity = $_POST['connect_activity'];
			$notification->email_on_account_issues = $_POST['email_on_account_issues'];
			$notification->connect_activity_on_user_post = $_POST['connect_activity_on_user_post'];
			$notification->non_connect_activity = $_POST['non_connect_activity'];
			$notification->connect_request = $_POST['connect_request'];
			$notification->e_card = $_POST['e_card'];
			$notification->credit_activity = $_POST['credit_activity'];
			$notification->sound_on_notification = $_POST['sound_on_notification'];
			$notification->sound_on_message = $_POST['sound_on_message'];
			$notification->is_like = $_POST['like_post'];
			$notification->is_comment = $_POST['comment_post'];
			$notification->is_share = $_POST['share_post'];
			$notification->follow_collection = $_POST['follow_collection'];
			$notification->share_collection = $_POST['share_collection'];
			$notification->add_trip_by_connect = $_POST['add_trip_by_connect'];
			$notification->invited_for_trip = $_POST['invited_for_trip'];
			$notification->update();
			
			return 1;
		
		 }
		 else
		 {
			$notification = new NotificationSetting();                  
			$notification->user_id = $user_id;  
			$notification->connect_activity = $_POST['connect_activity'];
			$notification->email_on_account_issues = $_POST['email_on_account_issues'];
			$notification->connect_activity_on_user_post = $_POST['connect_activity_on_user_post'];
			$notification->non_connect_activity = $_POST['non_connect_activity'];
			$notification->connect_request = $_POST['connect_request'];
			$notification->e_card = $_POST['e_card'];
			$notification->credit_activity = $_POST['credit_activity'];
			$notification->sound_on_notification = $_POST['sound_on_notification'];
			$notification->sound_on_message = $_POST['sound_on_message'];
			$notification->is_like = $_POST['like_post'];
			$notification->is_comment = $_POST['comment_post'];
			$notification->is_share = $_POST['share_post'];
			$notification->follow_collection = $_POST['follow_collection'];
			$notification->share_collection = $_POST['share_collection'];
			$notification->add_trip_by_connect = $_POST['add_trip_by_connect'];
			$notification->invited_for_trip = $_POST['invited_for_trip'];
			$notification->insert();
			return 2;
		   
		 }
    }
    
    
	public function notification2()
    {
        $session = Yii::$app->session;
		$email =  base64_decode(strrev($_GET['email']));
        $user = LoginForm::find()->where(['email' => $email])->one();
        $user_id = (string)$user->_id;
        
        $notification = NotificationSetting::find()->where(['user_id' => $user_id])->one();
        
       if(!empty($notification)) {
           $notification->connect_activity = 'Yes';
           $notification->email_on_account_issues = 'Yes';
           $notification->connect_activity_on_user_post = 'Yes';
           $notification->non_connect_activity = 'No';
           $notification->connect_request = 'Yes';
           $notification->e_card = 'Yes';
           $notification->credit_activity = 'Yes';
           $notification->sound_on_notification = 'Yes';
           $notification->sound_on_message = 'Yes';
		   $notification->is_like = 'Yes';
		   $notification->is_comment = 'Yes';
		   $notification->is_share = 'Yes';
		   $notification->follow_collection = 'Yes';
		   $notification->share_collection = 'Yes';
		   $notification->add_trip_by_connect = 'Yes';
		   $notification->invited_for_trip = 'Yes';
		   $notification->update();
		}
		else{
           $notification = new NotificationSetting();  
           $notification->user_id = $user_id;
           $notification->connect_activity = 'Yes';
           $notification->email_on_account_issues = 'Yes';
           $notification->connect_activity_on_user_post = 'Yes';
           $notification->non_connect_activity = 'No';
           $notification->connect_request = 'Yes';
           $notification->e_card = 'Yes';
           $notification->credit_activity = 'Yes';
           $notification->sound_on_notification = 'Yes';
           $notification->sound_on_message = 'No';
		   $notification->is_like = 'Yes';
		   $notification->is_comment = 'Yes';
		   $notification->is_share = 'Yes';
		   $notification->follow_collection = 'Yes';
		   $notification->share_collection = 'Yes';
		   $notification->add_trip_by_connect = 'Yes';
		   $notification->invited_for_trip = 'Yes';
		   $notification->insert();
       }
          return 1; 
    }
	
	public function notification3($email)
    {
        $session = Yii::$app->session;
		$user = LoginForm::find()->where(['email' => $email])->one();
        
        $user_id = (string)$user->_id;
        
        $notification = NotificationSetting::find()->where(['user_id' => $user_id])->one();
        
       if(!empty($notification))
	   {
		   $notification->connect_activity = 'Yes';
           $notification->email_on_account_issues = 'Yes';
           $notification->connect_activity_on_user_post = 'Yes';
           $notification->non_connect_activity = 'No';
           $notification->connect_request = 'Yes';
           $notification->e_card = 'Yes';
           $notification->credit_activity = 'Yes';
           $notification->sound_on_notification = 'Yes';
           $notification->sound_on_message = 'Yes';
		   $notification->is_like = 'Yes';
		   $notification->is_comment = 'Yes';
		   $notification->is_share = 'Yes';
		   $notification->follow_collection = 'Yes';
		   $notification->share_collection = 'Yes';
		   $notification->add_trip_by_connect = 'Yes';
		   $notification->invited_for_trip = 'Yes';
		   $notification->update();
		}
       else
	   {
			$notification = new NotificationSetting();  
			$notification->user_id = $user_id;
			$notification->connect_activity = 'Yes';
			$notification->email_on_account_issues = 'Yes';
			$notification->connect_activity_on_user_post = 'Yes';
			$notification->non_connect_activity = 'No';
			$notification->connect_request = 'Yes';
			$notification->e_card = 'Yes';
			$notification->credit_activity = 'Yes';
			$notification->sound_on_notification = 'Yes';
			$notification->sound_on_message = 'No';
			$notification->is_like = 'Yes';
		    $notification->is_comment = 'Yes';
		    $notification->is_share = 'Yes';
		    $notification->follow_collection = 'Yes';
		    $notification->share_collection = 'Yes';
		    $notification->add_trip_by_connect = 'Yes';
		    $notification->invited_for_trip = 'Yes';
		    $notification->insert();
       }
          return 1; 
    }
    
    protected function getUser()
    {
        if ($this->_user === null) 
		{
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
    
    public static function findIdentity($id)
    {
        return static::findOne(['email' => $id]);
    }
	
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }
	
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function getLastInsertedRecord($id)
    {
        return NotificationSetting::find()->select(['about'])->where(['user_id' => $id])->asarray()->one();
    }
}
