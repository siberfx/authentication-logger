<?php

namespace Siberfx\AuthenticationLogger\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Siberfx\AuthenticationLogger\AuthenticationLogger;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

/**
 * Add to any authenticatable Eloquent model whose authentication activity should be logged.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait AuthenticationLoggable
{
    /**
     * @return MorphMany<AuthLogger, $this>
     */
    public function authentications(): MorphMany
    {
        return $this->morphMany(AuthenticationLogger::model(), 'authenticatable')->latest('login_at');
    }

    /**
     * @return MorphOne<AuthLogger, $this>
     */
    public function latestAuthentication(): MorphOne
    {
        return $this->morphOne(AuthenticationLogger::model(), 'authenticatable')->latestOfMany('login_at');
    }

    /**
     * The channels the authentication notifications are delivered on.
     *
     * @return list<string>
     */
    public function notifyAuthenticationLogVia(): array
    {
        return ['mail'];
    }

    public function lastLoginAt(): ?Carbon
    {
        return $this->authentications()->first()?->login_at;
    }

    public function lastSuccessfulLoginAt(): ?Carbon
    {
        return $this->authentications()->successful()->first()?->login_at;
    }

    public function lastLoginIp(): ?string
    {
        return $this->authentications()->first()?->ip_address;
    }

    public function lastSuccessfulLoginIp(): ?string
    {
        return $this->authentications()->successful()->first()?->ip_address;
    }

    /**
     * The successful login before the current one.
     */
    public function previousLoginAt(): ?Carbon
    {
        return $this->authentications()->successful()->skip(1)->first()?->login_at;
    }

    /**
     * The IP address of the successful login before the current one.
     */
    public function previousLoginIp(): ?string
    {
        return $this->authentications()->successful()->skip(1)->first()?->ip_address;
    }
}
