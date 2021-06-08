<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Unit;

use Exception;
use Thulisoft\MultiAuthForPassport\PassportMultiauth;
use Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models\Customer;
use Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models\User;
use Thulisoft\MultiAuthForPassport\Tests\TestCase;

class PassportMultiauthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'passport']);

        $this->artisan('migrate');

        $this->withFactories(__DIR__ . '/../Fixtures/factories');

        $this->setAuthConfigs();
    }

    public function testActingAsWithScopes()
    {
        $user = factory(User::class)->create();

        $scopes = ['check-scopes', 'test-packages'];
        PassportMultiauth::actingAs($user, $scopes);

        foreach ($scopes as $scope) {
            $this->assertTrue($user->tokenCan($scope));
        }
    }

    public function testActingAsWithUserThatNotUsesHasApiTokens()
    {
        $this->expectException(Exception::class);

        PassportMultiauth::actingAs(new Customer);
    }
}
