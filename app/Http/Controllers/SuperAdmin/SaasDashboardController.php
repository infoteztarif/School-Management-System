<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaasDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function __invoke(Request $request)
    {
        $now = now();
        $validated = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from']);
        $from = \Carbon\Carbon::parse($validated['from'] ?? $now->copy()->startOfMonth())->startOfDay();
        $to = \Carbon\Carbon::parse($validated['to'] ?? $now)->endOfDay();
        abort_if($from->diffInDays($to) > 366, 422, 'Date range cannot exceed 366 days.');
        $metrics = [
            'schools' => [
                'total' => School::whereBetween('created_at', [$from, $to])->count(),
                'active' => School::where('status', 'active')->count(),
                'suspended' => School::where('status', 'suspended')->count(),
            ],
            'users' => [
                'tenant_users' => User::whereNotNull('school_id')->whereBetween('created_at', [$from, $to])->count(),
                'school_admins' => User::whereNotNull('school_id')
                    ->whereHas('userType', function ($query) {
                        $query->where('title', 'admin');
                    })->count(),
            ],
            'subscriptions' => [
                'active' => SchoolSubscription::where('status', 'active')
                    ->where('starts_at', '<=', $now)
                    ->where('ends_at', '>=', $now)
                    ->count(),
                'expiring_in_30_days' => SchoolSubscription::where('status', 'active')
                    ->whereBetween('ends_at', [$now, $now->copy()->addDays(30)])
                    ->count(),
            ],
            'licensed_modules' => DB::table('school_module_licenses')
                ->where('enabled', true)
                ->where('starts_at', '<=', $now)
                ->where('ends_at', '>=', $now)
                ->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json(['data' => $metrics]);
        }

        $schools = School::with(['subscriptions' => function ($query) {
            $query->latest('ends_at')->limit(1);
        }])->orderBy('name')->paginate(10);

        return view('pages.super_admin.saas_dashboard', compact('metrics', 'schools', 'from', 'to'));
    }
}
