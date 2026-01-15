<?php
require_once __DIR__ . '/vendor/autoload.php';

use Webman\Route;
use app\support\AttributeRouteLoader;

// 禁用默认路由
Route::disableDefaultRoute();

// 加载 Attribute 路由
AttributeRouteLoader::load();

// 获取所有已注册的路由
$routes = Route::getRoutes();

echo "已注册的路由列表：\n";
echo str_repeat('=', 80) . "\n";

foreach ($routes as $route) {
    $methods = implode('|', $route->getMethods());
    $path = $route->getPath();
    $name = $route->getName() ?: '-';
    $callback = $route->getCallback();
    
    if (is_array($callback)) {
        $handler = $callback[0] . '::' . $callback[1];
    } else {
        $handler = 'Closure';
    }
    
    printf("%-15s %-30s %-20s %s\n", $methods, $path, $name, $handler);
}

echo str_repeat('=', 80) . "\n";
echo "总计: " . count($routes) . " 个路由\n";
