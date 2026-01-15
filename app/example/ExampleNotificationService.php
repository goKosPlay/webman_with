<?php

namespace app\example;

use app\attribute\dependency\{Service, Lazy};

#[Service]
class ExampleNotificationService
{
    #[Lazy]
    private ExampleEmailService $emailService;
    
    public function notifyAdmins($message)
    {
    }
}
