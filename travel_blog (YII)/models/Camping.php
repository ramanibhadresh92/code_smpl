<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\UserForm;
use frontend\models\Connect;
use frontend\models\Personalinfo;

class Camping extends ActiveRecord 
{

    public static function collectionName()
    {
        return 'camping';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'title', 'min_guests', 'max_guests', 'rate','currency', 'description', 'location', 'telephone', 'email', 'website', 'period_s', 'period_e', 'services', 'images','created_at','updated_at','flagger', 'flagger_date', 'flagger_by'];
    }

    public function createcamping($post, $files, $user_id) {
        $services_array = array('Waste tank discharge', 'Public lavatory', 'Walking path', 'Swimming pool', 'Fishing permits', 'Cooking facilities', 'Sports hall', 'Washing machine', 'Hot pot', 'Sports field', 'Shower', 'Golf course', 'Sauna', 'Play ground');
        $camping_title = $post['camping_title'];
        $camping_minguests = $post['camping_minguests'];
        $camping_maxguests = $post['camping_maxguests'];
        $camping_rate = $post['camping_rate'];
        $camping_currency = $post['camping_currency'];
        $camping_description = $post['camping_description'];
        $camping_location = $post['camping_location'];
        $camping_telephone = $post['camping_telephone'];
        $camping_email = $post['camping_email'];
        $camping_website = $post['camping_website'];
        $camping_period = $post['camping_period'];
        $camping_services = $post['camping_services'];
        $camping_images = $files['camping_images'];
        $url = '../web/uploads/camping/'; 

        $imagesNames = array();
        if(!empty($camping_images['name'])) {
            for ($i=0; $i < count($camping_images); $i++) { 
                if(isset($camping_images['name'][$i]) && $camping_images['name'][$i] != '') {
                    $date = time();
                    $name = $camping_images["name"][$i]; 
                    $tmp_name = $camping_images["tmp_name"][$i];
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

        //check date is valid with range or not
        $current_year = date("Y"); 
        $camping_period = explode('-', $camping_period);
        $firstdate_filter = '';
        $seconddate_filter = '';
        $start_date = '';
        $end_date = '';

        if(count($camping_period) == 2) {
            $camping_period = array_values($camping_period);
            $firstdate = trim($camping_period[0]);
            $seconddate = trim($camping_period[1]);

            $f_date_box = array_map('trim', explode('/', $firstdate));

            if(count($f_date_box) == 2) {
                if(checkdate($f_date_box[0], $f_date_box[1], $current_year)) {
                    $firstdate_filter = $f_date_box[0].'-'.$f_date_box[1].'-'.$current_year;
                }
            }

            if($firstdate_filter != '') {
                $s_date_box = array_map('trim', explode('/', $seconddate));

                if(count($s_date_box) == 2) {
                    if(checkdate($s_date_box[0], $s_date_box[1], $current_year)) {
                        $seconddate_filter = $s_date_box[0].'-'.$s_date_box[1].'-'.$current_year;
                    }
                }
            }
        }

        if($firstdate_filter != '' && $seconddate_filter != '') {
            $start_date = $firstdate_filter;        
            $end_date = $seconddate_filter;        
        }


        $Camping = new Camping();
        $Camping->user_id = $user_id;
        $Camping->title = $camping_title;
        $Camping->min_guests = $camping_minguests;
        $Camping->max_guests = $camping_maxguests;
        $Camping->rate = $camping_rate;
        $Camping->currency = $camping_currency;
        $Camping->description = $camping_description;
        $Camping->location = $camping_location;
        $Camping->telephone = $camping_telephone;
        $Camping->email = $camping_email;
        $Camping->website = $camping_website;



        $Camping->period_s = $start_date;
        $Camping->period_e = $end_date;
        $Camping->services = $camping_services;
        $Camping->images = $imagesNames;
        if($Camping->save()) {
            $CampingData = Camping::find()->select(['user_id'])->where(['user_id' => $user_id])->andWhere(['not','flagger', "yes"])->orderby('_id DESC')->asarray()->one();
            if(!empty($CampingData)) {
                $date = time();
                $notification = new Notification();
                $notification->camping_id = (string)$CampingData['_id'];
                $notification->post_owner_id = $user_id;
                $notification->notification_type = 'addpostcamping';
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

    public function editcamping($post, $files, $user_id) {
        $id = $post['camping_id'];
        $Camping = Camping::find()->where(['_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($Camping)) {
            $services_array = array('Waste tank discharge', 'Public lavatory', 'Walking path', 'Swimming pool', 'Fishing permits', 'Cooking facilities', 'Sports hall', 'Washing machine', 'Hot pot', 'Sports field', 'Shower', 'Golf course', 'Sauna', 'Play ground');

            $camping_title = $post['camping_title'];
            $camping_minguests = $post['camping_minguests'];
            $camping_maxguests = $post['camping_maxguests'];
            $camping_rate = $post['camping_rate'];
            $camping_currency = $post['camping_currency'];
            $camping_description = $post['camping_description'];
            $camping_location = $post['camping_location'];
            $camping_telephone = $post['camping_telephone'];
            $camping_email = $post['camping_email'];
            $camping_website = $post['camping_website'];
            $camping_period = $post['camping_period'];
            $camping_services = $post['camping_services'];
            $url = '../web/uploads/camping/'; 

            $camping_images = isset($files['camping_images']) ? $files['camping_images'] : array(); 
            $old_images = $Camping['images'];
            $old_images = explode(',', $old_images);
            $old_images = array_values(array_filter($old_images));
            if(!empty($camping_images['name'])) {
                for ($i=0; $i < count($camping_images['name']); $i++) { 
                    if(isset($camping_images['name'][$i]) && $camping_images['name'][$i] != '') {
                        $date = time();
                        $name = $camping_images["name"][$i]; 
                        $tmp_name = $camping_images["tmp_name"][$i];
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
            
            //check date is valid with range or not
            $current_year = date("Y"); 
            $camping_period = explode('-', $camping_period);
            $firstdate_filter = '';
            $seconddate_filter = '';
            $start_date = '';
            $end_date = '';

            if(count($camping_period) == 2) {
                $camping_period = array_values($camping_period);
                $firstdate = trim($camping_period[0]);
                $seconddate = trim($camping_period[1]);

                $f_date_box = array_map('trim', explode('/', $firstdate));

                if(count($f_date_box) == 2) {
                    if(checkdate($f_date_box[0], $f_date_box[1], $current_year)) {
                        $firstdate_filter = $f_date_box[0].'-'.$f_date_box[1].'-'.$current_year;
                    }
                }

                if($firstdate_filter != '') {
                    $s_date_box = array_map('trim', explode('/', $seconddate));

                    if(count($s_date_box) == 2) {
                        if(checkdate($s_date_box[0], $s_date_box[1], $current_year)) {
                            $seconddate_filter = $s_date_box[0].'-'.$s_date_box[1].'-'.$current_year;
                        }
                    }
                }
            }

            if($firstdate_filter != '' && $seconddate_filter != '') {
                $start_date = $firstdate_filter;        
                $end_date = $seconddate_filter;        
            }
            $Camping->user_id = $user_id;
            $Camping->title = $camping_title;
            $Camping->min_guests = $camping_minguests;
            $Camping->max_guests = $camping_maxguests;
            $Camping->rate = $camping_rate;
            $Camping->currency = $camping_currency;
            $Camping->description = $camping_description;
            $Camping->location = $camping_location;
            $Camping->telephone = $camping_telephone;
            $Camping->email = $camping_email;
            $Camping->website = $camping_website;
            $Camping->period_s = $start_date;
            $Camping->period_e = $end_date;
            $Camping->services = $camping_services;
            $Camping->images = $imagesNames;
            if($Camping->save()) {
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

    public function uploadphotoscampingsave($post, $files, $user_id) {
        $id = $post['camping_id'];
        $Camping = Camping::find()->where(['_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();
        if(!empty($Camping)) {
            $url = '../web/uploads/camping/'; 
            $camping_images = isset($files['camping_images']) ? $files['camping_images'] : array(); 
            $old_images = $Camping['images'];
            $old_images = explode(',', $old_images);
            $old_images = array_values(array_filter($old_images));
            if(!empty($camping_images['name'])) {
                for ($i=0; $i < count($camping_images['name']); $i++) { 
                    if(isset($camping_images['name'][$i]) && $camping_images['name'][$i] != '') {
                        $date = time();
                        $name = $camping_images["name"][$i]; 
                        $tmp_name = $camping_images["tmp_name"][$i];
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
            $Camping->images = $imagesNames;
            if($Camping->save()) {
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