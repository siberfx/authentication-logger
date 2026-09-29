<?php

namespace Siberfx\AuthenticationLogger\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Siberfx\AuthenticationLogger\Traits\AuthenticationLoggable;

class User extends Authenticatable
{
    use AuthenticationLoggable;
    use Notifiable;

    protected $guarded = [];
}
