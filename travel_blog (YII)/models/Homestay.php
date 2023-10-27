<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\Personalinfo;

class Homestay extends ActiveRecord
{

    public static function collectionName()
    {
        return 'homestay';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'title', 'property_type', 'guests_room_type', 'bath', 'guest_type', 'homestay_location', 'homestay_facilities', 'adult_guest_rate', 'currency', 'description', 'rules', 'images','created_at','updated_at','flagger', 'flagger_date', 'flagger_by'];
    }

    public function createhomestay($post, $files, $user_id) {
        $property_type_array = array('House', 'Apartment', 'Condominium', 'Farmstay', 'Houseboat', 'Bed and breakfast');
        $guests_room_type_array = array('Entire place', 'Private room', 'Shared room');
        $guest_type_array = array('Males', 'Females', 'Couples', 'Families', 'Students');
        $title = $post['title'];
        $property_type = $post['property_type'];
        $guests_room_type = $post['guests_room_type'];
        $bath = $post['bath'];
        $guest_type = $post['guest_type'];
        $homestay_facilities = $post['homestay_facilities'];
        $homestay_location = $post['homestay_location'];
        $adult_guest_rate = $post['adult_guest_rate'];
        $currency = $post['currency'];
        $description = $post['description'];
        $rules = $post['rules'];
        $images = $files['images'];
        $url = '../web/uploads/homestay/'; 

        $imagesNames = array();
        if(!empty($images['name'])) {
            for ($i=0; $i < count($images); $i++) { 
                if(isset($images['name'][$i]) && $images['name'][$i] != '') {
                    $date = time();
                    $name = $images["name"][$i]; 
                    $tmp_name = $images["tmp_name"][$i];
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $time = time();
                    $uniqid = uniqid();
                    $gen_name = $time.$uniqid.'.'.$ext;
                    move_uploaded_file($tmp_name, $url . $date . $gen_name);
                    $img = $url . $date . $gen_name;
                    $imagesNames[] = $img;
                }
            }
        }

        $imagesNames = implode(',', $imagesNames);

        $Homestay = new Homestay();
        $Homestay->user_id = $user_id;
        $Homestay->title = $title;
        $Homestay->property_type = $property_type;
        $Homestay->guests_room_type = $guests_room_type;
        $Homestay->bath = $bath;
        $Homestay->guest_type = $guest_type;
        $Homestay->homestay_facilities = $homestay_facilities;
        $Homestay->homestay_location = $homestay_location;
        $Homestay->adult_guest_rate = $adult_guest_rate;
        $Homestay->currency = $currency;
        $Homestay->description = $description;
        $Homestay->rules = $rules;
        $Homestay->images = $imagesNames;
        if($Homestay->save()) {
            $HomestayData = Homestay::find()->select(['user_id'])->where(['user_id' => $user_id])->andWhere(['not','flagger', "yes"])->orderby('_id DESC')->asarray()->one();
            if(!empty($HomestayData)) {
                $date = time();
                $notification = new Notification();
                $notification->homestay_id = (string)$HomestayData['_id'];
                $notification->post_owner_id = $user_id;
                $notification->notification_type = 'addposthomestay';
                $notification->is_deleted = '0';
                $notification->status = '1';
                $notification->created_date = $date;
                $notification->updated_date = $date;
                $notification->insert();
            }
            $result = array('status' => true);
            return json_encode($result, true);
            exit;
        } else {
            $result = array('status' => false);
            return json_encode($result, true);
            exit;
        }
    }

    public function edithomestay($post, $files, $user_id) {
        $id = $post['id'];
        $Homestay = Homestay::find()->where(['_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($Homestay)) {
            $property_type_array = array('House', 'Apartment', 'Condominium', 'Farmstay', 'Houseboat', 'Bed and breakfast');
            $guests_room_type_array = array('Entire place', 'Private room', 'Shared room');
            $guest_type_array = array('Males', 'Females', 'Couples', 'Families', 'Students');
            $title = $post['title'];
            $property_type = $post['property_type'];
            $guests_room_type = $post['guests_room_type'];
            $bath = $post['bath'];
            $guest_type = $post['guest_type'];
            $homestay_facilities = $post['homestay_facilities'];
            $homestay_location = $post['homestay_location'];
            $adult_guest_rate = $post['adult_guest_rate'];
            $currency = $post['currency'];
            $description = $post['description'];
            $rules = $post['rules'];
            $images = isset($files['images']) ? $files['images'] : array(); 
            $url = '../web/uploads/homestay/'; 
            $old_images = $Homestay['images'];
            $old_images = explode(',', $old_images);
            $old_images = array_values(array_filter($old_images));
            if(!empty($images['name'])) {
                for ($i=0; $i < count($images['name']); $i++) { 
                    if(isset($images['name'][$i]) && $images['name'][$i] != '') {
                        $date = time();
                        $name = $images["name"][$i]; 
                        $tmp_name = $images["tmp_name"][$i];
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $time = time();
                        $uniqid = uniqid();
                        $gen_name = $time.$uniqid.'.'.$ext;
                        move_uploaded_file($tmp_name, $url . $date . $gen_name);
                        $img = $url . $date . $gen_name;
                        $old_images[] = $img;
                    }
                }

            }

            $imagesNames = implode(',', $old_images);

            $Homestay->user_id = $user_id;
            $Homestay->title = $title;
            $Homestay->property_type = $property_type;
            $Homestay->guests_room_type = $guests_room_type;
            $Homestay->bath = $bath;
            $Homestay->guest_type = $guest_type;
            $Homestay->homestay_facilities = $homestay_facilities;
            $Homestay->homestay_location = $homestay_location;
            $Homestay->adult_guest_rate = $adult_guest_rate;
            $Homestay->currency = $currency;
            $Homestay->description = $description;
            $Homestay->rules = $rules;
            $Homestay->images = $imagesNames;
            if($Homestay->save()) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            }
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function uploadphotoshomestaysave($post, $files, $user_id) {
        $id = $post['id'];
        $Homestay = Homestay::find()->where(['_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($Homestay)) {
            $images = isset($files['images']) ? $files['images'] : array(); 
            $url = '../web/uploads/homestay/'; 
            $old_images = $Homestay['images'];
            $old_images = explode(',', $old_images);
            $old_images = array_values(array_filter($old_images));
            if(!empty($images['name'])) {
                for ($i=0; $i < count($images['name']); $i++) { 
                    if(isset($images['name'][$i]) && $images['name'][$i] != '') {
                        $date = time();
                        $name = $images["name"][$i]; 
                        $tmp_name = $images["tmp_name"][$i];
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $time = time();
                        $uniqid = uniqid();
                        $gen_name = $time.$uniqid.'.'.$ext;
                        move_uploaded_file($tmp_name, $url . $date . $gen_name);
                        $img = $url . $date . $gen_name;
                        $old_images[] = $img;
                    }
                }

            }

            $imagesNames = implode(',', $old_images);
            $Homestay->images = $imagesNames;
            if($Homestay->save()) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            }
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }
}