<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class ProfileVisitor extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_visitor';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'visitor_id', 'visited_date', 'status', 'ip', 'month', 'year'];
    }
   
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'visitor_id']);
    }
    
	public function getAllVisitors($guserid)
    {
        $visitors = ProfileVisitor::find()->with('user')->where(['user_id'=>"$guserid"])->orderBy(['visited_date'=>SORT_DESC])->all();
        $count = count($visitors);
        return $count;
    }
    
	public function getAllProfileVisitors($guserid)
    {
        return ProfileVisitor::find()->where(['user_id'=>"$guserid"])->orderBy(['visited_date'=>SORT_DESC])->all();
    }
}