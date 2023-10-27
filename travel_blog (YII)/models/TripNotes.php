<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class TripNotes extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_trip_notes';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'created_date', 'updated_date', 'status', 'notetitle', 'notetext', 'tripid'];
    }
	
	public function getUser()
    {
        return $this->hasOne(LoginForm::className(), ['_id' => 'user_id']);
    }
    
    public function getMyTripNotes($user_id)
    {
        return TripNotes::find()->with('user')->where(['user_id' => "$user_id"])->orderBy(['created_date'=>SORT_DESC])->all();
    }
	
    public function getTripNotes($tripid)
    {
        return TripNotes::find()->with('user')->where(['tripid' => "$tripid"])->orderBy(['created_date'=>SORT_DESC])->all();
    }
	
    public function getNotesCount($tripid)
    {
        return TripNotes::find()->where(['tripid' => "$tripid"])->count();
    }
}