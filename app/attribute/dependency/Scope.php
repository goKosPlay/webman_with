<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Scope
{
    public const SINGLETON = 'singleton';
    public const PROTOTYPE = 'prototype';
    public const REQUEST = 'request';

    public function __construct(
        public string $value = self::SINGLETON
    ) {}
}
