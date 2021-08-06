<?php

namespace Joonika;


use App\Controllers\SeriesController;

class Router
{
    private static $routes = [];
    private static $params = [];
    private static $instance = null;

    private static function createPattern($route)
    {
        $route = preg_replace('/^\//', '', $route);

        $route = preg_replace('/\//', '\\/', $route);

        $route = preg_replace('/\{([a-z]+)\}/', '(?<\1>[a-z0-9-]+)', $route);

        $route = '/' . $route . '\/?$/i';
        return $route;
    }

    private static function addRoutesExecute($route, $params, $method, $isResource = false)
    {
        $route = self::createPattern($route);
        if (is_string($params)) {
            list($allParams['controller'], $allParams['action']) = explode('@', $params);
        } elseif (is_array($params)) {
            list($allParams['controller'], $allParams['action']) = explode('@', $params['uses']);
            unset($params['uses']);
            $allParams = array_merge($allParams, $params);
        }
        $allParams['method'] = $method;

        self::$routes[$method][$route] = $allParams;
    }

    private static function addRoute($route, $params, $method, $isResource = false)
    {
        if ($isResource) {
            $allMethods = ['GET' => "index", 'POST' => 'store', 'PUT' => 'update', 'PATCH' => 'update', 'DELETE' => 'delete'];
            foreach ($allMethods as $method => $action) {
                self::addRoutesExecute($route, $params . '@' . $action, $method, $isResource);
            }
        } else {
            self::addRoutesExecute($route, $params, $method, $isResource);
        }

    }

    private function __construct($route, $action, $method, $isResource = false)
    {
        $this->addRoute($route, $action, $method, $isResource);
    }

    public static function get($route, $action)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Router($route, $action, 'GET');
        } else {
            self::addRoute($route, $action, 'GET');
        }
    }

    public static function post($route, $action)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Router($route, $action, 'POST');
        } else {
            self::addRoute($route, $action, 'POST');
        }
    }

    public static function put($route, $action)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Router($route, $action, 'PUT');
        } else {
            self::addRoute($route, $action, 'PUT');
        }
    }

    public static function patch($route, $action)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Router($route, $action, 'PATCH');
        } else {
            self::addRoute($route, $action, 'PATCH');
        }
    }

    public static function delete($route, $action)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Router($route, $action, 'DELETE');
        } else {
            self::addRoute($route, $action, 'DELETE');
        }
    }

    public static function resource($route, $action)
    {
        if (is_null(self::$instance)) {

            self::$instance = new Router($route, $action, 'RESOURCE', true);

        } else {
            self::addRoute($route, $action, 'RESOURCE', true);
        }
    }

    public static function match($url, $requestMethod)
    {
        if (sizeof(self::$routes[$requestMethod]) > 0) {
            $url = str_replace(' ', '', urldecode($url));
            foreach (self::$routes[$requestMethod] as $route => $params) {
                if (preg_match($route, explode('?', $url)[0], $matches)) {
                    foreach ($matches as $key => $match) {
                        if (is_string($key)) {
                            $params['params'][$key] = $match;
                        }
                    }
                    self::$params = $params;
                    return true;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    public static function getParams()
    {
        return self::$params;
    }

    public static function dispatch($url, $requestMethod, $dispathObject)
    {
        if (self::match($url, $requestMethod)) {
            $controller = self::$params['controller'];
            if (class_exists($controller)) {
                $params = array_key_exists('params', self::$params) && isset(self::$params['params']) ? self::$params['params'] : [];
                $requests = new Request();
                $controller_object = new $controller($requests);

                $method = self::$params['action'];
                if (is_callable([$controller_object, $method])) {
                    echo call_user_func_array([$controller_object, $method], $params);
                } else {
                    die("Method : {$method} ==> not is not found");
                }
            }
        } else {
            global $View;
            $dispathObject->exexDispath();
            $View->render();
        }
    }
}