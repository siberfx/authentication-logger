<?php

namespace Siberfx\AuthenticationLog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

class AuthenticationLogFactory extends Factory
{
    protected $model = AuthLogger::class;

    public function definition()
    {
        return [
        ];
    }
}
