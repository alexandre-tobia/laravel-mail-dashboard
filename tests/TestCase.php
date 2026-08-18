<?php

namespace Copain\LaravelMailDashboard\Tests;

use Copain\LaravelMailDashboard\LaravelMailDashboardServiceProvider;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected string $storageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        // The dashboard is open by default in the local environment only.
        $this->app['env'] = 'local';

        File::deleteDirectory($this->storageRoot);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->storageRoot);

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMailDashboardServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $this->storageRoot = sys_get_temp_dir().'/mail-dashboard-tests-'.getmypid();

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('session.driver', 'array');

        $app['config']->set('mail.default', 'array');

        $app['config']->set('filesystems.disks.mail-dashboard-testing', [
            'driver' => 'local',
            'root' => $this->storageRoot,
            'throw' => false,
        ]);

        $app['config']->set('mail-dashboard.enabled', true);
        $app['config']->set('mail-dashboard.storage.disk', 'mail-dashboard-testing');
        $app['config']->set('mail-dashboard.storage.path', 'emails');
    }
}
