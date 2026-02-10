<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Message;
use App\Models\Device;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    // GET /api/v1/filter/project/{project}/summary
    public function summary($projectId, Request $request)
    {
        $project = Project::findOrFail($projectId);

        // Total messages
        $totalMessages = Message::where('project_id', $project->id)->count();

        // Active devices (devices that have sent at least 1 message)
        $activeDevices = Message::where('project_id', $project->id)
            ->distinct('device_id')
            ->count('device_id');

        // Topics used
        $topicsUsed = Message::where('project_id', $project->id)
            ->distinct('topic')
            ->count('topic');

        // Average message size
        $avgMessageSize = Message::where('project_id', $project->id)
            ->avg(\DB::raw('LENGTH(payload)'));

        // QoS >= 1
        $qos1Count = Message::where('project_id', $project->id)
            ->where('qos', '>=', 1)
            ->count();

        // Retained messages
        $retainedMessages = Message::where('project_id', $project->id)
            ->where('retained', true)
            ->count();

        return response()->json([
            'total_messages' => $totalMessages,
            'active_devices' => $activeDevices,
            'topics_used' => $topicsUsed,
            'avg_message_size' => $avgMessageSize,
            'qos_1_or_higher' => $qos1Count,
            'retained_messages' => $retainedMessages,
        ]);
    }
}
