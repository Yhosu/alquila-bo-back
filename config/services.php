<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ctlp' => [
        'database'  => env('CTLP_DATABASE', 'odootest'),
        'user'      => env('CTLP_USER', 'pruebawstdtix1'),
        'id'        => env('CTLP_ID', 12639),
        'front_url' => env('CTLP_FRONT_URL', 'https://ctlp.test.front.solunes.com/my-accounts')
        // https://ctlp.prod.libelula.bo/my-accounts
    ],

    'libelula' => [
        'appkey' => 'b77f7e4b-a9d2-d182-1e62-23237bf5251e'
    ]
];
