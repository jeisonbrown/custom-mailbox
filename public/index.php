<?php

require __DIR__ . '/../core/autoload.php';

require __DIR__ . '/../core/Route.php';

require __DIR__ . '/../app/routes.php';

use Core\Route;

if ($collector) {
    Route::dispatch($collector);
}