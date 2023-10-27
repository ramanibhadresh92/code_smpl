<?php 
namespace frontend\models;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use yii\mongodb\ActiveRecord;
use yii\helpers\ArrayHelper;
use frontend\models\BlockConnect;

class SecuritySetting extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function collectionName()
    {
        return 'security_setting';
    }

     public function attributes()
    {
        return ['_id','user_id','security_questions','answer','gf_ans','born_ans','eml_ans','device_type','browser_type','my_view_status','my_post_view_status', 'my_post_view_status_custom', 'restricted_list','blocked_list','pair_social_actions','contact_me',
                'message_filtering','connect_request','bothering_me','dashboard_view_status','add_public_wall', 'add_public_wall_custom',
                'see_public_wall','review_posts','view_posts_tagged_in','view_others_posts_on_mywall','review_tags',
                'recent_activities','connect_list','view_photos', 'view_photos_custom', 'created_date','modified_date','ip','created_by','modified_by','is_deleted','mute_users','archive_users','add_post_on_your_wall_view', 'add_post_on_your_wall_view_custom', 'request_filter','photosecuritycustomids','postsecuritycustomids','postonwallcustomids','viewpermissioncustomids','localguide_posts_delete'];
    }
    
    public function getPosts()
    {
        return $this->hasMany(PostForm::className(), ['post_user_id' => '_id']);
    }
    
    public function scenarios()
    {
        $scenarios = parent::scenarios();     
        return $scenarios;
    }
  
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }
    
    public function security() {
        $session = Yii::$app->session;
        $email = $session->get('email'); 
        $user_id = (string) $session->get('user_id');
		$data = array();
        $isNew = false;
        $security = SecuritySetting::find()->where(['user_id' => $user_id])->one();

		if(empty($security)) {
            $isNew = true;
            $security = new SecuritySetting();  
            $security->user_id = $user_id;
        }

        if(isset($_POST['security_questions'])) {
			$security->security_questions = $_POST['security_questions'];
			if($_POST['security_questions'] == 'eml_ans') {
				$security->eml_ans = $_POST['securityanswer']; 
			} else if($_POST['security_questions'] == 'born_ans') {
				$security->born_ans = $_POST['securityanswer'];
			} else if($_POST['security_questions'] == "gf_ans") {
				$security->gf_ans = $_POST['securityanswer'];
			}
		}

        if(isset($_POST['my_view_status'])) {
		     $security->my_view_status = $_POST['my_view_status'];
        }
        if(isset($_POST['my_post_view_status'])) {
            if(isset($_POST['type']) && $_POST['type'] == 'my_post_view_status') {
    		    $my_post_view_status = $_POST['my_post_view_status'];
                if($my_post_view_status == 'Custom') {
                    $custom = $_POST['custom'];
                    $security->my_post_view_status_custom = $custom;
                } else {
                    $security->my_post_view_status_custom = '';
                }
                $security->my_post_view_status = $my_post_view_status;
            }
        }
        if(isset($_POST['connect_request'])) {
			 $security->connect_request = $_POST['connect_request'];
        }
        if(isset($_POST['add_public_wall'])) {
            if(isset($_POST['type']) && $_POST['type'] == 'add_public_wall') {
    			$add_public_wall = $_POST['add_public_wall'];
                if($add_public_wall == 'Custom') {
                    $custom = $_POST['custom'];
                    $security->add_public_wall_custom = $custom;
                } else {
                    $security->add_public_wall_custom = '';
                }
                $security->add_public_wall = $add_public_wall;
            }
        }
        if(isset($_POST['view_photos'])) {
            if(isset($_POST['type']) && $_POST['type'] == 'view_photos') {
                $view_photos = $_POST['view_photos'];
                if($view_photos == 'Custom') {
                    $custom = $_POST['custom'];
                    $security->view_photos_custom = $custom;
                } else {
                    $security->view_photos_custom = '';
                }
                $security->view_photos = $view_photos;
            }
        }
        if(isset($_POST['review_posts'])) {
			 $security->review_posts = $_POST['review_posts'];
        }
        if(isset($_POST['review_tags'])) {
			 $security->review_tags = $_POST['review_tags'];
        }
        if(isset($_POST['connect_list'])) {
			 $security->connect_list = $_POST['connect_list'];
        }
        if(isset($_POST['add_post_on_your_wall_view'])) {
            if(isset($_POST['type']) && $_POST['type'] == 'add_post_on_your_wall_view') {
    			$add_post_on_your_wall_view = $_POST['add_post_on_your_wall_view'];
                if($add_post_on_your_wall_view == 'Custom') {
                    $custom = $_POST['custom'];
                    $security->add_post_on_your_wall_view_custom = $custom;
                } else {
                    $security->add_post_on_your_wall_view_custom = '';
                }
                $security->add_post_on_your_wall_view = $add_post_on_your_wall_view;
            }
        }

        if($isNew) {
            $security->insert();
        } else {
            $security->update();
        }
        return true;
    }
    
    public function security2()
    {
		$session = Yii::$app->session;
		$email =  base64_decode(strrev($_GET['email']));
        $user = LoginForm::find()->where(['email' => $email])->one();
		$user_id = (string)$user->_id;
        $security2 = SecuritySetting::find()->where(['user_id' => $user_id])->one();

		if(!empty($security2))
		{
			$security2->my_view_status = 'Public';
			$security2->my_post_view_status = 'Public';      
			$security2->connect_request = 'Public';
			$security2->view_photos = 'Public';
			$security2->dashboard_view_status = 'Public';
			$security2->add_public_wall = 'Public';
			$security2->see_public_wall = 'Public';
			$security2->review_posts = 'Disabled';
			$security2->review_tags = 'Disabled';
			$security2->connect_list = 'Public';
			$security2->add_post_on_your_wall_view = 'add_post_on_your_wall_view';

			$security2->update();
		}
        else
		{
			$security3 = new SecuritySetting(); 
			$security3->user_id = $user_id;
			$security3->my_view_status = 'Public';
			$security3->my_post_view_status = 'Public';      
			$security3->view_photos = 'Public';
			$security3->connect_request = 'Public';
			$security3->dashboard_view_status = 'Public';
			$security3->add_public_wall = 'Public';
			$security3->see_public_wall = 'Public';
			$security3->review_posts = 'Disabled';
			$security3->review_tags = 'Disabled';
			$security3->connect_list = 'Public';
			$security3->add_post_on_your_wall_view = 'add_post_on_your_wall_view';

			$security3->insert();  
        }
        return 1;
    }
    
    public function security3($email)
    {
        $session = Yii::$app->session;
        $user = LoginForm::find()->where(['email' => $email])->one();
        $user_id = (string)$user->_id;
        $security2 = SecuritySetting::find()->where(['user_id' => $user_id])->one();
		
		if(!empty($security2))
		{
			$security2->my_view_status = 'Public';
			$security2->my_post_view_status = 'Public';      
			$security2->connect_request = 'Public';
			$security2->view_photos = 'Public';
			$security2->dashboard_view_status = 'Public';
			$security2->add_public_wall = 'Public';
			$security2->see_public_wall = 'Public';
			$security2->review_posts = 'Disabled';
			$security2->review_tags = 'Disabled';
			$security2->connect_list = 'Public';
			$security2->add_post_on_your_wall_view = 'add_post_on_your_wall_view';
			$security2->update();
          }
          else
		  {            
             $security3 = new SecuritySetting(); 
             $security3->user_id = $user_id;
             $security3->my_view_status = 'Public';
             $security3->my_post_view_status = 'Public';      
             $security3->view_photos = 'Public';
             $security3->connect_request = 'Public';
             $security3->dashboard_view_status = 'Public';
             $security3->add_public_wall = 'Public';
             $security3->see_public_wall = 'Public';
             $security3->review_posts = 'Disabled';
             $security3->review_tags = 'Disabled';
             $security3->connect_list = 'Public';
             $security3->add_post_on_your_wall_view = 'add_post_on_your_wall_view';

             $security3->insert();  
          }
          return 1;
    }
    
    public function blocking()
    {   
        $session = Yii::$app->session;
        $email = $session->get('email'); 
        $user_id = (string) $session->get('user_id');
        
        $security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        $isNew = false;

        if(empty($security)) {
            $isNew = true;
            $security = new SecuritySetting();  
            $security->user_id = $user_id;
        }

        if(isset($_POST['SecuritySetting']['restricted_list']) && !empty($_POST['SecuritySetting']['restricted_list'])){
            $abc = $_POST['SecuritySetting']['restricted_list'];
            $restricted_list = implode(",", $abc);
            $security->restricted_list = $restricted_list; 
        }

        if(isset($_POST['SecuritySetting']['blocked_list']) && !empty($_POST['SecuritySetting']['blocked_list'])){
            $xyz = $_POST['SecuritySetting']['blocked_list'];
            $blocked_list = implode(",", $xyz);
            $security->blocked_list = $blocked_list;
        }

        if(isset($_POST['SecuritySetting']['message_filtering']) && !empty($_POST['SecuritySetting']['message_filtering'])){
            $def = $_POST['SecuritySetting']['message_filtering'];
            $message_filtering = implode(",", $def);
            $security->message_filtering = $message_filtering;
        }

        if(isset($_POST['SecuritySetting']['request_filter']) && !empty($_POST['SecuritySetting']['request_filter'])){
            $def = $_POST['SecuritySetting']['request_filter'];
            $request_filter = implode(",", $def);
            $security->request_filter = $request_filter;
        }
       
        if($isNew) {
            $security->insert();
            return true;
        } else {
            $security->update();
            return true;
        }
    }
    public function blocking1()
    {   
        $session = Yii::$app->session;
        $email = $session->get('email'); 
        $user_id = (string) $session->get('user_id');
        
        $security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
        $isNew = false;

        if(empty($security)) {
            $isNew = true;
            $security = new SecuritySetting();  
            $security->user_id = $user_id;
        }

        $ids = isset($_POST['ids']) ? implode(',', $_POST['ids']) : '';

        if($_POST['label'] == 'restricted_list_label') {
            $security->restricted_list = $ids; 
        }

        if($_POST['label'] == 'blocked_list_label') {
            $security->blocked_list = $ids;
        }

        if($_POST['label'] == 'message_filter_label') {
            $security->message_filtering = $ids;
        }

        if($_POST['label'] == 'request_filter_label') {
            $security->request_filter = $ids;
        }
       
        if($isNew) {
            $security->insert();
            return true;
        } else {
            $security->update();
            return true;
        }
    }
    
    public static function findIdentity($id)
    {
        return static::findOne(['email' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }
	
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
	
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public function getLastInsertedRecord($id)
    {
        return Personalinfo::find()->select(['about'])->where(['user_id' => $id])->asarray()->one();
    }

    public function blockuser($id, $user_id) {
        $securitySetting = SecuritySetting::find()->select(['message_filtering'])->where(['user_id' => $user_id])->asarray()->one();
        if($securitySetting) {
            if(isset($securitySetting['message_filtering']) && $securitySetting['message_filtering'] != '') {
                $message_filtering = $securitySetting['message_filtering'];
                $message_filtering = explode(",", $message_filtering);
                if(($key = array_search($id, $message_filtering)) !== false) {
                    unset($message_filtering[$key]);
                    $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $message_filtering = implode(",", $message_filtering);
                    $securitySetting->message_filtering = $message_filtering;
                    $securitySetting->update();
                    return 'unblock';
                    exit;
                } else {
                    $message_filtering[] = $id;
                    $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $message_filtering = implode(",", $message_filtering);
                    $securitySetting->message_filtering = $message_filtering;
                    $securitySetting->update();
                    return 'block';
                    exit;
                }
            } else {
                $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                if($securitySetting) {
                    $securitySetting->message_filtering = (string)$id;
                    $securitySetting->update();
                    return 'block';
                    exit;
                }
            }
        } else {
            $securitySetting = new SecuritySetting();            
            $securitySetting->user_id = $user_id;
            $securitySetting->message_filtering = (string)$id;
            $securitySetting->save();
            return 'block';
            exit;
        }
    }

    public function muteuser($id, $user_id) {
        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->asarray()->one();
        if(!empty($securitySetting)) {
            if(isset($securitySetting['mute_users']) && $securitySetting['mute_users'] != '') { 
                $mute_users = $securitySetting['mute_users'];
                $mute_users = explode(",", $mute_users);

                if(($key = array_search($id, $mute_users)) !== false) {
                    unset($mute_users[$key]);
                    $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $mute_users = implode(",", $mute_users);
                    $securitySetting->mute_users = $mute_users;
                    $securitySetting->update();
                    return 'unmute';
                    exit;
                } else {
                    $mute_users[] = $id;
                    $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                    $mute_users = implode(",", $mute_users);
                    $securitySetting->mute_users = $mute_users;
                    $securitySetting->update();
                    return 'mute';
                    exit;
                }
            } else {
                $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                if($securitySetting) {
                    $securitySetting->mute_users = (string)$id;
                    $securitySetting->update();
                    return 'mute';
                    exit;
                }
            }
        } else {
            $securitySetting = new SecuritySetting();            
            $securitySetting->user_id = $user_id;
            $securitySetting->mute_users = (string)$id;
            $securitySetting->save();
            return 'mute';
            exit;
        }
    }

     public function archiveuser($id, $user_id) {
        $securitySetting = SecuritySetting::find()->select(['archive_users'])->where(['user_id' => $user_id])->one();
        if($securitySetting) {
            if(isset($securitySetting['archive_users']) && $securitySetting['archive_users'] != '') { 
                $archive_users = $securitySetting['archive_users'];
                $archive_users = explode(",", $archive_users);

                if(($key = array_search($id, $archive_users)) !== false) {
                    unset($archive_users[$key]);
                    $archive_users = implode(",", $archive_users);
                    $securitySetting->archive_users = $archive_users;
                    $securitySetting->update();
                    return false;
                    exit;
                } else {
                    $archive_users[] = $id;
                    $archive_users = implode(",", $archive_users);
                    $securitySetting->archive_users = $archive_users;
                    $securitySetting->update();
                    return true;
                    exit;
                }
            } else {
                $securitySetting->archive_users = (string)$id;
                $securitySetting->update();
                return true;
                exit;
            }
        } else {
            $securitySetting = new SecuritySetting();            
            $securitySetting->user_id = $user_id;
            $securitySetting->archive_users = (string)$id;
            $securitySetting->save();
            return true;
            exit;
        }
    }

    public function messageuserstatus($id, $user_id) {
        $status = array();
        if($id) {
            // check is archieve.....                
            $info = SecuritySetting::find()->where(["user_id" => $user_id])->one();
            if($info) {
                $is_archive = $info['archive_users'];
                $is_archive = explode(",", $is_archive);
                if(in_array($id, $is_archive)) {
                    $status['is_archive'] = true;
                }
            
                $is_mute = $info['mute_users'];
                $is_mute = explode(",", $is_mute);
                if(in_array($id, $is_mute)) {
                    $status['is_mute'] = true;
                }
                
                $is_blocked = $info['message_filtering'];
                $is_blocked = explode(",", $is_blocked);
                if(in_array($id, $is_blocked)) {
                    $status['is_blocked'] = true;
                }
            }
        }
        return $status;
        exit;
    }

    public function isBlocked($id, $user_id) {
        if($id) {   
            $is_blocked = SecuritySetting::find()->where(["user_id" => $user_id])->one();
            if($is_blocked) {
                $is_blocked = $is_blocked['message_filtering'];
                $is_blocked = explode(",", $is_blocked);
                if(in_array($id, $is_blocked)) { 
                    $getdata = UserForm::find()->select(['fullname'])->where([(string)'_id' => (string)$id])->asarray()->one();
                    if(!empty($getdata)) {
                        $fullname = $getdata['fullname'];
                        $info = array('status' => true, 'by' => 'self', 'name' => $fullname);
                        return $info;
                        exit;
                    }
                }
            }

            $is_blocked = SecuritySetting::find()->where(["user_id" => $id])->one();
            if($is_blocked) {
                $is_blocked = $is_blocked['message_filtering'];
                $is_blocked = explode(",", $is_blocked);
                if(in_array($user_id, $is_blocked)) {
                    $getdata = UserForm::find()->select(['fullname'])->where([(string)'_id' => (string)$id])->asarray()->one();
                    if(!empty($getdata)) {
                        $fullname = $getdata['fullname'];
                        $info = array('status' => true, 'by' => 'other', 'name' => $fullname);
                        return $info;
                        exit;
                    }
                }
            }            
        }
    
        $info = array('status' => false);
        return $info;
        exit;
    }

    public function isMute($id, $user_id) {
        if($id) {  
            $info = SecuritySetting::find()->where(["user_id" => $user_id])->one();
            if($info) {
                if(isset($info['archive_users']) && $info['archive_users'] != '') { 
                    $archive_users = $info['archive_users'];
                    $archive_users = explode(",", $archive_users);

                    if(($key = array_search($id, $archive_users)) !== false) {
                        unset($archive_users[$key]);
                        $securitySetting = SecuritySetting::find()->where(['user_id' => $user_id])->one();
                        $archive_users = implode(",", $archive_users);
                        $securitySetting->archive_users = $archive_users;
                        $securitySetting->update();
                    } 
                }
         
                $is_mute = isset($info['mute_users']) ? $info['mute_users'] : '';
                if($is_mute) {
                    $is_mute = explode(",", $is_mute);
                    if(in_array($id, $is_mute)) {
                        $result = array('isMute' => true);
                    }
                }
            }
        }

        // Total count of Unread Message...............
        $unreadmsgcount = Messages::find()->where(['from_id' => $id, 'to_id' => $user_id, 'is_read' => 1])->count();

        $result = array('isMute' => false);
        
        $info = UserForm::find()
        ->select(['fullname', 'gender', 'status'])
        ->where(['_id' => $id])
        ->asarray()
        ->one();

        $thumbnail = Yii::$app->GenCls->getimage($id,'thumb');
        $result['thumbnail'] = $thumbnail;
        $result['fullname'] = $info['fullname'];
        $result['gender'] = isset($info['gender']) ? $info['gender'] : '';
        $result['status'] = $info['status'];
        $result['unreadmsgcount'] = $unreadmsgcount;

        //check message tone is active or not..
        $msgTone = Messages::checkmsgtone($user_id);
        $result['msgTone'] = $msgTone;   
        
        $match = Messages::matchWithPrevious($id, $user_id);
        $combineArray = array_merge($result, $match);
        return json_encode($combineArray, true);
        exit;
    }
	
	function user_block($block)
	{
        $fid = (string) $block;
		$data = array();

		$session = Yii::$app->session;
		$email = $session->get('email_id');
		$user_id = (string) $session->get('user_id');
		$userexist = BlockConnect::find()->select(['_id','block_ids'])->where(['user_id' => $user_id])->one();
		$mute = new BlockConnect();
		if ($userexist)
		{
			
			
			$mute = BlockConnect::find()->where(['user_id' => $user_id])->one();
			$mute->block_ids = $fid.',';
			if ($mute->update())
			{
				return 2;
			}
			else
			{
				return 0;
			} 
		}
		else
		{
			$mute->user_id = $user_id;
			$mute->block_ids = $fid.',';
			if ($mute->insert())
			{
				return 2;
			}
			else
			{
				return 0;
			}
		}
	}

    public function blockofferrequest($fid, $user_id)
    {
       if (isset($fid) && $fid != '') {
            $data = SecuritySetting::find()->where(['user_id' => $user_id])->one();
            if(!empty($data)) {
                if(isset($data['request_filter']) && $data['request_filter'] != '') {
                    $blockIds = $data['request_filter'];
                    $blockIds = explode(',', $blockIds);
                    if(!empty($blockIds)) {
                        if(!in_array($fid, $blockIds)) {
                            $blockIds[] = $fid;
                        }
                    } else {
                        $blockIds[] = $fid;
                    }
                } else {
                    $blockIds[] = $fid;
                }


                $blockIds = implode(',', $blockIds);
                if(empty($blockIds)) {
                    $data->request_filter = '';
                    $data->update();
                    return true;
                } else {
                    $data->request_filter = $blockIds;
                    $data->update();
                    return true;
                }
            } else {
                $data = new SecuritySetting();
                $data->user_id = $user_id;
                $data->request_filter = "$fid";
                $data->save();
                return true;
            }
        }
    }

    public function blockofferrequestidsget()
    {
        $session = Yii::$app->session;
        $user_id = (string)$session->get('user_id');
        $resultArray = array();
        if (isset($user_id) && $user_id != '') {
            $data = SecuritySetting::find()->where(['user_id' => $user_id])->one();
            if(!empty($data)) {
                if(isset($data['request_filter']) && $data['request_filter'] != '') {
                    $resultArray = $data['request_filter'];
                    $resultArray = explode(',', $resultArray);
                }
            }
        }

        return $resultArray;
    } 

    public function connectistprivacyupdate($value, $user_id)
    {
        if($value) {
            $security = SecuritySetting::find()->where(['user_id' => $user_id])->one();
            if(!empty($security)) {
                $security->connect_list = $value;
                $security->update();
                return true;
            } else {
                $security = new SecuritySetting();
                $security->user_id = $user_id;
                $security->connect_list = $value;
                $security->save();
                return true;
            }
        }
    }

    public function getFullnamesWithToolTip($ids, $label) {
        $commitArray = array(
            'restricted_list_label' => 'Add people to your restricted list',
            'page_restricted_list' => 'Add people to your restricted list',
            'blocked_list_label' => 'Add people to your block list',
            'page_block_list' => 'Add people to your block list',
            'message_filter_label' => 'Add people to your message filter',
            'page_block_message_list' => 'Add people to your message filter',
            'request_filter_label' => 'Add people to your request filter'
        );

        if(!empty($ids)) {
            $data = ArrayHelper::map(UserForm::find()->select(['fullname'])->where(['in', '_id', $ids])->asarray()->all(), function($data) { return (string)$data['_id']; }, 'fullname');

            $sentence = 'Add people to your mute list';
            if(!empty($data)) {
                $data = array_values($data);
                if(count($data) >2) {
                    $tempCount = count($data) - 1;
                    $tempNames = array_slice($data, 1);

                    $data_label = '<span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">'.$data[0] . '</span> and <span class="tagged_person_name compose_addpersonAction_as liveliketooltip" id="compose_addpersonAction_as" data-title="'.implode('<br/>', $tempNames).'">'.(count($data) - 1).' others</span>';     
                } else if (count($data) >1) {
                    $data_label = '<span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">'.$data[0] . '</span> and <span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">' . $data[1] .'</span>';  
                } else if (count($data) == 1) { 
                    $data_label = '<span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">'.$data[0].'</span>';  
                } else {
                    if(array_key_exists($label, $commitArray)) {
                        $sentence = $commitArray[$label];
                    }
                    $data_label = '<span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">'.$sentence.'</span>';    
                }
            } else {
                if(array_key_exists($label, $commitArray)) {
                    $sentence = $commitArray[$label];
                }
                $data_label = '<span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">'.$sentence.'</span>'; 
            }
            
        } else {
            if(array_key_exists($label, $commitArray)) {
                $sentence = $commitArray[$label];
            }
            $data_label = '<span class="tagged_person_name compose_addpersonAction_as" id="compose_addpersonAction_as">'.$sentence.'</span>';
        }
        return $data_label;
    }
}
