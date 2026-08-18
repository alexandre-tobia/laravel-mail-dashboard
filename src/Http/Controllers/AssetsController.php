<?php

namespace Copain\LaravelMailDashboard\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetsController
{
    private const ASSETS = [
        'app.js' => 'text/javascript; charset=utf-8',
        'app.css' => 'text/css; charset=utf-8',
    ];

    public function __invoke(string $asset): BinaryFileResponse
    {
        abort_unless(isset(self::ASSETS[$asset]), 404);

        $path = dirname(__DIR__, 3).'/resources/dist/'.$asset;

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => self::ASSETS[$asset],
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
