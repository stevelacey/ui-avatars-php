<?php

namespace SteveLacey\UiAvatars\Tests;

use Filament\FilamentServiceProvider;
use Filament\Support\SupportServiceProvider;
use Livewire\LivewireServiceProvider;

abstract class FilamentTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FilamentServiceProvider::class,
        ];
    }
}
