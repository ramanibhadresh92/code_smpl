<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use frontend\models\Like;
use frontend\models\SavePost;
use frontend\models\ReportPost;
use frontend\models\TurnoffNotification;
use frontend\models\PinImage;
use frontend\models\Order;
use frontend\models\HidePost;

class LocaldriverReview extends ActiveRecord
{
    public static function collectionName()
    {
        return 'localdriver_review';
    }

    public function attributes()
    {
		return ['_id','post_type', 'post_ip', 'post_title', 'post_text', 'shared_by','post_status', 'post_created_date', 'post_user_id','link_title','link_description','share_by','shared_from','post_privacy','is_timeline','is_deleted','currentlocation','parent_post_id','is_coverpic','share_setting','comment_setting', 'post_tags','custom_share', 'custom_notshare', 'anyone_tag', 'is_profilepic', 'pagepost', 'is_page_review', 'rating', 'page_id', 'collection_id', 'event_id', 'trav_cat', 'trav_link', 'trav_price', 'trav_item', 'is_ad', 'ad_type', 'post_flager_id' , 'is_trip', 'country', 'continent', 'adobj', 'adname', 'adid', 'adcatch', 'adheadeline', 'adtitle', 'adtext', 'adlogo', 'adimage', 'adsubcatch', 'adurl', 'adbtn', 'adlocations', 'adminage', 'admaxage', 'adlanguages', 'admale', 'adfemale', 'adpro', 'adint', 'adbudget', 'adruntype', 'adstartdate', 'adenddate', 'rate_impression', 'rate_action', 'rate_click', 'placetype', 'placetitlepost', 'placereview','page_owner','adtotbudget','customids','pinned', 'post_id'];
    }
}