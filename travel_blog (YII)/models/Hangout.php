<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\Personalinfo;

class Hangout extends ActiveRecord
{

    public static function collectionName()
    {
        return 'hangout';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'type', 'hostavailability', 'hostservices', 'personalmessage', 'is_hide', 'is_profile', 'profile', 'is_verified', 'verified', 'is_save'];
    }

    public function getdefaultinfo($user_id)
    {
        $userdata = UserForm::find()->select(['fname', 'lname', 'country', 'city', 'gender'])->where(['_id' => $user_id])->asarray()->one();
        $totalconnections = Connect::find()->where(['to_id' => (string)$user_id, 'status' => '1'])->count();
        if(!empty($userdata)) {
            $id = (string)$userdata['_id'];
            $Personalinfo = Personalinfo::find()->where(['user_id' => $id])->asarray()->one();
            if(!empty($Personalinfo)) {
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
        return $userdata;
    }
}