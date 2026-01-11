<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Show the dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        return view('dashboard.index', [
            'user' => $user,
            'projectsCount' => $user->projects()->count(),
            'devicesCount' => $user->projects()->withCount('devices')->get()->sum('devices_count'),
        ]);
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        return view('dashboard.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the authenticated user's account and related data.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($user) {
            // Delete permissions scoped to user's projects
            foreach ($user->projects as $project) {
                // permissions directly linked to project
                Permission::where('project_id', $project->id)->delete();

                // permissions linked by topic codes belonging to the project
                $topicCodes = $project->topics()->pluck('code')->toArray();
                if (!empty($topicCodes)) {
                    Permission::whereIn('topic_code', $topicCodes)->delete();
                }

                // delete devices and topics for the project
                $project->devices()->delete();
                $project->topics()->delete();

                // finally delete the project
                $project->delete();
            }

            // remove personal access tokens if table exists
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();
            }

            // delete the user
            $user->delete();
        });

        // Logout and invalidate session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }
}
