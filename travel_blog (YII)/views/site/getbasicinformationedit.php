<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\models\LoginForm;
use frontend\models\CountryCode;
use frontend\models\UserSetting;
use frontend\models\Personalinfo;
use frontend\models\Language;
use frontend\models\Education;
use frontend\models\Interests;
use frontend\models\Occupation;

$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$user_id = (string)$session->get('user_id');

$result = LoginForm::find()->where(['email' => $email])->one();

    if(!empty($result)) {
        $user_id = (string) $result['_id'];
        $fname = $result['fname'];
        $lname = $result['lname'];
        $password = $result['password'];
        $con_password = $result['con_password'];
        $birth_date = $result['birth_date'];
        $gender = $result['gender'];
        $city = $result['city'];
        $country_code = $result['country_code'];
        $country = $result['country'];
        $phone = $result['phone'];
        $isd_code = $result['isd_code'];

        if($isd_code == '') {
            $countryCodeData = CountryCode::find()->where(['code' => strtoupper($country_code)])->orwhere(['country_name' => strtoupper($country)])->asarray()->one();
            if(!empty($countryCodeData)) {
                $isd_code = $countryCodeData['isd_code'];
            }
        }
        
        $alternate_email = $result['alternate_email'];
        if(isset($result['pwd_changed_date']) && !empty($result['pwd_changed_date'])) {
            $result['pwd_changed_date'] = $result['pwd_changed_date'];
        } else {
            $result['pwd_changed_date'] = $result['created_date'];
        }

        $pwd_changed_date = Yii::$app->EphocTime->time_pwd_changed(time(),$result['pwd_changed_date']);
        $pwd_changed_date = date('F d, Y',$result['pwd_changed_date']);
            
        $result_setting = UserSetting::find()->where(['user_id' => $user_id])->one();
        $email_access = $result_setting['email_access'];
        $alternate_email_access = $result_setting['alternate_email_access'];
        $mobile_access = $result_setting['mobile_access'];
        $birth_date_access = $result_setting['birth_date_access'];
        $gender_access = $result_setting['gender_access'];
        $language_access = $result_setting['language_access'];
        $religion_access = $result_setting['religion_access'];
        $political_view_access = $result_setting['political_view_access'];

        $result_personal = Personalinfo::find()->where(['user_id' => $user_id])->one();

        $about = $result_personal['about'];
        $education = $result_personal['education'];
        if($education=='null'){$education='';}
        $education = explode(',', $education);
        
        $interests = $result_personal['interests'];
        if($interests=='null'){$interests='';}
        $interests = explode(',', $interests);
        
        $occupation = $result_personal['occupation'];
        if($occupation=='null'){$occupation='';}
        $occupation = explode(',', $occupation);
        
        $hometown = $result_personal['hometown'];    

        $language = $result_personal['language'];
        if($language=='null'){$language='';}
        $language = explode(',', $language);
        
        $religion = $result_personal['religion'];
        $political_view = $result_personal['political_view'];

        $languagearray = ArrayHelper::map(Language::languages(), 'name', 'name');    
        $languagearray = array_filter($languagearray);

        $educationarray = ArrayHelper::map(Education::find()->all(), 'name', 'name');
        $educationarray = array_filter($educationarray);

        $interestsarray = ArrayHelper::map(Interests::find()->all(), 'name', 'name');
        $interestsarray = array_filter($interestsarray);

        $occupationarray = ArrayHelper::map(Occupation::find()->all(), 'name', 'name');
        $occupationarray = array_filter($occupationarray);
        ?>
        
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Name</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s12 m6 l6">
                                    <div class="sliding-middle-out anim-area underlined fullwidth">
                                        <input type="text" placeholder="First name" name="LoginForm[fname]" value="<?=$fname?>" id="fname" class="capitalize"/>
                                    </div>
                                </div>
                                <div class="col s12 m6 l6">     
                                    <div class="sliding-middle-out anim-area underlined fullwidth">
                                        <input type="text" placeholder="Last name" value="<?= $lname?>" name="LoginForm[lname]" id="lname" class="capitalize">
                                    </div>
                                </div>
                            </div>                                      
                        </div>      
                    </div>  
                </div>
            </div>
        </li>
        
        <!-- email -->                      
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Email</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s12 m12 l6">
                                    <div class="sliding-middle-out anim-area underlined fullwidth">
                                        <input type="text" placeholder="Your Email Address" value="<?=$email?>" name="LoginForm[email]" id="bemail">
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>
                    </div>  
                </div>
            </div>
        </li>
    
        <!-- alternate email -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Alternate Email</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s12 m12 l6">
                                    <div class="sliding-middle-out anim-area underlined fullwidth">
                                        <input type="text" name="LoginForm[alternate_email]" placeholder="Alternate Email Address" value="<?php echo $alternate_email; ?>" id="alternate_email"/>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>
                    </div>  
                </div>
            </div>
        </li>
        <?php if(!(isset($result['fb_id']) && !empty($result['fb_id']))) { ?>
        <!-- password -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                 <div class="row">
                  <div class="col s12 m3 l2">
                   <div class="caption">
                    <label>Password</label>
                   </div>
                  </div>
                  <div class="col s12 m9 l10">
                   <div class="row">
                    <div class="col s12 m12 l6">    
                     <div class="form-group eyeicon">      
                      <div class="sliding-middle-out anim-area underlined fullwidth">
                         <input type="password" placeholder="Type current password" value="" name="old_password" id="old_password"/>
                         <input type="hidden" name="old_real_pwd" id="old_real_pwd" value="<?= $password?>"/>
                        <a href="javascript:void(0)" class="showPass"><i class="mdi mdi-eye"></i></a>
                      </div>
                     </div>
                    </div>            
                   </div> 
                   <div class="row">
                    <div class="col s12 m12 l6">
                     <div class="form-group eyeicon">      
                      <div class="sliding-middle-out anim-area underlined fullwidth">
                       <input type="password" placeholder="Type new password" id="password" name="LoginForm[password]"/>
                        <a href="javascript:void(0)" class="showPass"><i class="mdi mdi-eye"></i></a>
                      </div>
                     </div>
                    </div>            
                   </div>
                   <div class="row">
                    <div class="col s12 m12 l6">    
                     <div class="eyeicon">      
                      <div class="sliding-middle-out anim-area underlined fullwidth">
                       <input type="password" placeholder="Retype new password" id="con_password" name="LoginForm[con_password]"/>
                        <a href="javascript:void(0)" class="showPass"><i class="mdi mdi-eye"></i></a>
                      </div>
                     </div>
                    </div>            
                   </div>           
                  </div> 
                 </div> 
                
                </div>

            </div>
        </li>
        <?php } ?>
    
        <!-- city -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>City</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s12 m12 l6">                
                                        <div class="sliding-middle-out anim-area underlined fullwidth">
                                            <input type="text" id="autocomplete1" class="capitalize" name="LoginForm[city]"  placeholder="City" value="<?=$city?>" data-query="M" onfocus="filderMapLocationModal(this)" autocomplete="off"/>
                                        </div>
                                </div>                                              
                            </div>                                      
                        </div>  
                    </div>  
                </div>
            </div>
        </li>
        
        <!-- country -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Country</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s12 m12 l6">
                                    <div class="sliding-middle-out anim-area underlined fullwidth">
                                        <input type="text" placeholder="Country" id="country" name="LoginForm[country]" value="<?=$country?>"/>
                                        <input id="au_country" type="hidden" name="abc" value="" class="au_country"/>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>
                    </div>  
                </div>
            </div>
        </li>
    
        <!-- mobile -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Mobile</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s3 m4 l2 col-lg-2">
                                    <input type="text" id="isd_code" placeholder="isd code" readonly="true" value="<?= $isd_code?>" name="isd_code"/>
                                </div>
                                <div class="col s8 m8 l4">
                                    <div class="sliding-middle-out anim-area underlined fullwidth">
                                        <input type="text" id="phone" class="title" id="" onkeyup="checkAvailability()" placeholder="Mobile No" name="LoginForm[phone]" value="<?= $phone ?>"/>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>  
                    </div>  
                </div>
            </div>
        </li>
        
        <!-- about us -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>About Yourself</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10">
                            <div class="row">
                                <div class="col s12 m12 l12">
                                    <div class="cmntarea underlined fullwidth">

                                    <textarea data-adaptheight class="materialize-textarea mb0 md_textarea descinput data-adaptheight" id="personalinfo-about" placeholder="Something about you..." name="Personalinfo[about]"><?=trim($about)?></textarea>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>
                    </div>  
                </div>
            </div>
        </li>
        
        <!-- language -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Language</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 dropdown782" id="languagedropdown"> 
                            <div class="row">
                                <div class="col s12 m12 l12">
                                    <div class="sliding-middle-out anim-area underlined fullwidth width500">
                                    <select id="language1" data-fill="y" data-action="language" class="languagedrp select-dropdown initialized" data-selectore="languagedrp" name="Personalinfo[language][]" multiple="multiple" size="4" style="width: 100%" data-select-id="092f8d92-f83d-9e0b-0fbf-69c3b8e89f1a">
                                        <option value="" disabled selected>Choose language</option>
                                        <?php
                                        foreach ($languagearray as $slanguagearray) {
                                            $langcls = '';
                                            if(in_array($slanguagearray, $language)) {
                                                $langcls = 'selected';
                                            }
                                            ?>
                                            <option value="<?=$slanguagearray?>" <?=$langcls?>><?=$slanguagearray?></option>
                                            <?php
                                        } 
                                        ?>
                                    </select>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>      
                    </div>  
                </div>
            </div>
        </li>
        <!-- education -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Education</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 dropdown782" id="educationdropdown">
                            <div class="row">
                                <div class="col s12 m12 l12">
                                    <div class="sliding-middle-out anim-area underlined fullwidth width500">
                                        <select id="education1" data-fill="y" data-action="education" data-selectore="educationdrp" class="educationdrp select-dropdown initialized" name="Personalinfo[education][]" multiple="multiple" size="4" style="width: 100%">
                                            <option value="" disabled selected>Choose education</option>
                                            <?php
                                            foreach ($educationarray as $seducationarray) {
                                                $seducationarraycls = '';
                                                if(in_array($seducationarray, $education)) {
                                                    $seducationarraycls = 'selected';
                                                }
                                                ?>
                                                <option value="<?=$seducationarray?>" <?=$seducationarraycls?>><?=$seducationarray?></option>
                                                <?php
                                            } 
                                            ?>
                                        </select>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>  
                    </div>  
                </div>
            </div>
        </li>
        
        <!-- interests -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Interest</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 dropdown782" id="interestsdropdown"> 
                            <div class="row">
                                <div class="col s12 m12 l12">
                                    <div class="sliding-middle-out anim-area underlined fullwidth width500">
                                        <select data-fill="y" data-action="interest" id="interests1" data-selectore="interestdrp" class="interestdrp select-dropdown initialized" name="Personalinfo[interests][]" multiple="multiple" size="4" style="width: 100%">
                                            <option value="" disabled selected>Choose interest</option>
                                            <?php
                                            foreach ($interestsarray as $sinterestsarray) {
                                                $sinterestsarraycls = '';
                                                if(in_array($sinterestsarray, $interests)) {
                                                    $sinterestsarraycls = 'selected';
                                                }
                                                ?>
                                                <option value="<?=$sinterestsarray?>" <?=$sinterestsarraycls?>><?=$sinterestsarray?></option>
                                                <?php
                                            } 
                                            ?>
                                        </select>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>
                    </div>  
                
                </div>
            </div>
        </li>
        
        <!-- occupation -->
        <li>
            <div class="settings-group">
                <div class="edit-mode">
                    <div class="row">
                        <div class="col s12 m3 l2">
                            <div class="caption">
                                <label>Occupation</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 dropdown782" id="occupationdropdown">
                            <div class="row">
                                <div class="col s12 m12 l12">
                                    <div class="sliding-middle-out anim-area underlined fullwidth width500">
                                        <select id="occupations1" data-fill="y" data-action="occupation" data-selectore="occupationdrp" class="occupationdrp select-dropdown initialized" name="Personalinfo[occupation][]" multiple="multiple" size="4" style="width: 100%">
                                            <option value="" disabled selected>Choose occupation</option>
                                            <?php
                                            foreach ($occupationarray as $soccupationarray) {
                                                $soccupationarraycls = '';
                                                if(in_array($soccupationarray, $occupation)) {
                                                    $soccupationarraycls = 'selected';
                                                }
                                                ?>
                                                <option value="<?=$soccupationarray?>" <?=$soccupationarraycls?>><?=$soccupationarray?></option>
                                                <?php
                                            } 
                                            ?>
                                        </select>
                                    </div>
                                </div>                                              
                            </div>                                      
                        </div>  
                    </div>  
                </div>
            </div>
        </li>

        <li>
            <div class="personal-info fullwidth edit-mode">
                <div class="right">                                   
                   <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_act_bf_cl(false)">Cancel</a>                                    
                   <a href="javascript:void(0)" class="btngen-center-align waves-effect" onclick="open_edit_act_bf_cl(true)">Save</a>
                </div>
            </div>
        </li>

        <?php

    }
?>

<?php
exit;