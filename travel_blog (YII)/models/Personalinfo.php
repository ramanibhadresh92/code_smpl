<?php 
namespace frontend\models;
use Yii;
use yii\base\NotSupportedException;
use yii\mongodb\ActiveRecord;
use yii\web\IdentityInterface;


class Personalinfo extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function collectionName()
    {
        return 'personal_info';
    }

	public function attributes()
    {
        return ['_id','personalinfo_id', 'user_id','about','education','occupation','interests','language',
                'is_host','host_services','gender','religion','political_view','mission','hometown','amazing_things','visited_countries','lived_countries','created_date','modified_date',
                'ip','created_by','modified_by','is_deleted'];
    }
    
    public function getPosts()
    {
        return $this->hasMany(PostForm::className(), ['post_user_id' => '_id']);
    }
    
    public function rules()
    {
        return [[['about'], 'required', 'on' => 'personal_info'],
                 [['hometown','religion','political_view'], 'required', 'on' => 'basicinfo'],];
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
	
    public function getCity($connect_user_id)
    {
        return Personalinfo::find()->where(['user_id' => "$connect_user_id"])->one();
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
        return Personalinfo::find()->select(['about'])->where(['user_id' => $id])->asarray()->one();
    }
    
    public static function findByUserid($userid){
        $d = Personalinfo::find()->where(['user_id' => $userid])->one();
    }
    
    public function getPersonalInfo($userid)
    {   
        $d = Personalinfo::find()->where(['user_id' => $userid])->one();
        return $d;
    }
    
}
