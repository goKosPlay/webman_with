<?php

namespace app\service;

use app\attribute\dependency\Service;
use app\service\sms\SmsProviderInterface;

#[Service]
class SmsService
{
    public function send(string $phone, string $message, ?string $countryCode = null, ?string $provider = null): array
    {
        $countryCode = $countryCode ?: (string)config('sms.default_country_code', '86');
        $providerList = $this->normalizeProviderList($provider);

        $lastException = null;
        foreach ($providerList as $providerName) {
            try {
                return $this->sendViaProvider($providerName, $countryCode, $phone, $message);
            } catch (\Throwable $e) {
                $lastException = $e;
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        throw new \RuntimeException('No SMS provider available');
    }

    public function sendOtp(string $phone, string $code, ?int $ttlSeconds = null, ?string $countryCode = null, ?string $provider = null): array
    {
        $ttlSeconds ??= (int)config('sms.otp.ttl_seconds', 300);
        $template = (string)config('sms.otp.template', '您的验证码是 {code}，{ttl} 分钟内有效。');

        $ttlMinutes = (int)ceil($ttlSeconds / 60);
        $message = strtr($template, [
            '{code}' => $code,
            '{ttl}' => (string)$ttlMinutes,
        ]);

        return $this->send($phone, $message, $countryCode, $provider);
    }

    private function sendViaProvider(string $provider, string $countryCode, string $phone, string $message): array
    {
        $providers = (array)config('sms.providers', []);
        $providerConfig = $providers[$provider] ?? null;
        if (!is_array($providerConfig)) {
            throw new \RuntimeException("SMS provider not configured: {$provider}");
        }

        $class = (string)($providerConfig['class'] ?? '');
        if ($class === '' || !class_exists($class)) {
            throw new \RuntimeException("SMS provider class invalid for '{$provider}'");
        }

        $instance = new $class();
        if (!$instance instanceof SmsProviderInterface) {
            throw new \RuntimeException("SMS provider '{$provider}' must implement SmsProviderInterface");
        }

        $options = (array)($providerConfig['options'] ?? []);
        return $instance->send($countryCode, $phone, $message, $options);
    }

    private function normalizeProviderList(?string $provider): array
    {
        if ($provider !== null && $provider !== '') {
            $parts = array_filter(array_map('trim', explode(',', $provider)), static fn($v) => $v !== '');
            return array_values(array_unique($parts));
        }

        $default = (string)config('sms.default_provider', 'log');
        $fallback = (array)config('sms.fallback_providers', []);

        $list = array_merge([$default], $fallback);
        $list = array_filter(array_map('trim', $list), static fn($v) => $v !== '');
        return array_values(array_unique($list));
    }
}
