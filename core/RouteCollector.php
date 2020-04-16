<?php 

namespace Core;
use \ReflectionMethod;
use \ReflectionClass;

class RouteCollector extends \Phroute\Phroute\RouteCollector{
  
    public function __construct(){
        parent::__construct();
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
        
        if($methodName === self::DEFAULT_CONTROLLER_ROUTE) {
            $this->addRoute($requestMethod, $route . $params, [$classname, $method->name], $filters);
        }

        $this->addRoute($requestMethod, $route . $params, [$classname, $method->name], $filters);
     }
      
      return $this;
  }
}