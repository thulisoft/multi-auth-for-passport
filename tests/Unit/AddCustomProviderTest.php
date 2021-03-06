<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Unit;

use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mockery;
use Thulisoft\MultiAuthForPassport\Http\Middleware\AddCustomProvider;
use Thulisoft\MultiAuthForPassport\Tests\TestCase;

class AddCustomProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Config default provider
        config(['auth.guards.api.provider', 'users']);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfApiProviderOnAuthWasSetCorrectly()
    {
        // We'll push a fake testing provider in the auth config registered in the app..
        config(['auth.guards.testing.provider' => 'companies']);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')->andReturn('companies')->with('provider');

        $middleware = new AddCustomProvider();
        $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertEquals(config('auth.guards.api.provider'), 'companies');

        // Check if was correctly reset to default provider on `terminate()`
        $middleware->terminate();
        $this->assertEquals(config('auth.guards.api.provider'), 'users');
    }

    public function testPassNotExistentProvider()
    {
        $this->expectException(OAuthServerException::class);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')->andReturn('not_found')->with('provider');

        $middleware = new AddCustomProvider();
        $middleware->handle($request, function () {
            return 'response';
        });
    }

    public function testDoNotPassProviderToRequest()
    {
        $this->expectException(OAuthServerException::class);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')->andReturn(null)->with('provider');

        $middleware = new AddCustomProvider();
        $middleware->handle($request, function () {
            return 'response';
        });
    }
}
