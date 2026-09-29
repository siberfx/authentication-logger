<?php

namespace Siberfx\AuthenticationLogger\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

/**
 * @extends Factory<AuthLogger>
 */
class AuthenticationLogFactory extends Factory
{
    protected $model = AuthLogger::class;

    public function definition(): array
    {
        return [
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'login_at' => now(),
            'login_successful' => true,
            'logout_at' => null,
            'cleared_by_user' => false,
            'location' => null,
        ];
    }

    public function failed(): static
    {
        return $this->state(['login_successful' => false]);
    }

    public function loggedOut(): static
    {
        return $this->state(['logout_at' => now()]);
    }
}
