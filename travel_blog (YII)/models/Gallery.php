<?php
namespace frontend\models;
use yii\base\Model;
use Yii;
use yii\mongodb\ActiveRecord;

class Gallery extends ActiveRecord
{  
    public static function collectionName()
    {
        return 'gallery';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'image', 'title', 'description', 'location', 'tagged_connections', 'visible_to', 'created_at', 'modified_at', 'customids', 'hideids', 'store', 'post_id', 'type', 'place', 'placetitle', 'flagger', 'flagger_date', 'flagger_by'];
    }

    public function addGallery($data, $place, $placetitle, $user_id) {
        if(strstr($_SERVER['HTTP_REFERER'],'r=userwall')) {
            $type = 'userwall';
        } else  {
            $type = 'places';
        }

        $url = '../web/uploads/gallery/'; 

        foreach ($data as $key => $data) {
            $date = uniqid().'_'.rand(9999, 99999).'_'.time();
            $title = $data['$uploadpopupJIDSphototitle'];
            $description = $data['$uploadpopupJIDSdescription'];
            $location = $data['$uploadpopupJIDSlocation'];
            $taggedconnections = $data['$uploadpopupJIDStaggedconnections'];
            $visibleto = $data['$uploadpopupJIDSvisibleto'];
            $image = $data['image'];
            
            $Gallery = new Gallery();
            $Gallery->user_id = $user_id;

            if(!empty($image)) {
                if(isset($image['name']) && $image['name'] != '') {
                    $name = $image["name"]; 
                    $tmp_name = $image["tmp_name"];
                    move_uploaded_file($tmp_name, $url . $date . $name);
                    $img = $url . $date . $name;
                    $Gallery->image = $img;
                } else {
                    $Gallery->image = '';
                }
            } else {
                $Gallery->image = '';
            }

            $Gallery->title = $title;
            $Gallery->description = $description;
            $Gallery->location = $location;
            $Gallery->tagged_connections = $taggedconnections;
            $Gallery->visible_to = $visibleto;
            $Gallery->store = 'direct';
            $Gallery->place = $place;
            $Gallery->placetitle = $placetitle;
            $Gallery->type = $type;
            $Gallery->created_at = time();
            $Gallery->insert();
        }
    }

    public function getallgallery($id, $type) {
        return Gallery::find()->where(['user_id' => (string)$id, 'type' => $type])->andWhere(['not','flagger', "yes"])->asarray()->all(); 
    }

    public function getgallerydetail($id, $type) {
        $tempArray = array();
        if($type == 'PostForm') {
            $data = PostForm::find()->where([(string)'_id' => $id])->one();
            if(!empty($data)) {
                $tempArray['_id'] = isset($data['_id']) ? (string)$data['_id'] : '';
                $tempArray['title'] = isset($data['post_title']) ? $data['post_title'] : '';
                $tempArray['description'] = isset($data['post_text']) ? $data['post_text'] : '';
                $tempArray['location'] = isset($data['currentlocation']) ? $data['currentlocation'] : '';
                $tempArray['tagged_connections'] = isset($data['post_tags']) ? $data['post_tags'] : '';
                $tempArray['visible_to'] = isset($data['post_privacy']) ? $data['post_privacy'] : '';
                $tempArray['user_id'] = isset($data['post_user_id']) ? $data['post_user_id'] : '';
            }
        } else if($type == 'Gallery') {
            $data = Gallery::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($data)) {
                $tempArray['_id'] = isset($data['_id']) ? (string)$data['_id'] : '';
                $tempArray['title'] = isset($data['title']) ? $data['title'] : '';
                $tempArray['description'] = isset($data['description']) ? $data['description'] : '';
                $tempArray['location'] = isset($data['location']) ? $data['location'] : '';
                $tempArray['tagged_connections'] = isset($data['tagged_connections']) ? $data['tagged_connections'] : '';
                $tempArray['visible_to'] = isset($data['visible_to']) ? $data['visible_to'] : '';
                $tempArray['user_id'] = isset($data['user_id']) ? $data['user_id'] : '';
            }
        } else if($type == 'UserPhotos') {
            $data = UserPhotos::find()->where([(string)'_id' => $id])->one();
            if(!empty($data)) {                
                $tempArray['_id'] = isset($data['_id']) ? (string)$data['_id'] : '';
                $tempArray['title'] = isset($data['album_title']) ? $data['album_title'] : '';
                $tempArray['description'] = isset($data['post_text']) ? $data['post_text'] : '';
                $tempArray['location'] = isset($data['currentlocation']) ? $data['currentlocation'] : '';
                $tempArray['tagged_connections'] = isset($data['post_tags']) ? $data['post_tags'] : '';
                $tempArray['visible_to'] = isset($data['post_privacy']) ? $data['post_privacy'] : '';
                $tempArray['user_id'] = isset($data['post_user_id']) ? $data['post_user_id'] : '';
            }
        } else if($type == 'PlaceDiscussion') {
            $data = PlaceDiscussion::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($data)) {
                $tempArray['_id'] = isset($data['_id']) ? (string)$data['_id'] : '';
                $tempArray['title'] = isset($data['post_title']) ? $data['post_title'] : '';
                $tempArray['description'] = isset($data['post_text']) ? $data['post_text'] : '';
                $tempArray['location'] = isset($data['currentlocation']) ? $data['currentlocation'] : '';
                $tempArray['tagged_connections'] = isset($data['post_tags']) ? $data['post_tags'] : '';
                $tempArray['visible_to'] = isset($data['post_privacy']) ? $data['post_privacy'] : '';
                $tempArray['user_id'] = isset($data['post_user_id']) ? $data['post_user_id'] : '';
            }
        } else if($type == 'Collections') {
            $data = Collections::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one();
            if(!empty($data)) {
                $tempArray['_id'] = isset($data['_id']) ? (string)$data['_id'] : '';
                $tempArray['title'] = isset($data['post_title']) ? $data['title'] : '';
                $tempArray['description'] = isset($data['post_text']) ? $data['description'] : '';
                $tempArray['location'] = isset($data['location']) ? $data['location'] : '';
                $tempArray['tagged_connections'] = isset($data['tagged_connections']) ? $data['tagged_connections'] : '';
                $tempArray['visible_to'] = isset($data['visible_to']) ? $data['visible_to'] : '';
                $tempArray['user_id'] = isset($data['user_id']) ? $data['user_id'] : '';
            }
        }

        return json_encode($tempArray, true);
    }

    public function fetchgallerycategoriestaggeduser($id, $user_id) {
        $data = Gallery::find()->where([(string)'_id' => (string)$id])->andWhere(['not','flagger', "yes"])->asarray()->one(); 
        $result = array('success' => false);
        if(!empty($data)) {
            $tagged_connections = isset($data['tagged_connections']) ? $data['tagged_connections'] : '';
            $tagged_connections = explode(',', $tagged_connections);
            $tagged_connections = array_values(array_filter($tagged_connections));            

            $result = array('success' => true, 'tagged_connections' => $tagged_connections);
        }

        return json_encode($result, true);
    }

    public function galleryhidephoto($id, $user_id) {
        $data = Gallery::find()->where([(string)'_id' => $id])->andWhere(['not','flagger', "yes"])->one(); 

        if(!empty($data)) {
            $hideids = isset($data->hideids) ? $data->hideids : '';
            $hideids = explode(',', $hideids);
            $hideids = array_values(array_filter($hideids));

            $post_user_id = $data->user_id;
            //if($post_user_id != $user_id) {
                if(!in_array($user_id, $hideids)) {
                    $hideids[] = $user_id;
                }
            //}

            $hideids = implode(",", $hideids);

            $data->hideids = $hideids;
            $data->update();

            $result = array('success' => true);
            return json_encode($result, true);
        }

        $result = array('success' => false);
        return json_encode($result, true);
    }

    public function getgallerycommentlikecount($id, $user_id) {
        $like_buddies = Like::getLikeUser($id);
        $newlike_buddies = array(); 

        foreach($like_buddies as $like_buddy) {
            $newlike_buddies[] = ucwords(strtolower($like_buddy['user']['fullname']));
        }

        $newlike_buddiesImplode = '';
        if(!empty($newlike_buddies)) {
            $newlike_buddiesImplode = implode('<br/>', $newlike_buddies);  
        }
        $likesCount = count($newlike_buddies);

        $commentsCount = Comment::find()->where(['post_id' => (string)$id,'status' => '1','parent_comment_id'=>'0'])->count();
 
        $sendid = $id;
        $ids = explode('|||', $id);
        if(count($ids) == 2) {
            $sendid = $ids[0];
        }

        $isILiked = Like::find()->where(['post_id' => (string) $id,'status' => '1','user_id' => (string) $user_id])->one();
        if(!empty($isILiked)) {
            $likeIcon = 'mdi-thumb-up';
        } else {
            $likeIcon = 'mdi-thumb-up-outline';
        }

        $isICommented = Comment::isICommented((string)$id, $user_id);
        if(!empty($isICommented)) {
            $commentIcon ='mdi-comment';
        } else {
            $commentIcon ='mdi-comment-outline';
        }



        $result = array('commentsCount' => $commentsCount, 'likesCount' => $likesCount, 'likehtml' => $newlike_buddiesImplode, 'success' => true, 'tempid' => $sendid, 'likeIcon' => $likeIcon, 'commentIcon' => $commentIcon);

        return json_encode($result, true);        
    }

    public function likehtml($id) {
        $like_buddies = Like::getLikeUser($id);
        $newlike_buddies = array();

        foreach($like_buddies as $like_buddy) {
            $newlike_buddies[] = ucwords(strtolower($like_buddy['user']['fullname']));
        }

        $newlike_buddiesImplode = implode('<br/>', $newlike_buddies);  

        $likeHtml = 'No likes found.';
        if(!empty($newlike_buddies)) {
            if(count($newlike_buddies) == 1) {
                $likeHtml = '<a href="javascript:void(0)">'.$newlike_buddies[0].'</a> liked this.';
            } else if(count($newlike_buddies) == 2) {
                $likeHtml = '<a href="javascript:void(0)">'.$newlike_buddies[0] . '</a> and <a href="javascript:void(0)">' . $newlike_buddies[1].'</a> liked this.';
            } else {
                $likeHtml = '<a href="javascript:void(0)">'.$newlike_buddies[0] . '</a>, <a href="javascript:void(0)">' . $newlike_buddies[1] .'</a> and <a href="javascript:void(0)" data-title="'.$newlike_buddiesImplode.'">'.count($newlike_buddies) . '</a> more people liked this.';
            }
        }

        return $likeHtml;
    }
}