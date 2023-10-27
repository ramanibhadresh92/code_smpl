<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class MessageBlock extends ActiveRecord
{
    public static function collectionName()
    {
        return 'messageblocks';
    }

    public function attributes() {
        return ['_id', 'from_id', 'to_id', 'con_id', 'created_at', '__v'];
    }

    public function BlockUserReport() {
        if(isset($_POST['id']) && !empty($_POST['id'])) {
            $session = Yii::$app->session;
            $user_id = (string)$session->get('user_id');
            $id = $_POST['id'];
            $model = new \frontend\models\SecuritySetting();
            $type = $model->blockuser($id, $user_id);
            return $type;
        }
    }   

    public function ListUserData()
    {
        $session = Yii::$app->session;
        $user_id = $userid =  $session->get('user_id');
        if(isset($_POST['$like']) && $_POST['$like'] != '') {
            $like = trim($_POST['$like']);
            $usrfrd = Connect::getuserFriendsWithLike($user_id, $like);
            $newArray = array();

            foreach ($usrfrd as $indexKey => $indexValue) {
                $id = (string)$indexValue['userdata']['_id'];
                $email = $indexValue['userdata']['email'];
                $fullname = $indexValue['userdata']['fname'] . ' ' . $indexValue['userdata']['lname'];
                $country =  $indexValue['userdata']['country'];
                $thumb = isset($indexValue['userdata']['thumb']) ? $indexValue['userdata']['thumb'] : '' ;
                if($thumb == '' || $thumb == undefined || $thumb == null) {
                    $thumb = Yii::$app->GenCls->getimage($id,'thumb');
                }

                $current = array('id' => $id,
                'email' => $email,
                'fullname' => $fullname,
                'country' => $country,
                'thumb' => $thumb);
                $newArray[] = $current;
            }
            echo json_encode($newArray);
        }
    }
}