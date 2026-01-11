<?php

use App\Http\Controllers\MqttAuthController;
use App\Http\Controllers\MqttAclController;

Route::post('/mqtt/auth', [MqttAuthController::class, 'auth']);
Route::post('/mqtt/acl', [MqttAclController::class, 'acl']);
