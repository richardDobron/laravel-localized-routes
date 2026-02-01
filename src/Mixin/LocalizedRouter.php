<?php

namespace richarddobron\LocalizedRoutes\Mixin;

use Closure;
use Illuminate\Routing\CompiledRouteCollection;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

/**
 * @mixin Router
 */
class LocalizedRouter
{
    public function locale(): Closure
    {
        return function (?array $locales = null): LocalizedRouteRegistrar {
            return (new LocalizedRouteRegistrar($this))->locale($locales);
        };
    }

    public function localeGroup(): Closure
    {
        return function (array $attributes, $routes) {
            $attributes['locale'] = collect($attributes['locale'] ?? config('localized-routes.locales'))->mapWithKeys(
                fn ($value, $key) => is_int($key)
                    ? [$value => null]
                    : [$key => $value]
            )->all();

            $this->updateGroupStack($attributes);

            $this->loadRoutes($routes);

            array_pop($this->groupStack);
        };
    }

    public function getByKey(): Closure
    {
        return function (string $key): ?Route {
            $collection = $this->getRoutes();

            $getByKey = Closure::bind(function (string $key): ?Route {
                $attributes = null;
                foreach ($this->attributes as $route) {
                    if (($route['action']['key'] ?? null) === $key) {
                        $attributes = $route;

                        break;
                    }
                }

                return $attributes ? $this->newRoute($attributes) : null;
            }, $collection, CompiledRouteCollection::class);

            if ($collection instanceof RouteCollection) {
                $getByKey = Closure::bind(function (string $key): ?Route {
                    foreach (array_keys($this->routes) as $method) {
                        foreach ($this->routes[$method] ?? [] as $route) {
                            if ($route->getAction('key') === $key) {
                                return $route;
                            }
                        }
                    }

                    return null;
                }, $collection, RouteCollection::class);
            }

            return $getByKey($key);
        };
    }
}
