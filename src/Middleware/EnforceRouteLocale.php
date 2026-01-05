<?php

namespace richarddobron\LocalizedRoutes\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceRouteLocale
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentLocale = app()->getLocale();
        $route = $request->route();

        if ($routeLocale = $route->getAction('locale')) {
            if (! $routeName = $route->getAction('default_route')) {
                $routeName = $route->getName();
                $routeLocale = config('localized-routes.route_locale');
            }

            if (! config('localized-routes.prefix') && $routeLocale !== $currentLocale) {
                $correctUrl = route($routeName, [
                    'locale' => $currentLocale,
                ]);

                if ($query = $request->getQueryString()) {
                    $correctUrl .= '?' . $query;
                }

                if ($request->fullUrl() !== $correctUrl) {
                    return redirect()->to($correctUrl, config('localized-routes.redirect_code'));
                }
            }

            app()->setLocale($routeLocale);

            $request->setLocale($routeLocale);
        }

        return $next($request);
    }
}
