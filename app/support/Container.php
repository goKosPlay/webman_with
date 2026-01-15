<?php

namespace app\support;

use app\attribute\dependency\{
    Autowired,
    Inject,
    Lazy,
    Value,
    Qualifier,
    Service,
    Component,
    Repository,
    Bean,
    Configuration,
    Primary,
    Scope
};
use app\attribute\log\{Loggable, Log, LogPerformance, LogException, LogContext};
use ReflectionClass;
use ReflectionProperty;
use ReflectionParameter;
use ReflectionMethod;

class Container
{
    protected static ?self $instance = null;
    
    protected array $bindings = [];
    protected array $instances = [];
    protected array $aliases = [];
    protected array $resolved = [];
    protected array $lazyProxies = [];
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function bind(string $abstract, mixed $concrete = null, bool $singleton = false): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }
    
    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }
    
    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }
    
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }
    
    public function make(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->getAlias($abstract);
        
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        $concrete = $this->getConcrete($abstract);
        
        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete, $parameters);
        } else {
            $object = $this->make($concrete, $parameters);
        }
        
        if ($this->isSingleton($abstract)) {
            $this->instances[$abstract] = $object;
        }
        
        $this->resolved[$abstract] = true;
        
        return $object;
    }
    
    public function makeLazy(string $abstract): mixed
    {
        if (isset($this->lazyProxies[$abstract])) {
            return $this->lazyProxies[$abstract];
        }
        
        $proxy = new class($this, $abstract) {
            private $container;
            private $abstract;
            private $instance = null;
            
            public function __construct($container, $abstract)
            {
                $this->container = $container;
                $this->abstract = $abstract;
            }
            
            private function getInstance()
            {
                if ($this->instance === null) {
                    $this->instance = $this->container->make($this->abstract);
                }
                return $this->instance;
            }
            
            public function __call($method, $args)
            {
                return $this->getInstance()->$method(...$args);
            }
            
            public function __get($name)
            {
                return $this->getInstance()->$name;
            }
            
            public function __set($name, $value)
            {
                $this->getInstance()->$name = $value;
            }
            
            public function __isset($name)
            {
                return isset($this->getInstance()->$name);
            }
            
            public function __unset($name)
            {
                unset($this->getInstance()->$name);
            }
        };
        
        $this->lazyProxies[$abstract] = $proxy;
        return $proxy;
    }
    
    protected function build(string $concrete, array $parameters = []): mixed
    {
        $reflector = new ReflectionClass($concrete);
        
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }
        
        $constructor = $reflector->getConstructor();
        
        if ($constructor === null) {
            $instance = new $concrete();
        } else {
            $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);
            $instance = $reflector->newInstanceArgs($dependencies);
        }
        
        $this->injectProperties($instance, $reflector);
        
        return $instance;
    }
    
    protected function injectProperties(object $instance, ReflectionClass $reflector): void
    {
        foreach ($reflector->getProperties() as $property) {
            $this->injectProperty($instance, $property);
        }
    }
    
    protected function injectProperty(object $instance, ReflectionProperty $property): void
    {
        $autowired = $property->getAttributes(Autowired::class);
        $inject = $property->getAttributes(Inject::class);
        $lazy = $property->getAttributes(Lazy::class);
        $value = $property->getAttributes(Value::class);
        
        if (!empty($autowired)) {
            $attr = $autowired[0]->newInstance();
            $type = $property->getType();
            
            if ($type && !$type->isBuiltin()) {
                try {
                    $dependency = $this->make($type->getName());
                    $property->setValue($instance, $dependency);
                } catch (\Exception $e) {
                    if ($attr->required) {
                        throw $e;
                    }
                }
            }
        } elseif (!empty($inject)) {
            $attr = $inject[0]->newInstance();
            
            $name = $attr->name;
            if ($name === null) {
                $type = $property->getType();
                $name = $type ? $type->getName() : null;
            }
            
            if ($name) {
                try {
                    $dependency = $this->make($name);
                    $property->setValue($instance, $dependency);
                } catch (\Exception $e) {
                    if ($attr->required) {
                        throw $e;
                    }
                }
            }
        } elseif (!empty($lazy)) {
            $attr = $lazy[0]->newInstance();
            
            $service = $attr->service;
            if ($service === null) {
                $type = $property->getType();
                $service = $type ? $type->getName() : null;
            }
            
            if ($service) {
                $proxy = $this->makeLazy($service);
                $property->setValue($instance, $proxy);
            }
        } elseif (!empty($value)) {
            $attr = $value[0]->newInstance();
            
            $configValue = $this->getConfigValue($attr->key, $attr->default);
            $property->setValue($instance, $configValue);
        }
    }
    
    protected function resolveDependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            if (array_key_exists($parameter->getName(), $primitives)) {
                $dependencies[] = $primitives[$parameter->getName()];
                continue;
            }
            
            $type = $parameter->getType();
            
            if ($type === null || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve parameter {$parameter->getName()}");
                }
            } else {
                $dependencies[] = $this->make($type->getName());
            }
        }
        
        return $dependencies;
    }
    
    protected function getConfigValue(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = config($keys[0]);
        
        array_shift($keys);
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value ?? $default;
    }
    
    protected function getConcrete(string $abstract): mixed
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }
        
        return $abstract;
    }
    
    protected function isBuildable(mixed $concrete, string $abstract): bool
    {
        return $concrete === $abstract || is_string($concrete);
    }
    
    protected function isSingleton(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) && $this->bindings[$abstract]['singleton'];
    }
    
    protected function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }
    
    public function scanAndRegister(string $directory, string $namespace = 'app'): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->registerClassFromFile($file->getPathname());
            }
        }
    }
    
    protected function registerClassFromFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return;
        }
        
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return;
        }
        
        $className = $namespaceMatch[1] . '\\' . $classMatch[1];
        
        if (!class_exists($className)) {
            return;
        }
        
        try {
            $reflector = new ReflectionClass($className);
            $this->registerClass($reflector);
        } catch (\Exception $e) {
            error_log("Container: Failed to register {$className}: " . $e->getMessage());
        }
    }
    
    protected function registerClass(ReflectionClass $reflector): void
    {
        $service = $reflector->getAttributes(Service::class);
        $component = $reflector->getAttributes(Component::class);
        $repository = $reflector->getAttributes(Repository::class);
        $configuration = $reflector->getAttributes(Configuration::class);

        
        if (!empty($service)) {
            $attr = $service[0]->newInstance();
            $isSingleton = $attr->singleton;
            $name = $attr->name;
        } elseif (!empty($component)) {
            $attr = $component[0]->newInstance();
            $isSingleton = $attr->singleton;
            $name = $attr->name;
        } elseif (!empty($repository)) {
            $attr = $repository[0]->newInstance();
            $isSingleton = $attr->singleton;
            $name = $attr->name;
        } elseif (!empty($configuration)) {
            $this->registerConfiguration($reflector);
            return;
        } else {
            return;
        }
        
        $className = $reflector->getName();
        
        $scope = $reflector->getAttributes(Scope::class);
        if (!empty($scope)) {
            $scopeAttr = $scope[0]->newInstance();
            if ($scopeAttr->value === Scope::SINGLETON) {
                $isSingleton = true;
            } elseif ($scopeAttr->value === Scope::PROTOTYPE) {
                $isSingleton = false;
            }
        }
        
        if ($name) {
            $this->bind($name, $className, $isSingleton);
            $this->alias($className, $name);
        } else {
            $this->bind($className, $className, $isSingleton);
        }
    }
    
    protected function registerConfiguration(ReflectionClass $reflector): void
    {
        $className = $reflector->getName();
        
        // Build the configuration instance with property injection
        $instance = $this->build($className);
        
        // Register the configuration class itself as a singleton
        $this->instance($className, $instance);
        
        // Register beans defined in the configuration
        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $beanAttrs = $method->getAttributes(Bean::class);
            
            foreach ($beanAttrs as $beanAttr) {
                $bean = $beanAttr->newInstance();
                $beanInstance = $method->invoke($instance);
                
                $name = $bean->name ?? $method->getName();
                
                if ($bean->singleton) {
                    $this->instance($name, $beanInstance);
                } else {
                    $this->bind($name, function() use ($instance, $method) {
                        return $method->invoke($instance);
                    });
                }
            }
        }
    }
}
