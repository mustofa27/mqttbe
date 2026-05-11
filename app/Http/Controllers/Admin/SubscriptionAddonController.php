<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionAddon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionAddonController extends Controller
{
    public function index()
    {
        $addons = SubscriptionAddon::orderBy('created_at', 'desc')->get();

        return view('admin.subscription-addons.index', compact('addons'));
    }

    public function create()
    {
        return view('admin.subscription-addons.create', [
            'unitTypeOptions' => SubscriptionAddon::unitTypeOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:subscription_addons,code',
            'name' => 'required|string|max:255',
            'unit_type' => ['required', Rule::in(SubscriptionAddon::unitTypeKeys())],
            'price' => 'required|numeric|min:0',
            'included_units' => 'required|integer|min:0',
            'is_recurring' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['active'] = $request->boolean('active', true);

        SubscriptionAddon::create($validated);

        return redirect()->route('admin.subscription-addons.index')
            ->with('success', 'Add-on created successfully.');
    }

    public function edit(SubscriptionAddon $subscriptionAddon)
    {
        return view('admin.subscription-addons.edit', [
            'addon' => $subscriptionAddon,
            'unitTypeOptions' => SubscriptionAddon::unitTypeOptions(),
        ]);
    }

    public function update(Request $request, SubscriptionAddon $subscriptionAddon)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:subscription_addons,code,' . $subscriptionAddon->id,
            'name' => 'required|string|max:255',
            'unit_type' => ['required', Rule::in(SubscriptionAddon::unitTypeKeys())],
            'price' => 'required|numeric|min:0',
            'included_units' => 'required|integer|min:0',
            'is_recurring' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['active'] = $request->boolean('active');

        $subscriptionAddon->update($validated);

        return redirect()->route('admin.subscription-addons.index')
            ->with('success', 'Add-on updated successfully.');
    }

    public function destroy(SubscriptionAddon $subscriptionAddon)
    {
        $subscriptionAddon->delete();

        return redirect()->route('admin.subscription-addons.index')
            ->with('success', 'Add-on deleted successfully.');
    }
}
