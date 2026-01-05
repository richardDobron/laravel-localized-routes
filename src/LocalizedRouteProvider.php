<?php

namespace richarddobron\LocalizedRoutes;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use richarddobron\LocalizedRoutes\Contracts\LocalizedRoute;
use richarddobron\LocalizedRoutes\Contracts\LocalizedRouter;
use richarddobron\LocalizedRoutes\Middleware\EnforceRouteLocale;

class LocalizedRouteProvider
{
    public function registerLocalizedRoutes(): void
    {
        if (app()->routesAreCached()) {
            return;
        }

        $collection = app(Router::class)->getRoutes();

        foreach ($collection->getRoutes() as $route) {
            /** @var Route&LocalizedRoute $route */
            if ($route->getAction('canonical') || empty($route->locale())) {
                continue;
            }

            $route->middleware(EnforceRouteLocale::class);

            $route->action['key'] = $route->getKey();

            foreach ($route->translateRoutes() as $localizedRoute) {
                $collection->add($localizedRoute);
            }

            $collection->add($route);
        }
    }

    public function is(string ...$patterns): bool
    {
        /** @var LocalizedRouter&Router $router */
        $router = app(Router::class);

        $route = $router->current();

        if ($route && $canonical = $route->getAction('canonical')) {
            $route = $router->getByKey($canonical);
        }

        return $route && $route->named(...$patterns);
    }
}
