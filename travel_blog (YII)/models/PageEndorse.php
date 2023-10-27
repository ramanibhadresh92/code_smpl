<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PageEndorse extends ActiveRecord
{
    public static function collectionName()
    {
        return 'page_endorse';
    }
	
    public function attributes()
    {
        return ['_id', 'page_id', 'user_id', 'endorse_name', 'created_date', 'updated_date', 'is_deleted'];
    }
	
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'user_id']);
    }
	
    public function getAllEndorse($pageid)
    {
        return PageEndorse::find()->where(['page_id' => "$pageid",'is_deleted' => '1'])->distinct('endorse_name');
    }
    
    public function getAllEndorseCount($pageid)
    {
        return PageEndorse::find()->where(['page_id' => "$pageid",'is_deleted' => '1'])->count();
    }
    
    public function getExceptEndorse($pageid)
    {
        $defend = "Customer Support,Business Attitude,Refund,Information Technology,Project Management";
        $defendlist = (explode(",",$defend));
        return PageEndorse::find()->where(['page_id' => "$pageid",'is_deleted' => '1'])->andWhere(['not in', 'endorse_name', "$defendlist[0]"])->andWhere(['not in', 'endorse_name', "$defendlist[1]"])->andWhere(['not in', 'endorse_name', "$defendlist[2]"])->andWhere(['not in', 'endorse_name', "$defendlist[3]"])->andWhere(['not in', 'endorse_name', "$defendlist[4]"])->distinct('endorse_name');
    }
	
    public function getPEusers($pageid,$pe)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $pe = PageEndorse::find()->with('user')->where(['page_id'=>"$pageid",'endorse_name'=>"$pe",'is_deleted'=>"1"])->andWhere(['not in', 'user_id', "$user_id"])->orderBy(['updated_date'=>SORT_DESC])->limit(10)->offset(0)->all();
        return $pe;
    }
	
    public function getPEcount($pageid,$pe)
    {
        $pecount = PageEndorse::find()->where(['page_id'=>"$pageid",'endorse_name'=>"$pe",'is_deleted'=>"1"])->count();
        return $pecount;
    }
	
    public function getPEexist($pageid,$pe)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $pe = PageEndorse::find()->with('user')->where(['page_id'=>"$pageid",'user_id'=>"$user_id",'endorse_name'=>"$pe",'is_deleted'=>"1"])->one();
        if($pe){return true;}
        else{return false;}
    }
	
    public function getPEallusers($pageid,$pe)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $pe = PageEndorse::find()->with('user')->where(['page_id'=>"$pageid",'endorse_name'=>"$pe",'is_deleted'=>"1"])->orderBy(['updated_date'=>SORT_DESC])->all();
        return $pe;
    }
}