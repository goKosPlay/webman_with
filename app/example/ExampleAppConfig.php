<?php

namespace app\example;

use app\attribute\dependency\{Configuration, Bean, Value};

#[Configuration]
class ExampleAppConfig
{
    #[Value(key: 'app.name', default: 'MyApp')]
    private string $appName;
    
    #[Value(key: 'app.debug', default: false)]
    private bool $debug;

    public function getDebug()
    {
        return $this->appName;
    }
    #[Bean(name: 'cache.manager', singleton: true)]
    public function cacheManager()
    {
        return new ExampleCacheService();
    }
    
    #[Bean(name: 'db.connection')]
    public function databaseConnection()
    {
        return new ExampleDatabaseConnection();
    }
}
