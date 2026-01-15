<?php
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => '127.0.0.1',
            'port'        => '3306',
            'database'    => '',
            'username'    => 'root',
            'password'    => '',
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '',
            'strict'      => true,
            'engine'      => null,
//            'modes'       => 'NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION', // 严格模式
            'options'   => [
                \PDO::ATTR_EMULATE_PREPARES   => false,   // 必须关闭，使用真预处理语句
                \PDO::ATTR_TIMEOUT            => 3,       // 连接超时 3秒
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                \PDO::ATTR_PERSISTENT         => false,   // 不要用 PDO 自带持久连接
            ],
            // 连接池配置（webman/database 内置，支持 Swoole/Swow 协程）
            'pool' => [
                'max_connections'  => 60,     // 最大连接数（见下面调优建议）
                'min_connections'  => 8,      // 最小空闲连接（预热用，建议 8~16）
                'connect_timeout'  => 2,      // 建立连接超时（秒）
                'wait_timeout'     => 5,      // 从池子取连接等待超时（秒），超时抛异常
                'heartbeat'        => -1,     // 心跳检测间隔（-1 关闭，建议 -1 或 30）
                'max_idle_time'    => 60,     // 连接最大空闲时间（秒），超时自动回收
            ],
        ],
    ],
];