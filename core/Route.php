<?php 

namespace Core;

use \Exception;
use Core\RouteCollector;
use Core\View;
use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\Exception\HttpMethodNotAllowedException;
use Phroute\Phroute\Exception\HttpRouteNotFoundException;

class Route {
  
    private static $collector;

    private static function getCollector() {
        if (!self::$collector instanceof RouteCollector) {
            self::$collector = new RouteCollector();
        }
        return self::$collector;
    }

    private static function buildRoute($method, $route, $handler, $filters){
        if (is_string( $handler ) ) {
            $controller = "\\Controller\\{$handler}";
            self::getCollector()->controller($method, $route, $controller, $filters);
        } else {
            self::getCollector()->{$method}($route, $handler, $filters);
        }
    }

    public static function dispatch($collector) {
        
        try {
            
            $routes = $collector->getData();
            $dispatcher = new Dispatcher($routes);
            echo $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        
        } catch(Exception $e) {
            switch (get_class($e)) {
                case 'Phroute\\Phroute\\Exception\\HttpRouteNotFoundException':
                    echo "asdsa";
                    break;
                case 'Phroute\\Phroute\\Exception\\HttpMethodNotAllowedException':
                    http_response_code(403);
                    break;
                default:
                    http_response_code(400);
                    break;
            }
        } catch(HttpRouteNotFoundException $e) {
            $theme = new View();
            return $theme->render('errors.404', [
                "debug" => boolval(getenv('DEBUG')),
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "previous" => $e->getPrevious(),
                "traceAsString" => $e->getTraceAsString()
            ]);
        }
    }

    public static function get($route, $handler, $filters = []) {
        self::buildRoute('get', $route, $handler, $filters);
    }

    public static function any($route, $handler, $filters = []) {
        self::buildRoute('any', $route, $handler, $filters);
    }

    public static function head($route, $handler, $filters = []) {
        self::buildRoute('head', $route, $handler, $filters);
    }

    public static function post($route, $handler, $filters = []) {
        self::buildRoute('post', $route, $handler, $filters);
    }

    public static function put($route, $handler, $filters = []) {
        self::buildRoute('put', $route, $handler, $filters);
    }

    public static function patch($route, $handler, $filters = []) {
        self::buildRoute('patch', $route, $handler, $filters);
    }

    public static function delete($route, $handler, $filters = []) {
        self::buildRoute('delete', $route, $handler, $filters);
    }

    public static function options($route, $handler, $filters = []) {
        self::buildRoute('options', $route, $handler, $filters);
    }

    public static function group($filters, $callback) {
        self::getCollector()->group($filters, $callback);
    }

    public static function filter($name, $handler) {
        self::getCollector()->filter($name, $handler);
    }



}


// try {
//   $dispatcher =  new Dispatcher($collector->getData());
//   // $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
// } catch(Exception $e) {
//   // switch (get_class($e)) {
//   //   case 'Phroute\\Phroute\\Exception\\HttpRouteNotFoundException':
//   //       http_response_code(404);
//   //       break;
//   //   case 'Phroute\\Phroute\\Exception\\HttpMethodNotAllowedException':
//   //       http_response_code(403);
//   //       break;
//   //   default:
//   //       http_response_code(400);
//   //       break;
//   // }
// }