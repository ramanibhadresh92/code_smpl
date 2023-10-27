<?php 
namespace frontend\models;
use Yii;
use yii\helpers\ArrayHelper;

use yii\mongodb\ActiveRecord;
use yii\mongodb\Query;
use yii\db\ActiveQuery;
use yii\db\Expression;
use frontend\models\Personalinfo;
use frontend\models\Language;
use frontend\models\BusinessCategory;
use frontend\models\Education;
use frontend\models\Interests;
use frontend\models\Occupation;
use frontend\models\CountryCode;


class DropdownFilter extends ActiveRecord 
{
	 
	public function filter($action, $fill, $selectore, $dummy)
    {
        $session = Yii::$app->session;                      
        $request = Yii::$app->request;
        $user_id = (string)$session->get('user_id');
        $radiobulk = array('joindate', 'lastlogin', 'pageservices');
        $actionArray = array('lastlogin', 'language', 'joindate', 'occupation', 'interest', 'education', 'pageservices','country','proficient');
        $isValid = false;
        $checkData = '';
                
        if(in_array($action, $actionArray)) {
            switch ($action) {
                case "pageservices":
                    $isValid = true;
                    $header = 'Choose page services';
                    $dataArray = ArrayHelper::map(BusinessCategory::find()->all(), 'name', 'name');
                    break;
                case "language":
                    $isValid = true; 
                    $header = 'Choose language';
                    $dataArray = ArrayHelper::map(Language::languages(), 'name', 'name');
                    break;
                case "occupation":
                    $isValid = true;
                    $header = 'Choose occupation';
                    $dataArray = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
                    break;
                case "proficient":
                    $isValid = true;
                    $header = 'Choose proficient';
                    $dataArray = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
                    break;
                case "interest":
                    $isValid = true;
                    $header = 'Choose interest';
                    $dataArray = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
                    break;
                case "education":
                    $isValid = true;
                    $header = 'Choose education';
                    $dataArray = ArrayHelper::map(Education::find()->all(), 'name', 'name');
                    break;
                case "lastlogin":
                    $isValid = true;
                    $header = 'Choose last login';
                    $dataArray = array('NT' => 'Anytime', '1' => 'Yesterday', '7' => 'Lastweek','30' => 'Lastmonth','365' => 'Lastyear', 'MORE' => 'More than a year');
                    break;
                case "joindate":
                    $isValid = true;
                    $header = 'Choose join date';
                    $dataArray = array('NT' => 'Anytime', '1' => 'Yesterday', '7' => 'Since a week','30' => 'Since a month','365' => 'Since a year', 'MORE' => 'More than a year');
                    break;
                case "country":
                    $isValid = true;
                    $header = 'Choose country';
                    $dataArray = ArrayHelper::map(CountryCode::find()->all(), 'country_name', 'country_name');
                    break;
            }

            if($fill == 'y') {
                $checkData = $dummy;
            }

            if (!is_array($checkData)) {
                $checkData = explode(",",$checkData);
            }

            if($isValid) { ?>
                <div class="hidden_header">
                    <div class="content_header">
                        <button class="close_span cancel_poup">
                            <i class="mdi mdi-close mdi-20px compose_discard_popup"></i>
                        </button>
                        <p class="modal_header_xs"><?=$header?></p>
                        <!-- <a type="button" class="post_btn action_btn post_btn_xs" onclick="addNewPost()">Post</a>                 -->
                        <?php if(in_array($action, $radiobulk)) { ?>
                        <a type="button" class="action_btn custom_close" href="javascript:void(0)" onclick="savedrpfilterdatardo('<?=$selectore?>')">Done</a>
                      <?php } else { ?>
                        <a type="button" class="action_btn custom_close" href="javascript:void(0)" onclick="savedrpfilterdatachk('<?=$selectore?>')">Done</a>
                      <?php } ?>
                    </div>
                </div>
                <div class="modal_content_child modal-content slot2">
                        <?php
                        foreach ($dataArray as $key => $sdata) {
                            $rand = rand(999, 99999);
                            $time = time();
                            $uniq = $time.$rand;
                            $checkedcls = '';
                            
                            if(in_array($key, $checkData)) {
                                $checkedcls = 'checked';
                            }

                            if(array_key_exists($key, $checkData)) {
                                $checkedcls = 'checked';
                            }

                            if(in_array($action, $radiobulk)) {
                                $keygen = $key; 
                                ?>
                                <p>
                                    <input type="radio" class="filterdrpchk" name="filterdrpchk" id="<?=$uniq?>" value="<?=$keygen?>" <?=$checkedcls?>/>
                                    <label for="<?=$uniq?>"><?=$sdata?></label>
                                </p>
                                <?php
                            } else {
                                $keygen = $sdata;
                                ?>
                                <p>
                                    <input type="checkbox" class="filterdrpchk" id="<?=$uniq?>" value="<?=$keygen?>" <?=$checkedcls?>/>
                                    <label for="<?=$uniq?>"><?=$sdata?></label>
                                </p>
                            <?php
                            }
                        }
                        ?>
                  </div>
                </div>
                <div class="valign-wrapper additem_modal_footer modal-footer">
                    <a href="javascript:void(0)" class="btngen-center-align close_modal open_discard_modal">Cancel</a>
                  <?php if(in_array($action, $radiobulk)) { ?>
                    <a href="javascript:void(0)" class="btngen-center-align" onclick="savedrpfilterdatardo('<?=$selectore?>')">Publish</a>
                  <?php } else { ?>
                    <a href="javascript:void(0)" class="btngen-center-align" onclick="savedrpfilterdatachk('<?=$selectore?>')">Publish</a>
                  <?php } ?>
                </div>
            <?php
            }
        } 
    }
}