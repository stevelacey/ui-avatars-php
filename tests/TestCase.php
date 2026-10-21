<?php

namespace SteveLacey\UiAvatars\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SteveLacey\UiAvatars\Laravel\ServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        resetShared();

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
