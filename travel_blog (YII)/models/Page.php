<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\models\PostForm;
use frontend\models\Like;
use frontend\models\Connect;
use frontend\models\PageVisitor;

class Page extends ActiveRecord
{  
    public static function collectionName()
    {
        return 'pages';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'category', 'page_name', 'short_desc', 'desc', 'site', 'page_photo', 'page_thumb', 'address', 'city', 'postal_code', 'email', 'phone', 'start_date', 'start_type', 'verify_text', 'verify_phone', 'company_email', 'created_date', 'updated_date', 'created_by', 'updated_by', 'created_from_ip', 'updated_from_ip', 'is_deleted', 'page_id', 'country_code', 'pgendorse', 'pgmail', 'gen_post_review', 'gen_photos_review', 'gen_post', 'gen_photos', 'gen_reviews', 'gen_msg_filter', 'gen_page_filter', 'not_add_post', 'not_add_comment', 'not_like_page', 'not_like_post', 'not_post_edited', 'not_get_review', 'not_msg_rcv', 'blk_restrct_list', 'blk_block_list', 'blk_restrct_evnt', 'blk_msg_filtering', 'msg_use_key', 'send_instant', 'send_instant_msg', 'show_greeting', 'show_greeting_msg', 'bsnesbtn', 'bsnesbtnvalue', 'fullname', 'cover_photo'];
    }
    
    public function getMyPages($user_id)
    {
        return Page::find()->where(['is_deleted' => '2'])->orwhere(['created_by' => $user_id,'is_deleted' => '1'])->orderBy(['created_date'=>SORT_DESC])->all();
    }
	
    public function getAllPages()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Page::find()->where(['is_deleted' => '1'])->orwhere(['is_deleted' => '2'])->orwhere(['is_deleted' => '0','created_by' => $user_id])->orderBy(['created_date'=>SORT_DESC])->all();
    }
	
    public function getMyLikesPages($user_id)
    {
        return Like::find()->where(['user_id' => $user_id, 'status' => '1', 'like_type' => 'page'])->orderBy(['created_date'=>SORT_DESC])->all();
    }  
	
    public function getMyLikesPagesCount($user_id)
    {
        $count = Like::find()->where(['user_id' => $user_id, 'status' => '1', 'like_type' => 'page'])->orderBy(['created_date'=>SORT_DESC])->all();
        return count($count);
    }
	
    public function getLastSixMyLikesPages($user_id)
    {
        return Like::find()->where(['user_id' => $user_id, 'status' => '1', 'like_type' => 'page'])->orderBy(['created_date'=>SORT_DESC])->limit(6)->offset(0)->all();
    }
	
    public function getAllLikesPages($user_id)
    {
        return Like::find()->where(['user_id' => $user_id, 'status' => '1', 'like_type' => 'page'])->orderBy(['created_date'=>SORT_DESC])->all();
    }
	
    public function getPageLikes($page_id)
    {
        $count = Like::find()->where(['post_id' => "$page_id", 'status' => '1', 'like_type' => 'page'])->all();
        return count($count);
    }
	
    public function Pagedetails($page_id)
    {
        return Page::find()->where([(string)'_id' => "$page_id"])->one();
    }
	
    public function getpagenameLikes($pageid)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');

        $user_pages_likes = Like::find()->where(['status'=>"1",'like_type'=>'page'])->andwhere(['not in','post_id',$pageid])->orderBy(['updated_date'=>SORT_DESC])->limit(3)->offset(0)->all();
        $newpage = '';
        if($user_pages_likes)
        {
            $newpage .= "People also liked";
            foreach($user_pages_likes as $user_pages_like)
            {
                $pageid = $user_pages_like['post_id'];
                $pagelink = Url::to(['page/index', 'id' => "$pageid"]);
                $pagedetail = Page::find()->where(['page_id' => $pageid])->one();
                $pagename = $pagedetail['page_name'];
                $newpage .= " <a href='".$pagelink."'>".$pagename."</a>,";
            }
            $newpage = substr($newpage,0,-1);
        }
        else
        {
            $newpage .= "No likes were getting for other pages";
        }
        return $newpage;
    }
	
    public function getPageLikeDetails($page_id)
    {
        return Like::find()->with('user')->where(['post_id' => "$page_id", 'status' => '1', 'like_type' => 'page'])->all();
    }
    
    public function getConnectList($page_id)
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Connect::find()->where(['from_id' => "$user_id", 'status' => '1'])->orderBy(['updated_date'=>SORT_DESC])->limit(6)->offset(0)->all();
    }
    
	public function getLastWeekLikeCount($post_id)
    {
        $dateee = strtotime("-14 day");
        $date = date('Y-m-d', $dateee);
        $datee = strtotime("-7 day");
        $date1 = date('Y-m-d', $datee);
        $likes = Like::find()->where(['post_id' => $post_id,'status' => '1'])->andwhere(['liked_modified'=> ['$gte'=>"$date"]])->andwhere(['liked_modified'=> ['$lte'=>"$date1"]])->all();
        return count($likes);
    }
	
     public function getCurrentWeekLikeCount($post_id)
    {
        $datee = strtotime("-7 day");
        $date = date('Y-m-d', $datee);
        $likes = Like::find()->where(['post_id' => $post_id,'status' => '1'])->andwhere(['liked_modified'=> ['$gte'=>"$date"]])->all();
        return count($likes);
    }
	
     public function getLikeCountGraph($post_id,$time)
    {
        $date = $time;
        $likes = Like::find()->where(['post_id' => $post_id,'status' => '1'])->andwhere(['like','liked_modified',"$date"])->all();
        return count($likes);
    }
    
	public function getInsightViewCount($page_id,$date)
    {
        $date1 = date('Y-m-d h:i:s',strtotime($date . " -1 month"));
        $likes = PageVisitor::find()->where(['page_id' => $page_id,'status' => '1'])->andwhere(['date'=> ['$gte'=>"$date1"]])->andwhere(['date'=> ['$lte'=>"$date"]])->all();
        return count($likes);
    }
	
     public function getInsightLikeCount($page_id,$date)
    {
        $date1 = date('Y-m-d h:i:s',strtotime($date . " -1 month"));
        $likes = Like::find()->where(['between', 'liked_modified', "$date1", "$date" ])->andwhere(['post_id' => $page_id,'status' => '1'])->count();
        return $likes;
    }
	
    public function getPageReviews($page_id)
    { 
        return PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
	public function getPageReviewPerson($page_id)
	{
		$result =  ArrayHelper::map(PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->all(), function($data) { return $data['post_user_id'];}, function($data) { return $data;});
		return $result;
	}
	
    public function getPageReviewsCount($page_id)
    {
        return PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->count();
    }
	
	public function getPageReviewsCountPerson($page_id)
    {
        $result = ArrayHelper::map(PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->all(), function($data) { return $data['post_user_id'];}, function($data) { return $data;});
		return $result;
    }
	
    public function getPageReviewsLastMonthCount($page_id)
    {
        $lastmonth = strtotime("-1 month");
        $lasttwomonth = strtotime("-2 months");
        return PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->andwhere(['post_created_date'=> ['$lte'=>"$lastmonth"]])->andwhere(['post_created_date'=> ['$gte'=>"$lasttwomonth"]])->orderBy(['post_created_date'=>SORT_DESC])->count();
    }
    
    public function getLastThreePageReviews($page_id)
    {
        return PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->limit(3)->offset(0)->all();
    }
    
    public function getSpecicficPageReviewsCount($page_id,$num)
    {
        return PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'rating'=>"$num",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->count();
    }
    
    public function getPageReviewsSum($page_id)
    {
        $vals = PostForm::find()->where(['is_deleted'=>"0",'is_page_review'=>"1",'page_id'=>$page_id])->orderBy(['post_created_date'=>SORT_DESC])->all();
        $sum = 0;
        foreach($vals as $val)
        {
            $sum += $val['rating'];
        }
        return $sum;
    }
    
	public function getLastYearPageLikes($pageid,$date)
    {
        return Like::find()->where(['post_id'=>"$pageid"])->andwhere(['liked_modified'=> ['$gte'=>"$date"]])->count();
    }
	
    public function getPromoPages()
    {
        return PostForm::find()->where(['is_ad'=>"1"])->andwhere(['like','adobj','page'])->orderBy(['post_created_date'=>SORT_DESC])->all();
    }
	
	public function getFlagpage()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        return Page::find()->where(['is_deleted' => '2'])->orderBy(['created_date'=>SORT_DESC])->all();
    }

    public function getpageinfochat($id)
    {
        $result = array('status' => false);
        if($id) {
            $info = Page::find()->select(['page_id', 'created_by'])->where(['page_id' => $id])->asarray()->one();
            if(!empty($info)) {
                $pageOWNER = $info['created_by'];
                $pageID = $info['page_id'];
                $result = array('status' => true, 'pageID' => $pageID, 'pageOWNER' => $pageOWNER);
                return json_encode($result, true);
                exit;
            }
        }
        return json_encode($result, true);
        exit;
    } 

    public function checkpageid($pageId, $userid)
    {
        $result = array('status' => false);
        if($pageId) {
            $count = Page::find()->where(['page_id' => $pageId, 'created_by' => $userid])->count();
            if($count>0) {
                $result = array('status' => true);
                return json_encode($result, true);
                exit;
            }
        }
        return json_encode($result, true);
        exit;
    }  

    public function isPage($id)
    {
        if($id) {
            $count = Page::find()->where(['page_id' => $id])->count();
            if($count>0) {
                return true;
                exit;
            }
        }
        return false;
        exit;
    }

    public function getlogo($id)
    {
        if($id) {
            $pageData = Page::find()->where(['page_id' => $id])->one();
            if(!empty($pageData)) {
                $logo = isset($pageData['page_thumb']) ? $pageData['page_thumb'] : 'demo-business.jpg';
                $logo = 'profile/'.$logo;
                return $logo;
            }
        }

        $logo = 'profile/demo-business.jpg';
        return $logo;
    }
}