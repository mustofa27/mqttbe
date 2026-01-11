<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Device;
use App\Models\Topic;
use App\Models\Permission;

class MqttAclController extends Controller
{
    public function acl(Request $request)
    {
        // Validasi minimal payload
        if (
            !$request->username ||
            !$request->clientid ||
            !$request->topic ||
            !$request->access
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

        /**
         * STEP 3 — Topic match
         */
        $topics = Topic::where('enabled', true)->get();

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
                if ($this->accessAllowed($permission->access, $request->access)) {
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
    private function accessAllowed(string $rule, string $request): bool
    {
        return match ($rule) {
            'read' => $request === 'read',
            'write' => $request === 'write',
            'readwrite' => in_array($request, ['read', 'write']),
            default => false,
        };
    }
}
