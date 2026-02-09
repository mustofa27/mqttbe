<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Models\Project;
use App\Services\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Display webhooks management page
     */
    public function index(Request $request)
    {
        $webhooks = Webhook::where('project_id', $request->query('project_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $eventTypes = Webhook::getEventTypes();

        return view('dashboard.webhooks.index', compact('webhooks', 'eventTypes'));
    }

    /**
     * Store a new webhook
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'url' => ['required', 'url', 'max:500'],
            'event_type' => ['required', 'in:' . implode(',', array_keys(Webhook::getEventTypes()))],
            'description' => ['nullable', 'string', 'max:500'],
            'headers' => ['nullable', 'json'],
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if ($project->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $webhook = Webhook::create($validated);

        return redirect()->back()->with('success', 'Webhook created successfully');
    }

    /**
     * Test a webhook
     */
    public function test(Request $request, Webhook $webhook)
    {
        if ($webhook->project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $success = $this->webhookService->test($webhook);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Test webhook sent successfully' : 'Failed to send test webhook',
        ]);
    }

    /**
     * Delete a webhook
     */
    public function destroy(Request $request, Webhook $webhook)
    {
        if ($webhook->project->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $webhook->delete();

        return redirect()->back()->with('success', 'Webhook deleted successfully');
    }

    /**
     * Toggle webhook active status
     */
    public function toggle(Request $request, Webhook $webhook)
    {
        if ($webhook->project->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $webhook->update(['active' => !$webhook->active]);

        return response()->json([
            'active' => $webhook->active,
            'message' => $webhook->active ? 'Webhook activated' : 'Webhook deactivated',
        ]);
    }
}
