<?php 

namespace Core;

use \Core\View;

class Controller {

    public function render($template, $data = []){
        $view = new View();
        return $view->render($template, $data);
    }
}