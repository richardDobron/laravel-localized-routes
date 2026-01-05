<?php

namespace richarddobron\LocalizedRoutes\Mixin;

use Closure;
use Illuminate\Routing\Route;
use richarddobron\LocalizedRoutes\Contracts\LocalizedRoute as Localized;

/**
 * @mixin Localized&Route
 */
class LocalizedRoute
{
    public function locale(): Closure
    {
        return function (?array $locales = null): Route|array {
            $locale = $this->action['locale'] ?? [];

            if (is_null($locales)) {
                return (array)$locale;
            }

            $this->action['locale'] = collect([$locale, $locales])
                ->flatMap(
                    fn ($set) => collect($set)->mapWithKeys(
                        fn ($value, $key) => is_int($key)
                            ? [$value => null]
                            : [$key => $value]
                    )
                )
                ->all();

            return $this;
        };
    }

    public function getKey(): Closure
    {
        return function (): string {
            return implode('|', $this->methods()) . $this->getDomain() . $this->uri();
        };
    }

    public function translateRoutes(): Closure
    {
        return function (): array {
            $locales = [];

            foreach (array_keys($this->locale()) as $locale) {
                $locales[$locale] = $this->translateRoute($locale);
            }

            return $locales;
        };
    }

    public function translateRoute(): Closure
    {
        return function (string $locale): ?Route {
            $locales = $this->locale();
            if (! array_key_exists($locale, $locales)) {
                return null;
            }
            $uri = $locales[$locale];

            $action = ['locale' => $locale, 'canonical' => $this->getKey()] + $this->action;

            if (isset($action['as'])) {
                $action['canonical_route'] = $action['as'];
                unset($action['as']);
            }

            unset($action['prefix']);

            if ($domain = $this->getDomain()) {
                $action['domain'] = trans()->hasForLocale("routes.$domain", $locale)
                    ? trans("routes.$domain", [], $locale)
                    : $domain;
            }

            if ($uri === null) {
                $uri = trans()->hasForLocale("routes.$this->uri", $locale)
                    ? trans("routes.$this->uri", [], $locale)
                    : $this->uri;
            }

            $route = new Route($this->methods(), $uri, $action);

            if (config('localized-routes.prefix')) {
                $route->prefix($locale);
            }

            return $route
                ->setDefaults($this->defaults)
                ->setContainer($this->container)
                ->setRouter($this->router)
                ->where($this->wheres);
        };
    }
}
