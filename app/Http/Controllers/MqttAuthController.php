<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MqttAuthController extends Controller
{
    public function auth(Request $request)
    {
        /**
         * Payload dari Mosquitto biasanya berisi:
         * username  -> project_key
         * password  -> project_secret (plaintext, verified via hash)
         * clientid  -> device_id
         */

        Log::info('MQTT AUTH RAW', [
            'all' => $request->all(),
            'input' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);


        if (!$request->username || !$request->password) {
            return response('deny', 403);
        }

        $project = Project::where('project_key', $request->username)->first();

        if (!$project) {
            return response('deny', 403);
        }

        if (!$project->active) {
            return response('deny', 403);
        }

        if (!Hash::check($request->password, $project->project_secret)) {
            return response('deny', 403);
        }

        // AUTH SUCCESS
        return response('allow', 200);
    }
}
