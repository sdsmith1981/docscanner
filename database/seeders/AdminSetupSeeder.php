<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSetupSeeder extends Seeder
{
    public function run(): void
    {
        // Create main tenant
        $tenant = Tenant::create([
            'id' => 'admin',
            'plan' => 'pro',
            'document_limit' => 1000,
        ]);

        // Create admin user
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@docscanner.test',
            'password' => Hash::make('password'),
            'tenant_id' => $tenant->id,
        ]);

        // Assign super admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $user->assignRole($superAdminRole);

        // Create domains for the tenant
        $tenant->domains()->create([
            'domain' => 'admin.docscanner.test',
        ]);

        $this->command->info( 'Admin tenant created with ID: ' . $tenant->id);
        $this->command->info( 'Admin user created: admin@docscanner.test');
        $this->command->info( 'Access at: http://admin.docscanner.test');
    }
}