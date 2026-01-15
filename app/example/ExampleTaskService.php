<?php

namespace app\example;

use app\attribute\dependency\{Service, Autowired};
use app\attribute\schedule\Scheduled;

#[Service]
class ExampleTaskService
{
    #[Autowired]
    private ExampleUserRepository $userRepository;
    
    #[Scheduled(cron: '0 0 * * *', timeZone: 'Asia/Shanghai')]
    public function dailyBackup()
    {
    }
    
    #[Scheduled(cron: '*/5 * * * *')]
    public function checkSystemHealth()
    {
    }
    
    #[Scheduled(fixedDelay: 5000, initialDelay: 1000)]
    public function processQueue()
    {
    }
    
    #[Scheduled(fixedRate: 60000)]
    public function updateMetrics()
    {
    }
    
    #[Scheduled(cron: '0 */6 * * *', enabled: true)]
    public function cleanupTempFiles()
    {
    }
}
