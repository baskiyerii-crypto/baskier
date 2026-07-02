<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Turkiye API HTTPS doğrulaması
    |--------------------------------------------------------------------------
    |
    | Yerel ortamda (Windows) CA zinciri eksikse `false` yaparak HTTP ile
    | doldurma denemesi yapılabilir. Üretimde `true` bırakın.
    |
    */
    'verify_ssl' => env('TURKIYE_GEOGRAPHY_VERIFY_SSL', true),
];
