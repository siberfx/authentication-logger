<?php

namespace Siberfx\AuthenticationLogger\Commands;

use Illuminate\Console\Command;
use Siberfx\AuthenticationLogger\Models\AuthLogger;

class PurgeAuthenticationLogCommand extends Command
{
    public $signature = 'auth-logger:purge';

    public $description = 'Purge all authentication logs older than the configurable amount of days.';

    public function handle(): void
    {
        $this->comment('Clearing authentication log...');

        $deleted = AuthLogger::where('login_at', '<', now()->subDays(config('auth-logger.purge'))->format('Y-m-d H:i:s'))->delete();

        $this->info($deleted . ' authentication logs cleared.');
    }
}
