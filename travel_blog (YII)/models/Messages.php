<?php
namespace frontend\models;
use Yii;
use yii\helpers\Url;
use yii\base\Model;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\UserForm;
use frontend\models\Friend;
use frontend\models\MessageBlock;
use frontend\models\Vip;
use frontend\models\Credits;
use frontend\models\SecuritySetting;
use frontend\models\Page;
 
class Messages extends ActiveRecord
{
    public static function collectionName()
    {
        return 'messages';
    }

    public function attributes() {
        return ['_id', 'reply', 'type', 'from_id', 'to_id', 'con_id','is_read', 'category', 'created_at', 'to_id_del', 'from_id_del', 'to_id_read', 'to_id_flush', 'from_id_flush', 'from_id_read', '__v'];
    }

    public function getimage($userid)
    {
        $resultimg = LoginForm::find()->where(['_id' => $userid])->one();
        if(substr($resultimg['photo'],0,4) == 'http')
        {
                $dp = $resultimg['thumbnail'];
        
        } else {
              
            $assetsPath = '../../vendor/bower/travel/images/';

            if(isset($resultimg['thumbnail']) && $resultimg['thumbnail'] != '' && file_exists('profile/'.$resultimg['thumbnail']))
            {
                $dp = "profile/".$resultimg['thumbnail'];
            }
            else if(isset($resultimg['gender']) && !empty($resultimg['gender']) && file_exists($assetsPath.$resultimg['gender'].'.jpg'))
            {
                $dp = $assetsPath.$resultimg['gender'].'.jpg';
            }
            else
            {
                $dp = $assetsPath."DefaultGender.jpg";
            }
        }
        return $dp;
    }

    public function getInfoForSelf($post) {
        $newpost = [];
        $int = 0;
        foreach ($post as $key => $data) {
            if(!empty($data)) {
                $id= (string)$data['id'];
                $userinfo = UserForm::find()
                ->select(['fullname', 'country', 'gender'])
                ->where([(string)'_id' => $id, 'status' => '1'])
                ->asarray()
                ->one();

                if(!empty($userinfo)) {
                    $thumbnail = Yii::$app->GenCls->getimage($id, 'thumb');
                    $fullname = ucwords(strtolower($userinfo['fullname']));
                    $data['fullname'] = $fullname;
                    $data['thumbnail'] = $thumbnail;
                    $newpost[] = $data;
                }
            }
            $int++;

        }
        return json_encode($newpost, true);
    }

    public function getInfoForAll($post, $user_id) {
        $newInfo = array(); 
        if(!empty($post)) {
            reset($post);
            $status = key($post);
            $id = $post[$status];
            $info = UserForm::find()->select(['fullname', 'gender', 'photo', 'thumbnail'])->where(['_id' => $id, 'status' => '1'])->asArray()->one();
            if(!empty($info)) {   
                $thumbnail = Yii::$app->GenCls->getimage($id, 'thumb');
                $fullname = ucwords(strtolower($info['fullname']));
                $newInfo['id'] = $id;
                $newInfo['fullname'] = $fullname;
                $newInfo['thumbnail'] = $thumbnail; 
                $newInfo['status'] = $status;

                // check for mute user or not...
                $SecuritySetting = SecuritySetting::find()->where(["user_id" => $user_id])->one();
                if(!empty($SecuritySetting)) {
                    if(isset($SecuritySetting['mute_users'])) {
                        $muteData = $SecuritySetting['mute_users'];
                        $muteData = explode(",", $muteData);
                        if(in_array($id, $muteData)) {
                            $newInfo['status'] = 'mute';
                        }
                    }

                    if(isset($SecuritySetting['message_filtering']) && $SecuritySetting['message_filtering'] != '') { 
                        $message_filtering = $SecuritySetting['message_filtering'];
                        $message_filtering = explode(",", $message_filtering);
                        if(in_array($id, $message_filtering)) {
                            $newInfo['status'] = 'block';
                        }
                    }
                }

            }
        }
        return json_encode($newInfo, true);
        exit;
    }

     public function getLoadHistoryMessage($post, $user_id) {
        $result = [];
        if(!empty($post)) {
            $socket = array();

            if($post['from_id'] == $user_id) {
                $ownerID = $post['from_id'];
                $otherID = $post['to_id'];
            } else {
                $ownerID = $post['to_id'];
                $otherID = $post['from_id'];
            }

            $category = isset($post['category']) ? $post['category'] : '';   
            $start = $post['start'];
            $limit = $post['limit'];
            if($category == 'page') {
                $otherID = $user_id;
            }
            $FTCombine = array($ownerID, $otherID);
            $users = Yii::$app->GenCls->msgGetIdsDATA($FTCombine);
            
            if(!empty($users)) {
                $socket = Messages::find()
                ->where(['from_id' => $ownerID,'to_id' => $otherID,'from_id_del' => 0,'from_id_flush' => 0])
                ->orwhere(['from_id' => $otherID,'to_id' => $ownerID,'to_id_del' => 0,'to_id_flush' => 0])
                ->orderBy('created_at DESC')
                ->limit($limit)
                ->offset($start)
				->asarray()
                ->all();

                if(!empty($socket)) {
                    for ($i=0; $i < count($socket); $i++) { 
                        $newFrom_id = $socket[$i]['from_id'];
                        $fullname = $users[$newFrom_id]['fullname'];
                        $thumb = $users[$newFrom_id]['thumb'];

                        $socket[$i]['_id'] = (string)$socket[$i]['_id'];
                        $Create_date1 = $socket[$i]['created_at']->toDateTime();
                        $socket[$i]['created_at'] = strtotime($Create_date1->format('r').' UTC');
                        $socket[$i]['thumb'] = $thumb;
                        $socket[$i]['fullname'] = $fullname;
                        $fullnameAry = explode(' ',$fullname);
                        $fname = $fullnameAry[0];
                        $socket[$i]['fname'] = $fname;
                    }
                }
                return json_encode($socket, true);
            }
        }
    }

     public function getLoadHistoryChat($post, $user_id) {
        $newCreatePost = [];
        
        if(!empty($post)) {
            $ids = [];
            $from_id = $post['from_id'];   
            $to_id = $post['to_id'];   
            $start = $post['start'];
            $limit = $post['limit'];

            $from_idData = UserForm::find()->select(['fullname'])->where([(string)'_id' => $from_id])->one();
            if(!empty($from_idData)) {
                $fullname = $from_idData['fullname'];
                $thumb = Yii::$app->GenCls->getimage($from_id,'thumb');
                $ids[$from_id]['fullname'] = $fullname;
                $ids[$from_id]['thumb'] = $thumb;

                $to_idData = UserForm::find()->select(['fullname'])->where([(string)'_id' => $to_id])->one();
                if(!empty($to_idData)) {
                    $fullname = $to_idData['fullname'];
                    $thumb = Yii::$app->GenCls->getimage($from_id,'thumb');
                    $ids[$to_id]['fullname'] = $fullname;
                    $ids[$to_id]['thumb'] = $thumb;
                }
            }

            if(!empty($ids)) {
                $socket = Messages::find()
                ->where(['from_id' => $from_id,'to_id' => $to_id])
                ->orwhere(['from_id' => $to_id,'to_id' => $from_id])
                ->orderBy('created_at DESC')
                ->limit($limit)
                ->offset($start)
                ->all();
                
                if(!empty($socket)) {
                    foreach ($socket as $key => $socval) {
                        $current = [];
                        $newFrom_id = $socval['from_id'];
                        $fullname = $ids[$newFrom_id]['fullname'];
                        $thumb = $ids[$newFrom_id]['thumb'];

                        $current['_id'] = (string)$socval['_id'];
                        $current['reply'] = $socval['reply'];
                        $current['fullname'] = $fullname;
                        $fullnameAry = explode(' ',$fullname);
                        $fname = $fullnameAry[0];
                        $current['fname'] = $fname;
                        $current['thumb'] = $thumb;
                        $current['type'] = $socval['type'];
                        $current['from_id'] = $socval['from_id'];
                        $current['to_id'] = $socval['to_id'];
                        $Create_date1 = $socval['created_at']->toDateTime();
                        $current['created_at'] = strtotime($Create_date1->format('r').' UTC');
                        $newCreatePost[] = $current;
                    }
                }
            }
        }
        return json_encode($newCreatePost, true);
        exit;
    }

    public function deletesocketconversation($id, $user_id) {
        $demo = MessageBlock::find()->select(['con_id'])->where(['from_id' => $user_id, 'to_id' => $id])->orwhere(['from_id' => $id, 'to_id' => $user_id])->asArray()->one();
        $con_id = isset($demo['con_id']) ? $demo['con_id'] : '';
        Messages::updateAll(['to_id_del' => 1, 'to_id_flush' => 1], ['con_id' => $con_id, 'to_id' => (string)$user_id]);
        Messages::updateAll(['from_id_del' => 1, 'from_id_flush' => 1], ['con_id' => $con_id, 'from_id' => (string)$user_id]);
        return true;
        exit;
    }

    public function getFriendsUserIds($user_id){
        $query = ArrayHelper::map(Connect::find()->where(['to_id' => $user_id])->all(), 'from_id', 'to_id');
        $ids = array_keys($query);
        return $ids; exit;
    }

    public function getSearchResult($key, $user_id) {
        $friendIdsBox = array($user_id);
        $query = UserForm::find()
        ->select([(string)'_id', 'fullname', 'country'])
        ->Where(['like', 'fullname', $key])
        ->orwhere(['like','fname', $key])
        ->orwhere(['like','lname', $key])
        ->andwhere(['status' => '1'])
        ->andwhere(['not in', (string)'_id', $friendIdsBox])
        ->asarray()
        ->all(); 

        $newResult = array();
        if($key != '') {
            $muteUsers = array();
            $messageFiltering = array();

            $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($SecuritySetting)) {
                $muteUsers = isset($SecuritySetting['mute_users']) ? $SecuritySetting['mute_users'] : '';
                $muteUsers = explode(',', $muteUsers);
                
                $messageFiltering = isset($SecuritySetting['message_filtering']) ? $SecuritySetting['message_filtering'] : '';
                $messageFiltering = explode(',', $messageFiltering);
            }

            foreach ($query as $value) {
                $id = (string)$value['_id'];
                $name = trim($value['fullname']);
                if (stripos($name, $key) === 0) {
                    $isMute = 'no';
                    if(in_array($id, $muteUsers)) {
                        $isMute = 'yes';
                    }
                    $value['isMute'] = $isMute;

                    $isBlock = 'no';
                    if(in_array($id, $messageFiltering)) {
                        $isBlock = 'yes';
                        $value['blockprofile_img'] = 'blockprofile_img';
                        $value['blockicon_img'] = '<i class="mdi mdi-cancel blockicon_img blockicon_img"></i>';
                    } else {
                        $value['blockprofile_img'] = '';
                        $value['blockicon_img'] = '';    
                    }
                    $value['isBlock'] = $isBlock;

                    $thumb = Yii::$app->GenCls->getimage($id,'thumb');
                    $value['thumb'] = $thumb;
                    $value['id'] = $id;
                    $newResult[] = $value;
                }
            }
        }

        return $newResult;
        exit;
    }
    
    public function getSearchResultFORPAGE($post, $user_id) {
        if(!empty($post)) {
            $key = $post['key'];
            $pageID = $post['pageID'];
            $isCreatedPage = Page::find()->where(['page_id' => $pageID, 'created_by' => $user_id])->count();
            if($isCreatedPage>0) {
                $idsBulk = ArrayHelper::map(MessageBlock::find()->where(['to_id' => $pageID])->asArray()->all(),'from_id', 'from_id');

                if(!empty($idsBulk)) {
                    $query = UserForm::find()->select([(string)'_id', 'fullname', 'country'])->where(['in', (string)'_id', $idsBulk])->andwhere(['status' => '1'])->andWhere(['or', ['like', 'fname', $key], ['like', 'lname', $key],])->asarray()->all();

                    for ($i=0; $i <count($query); $i++) { 
                        $id = (string)$query[$i]['_id'];
                        $thumb = Yii::$app->GenCls->getimage($id,'thumb');
                        $query[$i]['thumb'] = $thumb;
                        $query[$i]['id'] = $id;
                    }
                    $result = array('status' => true, 'data' => $query);
                    return json_encode($result, true);
                    exit;
                }
            }
        }

        $result = array('status' => false);
        return json_encode($result, true);
        exit;
    }

    public function isreadmessage($from_id, $user_id) {
        $idBuls = array($user_id);
        $pageInfo = ArrayHelper::map(Page::find()->select(['page_id'])->where(['created_by' => $user_id])->asarray()->all(), 'page_id', 'page_id');
        if(!empty($pageInfo)) {
            $idBuls = array_merge($idBuls, $pageInfo);
        }

        $info = array();
        Messages::updateAll(['is_read' => 0], ['from_id' => $from_id], ['in', 'to_id', $idBuls]);
        $values = ArrayHelper::map(Messages::find()->select(['from_id'])->where(['in', 'to_id', $idBuls])->andwhere(['is_read' => 1])->asarray()->all(), 'from_id', 'from_id');
        if(isset($values) && !empty($values)) {
            $values = count($values);
        }
        $info = array('status' => true, 'count' => $values);
        return $info;
    }

    public function setreadall($user_id) {
        Messages::updateAll(['is_read' => 0], ['to_id' => $user_id]);
        return true;
    }

    public function setisreadmessage($msgid, $user_id) {
        if($msgid) {
            $currentStatus = Messages::find()->select(['is_read'])->where([(string)'_id' => $msgid])->asarray()->one();
            if(!empty($currentStatus)) {
                $is_read = $currentStatus['is_read'];
                $is_read = ($is_read === 1) ? 0 : 1;

                // Merge ids with page also
                $idBuls = array($user_id);
                $pageInfo = ArrayHelper::map(Page::find()->select(['page_id'])->where(['created_by' => $user_id])->asarray()->all(), 'page_id', 'page_id');
                if(!empty($pageInfo)) {
                    $idBuls = array_merge($idBuls, $pageInfo);
                }

                Messages::updateAll(['is_read' => $is_read], [(string)'_id' => $msgid], ['in', 'to_id' => $idBuls]);
                return true;
                exit;
                
            }
            
        }
        return false;
        exit;
    }

    public function getinfoforsingle($id) {
        if($id) {
            $info = UserForm::find()
            ->select(['fullname'])
            ->where(['_id' => $id])
            ->asarray()
            ->one();
            $thumbnail = Yii::$app->GenCls->getimage($id,'thumb');
            $info['thumbnail'] = $thumbnail;
        
            return json_encode($info, true);
            exit;
        }

    }

    public function matchWithPrevious($id, $user_id) {
       $msgsArray = Messages::find()->where(['from_id' => $id, 'to_id' => $user_id, 'to_id_del' => 0])->orwhere(['from_id' => $user_id, 'to_id' => $id, 'from_id_del' => 0])->orderBy(['_id' => SORT_DESC])->asarray()->limit(2)->all();

       $result = array('isSameDate' => false, 'difftime' => 0, 'isSameSender' => false);
       if(!empty($msgsArray) && count($msgsArray) == 2) {
            $Create_date1 = $msgsArray[0]['created_at']->toDateTime();
            $Strtotime_date1 = strtotime($Create_date1->format('r').' UTC');
            $date1 = date("Y-m-d", $Strtotime_date1);

            $Create_date2 = $msgsArray[1]['created_at']->toDateTime();
            $Strtotime_date2 = strtotime($Create_date2->format('r').' UTC');
            $date2 = date("Y-m-d", $Strtotime_date2);

            if($date1 == $date2) {
                if($msgsArray[0]['from_id'] == $msgsArray[1]['from_id']) {
                    $result['isSameSender'] = true;
                }

                $result['isSameDate'] = true;
                $diff = abs($Strtotime_date1 - $Strtotime_date2);
                $diff = round($diff / 60);
                $result['difftime'] = $diff;
            }
       } 
       
       return $result;
    }

    public function userbasicinfo($id, $user_id) {
         if($id) {
            $info = UserForm::find()
            ->select(['fullname', 'gender', 'status'])
            ->where(['_id' => $id])
            ->asarray()
            ->one();

            $thumbnail = Yii::$app->GenCls->getimage($id,'thumb');
            $info['thumbnail'] = $thumbnail;
            $info['result'] = true;

            $match = Messages::matchWithPrevious($id, $user_id);
            $combineArray = array_merge($info, $match);
            return json_encode($combineArray, true);
            exit;
        }
    }

    public function getinfoformultiple($ids) {
        $result = array();
        if($ids) {
            $info = UserForm::find()
            ->select(['fullname'])
            ->where(['in', '_id', $id])
            ->asarray()
            ->all();

            if(!empty($ids)) {
                foreach ($info as $key => $value) {
                    $tempid = (string)$value['_id']; 
                    $thumbnail = Yii::$app->GenCls->getimage($tempid,'thumb');
                    $value['thumbnail'] = $thumbnail;
                    $result[] = $value;
                }

                return json_encode($result, true);
                exit;
            }
        }
        
    }

    public function getUnreadMsg($user_id) {
        if($user_id) {
            $idBuls = array($user_id);
            $pageInfo = ArrayHelper::map(Page::find()->select(['page_id'])->where(['created_by' => $user_id])->asarray()->all(), 'page_id', 'page_id');
            if(!empty($pageInfo)) {
                $idBuls = array_merge($idBuls, $pageInfo);
            }

            $info = Messages::find()->select(['from_id'])->where(['in', 'to_id', $idBuls])->andWhere(['is_read' => 1])->asarray()->all();
            $uniqData = [];
            foreach ($info as $key => $value) {
                $key = $value['from_id'];
                $uniqData[$key] = 1;
            }
            return count($uniqData);
        }
    }    

    public function getCategory($user_id, $id) {
        $ids = Messages::getFriendsUserIds($user_id);
        if(!empty($ids)) {
            if(in_array($id, $ids)) {
                // It meance Friends
                return 'inbox';
                exit;
            } else {
                //check is VIP...
                $isVIP = Vip::isVIP($id);
                if($isVIP) {
                    return 'inbox';
                    exit;
                } else {
                    // check it is page or not.
                    $isPage = Page::isPage($id);
                    if($isPage) {
                        return 'page';
                        exit;
                    } else {
                        return 'others';
                        exit;
                    }
                }
            }
        }
    }

    public function getCategoryWithGift($user_id, $id) {
        // Check User have creadit or not......
        $totalcredits = Credits::usertotalcredits();
        $total = (isset($totalcredits[0])) ? $totalcredits[0]['totalcredits'] : '0';
        if($total >=25) {
            return true;
            exit; 
        } else {
            return false;
            exit;
        }
    }

    public function getThread($id, $user_id) {
        $con_id = MessageBlock::find()->select(['con_id'])->where(['from_id' => $id, 'to_id' => (string)$user_id])->orwhere(['from_id' => (string)$user_id, 'to_id' => $id])->asarray()->one();
        $con_id = isset($con_id['con_id']) ? $con_id['con_id'] : '';
        if($con_id >= 0) {

            $images = Messages::find()->select(['reply'])->where(['con_id' => (string)$con_id, 'type' => 'image'])->asarray()->all();
            $newimages = array();

            if(!empty($images)) {
                foreach ($images as $key => $value) {
                    $file = $value['reply'];
                    if($file) {
                        $newimages[] = $file;
                    }
                }
            }
            return json_encode($newimages);
            exit;
        }
    }

    public function deleteselectedmessage($ids, $user_id) {
        if(!empty($ids)) {
            foreach ($ids as $key => $id) {
                $cr = Messages::findOne($id);
                $from_id = $cr['from_id'];
                $to_id = $cr['to_id'];
 
                if($from_id == (string)$user_id || $to_id == (string)$user_id) {
                    if($from_id == (string)$user_id) {
                        $key = 'from_id_del';
                    } else {
                        $key = 'to_id_del';
                    }

                    $cr->$key = 1;
                    $cr->update();
                }
            }
            return true;
        }
    }

    /* ========== START strophe loaded ==================
    public function recentMessagesUserList($user_id, $getRecentUsers) { 
        $result = array('status' => false);
        $redisData = array();
        $mergedData = array();

        if(isset($getRecentUsers) && !empty($getRecentUsers)) {
            //$getRecentUsers = array_reverse($getRecentUsers);
            foreach ($getRecentUsers as $getRecentUsersS) {
                if(UserForm::isJSON($getRecentUsersS)) {
                    $data = json_decode($getRecentUsersS, true);
                    $from = $data['from'];
                    $to = $data['to'];
                    $message = $data['message'];
                    if($user_id == $from) {
                        $other = $to;
                        $isSenderSelf = true;
                    } else {
                        $isSenderSelf = false;
                        $other = $from;
                    }
                    $prepareData = array();
                    $prepareData['to'] = $to;
                    $prepareData['from'] = $from;
                    $prepareData['message'] = $message;
                    $prepareData['isSenderSelf'] = $isSenderSelf;
                    $prepareData['other'] = $other;
                    $redisData[$other] = $prepareData;
                    //$keys[]
                }
            }   
        }
        
        if(!empty($redisData)) {
            $redisIDs = array_keys($redisData);

            // check user exist in archive...
            $archiRecord = SecuritySetting::find()->select(['archive_users'])->where(['user_id' => $user_id])->asarray()->one();
            if($archiRecord) {
                $archiRecord = isset($archiRecord['archive_users']) ? $archiRecord['archive_users'] : '';
                if($archiRecord) {
                    $archiRecord = explode(',', $archiRecord);
                    $redisIDs = array_diff($redisIDs, $archiRecord);
                }
            }

            $UserForm = ArrayHelper::map(UserForm::find()->select(['thumbnail', 'fullname', 'gender'])->where(['in', (string)'_id', $redisIDs])->asarray()->all(), function($data) { return (string)$data['_id']; }, function($data) { return $data; });
            $mergedData = array_values(array_merge_recursive($redisData, $UserForm));   
        }

        return json_encode($mergedData, true);
    }
    ============ END strophe loaded =====================*/

    /* ========== START node loaded ================== */
    public function recentMessagesUserList($user_id) {
        $newArray = array();
        $con_ids = MessageBlock::find()->where(['from_id' => $user_id])->orwhere(['to_id' => $user_id])->orderBy(['created_at' => SORT_DESC])->asarray()->all();
        $data = array();
        
        // check user is friends.....
        // $friendsIds = Connect::getfriendids($user_id);
        
        $archiveUsers = array();
        $muteUsers = array();
        $messageFiltering = array();
        $assetsPath = '../../vendor/bower/travel/images/';

        $SecuritySetting = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
        if(!empty($SecuritySetting)) {
            $archiveUsers = isset($SecuritySetting['archive_users']) ? $SecuritySetting['archive_users'] : '';
            $archiveUsers = explode(',', $archiveUsers);
            
            $muteUsers = isset($SecuritySetting['mute_users']) ? $SecuritySetting['mute_users'] : '';
            $muteUsers = explode(',', $muteUsers);
            
            $messageFiltering = isset($SecuritySetting['message_filtering']) ? $SecuritySetting['message_filtering'] : '';
            $messageFiltering = explode(',', $messageFiltering);
        }

        foreach ($con_ids as $key => $value) {
            $con_id = $value['con_id'];
            $otherID = $value['from_id'];
            if($otherID == $user_id) {
                $otherID = $value['to_id'];
            }

            /*if(!in_array($otherID, $friendsIds)) {
                continue;
            }*/

            if(in_array($otherID, $archiveUsers)) {
                continue;
            }

            $info = Messages::find()->where(['con_id' => $con_id])->orderBy(['_id' => SORT_DESC])->asarray()->one();
            
            if(!empty($info)) {
                $id = $value['to_id'];
                if($id == $user_id) {
                    $id = $value['from_id'];
                    if($info['to_id_flush'] == 1) {
                        continue;
                    }

                    if($info['to_id_del'] == 1) {
                        $info['reply'] = '';
                    }
                } else {
                    if($info['from_id_flush'] == 1) {
                        continue;
                    }

                    if($info['from_id_del'] == 1) {
                        $info['reply'] = '';
                    }
                }

                $isMute = 'no';
                if(in_array($otherID, $muteUsers)) {
                    $isMute = 'yes';
                }
                $info['isMute'] = $isMute;

                // working with created_at
                $Create_date1 = $info['created_at']->toDateTime();
                $Strtotime_date1 = strtotime($Create_date1->format('r').' UTC');
                $info['created_at'] = $Strtotime_date1;


                
                $isBlock = 'no';
                if(in_array($otherID, $messageFiltering)) {
                    $isBlock = 'yes';
                    $info['blockprofile_img'] = 'blockprofile_img';
                    $info['blockicon_img'] = '<i class="mdi mdi-cancel blockicon_img blockicon_img"></i>';
                } else {
                    $info['blockprofile_img'] = '';
                    $info['blockicon_img'] = '';
                    
                }
                $info['isBlock'] = $isBlock;

                $unreadmsgcount = Messages::find()->where(['con_id' => $con_id, 'to_id' => $user_id, 'is_read' => 1])->count();
                $info['unreadmsgcount'] = $unreadmsgcount;

                $user = Userform::find()->select(['fullname', 'thumb', 'country'])->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($user)) {
                    $dp = Yii::$app->GenCls->getimage($id, 'thumb');
                    $info['user_id'] = $id;
                    $info['thumb'] = $dp;
                    $info['fullname'] = $user['fullname'];
                    $info['country'] = isset($user['country']) ? $user['country'] : '';
                    $newArray['result'][] = $info;
                }
            }
        }
        
        return json_encode($newArray, true);
        exit;
    }
    /* ========== END node loaded ================== */

    public function recentChatUserList($user_id) {
        $newArray = array();
        if($user_id) {
            $con_ids = MessageBlock::find()->where(['from_id' => $user_id])->orwhere(['to_id' => $user_id])->asarray()->all();
            $data = array();
            foreach ($con_ids as $key => $value) {
                $con_id = $value['con_id'];
                $info = Messages::find()->select(['_id'])->where(['con_id' => $con_id])->orderBy(['_id' => SORT_DESC])->asarray()->one();
                if($info) {
                    $data[] = (string)$info['_id'];
                }
            }

            $info = Messages::find()->where(['in', '_id', $data])->orderBy(['created_at' => SORT_DESC])->asarray()->all();
            foreach ($info as $key => $value) {
                $id = $value['to_id'];
                if($id == $user_id) {
                    $id = $value['from_id'];
                }

                $user = Userform::find()->where([(string)'_id' => $id])->asarray()->one();
                if(!empty($user)) {
                    $dp = Yii::$app->GenCls->getimage($id, 'thumb');
                    $user['id'] = (string)$user['_id'];
                    $user['thumb'] = $dp;
                    $newArray[] = $user;
                }
            }
            return json_encode($newArray, true);
        }
    }

    public function getRecentMessagesUsers($user_id) {
        $conIdBulk = array();
        if($user_id) {
            $idBuls = array($user_id);

            $conIdBulkArray = ArrayHelper::map(MessageBlock::find()->select(['con_id'])->where(['in', 'from_id', $idBuls])->orwhere(['in', 'to_id', $idBuls])->asarray()->all(), function($scope) { return (string)$scope['_id'];}, 'con_id');
            if(!empty($conIdBulkArray)) {
                foreach ($conIdBulkArray as $key => $value) {
                    $con_id = $value;
                    $message = Messages::find()->where(['con_id' => $con_id])->orderBy(['created_at' => SORT_DESC])->asArray()->one();
                    if(!empty($message)) {
                        $id = $message['from_id'];
                        if($user_id == $id) {
                            $id = $message['to_id'];
                        }

                        // check user exist in archive...
                        $archiRecord = SecuritySetting::find()->select(['archive_users'])->where(['user_id' => $user_id])->asarray()->one();
                        if($archiRecord) {
                            $archiRecord = isset($archiRecord['archive_users']) ? $archiRecord['archive_users'] : '';
                            if($archiRecord) {
                                $archiRecord = explode(',', $archiRecord);
                                if(!empty($archiRecord)) {
                                    if(in_array($id, $archiRecord)) {
                                        continue;
                                    }
                                }
                            }
                        }

                        $userInfo = Userform::find()->select(['fullname'])->where([(string)'_id' => $id])->asarray()->one();
                        if(!empty($userInfo)) {
                            
                            $id = (string)$userInfo['_id'];
                            $thumbnail= Yii::$app->GenCls->getimage($id, 'thumb');
                            $message['thumbnail'] = $thumbnail;
                            $fullname = $userInfo['fullname'];
                            $message['fullname'] = $fullname;
                            $conIdBulk[] = $message;
                        }
                    }
                }
            }
        }

        return json_encode($conIdBulk, true);
        exit;
    }

    public function XML($xml, $user_id) {
        if($xml) {
            $xml = simplexml_load_string($xml);
            $result = array();
            $messageData = $xml->forwarded;
            $result['message'] = (string)$messageData->message->body;
            $dateFormate = (string)$messageData->delay->attributes()->stamp;
            $datetime = strtotime($dateFormate);
            $date =  date('Y-m-d', strtotime($dateFormate));
            $time = date('H:i:s', strtotime($dateFormate));
            $from = $messageData->message->attributes()->from;
            $from = explode('/', $from);
            $from = $from[0];
            $from = str_replace("@arabiaface.com", "", $from);
            $to = (string)$messageData->message->attributes()->to;
            $to = str_replace("@arabiaface.com", "", $to);
            $result['date'] = $date;
            $result['time'] = $time;
            $thumbnail = Yii::$app->GenCls->getimage($to, 'thumb');
            $result['thumbnail'] = $thumbnail;
            $result['datetime'] = $datetime;
            if($from == $user_id.'@arabiaface.com') {
                $result['isselfsender'] = true;
            } else {
                $result['isselfsender'] = false;
            }

            if($user_id == $to) {
                $other = $from;
            } else {
                $other = $to;
            }
            $result['from'] = $from;
            $result['to'] = $to;
            $result['other'] = $other;
            return json_encode($result, true);
        }
    }

    public function singlemessage($xmlbulk, $user_id) {
        if($xmlbulk) {
            $dataarray = array();

            foreach ($xmlbulk as $key => $xml) {
                $result = array();
                $data = simplexml_load_string($xml);
                $messageData = $data->forwarded;
                $result['message'] = (string)$messageData->message->body;
                $dateFormate = (string)$messageData->delay->attributes()->stamp;
                $datetime = strtotime($dateFormate);
                $date =  date('Y-m-d', strtotime($dateFormate));
                $time = date('H:i:s', strtotime($dateFormate));
                $from = $messageData->message->attributes()->from;
                $from = explode('/', $from);
                $from = $from[0];
                $from = str_replace("@arabiaface.com", "", $from);
                $to = (string)$messageData->message->attributes()->to;
                $to = str_replace("@arabiaface.com", "", $to);
                $result['date'] = $date;
                $result['time'] = $time;
                $thumbnail = Yii::$app->GenCls->getimage($to, 'thumb');
                $result['thumbnail'] = $thumbnail;
                $result['datetime'] = $datetime;
                if($from == $user_id.'@arabiaface.com') {
                    $result['isselfsender'] = true;
                } else {
                    $result['isselfsender'] = false;
                }

                if($user_id == $to) {
                    $other = $from;
                } else {
                    $other = $to;
                }
                $result['from'] = $from;
                $result['to'] = $to;
                $result['other'] = $other;
                $dataarray[] = $result;
            }
            $resultdata = array('isSameDate' => false, 'difftime' => 0, 'isSameSender' => false);
            if(count($dataarray) >=2) {
                if($dataarray[0]['from'] == $dataarray[1]['from']) {
                    $isSameSender = true;
                    $resultdata['isSameSender'] = $isSameSender;
                    if($dataarray[0]['date'] == $dataarray[1]['date']) {
                        $isSameDate = true;
                        $resultdata['isSameDate'] = $isSameDate;
                        $datetime1 = $dataarray[0]['datetime'];
                        $datetime2 = $dataarray[1]['datetime'];
                        $diff = abs($datetime1 - $datetime);
                        $diff = round($diff / 60);
                        $resultdata['difftime'] = $diff;
                    } else {
                        $isSameDate = false;
                        $resultdata['isSameDate'] = $isSameDate;
                    }
                } else {
                    $isSameSender = false;
                    $resultdata['isSameSender'] = $isSameSender;
                }
                $merge = array_merge($resultdata, $dataarray[1]);
            } else {
                $merge = array_merge($resultdata, $dataarray[0]);
            }
            
            return json_encode($merge, true);
        }
    }

    public function getbasicinfoouser($uid, $user_id) {
        if($user_id) {
            $data = UserForm::find()->select(['fullname', 'city', 'country', 'last_logout_time'])->where(['_id' => $uid])->asarray()->one();
            if(!empty($data)) {
                $thumbnail = Yii::$app->GenCls->getimage($uid, 'thumb');
                $data['thumbnail'] = $thumbnail;
                $last_logout_time = isset($data['last_logout_time']) ? $data['last_logout_time'] : '';
                $lastseen = '';
                if($last_logout_time != '') {
                    $rowdate1 = date('Y-m-d H:i:s', $last_logout_time);
                    $rowdate2 = date('Y-m-d H:i:s');

                    $datetime1 = date_create($rowdate1);
                    $datetime2 = date_create($rowdate2);
                    $interval = date_diff($datetime1, $datetime2);
                    
                    if($interval->d == 0) {
                        if($interval->h == 0) {
                            if($interval->i == 0) {
                                $lastseen = '1min';
                            } else {
                                $lastseen = $interval->i.'min';
                            }
                        } else {
                            $lastseen = $interval->h.'hr';
                        }
                    } else if($interval->d == 1) {
                        $lastseen = 'yesterday';        
                    } else {
                        $lastseen = date('d M, Y H:i', $last_logout_time); // 2days upto 7days, one week
                    }
                }
                $data['lastseen'] = $lastseen;
                $currenttime = date('h:i A');
                $data['currenttime'] = $currenttime;
                return json_encode($data, true);
            }

        }
    }


    public function sethistory($storedtemp, $uid) {
        $newResultArray = array();
        if($uid) {
            $currentdate = time();
            $currentdateformat = time('m/d/Y');
            $previousDate = '';
            $label = '';
            $previousLabel = '';
            $storedtemp = array_reverse($storedtemp);
            $previousSender = '';
            $previoustemp = '';
            $previousType = 'text';

            foreach ($storedtemp as $key => $data) {
                $result = array();
                $xml = simplexml_load_string($data);
                $messageData = $xml->forwarded;
                $msg = (string)$messageData->message->body;
                $dateFormate = (string)$messageData->delay->attributes()->stamp;
                $datetime = strtotime($dateFormate);
                $date =  date('Y-m-d', strtotime($dateFormate));
                $time = date('H:i:s', strtotime($dateFormate));
                $from = $messageData->message->attributes()->from;
                $from = explode('/', $from);
                $from = $from[0];
                $from = str_replace("@arabiaface.com", "", $from);
                $to = (string)$messageData->message->attributes()->to;
                $to = str_replace("@arabiaface.com", "", $to);
                $thumbnail = Yii::$app->GenCls->getimage($to, 'thumb');
                $year = date('Y', $datetime);
                $msgid = '';
                $seconds = false;
                if($from == $uid.'@arabiaface.com') {
                    $result['isselfsender'] = true;
                } else {
                    $result['isselfsender'] = false;
                }

                if($uid == $to) {
                    $other = $from;
                } else {
                    $other = $to;
                }
                
                if($year == date("Y")) {
                    $datetimedisplays = date('m/d h:i', $datetime);
                } else {
                    $datetimedisplays = date('m/d/y h:i', $datetime);
                }

                $existingDateformate = date('m/d/Y', $datetime);
                
                if($previousDate != '' && $previousDate == $existingDateformate) {
                    $olddatetime = date('Y-m-d H:i:s', $previoustemp);
                    $currentformat = date('Y-m-d H:i:s', $datetime);
                    $start_date = new \DateTime($olddatetime);
                    $diff = $start_date->diff(new \DateTime($currentformat));

                    if($diff->d == 0 && $diff->h == 0 && $diff->i == 0 ) {
                        $seconds = true;
                    }
                }
                

                if($previousDate != '' && $previousDate != $existingDateformate) {
                    $olddatetime = date('Y-m-d H:i:s', $previoustemp);
                    $currentformat = date('Y-m-d H:i:s', $datetime);
                    $start_date = new \DateTime($olddatetime);
                    $diff = $start_date->diff(new \DateTime($currentformat));

                    if($diff->d <7) {
                        if($diff->d <2) {
                            if($diff->d == 0) {
                                $label = 'Today';
                            } else if($diff->d == 1) {
                                $label = 'Yesterday';
                            }   

                        } else {
                             $dayName = date('l', $existingDateformate);
                             $label = $dayName;
                        }
                    } else {
                          $label = date('d/m/Y', $datetime);
                          $isCheckForFullDateTimeLabe = false;
                    }
                    
                    $label = '<li class="date-divider"><span>'.$label.'</span></li>';
                }

                if($previousLabel == $label) {
                    $label = '';
                }

                if($previousDate == $existingDateformate && $previousSender == $from) {
                    if($seconds) {
                        $msg = $msg.'<br/>';
                        if($previousType == 'text') {
                            $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').find('li.msgli:first').find('.descholder').find('.msg-handle:first').find('p:first').prepend('$msg')";
                        } else {
                            $msg = '<div class="msg-handle gogle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span><p data-time="'.$datetime.'">'.$msg.'</p><span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg2" /> <label for="select_msg2"></label> </span> </div>'; 
                            $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').find('li.msgli:first').find('.descholder').prepend('$msg')";
                        }
                    } else {
                        $msg = '<div class="msg-handle final" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span><p data-time="'.$datetime.'">'.$msg.'</p><span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg2" /> <label for="select_msg2"></label> </span> </div>'; 
                        $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').find('li.msgli:first').find('.descholder').prepend('$msg')";

                    }
                } else if($from == $uid) {
                    $msg = '<li class="msgli msg-outgoing time ramani2"> <div class="checkbox-holder"> <div class="h-checkbox entertosend msg-checkbox"> <input type="checkbox" name="deleteselectedmsg" value=""> <label>&nbsp;</label> </div> </div> <div class="msgdetail-box"> <div class="descholder"> <div class="msg-handle" data-time="'.$datetime.'"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span> <p class="onw" data-time="'.$datetime.'">'.$msg.'</p> <span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg7" /> <label for="select_msg7"></label> </span> </div> </div> </div> </li>';
                    $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').prepend('$msg')";
                } else {
                    $msg = '<li class="msgli received msg-income time"> <div class="checkbox-holder"> <div class="h-checkbox entertosend msg-checkbox"> <input type="checkbox" name="deleteselectedmsg" value="'.$msgid.'"> <label>&nbsp;</label> </div> </div> <div class="msgdetail-box"> <div class="imgholder"> <img src="'.$thumbnail.'"> </div> <div class="descholder"> <div class="msg-handle"><span class="timestamp">'.$datetimedisplays.'</span><span class="settings-icon"> <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="'.$datetime.'"> <i class="zmdi zmdi-more"></i> </a> <ul id="'.$datetime.'" class="dropdown-content custom_dropdown persondropdown individiual_chat_setting"><div class="lds-css ng-scope"> <div style="width:100%;height:100%" class="lds-rolling-crntmsgpsndrp"> <div class="lststg"></div> </div> </div></ul> </span>  <p class="two" data-time="'.$datetime.'">'.$msg.'</p> <span class="select_msg_checkbox"> <input type="checkbox" class="filled-in" id="select_msg21" /> <label for="select_msg21"></label> </span> </div> </div> </div> </li>';
                    $putblock = "$('.right-section').find('ul.current-messages').find('li#li_leftli_".$other."').find('ul.outer').prepend('$msg')";  
                }

                $result['putblock'] = $putblock;
                $newResultArray[] = $result;
                $previousDate = date('m/d/Y', $datetime);
                $previoustemp = $datetime;
                $previousLabel = $label;
                $previousSender = $from;
            }
            
            return json_encode($newResultArray, true);
        }
    }
 
    public function getuserdetail($id, $user_id) {
        $data = LoginForm::find()->where([(string)'_id' => $id])->asarray()->one();
        if(!empty($data)) {
            $isMute = false;
            $isBlock = false;
            $thumbnail = Yii::$app->GenCls->getimage($id, 'thumbnail');
            $fullname = $data['fullname'];
            $securitySetting = SecuritySetting::find()->select(['mute_users'])->where(['user_id' => $user_id])->asarray()->one();
            if(!empty($securitySetting)) {
                if(isset($securitySetting['mute_users']) && $securitySetting['mute_users'] != '') { 
                    $mute_users = $securitySetting['mute_users'];
                    $mute_users = explode(",", $mute_users);
                    if(($key = array_search($id, $mute_users)) !== false) {
                        $isMute = true;
                    }
                }
            }

            $result_security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
            if(!empty($result_security)) {
                if(isset($result_security['message_filtering']) && $result_security['message_filtering'] != '') { 
                    $message_filtering = $result_security['message_filtering'];
                    $message_filtering = explode(",", $message_filtering);
                    if(($key = array_search($id, $message_filtering)) !== false) {
                        $isBlock = true;
                    }
                }
            }
            ?>
            <div class="custom_side_header">
                <span class="slide_out_right_btn close_side_slider waves-effect" onclick="contactInfo()">
                    <i class="mdi mdi-close mdi-20px"></i>
                </span>
                <h3>Contact info</h3>
            </div>
            <div class="side_modal_container contact_info_container">
                <div class="contact_info_profile">
                    <span class="big_profile_image">
                        <img src="<?=$thumbnail?>"/>
                    </span>
                </div>  
                <div class="contact_user_container">
                    <p class="contact_user_name"><?=$fullname?></p>
                    <p class="contact_user_lastseen">last seen today at 12:45PM</p>

                </div>

                <ul class="contact_info_ul">
                    <li>
                        <a href="<?php echo Url::to(['userwall/index', 'id' => $id]); ?>">
                            <span class="contact_list_options">View Wall</span>
                            <span class="contact_list_icon">
                                <i class="zmdi zmdi-chevron-right"></i>
                            </span>
                        </a>
                    </li>
                    <li onclick="giftModalActionFromCtIfo()">
                        <a>
                            <span class="contact_list_options">Send Gift</span>
                            <span class="contact_list_icon">
                                <i class="zmdi zmdi-chevron-right"></i>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a onclick="getparticularusersavedmsg()">
                            <span class="contact_list_options">Saved messages</span>
                            <span class="contact_list_icon">
                                <i class="zmdi zmdi-chevron-right"></i>
                            </span>
                        </a>
                    </li>
                </ul>
 
                <ul class="contact_info_ul contact_info_mute">
                    <li>
                        <a>
                            <span class="contact_list_options">Mute</span>
                            <span class="contact_list_icon">
                                <div class="switch contact_info_switch">
                                    <label>
                                        <?php if($isMute) { ?>
                                        <input type="checkbox" id="do_mute_message" checked/>
                                        <?php } else { ?>
                                        <input type="checkbox" id="do_mute_message"/>
                                        <?php } ?>
                                        <span class="lever"></span>
                                    </label>
                                </div>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a>
                            <span class="contact_list_options">Block</span>
                            <span class="contact_list_icon">
                                <div class="switch contact_info_switch">
                                    <label>
                                        <?php if($isBlock) { ?>
                                        <input type="checkbox" id="do_block_message" checked/>
                                        <?php } else { ?>
                                        <input type="checkbox" id="do_block_message"/>
                                        <?php } ?>
                                        <span class="lever"></span>
                                    </label>
                                </div>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <?php
        }
    }

    public function deletemessage_sm($id, $user_id)
    {
        $StarMessages = Messages::find()->where([(string)'_id' => $id])->one();
        if(!empty($StarMessages)) {
            $from_id = isset($StarMessages['from_id']) ? $StarMessages['from_id'] : '';
            $to_id = isset($StarMessages['to_id']) ? $StarMessages['to_id'] : '';

            if($from_id == $user_id) {
                $StarMessages->from_id_del = 1;
            } else {
                $StarMessages->to_id_del = 1;
            }

            $StarMessages->update();
            
            $result = array('status' => true);
            return json_encode($result, true);
        } 

        $result = array('status' => false);
        return json_encode($result, true);
    }

    public function getallarchivedusers($user_id) {
        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        $isEmpty = true;
        if($securitySetting) {
            if(isset($securitySetting['archive_users']) && $securitySetting['archive_users'] != '') { 
                $archive_users = $securitySetting['archive_users'];
                $archive_users = explode(",", $archive_users);
                $archive_users = array_values(array_filter($archive_users));
                if(!empty($archive_users)) {
                    $users = LoginForm::find()->where(['in', (string)'_id', $archive_users])->all();
                    foreach ($users as $single_user) {
                        $uid = (string)$single_user['_id'];
                        $thumbnail = Yii::$app->GenCls->getimage($uid,'thumb');
                        $fullname = Yii::$app->GenCls->getuserdata($uid,'fullname');
                        $rand = rand(999, 9999);
                        $time = time();
                        $uniqID = $rand.'_'.$time.'_'.$uid;
                        $isEmpty = false;
                        ?>
                        <div class="archeive_info" data-id="<?=$uid?>" onclick="openArchivedMessage(this)">
                            <span class="participants_profile">
                                <img src="<?=$thumbnail?>" />
                            </span> 
                            <span class="participants_name"><?=$fullname?></span>
                            <span class="day_time"></span>
                            <span class="settings-icon archeive_chat_dropdown">
                                <a class="dropdown-button more_btn" href="javascript:void(0)" data-activates="<?=$uniqID?>">
                                    <i class="zmdi zmdi-more"></i>
                                </a>
                                <ul id="<?=$uniqID?>" class="dropdown-content custom_dropdown">
                                   <li data-id="<?=$uid?>" onclick="unarchiveuser(this)"> <a>Unarchive chat</a> </li>
                                </ul>
                            </span>
                        </div>
                        <?php
                    }
                }
            }
        }

        if($isEmpty) {
            ?>
            <span class="no_archeive_chat_icon">
                <i class="zmdi zmdi-archive"></i>
            </span>
            <p class="archeived_msg">No archeived chat</p>
            <?php
        }
    }

    public function getallblockusers($user_id) {
        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        $isEmpty = true;
        if(!empty($securitySetting)) {
            if(isset($securitySetting['message_filtering']) && $securitySetting['message_filtering'] != '') { 
                $message_filtering = $securitySetting['message_filtering'];
                $message_filtering = explode(",", $message_filtering);
                $message_filtering = array_values(array_filter($message_filtering));
                if(!empty($message_filtering)) {

                    $users = LoginForm::find()->where(['in', (string)'_id', $message_filtering])->all();
                    
                    foreach ($users as $single_user) {
                        $uid = (string)$single_user['_id'];
                        $thumbnail = Yii::$app->GenCls->getimage($uid,'thumb');
                        $fullname = Yii::$app->GenCls->getuserdata($uid,'fullname');
                        $rand = rand(999, 9999);
                        $isEmpty = false;
                        ?>
                        <div class="blocked_person_box">
                            <span class="blocked_person_img">
                            <img class="circle" src="<?=$thumbnail?>" />
                            </span>
                            <div class="number_blocked">
                               <p class="blocked_number"><?=$fullname?></p>
                               <p class="blocked_name">&nbsp;</p>
                            </div>
                            <span class="blocked_remove waves-effect" data-id="<?=$uid?>" onclick="unblockuser(this)">
                                <i class="mdi mdi-close"></i>
                            </span>
                         </div>
                        <?php
                    }
                    ?>
                    <p class="blocked_msg">
                        Blocked Contacts will no longer be able to call you or send you messages
                    </p>
                    <?php
                }
            }
        }

        if($isEmpty) {
            ?>
            <p class="blocked_msg">
                Blocked Contacts will no longer be able to call you or send you messages
            </p>
            <?php
        }
    }

    public function getmessageleftsetting($id) {
        $communication_settings = CommunicationSettings::find()->where(['user_id' => $id])->asarray()->one();
        $thumbnail = Yii::$app->GenCls->getimage($id,'thumb');
        
        $user = LoginForm::find()->where([(string)'_id' => $id])->one();
        $fullname = $user['fullname'];
        $user_status_sentence = isset($user['user_status_sentence']) ? $user['user_status_sentence'] : 'Hey there ! i am using arabiaface';

        $is_received_message_tone_on = $is_new_message_display_preview_on = $communication_label = $show_away = $is_send_message_on_enter = '';

        if(!empty($communication_settings)) {
            $is_received_message_tone_on = isset($communication_settings['is_received_message_tone_on']) ? $communication_settings['is_received_message_tone_on'] : '';
            $is_new_message_display_preview_on = isset($communication_settings['is_new_message_display_preview_on']) ? $communication_settings['is_new_message_display_preview_on'] : '';
            $communication_label = isset($communication_settings['communication_label']) ? $communication_settings['communication_label'] : '';
            $show_away = isset($communication_settings['show_away']) ? $communication_settings['show_away'] : '';
            $is_send_message_on_enter = isset($communication_settings['is_send_message_on_enter']) ? $communication_settings['is_send_message_on_enter'] : '';
        }
                        ?>
<div class="profile_setting_box">
    <span class="user_profile_setting">
        <img class="circle" src="<?=$thumbnail?>" />
    </span>
    <div class="user_profile_container">
        <span class="user_name_setting"><?=$fullname?></span>
        <span class="user_status_setting">
            <?=$user_status_sentence?>
        </span>
        <span class="profile_name_edit" onclick="ProfileNameChange()">
            <i class="zmdi zmdi-edit"></i>
        </span>
        <div class="setting_profile_name">
            <input placeholder="" id="profile_name_add" type="text" class="validate" />
        </div>
    </div>
</div>
<div class="msg_setting_content">
    <div class="frow">
        <a class="setting_switch">
            <span class="switch_header">Sound</span>
            <span class="contact_list_options">Play sound when new message is received</span>
            <span class="contact_list_icon">
                <div class="switch contact_info_switch contact_info_switch_sound" data-issound="<?=$is_received_message_tone_on?>">
                    <label>
                        <input type="checkbox" id="is_received_message_tone_on">
                        <span class="lever"></span>
                    </label>
                </div>
            </span>
        </a>
        <a class="setting_switch">
            <span class="switch_header">Display preview</span>
            <span class="contact_list_options">Show new message preview</span>
            <span class="contact_list_icon">
                <div class="switch contact_info_switch contact_info_switch_displaypreview" data-isdisplaypreview="<?=$is_new_message_display_preview_on?>">
                    <label>
                        <input type="checkbox" id="is_new_message_display_preview_on">
                        <span class="lever"></span>
                    </label>
                </div>
            </span>
        </a>
    </div>
    <div class="frow i_mute">
        <select>
            <option value="" disabled="" selected>Turn off alerts and sound for...</option>
            <option value="1">Option 1</option>
            <option value="2">Option 2</option>
            <option value="3">Option 3</option>
        </select>

    </div>
    <div class="msg_setting_label_container">
        <p class="msg_setting_label">
            <?php if(isset($show_away) && $show_away== 'on') { ?>
                <input type="checkbox" id="show_away" checked>
            <?php } else { ?>
                <input type="checkbox" id="show_away">
            <?php } ?>
            <label for="show_away" class="setting_options">Show me a way when inactive for 10 minutes</label>
        </p>
        <p class="msg_setting_label">
            <?php if(isset($is_send_message_on_enter) && $is_send_message_on_enter== 'on') { ?>
                <input type="checkbox" id="is_send_message_on_enter" checked>
            <?php } else { ?>
                <input type="checkbox" id="is_send_message_on_enter">
            <?php } ?>
            <label for="is_send_message_on_enter" class="setting_options">Enter to send message</label>
        </p>
    </div>
</div>
        <?php            
        
    }

    public function unarchiveuser($id, $user_id)
    {
        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        if(!empty($securitySetting)) {
            $archive_usersIDS = isset($securitySetting['archive_users']) ? $securitySetting['archive_users'] : '';
            $exist = explode(',', $archive_usersIDS);
            
            if (($key = array_search($id, $exist)) !== false) {
                unset($exist[$key]);
            }

            $exist = array_values(array_filter($exist));
            $exist = array_unique($exist);
            $exist = implode(",", $exist);
                
            $securitySetting->archive_users = $exist;
            $securitySetting->update();
            
            $result = array('status' => true);
            return json_encode($result, true);
        } 

        $result = array('status' => false);
        return json_encode($result, true);
    }

    public function unblockuser($id, $user_id)
    {
        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        if(!empty($securitySetting)) {
            $message_filter_labelIDS = isset($securitySetting['message_filtering']) ? $securitySetting['message_filtering'] : '';
            $exist = explode(',', $message_filter_labelIDS);
            
            if (($key = array_search($id, $exist)) !== false) {
                unset($exist[$key]);
            }

            $exist = array_values(array_filter($exist));
            $exist = array_unique($exist);
            $exist = implode(",", $exist);
                
            $securitySetting->message_filtering = $exist;
            $securitySetting->update();
            
            $result = array('status' => true);
            return json_encode($result, true);
        } 

        $result = array('status' => false);
        return json_encode($result, true);
    }

    public function clearmessages($id, $user_id)
    {
        //cleared messages history..
        $demo = MessageBlock::find()->select(['con_id'])->where(['from_id' => $user_id, 'to_id' => $id])->orwhere(['from_id' => $id, 'to_id' => $user_id])->asArray()->one();
        $con_id = isset($demo['con_id']) ? $demo['con_id'] : '';
        Messages::updateAll(['to_id_del' => 1], ['con_id' => $con_id, 'to_id' => (string)$user_id]);
        Messages::updateAll(['from_id_del' => 1], ['con_id' => $con_id, 'from_id' => (string)$user_id]);
        return true;
        exit;
    }

    public function domutemessage() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['id']) && !empty($_POST['id'])) {
                
                $id = $_POST['id'];
                $do_mute_message = $_POST['do_mute_message'];
                
                $fullname = Yii::$app->GenCls->getuserdata($id,'fullname');

                $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                if(!empty($securitySetting)) {
                    if($do_mute_message == 'on') {
                        $label = $fullname.' is muted.';
                        if(isset($securitySetting['mute_users']) && $securitySetting['mute_users'] != '') { 
                            $mute_users = $securitySetting['mute_users'];
                            $mute_users = explode(",", $mute_users);
                            $mute_users = array_values(array_filter($mute_users));
                            $mute_users[] = $id;
                            $mute_users = array_unique($mute_users);
                            $mute_users = implode(',', $mute_users);
                            $securitySetting->mute_users = $mute_users;
                            $securitySetting->update();

                            $result = array('status' => true, 'label' => $label, 'isdone' => 'mute');
                            return json_encode($result, true);
                        } else {
                            $securitySetting->mute_users = $id;
                            $securitySetting->update();

                            $result = array('status' => true, 'label' => $label, 'isdone' => 'mute');
                            return json_encode($result, true);
                        }
                    } else {
                        if(isset($securitySetting['mute_users']) && $securitySetting['mute_users'] != '') { 
                            $mute_users = $securitySetting['mute_users'];
                            $mute_users = explode(",", $mute_users);

                            if(($key = array_search($id, $mute_users)) !== false) {
                                unset($mute_users[$key]);
                                $mute_users = implode(",", $mute_users);
                                $securitySetting->mute_users = $mute_users;
                                $securitySetting->update();

                                $label = $fullname.' is unmuted.';
                                $result = array('status' => true, 'label' => $label, 'isdone' => 'unmute');
                                return json_encode($result, true);
                            }
                        }
                    }
                } else {
                    $securitySetting = new SecuritySetting();            
                    $securitySetting->user_id = $user_id;
                    $securitySetting->mute_users = $id;
                    $securitySetting->save();

                    $label = $fullname.' is muted.';
                    $result = array('status' => true, 'label' => $label, 'isdone' => 'mute');
                    return json_encode($result, true);
                }
            }
        }
    }

    public function doblockmessage() {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        
        if(isset($user_id) && $user_id != '') {
            if(isset($_POST['id']) && !empty($_POST['id'])) {
                
                $id = $_POST['id'];
                $do_block_message = $_POST['do_block_message'];

                $fullname = Yii::$app->GenCls->getuserdata($id,'fullname');

                $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                if(!empty($securitySetting)) {
                    if($do_block_message == 'on') {
                        $label = $fullname.' is blocked.';
                        if(isset($securitySetting['message_filtering']) && $securitySetting['message_filtering'] != '') { 
                            $message_filtering = $securitySetting['message_filtering'];
                            $message_filtering = explode(",", $message_filtering);
                            $message_filtering = array_values(array_filter($message_filtering));
                            $message_filtering[] = $id;
                            $message_filtering = array_unique($message_filtering);
                            $message_filtering = implode(',', $message_filtering);
                            $securitySetting->message_filtering = $message_filtering;
                            $securitySetting->update();

                            $result = array('status' => true, 'label' => $label, 'isdone' => 'block');
                            return json_encode($result, true);
                        } else {
                            $securitySetting->message_filtering = $id;
                            $securitySetting->update();

                            $result = array('status' => true, 'label' => $label, 'isdone' => 'block');
                            return json_encode($result, true);
                        }
                    } else {
                        if(isset($securitySetting['message_filtering']) && $securitySetting['message_filtering'] != '') { 
                            $message_filtering = $securitySetting['message_filtering'];
                            $message_filtering = explode(",", $message_filtering);

                            if(($key = array_search($id, $message_filtering)) !== false) {
                                unset($message_filtering[$key]);
                                $message_filtering = implode(",", $message_filtering);
                                $securitySetting->message_filtering = $message_filtering;
                                $securitySetting->update();

                                $label = $fullname.' is unblocked.';
                                $result = array('status' => true, 'label' => $label, 'isdone' => 'unblock');
                                return json_encode($result, true);
                            }
                        }
                    }
                } else {
                    $securitySetting = new SecuritySetting();            
                    $securitySetting->user_id = $user_id;
                    $securitySetting->message_filtering = $id;
                    $securitySetting->save();

                    $label = $fullname.' is blocked.';
                    $result = array('status' => true, 'label' => $label, 'isdone' => 'block');
                    return json_encode($result, true);
                }
            }
        }
    }

    public function UptUsrSentence($id, $user_id, $sentence) {
        $user = LoginForm::find()->where(['_id' => $id])->one();
        $sentence = trim($sentence);
        if(!empty($user)) {
            $user->user_status_sentence = $sentence;
            $user->update();
        }
    }

    public function checkmsgtone($user_id) {
        $NotificationSetting = NotificationSetting::find()->where(['user_id' => $user_id])->one();
        if(!empty($NotificationSetting)) {
            $sound_on_message = isset($NotificationSetting['sound_on_message']) ? $NotificationSetting['sound_on_message'] : '';
            if($sound_on_message == 'Yes') {
                return 'Yes';
            }
        }
        return 'No';
    }

    public function sendiconstatus($user_id) {
        $communication_settings = CommunicationSettings::find()->where(['user_id' => $user_id])->asarray()->one();

        /*echo '<pre>';
        print_r($communication_settings);
        die;*/

        if(!empty($communication_settings)) {
            $is_send_message_on_enter = isset($communication_settings['is_send_message_on_enter']) ? $communication_settings['is_send_message_on_enter'] : '';
            if($is_send_message_on_enter == 'on') {
                return 'Yes';
            }
        }
        return 'No';
    }
}