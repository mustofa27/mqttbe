<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Device;
use App\Models\Topic;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;

class MqttAclController extends Controller
{
    public function acl(Request $request)
    {
        Log::info('MQTT AUTH RAW', [
            'all' => $request->all(),
            'input' => $request->getContent(),
            'headers' => $request->headers->all(),
        ]);
        // Validasi minimal payload
        if (
            !$request->username ||
            !$request->clientid ||
            !$request->topic ||
            !$request->acc
        ) {
            return response('deny', 403);
        }

        /**
         * STEP 1 — Project
         */
        $project = Project::where('project_key', $request->username)
            ->where('active', true)
            ->first();

        if (!$project) {
            return response('deny', 403);
        }

        /**
         * STEP 2 — Device
         */
        $device = Device::where('project_id', $project->id)
            ->where('device_id', $request->clientid)
            ->where('active', true)
            ->first();

        if (!$device) {
            return response('deny', 403);
        }

        if($device->type === "dashboard") {
            // Dashboard selalu diizinkan
            return response('allow', 200);
        }

        /**
         * STEP 3 — Topic match
         */
        $topics = Topic::where('project_id', $project->id)->where('enabled', true)->get();

        foreach ($topics as $topic) {
            $pattern = str_replace(
                ['{project}', '{device_id}'],
                [$project->project_key, $device->device_id],
                $topic->template
            );

            if ($this->mqttTopicMatch($pattern, $request->topic)) {

                /**
                 * STEP 4 — Permission
                 */
                $permission = Permission::where('project_id', $project->id)
                    ->where('device_type', $device->type)
                    ->where('topic_code', $topic->code)
                    ->first();

                if (!$permission) {
                    return response('deny', 403);
                }

                /**
                 * STEP 5 — Access check
                 */
                if ($this->accessAllowed($permission->access, $request->acc)) {
                    return response('allow', 200);
                }

                return response('deny', 403);
            }
        }

        // Topic tidak cocok dengan schema manapun
        return response('deny', 403);
    }

    /**
     * MQTT topic matcher sederhana
     */
    private function mqttTopicMatch(string $pattern, string $topic): bool
    {
        // Tidak pakai wildcard liar dulu
        return $pattern === $topic;
    }

    /**
     * Mapping akses
     */
    private function accessAllowed(string $rule, int $request): bool
    {
        return match ($rule) {
            'read' => $request === 1,
            'write' => $request === 2,
            'readwrite' => in_array($request, [1, 2, 3]),
            'subscribe' => $request === 4,
            'readsubscribe' => in_array($request, [1, 4, 5]),
            'writesubscribe' => in_array($request, [2, 4, 6]),
            'readwritesubscribe' => in_array($request, [1, 2, 3, 4, 5, 6, 7]),
            default => false,
        };
    }
}
