<?php

declare(strict_types=1);

return [

    'provider' => env('SSO_PROVIDER'),
    'client'   => [
        'id'     => env('SSO_CLIENT_ID'),
        'secret' => env('SSO_CLIENT_SECRET'),
    ],
    'tenant' => env('SSO_TENANT'),

];
