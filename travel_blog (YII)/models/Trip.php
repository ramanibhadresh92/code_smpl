<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Trip extends ActiveRecord
{
    
    public static function collectionName()
    {
        return 'user_trips';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'created_date', 'updated_date', 'status', 'trip_name', 'trip_summary', 'tripcolor', 'mapline', 'trip_start_date', 'start_from', 'end_to', 'privacy','flagger', 'flagger_date', 'flagger_by'];
    }
	
	public function getUser()
    {
        return $this->hasOne(LoginForm::className(), ['_id' => 'user_id']);
    }
  
    public function getMyTrips($user_id)
    {
        return Trip::find()->with('user')->where(['user_id' => "$user_id"])->andWhere(['not','flagger', "yes"])->orderBy(['created_date'=>SORT_DESC])->all();
    }

    public function getTripDetails($tripid)
    {
        return Trip::find()->where(['_id' => "$tripid"])->andWhere(['not','flagger', "yes"])->asArray()->one();
    }

	public function getMyTripCount($user_id)
    {
        return Trip::find()->where(['user_id' => "$user_id"])->andWhere(['not','flagger', "yes"])->count();
    }
	
	public function delTrip($tripid)
    {
        Trip::deleteAll(['_id' => "$tripid"]);
		TripNotes::deleteAll(['tripid' => "$tripid"]);
    }
}