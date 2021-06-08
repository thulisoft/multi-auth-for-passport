<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Thulisoft\MultiAuthForPassport\Facades\ServerRequest;
use Symfony\Component\HttpFoundation\Request;

class ServerRequestFacadeTest extends TestCase
{
    public function testCreateRequest()
    {
        $symfonyRequest = Request::create('/');

        $psrRequest = ServerRequest::createRequest($symfonyRequest);

        $this->assertInstanceOf(ServerRequestInterface::class, $psrRequest);
    }
}
