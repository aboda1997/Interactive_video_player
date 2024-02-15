<?php
include "DbConnect.php";

class Router
{
    protected $routes = []; // stores routes

    public function addRoute(string $method, string $url, closure $target)
    {
        $this->routes[$method][$url] = $target;
    }

    public function matchRoute()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $url = $_SERVER['REQUEST_URI'];

        $objDb = new DbConnect;
        $connection = $objDb->connect();
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routeUrl => $target) {
                // Use named subpatterns in the regular expression pattern to capture each parameter value separately
                $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $routeUrl);
                if (preg_match('#^' . $pattern . '$#', $url, $matches)) {
                    // Pass the captured parameter values as named arguments to the target function
                    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Only keep named subpattern matches
                    $params["connection"] = $connection;
                    call_user_func_array($target,   $params);
                    return;
                }
            }
        }
        throw new Exception('Route not found');
    }

}