<?php

namespace richarddobron\LocalizedRoutes;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use ReflectionException;
use richarddobron\LocalizedRoutes\Mixin\LocalizedRoute;
use richarddobron\LocalizedRoutes\Mixin\LocalizedRouter;
use richarddobron\LocalizedRoutes\Routing\LocalizedUrlGenerator;

class LocalizedRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/localized-routes.php' => config_path('localized-routes.php')], 'config');

        $this->app->booted(fn () => app(LocalizedRouteProvider::class)->registerLocalizedRoutes());
    }

    /**
     * @throws ReflectionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/localized-routes.php', 'localized-routes');

        $this->app->singleton(LocalizedRouteProvider::class);

        $this->app->singleton(LocalizedUrlGenerator::class, function (Application $app) {
            $routes = $app['router']->getRoutes();

            $this->app->instance('routes', $routes);

            $url = new LocalizedUrlGenerator(
                $routes, $app->rebinding('request', function ($app, $request) {
                $app['url']->setRequest($request);
            }));

            $url->setSessionResolver(function () {
                return $this->app['session'];
            });

            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });

        $this->app->alias(LocalizedUrlGenerator::class, 'url');

        Router::mixin(new LocalizedRouter());
        Route::mixin(new LocalizedRoute());
    }

    protected function requestRebinder(): Closure
    {
        return function ($app, $request) {
            $app['url']->setRequest($request);
        };
    }
}
