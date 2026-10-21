<?php

namespace SteveLacey\UiAvatars\Laravel;

use Illuminate\Support\Str;
use SteveLacey\UiAvatars\Avatars;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ui-avatars.php', 'ui-avatars');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        Avatars::configure(...collect(config('ui-avatars'))
            ->except('disk')
            ->reject(fn ($value) => $value === null)
            ->mapWithKeys(fn ($value, $key) => [Str::camel($key) => $value]));

        $this->publishes([__DIR__.'/../../config/ui-avatars.php' => config_path('ui-avatars.php')], 'config');
    }
}
