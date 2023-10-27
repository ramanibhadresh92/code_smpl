<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Referal extends ActiveRecord
{
    public static function collectionName()
    {
        return 'referal';
    }

    public function attributes()
    {
        return ['_id', 'user_id','sender_id', 'referal_id', 'referal_text', 'recommend', 'category', 'is_deleted', 'month_year', 'created_date'];
    }

    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }

    public function getAllReferals($userid)
    {
        return Referal::find()->where(['user_id'=>"$userid",'is_deleted'=>'1'])->orderBy(['created_date'=>SORT_DESC])->all();
    }
    
    public function getAllReferalsAdmin()
    {
        return Referal::find()->all();
    }
    
    public function getAllCatReferals($userid,$category)
    {
        if($category == "Negative" || $category == "Positive")
        {
            $referal =  Referal::find()->where(['user_id'=>"$userid",'is_deleted'=>'1','recommend'=> "$category"])->orderBy(['created_date'=>SORT_DESC])->all();
        }
        else
        {
            $referal = Referal::find()->where(['user_id'=>"$userid",'is_deleted'=>'1','category'=> "$category"])->orderBy(['created_date'=>SORT_DESC])->all();
        }
        return $referal;    
    }
    
    public function getLastMonthReferals($userid)
    {
        $last_month = date("Y-n", strtotime("previous month"));
        $ref = Referal::find()->where(['user_id'=>"$userid",'month_year'=>"$last_month",'is_deleted'=>'1'])->orderBy(['created_date'=>SORT_DESC])->count();
        return $ref;
    }
    
    public function getCrntMonthReferals($userid)
    {
        $crnt_month = date("Y-n");
        $ref = Referal::find()->where(['user_id'=>"$userid",'month_year'=>"$crnt_month",'is_deleted'=>'1'])->orderBy(['created_date'=>SORT_DESC])->distinct('sender_id');
        return $ref;
    }
    
    public function getTotalReferals($userid)
    {
        return Referal::find()->where(['user_id'=>"$userid",'is_deleted'=>'1'])->count();
    }

    public function getAllReferalscount($userid)
    {
        return Referal::find()
        ->where(['user_id'=> (string)$userid,'is_deleted'=>'1','recommend'=>'Negative'])
        ->orwhere(['user_id'=> (string)$userid,'is_deleted'=>'1','recommend'=>'negative'])
        ->orwhere(['user_id'=> (string)$userid,'is_deleted'=>'1','recommend'=>'Positive'])
        ->orwhere(['user_id'=> (string)$userid,'is_deleted'=>'1','recommend'=>'positive'])
        ->count();
    }

    public function getTotalPositiveReferals($userid)
    {
        return Referal::find()
        ->where(['user_id'=>(string)$userid,'is_deleted'=>'1','recommend'=>'Positive'])
        ->orwhere(['user_id'=>(string)$userid,'is_deleted'=>'1','recommend'=>'positive'])
        ->count();
    }

    public function getTotalNegativeReferals($userid)
    {
        return Referal::find()
        ->where(['user_id'=>(string)$userid,'is_deleted'=>'1','recommend'=>'Negative'])
        ->orwhere(['user_id'=>(string)$userid,'is_deleted'=>'1','recommend'=>'negative'])
        ->count();
    }
    
}