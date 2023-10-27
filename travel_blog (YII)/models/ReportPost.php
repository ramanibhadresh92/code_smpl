<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class ReportPost extends ActiveRecord
{
   public static function collectionName()
    {
        return 'user_reports';
    }
	
    public function attributes()
    {
        return ['_id', 'post_id', 'reporter_id', 'reason', 'created_date',];
    }
}