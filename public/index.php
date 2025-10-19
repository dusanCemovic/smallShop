<?php

// loading database (PDO)
require_once __DIR__ . '/../App/Models/DB.php';

// this is simple autoloader
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) require $path;
});

// parse route from GET parameter.
// if nothing is added, then articles index is default. Getting route into array
$route = explode('.', $_GET['route'] ?? 'articles.index');
$route[1] = $route[1] ?? 'index';

// checking if class exists
$controllerClass = '\\App\\Controllers\\' . ucfirst($route[0]) . 'Controller';
if (!class_exists($controllerClass)) {
    echo "Controller " . $controllerClass . " not found";
    exit;
}

// checking if method exists
$controller = new $controllerClass();
if (!method_exists($controller, $route[1])) {
    echo "Method " . $route[1] . " not found";
    exit;
}

try {
    // send to method
    $controller->{$route[1]}();
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Server error</h1><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

