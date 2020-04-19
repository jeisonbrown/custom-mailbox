<?php 

namespace Core;

use \Core\View;

class Controller {

    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function redirect($location, $params = []) {
        $_SESSION['REQUEST_REDIRECT_PARAMS'] = $params;
        header("Location: {$location}");
        return true;
    }

    public function render($template, $data = []){
        $view = new View();
        if(!empty($_SESSION['REQUEST_REDIRECT_PARAMS']) && is_array($_SESSION['REQUEST_REDIRECT_PARAMS'])){
            $data = array_merge($_SESSION['REQUEST_REDIRECT_PARAMS'], $data);
            unset($_SESSION['REQUEST_REDIRECT_PARAMS']);
        }
        return $view->render($template, $data);
    }
}