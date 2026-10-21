<?php

use SteveLacey\UiAvatars\Avatars;
use SteveLacey\UiAvatars\Tests\FilamentTestCase;
use SteveLacey\UiAvatars\Tests\TestCase;

uses()->beforeEach(fn () => resetShared())->in('Unit');
uses(TestCase::class)->in('Laravel');
uses(FilamentTestCase::class)->in('Filament');

const NAME = 'Ada Lovelace';
const EMAIL = 'ada@example.com';

function shared(): Avatars
{
    return (new ReflectionProperty(Avatars::class, 'shared'))->getValue() ?? Avatars::configure();
}

function resetShared(): void
{
    (new ReflectionProperty(Avatars::class, 'shared'))->setValue(null, null);
}

function query(string $url): array
{
    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

    return $query;
}
