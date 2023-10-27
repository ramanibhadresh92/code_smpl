<?php 
namespace frontend\models;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\PostForm;
use backend\models\Googlekey;


class LoginForm extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function collectionName()
    {
        return 'user';
    }

	public function attributes()
    {
		return ['_id', 'fb_id', 'username', 'fname','lname','fullname', 'password', 'con_password', 'pwd_changed_date', 'email','alternate_email','photo','thumbnail','cover_photo', 'birth_date','gender','created_date','updated_date','created_at','updated_at','status','phone','isd_code','country','country_code','city','citylat','citylong','captcha','vip_flag','member_type','last_login_time','forgotcode','forgotpassstatus','lat','long','login_from_ip','last_time','last_logout_time','point_total','birth_date_privacy', 'birth_date_privacy_custom', 'gender_privacy', 'gender_privacy_custom','user_status_sentence'];
	}
    
    public function getPosts()
    {
        return $this->hasMany(PostForm::className(), ['post_user_id' => '_id']);
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
				['vip_flag', 'default', 'value' => 0],
        ];
    }
    
	public function scenarios()
    {
        $scenarios = parent::scenarios();     
        return $scenarios;
    }
    
    public function login()
    {
        $email = strtolower($_POST['LoginForm']['email']);
        $password = $_POST['LoginForm']['password'];    
        $login = LoginForm::find()->where(['email' => $email,'password' => $password])->orwhere(['phone'=> $email,'password' => $password])->one();
    
        $count = count($login);
        if($count == '1')
        {
            $id = $login['_id'];
            $session = Yii::$app->session; 
            $email = $login['email'];
            $session->set('email',$email); 
            if($login->status == '1' || $login->status == '0' || $login->status == '10')
            {
                return 1;
            }
            else{
                return 2;
            }
        }
        else{
            return 6;
        }
    }

    public function extralogin()
    {
        $email = isset($_POST['email']) ? strtolower($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if($email != ''  && $password != '') {
    		$login = LoginForm::find()->where(['email' => $email,'password' => $password])->orwhere(['phone'=> $email,'password' => $password])->one();
            $count = count($login);
            if($count == '1')
    		{
    			$id = $login['_id'];
    			$session = Yii::$app->session; 
    			$email = $login['email'];
    			$session->set('email',$email); 
    			if($login->status == '1' || $login->status == '0' || $login->status == '10')
    			{
    				return 1;
    			}
    			else{
    				return 2;
    			}
            }
            else{
                return 6;
            }
        } else {
            return false;
        }
    }
	
    public function signup()
    {
        $email = $_POST['LoginForm']['email'];
        $signup = LoginForm::find()->where(['email' => $email])->one();
		return $signup;
    }
	
	public function saverecord()
    {
		$fname = $_POST['LoginForm']['fname'];
		$lname = $_POST['LoginForm']['lname'];
        $email = strtolower($_POST['LoginForm']['email']);
		$password = $_POST['LoginForm']['password'];
		$date = time();
        
        $signup = new LoginForm();
		$signup->fname = $fname;
		$signup->lname = $lname;
		$signup->email = $email;
		$signup->password = $password;
		$signup->con_password = $password;
		$signup->status = "0";
		$signup->created_date = "$date";
		$signup->updated_date = "$date";
		$signup->created_at = $date;
		$signup->updated_at = $date;
		$signup->fullname = $fname . " " . $lname;
		$signup->login_from_ip = $_SERVER['REMOTE_ADDR'];
		$signup->insert();

        return $signup;
	}    
    
    public function signup2()
    {
		$session = Yii::$app->session;
        $email = $session->get('email_id');
		$login_from_ip = $_SERVER['REMOTE_ADDR'];
		$record = LoginForm::find()->select(['_id','email'])->where(['login_from_ip' => $login_from_ip])->orderBy(['created_date'=>SORT_DESC])->one();
		$email = $record['email'];
        $city = $_POST['LoginForm']['city'];
        $country = $_POST['LoginForm']['country'];
        $birth_date = $_POST['LoginForm']['birth_date'];
        $gender = $_POST['gender'];
        $isd_code = $_POST['isd_code'];
        $country_code = $_POST['country_code'];
             
		if(!empty($record))
		{
         $latitude = '';
         $longitude = '';

		 $record->city = $city;
		 $record->country = $country;
		 $record->gender = $gender;
		 $record->isd_code = $isd_code;
		 $record->birth_date = $birth_date;
		 $record->country_code = $country_code;
		 
		 $prepAddr = str_replace(' ','+',$city);
		 $prepAddr = str_replace("'",'',$prepAddr);

         $GApiKeyL = $GApiKeyP = Googlekey::getkey();
        
		 $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?key='.$GApiKeyL.'&address='.$prepAddr.'&sensor=false');
		 $output = json_decode($geocode);
         if(isset($output->results[0])) {
    		  $latitude = $output->results[0]->geometry->location->lat;
              $longitude = $output->results[0]->geometry->location->lng;
        }
		 $record->citylat = "$latitude";
		 $record->citylong = "$longitude";
		 $record->update();
		 
		 return $record;
		} 

      return false;
    }
	
	public function forgot()
	{
		$email = $_POST['fmail'];
		$chars = '0123456789';
		$count = mb_strlen($chars);

		for ($i = 0, $rand = ''; $i < 8; $i++) {
			$index = rand(0, $count - 1);
			$rand .= mb_substr($chars, $index, 1);
		}
	   
		$forgot = LoginForm::find()->where(['email' => $email])->one();
		 
		if(!empty($forgot))
		{
			$forgot->password = $rand;
			$forgot->con_password = $rand;
			$forgot->update();
			return $forgot;
		} 
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
     public function getLastInsertedRecord($email)
    {
        return LoginForm::find()->select(['_id', 'fname', 'lname', 'thumb', 'country', 'last_login_time'])->where(['email' => $email])->asarray()->one();
      
    }
	
	public function getrecentlyjoined()
	{
		return LoginForm::find()->select(['_id','fullname','photo','thumbnail'])->Where(['status'=>'1'])->orderBy(['created_date'=>SORT_DESC])->limit(6)->offset(0)->all();
	}
	
	public function recentlyjoined()
	{
		$data = LoginForm::find()->select(['fname', 'lname', 'fullname', 'photo', 'thumbnail', 'gender', 'city', 'country', 'vip_flag'])->where(['status'=>'1'])->orderBy(['point_total'=>SORT_DESC])->limit(4)->offset(0)->asarray()->all();
		if(!empty($data)) {
			return json_encode($data, true);
		}
		else
		{
			$data = LoginForm::find()->Where(['status'=>'1'])->andWhere(['country'=>'India'])->orWhere(['country'=>'Japan'])->orderBy(['created_date'=>SORT_DESC])->limit(4)->offset(0)->all();
			return json_encode($data, true);
		}	
	}

	public function getTotalUser()
	{
		return LoginForm::find()->Where(['status'=>'1'])->all();
	}
}