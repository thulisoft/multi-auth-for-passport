<?php

namespace Thulisoft\MultiAuthForPassport\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Thulisoft\MultiAuthForPassport\HasMultiAuthApiTokens;

class Company extends Authenticatable
{
    protected $table = 'companies';

    use HasMultiAuthApiTokens;

    public function getAuthIdentifierName()
    {
        return 'id';
    }
}
