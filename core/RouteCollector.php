<?php 

namespace Core;
use \ReflectionMethod;
use \ReflectionClass;

class RouteCollector extends \Phroute\Phroute\RouteCollector {
  
    public function __construct(){
        parent::__construct();
    }

    private function buildRoute($method, $route, $handler, $filters){
        if (is_string( $handler ) ) {
            $controller = "\\Controller\\{$handler}";
            $this->controller($method, $route, $controller, $filters);
        } else {
            $this->{$method}($route, $handler, $filters);
        }
    }

    private function camelCaseToDashed($string) {
        return strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($string)));
    }

    private function buildControllerParameters(ReflectionMethod $method) {
        $params = '';
        foreach($method->getParameters() as $param) {
            $params .= "/{" . $param->getName() . "}" . ($param->isOptional() ? '?' : '');
        }

        return $params;
    }

    public function get($route, $handler, $filters = []) {
        $this->buildRoute('get', $route, $handler, $filters);
    }

    public function any($route, $handler, $filters = []) {
        $this->buildRoute('any', $route, $handler, $filters);
    }

    public function head($route, $handler, $filters = []) {
        $this->buildRoute('head', $route, $handler, $filters);
    }

    public function post($route, $handler, $filters = []) {
        $this->buildRoute('post', $route, $handler, $filters);
    }

    public function put($route, $handler, $filters = []) {
        $this->buildRoute('put', $route, $handler, $filters);
    }

    public function patch($route, $handler, $filters = []) {
        $this->buildRoute('patch', $route, $handler, $filters);
    }

    public function delete($route, $handler, $filters = []) {
        $this->buildRoute('delete', $route, $handler, $filters);
    }

    public function options($route, $handler, $filters = []) {
        $this->buildRoute('options', $route, $handler, $filters);
    }
   

    public function controller($requestMethod, $route, $classnameFunc, array $filters = []) {
        
      $custom = explode('::', $classnameFunc);
      if(empty($custom[0]) || empty($custom[1])){
          parent::controller($route, $classnameFunc, $filters);
          return $this;
      }
      
      $method = false;
      $classname = $custom[0];
      $funcName = $custom[1];
      $requestMethod = strtoupper($requestMethod);
      $sep = $route === '/' ? '' : '/';

      $reflection = new ReflectionClass($classname);      
      foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $value){
        if($value->name === $funcName) {
            $method = $value;
            break;
        }
      }
      
      if(!$method) {
          return $this;
      }

      if(in_array($requestMethod, $this->getValidMethods())) {

        $methodName = $this->camelCaseToDashed($method->name);
        
        $params = $this->buildControllerParameters($method);

        $this->addRoute($requestMethod, $route, [$classname, $method->name], $filters);
        
    }
      
      return $this;
  }
}