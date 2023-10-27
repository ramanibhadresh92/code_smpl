<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class ReferForm extends ActiveRecord
{
    public static function collectionName()
    {
        return 'user_refers';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'referal_id', 'referal_point', 'referal_text', 'referred_date', 'is_deleted', 'date', 'month', 'year'];
    }

    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }

    public function getAllReferals($userid)
    {
        return ReferForm::find()->with('user')->where(['referal_id'=>"$userid",'is_deleted'=>'0'])->orderBy(['referred_date'=>SORT_DESC])->all();
    }
    
    public function getTotalReferals($userid)
    {
        $ref = ReferForm::find()->where(['referal_id'=>"$userid",'is_deleted'=>'0'])->orderBy(['referred_date'=>SORT_DESC])->all();
        return count($ref);
    }
}