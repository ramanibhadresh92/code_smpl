<?php


use frontend\assets\AppAsset; 
use yii\web\View;

$asset = frontend\assets\AppAsset::register($this);
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session;
$email = $session->get('email');
$theme_user_id = (string)$session->get('user_id');
?>
    <ul class="pages">
        <li class="chat page" id="exretiredment">
            <div class="chatArea">
                <ul class="messages" id="exview"></ul>
            </div>
            <input class="inputMessage" placeholder="Type here..." />
            <input type="file" id="imageFile" />
        </li>  
        <li class="login page">
            <div class="form">
                <h3 class="title">What's your nickname?</h3>
                <input class="usernameInput" type="text" maxlength="14" />
            </div>
        </li>
    </ul>