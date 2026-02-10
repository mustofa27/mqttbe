<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $projects = $user->projects;

        // Only show messages for projects the user owns
        $query = Message::whereIn('project_id', $projects->pluck('id'));

        // Filter by device, topic, date if provided
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }
        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }
        if ($request->filled('date')) {
            $query->whereDate('received_at', $request->date);
        }

        $messages = $query->orderByDesc('received_at')->paginate(25);

        return view('dashboard.messages.index', compact('messages', 'projects'));
    }
}
