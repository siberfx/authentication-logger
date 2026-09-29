<?php

namespace Siberfx\AuthenticationLogger\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Siberfx\AuthenticationLogger\Database\Factories\AuthenticationLogFactory;

/**
 * @property int $id
 * @property string $authenticatable_type
 * @property int|string $authenticatable_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property Carbon|null $login_at
 * @property bool $login_successful
 * @property Carbon|null $logout_at
 * @property bool $cleared_by_user
 * @property array<string, mixed>|null $location
 */
#[UseFactory(AuthenticationLogFactory::class)]
class AuthLogger extends Model
{
    /** @use HasFactory<AuthenticationLogFactory> */
    use HasFactory;
    use MassPrunable;

    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'login_at',
        'login_successful',
        'logout_at',
        'cleared_by_user',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'cleared_by_user' => 'boolean',
            'location' => 'array',
            'login_successful' => 'boolean',
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    public function getTable(): string
    {
        return config('auth-logger.table_name') ?? parent::getTable();
    }

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Logs older than the configured `purge` days are removed by `model:prune`.
     */
    public function prunable(): Builder
    {
        $days = config('auth-logger.purge');

        return $days === null
            ? static::query()->whereRaw('1 = 0')
            : static::query()->where('login_at', '<', now()->subDays((int) $days));
    }

    #[Scope]
    protected function successful(Builder $query): void
    {
        $query->where('login_successful', true);
    }

    #[Scope]
    protected function failed(Builder $query): void
    {
        $query->where('login_successful', false);
    }

    #[Scope]
    protected function fromDevice(Builder $query, ?string $ipAddress, ?string $userAgent): void
    {
        $query->where('ip_address', $ipAddress)->where('user_agent', $userAgent);
    }
}
