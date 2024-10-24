<?php

namespace Workbench\App\Models;

use Illuminate\Foundation\Auth\User as AuthUser;

class User extends AuthUser
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
