<?php

namespace app\attribute\event;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EventListener
{
    public function __construct(
        public string|array $events,
        public int $priority = 0
    ) {
        if (is_string($this->events)) {
            $this->events = [$this->events];
        }
    }
}
