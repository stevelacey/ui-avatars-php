<?php

use SteveLacey\UiAvatars\Support\Url;

test('build omits null values', function () {
    expect(Url::build('https://example.com/', ['a' => null, 'b' => 'two words']))
        ->toBe('https://example.com/?b=two%20words');
});

test('with scheme defaults to https', function () {
    expect(Url::withScheme('example.com'))->toBe('https://example.com');
    expect(Url::withScheme('http://example.com'))->toBe('http://example.com');
    expect(Url::withScheme(null))->toBeNull();
});

test('is local', function () {
    expect(Url::isLocal('/media/avatar.jpg'))->toBeTrue();
    expect(Url::isLocal('http://localhost:8000/avatar.jpg'))->toBeTrue();
    expect(Url::isLocal('https://example.com/avatar.jpg'))->toBeFalse();
});
