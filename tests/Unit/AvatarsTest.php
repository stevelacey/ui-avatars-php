<?php

use SteveLacey\UiAvatars\Avatars;

test('instance requires name or email', function () {
    (new Avatars)->build();
})->throws(InvalidArgumentException::class, 'requires at least one');

test('all colors are valid hex triples', function () {
    expect(Avatars::COLORS)->not->toBeEmpty();
    foreach (Avatars::COLORS as $color) {
        expect($color)->toMatch('/^#[0-9a-f]{6}$/');
    }
});

test('all colors are unique', function () {
    expect(Avatars::COLORS)->toHaveCount(count(array_unique(Avatars::COLORS)));
});

test('default instance uses the default palette', function () {
    expect((new Avatars)->colors)->toBe(Avatars::COLORS);
});

test('custom colors are used instead of the default palette', function () {
    expect((new Avatars(colors: ['000000']))->build(name: NAME))->toContain('/000000/');
});

test('hash prefixed colors are stripped before building the url', function () {
    $url = (new Avatars(colors: ['#000000']))->build(name: NAME);
    expect($url)->toContain('/000000/')->not->toContain('#');
});

test('shorthand hex colors are expanded to six digits', function () {
    expect((new Avatars(colors: ['#fff']))->build(name: NAME))->toContain('/ffffff33/ffffff/');
});

test('shorthand hex colors do not crash the png path', function () {
    $url = rawurldecode((new Avatars(colors: ['#fff']))->build(name: NAME, email: EMAIL));
    expect($url)->toContain('rgba(255,255,255,0.2)')->toContain('/ffffff/');
});

test('shorthand hex works in pair color entries', function () {
    expect((new Avatars(colors: [['#fff', '#f00']]))->build(name: NAME))->toContain('/ffffff33/ff0000/');
});

test('custom colors are keyed sequentially', function () {
    expect((new Avatars(colors: ['a' => '000000', 'b' => 'ffffff']))->colors)->toBe(['000000', 'ffffff']);
});

test('pair color entries pick background and text independently', function () {
    $url = (new Avatars(colors: [['#fee2e2', '#ef4444']]))->build(name: NAME);
    preg_match('#/([0-9a-f]{6})[0-9a-f]{2}/([0-9a-f]{6})/2/#', $url, $match);
    expect($match[1])->toBe('fee2e2');
    expect($match[2])->toBe('ef4444');
});

test('pair color entries still apply alpha to the background', function () {
    // default alpha 0.2 * 255 = 51 = 0x33
    expect((new Avatars(colors: [['#fee2e2', '#ef4444']]))->build(name: NAME))->toContain('/fee2e233/ef4444/');
});

test('pair color entries can be made fully opaque via alpha', function () {
    expect((new Avatars(colors: [['#fee2e2', '#ef4444']], alpha: 1))->build(name: NAME))->toContain('/fee2e2/ef4444/');
});

test('opaque single color keeps the text the same hue', function () {
    expect((new Avatars(colors: ['#ef4444'], alpha: 1))->build(name: NAME))->toContain('/ef4444/ef4444/');
});

test('font color overrides the palette derived text color', function () {
    $url = (new Avatars(colors: [['#fee2e2', '#ef4444']]))->build(name: NAME, fontColor: '#fff');
    expect($url)->toContain('/ffffff/')->not->toContain('ef4444');
});

test('font color overrides regardless of alpha', function () {
    expect((new Avatars(colors: ['#ef4444'], alpha: 1))->build(name: NAME, fontColor: '#000'))->toContain('/ef4444/000000/');
});

test('configure sets a persistent font color override', function () {
    $url = (new Avatars(colors: [['#fee2e2', '#ef4444']]))->configure(fontColor: '#fff')->build(name: NAME);
    expect($url)->toContain('/ffffff/')->not->toContain('ef4444');
});

test('background overrides the palette derived background', function () {
    $url = (new Avatars(colors: [['#fee2e2', '#ef4444']]))->build(name: NAME, background: '#000', alpha: 1);
    expect($url)->toContain('/000000/ef4444/');
});

test('configure sets a persistent background override', function () {
    $avatars = (new Avatars(alpha: 1))->configure(background: '#000000');
    $ada = $avatars->build(name: NAME);
    $grace = $avatars->build(name: 'Grace Hopper');
    expect($ada)->toContain('/000000/');
    expect($grace)->toContain('/000000/');
    expect(explode('/000000/', $ada)[1])->not->toBe(explode('/000000/', $grace)[1]);
});

test('pair color entries apply alpha on the png path too', function () {
    $url = rawurldecode((new Avatars(colors: [['#fee2e2', '#ef4444']]))->build(name: NAME, email: EMAIL));
    expect($url)->toContain('rgba(254,226,226,0.2)')->toContain('/ef4444/');
});

test('colors list can mix plain hex and pair entries', function () {
    $url = (new Avatars(colors: ['#3b82f6', ['#fee2e2', '#ef4444']]))->build(name: NAME);
    expect(str_contains($url, '3b82f6') || str_contains($url, 'fee2e2'))->toBeTrue();
});

test('separate instances do not share configuration', function () {
    expect((new Avatars(colors: ['ff0000']))->build(name: NAME))->toContain('/ff0000/');
    expect((new Avatars(colors: ['0000ff']))->build(name: NAME))->toContain('/0000ff/');
});

test('custom size appears in the ui avatars url', function () {
    expect((new Avatars(size: 256))->build(name: NAME))->toContain('/256/');
});

test('custom size is passed through to gravatars s param', function () {
    $url = (new Avatars(size: 256))->build(name: NAME, email: EMAIL);
    expect($url)->toStartWith('https://www.gravatar.com/avatar/')->toContain('?s=256&d=');
});

test('default size is 128', function () {
    expect((new Avatars)->size)->toBe(128);
});

test('default host is null', function () {
    expect((new Avatars)->host)->toBeNull();
});

test('default proxy is null', function () {
    expect((new Avatars)->proxy)->toBeNull();
});

test('default region is null', function () {
    expect((new Avatars)->region)->toBeNull();
});

test('default ui avatars host is used', function () {
    expect((new Avatars)->build(name: NAME))->toStartWith('https://ui-avatars.com/api/');
});

test('region selects the ui avatars host', function () {
    expect((new Avatars)->build(name: NAME, region: 'eu'))->toStartWith('https://eu.ui-avatars.com/api/');
});

test('na region selects the ui avatars host', function () {
    expect((new Avatars)->build(name: NAME, region: 'na'))->toStartWith('https://na.ui-avatars.com/api/');
});

test('unknown region throws', function () {
    (new Avatars)->build(name: NAME, region: 'notaregion');
})->throws(InvalidArgumentException::class, 'unknown region');

test('custom host without a scheme defaults to https', function () {
    expect((new Avatars)->build(name: NAME, host: 'avatars.example.com'))->toStartWith('https://avatars.example.com/api/');
});

test('custom host keeps an explicit scheme', function () {
    expect((new Avatars)->build(name: NAME, host: 'https://avatars.example.com'))->toStartWith('https://avatars.example.com/api/');
});

test('custom host is used for email fallback', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, host: 'https://avatars.example.com');
    expect(rawurldecode($url))->toContain('https://avatars.example.com/api/');
});

test('custom host takes precedence over region', function () {
    $url = (new Avatars)->build(name: NAME, host: 'https://avatars.example.com', region: 'eu');
    expect($url)->toStartWith('https://avatars.example.com/api/')->not->toContain('eu.ui-avatars.com');
});

test('unknown host throws', function () {
    (new Avatars)->build(name: NAME, host: 'notarehost');
})->throws(InvalidArgumentException::class, 'unknown host');

test('default wsrv proxy is used', function () {
    expect((new Avatars)->build(name: NAME, mask: 'hexagon'))->toStartWith('https://wsrv.nl/?url=');
});

test('custom proxy without a scheme defaults to https', function () {
    expect((new Avatars)->build(name: NAME, mask: 'hexagon', proxy: 'images.example.com'))->toStartWith('https://images.example.com/?url=');
});

test('custom proxy keeps an explicit scheme', function () {
    expect((new Avatars)->build(name: NAME, mask: 'hexagon', proxy: 'https://images.example.com'))->toStartWith('https://images.example.com/?url=');
});

test('instance proxy is used when wrapping', function () {
    expect((new Avatars(proxy: 'https://images.example.com'))->build(name: NAME, mask: 'hexagon'))->toStartWith('https://images.example.com/?url=');
});

test('unknown proxy throws', function () {
    (new Avatars)->build(name: NAME, mask: 'hexagon', proxy: 'notaproxy');
})->throws(InvalidArgumentException::class, 'unknown proxy');

test('unknown proxy throws even when not wrapping', function () {
    (new Avatars)->build(name: NAME, proxy: 'notaproxy');
})->throws(InvalidArgumentException::class, 'unknown proxy');

test('default source is gravatar', function () {
    expect((new Avatars)->source)->toBe('gravatar');
});

test('source selects the photo host', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, source: 'libravatar'))->toStartWith('https://seccdn.libravatar.org/avatar/');
});

test('default hash is sha256', function () {
    expect((new Avatars)->hash)->toBe('sha256');
});

test('instance hash is used for photo lookups', function () {
    expect((new Avatars(hash: 'md5'))->build(name: NAME, email: EMAIL))->toContain('/avatar/'.md5(EMAIL).'?');
});

test('configure sets a persistent hash', function () {
    expect((new Avatars)->configure(hash: 'md5')->build(name: NAME, email: EMAIL))->toContain('/avatar/'.md5(EMAIL).'?');
});

test('hash applies to proxied photo lookups', function () {
    expect(rawurldecode((new Avatars)->build(name: NAME, email: EMAIL, mask: 'hexagon')))->toContain('/avatar/'.hash('sha256', EMAIL).'?s=128&d=404');
});

test('masks without an email proxy the initials directly', function () {
    $url = (new Avatars)->build(name: NAME, mask: 'hexagon');
    expect(query($url))->toMatchArray(['url' => (new Avatars)->build(name: NAME), 'mask' => 'hexagon'])->not->toHaveKey('default');
    expect($url)->not->toContain('gravatar');
});

test('unknown hash throws', function () {
    (new Avatars)->build(name: NAME, email: EMAIL, hash: 'sha1');
})->throws(InvalidArgumentException::class, "unknown hash: 'sha1'");

test('unknown source throws', function () {
    (new Avatars)->build(name: NAME, email: EMAIL, source: 'notarealsource');
})->throws(InvalidArgumentException::class, 'unknown source');

test('unknown source throws even without email', function () {
    (new Avatars)->build(name: NAME, source: 'notarealsource');
})->throws(InvalidArgumentException::class, 'unknown source');

test('custom source without a scheme defaults to https', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, source: 'avatars.example.com'))->toStartWith('https://avatars.example.com/avatar/');
});

test('custom source keeps an explicit scheme', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, source: 'https://avatars.example.com'))->toStartWith('https://avatars.example.com/avatar/');
});

test('configure updates host in place', function () {
    expect((new Avatars)->configure(host: 'https://avatars.example.com')->host)->toBe('https://avatars.example.com');
});

test('configure updates proxy in place', function () {
    expect((new Avatars)->configure(proxy: 'https://images.example.com')->proxy)->toBe('https://images.example.com');
});

test('configure updates region in place', function () {
    expect((new Avatars)->configure(region: 'eu')->region)->toBe('eu');
});

test('configure updates source in place', function () {
    expect((new Avatars)->configure(source: 'libravatar')->source)->toBe('libravatar');
});

test('host can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars(region: 'eu');
    expect($avatars->build(name: NAME, host: 'https://avatars.example.com'))->toStartWith('https://avatars.example.com/api/');
    expect($avatars->host)->toBeNull();
    expect($avatars->region)->toBe('eu');
});

test('proxy can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars;
    expect($avatars->build(name: NAME, mask: 'hexagon', proxy: 'https://images.example.com'))->toStartWith('https://images.example.com/?url=');
    expect($avatars->proxy)->toBeNull();
});

test('region can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars(region: 'eu');
    expect($avatars->build(name: NAME, region: 'na'))->toStartWith('https://na.ui-avatars.com/api/');
    expect($avatars->region)->toBe('eu');
});

test('source can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars;
    expect($avatars->build(name: NAME, email: EMAIL, source: 'libravatar'))->toStartWith('https://seccdn.libravatar.org/avatar/');
    expect($avatars->source)->toBe('gravatar');
});

test('default format is null', function () {
    expect((new Avatars)->format)->toBeNull();
});

test('email with gravatar defaults to png', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL))->toEndWith('%2Fpng');
});

test('email with libravatar defaults to svg', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, source: 'libravatar'))->toEndWith('%2Fsvg');
});

test('email with rounded defaults to png even on libravatar', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, rounded: true, source: 'libravatar');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toEndWith('&output=png')->toContain('rgba');
});

test('no email defaults to svg regardless of source', function () {
    expect((new Avatars)->build(name: NAME, source: 'libravatar'))->toEndWith('/svg');
});

test('format can force svg on gravatar even though it is broken there', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, format: 'svg'))->toEndWith('%2Fsvg');
});

test('format can force png on libravatar', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, source: 'libravatar', format: 'png'))->toEndWith('%2Fpng');
});

test('format can force png without an email', function () {
    $url = (new Avatars)->build(name: NAME, format: 'png');
    expect($url)->toEndWith('/png')->not->toContain('wsrv.nl');
});

test('non standard format is proxied through wsrv', function () {
    $url = (new Avatars)->build(name: NAME, format: 'webp');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toEndWith('&output=webp');
    expect(rawurldecode($url))->toContain('ui-avatars.com');
});

test('non standard format with email is proxied through wsrv', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, format: 'webp');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toEndWith('&output=webp');
    expect(rawurldecode($url))->toContain('gravatar.com');
});

test('configure updates format in place', function () {
    expect((new Avatars)->configure(format: 'png')->format)->toBe('png');
});

test('format can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars(format: 'png');
    expect($avatars->build(name: NAME, format: 'svg'))->toEndWith('/svg');
    expect($avatars->format)->toBe('png');
});

test('default ui avatars options match the original hardcoded values', function () {
    $avatars = new Avatars;
    expect($avatars->length)->toBe(2);
    expect($avatars->fontSize)->toBe(0.4);
    expect($avatars->rounded)->toBeFalse();
    expect($avatars->bold)->toBeTrue();
    expect($avatars->uppercase)->toBeTrue();
    expect($avatars->build(name: NAME))->toEndWith('/2/0.4/0/1/1/svg');
});

test('custom ui avatars options appear in the url', function () {
    $avatars = new Avatars(bold: false, fontSize: 0.5, length: 1, rounded: true, uppercase: false);
    expect($avatars->build(name: NAME))->toEndWith('/1/0.5/1/0/0/svg');
});

test('default mask is null', function () {
    expect((new Avatars)->mask)->toBeNull();
});

test('rounded without email is not proxied through wsrv', function () {
    $url = (new Avatars)->build(name: NAME, rounded: true);
    expect($url)->toStartWith('https://ui-avatars.com/api/')->not->toContain('wsrv.nl')->toEndWith('/2/0.4/1/1/1/svg');
});

test('email without rounded or mask is not proxied through wsrv', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL);
    expect($url)->toStartWith('https://www.gravatar.com/avatar/')->not->toContain('wsrv.nl');
});

test('email with rounded is proxied through wsrv', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, rounded: true);
    expect($url)
        ->toStartWith('https://wsrv.nl/?url=')
        ->toContain('&w=128&h=128')
        ->toContain('&mask=circle')
        ->toEndWith('&output=png');
    expect(rawurldecode($url))->toContain('gravatar.com');
});

test('wsrv uses 404 and its own default for email fallback', function () {
    $query = query((new Avatars)->build(name: NAME, email: EMAIL, rounded: true));
    expect(query($query['url']))->toBe(['s' => '128', 'd' => '404']);
    expect($query['default'])->toStartWith('https://ui-avatars.com/api/');
});

test('circle mask makes the email fallback rounded', function () {
    $default = query((new Avatars)->build(name: NAME, email: EMAIL, mask: 'circle'))['default'];
    expect($default)->toStartWith('https://ui-avatars.com/api/')->toContain('/2/0.4/1/1/1/png');
});

test('wsrv wraps a masked email fallback', function () {
    $default = query((new Avatars)->build(name: NAME, email: EMAIL, mask: 'hexagon'))['default'];
    $defaultQuery = query($default);
    expect($default)->toStartWith('https://wsrv.nl/?url=');
    expect($defaultQuery['url'])->toStartWith('https://ui-avatars.com/api/');
    expect($defaultQuery['mask'])->toBe('hexagon');
});

test('mask is proxied through wsrv even without email', function () {
    $url = (new Avatars)->build(name: NAME, mask: 'hexagon');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon')->toEndWith('&output=png');
    expect(rawurldecode($url))->toContain('ui-avatars.com');
});

test('mask with email is proxied through wsrv', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, mask: 'hexagon');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon')->toEndWith('&output=png');
    expect(rawurldecode($url))->toContain('gravatar.com');
});

test('custom mask is passed through to wsrv', function () {
    $url = (new Avatars)->build(name: NAME, mask: 'hexagon');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon');
});

test('explicit mask overrides the rounded circle default', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, rounded: true, mask: 'hexagon');
    expect($url)->toContain('&mask=hexagon')->not->toContain('&mask=circle');
});

test('wsrv proxy uses the requested size', function () {
    $url = (new Avatars(size: 256))->build(name: NAME, email: EMAIL, rounded: true);
    expect($url)->toContain('&w=256&h=256');
    expect(rawurldecode($url))->toContain('s=256');
});

test('wsrv proxy uses the requested format', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, rounded: true, format: 'jpg'))->toEndWith('&output=jpg');
});

test('rounded email proxy works with libravatar', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, rounded: true, source: 'libravatar');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=circle')->toEndWith('&output=png');
    expect(rawurldecode($url))->toContain('libravatar.org');
});

test('instance mask wraps through wsrv', function () {
    $url = (new Avatars(mask: 'hexagon'))->build(name: NAME);
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon');
});

test('instance rounded wraps email urls through wsrv', function () {
    $url = (new Avatars(rounded: true))->build(name: NAME, email: EMAIL);
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=circle');
});

test('configure mask enables the proxy', function () {
    $url = (new Avatars)->configure(mask: 'hexagon')->build(name: NAME);
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon');
});

test('configure rounded enables the email proxy', function () {
    $url = (new Avatars)->configure(rounded: true)->build(name: NAME, email: EMAIL);
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=circle');
});

test('configure updates mask in place', function () {
    expect((new Avatars)->configure(mask: 'hexagon')->mask)->toBe('hexagon');
});

test('mask can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars;
    expect($avatars->build(name: NAME, mask: 'hexagon'))->toStartWith('https://wsrv.nl/?url=');
    expect($avatars->mask)->toBeNull();
});

test('rounded can be disabled per call without mutating the instance', function () {
    $avatars = new Avatars(rounded: true);
    $url = $avatars->build(name: NAME, email: EMAIL, rounded: false);
    expect($url)->toStartWith('https://www.gravatar.com/avatar/')->not->toContain('wsrv.nl');
    expect($avatars->rounded)->toBeTrue();
});

test('default alpha is 0.2', function () {
    expect((new Avatars)->alpha)->toBe(0.2);
});

test('default alpha matches between svg and rgba paths', function () {
    $avatars = new Avatars(colors: ['#000000']);
    expect($avatars->build(name: NAME))->toContain('/00000033/');
    expect(rawurldecode($avatars->build(name: NAME, email: EMAIL)))->toContain('rgba(0,0,0,0.2)');
});

test('custom alpha changes both representations', function () {
    $avatars = new Avatars(colors: ['#000000'], alpha: 0.5);
    // 0.5 * 255 = 127.5, rounds to 128 = 0x80
    expect($avatars->build(name: NAME))->toContain('/00000080/');
    expect(rawurldecode($avatars->build(name: NAME, email: EMAIL)))->toContain('rgba(0,0,0,0.5)');
});

test('alpha rounds half to even like python', function () {
    // 0.3 * 255 = 76.5, rounds to 76 = 0x4c
    expect((new Avatars(colors: ['#000000'], alpha: 0.3))->build(name: NAME))->toContain('/0000004c/');
});

test('alpha is clamped to a valid hex byte range', function () {
    expect((new Avatars(colors: ['#000000'], alpha: 2))->build(name: NAME))->toContain('/000000/');
});

test('configure updates colors in place', function () {
    $avatars = new Avatars(colors: ['000000']);
    $avatars->configure(colors: ['ffffff']);
    expect($avatars->build(name: NAME))->toContain('/ffffff/');
});

test('configure can switch to pair color entries', function () {
    $avatars = (new Avatars)->configure(colors: [['#fee2e2', '#ef4444']]);
    expect($avatars->build(name: NAME))->toContain('/fee2e233/ef4444/');
});

test('configure updates size in place', function () {
    expect((new Avatars)->configure(size: 64)->size)->toBe(64);
});

test('configure updates ui avatars options in place', function () {
    $avatars = (new Avatars)->configure(bold: false, fontSize: 0.5, length: 1, rounded: true, uppercase: false);
    expect($avatars->build(name: NAME))->toEndWith('/1/0.5/1/0/0/svg');
});

test('configure leaves ui avatars options unchanged when omitted', function () {
    $avatars = (new Avatars(rounded: true))->configure(size: 256);
    expect($avatars->rounded)->toBeTrue();
    expect($avatars->build(name: NAME))->toEndWith('/2/0.4/1/1/1/svg');
});

test('configure updates alpha in place', function () {
    expect((new Avatars)->configure(alpha: 0.5)->alpha)->toBe(0.5);
});

test('configure leaves omitted fields unchanged', function () {
    $avatars = (new Avatars(colors: ['000000'], size: 64))->configure(size: 256);
    expect($avatars->colors)->toBe(['000000']);
    expect($avatars->size)->toBe(256);
});

test('configure returns self for chaining', function () {
    $avatars = new Avatars;
    expect($avatars->configure(size: 64))->toBe($avatars);
});

test('alpha can be overridden per call without mutating the instance', function () {
    $avatars = new Avatars(colors: ['#000000']);
    expect($avatars->build(name: NAME, alpha: 0.5))->toContain('/00000080/');
    expect($avatars->alpha)->toBe(0.2);
});

test('color bypasses the configured palette', function () {
    expect((new Avatars(colors: ['#000000']))->build(name: NAME, color: '#dc2626'))->toContain('/dc2626/');
});

test('color combines with font color', function () {
    expect((new Avatars)->build(name: NAME, color: '#fee2e2', fontColor: '#dc2626'))->toContain('/fee2e233/dc2626/');
});

test('color does not mutate the instance', function () {
    $avatars = new Avatars;
    $avatars->build(name: NAME, color: '#dc2626');
    expect($avatars->colors)->toBe(Avatars::COLORS);
});

test('random color uses auto text color', function () {
    expect((new Avatars)->build(name: NAME, color: 'random'))->toContain('/random/auto/');
});

test('random background uses auto text color', function () {
    expect((new Avatars)->build(name: NAME, background: 'random'))->toContain('/random/auto/');
});

test('random color works on the png path', function () {
    expect(rawurldecode((new Avatars)->build(name: NAME, email: EMAIL, color: 'random')))->toContain('/random/auto/');
});

test('random background works on the png path', function () {
    expect(rawurldecode((new Avatars)->build(name: NAME, email: EMAIL, background: 'random')))->toContain('/random/auto/');
});

test('configure statically configures the shared instance', function () {
    expect(Avatars::configure(size: 64))->toBe(shared());
    expect(Avatars::build(name: NAME))->toContain('/64/');
    expect((new Avatars)->build(name: NAME))->toContain('/128/');
});

test('build statically uses the shared instance', function () {
    expect(Avatars::build(name: NAME))->toBe((new Avatars)->build(name: NAME));
});

test('unknown methods throw', function () {
    Avatars::nope();
})->throws(BadMethodCallException::class, 'Call to undefined method SteveLacey\\UiAvatars\\Avatars::nope()');

test('shared avatars instance has default configuration', function () {
    $avatars = shared();
    expect($avatars)->toBeInstanceOf(Avatars::class);
    expect($avatars->colors)->toBe(Avatars::COLORS);
    expect($avatars->host)->toBeNull();
    expect($avatars->proxy)->toBeNull();
    expect($avatars->region)->toBeNull();
    expect($avatars->size)->toBe(128);
    expect($avatars->rounded)->toBeFalse();
    expect($avatars->mask)->toBeNull();
    expect($avatars->source)->toBe(Avatars::GRAVATAR);
    expect($avatars->format)->toBeNull();
    expect($avatars->alpha)->toBe(0.2);
});

test('image is proxied through wsrv with fit cover and size', function () {
    $url = (new Avatars)->build(name: NAME, image: 'https://example.com/avatar.jpg');
    expect($url)
        ->toStartWith('https://wsrv.nl/?url=')
        ->toContain('https%3A%2F%2Fexample.com%2Favatar.jpg')
        ->toContain('&w=128&h=128')
        ->toContain('&fit=cover')
        ->toContain('default=https%3A%2F%2Fui-avatars.com%2Fapi%2F');
});

test('image prefers over email with initials fallback', function () {
    $url = (new Avatars)->build(name: NAME, email: EMAIL, image: 'https://example.com/pic.png');
    expect($url)
        ->toStartWith('https://wsrv.nl/?url=')
        ->toContain('https%3A%2F%2Fexample.com%2Fpic.png')
        ->not->toContain('gravatar.com')
        ->toContain('default=https%3A%2F%2Fui-avatars.com%2Fapi%2F');
});

test('image supports rounded', function () {
    $url = (new Avatars)->build(name: NAME, image: 'https://example.com/pic.png', rounded: true);
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=circle');
});

test('image supports mask', function () {
    $url = (new Avatars)->build(name: NAME, image: 'https://example.com/pic.png', mask: 'hexagon');
    expect($url)->toStartWith('https://wsrv.nl/?url=')->toContain('&mask=hexagon');
});

test('image supports custom size', function () {
    expect((new Avatars)->build(name: NAME, image: 'https://example.com/pic.png', size: 256))->toContain('&w=256&h=256');
});

test('image supports custom format', function () {
    expect((new Avatars)->build(name: NAME, image: 'https://example.com/pic.png', format: 'webp'))->toEndWith('&output=webp');
});

test('null image falls back to initials', function () {
    expect((new Avatars)->build(name: NAME, image: null))->toStartWith('https://ui-avatars.com/api/');
});

test('null image falls back to email gravatar', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, image: null))->toStartWith('https://www.gravatar.com/avatar/');
});

test('empty image without name or email throws', function () {
    (new Avatars)->build(image: '');
})->throws(InvalidArgumentException::class, 'requires at least one');

test('relative image url is served unchanged', function () {
    expect((new Avatars)->build(name: NAME, email: EMAIL, image: '/media/avatar.jpg', mask: 'hexagon'))->toBe('/media/avatar.jpg');
});

test('local images are served unchanged', function (string $image) {
    expect((new Avatars)->build(name: NAME, image: $image, rounded: true))->toBe($image);
})->with([
    'media/avatar.jpg',
    '//cdn.example.com/avatar.jpg',
    'http://localhost:8000/avatar.jpg',
    'http://app.localhost/avatar.jpg',
    'https://myapp.test/avatar.jpg',
    'http://box.local/avatar.jpg',
    'http://myapp.internal/avatar.jpg',
    'http://nas.lan/avatar.jpg',
    'http://router.home.arpa/avatar.jpg',
    'https://foo.example/avatar.jpg',
    'http://myapp:8000/avatar.jpg',
    'http://LOCALHOST./avatar.jpg',
    'http://user:pass@localhost/avatar.jpg',
    'http://0.0.0.0:8000/avatar.jpg',
    'http://127.0.0.1/avatar.jpg',
    'http://10.0.0.5/avatar.jpg',
    'http://169.254.1.1/avatar.jpg',
    'http://172.16.0.1/avatar.jpg',
    'http://172.31.255.255/avatar.jpg',
    'http://192.168.1.2:8080/avatar.jpg',
    'http://[::1]:8000/avatar.jpg',
    'http://[fd00::1]/avatar.jpg',
    'http://[fe80::1]/avatar.jpg',
]);

test('public images are proxied', function (string $image) {
    expect((new Avatars)->build(name: NAME, image: $image))->toStartWith('https://wsrv.nl/?url=');
})->with([
    'https://example.com/avatar.jpg',
    'https://cdn.example.com/avatar.jpg?size=2',
    'http://172.32.0.1/avatar.jpg',
    'http://8.8.8.8/avatar.jpg',
    'https://10.example.com/avatar.jpg',
    'https://172.16.0.1.nip.io/avatar.jpg',
    'http://[2001:db8::1]/avatar.jpg',
    'https://testing.com/avatar.jpg',
]);

test('stringable image is supported', function () {
    $image = new class implements Stringable
    {
        public function __toString(): string
        {
            return 'https://cdn.example.com/user.png';
        }
    };

    expect((new Avatars)->build(name: NAME, image: $image))->toContain('https%3A%2F%2Fcdn.example.com%2Fuser.png');
});
