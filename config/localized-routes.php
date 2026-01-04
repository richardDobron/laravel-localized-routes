<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Prefix
    |--------------------------------------------------------------------------
    |
    | Prepends the locale code to translated routes (excluding the fallback locale).
    | e.g. /example and /de/beispiel.
    |
    | Prefixed URLs are always visible and served as-is; requests without a
    | locale prefix will be redirected to the current locale's route.
    |
    */

    'prefix' => true,

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | List of supported locales for localized routes.
    |
    */

    'locales' => [],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | Locale used for default routes.
    |
    */

    'route_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Redirect Code
    |--------------------------------------------------------------------------
    |
    | HTTP status code used for redirecting to the correct localized route.
    |
    */

    'redirect_code' => 302,

];
