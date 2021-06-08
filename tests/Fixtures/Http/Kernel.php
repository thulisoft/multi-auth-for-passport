<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Fixtures\Http;

use Orchestra\Testbench\Http\Kernel as HttpKernel;
use Orchestra\Testbench\Http\Middleware\RedirectIfAuthenticated;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Thulisoft\MultiAuthForPassport\Http\Middleware\MultiAuthenticate::class,
        'oauth.providers' => \Thulisoft\MultiAuthForPassport\Http\Middleware\AddCustomProvider::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
