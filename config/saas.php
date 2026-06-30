<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Edition guard
    |--------------------------------------------------------------------------
    |
    | 'edition' is baked into the branch and must NOT be edited per environment:
    | it is 'saas' on the multi-tenant "saas" branch and 'standalone' on "main".
    | 'expected' comes from the deployment's .env (APP_EDITION). The
    | EnsureCorrectEdition middleware refuses to serve when they disagree, so a
    | client server can never accidentally run the SaaS build and vice-versa.
    |
    */
    'edition' => 'saas',
    'expected_edition' => env('APP_EDITION'),

    /*
    |--------------------------------------------------------------------------
    | E-mail verification
    |--------------------------------------------------------------------------
    |
    | When enabled, a newly registered société owner must confirm their e-mail
    | address before accessing the application. Kept OFF by default because the
    | outgoing mail server is not wired up yet — flip SAAS_EMAIL_VERIFICATION to
    | true in .env once SMTP is ready.
    |
    */
    'email_verification' => env('SAAS_EMAIL_VERIFICATION', false),

    /*
    |--------------------------------------------------------------------------
    | Open registration
    |--------------------------------------------------------------------------
    |
    | Allows anyone to create a new société from the public landing page.
    |
    */
    'registration_enabled' => env('SAAS_REGISTRATION_ENABLED', true),

];
