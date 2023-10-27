<?php   
use frontend\assets\AppAsset;
use yii\helpers\Url;
use frontend\models\LoginForm;
use frontend\models\CountryCode;
use frontend\models\UserSetting;
use frontend\models\Personalinfo;

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
        
        $interests = $result_personal['interests'];
        if($interests=='null'){$interests='';}
        
        $occupation = $result_personal['occupation'];
        if($occupation=='null'){$occupation='';}
        
        $hometown = $result_personal['hometown'];    

        $language = $result_personal['language'];
        if($language=='null'){$language='';}
        
        $religion = $result_personal['religion'];
        $political_view = $result_personal['political_view'];


        ?>
        
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Name</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">  
                            <div class="info">
                                <label id="name"><?= $fname ?> <?= $lname ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        
        <!-- email -->                      
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Email</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">                          
                            <div class="info">
                                <label id="email"><?= $email ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    
        <li>
        <!-- alternate email -->
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Alternate Email</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                            <label id="alt-email">
                                <?php
                                if($alternate_email == ""){
                                    echo 'No alternate email set';
                                } else {
                                    echo $alternate_email;
                                }
                                ?>
                            </label>
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
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Password</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="pwd-change">Password updated on <?= $pwd_changed_date?></label>
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
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>City</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="city">
                                    <?php
                                    if($city == "") {
                                        echo 'No city added';
                                    } else {
                                        echo $city;
                                    }
                                    ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        
        <!-- country -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Country</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="country1">
                                <?php
                                if($country == ""){
                                        echo 'No country added';
                                } else {
                                    echo $country;
                                }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    
        <!-- mobile -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Mobile</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                            <label id="phone2">
                            </label>
                                <label id="phone1">
                                <?php
                                if($phone == ""){
                                    echo 'Add mobile number';
                                } else {
                                    echo $isd_code.' '.$phone;
                                }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        
        <!-- birth date -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Birth Date</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">  
                            <div class="info">      
                            <label id="birth_date"> 
                                <?php
                                if($birth_date == ""){
                                    echo 'No birthdate set';
                                } else {
                                    $birth_date2 = strtotime($birth_date);
                                    $day=date("d",$birth_date2);
                                    $month = date("F",$birth_date2);
                                    $year=date("Y",$birth_date2);
                                    ?>
                                    <?=$month?> <?=$day?>, <?=$year?>
                                <?php 
                                }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>
        </li>
        
        <!-- gender -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Gender</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">      
                            <label  id="gender">    
                                    <?= $gender ?>
                                </label>
                            </div>
                        </div>
                    </div>  
                </div>
            </div>
        </li>
                  
        <!-- about us -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>About Yourself</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="about">
                                <?php
                                if($about == ""){
                                    echo 'Add about yourself';
                                } else {
                                    echo trim($about);
                                }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        
        <!-- language -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Language</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="language">
                                <?php
                                    if($language == ""){
                                        echo 'No language set';
                                    } else {
                                        echo str_replace(",", ", ", $language);
                                    }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <!-- education -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Education</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="education">
                                <?php
                                if($education == ""){
                                    echo 'No education set';
                                } else {
                                    echo str_replace(",", ", ", $education);
                                }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        
        <!-- interests -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Interest</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="interests">
                            <?php
                            if($interests == ""){
                                echo 'No interest set';
                            } else {
                                echo str_replace(",", ", ", $interests);
                            }
                            ?>
                            </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        
        <!-- occupation -->
        <li>
            <div class="settings-group">
                <div class="normal-mode">
                    <div class="row">
                        <div class="col s12 m3 l2 caption-holder">
                            <div class="caption">
                                <label>Occupation</label>
                            </div>
                        </div>
                        <div class="col s12 m9 l10 detail-holder">
                            <div class="info">
                                <label id="occupation">
                                <?php
                                if($occupation == ""){
                                    echo 'No occupation set';
                                } else {
                                    echo str_replace(",", ", ", $occupation);
                                }
                                ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>

        <?php

    }
?>

<?php
exit;