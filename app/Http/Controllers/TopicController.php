<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::whereIn('project_id', auth()->user()->projects->pluck('id'))->with('project')->get();
        return view('dashboard.topics.index', compact('topics'));
    }

    public function create()
    {
        $projects = auth()->user()->projects;
        return view('dashboard.topics.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('topics', 'code')->where('project_id', $request->project_id),
            ],
            'enabled' => 'boolean',
        ]);

        // Auto-generate template: {project}/{device_id}/{code}
        $template = '{project}/{device_id}/' . $validated['code'];

        Topic::create($validated + ['template' => $template, 'enabled' => true]);

        return redirect()->route('topics.index')->with('success', 'Topic created successfully!');
    }

    public function show(Topic $topic)
    {
        return view('dashboard.topics.show', compact('topic'));
    }

    public function edit(Topic $topic)
    {
        return view('dashboard.topics.edit', compact('topic'));
    }

    public function update(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('topics', 'code')
                    ->where('project_id', $topic->project_id)
                    ->ignore($topic->id),
            ],
            'enabled' => 'boolean',
        ]);

        // Auto-generate template: {project}/{device_id}/{code}
        $template = '{project}/{device_id}/' . $validated['code'];

        $topic->update($validated + ['template' => $template]);

        return redirect()->route('topics.show', $topic)->with('success', 'Topic updated successfully!');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()->route('topics.index')->with('success', 'Topic deleted successfully!');
    }
}
