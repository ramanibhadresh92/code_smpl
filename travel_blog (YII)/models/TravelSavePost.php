<?php
namespace frontend\models;
use Yii;
use yii\base\Model;
use yii\mongodb\ActiveRecord;

class TravelSavePost extends ActiveRecord
{
    public static function collectionName()
    {
        return 'travel_save_post';
    }

    public function attributes()
    {
        return ['_id', 'user_id', 'travelbuddy_save_post', 'hangout_save_posts', 'weekendescape_save_posts', 'hireguide_save_posts','localguide_save_posts'];
    }

    public function hangoutsaveevent($id, $user_id)
	{
        $label = 'Save';
        $Ids = array();
        if (isset($id) && $id != '') {
			$data = TravelSavePost::find()->where(['user_id' => $user_id])->one();
			if(!empty($data)) {
				if(isset($data['hangout_save_posts']) && $data['hangout_save_posts'] != '') {
					$Ids = $data['hangout_save_posts'];
					$Ids = explode(',', $Ids);
					if(!empty($Ids)) {
						if(in_array($id, $Ids)) {
							if (($key = array_search($id, $Ids)) !== false) {
							    unset($Ids[$key]);
							    $label = 'Unsave';
							}
						} else {
							$Ids[] = $id;
						}
					} else {
						$Ids[] = $id;
					}
				} else {
					$Ids[] = $id;
				}

				$Ids = implode(',', $Ids);
				if(empty($Ids)) {
					$data->hangout_save_posts = '';
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				} else {
					$data->hangout_save_posts = $Ids;
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				}
			} else {
				$data = new TravelSavePost();
				$data->user_id = $user_id;
				$data->hangout_save_posts = "$id";
				$data->save();
				$result = array('status' => true, 'label' => $label);
				return json_encode($result, true);
			}
		}
    }
	
	public function weekendescapesaveevent($id, $user_id)
	{
        $label = 'Save';
        $Ids = array();
        if (isset($id) && $id != '') {
			$data = TravelSavePost::find()->where(['user_id' => $user_id])->one();
			if(!empty($data)) {
				if(isset($data['weekendescape_save_posts']) && $data['weekendescape_save_posts'] != '') {
					$Ids = $data['weekendescape_save_posts'];
					$Ids = explode(',', $Ids);
					if(!empty($Ids)) {
						if(in_array($id, $Ids)) {
							if (($key = array_search($id, $Ids)) !== false) {
							    unset($Ids[$key]);
							    $label = 'Unsave';
							}
						} else {
							$Ids[] = $id;
						}
					} else {
						$Ids[] = $id;
					}
				} else {
					$Ids[] = $id;
				}

				$Ids = implode(',', $Ids);
				if(empty($Ids)) {
					$data->weekendescape_save_posts = '';
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				} else {
					$data->weekendescape_save_posts = $Ids;
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				}
			} else {
				$data = new TravelSavePost();
				$data->user_id = $user_id;
				$data->weekendescape_save_posts = "$id";
				$data->save();
				$result = array('status' => true, 'label' => $label);
				return json_encode($result, true);
			}
		}
    }

    public function hireguidesaveevent($id, $user_id)
	{
        $label = 'Save';
        $Ids = array();
        if (isset($id) && $id != '') {
			$data = TravelSavePost::find()->where(['user_id' => $user_id])->one();
			if(!empty($data)) {
				if(isset($data['hireguide_save_posts']) && $data['hireguide_save_posts'] != '') {
					$Ids = $data['hireguide_save_posts'];
					$Ids = explode(',', $Ids);
					if(!empty($Ids)) {
						if(in_array($id, $Ids)) {
							if (($key = array_search($id, $Ids)) !== false) {
							    unset($Ids[$key]);
							    $label = 'Unsave';
							}
						} else {
							$Ids[] = $id;
						}
					} else {
						$Ids[] = $id;
					}
				} else {
					$Ids[] = $id;
				}

				$Ids = implode(',', $Ids);
				if(empty($Ids)) {
					$data->hireguide_save_posts = '';
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				} else {
					$data->hireguide_save_posts = $Ids;
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				}
			} else {
				$data = new TravelSavePost();
				$data->user_id = $user_id;
				$data->hireguide_save_posts = "$id";
				$data->save();
				$result = array('status' => true, 'label' => $label);
				return json_encode($result, true);
			}
		}
    }

    public function travelbuddysaveevent($id, $user_id)
	{
        $label = 'Save';
        $Ids = array();
        if (isset($id) && $id != '') {
			$data = TravelSavePost::find()->where(['user_id' => $user_id])->one();
			if(!empty($data)) {
				if(isset($data['travelbuddy_save_post']) && $data['travelbuddy_save_post'] != '') {
					$Ids = $data['travelbuddy_save_post'];
					$Ids = explode(',', $Ids);
					if(!empty($Ids)) {
						if(in_array($id, $Ids)) {
							if (($key = array_search($id, $Ids)) !== false) {
							    unset($Ids[$key]);
							    $label = 'Unsave';
							}
						} else {
							$Ids[] = $id;
						}
					} else {
						$Ids[] = $id;
					}
				} else {
					$Ids[] = $id;
				}

				$Ids = implode(',', $Ids);
				if(empty($Ids)) {
					$data->travelbuddy_save_post = '';
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				} else {
					$data->travelbuddy_save_post = $Ids;
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				}
			} else {
				$data = new TravelSavePost();
				$data->user_id = $user_id;
				$data->travelbuddy_save_post = "$id";
				$data->save();
				$result = array('status' => true, 'label' => $label);
				return json_encode($result, true);
			}
		}
    }

    public function localguidesaveevent($id, $user_id)
	{
        $label = 'Save';
        $Ids = array();
        if (isset($id) && $id != '') {
			$data = TravelSavePost::find()->where(['user_id' => $user_id])->one();
			if(!empty($data)) {
				if(isset($data['localguide_save_posts']) && $data['localguide_save_posts'] != '') {
					$Ids = $data['localguide_save_posts'];
					$Ids = explode(',', $Ids);
					if(!empty($Ids)) {
						if(in_array($id, $Ids)) {
							if (($key = array_search($id, $Ids)) !== false) {
							    unset($Ids[$key]);
							    $label = 'Unsave';
							}
						} else {
							$Ids[] = $id;
						}
					} else {
						$Ids[] = $id;
					}
				} else {
					$Ids[] = $id;
				}

				$Ids = implode(',', $Ids);
				if(empty($Ids)) {
					$data->localguide_save_posts = '';
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				} else {
					$data->localguide_save_posts = $Ids;
					$data->update();
					$result = array('status' => true, 'label' => $label);
					return json_encode($result, true);
				}
			} else {
				$data = new TravelSavePost();
				$data->user_id = $user_id;
				$data->localguide_save_posts = "$id";
				$data->save();
				$result = array('status' => true, 'label' => $label);
				return json_encode($result, true);
			}
		}
    }
}