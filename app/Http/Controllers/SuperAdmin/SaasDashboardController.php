<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\User;
use Illuminate\Support\Facades\DB;

class SaasDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function __invoke()
    {
        $now = now();

        return response()->json([
            'data' => [
                'schools' => [
                    'total' => School::count(),
                    'active' => School::where('status', 'active')->count(),
                    'suspended' => School::where('status', 'suspended')->count(),
                ],
                'users' => [
                    'tenant_users' => User::whereNotNull('school_id')->count(),
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
            ],
        ]);
    }
}
