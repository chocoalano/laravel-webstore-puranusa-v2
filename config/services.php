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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'env'        => env('MIDTRANS_ENV', 'sandbox'),
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'iris_key' => env('MIDTRANS_IRIS_KEY'),
    ],

    'lion_parcel' => [
        'base_url'  => env('LION_PARCEL_BASE_URL', 'https://api-stg-middleware.thelionparcel.com'),
        'auth'      => env('LION_PARCEL_AUTH', 'bGlvbnBhcmNlbDpsaW9ucGFyY2VsQDEyMw=='),
        'origin'    => env('LION_PARCEL_ORIGIN', 'KALIDERES, JAKARTA BARAT'),
        'commodity' => env('LION_PARCEL_COMMODITY', 'ABR036'),
    ],

    'qontak' => [
        'api_token' => env('QONTAK_API_TOKEN'),
        'channel_integration_id' => env('QONTAK_CHANNEL_INTEGRATION_ID'),
        'wd_approved_template_id' => env('QONTAK_WD_APPROVED_TEMPLATE_ID'),
        'wd_approved_header_image_url' => env('QONTAK_WD_APPROVED_HEADER_IMAGE_URL'),
        'broadcast_template_id' => env('QONTAK_BROADCAST_TEMPLATE_ID'),
        'broadcast_header_image_url' => env('QONTAK_BROADCAST_HEADER_IMAGE_URL'),
    ],

    'rajaongkir' => [
        'api_key_shipping' => env('RAJAONGKIR_API_KEY_SHIPPING'),
        'api_key_delivery' => env('RAJAONGKIR_API_KEY_DELIVERY'),
        'origin_district_id' => env('RAJAONGKIR_ORIGIN_DISTRICT_ID', 135),
    ],

];
