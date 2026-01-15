<?php

namespace app\attribute\web;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Validated
{
    public function __construct(
        public array $groups = []
    ) {}
}
