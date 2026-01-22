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
        $content = $request->getContent();
        $data = [];

        // Try to parse as JSON first
        $jsonData = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $data = $jsonData;
        } else {
            // If not JSON, parse as form data
            parse_str($content, $data);
        }

        Log::info('MQTT AUTH RAW', [
            'data' => $data,
            'input' => $content,
            'headers' => $request->headers->all(),
        ]);


        if (empty($data['username']) || empty($data['password'])) {
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
