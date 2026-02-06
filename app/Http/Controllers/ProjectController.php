<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->projects;
        return view('dashboard.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('dashboard.projects.create');
    }

    public function store(Request $request)
    {
        // Check subscription limits
        $user = auth()->user();
        if (!$user->canCreateProject()) {
            $limits = $user->getSubscriptionLimits();
            return back()->withErrors([
                'subscription' => "Your {$user->subscription_tier} plan allows up to {$limits['max_projects']} projects. Please upgrade to add more."
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_key' => 'required|string|max:255|unique:projects,project_key',
            'project_secret' => 'required|string|max:255',
        ]);

        $project = auth()->user()->projects()->create([
            'name' => $validated['name'],
            'project_key' => $validated['project_key'],
            'project_secret' => Hash::make($validated['project_secret']),
            'active' => true,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully!');
    }

    public function show(Project $project)
    {
        return view('dashboard.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('dashboard.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }
}
