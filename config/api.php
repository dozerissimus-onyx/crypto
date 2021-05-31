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
    ]
];
