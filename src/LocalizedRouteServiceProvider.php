<?php

namespace richarddobron\LocalizedRoutes;

use Closure;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
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

        $this->app->booted(function () {
            return app(LocalizedRouteProvider::class)->registerLocalizedRoutes();
        });
    }

    /**
     * @throws ReflectionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/localized-routes.php', 'localized-routes');

        $this->app->singleton(LocalizedRouteProvider::class);

        $this->app->singleton('url', function (Application $app) {
            $routes = $app['router']->getRoutes();

            $app->instance('routes', $routes);

            return new LocalizedUrlGenerator(
                $routes,
                $app->rebinding(
                    'request',
                    $this->requestRebinder()
                ),
                $app['config']['app.asset_url']
            );
        });

        $this->app->extend('url', function (UrlGeneratorContract $url, Application $app) {
            $url->setSessionResolver(function () {
                return $this->app['session'] ?? null;
            });

            $url->setKeyResolver(function () {
                return $this->app->make('config')->get('app.key');
            });

            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });

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
