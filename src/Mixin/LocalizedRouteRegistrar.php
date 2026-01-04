<?php

namespace richarddobron\LocalizedRoutes\Mixin;

use Illuminate\Routing\RouteRegistrar;

class LocalizedRouteRegistrar extends RouteRegistrar
{
    public function localeGroup($callback): void
    {
        $this->router->group($this->attributes, $callback);
    }

    public function locale(?array $locales = null): static
    {
        $this->attributes['locale'] = array_fill_keys($locales ?? config('localized-routes.locales'), null);

        return $this;
    }
}
