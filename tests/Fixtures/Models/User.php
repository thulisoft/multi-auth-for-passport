<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Thulisoft\MultiAuthForPassport\HasMultiAuthApiTokens;

class User extends Authenticatable
{
    protected $table = 'users';

    use HasMultiAuthApiTokens;

    public function getAuthIdentifierName()
    {
        return 'id';
    }
}
