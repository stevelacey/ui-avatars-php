<?php

namespace SteveLacey\UiAvatars\Support;

final class Url
{
    // Hosts the proxy can't reach: single labels, local and reserved names, and private IPs
    public const LOCAL_HOST = '/^(localhost|[^.:]+|.+\.(localhost|test|local|internal|lan|example|home\.arpa)'
        .'|(127|10|192\.168|169\.254|172\.(1[6-9]|2\d|3[01]))\.[\d.]+|0\.0\.0\.0'
        .'|::1|f[cd][\da-f]*:.*|fe[89ab][\da-f]*:.*)$/';

    public static function build(string $base, array $params): string
    {
        return $base.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public static function isLocal(string $url): bool
    {
        if (! str_contains($url, '://')) {
            return true;
        }

        $rest = explode('://', $url, 2)[1];
        $authority = substr($rest, 0, strcspn($rest, '/?#'));
        $host = preg_replace('/^.*@/', '', $authority);
        $host = str_starts_with($host, '[') ? explode(']', substr($host, 1))[0] : explode(':', $host)[0];

        return (bool) preg_match(self::LOCAL_HOST, rtrim(strtolower($host), '.'));
    }

    public static function withScheme(?string $value): ?string
    {
        return $value && ! str_contains($value, '://') ? "https://{$value}" : $value;
    }
}
