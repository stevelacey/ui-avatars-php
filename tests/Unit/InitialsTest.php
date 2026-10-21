<?php

use SteveLacey\UiAvatars\Initials;

test('it matches php initials', function (string $name, string $expected) {
    expect(Initials::generate($name))->toBe($expected);
})->with([
    ['Dr. Dre', 'DD'],
    ['Gustav Årgonson', 'GÅ'],
    ['John Doe (Anderson)', 'JA'],
    ['John Doe Bergerson', 'JB'],
    ['John Doe', 'JD'],
    ['John', 'JO'],
    ['MA', 'MA'],
]);

test('it can preserve letter case', function () {
    expect(Initials::generate('john doe', uppercase: false))->toBe('jd');
});

test('it generates first and last initials', function () {
    expect(Initials::generate('John Christian Doe'))->toBe('JD');
});

test('it limits a single name to the requested length', function () {
    expect(Initials::generate('Leonardo'))->toBe('LE');
});

test('it uses each name when the requested length allows it', function () {
    expect(Initials::generate('John Christian Doe', length: 3))->toBe('JCD');
});

test('it reuses letters from the last name when needed', function () {
    expect(Initials::generate('Ada Lovelace', length: 3))->toBe('ALO');
});

test('it preserves hyphenated surnames when two initials are requested', function () {
    expect(Initials::generate('Lasse Foo-Rafn'))->toBe('LF');
});

test('it removes special characters', function () {
    expect(Initials::generate('John Doe (Anderson)'))->toBe('JA');
});

test('it splits hyphenated surnames when more initials are requested', function () {
    expect(Initials::generate('Lasse Foo-Rafn', length: 3))->toBe('LFR');
});

test('it splits a hyphenated single name', function () {
    expect(Initials::generate('Jean-Luc'))->toBe('JL');
});

test('it trims outer hyphens and whitespace', function () {
    expect(Initials::generate(' -Jean-Luc- '))->toBe('JL');
});

test('it uppercases unicode initials', function () {
    expect(Initials::generate('Jens Ølsted'))->toBe('JØ');
});
