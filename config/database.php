<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
//            'modes'       => 'NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION', // 严格模式
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => false,   // 必须关闭，使用真预处理语句
                PDO::ATTR_TIMEOUT => 3,       // 连接超时 3秒
//                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => false,   // 不要用 PDO 自带持久连接
            ],
            // 连接池配置（webman/database 内置，支持 Swoole/Swow 协程）
            'pool' => [
                'max_connections' => 100,     // 减少最大连接数，避免超过MySQL限制
                'min_connections' => 5,      // 减少最小空闲连接
                'connect_timeout' => 3,      // 增加建立连接超时
                'wait_timeout' => 3,      // 减少取连接等待时间
                'heartbeat_interval' => 50,     // 心跳检测间隔（-1 关闭，建议 -1 或 30）
                'max_idle_time' => 30,     // 减少连接最大空闲时间，更快回收
                'idle_timeout' => 60,     // 减少连接最大空闲时间，更快回收
            ],
        ],
    ],
];