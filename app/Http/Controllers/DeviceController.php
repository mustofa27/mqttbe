<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Project;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::whereIn('project_id', auth()->user()->projects->pluck('id'))->with('project')->get();
        return view('dashboard.devices.index', compact('devices'));
    }

    public function create()
    {
        $projects = auth()->user()->projects;
        return view('dashboard.devices.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'device_id' => 'required|string|max:255|unique:devices,device_id',
            'type' => 'required|string|max:255',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        // Check subscription limits
        $user = auth()->user();
        if (!$user->canAddDevice($project)) {
            $limits = $user->getSubscriptionLimits();
            return back()->withErrors([
                'subscription' => "Your {$user->subscription_tier} plan allows up to {$limits['max_devices_per_project']} devices per project. Please upgrade to add more."
            ]);
        }

        Device::create($validated + ['active' => true]);

        return redirect()->route('devices.index')->with('success', 'Device created successfully!');
    }

    public function show(Device $device)
    {
        return view('dashboard.devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        $projects = auth()->user()->projects;
        return view('dashboard.devices.edit', compact('device', 'projects'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'device_id' => 'required|string|max:255|unique:devices,device_id,' . $device->id,
            'type' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $device->update($validated);

        return redirect()->route('devices.show', $device)->with('success', 'Device updated successfully!');
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully!');
    }
}
