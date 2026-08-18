<?php

namespace Copain\LaravelMailDashboard\Commands;

use Illuminate\Console\Command;

class LaravelMailDashboardCommand extends Command
{
    public $signature = 'laravel-mail-dashboard';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
