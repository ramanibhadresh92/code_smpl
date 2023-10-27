<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class PageRoles extends ActiveRecord
{
    public static function collectionName()
    {
        return 'pageroles';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'page_id', 'created_date', 'added_by', 'pagerole'];
    }
    
    public function getPage()
    {
        return $this->hasOne(Page::className(), ['page_id' => 'page_id']);
    }

    public function getRole($user_id,$page_id)
    {
        return PageRoles::find()->where(['user_id' => "$user_id",'page_id' => "$page_id"])->one();
    }
    
    public function pageRole($user_id,$page_id)
    {
        $pageroleexist = PageRoles::find()->where(['user_id' => "$user_id",'page_id' => "$page_id"])->one();
        return $pageroleexist['pagerole'];
    }
    
    public function pageAdmins($page_id)
    {
        return PageRoles::find()->where(['pagerole' => "Admin",'page_id' => "$page_id"])->all();
    }
    
    public function pageEditors($page_id)
    {
        return PageRoles::find()->where(['pagerole' => "Editor",'page_id' => "$page_id"])->all();
    }
    
    public function pageSupporters($page_id)
    {
        return PageRoles::find()->where(['pagerole' => "Supporter",'page_id' => "$page_id"])->all();
    }
    
    public function getAdsPages($user_id)
    {
        return PageRoles::find()->with('page')->select(['_id','page_id'])->where(['user_id' => "$user_id"])->orderBy(['created_date'=>SORT_DESC])->asarray()->all();
    }
}