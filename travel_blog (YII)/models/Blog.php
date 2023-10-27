<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class Blog extends ActiveRecord
{
    public static function collectionName()
    {
        return 'blog';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'image', 'title', 'description', 'tagged_connections', 'visible_to', 'created_at', 'modified_at', 'customids', 'hideids', 'post_id', 'place', 'placetitle','flagger', 'flagger_date', 'flagger_by'];
    }
    
    public function createblog($data, $images, $user_id) {
        $date = uniqid().'_'.rand(9999, 99999).'_'.time();
        $title = $data['title'];
        $description = $data['description'];
        $url = '../web/uploads/blog/'; 
        $place = Yii::$app->params['place'];
        $placetitle = Yii::$app->params['placetitle'];
        $placefirst = Yii::$app->params['placefirst'];

        $Blog = new Blog();
        $Blog->user_id = $user_id;
        
        if(isset($images['name']) && $images['name'] != '') {
            $name = $images["name"]; 
            $tmp_name = $images["tmp_name"];
            move_uploaded_file($tmp_name, $url . $date . $name);
            $img = $url . $date . $name;
            $Blog->image = $img;
        }

        $Blog->title = $title;
        $Blog->description = $description;
        $Blog->place = $place;
        $Blog->placetitle = $placetitle;
        $Blog->created_at = time();
        $Blog->insert();
        return true;
    }

    public function editblog($post, $images, $user_id)  {
        if(isset($post) && !empty($post)) {
            $id = isset($post['id']) ? $post['id'] : '';
            if($id) {
                $title = isset($post['title']) ? $post['title'] : '';
                $description = isset($post['description']) ? $post['description'] : '';
                
                $url = '../web/uploads/blog/';
                $date = uniqid().'_'.rand(9999, 99999).'_'.time();
               
                $Blog = Blog::find()->where([(string)'_id' => $id, 'user_id' => $user_id])->andWhere(['not','flagger', "yes"])->one();

                if(!empty($Blog)) {
                    if(isset($images['name']) && $images['name'] != '') {
                        $name = $images["name"]; 
                        $tmp_name = $images["tmp_name"];
                        move_uploaded_file($tmp_name, $url . $date . $name);
                        $img = $url . $date . $name;
                        $Blog->image = $img;
                    }

                    $Blog->title = $title;
                    $Blog->description = $description;
                    $Blog->modified_at = time();
                    $Blog->update(); 
                    return true;
                }
            }
        }
        return false;
    }
}