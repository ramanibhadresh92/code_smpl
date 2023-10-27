<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
 

class Abuse extends ActiveRecord
{
    public static function collectionName()
    {
        return 'abuse';
    }

    public function attributes()
    {
        return ['_id', 'type', 'offer_id', 'user_id', 'reason', 'status', 'created_at'];
    }

    public function abuseReport($post, $user_id) {
        if(isset($post) && !empty($post)) {
            $id = $post['$id'];
            $type = $post['$type'];
            $reason = implode(',', $post['$reason']);
            $abuseReport = new Abuse();
            $abuseReport->type = $type;
            $abuseReport->offer_id = $id;
            $abuseReport->user_id = $user_id; 
            $abuseReport->reason = $reason;
            $abuseReport->created_at = strtotime("now");
            $abuseReport->save();
            return true;
            exit;
        }
    }

    public function abuseReportUniq($post, $user_id) {
        if(isset($post) && !empty($post)) {
            $id = $post['$id'];
            $type = strtolower($post['$type']);
            $reason = $post['$reason'];

            $abuseReport = new Abuse();
            $abuseReport->type = $type;
            $abuseReport->offer_id = $id;
            $abuseReport->user_id = $user_id; 
            $abuseReport->reason = $reason;
            $abuseReport->created_at = strtotime("now");
            $abuseReport->save();

            return true;
            exit;
        }
    }
}