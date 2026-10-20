<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zarinpal Merchant ID
    |--------------------------------------------------------------------------
    |
    | This is the merchant ID provided to you by Zarinpal. It is required
    | for all payment requests and verifications. It is highly recommended
    | to store this value in your .env file for security.
    |
    */
    'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Zarinpal can process payments in either Iranian Rials (IRR) or
    | Tomans (IRT). Specify which currency you are sending amounts in.
    | The helper functions will automatically handle the conversion if
    | you use Tomans (dividing by 10 for the API call).
    |
    | Supported: "IRR", "IRT"
    |
    */
    'currency' => env('ZARINPAL_CURRENCY', 'IRR'),

    /*
    |--------------------------------------------------------------------------
    | Default Description
    |--------------------------------------------------------------------------
    |
    | A default description for payments if one is not provided.
    |
    */
    'description' => 'Online Payment',
];