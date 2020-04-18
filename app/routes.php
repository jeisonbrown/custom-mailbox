<?php

use \Core\RouteCollector;

$collector = new RouteCollector();

$collector->filter('auth', function(){
    if(!isset($_SESSION['auth'])) {
        header('Location: /login');   
        return false;
    }
});

$collector->filter('no-auth', function(){
    if(isset($_SESSION['auth'])) {
        header('Location: /');   
        return false;
    }
});

$collector->get('/signup', 'AuthController::getSignup');

$collector->group(['before' => 'auth'], function($router){
    
    $router->get('/', 'InboxController::getIndex');
    $router->get('/{id:\d+}', 'InboxController::getDetail');
    $router->get('/logout', 'AuthController::getLogout');
    $router->post('/send', 'InboxController::sendMessage');

});

$collector->group(['before' => 'no-auth'], function($router){
    
    $router->get('/login', 'AuthController::getLogin');
    $router->post('/login', 'AuthController::postLogin');
    $router->get('/forgot-password', 'AuthController::getForgotPassword');
    $router->get('/reset-password', 'AuthController::getResetPassword');

});




