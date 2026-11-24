<?php

namespace App\Http\Middleware\Custom;

use App\Models\SchoolSubscription;
use Closure;

class EnsureActiveSchoolSubscription
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->school_id) {
            return $next($request);
        }

        $active = SchoolSubscription::where('school_id', $user->school_id)
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->get()
            ->contains(function (SchoolSubscription $subscription) {
                return $subscription->isAccessibleAt();
            });

        abort_unless($active, 403, 'School subscription is inactive or expired.');

        return $next($request);
    }
}
