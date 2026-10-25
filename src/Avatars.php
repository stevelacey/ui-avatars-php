<?php

namespace SteveLacey\UiAvatars;

use BadMethodCallException;
use InvalidArgumentException;
use Stringable;

/**
 * Call build() and configure() statically to use the shared instance,
 * or on an instance of your own.
 *
 * @method string build(?string $name = null, ?string $email = null, string|Stringable|null $image = null, mixed ...$options)
 * @method static string build(?string $name = null, ?string $email = null, string|Stringable|null $image = null, mixed ...$options)
 * @method static configure(mixed ...$options)
 * @method static static configure(mixed ...$options)
 */
class Avatars
{
    // Tailwind's 500 shades, in rainbow order
    public const COLORS = [
        '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e',
        '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1',
        '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e',
    ];

    public const HOST = 'https://ui-avatars.com';

    public const PROXY = 'https://wsrv.nl';

    public const GRAVATAR = 'gravatar';

    public const LIBRAVATAR = 'libravatar';

    public const SOURCES = [
        self::GRAVATAR => 'https://www.gravatar.com',
        self::LIBRAVATAR => 'https://seccdn.libravatar.org',
    ];

    private static ?self $shared = null;

    /**
     * @param  array<string|array{string, string}>  $colors
     */
    public function __construct(
        public float $alpha = 0.2,
        public ?string $background = null,
        public bool $bold = true,
        public array $colors = self::COLORS,
        public ?string $fontColor = null,
        public float $fontSize = 0.4,
        public ?string $format = null,
        public string $hash = 'sha256',
        public ?string $host = null,
        public int $length = 2,
        public ?string $mask = null,
        public ?string $proxy = null,
        public ?string $region = null,
        public bool $rounded = false,
        public int $size = 128,
        public string $source = self::GRAVATAR,
        public bool $uppercase = true,
    ) {
        $this->colors = array_values($colors);
    }

    protected function build(
        ?string $name = null,
        ?string $email = null,
        string|Stringable|null $image = null,
        ?float $alpha = null,
        ?string $background = null,
        ?bool $bold = null,
        ?string $color = null,
        ?string $fontColor = null,
        ?float $fontSize = null,
        ?string $format = null,
        ?string $hash = null,
        ?string $host = null,
        ?int $length = null,
        ?string $mask = null,
        ?string $proxy = null,
        ?string $region = null,
        ?bool $rounded = null,
        ?int $size = null,
        ?string $source = null,
        ?bool $uppercase = null,
    ): string {
        $alpha ??= $this->alpha;
        $background ??= $this->background;
        $bold = (int) ($bold ?? $this->bold);
        $fontColor ??= $this->fontColor;
        $fontSize ??= $this->fontSize;
        $format ??= $this->format;
        $hash ??= $this->hash;
        $host = $this->parseHostname($host ?: $this->host);
        $image = (string) $image ?: null;
        $length ??= $this->length;
        $region ??= $this->region;
        $rounded = (int) ($rounded ?? $this->rounded);
        $mask = $mask ?: $this->mask ?: (($email || $image) && $rounded ? 'circle' : null);
        $rounded = (int) ($rounded || $mask === 'circle');
        $proxy = $this->parseHostname($proxy ?: $this->proxy);
        $size ??= $this->size;
        $source ??= $this->source;
        $origin = $this->parseHostname(self::SOURCES[$source] ?? $source);
        $uppercase = (int) ($uppercase ?? $this->uppercase);

        if (! $name && ! $email) {
            throw new InvalidArgumentException('requires at least one of name or email');
        }

        if ($host && ! str_contains($host, '.')) {
            throw new InvalidArgumentException("unknown host: '{$host}'");
        }

        if ($proxy && ! str_contains($proxy, '.')) {
            throw new InvalidArgumentException("unknown proxy: '{$proxy}'");
        }

        if ($region && ! in_array($region, ['eu', 'na'], true)) {
            throw new InvalidArgumentException("unknown region: '{$region}'");
        }

        if (! in_array($hash, ['md5', 'sha256'], true)) {
            throw new InvalidArgumentException("unknown hash: '{$hash}'");
        }

        if ($origin && ! str_contains($origin, '.')) {
            throw new InvalidArgumentException("unknown source: '{$source}'");
        }

        $host = $host ?: ($region ? str_replace('//', "//{$region}.", self::HOST) : self::HOST);
        $output = $format ?: 'png';
        $proxy = $proxy ?: self::PROXY;

        if (! $name) {
            $localPart = explode('@', trim($email), 2)[0];
            $name = mb_substr(preg_replace('/\P{L}/u', '', $localPart), 0, 2) ?: mb_substr($localPart, 0, 2);
        }

        $digest = md5(mb_strtolower(trim($email ?: $name)));
        $emailHash = $email ? hash($hash, mb_strtolower(trim($email))) : null;

        $paletteItem = $this->colors[hexdec(substr($digest, 0, 8)) % count($this->colors)];
        $palettePair = is_array($paletteItem) ? $paletteItem : [$paletteItem, $paletteItem];
        $backgroundColor = ltrim($background ?: $color ?: $palettePair[0], '#');
        $textColor = ltrim($fontColor ?: $color ?: $palettePair[1], '#');

        if (strlen($backgroundColor) === 3) {
            $backgroundColor = preg_replace('/./', '$0$0', $backgroundColor);
        }
        if (strlen($textColor) === 3) {
            $textColor = preg_replace('/./', '$0$0', $textColor);
        }

        if (! in_array($format, ['png', 'svg'], true)) {
            $format = $email && (str_contains($origin, 'gravatar.com') || $rounded) ? 'png' : 'svg';
        }

        if ($backgroundColor === 'random') {
            $textColor = 'auto';
        } elseif ($format === 'png') {
            [$r, $g, $b] = array_map('hexdec', str_split(substr($backgroundColor, 0, 6), 2));
            $backgroundColor = "rgba({$r},{$g},{$b},{$alpha})";
        } elseif ($alpha < 1) {
            $alphaSuffix = sprintf('%02x', min(255, max(0, (int) round($alpha * 255, 0, PHP_ROUND_HALF_EVEN))));
            $backgroundColor = "{$backgroundColor}{$alphaSuffix}";
        }

        $initials = Initials::generate($name, $length, uppercase: (bool) $uppercase);
        $url = $default = "{$host}/api/".str_replace('%2F', '/', rawurlencode($initials))."/{$size}/{$backgroundColor}/{$textColor}/"
            ."{$length}/{$fontSize}/{$rounded}/{$bold}/{$uppercase}/{$format}";

        if ($email) {
            $url = $this->buildUrl("{$origin}/avatar/{$emailHash}", ['s' => $size, 'd' => $default]);
        }

        $supportedDefault = ! str_contains($origin, 'libravatar.org') || str_contains($host, 'ui-avatars.com');
        $supportedFormat = in_array($output, ['png', 'svg'], true);
        $supportedMask = $mask === null || $mask === 'circle';
        $supportedNatively = $supportedDefault && $supportedFormat && $supportedMask;

        if ($email && $mask || $image || ! $supportedNatively) {
            $params = ['w' => $size, 'h' => $size, 'mask' => $mask, 'output' => $output];

            if (! $supportedFormat || ! $supportedMask) {
                $default = $this->buildUrl("{$proxy}/", ['url' => $default, ...$params]);
            }

            // Without a photo to look up, the proxy fetches the initials directly
            $photo = $image ?: ($email ? $this->buildUrl("{$origin}/avatar/{$emailHash}", ['s' => $size, 'd' => 404]) : null);

            $url = $this->buildUrl("{$proxy}/", [
                'url' => $photo ?: $url,
                'default' => $photo ? $default : null,
                'fit' => 'cover',
                ...$params,
            ]);
        }

        return $url;
    }

    /**
     * @param  array<string|array{string, string}>|null  $colors
     */
    protected function configure(
        ?float $alpha = null,
        ?string $background = null,
        ?bool $bold = null,
        ?array $colors = null,
        ?string $fontColor = null,
        ?float $fontSize = null,
        ?string $format = null,
        ?string $hash = null,
        ?string $host = null,
        ?int $length = null,
        ?string $mask = null,
        ?string $proxy = null,
        ?string $region = null,
        ?bool $rounded = null,
        ?int $size = null,
        ?string $source = null,
        ?bool $uppercase = null,
    ): static {
        $this->alpha = $alpha ?? $this->alpha;
        $this->background = $background ?? $this->background;
        $this->bold = $bold ?? $this->bold;
        $this->colors = $colors !== null ? array_values($colors) : $this->colors;
        $this->fontColor = $fontColor ?? $this->fontColor;
        $this->fontSize = $fontSize ?? $this->fontSize;
        $this->format = $format ?? $this->format;
        $this->hash = $hash ?? $this->hash;
        $this->host = $host ?? $this->host;
        $this->length = $length ?? $this->length;
        $this->mask = $mask ?? $this->mask;
        $this->proxy = $proxy ?? $this->proxy;
        $this->region = $region ?? $this->region;
        $this->rounded = $rounded ?? $this->rounded;
        $this->size = $size ?? $this->size;
        $this->source = $source ?? $this->source;
        $this->uppercase = $uppercase ?? $this->uppercase;

        return $this;
    }

    public function __call(string $method, array $arguments): mixed
    {
        if (! in_array($method, ['build', 'configure'], true)) {
            throw new BadMethodCallException('Call to undefined method '.static::class."::{$method}()");
        }

        return $this->{$method}(...$arguments);
    }

    public static function __callStatic(string $method, array $arguments): mixed
    {
        return (self::$shared ??= new self)->__call($method, $arguments);
    }

    public function buildUrl(string $base, array $params): string
    {
        return $base.'?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function parseHostname(?string $value): ?string
    {
        return $value && ! str_contains($value, '://') ? "https://{$value}" : $value;
    }
}
