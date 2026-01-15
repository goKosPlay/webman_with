<?php

namespace app\attribute\validation;

use Attribute;

/**
 * 通用验证规则属性
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Rule
{
    public function __construct(
        public string $rule,
        public ?string $message = null
    ) {}
}
