<?php

namespace Spork\Core\Tests\Traits;

use CreateTagTables;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spork\Core\Tests\migrations\CreateUsersTable;
use Spork\Core\Tests\TestUser;

trait UseTestBenchDatabase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]
        );
        $app['config']->set('spork-core.models.user', TestUser::class);
        include_once __DIR__.'/../migrations/2014_10_12_000000_create_users_table.php';
        include_once __DIR__.'/../../vendor/spatie/laravel-tags/database/migrations/create_tag_tables.php.stub';

        (new CreateUsersTable)->up();
        (new CreateTagTables)->up();
    }
}
