<?php

namespace app\support;

use app\attribute\validation\{
    Required, Email, Min, Max, Length, Pattern, In, Url, Numeric
};
use ReflectionClass;
use ReflectionProperty;
use ReflectionParameter;

class Validator
{
    protected array $errors = [];
    
    /**
     * 验证数据对象
     */
    public function validate(object $dto): bool
    {
        $this->errors = [];
        
        $reflection = new ReflectionClass($dto);
        
        foreach ($reflection->getProperties() as $property) {
            $value = $property->getValue($dto);
            $fieldName = $property->getName();
            
            $this->validateProperty($property, $value, $fieldName);
        }
        
        return empty($this->errors);
    }
    
    /**
     * 验证数组数据
     */
    public function validateArray(array $data, array $rules): bool
    {
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $fieldRules);
        }
        
        return empty($this->errors);
    }
    
    /**
     * 验证属性
     */
    protected function validateProperty(ReflectionProperty $property, mixed $value, string $fieldName): void
    {
        $attributes = $property->getAttributes();
        
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            
            match (true) {
                $instance instanceof Required => $this->validateRequired($fieldName, $value, $instance),
                $instance instanceof Email => $this->validateEmail($fieldName, $value, $instance),
                $instance instanceof Min => $this->validateMin($fieldName, $value, $instance),
                $instance instanceof Max => $this->validateMax($fieldName, $value, $instance),
                $instance instanceof Length => $this->validateLength($fieldName, $value, $instance),
                $instance instanceof Pattern => $this->validatePattern($fieldName, $value, $instance),
                $instance instanceof In => $this->validateIn($fieldName, $value, $instance),
                $instance instanceof Url => $this->validateUrl($fieldName, $value, $instance),
                $instance instanceof Numeric => $this->validateNumeric($fieldName, $value, $instance),
                default => null
            };
        }
    }
    
    /**
     * 验证字段
     */
    protected function validateField(string $field, mixed $value, array $rules): void
    {
        foreach ($rules as $rule) {
            if (is_string($rule)) {
                $this->applyStringRule($field, $value, $rule);
            } elseif (is_object($rule)) {
                $this->applyObjectRule($field, $value, $rule);
            }
        }
    }
    
    /**
     * 应用字符串规则
     */
    protected function applyStringRule(string $field, mixed $value, string $rule): void
    {
        [$ruleName, $params] = $this->parseRule($rule);
        
        match ($ruleName) {
            'required' => $this->validateRequired($field, $value, new Required()),
            'email' => $this->validateEmail($field, $value, new Email()),
            'url' => $this->validateUrl($field, $value, new Url()),
            'numeric' => $this->validateNumeric($field, $value, new Numeric()),
            'min' => $this->validateMin($field, $value, new Min((int)$params[0])),
            'max' => $this->validateMax($field, $value, new Max((int)$params[0])),
            default => null
        };
    }
    
    /**
     * 应用对象规则
     */
    protected function applyObjectRule(string $field, mixed $value, object $rule): void
    {
        match (true) {
            $rule instanceof Required => $this->validateRequired($field, $value, $rule),
            $rule instanceof Email => $this->validateEmail($field, $value, $rule),
            $rule instanceof Min => $this->validateMin($field, $value, $rule),
            $rule instanceof Max => $this->validateMax($field, $value, $rule),
            $rule instanceof Length => $this->validateLength($field, $value, $rule),
            $rule instanceof Pattern => $this->validatePattern($field, $value, $rule),
            $rule instanceof In => $this->validateIn($field, $value, $rule),
            $rule instanceof Url => $this->validateUrl($field, $value, $rule),
            $rule instanceof Numeric => $this->validateNumeric($field, $value, $rule),
            default => null
        };
    }
    
    /**
     * 解析规则字符串
     */
    protected function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $params] = explode(':', $rule, 2);
            return [$name, explode(',', $params)];
        }
        
        return [$rule, []];
    }
    
    /**
     * 验证必填
     */
    protected function validateRequired(string $field, mixed $value, Required $rule): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, $rule->message ?? "{$field} is required");
        }
    }
    
    /**
     * 验证邮箱
     */
    protected function validateEmail(string $field, mixed $value, Email $rule): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $rule->message ?? "{$field} must be a valid email address");
        }
    }
    
    /**
     * 验证最小值
     */
    protected function validateMin(string $field, mixed $value, Min $rule): void
    {
        if ($value === null || $value === '') {
            return;
        }
        
        if (is_numeric($value) && $value < $rule->value) {
            $this->addError($field, $rule->message ?? "{$field} must be at least {$rule->value}");
        } elseif (is_string($value) && mb_strlen($value) < $rule->value) {
            $this->addError($field, $rule->message ?? "{$field} must be at least {$rule->value} characters");
        }
    }
    
    /**
     * 验证最大值
     */
    protected function validateMax(string $field, mixed $value, Max $rule): void
    {
        if ($value === null || $value === '') {
            return;
        }
        
        if (is_numeric($value) && $value > $rule->value) {
            $this->addError($field, $rule->message ?? "{$field} must not exceed {$rule->value}");
        } elseif (is_string($value) && mb_strlen($value) > $rule->value) {
            $this->addError($field, $rule->message ?? "{$field} must not exceed {$rule->value} characters");
        }
    }
    
    /**
     * 验证长度
     */
    protected function validateLength(string $field, mixed $value, Length $rule): void
    {
        if ($value === null || $value === '') {
            return;
        }
        
        $length = is_string($value) ? mb_strlen($value) : (is_array($value) ? count($value) : 0);
        
        if ($rule->min !== null && $length < $rule->min) {
            $this->addError($field, $rule->message ?? "{$field} must be at least {$rule->min} characters");
        }
        
        if ($rule->max !== null && $length > $rule->max) {
            $this->addError($field, $rule->message ?? "{$field} must not exceed {$rule->max} characters");
        }
    }
    
    /**
     * 验证正则表达式
     */
    protected function validatePattern(string $field, mixed $value, Pattern $rule): void
    {
        if ($value === null || $value === '') {
            return;
        }
        
        if (!preg_match($rule->pattern, (string)$value)) {
            $this->addError($field, $rule->message ?? "{$field} format is invalid");
        }
    }
    
    /**
     * 验证枚举值
     */
    protected function validateIn(string $field, mixed $value, In $rule): void
    {
        if ($value === null || $value === '') {
            return;
        }
        
        if (!in_array($value, $rule->values, true)) {
            $allowed = implode(', ', $rule->values);
            $this->addError($field, $rule->message ?? "{$field} must be one of: {$allowed}");
        }
    }
    
    /**
     * 验证 URL
     */
    protected function validateUrl(string $field, mixed $value, Url $rule): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $rule->message ?? "{$field} must be a valid URL");
        }
    }
    
    /**
     * 验证数字
     */
    protected function validateNumeric(string $field, mixed $value, Numeric $rule): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, $rule->message ?? "{$field} must be numeric");
        }
    }
    
    /**
     * 添加错误
     */
    protected function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * 获取错误
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * 获取第一个错误
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        
        return null;
    }
    
    /**
     * 是否有错误
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
