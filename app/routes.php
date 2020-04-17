<?php 

use Core\Route;
Route::get('/', 'InboxController::getIndex');
Route::get('/{id:\d+}', 'InboxController::getDetail');
