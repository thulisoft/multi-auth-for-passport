<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Thulisoft\MultiAuthForPassport\HasMultiAuthApiTokens;

class Admin extends Authenticatable
{
    protected $table = 'admins';

    use HasMultiAuthApiTokens;

    public function getAuthIdentifierName()
    {
        return 'id';
    }
}
