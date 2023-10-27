<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class GiftCost extends ActiveRecord
{
    public static function collectionName()
    {
        return 'message_gift';
    }

    public function attributes() {
        return ['_id', 'price', 'created_by', 'created_at'];
    }

    public function FetchGiftCost($uid) {
        if(isset($uid)) {
            $record = GiftCost::find()->select(['price'])->orderby(['created_at' => SORT_DESC])->asarray()->one();
            if(!empty($record)) {
                $cost  = isset($record['price']) ? $record['price'] : 0;
            } else {
                $cost = 0;
            }

            $result = array('status' => true, 'cost' => $cost);
            return json_encode($result, true);
            exit;
        } 

        $records = array();
        return json_encode($records, true);
        exit;
    } 

    public function EditUpdateCost($uid, $price) {
        if(isset($uid) && isset($price)) {
            if($price%5 == 0) {
                $message = new GiftCost();
                $message->price = $price;
                $message->created_by = $uid;
                $message->created_at = strtotime('now');
                if($message->save()) {
                    return true;
                    exit;
                }
            }
        } 
        return false;
        exit;
    }
}