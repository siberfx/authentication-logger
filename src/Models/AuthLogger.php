<?php

namespace Siberfx\AuthenticationLogger\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuthLogger extends Model
{
    public $timestamps = false;

    protected $table = 'auth_logger';

    protected $fillable = [
        'ip_address',
        'user_agent',
        'login_at',
        'login_successful',
        'logout_at',
        'cleared_by_user',
        'location',
    ];

    protected $casts = [
        'cleared_by_user' => 'boolean',
        'location' => 'array',
        'login_successful' => 'boolean',
    ];

    protected $dates = [
        'login_at',
        'logout_at',
    ];

    public function getTable()
    {
        return config('auth-logger.table_name', parent::getTable());
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }
}
