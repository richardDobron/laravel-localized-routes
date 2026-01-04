<?php

namespace richarddobron\LocalizedRoutes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerLocalizedRoutes()
 * @method static bool is(string ...$patterns)
 *
 * @see \richarddobron\LocalizedRoutes\LocalizedRoute
 */
class LocalizedRoute extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \richarddobron\LocalizedRoutes\LocalizedRouteProvider::class;
    }
}
