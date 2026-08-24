<?php

declare(strict_types=1);

namespace App\Support\Providers\Ingestion;

final class SensitiveDataRedactor
{
    /** @var list<string> */
    private const SENSITIVE_KEYS = ['authorization', 'cookie', 'set-cookie', 'api_key', 'apikey', 'access_token', 'refresh_token', 'client_secret', 'password'];

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function redact(array $data): array
    {
        $redacted = [];
        foreach ($data as $key => $value) {
            $normalized = strtolower(str_replace('-', '_', (string) $key));
            $redacted[$key] = in_array($normalized, self::SENSITIVE_KEYS, true)
                ? '[REDACTED]'
                : (is_array($value) ? $this->redact($value) : $value);
        }

        return $redacted;
    }
}
