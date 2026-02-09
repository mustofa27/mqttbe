<?php

return [
    'host' => env('MQTT_HOST', 'localhost'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'client_id_prefix' => env('MQTT_CLIENT_ID_PREFIX', 'dashboard-subscriber'),
];
