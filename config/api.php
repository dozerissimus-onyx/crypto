<?php
return [
    'sumsub' => [
        'baseUri' => 'https://test-api.sumsub.com',
        'secret' => env('SUMSUB_SECRET_KEY'),
        'key' => env('SUMSUB_APP_TOKEN'),
//    'baseUri' => 'https://api.sumsub.com'
        'file_path' => '',
    ],

    'elliptic' => [
        'baseUri' => 'https://aml-api.elliptic.co',
        'secret' => env('ELLIPTIC_SECRET'),
        'key' => env('ELLIPTIC_KEY')
    ],

    'wyre' => [
        'baseUri' => 'https://api.testwyre.com',
//        'baseUri' => 'https://api.sendwyre.com',
        'secret' => env('WYRE_SECRET'),
        'key' => env('WYRE_KEY')
    ],

    'growsurf' => [
        'baseUri' => 'https://api.growsurf.com',
        'key' => env('GROWSURF_API_KEY'),
        'campaignId' => 'fr4nyx',
        'rewardId' => 'cfdug2'
    ],

    'enigmaSecurities' => [
//        'baseUri' => 'https://sandbox.rest-api.enigma-securities.io',
        'baseUri' => 'https://api.enigma-securities.io',
        'login' => env('ENIGMA_SECURITIES_LOGIN'),
        'password' => env('ENIGMA_SECURITIES_PASSWORD'),
    ],

    'clearJunction' => [
        'baseUri' => 'https://private-30ee36-clearjunctionrestapi.apiary-mock.com',
        'key' => env('CLEAR_JUNCTION_API_KEY')
    ],
];
