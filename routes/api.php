<?php

use App\Http\Controllers\MqttAuthController;

Route::post('/mqtt/auth', [MqttAuthController::class, 'auth']);
