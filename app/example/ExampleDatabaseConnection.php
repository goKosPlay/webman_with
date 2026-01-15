<?php

namespace app\example;

use app\attribute\dependency\{Component, Value};

#[Component(singleton: true)]
class ExampleDatabaseConnection
{
    #[Value(key: 'database.host', default: 'localhost')]
    private string $host;
    
    #[Value(key: 'database.port', default: 3306)]
    private int $port;
    
    public function query($sql, $params = [])
    {
        return [];
    }
}
