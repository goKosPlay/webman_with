<?php

namespace app\support;

use app\attribute\routing\{
    Route as RouteAttribute,
    RequestMapping,
    GetMapping,
    PostMapping,
    PutMapping,
    DeleteMapping,
    PatchMapping
};
use app\attribute\dependency\{Controller, RestController};
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use Webman\Route;

class AttributeRouteLoader
{
    /**
     * @var bool Prevent duplicate loading
     */
    protected static bool $loaded = false;
    
    /**
     * Scan and register routes from controllers with Route attributes
     * 
     * @param string|array|null $directories Directories to scan (default: app/admin/controller, app/front/controller)
     * @param string $namespace Base namespace (default: app)
     * @return void
     */
    public static function load(string|array|null $directories = null, string $namespace = 'app'): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;
        
        if ($directories === null) {
            $baseDir = base_path() . '/app';
            $directories = [];
            
            foreach (['admin', 'front', 'api'] as $module) {
                $controllerDir = $baseDir . '/' . $module . '/controller';
                if (is_dir($controllerDir)) {
                    $directories[] = $controllerDir;
                }
            }
            
            if (is_dir($baseDir . '/controller')) {
                $directories[] = $baseDir . '/controller';
            }
        }
        
        $directories = is_array($directories) ? $directories : [$directories];
        
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            
            self::scanDirectory($directory, $namespace);
        }
    }
    
    /**
     * Recursively scan directory for controller classes
     */
    protected static function scanDirectory(string $directory, string $namespace): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                self::loadClassFromFile($file->getPathname(), $namespace);
            }
        }
    }
    
    /**
     * Load class from file and register routes
     */
    protected static function loadClassFromFile(string $filePath, string $namespace): void
    {
        $className = self::getClassNameFromFile($filePath);
        
        if (!$className || !class_exists($className)) {
            return;
        }
        
        try {
            $reflection = new ReflectionClass($className);
            
            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                return;
            }
            
            self::registerClassRoutes($reflection);
        } catch (\Throwable $e) {
            error_log("AttributeRouteLoader: Failed to load routes from {$className}: " . $e->getMessage());
        }
    }
    
    /**
     * Register routes from class methods with Route attributes
     */
    protected static function registerClassRoutes(ReflectionClass $class): void
    {
        $classPrefix = '';
        $classMiddleware = [];
        
        $controllerAttrs = $class->getAttributes(Controller::class);
        $restControllerAttrs = $class->getAttributes(RestController::class);
        $requestMappingAttrs = $class->getAttributes(RequestMapping::class);
        
        if (!empty($controllerAttrs)) {
            $ctrl = $controllerAttrs[0]->newInstance();
            $classPrefix = $ctrl->prefix;
            $classMiddleware = $ctrl->middleware;
        } elseif (!empty($restControllerAttrs)) {
            $ctrl = $restControllerAttrs[0]->newInstance();
            $classPrefix = $ctrl->prefix;
            $classMiddleware = $ctrl->middleware;
        } elseif (!empty($requestMappingAttrs)) {
            $ctrl = $requestMappingAttrs[0]->newInstance();
            $classPrefix = $ctrl->path;
            $classMiddleware = $ctrl->middleware;
        }
        
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            if ($method->isStatic() || $method->isConstructor() || $method->isDestructor()) {
                continue;
            }
            
            $routeAttributes = array_merge(
                $method->getAttributes(RouteAttribute::class),
                $method->getAttributes(RequestMapping::class),
                $method->getAttributes(GetMapping::class),
                $method->getAttributes(PostMapping::class),
                $method->getAttributes(PutMapping::class),
                $method->getAttributes(DeleteMapping::class),
                $method->getAttributes(PatchMapping::class)
            );
            
            foreach ($routeAttributes as $attribute) {
                try {
                    $routeAttr = $attribute->newInstance();
                    
                    // 使用闭包包装，支持依赖注入
                    $className = $class->getName();
                    $methodName = $method->getName();
                    
                    $callback = function() use ($className, $methodName) {
                        $injector = ControllerInjector::getInstance();
                        
                        // 如果需要依赖注入，使用 ControllerInjector
                        if ($injector->needsInjection($className)) {
                            $instance = $injector->make($className);
                        } else {
                            $instance = new $className();
                        }
                        
                        // 获取所有传递给闭包的参数
                        $args = func_get_args();
                        
                        // 调用方法
                        return call_user_func_array([$instance, $methodName], $args);
                    };
                    
                    $httpMethods = self::getHttpMethods($routeAttr);
                    $path = self::buildPath($classPrefix, $routeAttr->path ?: self::generatePathFromMethod($class, $method));
                    $middleware = array_merge($classMiddleware, $routeAttr->middleware ?? []);
                    
                    // Register route for each HTTP method
                    foreach ((array)$httpMethods as $httpMethod) {
                        $route = Route::add($httpMethod, $path, $callback);
                        
                        if ($route === null) {
                            error_log("AttributeRouteLoader: Route::add returned null for {$class->getName()}::{$method->getName()} [{$httpMethod}] {$path}");
                            continue;
                        }
                        
                        if (isset($routeAttr->name) && $routeAttr->name) {
                            $route->name($routeAttr->name);
                        }
                        
                        if (!empty($middleware)) {
                            $route->middleware($middleware);
                        }
                    }
                } catch (\Throwable $e) {
                    error_log("AttributeRouteLoader: Failed to register route for {$class->getName()}::{$method->getName()}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Get HTTP methods from route attribute
     */
    protected static function getHttpMethods(object $routeAttr): array
    {
        if ($routeAttr instanceof GetMapping) {
            return ['GET'];
        } elseif ($routeAttr instanceof PostMapping) {
            return ['POST'];
        } elseif ($routeAttr instanceof PutMapping) {
            return ['PUT'];
        } elseif ($routeAttr instanceof DeleteMapping) {
            return ['DELETE'];
        } elseif ($routeAttr instanceof PatchMapping) {
            return ['PATCH'];
        } elseif (isset($routeAttr->methods)) {
            return is_array($routeAttr->methods) ? $routeAttr->methods : [$routeAttr->methods];
        }
        
        return ['GET'];
    }
    
    /**
     * Build full path from class prefix and method path
     */
    protected static function buildPath(string $prefix, string $path): string
    {
        $prefix = rtrim($prefix, '/');
        $path = '/' . ltrim($path, '/');
        
        return $prefix . $path;
    }
    
    /**
     * Generate route path from class and method name
     */
    protected static function generatePathFromMethod(ReflectionClass $class, ReflectionMethod $method): string
    {
        $className = $class->getShortName();
        $methodName = $method->getName();
        
        $controller = preg_replace('/Controller$/', '', $className);
        $controller = self::camelToKebab($controller);
        
        $action = self::camelToKebab($methodName);
        
        if ($action === 'index') {
            return '/' . $controller;
        }
        
        return '/' . $controller . '/' . $action;
    }
    
    /**
     * Convert camelCase to kebab-case
     */
    protected static function camelToKebab(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $string));
    }
    
    /**
     * Get fully qualified class name from file path
     */
    protected static function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        
        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }
        
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }
        
        return $namespaceMatch[1] . '\\' . $classMatch[1];
    }
}
