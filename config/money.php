<?php

return [

    'defaults' => [

        'currency' => env('MONEY_DEFAULTS_CURRENCY', 'USD'),

        'convert' => env('MONEY_DEFAULTS_CONVERT', false),

    ],

    'currencies' => [

        'USD' => [
            'name' => 'US Dollar',
            'code' => 840,
            'precision' => 2,
            'subunit' => 100,
            'symbol' => '$',
            'symbol_first' => true,
            'decimal_mark' => '.',
            'thousands_separator' => ',',
            'rate' => 1,
        ],

    ],
];
