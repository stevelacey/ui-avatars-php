<?php

use SteveLacey\UiAvatars\Laravel\ServiceProvider;

test('it merges the default config', function () {
    expect(config('ui-avatars'))->toHaveKeys(['alpha', 'colors', 'font_color', 'size', 'source']);
    expect(array_filter(config('ui-avatars'), fn ($value) => $value !== null))->toBe(['disk' => 'public']);
});

test('it configures the shared instance from config', function () {
    config([
        'ui-avatars.colors' => [['#fee2e2', '#ef4444']],
        'ui-avatars.font_size' => 0.5,
        'ui-avatars.rounded' => true,
        'ui-avatars.size' => 64,
    ]);

    resetShared();
    (new ServiceProvider($this->app))->boot();

    $avatars = shared();

    expect($avatars->colors)->toBe([['#fee2e2', '#ef4444']]);
    expect($avatars->fontSize)->toBe(0.5);
    expect($avatars->rounded)->toBeTrue();
    expect($avatars->size)->toBe(64);
    expect(avatar_url(name: 'Ada Lovelace'))->toBe('https://ui-avatars.com/api/AL/64/fee2e233/ef4444/2/0.5/1/1/1/svg');
});

test('it registers publishable config', function () {
    $paths = ServiceProvider::pathsToPublish(ServiceProvider::class, 'config');
    expect(array_combine(array_map('realpath', array_keys($paths)), $paths))->toBe(
        [realpath(dirname(__DIR__, 2).'/config/ui-avatars.php') => config_path('ui-avatars.php')],
    );
});
