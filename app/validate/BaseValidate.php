<?php

namespace app\validate;

/**
 * 基础验证器类
 * 参考 ThinkPHP think-validate
 */
abstract class BaseValidate
{
    protected array $rule = [];
    protected array $message = [];
    protected array $scene = [];
    protected array $errors = [];
    protected ?string $currentScene = null;
    
    /**
     * 验证数据
     */
    public function check(array $data): bool
    {
        $this->errors = [];
        
        $rules = $this->getSceneRules();
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (!$this->checkField($field, $value, $rule, $data)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 批量验证（不在第一个错误时停止）
     */
    public function batch(bool $batch = true): self
    {
        $this->batch = $batch;
        return $this;
    }
    
    /**
     * 设置验证场景
     */
    public function scene(string $scene): self
    {
        $this->currentScene = $scene;
        return $this;
    }
    
    /**
     * 获取场景规则
     */
    protected function getSceneRules(): array
    {
        if ($this->currentScene && isset($this->scene[$this->currentScene])) {
            $fields = $this->scene[$this->currentScene];
            return array_intersect_key($this->rule, array_flip($fields));
        }
        
        return $this->rule;
    }
    
    /**
     * 验证单个字段
     */
    protected function checkField(string $field, mixed $value, string $rule, array $data): bool
    {
        $rules = explode('|', $rule);
        
        foreach ($rules as $ruleItem) {
            if (!$this->validateRule($field, $value, $ruleItem, $data)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 验证规则
     */
    protected function validateRule(string $field, mixed $value, string $rule, array $data): bool
    {
        [$ruleName, $params] = $this->parseRule($rule);
        
        $result = match ($ruleName) {
            'require'   => $this->validateRequire($value),
            'email'     => $this->validateEmail($value),
            'mobile'    => $this->validateMobile($value),
            'url'       => $this->validateUrl($value),
            'number'    => $this->validateNumber($value),
            'integer'   => $this->validateInteger($value),
            'alphaNum'  => $this->validateAlphaNum($value),
            'alpha'     => $this->validateAlpha($value),
            'length'    => $this->validateLength($value, $params),
            'min'       => $this->validateMin($value, $params),
            'max'       => $this->validateMax($value, $params),
            'between'   => $this->validateBetween($value, $params),
            'in'        => $this->validateIn($value, $params),
            'notIn'     => $this->validateNotIn($value, $params),
            'regex'     => $this->validateRegex($value, $params),
            'confirm'   => $this->validateConfirm($value, $field, $data),
            default     => $this->callCustomRule($ruleName, $value, $params, $data)
        };
        
        if ($result !== true) {
            $this->setError($field, $ruleName, $result);
            return false;
        }
        
        return true;
    }
    
    /**
     * 解析规则
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
     * 必填验证
     */
    protected function validateRequire($value): bool
    {
        return !($value === null || $value === '' || (is_array($value) && empty($value)));
    }
    
    /**
     * 邮箱验证
     */
    protected function validateEmail($value): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 手机号验证
     */
    protected function validateMobile($value): bool
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^1[3-9]\d{9}$/', $value) === 1;
    }
    
    /**
     * URL 验证
     */
    protected function validateUrl($value): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * 数字验证
     */
    protected function validateNumber($value): bool
    {
        if ($value === null || $value === '') return true;
        return is_numeric($value);
    }
    
    /**
     * 整数验证
     */
    protected function validateInteger($value): bool
    {
        if ($value === null || $value === '') return true;
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * 字母和数字验证
     */
    protected function validateAlphaNum($value): bool
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^[A-Za-z0-9]+$/', $value) === 1;
    }
    
    /**
     * 字母验证
     */
    protected function validateAlpha($value): bool
    {
        if ($value === null || $value === '') return true;
        return preg_match('/^[A-Za-z]+$/', $value) === 1;
    }
    
    /**
     * 长度验证
     */
    protected function validateLength($value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        
        $length = is_string($value) ? mb_strlen($value) : (is_array($value) ? count($value) : 0);
        
        if (count($params) === 1) {
            return $length === (int)$params[0];
        } elseif (count($params) === 2) {
            return $length >= (int)$params[0] && $length <= (int)$params[1];
        }
        
        return true;
    }
    
    /**
     * 最小值验证
     */
    protected function validateMin($value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        
        $min = $params[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value >= $min;
        } elseif (is_string($value)) {
            return mb_strlen($value) >= $min;
        }
        
        return true;
    }
    
    /**
     * 最大值验证
     */
    protected function validateMax($value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        
        $max = $params[0] ?? 0;
        
        if (is_numeric($value)) {
            return $value <= $max;
        } elseif (is_string($value)) {
            return mb_strlen($value) <= $max;
        }
        
        return true;
    }
    
    /**
     * 区间验证
     */
    protected function validateBetween($value, array $params): bool
    {
        if ($value === null || $value === '' || count($params) < 2) return true;
        
        return $value >= $params[0] && $value <= $params[1];
    }
    
    /**
     * 在范围内验证
     */
    protected function validateIn($value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return in_array($value, $params, true);
    }
    
    /**
     * 不在范围内验证
     */
    protected function validateNotIn($value, array $params): bool
    {
        if ($value === null || $value === '') return true;
        return !in_array($value, $params, true);
    }
    
    /**
     * 正则验证
     */
    protected function validateRegex($value, array $params): bool
    {
        if ($value === null || $value === '' || empty($params)) return true;
        return preg_match($params[0], $value) === 1;
    }
    
    /**
     * 确认字段验证
     */
    protected function validateConfirm($value, string $field, array $data): bool
    {
        $confirmField = $field . '_confirm';
        return isset($data[$confirmField]) && $value === $data[$confirmField];
    }
    
    /**
     * 调用自定义验证规则
     */
    protected function callCustomRule(string $rule, $value, array $params, array $data)
    {
        $method = 'check' . ucfirst($rule);
        
        if (method_exists($this, $method)) {
            return $this->$method($value, $params, $data);
        }
        
        return true;
    }
    
    /**
     * 设置错误信息
     */
    protected function setError(string $field, string $rule, $message): void
    {
        $key = "{$field}.{$rule}";
        
        if (is_string($message)) {
            $this->errors[$field] = $message;
        } elseif (isset($this->message[$key])) {
            $this->errors[$field] = $this->message[$key];
        } else {
            $this->errors[$field] = "{$field} validation failed for rule: {$rule}";
        }
    }
    
    /**
     * 获取错误信息
     */
    public function getError(): array|string
    {
        return $this->errors;
    }
    
    /**
     * 获取第一个错误
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
}
