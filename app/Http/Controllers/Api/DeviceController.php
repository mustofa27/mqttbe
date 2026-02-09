<?php

namespace App\Http\Controllers\Api;

use App\Models\Device;
use App\Models\Project;
use Illuminate\Http\Request;

class DeviceController
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $user = $request->user();

        if (!$projectId) {
            return response()->json(['error' => 'project_id is required'], 400);
        }

        $project = Project::findOrFail($projectId);

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $devices = Device::where('project_id', $projectId)->paginate(15);

        return response()->json([
            'data' => $devices->items(),
            'pagination' => [
                'total' => $devices->total(),
                'per_page' => $devices->perPage(),
                'current_page' => $devices->currentPage(),
            ],
        ]);
    }

    public function show(Request $request, Device $device)
    {
        $user = $request->user();

        if ($device->project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $device]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $limits = $request->plan_limits;

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'device_id' => ['required', 'string', 'unique:devices'],
            'status' => ['nullable', 'in:online,offline,inactive'],
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deviceCount = Device::where('project_id', $project->id)->count();

        if ($limits['max_devices_per_project'] !== -1 && 
            $deviceCount >= $limits['max_devices_per_project']) {
            return response()->json([
                'error' => 'Device limit exceeded',
                'message' => "Your plan allows a maximum of {$limits['max_devices_per_project']} devices per project",
            ], 422);
        }

        $device = Device::create($validated);

        return response()->json(['data' => $device], 201);
    }

    public function update(Request $request, Device $device)
    {
        $user = $request->user();

        if ($device->project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'in:online,offline,inactive'],
        ]);

        $device->update($validated);

        return response()->json(['data' => $device]);
    }

    public function destroy(Request $request, Device $device)
    {
        $user = $request->user();

        if ($device->project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $device->delete();

        return response()->json(['message' => 'Device deleted successfully']);
    }
}
