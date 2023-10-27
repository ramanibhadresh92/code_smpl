<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PageVisitor extends ActiveRecord
{
    public static function collectionName()
    {
        return 'page_visitor';
    }
	
    public function attributes()
    {
        return ['_id', 'page_id', 'visitor_id', 'visited_date', 'status', 'ip', 'month', 'year', 'day', 'date', 'viewed_date', 'v_date'];
    }
	
    public function getUser()
    {
        return $this->hasOne(UserForm::className(), ['_id' => 'visitor_id']);
    }
	
	public function getPageVisitors($pageid)
    {
        $visitors = PageVisitor::find()->with('user')->where(['page_id'=>"$pageid"])->orderBy(['visited_date'=>SORT_DESC])->all();
        $count = count($visitors);
        return $count;
    }
    
	public function getAllPageVisitors($pageid)
    {
        return PageVisitor::find()->where(['page_id'=>"$pageid"])->orderBy(['visited_date'=>SORT_DESC])->all();
    }
	
	public function getAllPageVisitorsCount($pageid)
    {
        return PageVisitor::find()->where(['page_id'=>"$pageid"])->count();
    }
    
     public function getLastYearPageVisitors($pageid,$date)
    {
        return PageVisitor::find()->where(['date'=> ['$gte'=>"$date"]])->andwhere(['page_id'=>"$pageid"])->count();
    }
}