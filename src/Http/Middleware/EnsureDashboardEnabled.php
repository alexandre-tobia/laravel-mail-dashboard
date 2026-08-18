<?php

namespace Copain\LaravelMailDashboard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) config('mail-dashboard.enabled'), 404);

        return $next($request);
    }
}
