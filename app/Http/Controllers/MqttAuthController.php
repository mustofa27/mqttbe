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
         * password  -> project_secret (plaintext)
         * clientid  -> device_id
         */
        $data = $request->all();
        if (empty($data)) {
            // If Laravel couldn't parse the request (e.g., invalid JSON or wrong Content-Type), try parsing as form data
            parse_str($request->getContent(), $data);
        }

        Log::info('MQTT AUTH RAW', [
            'all' => $data,
            'input' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);


        if (!$data['username'] ?? false || !$data['password'] ?? false) {
            return response('deny', 403);
        }

        $project = Project::where('project_key', $data['username'])->first();

        if (!$project) {
            return response('deny', 403);
        }

        if (!$project->active) {
            return response('deny', 403);
        }

        if (!Hash::check($data['password'], $project->project_secret)) {
            return response('deny', 403);
        }

        // AUTH SUCCESS
        return response('allow', 200);
    }
}
