<?php 
namespace frontend\models;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use yii\mongodb\ActiveRecord;

class UserSetting extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function collectionName()
    {
        return 'user_setting';
    }

     public function attributes()
    {
        return ['_id', 'user_id','email_access','alternate_email_access','birth_date_access',
                'language_access','religion_access','political_view_access',
                'contact_me_access','gender_access','browser_type','user_theme','created_date','modified_date','ip','created_by',
                'modified_by','is_deleted','mobile_access'];
    }
    
    public function getPosts()
    {
        return $this->hasMany(PostForm::className(), ['post_user_id' => '_id']);
    }
	
    public function rules()
    {
        return [
            
                [['email_access', 'alternate_email_access','birth_date_access','language_access',
                    'religion_access','political_view_access','gender_access'], 'required', 'on' => 'basicinfo'],
			];
	}
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();     
        return $scenarios;
    }
	
    protected function getUser()
    {
        if ($this->_user === null) {
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
    
}
