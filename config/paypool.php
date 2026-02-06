<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paypool Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Paypool payment gateway integration
    |
    */

    'api_url' => env('PAYPOOL_API_URL', 'http://localhost'),
    
    'api_token' => env('PAYPOOL_API_TOKEN'),
    
    'app_name' => env('APP_NAME', 'ICMQTT'),
    
    // Default redirect URLs (can be overridden in admin panel)
    'success_redirect_url' => env('APP_URL') . '/subscription/payment/success',
    
    'failure_redirect_url' => env('APP_URL') . '/subscription/payment/failed',
    
    // Webhook URL for Paypool to send payment updates
    'webhook_url' => env('APP_URL') . '/api/paypool/webhook',
];
