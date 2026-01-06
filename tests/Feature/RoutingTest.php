<?php

namespace richarddobron\LocalizedRoutes\Tests\Feature;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\Translator;
use richarddobron\LocalizedRoutes\Contracts\LocalizedRoute;
use richarddobron\LocalizedRoutes\Facades\LocalizedRoute as LocalizedRouteFacade;
use richarddobron\LocalizedRoutes\LocalizedRouteProvider;
use richarddobron\LocalizedRoutes\Tests\Support\Controller;
use richarddobron\LocalizedRoutes\Tests\TestCase;

class RoutingTest extends TestCase
{
    public function test_generates_localized_routes(): void
    {
        app(Translator::class)->addJsonPath(__DIR__ . '/../Support/lang');

        config(['localized-routes.locales' => ['es', 'de']]);

        Route::localeGroup([], function () {
            Route::get('/example', function () {
                return 'Hello, World!';
            });
        });

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        foreach (['/example', '/es/ejemplo', '/de/beispiel'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_detects_and_sets_the_route_locale(): void
    {
        app(Translator::class)->addJsonPath(__DIR__ . '/../Support/lang');

        Route::get('/example', function () {
            return 'Hello, World!';
        })
            ->locale(['es', 'de'])
            ->name('example');

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        foreach (['en' => '/example', 'es' => '/es/ejemplo', 'de' => '/de/beispiel'] as $locale => $url) {
            $this->get($url)->assertOk();
            $this->assertSame($locale, app()->getLocale());
        }
    }

    public function test_generates_localized_routes_without_prefix(): void
    {
        app(Translator::class)->addJsonPath(__DIR__ . '/../Support/lang');

        config(['localized-routes.prefix' => false]);

        Route::get('/example', function () {
            return 'Hello, World!';
        })
            ->locale(['es'])
            ->name('example');

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        app()->setLocale('es');
        $this->get('/ejemplo')->assertOk();

        app()->setLocale('en');
        $this->get('/ejemplo')
            ->assertStatus(302)
            ->assertRedirect('/example');

        config(['localized-routes.redirect_code' => 301]);

        $this->get('/ejemplo')
            ->assertStatus(301)
            ->assertRedirect('/example');
    }

    public function test_generates_localized_uri_via_helpers(): void
    {
        app(Translator::class)->addJsonPath(__DIR__ . '/../Support/lang');

        Route::get('/example', Controller::class)
            ->name('example')
            ->locale(['es']);

        $this->assertSame(
            'http://localhost/example',
            route('example')
        );

        $this->assertSame(
            'http://localhost/example',
            action(Controller::class)
        );

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        $this->assertSame(
            'http://localhost/es/ejemplo',
            route('example', ['locale' => 'es'])
        );

        $this->assertSame(
            'http://localhost/es/ejemplo',
            action(Controller::class, ['locale' => 'es'])
        );

        config(['localized-routes.route_key' => 'l']);

        $this->assertSame(
            'http://localhost/example?locale=es',
            route('example', ['locale' => 'es'])
        );

        $this->assertSame(
            'http://localhost/example?locale=es',
            action(Controller::class, ['locale' => 'es'])
        );

        $this->assertSame(
            'http://localhost/es/ejemplo',
            route('example', ['l' => 'es'])
        );

        $this->assertSame(
            'http://localhost/es/ejemplo',
            action(Controller::class, ['l' => 'es'])
        );
    }

    public function test_non_localized_routes_generate_with_locale_parameter(): void
    {
        Route::get('/home', Controller::class)
            ->name('home');

        $this->assertSame(
            'http://localhost/home?locale=es',
            route('home', ['locale' => 'es'])
        );

        $this->assertSame(
            'http://localhost/home?locale=es',
            action(Controller::class, ['locale' => 'es'])
        );
    }

    public function test_generates_localized_domains(): void
    {
        app(Translator::class)->addJsonPath(__DIR__ . '/../Support/lang');

        Route::locale(['es', 'de'])
            ->domain('example.com')
            ->group(function () {
                Route::get('/example', function () {
                    return 'Hello, World!';
                });
            });

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        $this->get('http://es.example.com/es/ejemplo')->assertOk();
        $this->get('http://example.com/de/beispiel')->assertOk();
    }

    /**
     * @dataProvider routeMatchingProvider
     */
    public function test_matches_route_name_against_canonical_route(bool $withCachedRoutes): void
    {
        $matches = false;

        Route::get('/example', function () use (&$matches) {
            $matches = LocalizedRouteFacade::is('example');
        })
            ->name('example')
            ->locale(['es']);

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        if ($withCachedRoutes) {
            Route::setCompiledRoutes(Route::getRoutes()->compile());
        }

        $this->get('es/example')->assertOk();

        $this->assertTrue($matches);
    }

    public static function routeMatchingProvider(): array
    {
        return [
            'RouteCollection' => [false],
            'CompiledRouteCollection' => [true],
        ];
    }

    public function test_register_localized_routes_is_idempotent(): void
    {
        Route::get('/example', function () {
            return 'Hello, World!';
        })
            ->locale(['es', 'de'])
            ->name('example');

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        $count = app(Router::class)->getRoutes()->count();

        app(LocalizedRouteProvider::class)->registerLocalizedRoutes();

        $this->assertSame(
            $count,
            app(Router::class)->getRoutes()->count()
        );
    }

    public function test_localized_route_inherits_properties_from_canonical_route(): void
    {
        /** @var LocalizedRoute $canonical */
        $canonical = Route::get('/example/{id}', function () {
            return 'Hello, World!';
        })
            ->locale(['de' => 'beispiel/{id}'])
            ->where('id', '[0-9]+')
            ->defaults('id', 1);

        $localized = $canonical->translateRoute('de');

        $this->assertNotNull($localized);
        $this->assertSame('de/beispiel/{id}', $localized->uri());
        $this->assertSame(['id' => '[0-9]+'], $localized->wheres);
        $this->assertSame(['id' => 1], $localized->defaults);
    }
}
