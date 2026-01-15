<?php

namespace app\task;

use app\attribute\schedule\Scheduled;
use app\attribute\dependency\Service;

#[Service]
class MyTaskService
{
    // 每分钟执行一次（标准 Cron 格式：5 个字段）

}