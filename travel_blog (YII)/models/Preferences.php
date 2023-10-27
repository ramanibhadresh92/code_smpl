<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\CollectionFollow;

class Preferences extends ActiveRecord
{
    public static function collectionName()
    {
        return 'preferences';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'type', 'object_id', 'feed', 'sort', 'notification', 'created_at', 'modified_at'];
    }

    public function getinfo($user_id, $type, $object_id) {
        $data = Preferences::find()->where(['user_id' => $user_id, 'type' => $type, 'object_id' => $object_id])->asarray()->one();
        return json_encode($data, true); 
    }

    public function preferencessave($user_id, $post) {
        $feed = $post['$feed'];
        $sort = ($post['$sort'] == '') ? 'Recent' : $post['$sort'] ;
        $notification = $post['$notification'];
        $id = $post['$id'];
        $action = $post['$action'];
        $action = strtolower($action);
        if($feed != '' && $sort != '' && $notification != '' && $id != '' && $action != '') {
            $data = array();
            if ($action == 'collection') {
                $data = CollectionFollow::find()->Where(['user_id'=> $user_id, 'collection_id'=> $id, 'is_deleted' => '1'])->one();
            } elseif ($action == 'page') {
                $data = Page::find()->Where(['created_by'=> $user_id, (string)'_id'=> $id])->one();
            }
         
            if(!empty($data)) {
                $preferences = Preferences::find()->where(['user_id' => $user_id, 'type' => $action, 'object_id' => $id])->one();
                if(!empty($preferences)) {
                    $preferences->feed = $feed;
                    $preferences->sort = $sort;
                    $preferences->notification = $notification;
                    $preferences->modified_at = strtotime('now');
                    if($preferences->update()) {
                        return true;    
                    }
                } else {
                    $preferences = new Preferences();
                    $preferences->user_id = $user_id;
                    $preferences->type = $action;
                    $preferences->object_id = $id;
                    $preferences->feed = $feed;
                    $preferences->sort = $sort;
                    $preferences->notification = $notification;
                    $preferences->created_at = strtotime('now');
                    $preferences->modified_at = strtotime('now');
                    if($preferences->save()) {
                        return true;    
                    }
                }
            }
        }
        
        return false; 
    }

    public function all($user_id) {
        return ArrayHelper::map(Preferences::find()->where(['user_id' => (string)$user_id])->asarray()->all(), 'object_id', function($data) { return $data; });
    }
}