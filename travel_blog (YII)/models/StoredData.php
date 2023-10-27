<?php
namespace frontend\models;
use yii\base\Model;
use Yii;
use yii\mongodb\ActiveRecord;

/**
    *  it is general table for used multi purpose data stored things. one of like viewcount post..
**/

class StoredData extends ActiveRecord
{  
    public static function collectionName()
    {
        return 'stored_data';
    }

    public function attributes()
    {
        return ['_id', 'post_id', 'type', 'viewids', 'created_at'];
    }

    public function increaseGalleryViewCount($id, $user_id) {
        $data = StoredData::find()->where(['post_id' => $id, 'type' => 'viewcount'])->one(); 

        if(!empty($data)) {
            $StoredData = isset($data->viewids) ? $data->viewids : '';
            $StoredData = explode(',', $StoredData);
            $StoredData = array_values(array_filter($StoredData));

            if(!in_array($user_id, $StoredData)) {
                $StoredData[] = $user_id;
            }
        
            $StoredData = implode(",", $StoredData);
            $data->viewids = $StoredData;
            $data->update();
        } else {
            $StoredData = new StoredData();
            $StoredData->post_id = $id;
            $StoredData->viewids = $user_id;
            $StoredData->type = 'viewcount';
            $StoredData->save();
        }
    }
}