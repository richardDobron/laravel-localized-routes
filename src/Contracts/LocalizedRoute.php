<?php

namespace richarddobron\LocalizedRoutes\Contracts;

use Illuminate\Routing\Route;

interface LocalizedRoute
{
    /** @see \richarddobron\LocalizedRoutes\Mixin\LocalizedRoute::locale */
    public function locale(?array $locales = null): Route;

    /** @see \richarddobron\LocalizedRoutes\Mixin\LocalizedRoute::getKey */
    public function getKey(): string;

    /**
     * @return Route[]
     *
     * @see \richarddobron\LocalizedRoutes\Mixin\LocalizedRoute::translateRoutes
     */
    public function translateRoutes(): array;

    /** @see \richarddobron\LocalizedRoutes\Mixin\LocalizedRoute::translateRoute */
    public function translateRoute(string $locale): ?Route;
}
