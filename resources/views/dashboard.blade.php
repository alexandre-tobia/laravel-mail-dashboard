<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mail Dashboard</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📬</text></svg>">
    <link rel="stylesheet" href="{{ route('mail-dashboard.asset', ['asset' => 'app.css']) }}?v={{ $assetVersion ?? '' }}">
</head>
<body>
    <div id="mail-dashboard"></div>

    <script>
        window.__MAIL_DASHBOARD__ = {
            basePath: @json($basePath),
        };
    </script>
    <script type="module" src="{{ route('mail-dashboard.asset', ['asset' => 'app.js']) }}?v={{ $assetVersion ?? '' }}"></script>
</body>
</html>
