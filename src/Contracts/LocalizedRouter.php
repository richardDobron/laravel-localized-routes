<?php

namespace richarddobron\LocalizedRoutes\Contracts;

use Illuminate\Routing\Route;

interface LocalizedRouter
{
    /** @see \richarddobron\LocalizedRoutes\Mixin\LocalizedRouter::locale */
    public function locale(array $locales): void;

    /** @see \richarddobron\LocalizedRoutes\Mixin\LocalizedRouter::getByKey */
    public function getByKey(string $key): ?Route;
}
