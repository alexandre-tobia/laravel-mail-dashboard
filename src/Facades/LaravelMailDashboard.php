<?php

namespace Copain\LaravelMailDashboard\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Copain\LaravelMailDashboard\LaravelMailDashboard
 */
class LaravelMailDashboard extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Copain\LaravelMailDashboard\LaravelMailDashboard::class;
    }
}
