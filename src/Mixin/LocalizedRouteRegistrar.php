<?php

namespace richarddobron\LocalizedRoutes\Mixin;

use Illuminate\Routing\RouteRegistrar;

class LocalizedRouteRegistrar extends RouteRegistrar
{
    public function localeGroup($callback)
    {
        $this->router->group($this->attributes, $callback);
    }

    public function locale(?array $locales = null): LocalizedRouteRegistrar
    {
        $this->attributes['locale'] = array_fill_keys($locales ?? config('localized-routes.locales'), null);

        return $this;
    }
}
