<?php

use \Core\RouteCollector;

$collector = new RouteCollector();

$collector->filter('auth', function(){

    if(!isset($_SESSION['AUTH'])) {
        header('Location: /login');   
        return false;
    }
    
});

$collector->filter('no-auth', function(){
    if(isset($_SESSION['AUTH'])) {
        header('Location: /');   
        return false;
    }
});

$collector->filter('admin', function(){
    $isAdmin = $_SESSION['USER_ROLE'] == 1;
    $isSuperAdmin = $_SESSION['USER_ROLE'] == 2;
    if(!$isAdmin && !$isSuperAdmin) {
        header('Location: /');   
        return false;
    }
});

$collector->group(['before' => 'admin'], function($router){
    $router->get('/user/{id}', 'UserController::getUser');
    $router->post('/user/{id}', 'UserController::postUser');
    $router->get('/users', 'UserController::getUsers');
});

$collector->group(['before' => 'auth'], function($router){
    
    $router->get('/', 'InboxController::getIndex');
    $router->get('/{id:\d+}', 'InboxController::getDetail');
    
    $router->post('/delete/{id:\d+}', 'InboxController::deleteOne');
    $router->post('/mark-as/{id:\d+}', 'InboxController::markAs');

    $router->get('/logout', 'AuthController::getLogout');
    $router->post('/send', 'InboxController::sendMessage');
    $router->get('/attachments/{user_id:i}/{id}', 'InboxController::downloadAttachment');
    $router->post('/notifications/clear-all', 'NotificationController::clearAll');
    $router->post('/notifications/goto/{id:\d+}', 'NotificationController::goto');

    $router->get('/profile', 'UserController::getUser');
    $router->post('/profile', 'UserController::postUser');
});

$collector->group(['before' => 'no-auth'], function($router){
    
    $router->get('/login', 'AuthController::getLogin');
    $router->post('/login', 'AuthController::postLogin');
    $router->get('/forgot-password', 'AuthController::getForgotPassword');
    $router->post('/forgot-password', 'AuthController::postForgotPassword');
});

$collector->get('/reset-password/{token}?', 'AuthController::getResetPassword');
$collector->post('/reset-password/{token}?', 'AuthController::postResetPassword');

