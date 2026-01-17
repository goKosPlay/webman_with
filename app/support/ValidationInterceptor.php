<?php

declare(strict_types=1);

namespace app\support;

use app\attribute\validation\{
    Required, Email, Min, Max, Length, Pattern, In, Url, Numeric, Rule
};
use ReflectionMethod;
use ReflectionParameter;

/**
 * 验证拦截器 - 自动处理方法参数验证
 */
class ValidationInterceptor
{
    protected array $errors = [];
    protected AttributeCache $attributeCache;
    
    public function __construct()
    {
        $this->attributeCache = AttributeCache::getInstance();
    }
    
    /**
     * 验证方法参数
     */
    public function validateMethodParameters(ReflectionMethod $method, array $arguments): bool
    {
        $this->errors = [];
        
        $parameters = $method->getParameters();
        
        foreach ($parameters as $index => $parameter) {
            $value = $arguments[$index] ?? $arguments[$parameter->getName()] ?? null;
            
            if (!$this->validateParameter($parameter, $value)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 验证单个参数
     */
    protected function validateParameter(ReflectionParameter $parameter, mixed $value): bool
    {
        $attributes = $this->attributeCache->getParameterAttributes($parameter);
        $paramName = $parameter->getName();
        
        foreach ($attributes as $attribute) {
            $instance = $this->attributeCache->getAttributeInstance($attribute);
            
            $result = match (true) {
                $instance instanceof Required => $this->validateRequired($value, $instance),
                $instance instanceof Email => $this->validateEmail($value, $instance),
                $instance instanceof Min => $this->validateMin($value, $instance),
                $instance instanceof Max => $this->validateMax($value, $instance),
                $instance instanceof Length => $this->validateLength($value, $instance),
                $instance instanceof Pattern => $this->validatePattern($value, $instance),
                $instance instanceof In => $this->validateIn($value, $instance),
                $instance instanceof Url => $this->validateUrl($value, $instance),
                $instance instanceof Numeric => $this->validateNumeric($value, $instance),
                $instance instanceof Rule => $this->validateRule($value, $instance),
                default => true
            };
            
            if ($result !== true) {
                $this->errors[$paramName] = is_string($result) ? $result : ($instance->message ?? "Validation failed for {$paramName}");
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 验证 Rule 属性
     */
    protected function validateRule($value, Rule $rule): bool|string
    {
        $rules = explode('|', $rule->rule);
        
        foreach ($rules as $ruleItem) {
            [$ruleName, $params] = $this->parseRule($ruleItem);
            
            $result = match ($ruleName) {
                'require'   => $this->checkRequire($value),
                'email'     => $this->checkEmail($value),
                'mobile'    => $this->checkMobile($value),
                'url'       => $this->checkUrl($value),
                'number'    => $this->checkNumber($value),
                'integer'   => $this->checkInteger($value),
                'alphaNum'  => $this->checkAlphaNum($value),
                'alpha'     => $this->checkAlpha($value),
                'length'    => $this->checkLength($value, $params),
                'min'       => $this->checkMin($value, $params),
                'max'       => $this->checkMax($value, $params),
                'between'   => $this->checkBetween($value, $params),
                'in'        => $this->checkIn($value, $params),
                'regex'     => $this->checkRegex($value, $params),
                default     => true
            };
            
            if ($result !== true) {
                return $rule->message ?? $result;
            }
        }
        
        return true;
    }
    
    protected function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $params] = explode(':', $rule, 2);
            return [$name, explode(',', $params)];
        }
        return [$rule, []];
    }
    
    protected function checkRequire($value): bool|string
    {
        return !($value === null || $value === '' || (is_array($value) && empty($value))) ?: 'Field is required';
    }
    
    protected function checkEmail($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false ?: 'Invalid email format';
    }
    
    protected function checkMobile($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^1[3-9]\d{9}$/', $value) === 1 ?: 'Invalid mobile number';
    }
    
    protected function checkUrl($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false ?: 'Invalid URL';
    }
    
    protected function checkNumber($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return is_numeric($value) ?: 'Must be numeric';
    }
    
    protected function checkInteger($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_INT) !== false ?: 'Must be integer';
    }
    
    protected function checkAlphaNum($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^[A-Za-z0-9]+$/', $value) === 1 ?: 'Must be alphanumeric';
    }
    
    protected function checkAlpha($value): bool|string
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^[A-Za-z]+$/', $value) === 1 ?: 'Must be alphabetic';
    }
    
    protected function checkLength($value, array $params): bool|string
    {
        if ($value === null || $value === '') return true;
        $length = mb_strlen($value);
        
        if (count($params) === 1) {
            return $length === (int)$params[0] ?: "Length must be {$params[0]}";
        } elseif (count($params) === 2) {
            return ($length >= (int)$params[0] && $length <= (int)$params[1]) ?: "Length must be between {$params[0]} and {$params[1]}";
        }
        return true;
    }
    
    protected function checkMin($value, array $params): bool|string
    {
        if ($value === null || $value === '' || empty($params)) return true;
        $min = $params[0];
        
        if (is_numeric($value)) {
            return $value >= $min ?: "Must be at least {$min}";
        }
        return mb_strlen($value) >= $min ?: "Length must be at least {$min}";
    }
    
    protected function checkMax($value, array $params): bool|string
    {
        if ($value === null || $value === '' || empty($params)) return true;
        $max = $params[0];
        
        if (is_numeric($value)) {
            return $value <= $max ?: "Must not exceed {$max}";
        }
        return mb_strlen($value) <= $max ?: "Length must not exceed {$max}";
    }
    
    protected function checkBetween($value, array $params): bool|string
    {
        if ($value === null || $value === '' || count($params) < 2) return true;
        return ($value >= $params[0] && $value <= $params[1]) ?: "Must be between {$params[0]} and {$params[1]}";
    }
    
    protected function checkIn($value, array $params): bool|string
    {
        if ($value === null || $value === '') return true;
        return in_array($value, $params, true) ?: "Must be one of: " . implode(', ', $params);
    }
    
    protected function checkRegex($value, array $params): bool|string
    {
        if ($value === null || $value === '' || empty($params)) return true;
        return preg_match($params[0], $value) === 1 ?: 'Format is invalid';
    }
    
    protected function validateRequired($value, Required $rule): bool|string
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return $rule->message ?? 'Field is required';
        }
        return true;
    }
    
    protected function validateEmail($value, Email $rule): bool|string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $rule->message ?? 'Invalid email format';
        }
        return true;
    }
    
    protected function validateMin($value, Min $rule): bool|string
    {
        if ($value === null || $value === '') return true;
        
        if (is_numeric($value) && $value < $rule->value) {
            return $rule->message ?? "Must be at least {$rule->value}";
        } elseif (is_string($value) && mb_strlen($value) < $rule->value) {
            return $rule->message ?? "Length must be at least {$rule->value}";
        }
        return true;
    }
    
    protected function validateMax($value, Max $rule): bool|string
    {
        if ($value === null || $value === '') return true;
        
        if (is_numeric($value) && $value > $rule->value) {
            return $rule->message ?? "Must not exceed {$rule->value}";
        } elseif (is_string($value) && mb_strlen($value) > $rule->value) {
            return $rule->message ?? "Length must not exceed {$rule->value}";
        }
        return true;
    }
    
    protected function validateLength($value, Length $rule): bool|string
    {
        if ($value === null || $value === '') return true;
        
        $length = mb_strlen($value);
        
        if ($rule->min !== null && $length < $rule->min) {
            return $rule->message ?? "Length must be at least {$rule->min}";
        }
        
        if ($rule->max !== null && $length > $rule->max) {
            return $rule->message ?? "Length must not exceed {$rule->max}";
        }
        
        return true;
    }
    
    protected function validatePattern($value, Pattern $rule): bool|string
    {
        if ($value === null || $value === '') return true;
        
        if (!preg_match($rule->pattern, (string)$value)) {
            return $rule->message ?? 'Format is invalid';
        }
        return true;
    }
    
    protected function validateIn($value, In $rule): bool|string
    {
        if ($value === null || $value === '') return true;
        
        if (!in_array($value, $rule->values, true)) {
            return $rule->message ?? 'Invalid value';
        }
        return true;
    }
    
    protected function validateUrl($value, Url $rule): bool|string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            return $rule->message ?? 'Invalid URL';
        }
        return true;
    }
    
    protected function validateNumeric($value, Numeric $rule): bool|string
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            return $rule->message ?? 'Must be numeric';
        }
        return true;
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
