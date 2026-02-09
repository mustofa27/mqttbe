<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Project;
use App\Services\AlertService;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Display alerts management page
     */
    public function index(Request $request)
    {
        $alerts = Alert::where('project_id', $request->query('project_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $alertTypes = Alert::getAlertTypes();

        return view('dashboard.alerts.index', compact('alerts', 'alertTypes'));
    }

    /**
     * Store a new alert
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(Alert::getAlertTypes()))],
            'threshold' => ['nullable', 'integer'],
            'condition' => ['nullable', 'string'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['email'],
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if ($project->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        Alert::create($validated);

        return redirect()->back()->with('success', 'Alert created successfully');
    }

    /**
     * Update an alert
     */
    public function update(Request $request, Alert $alert)
    {
        if ($alert->project->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'threshold' => ['nullable', 'integer'],
            'condition' => ['nullable', 'string'],
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => ['email'],
            'active' => ['nullable', 'boolean'],
        ]);

        $alert->update($validated);

        return redirect()->back()->with('success', 'Alert updated successfully');
    }

    /**
     * Delete an alert
     */
    public function destroy(Request $request, Alert $alert)
    {
        if ($alert->project->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $alert->delete();

        return redirect()->back()->with('success', 'Alert deleted successfully');
    }

    /**
     * Toggle alert active status
     */
    public function toggle(Request $request, Alert $alert)
    {
        if ($alert->project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alert->update(['active' => !$alert->active]);

        return response()->json([
            'active' => $alert->active,
            'message' => $alert->active ? 'Alert activated' : 'Alert deactivated',
        ]);
    }

    /**
     * Manually trigger an alert for testing
     */
    public function test(Request $request, Alert $alert)
    {
        if ($alert->project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        dispatch(new \App\Jobs\SendAlertJob($alert, $alert->project));

        return response()->json([
            'success' => true,
            'message' => 'Test alert email queued',
        ]);
    }
}
