<?php

use SteveLacey\UiAvatars\Avatars;

test('it requires name or email', function () {
    avatar_url();
})->throws(InvalidArgumentException::class, 'requires at least one');

test('avatar_url delegates to the shared avatars instance', function () {
    expect(avatar_url(name: NAME, email: EMAIL))->toBe(Avatars::build(name: NAME, email: EMAIL));
});

test('avatar_url delegates image to the shared avatars instance', function () {
    expect(avatar_url(name: NAME, email: EMAIL, image: 'https://example.com/photo.jpg'))
        ->toBe(Avatars::build(name: NAME, email: EMAIL, image: 'https://example.com/photo.jpg'));
});

test('reconfiguring the shared instance changes the helper', function () {
    Avatars::configure(colors: ['abcdef']);
    expect(avatar_url(name: NAME))->toContain('/abcdef/');
});

test('fresh default configuration matches the helper', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL))->toBe(avatar_url(name: NAME, email: EMAIL));
});

test('without email is direct ui avatars url', function () {
    expect(avatar_url(name: NAME))->toStartWith('https://ui-avatars.com/api/AL/')->not->toContain('gravatar');
});

test('without email uses svg', function () {
    expect(avatar_url(name: NAME))->toEndWith('/svg');
});

test('with email uses png', function () {
    expect(avatar_url(name: NAME, email: EMAIL))->toEndWith('%2Fpng');
});

test('with email wraps in gravatar', function () {
    expect(avatar_url(name: NAME, email: EMAIL))->toStartWith('https://www.gravatar.com/avatar/')->toContain('ui-avatars.com');
});

test('without name derives initials from email', function () {
    expect(avatar_url(email: EMAIL))->toContain('%2FAD%2F');
});

test('derived initials skip non letters in the local part', function () {
    expect(avatar_url(email: '42.john@example.com'))->toContain('%2FJO%2F');
});

test('derived initials fall back to raw characters when no letters exist', function () {
    expect(avatar_url(email: '42@example.com'))->toContain('%2F42%2F');
});

test('derived initials strip whitespace before the no letters fallback', function () {
    expect(avatar_url(email: '  42@example.com'))->toContain('%2F42%2F')->not->toContain('%2520');
});

test('derived initials never come from the email domain', function () {
    expect(avatar_url(email: '42@example.com'))->not->toContain('%2Fex%2F');
});

test('it is deterministic', function () {
    expect(avatar_url(name: NAME))->toBe(avatar_url(name: NAME));
});

test('gravatar hash matches email hash', function () {
    expect(avatar_url(name: NAME, email: EMAIL))->toContain(md5(EMAIL));
});

test('color is picked from email not name when both given', function () {
    $color = fn ($url) => preg_match('#/([0-9a-f]{6})/2/0\.4/0/1/1/#', rawurldecode($url), $match) ? $match[1] : null;

    expect($color(avatar_url(name: NAME, email: EMAIL)))->toBe($color(avatar_url(name: 'Someone Else', email: EMAIL)));
});

test('color differs from email when no email given', function () {
    expect(avatar_url(name: 'Ada Lovelace'))->not->toBe(avatar_url(name: 'Grace Hopper'));
});

test('email is case and whitespace insensitive for the gravatar hash', function () {
    $digest = fn ($url) => explode('?', str_replace('https://www.gravatar.com/avatar/', '', $url))[0];

    expect($digest(avatar_url(email: ' Ada@Example.com ')))->toBe($digest(avatar_url(email: 'ada@example.com')));
});

test('name is replaced with its initials', function () {
    expect(avatar_url(name: 'José García'))->not->toContain('José')->not->toContain('García')->toContain('/JG/');
});

test('uppercase option controls generated initials', function () {
    expect(avatar_url(name: 'john doe', uppercase: false))->toContain('/jd/');
});

test('png output removes special characters from generated initials', function () {
    expect(avatar_url(name: 'John Doe (Anderson)', format: 'png'))->toContain('/JA/');
});

test('svg background carries alpha suffix on hex color', function () {
    [$background, $text] = explode('/', explode('/128/', avatar_url(name: NAME))[1]);
    // default alpha 0.2 * 255 = 51 = 0x33
    expect($background)->toBe("{$text}33");
});

test('png background is rgba of the hex color', function () {
    $url = rawurldecode(avatar_url(name: NAME, email: EMAIL));
    expect(preg_match('#/128/rgba\((\d+),(\d+),(\d+),0\.2\)/([0-9a-f]{6})/#', $url, $match))->toBe(1);
    [, $r, $g, $b, $text] = $match;
    expect([(int) $r, (int) $g, (int) $b])->toBe(array_map('hexdec', str_split($text, 2)));
});

test('size can be overridden per call', function () {
    expect(avatar_url(name: NAME, size: 64))->toContain('/64/');
});

test('host can be overridden per call', function () {
    expect(avatar_url(name: NAME, host: 'https://avatars.example.com'))->toStartWith('https://avatars.example.com/api/');
});

test('host without a scheme defaults to https', function () {
    expect(avatar_url(name: NAME, host: 'avatars.example.com'))->toStartWith('https://avatars.example.com/api/');
});

test('proxy can be overridden per call', function () {
    expect(avatar_url(name: NAME, mask: 'hexagon', proxy: 'https://images.example.com'))->toStartWith('https://images.example.com/?url=');
});

test('proxy without a scheme defaults to https', function () {
    expect(avatar_url(name: NAME, mask: 'hexagon', proxy: 'images.example.com'))->toStartWith('https://images.example.com/?url=');
});

test('region can be overridden per call', function () {
    expect(avatar_url(name: NAME, region: 'eu'))->toStartWith('https://eu.ui-avatars.com/api/');
});

test('host takes precedence over region', function () {
    expect(avatar_url(name: NAME, host: 'https://avatars.example.com', region: 'eu'))->toStartWith('https://avatars.example.com/api/');
});

test('source can be overridden per call', function () {
    expect(avatar_url(name: NAME, email: EMAIL, source: 'libravatar'))->toStartWith('https://seccdn.libravatar.org/avatar/');
});

test('format can be overridden per call', function () {
    expect(avatar_url(name: NAME, email: EMAIL, format: 'svg'))->toEndWith('%2Fsvg');
});

test('non standard format is proxied through wsrv', function () {
    expect(avatar_url(name: NAME, format: 'webp'))->toStartWith('https://wsrv.nl/?url=')->toEndWith('&output=webp');
});

test('ui avatars options can be overridden per call', function () {
    $url = avatar_url(name: NAME, length: 1, fontSize: 0.5, rounded: true, bold: false, uppercase: false);
    expect($url)->toEndWith('/1/0.5/1/0/0/svg');
});

test('rounded without email is not proxied through wsrv', function () {
    expect(avatar_url(name: NAME, rounded: true))->toStartWith('https://ui-avatars.com/api/')->not->toContain('wsrv.nl');
});

test('with email and rounded is proxied through wsrv', function () {
    $url = avatar_url(name: NAME, email: EMAIL, rounded: true);
    expect($url)
        ->toStartWith('https://wsrv.nl/?url=')
        ->toContain('&mask=circle')
        ->toContain('&w=128&h=128')
        ->toEndWith('&output=png');
    expect(rawurldecode($url))->toContain('gravatar.com');
});

test('mask is proxied through wsrv', function () {
    $url = avatar_url(name: NAME, mask: 'hexagon');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon')->toEndWith('&output=png');
});

test('alpha can be overridden per call', function () {
    Avatars::configure(colors: ['#000000']);
    expect(avatar_url(name: NAME, alpha: 0.5))->toContain('/00000080/');
});

test('color forces the same color regardless of name', function () {
    expect(avatar_url(name: 'Ada Lovelace', color: '#dc2626'))->toContain('/dc262633/dc2626/');
    expect(avatar_url(name: 'Grace Hopper', color: '#dc2626'))->toContain('/dc262633/dc2626/');
});

test('color combines with font color', function () {
    expect(avatar_url(name: NAME, color: '#fee2e2', fontColor: '#dc2626'))->toContain('/fee2e233/dc2626/');
});

test('color accepts shorthand hex', function () {
    expect(avatar_url(name: NAME, color: '#fff'))->toContain('/ffffff33/ffffff/');
});

test('color does not mutate the shared avatars instance', function () {
    $before = shared()->colors;
    avatar_url(name: NAME, color: '#dc2626');
    expect(shared()->colors)->toBe($before);
});

test('hash prefixed colors do not leak hash into url', function () {
    Avatars::configure(colors: ['#000000']);
    expect(avatar_url(name: NAME))->toContain('/000000/')->not->toContain('#');
});

test('per call overrides do not mutate the shared avatars instance', function () {
    $avatars = shared();
    $before = [$avatars->bold, $avatars->size, $avatars->mask, $avatars->proxy];
    avatar_url(name: NAME, bold: false, size: 64, mask: 'hexagon', proxy: 'example.com');
    expect([$avatars->bold, $avatars->size, $avatars->mask, $avatars->proxy])->toBe($before);
});

test('avatar_url returns wsrv proxied image', function () {
    expect(avatar_url(name: NAME, image: 'https://example.com/avatar.jpg'))->toStartWith('https://wsrv.nl/?url=')->toContain('&fit=cover');
});

test('avatar_url prefers image over email', function () {
    $url = avatar_url(name: NAME, email: EMAIL, image: 'https://example.com/avatar.jpg');
    expect($url)
        ->toStartWith('https://wsrv.nl/?url=')
        ->toContain('https%3A%2F%2Fexample.com%2Favatar.jpg')
        ->not->toContain('gravatar.com')
        ->toContain('default=https%3A%2F%2Fui-avatars.com%2Fapi%2F');
});
