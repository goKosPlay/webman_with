<?php

declare(strict_types=1);

namespace app\attribute\event;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EventListener
{
    public readonly array $events;
    
    public function __construct(
        string|array $events,
        public readonly int $priority = 0
    ) {
        $this->events = is_array($events) ? $events : [$events];
    }
}
