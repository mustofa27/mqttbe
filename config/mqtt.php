<?php

return [
    'host' => env('MQTT_HOST', 'localhost'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME'),
    'password' => env('MQTT_PASSWORD'),
    'client_id_prefix' => env('MQTT_CLIENT_ID_PREFIX', 'dashboard-subscriber'),
    'listener' => [
        'max_processes_per_user' => (int) env('MQTT_LISTENER_MAX_PER_USER', 1),
        'start_lock_seconds' => (int) env('MQTT_LISTENER_START_LOCK_SECONDS', 10),
    ],
    'supervisor' => [
        'path' => env('SUPERVISORCTL_PATH', 'supervisorctl'),
        'program' => env('MQTT_SUPERVISOR_PROGRAM', 'icmqtt-subscriber'),
    ],
];
