<?php

namespace app\example;

use app\attribute\dependency\{Service, Value, Autowired};

#[Service]
class ExampleEmailService
{
    #[Value(key: 'mail.from', default: 'noreply@example.com')]
    private string $fromAddress;
    
    #[Value(key: 'mail.driver', default: 'smtp')]
    private string $driver;
    
    public function send($to, $subject, $data = [])
    {
    }
}
