<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->ofType($request->type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->byUser($request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->betweenDates($request->start_date, $request->end_date);
        }

        $logs = $query->paginate(50);

        // Get available types for filter
        $types = ActivityLog::distinct()->pluck('type');

        return view('admin.activity-logs.index', compact('logs', 'types'));
    }

    /**
     * Show log details
     */
    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user', 'subject');

        return view('admin.activity-logs.show', compact('activityLog'));
    }

    /**
     * Clear old logs
     */
    public function clear(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1',
        ]);

        $count = ActivityLog::where('created_at', '<', now()->subDays($validated['days']))->delete();

        return redirect()
            ->route('admin.activity-logs.index')
            ->with('success', "Deleted {$count} activity logs older than {$validated['days']} days");
    }
}
