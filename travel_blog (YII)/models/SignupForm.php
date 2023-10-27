<?php
namespace frontend\models;
use Yii;
use yii\mongodb\ActiveRecord;

class SignupForm extends ActiveRecord
{
    public static function CollectionName()
    {
        return 'user';
    }

    public function rules()
    {
        return [
                [['username', 'lname', 'email', 'password', 'con_password', 'birth_date', 'gender'], 'required'],
                ['email', 'email'],
                [['con_password'], 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function attributes()
    {
        return ['_id', 'fb_id', 'username', 'lname', 'email', 'password', 'con_password', 'birth_date', 'gender','photo','created_date','updated_date'];
    }
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }
     protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->email);
        }

        return $this->_user;
    }
}
