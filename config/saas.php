<?php

return [

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
