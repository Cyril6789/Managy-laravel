<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Edition guard
    |--------------------------------------------------------------------------
    */
    'edition' => 'saas',
    'expected_edition' => env('APP_EDITION'),

    /*
    |--------------------------------------------------------------------------
    | Tenant domains
    |--------------------------------------------------------------------------
    |
    | The central platform is served from SAAS_DOMAIN while each society is
    | served from {slug}.SAAS_DOMAIN. One wildcard DNS record and one Traefik
    | HostRegexp router are enough for every current and future society.
    |
    */
    'domain' => env('SAAS_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),
    'scheme' => env('SAAS_SCHEME', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_SCHEME) ?: 'http'),
    'reserved_subdomains' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('SAAS_RESERVED_SUBDOMAINS', 'www,admin,api,mail')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Platform super-admin
    |--------------------------------------------------------------------------
    */
    'super_admin' => [
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@managy.fr'),
        'password' => env('SUPER_ADMIN_PASSWORD', 'password'),
        'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
    ],

    /*
    |--------------------------------------------------------------------------
    | E-mail verification
    |--------------------------------------------------------------------------
    */
    'email_verification' => env('SAAS_EMAIL_VERIFICATION', false),

    /*
    |--------------------------------------------------------------------------
    | Open registration
    |--------------------------------------------------------------------------
    */
    'registration_enabled' => env('SAAS_REGISTRATION_ENABLED', true),

];
