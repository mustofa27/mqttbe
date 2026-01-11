<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Project;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::whereIn('project_id', auth()->user()->projects->pluck('id'))->with('project')->get();
        return view('dashboard.permissions.index', compact('permissions'));
    }

    public function create()
    {
        $projects = auth()->user()->projects;
        $topics = auth()->user()->projects()->with('topics')->get()->flatMap(function ($project) {
            return $project->topics->map(function ($topic) use ($project) {
                return [
                    'code' => $topic->code,
                    'project_id' => $topic->project_id,
                    'project_name' => $project->name,
                ];
            });
        });
        return view('dashboard.permissions.create', compact('projects', 'topics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'device_type' => 'required|string|max:255',
            'topic_code' => 'required|string|max:255',
            'access' => 'required|in:read,write,readwrite',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        Permission::create($validated);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully!');
    }

    public function show(Permission $permission)
    {
        return view('dashboard.permissions.show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        $projects = auth()->user()->projects;
        return view('dashboard.permissions.edit', compact('permission', 'projects'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'device_type' => 'required|string|max:255',
            'topic_code' => 'required|string|max:255',
            'access' => 'required|in:read,write,readwrite',
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.show', $permission)->with('success', 'Permission updated successfully!');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully!');
    }
}
