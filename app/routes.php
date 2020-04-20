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

$collector->group(['before' => 'auth'], function($router){
    
    $router->get('/', 'InboxController::getIndex');
    $router->get('/{id:\d+}', 'InboxController::getDetail');
    
    $router->post('/delete/{id:\d+}', 'InboxController::deleteOne');
    $router->post('/mark-as/{id:\d+}', 'InboxController::markAs');

    $router->get('/logout', 'AuthController::getLogout');
    $router->post('/send', 'InboxController::sendMessage');
    $router->get('/attachments/{user_id:i}/{id}', 'InboxController::downloadAttachment');
    

});

$collector->group(['before' => 'no-auth'], function($router){
    
    $router->get('/login', 'AuthController::getLogin');
    $router->post('/login', 'AuthController::postLogin');
    $router->get('/forgot-password', 'AuthController::getForgotPassword');
    $router->get('/reset-password', 'AuthController::getResetPassword');

});




