<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\PostForm;

class ProfileViewer extends ActiveRecord
{
    public static function collectionName()
    {
        return 'profile_viewer';
    }

    public function attributes()
    {
         return ['_id', 'user_id', 'viewer_ids', 'viewer_detais'];
    }

    public function getViewerAllIDs($id) {
        $result = array();
        $data = ProfileViewer::find()->select(['viewer_ids'])->where(['user_id' => $id])->asarray()->one();
        if(!empty($data)) {
            $ids_data = $data['viewer_ids'];
            $result = explode(',', $ids_data);
            return $result;
        }

        return $result;
    }

    public function getViewerCount($id) {
        $data = ProfileViewer::getViewerAllIDs($id);
        return count($data);
    }


    public function getViewerAllDetails_DIRECT($id) {
        $result = array();
        $data = ProfileViewer::find()->select(['viewer_detais'])->where(['user_id' => $id])->asarray()->all();
        if(!empty($data)) {
            $ids_data = $data['viewer_detais'];
            $result = explode(',', $ids_data);
            return $result;
        }

        return $result;
    }

    public function getViewerAllDetails($id) {
        $result = array();
        $ids_data = ProfileViewer::getViewerAllDetails_DIRECT($id);
        if(!empty($ids_data)) {
            foreach ($ids_data as $sub_ids_data) {
                $split_sub_data = explode('_', $sub_ids_data);
                if(count($split_sub_data) == 2) {
                    $id = $split_sub_data[0];
                    $time = $split_sub_data[1];
                    $result[$id] = $time;
                }
            }
        }

        return $result;
    }

    public function checkIsViewer($uid, $id) {
        if($uid != $id) {
            $data = ProfileViewer::getViewerAllIDs($id);
            if(!empty($data)) {
                if(in_array($uid, $data)) {
                    return 'yes';
                }
            }
        }
    }

    public function AddViewer($uid, $id) {
        if($uid != $id) {
            $isViewer = ProfileViewer::checkIsViewer($uid, $id);
            if($isViewer != 'yes') {
                $time = strtotime("now");
                $label = $uid.'_'.$time;

                $I__Ids = ProfileViewer::getViewerAllIDs($id);
                $I__Details = ProfileViewer::getViewerAllDetails_DIRECT($id);

                $I__Ids[] = $uid;
                $I__Details[] = $label;

                $ProfileViewer = new ProfileViewer();
                $ProfileViewer->user_id = $id; 
                $ProfileViewer->viewer_ids = implode(',', $I__Ids); 
                $ProfileViewer->viewer_detais = implode(',', $I__Details); 
                $ProfileViewer->save();
                return true;
            }
        }

        return false;
    } 

}