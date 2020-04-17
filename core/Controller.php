<?php 

namespace Core;

use \Core\View;

class Controller {

    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function redirect($location, $params = []) {
        $queryStringArray = [];
        foreach($params as $key => $param){
            $queryStringArray[] = "{$key}={$param}";
        }
        $queryString = count($queryStringArray) ? '?' . implode('&', $queryStringArray) : '';
        header("Location: {$location}{$queryString}");
    }

    public function render($template, $data = []){
        $view = new View();
        return $view->render($template, $data);
    }
}