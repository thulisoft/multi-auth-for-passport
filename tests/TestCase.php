<?php

namespace Thulisoft\MultiAuthForPassport\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Thulisoft\MultiAuthForPassport\Providers\MultiauthServiceProvider;
use Thulisoft\MultiAuthForPassport\Tests\Fixtures\Http\Kernel;
use Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models\Company;
use Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models\User;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__ . '/Fixtures/database/migrations'));
    }

    protected function getPackageProviders($app)
    {
        return [
            MultiauthServiceProvider::class,
            PassportServiceProvider::class,
            ConsoleServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'passport');
        $app['config']->set('database.connections.passport', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $app->useEnvironmentPath(__DIR__ . '/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
        parent::getEnvironmentSetUp($app);
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', Kernel::class);
    }

    /**
     * Setup auth configs.
     *
     * @return void
     */
    protected function setAuthConfigs()
    {
        // Set up default entity
        config(['auth.defaults.guard' => 'company']);
        config(['auth.guards.api.driver' => 'passport']);
        config(['auth.guards.api.provider' => 'users']);
        config(['auth.providers.users.model' => User::class]);

        // Set up company entity
        config(['auth.guards.company.driver' => 'passport']);
        config(['auth.guards.company.provider' => 'companies']);
        config(['auth.providers.companies.driver' => 'eloquent']);
        config(['auth.providers.companies.model' => Company::class]);
    }
}
