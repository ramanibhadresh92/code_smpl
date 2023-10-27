<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\Personalinfo;

class Localdine extends ActiveRecord 
{

    public static function collectionName()
    {
        return 'localdine';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'event_type', 'cuisine', 'min_guests', 'max_guests', 'title', 'description', 'dish_name', 'summary', 'meal', 'currency', 'whereevent', 'images','created_at','updated_at','flagger', 'flagger_date', 'flagger_by'];
    }

    public function createlocaldine($post, $files, $user_id) {
        $cuisine_array = array("African", "American", "Antique", "Asian", "Barbecue", "Basque", "Belgian", "Brazilian", "British", "Cajun & Creole", "Cambodian", "Caribbean", "Catalan", "Chilean", "Chinese", "Creole", "Danish", "Dutch", "Eastern Europe", "European", "French", "Fusion", "German", "Greek", "Hawaiian", "Hungarian", "Icelandic", "Indian", "Indonesian", "Irish", "Italian", "Jamaican", "Japanese", "Korean", "Kurdish", "Latin American", "Malay", "Malaysian", "Mediterranean", "Mexican", "Middle Eastern", "Nepalese", "Nordic", "North African", "Organic", "Other", "Persian", "Peruvian", "Philippine", "Portuguese", "Russian", "Sami", "Scandinavian", "Seafood", "Singaporean", "South American", "Southern & Soul", "Spanish", "Sri Lankan", "Thai", "Turkish", "Vietnamese");
        $guests_array = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59", "60", "61", "62", "63", "64", "65", "66", "67", "68", "69", "70", "71", "72", "73", "74", "75", "76", "77", "78", "79", "80", "81", "82", "83", "84", "85", "86", "87", "88", "89", "90", "91", "92", "93", "94", "95", "96", "97", "98", "99", "100");
        $meal_array = array("USD", "EUR", "YEN", "CAD", "AUE");
        $max_fields = 10; 

        $event_type = $post['event_type'];
        $cuisine = $post['cuisine'];
        $minguests = $post['minguests'];
        $maxguests = $post['maxguests'];
        $title = $post['title'];
        $description = $post['description'];
        $dishname = $post['dishname'];
        $summary = $post['summary'];
        $meal = $post['meal'];
        $currency = $post['currency'];
        $whereevent = $post['whereevent'];
        $images = $files['images'];
        $url = '../web/uploads/localdine/'; 

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

        $Localdine = new Localdine();
        $Localdine->user_id = $user_id;
        $Localdine->event_type = $event_type;
        if(in_array($cuisine, $cuisine_array)) {
            $Localdine->cuisine = $cuisine;
        } else {
            $Localdine->cuisine = '';
        }
        if(in_array($minguests, $guests_array)) {
            $Localdine->min_guests = $minguests;
        } else {
            $Localdine->min_guests = '';
        }
        if(in_array($maxguests, $guests_array)) {
            $Localdine->max_guests = $maxguests;
        } else {
            $Localdine->max_guests = '';
        }
        $Localdine->title = $title;
        $Localdine->description = $description;
        $Localdine->dish_name = $dishname;
        $Localdine->summary = $summary;
        $Localdine->meal = $meal;
        $Localdine->currency = $currency;
        $Localdine->whereevent = $whereevent;
        $Localdine->images = $imagesNames;
        $Localdine->created_at = time();
        if($Localdine->save()) {
            $LocaldineData = Localdine::find()->select(['user_id'])->where(['user_id' => $user_id])->andWhere(['not','flagger', "yes"])->orderby('_id DESC')->asarray()->one();
            if(!empty($LocaldineData)) {
                $date = time();
                $notification = new Notification();
                $notification->localdine_id = (string)$LocaldineData['_id'];
                $notification->post_owner_id = $user_id;
                $notification->notification_type = 'addpostlocaldine';
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

    public function editlocaldine($post, $files, $user_id) {
        $id = $post['id'];
        $Localdine = Localdine::find()->where(['_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($Localdine)) {
            $cuisine_array = array("African", "American", "Antique", "Asian", "Barbecue", "Basque", "Belgian", "Brazilian", "British", "Cajun & Creole", "Cambodian", "Caribbean", "Catalan", "Chilean", "Chinese", "Creole", "Danish", "Dutch", "Eastern Europe", "European", "French", "Fusion", "German", "Greek", "Hawaiian", "Hungarian", "Icelandic", "Indian", "Indonesian", "Irish", "Italian", "Jamaican", "Japanese", "Korean", "Kurdish", "Latin American", "Malay", "Malaysian", "Mediterranean", "Mexican", "Middle Eastern", "Nepalese", "Nordic", "North African", "Organic", "Other", "Persian", "Peruvian", "Philippine", "Portuguese", "Russian", "Sami", "Scandinavian", "Seafood", "Singaporean", "South American", "Southern & Soul", "Spanish", "Sri Lankan", "Thai", "Turkish", "Vietnamese");
            $guests_array = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59", "60", "61", "62", "63", "64", "65", "66", "67", "68", "69", "70", "71", "72", "73", "74", "75", "76", "77", "78", "79", "80", "81", "82", "83", "84", "85", "86", "87", "88", "89", "90", "91", "92", "93", "94", "95", "96", "97", "98", "99", "100");
            $meal_array = array("USD", "EUR", "YEN", "CAD", "AUE");
            $max_fields = 10; 

            $event_type = $post['event_type'];
            $cuisine = $post['cuisine'];
            $minguests = $post['minguests'];
            $maxguests = $post['maxguests'];
            $title = $post['title'];
            $description = $post['description'];
            $dishname = $post['dishname'];
            $summary = $post['summary'];
            $meal = $post['meal'];
            $currency = $post['currency'];
            $whereevent = $post['whereevent'];
            $url = '../web/uploads/localdine/'; 

            $images = isset($files['images']) ? $files['images'] : array(); 
            $old_images = $Localdine['images'];
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

            $Localdine->user_id = $user_id;
            $Localdine->title = $title;
            $Localdine->event_type = $event_type;
            if(in_array($cuisine, $cuisine_array)) {
                $Localdine->cuisine = $cuisine;
            } else {
                $Localdine->cuisine = '';
            }
            if(in_array($minguests, $guests_array)) {
                $Localdine->min_guests = $minguests;
            } else {
                $Localdine->min_guests = '';
            }
            if(in_array($maxguests, $guests_array)) {
                $Localdine->max_guests = $maxguests;
            } else {
                $Localdine->max_guests = '';
            }
            $Localdine->title = $title;
            $Localdine->description = $description;
            $Localdine->dish_name = $dishname;
            $Localdine->summary = $summary;
            $Localdine->meal = $meal;
            $Localdine->currency = $currency;
            $Localdine->whereevent = $whereevent;
            $Localdine->images = $imagesNames;
            $Localdine->updated_at = time();
            if($Localdine->save()) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            } else {
                $result = array('status' => false);
                return json_encode($result, true);
                exit;
            }
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }
    
    public function uploadphotoslocaldinesave($post, $files, $user_id) {
        $id = $post['id'];
        $Localdine = Localdine::find()->where(['_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($Localdine)) {
            $url = '../web/uploads/localdine/'; 

            $images = isset($files['images']) ? $files['images'] : array(); 
            $old_images = $Localdine['images'];
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
            $Localdine->images = $imagesNames;
            $Localdine->updated_at = time();
            if($Localdine->save()) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            } else {
                $result = array('status' => false);
                return json_encode($result, true);
                exit;
            }
        }
        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }
}   