<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CoreSeedersCommand extends Command
{
    protected $signature = 'core:seeders';

    protected $description = 'Executes all core seeders for central';

    public function handle(): int
    {
        $this->info('Running core seeders...');

        // Artisan::call('db:seed', ['--class' => RoleSeeder::class]);
        // Artisan::call('db:seed', ['--class' => PermissionSeeder::class]);

        $this->info('Core seeders executed successfully.');

        return self::SUCCESS;
    }
}
