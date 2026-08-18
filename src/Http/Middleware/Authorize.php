<?php

namespace Copain\LaravelMailDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class Authorize
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->authorized($request), 403);

        return $next($request);
    }

    /**
     * Mirrors Horizon's authorization convention: when the application
     * defines a "viewMailDashboard" gate it has full control, otherwise
     * the dashboard is only available in the local environment.
     */
    protected function authorized(Request $request): bool
    {
        if (Gate::has('viewMailDashboard')) {
            return Gate::forUser($request->user())->allows('viewMailDashboard');
        }

        return app()->environment('local');
    }
}
