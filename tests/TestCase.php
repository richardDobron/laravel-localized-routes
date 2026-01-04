<?php

namespace richarddobron\LocalizedRoutes\Tests;

use richarddobron\LocalizedRoutes\LocalizedRouteServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            LocalizedRouteServiceProvider::class,
        ];
    }
}
