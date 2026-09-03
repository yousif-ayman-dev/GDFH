<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform Monetization & Commission Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines the platform's commercial settings, including default
    | commission rates. The rate is configurable and can be overridden via
    | environment variables or administrative settings.
    |
    */

    'commission_rate' => (float) env('PLATFORM_COMMISSION_RATE', 0.10),
];
