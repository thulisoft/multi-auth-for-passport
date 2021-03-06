<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Unit;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mockery;
use Thulisoft\MultiAuthForPassport\Http\Middleware\MultiAuthenticate;
use Thulisoft\MultiAuthForPassport\Provider;
use Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models\Company;
use Thulisoft\MultiAuthForPassport\Tests\TestCase;
use Zend\Diactoros\ServerRequest;

class MultiAuthenticateMiddlewareTest extends TestCase
{
    protected $auth;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'passport']);

        $this->artisan('migrate');

        $this->withFactories(__DIR__ . '/../Fixtures/factories');

        $this->setAuthConfigs();

        $this->auth = $this->app['auth'];
    }

    public function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testTryAuthWithoutGuards()
    {
        $this->expectException(AuthenticationException::class);

        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');

        $repository = Mockery::mock('Thulisoft\MultiAuthForPassport\ProviderRepository');

        $request = $this->createRequest();

        $middleware = new MultiAuthenticate($resourceServer, $repository, $this->auth);
        $middleware->handle($request, function () {
            return 'response';
        });
    }

    public function testTryAuthWithoutAccessTokenId()
    {
        $this->expectException(AuthenticationException::class);

        $psr = (new ServerRequest())->withAttribute('oauth_access_token_id', null);

        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr);

        $repository = Mockery::mock('Thulisoft\MultiAuthForPassport\ProviderRepository');

        $request = $this->createRequest();

        $middleware = new MultiAuthenticate($resourceServer, $repository, $this->auth);
        $middleware->handle($request, function () {
            return 'response';
        }, 'api', 'company');
    }

    public function testTryAuthWithNotExistentAccessToken()
    {
        $this->expectException(AuthenticationException::class);

        $psr = (new ServerRequest())->withAttribute('oauth_access_token_id', 1);

        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr);

        $repository = Mockery::mock('Thulisoft\MultiAuthForPassport\ProviderRepository');
        $repository->shouldReceive('findForToken')->andReturn(null);

        $request = $this->createRequest();

        $middleware = new MultiAuthenticate($resourceServer, $repository, $this->auth);
        $middleware->handle($request, function () {
            return 'response';
        }, 'api', 'company');
    }

    public function testTryAuthWithExistentAccessTokenAndExistentOnProviders()
    {
        $psr = (new ServerRequest())->withAttribute('oauth_access_token_id', 1);

        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr);

        $tokenProvider = new Provider;
        $tokenProvider->provider = 'companies';

        $repository = Mockery::mock('Thulisoft\MultiAuthForPassport\ProviderRepository');
        $repository->shouldReceive('findForToken')->andReturn($tokenProvider);

        $request = $this->createRequest();

        $guard = 'company';
        app('auth')->guard($guard)->setUser(factory(Company::class)->create());

        app('auth')->shouldUse($guard);

        $middleware = new MultiAuthenticate($resourceServer, $repository, $this->auth);
        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'api', 'company');

        $this->assertEquals('response', $response);
    }

    public function testTryAuthWithExistentAccessTokenAndNotExistentOnProviders()
    {
        $this->expectException(AuthenticationException::class);

        $psr = (new ServerRequest())->withAttribute('oauth_access_token_id', 1);

        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr);

        $tokenProvider = new Provider;
        $tokenProvider->provider = 'companies';

        $repository = Mockery::mock('Thulisoft\MultiAuthForPassport\ProviderRepository');
        $repository->shouldReceive('findForToken')->andReturn($tokenProvider);

        $request = $this->createRequest();

        $middleware = new MultiAuthenticate($resourceServer, $repository, $this->auth);
        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'api');
    }

    public function testTryAuthWithoutAuthorizationHeader()
    {
        $this->expectException(AuthenticationException::class);

        $resourceServer = $this->createMock('League\OAuth2\Server\ResourceServer');
        $resourceServer->method('validateAuthenticatedRequest')
            ->will($this->throwException(OAuthServerException::accessDenied('Missing "Authorization" header')));

        $repository = Mockery::mock('Thulisoft\MultiAuthForPassport\ProviderRepository');
        $repository->shouldReceive('findForToken')->andReturn(null);

        $request = Request::create('/');

        $middleware = new MultiAuthenticate($resourceServer, $repository, $this->auth);
        $middleware->handle($request, function () {
            return 'response';
        }, 'api', 'company');
    }

    /**
     * Create request instance to be used on MultiAuthenticate::handle() param.
     *
     * @param string $token
     * @return \Illuminate\Http\Request
     */
    protected function createRequest(string $token = null)
    {
        $token = $token ? $token : 'Bearer token';

        $request = Request::create('/');
        $request->headers->set('Authorization', $token);

        return $request;
    }
}
