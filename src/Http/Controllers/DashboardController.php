<?php

namespace Copain\LaravelMailDashboard\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        $distPath = dirname(__DIR__, 3).'/resources/dist';

        return view('mail-dashboard::dashboard', [
            'basePath' => '/'.trim((string) config('mail-dashboard.path', 'mail-dashboard'), '/'),
            'assetVersion' => is_file($distPath.'/app.js') ? (string) filemtime($distPath.'/app.js') : '0',
        ]);
    }
}
