<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $limits = $request->plan_limits;

        $projects = Project::where('user_id', $user->id)
            ->paginate(15);

        return response()->json([
            'data' => $projects->items(),
            'pagination' => [
                'total' => $projects->total(),
                'per_page' => $projects->perPage(),
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
            ],
            'limits' => [
                'max_projects' => $limits['max_projects'],
                'current_projects' => Project::where('user_id', $user->id)->count(),
            ],
        ]);
    }

    public function show(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $project,
            'devices_count' => $project->devices()->count(),
            'topics_count' => $project->topics()->count(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $limits = $request->plan_limits;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $count = Project::where('user_id', $user->id)->count();

        if ($limits['max_projects'] !== -1 && $count >= $limits['max_projects']) {
            return response()->json([
                'error' => 'Project limit exceeded',
                'message' => "Your plan allows a maximum of {$limits['max_projects']} projects",
            ], 422);
        }

        $project = Project::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json(['data' => $project], 201);
    }

    public function update(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->update($validated);

        return response()->json(['data' => $project]);
    }

    public function destroy(Request $request, Project $project)
    {
        $user = $request->user();

        if ($project->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
