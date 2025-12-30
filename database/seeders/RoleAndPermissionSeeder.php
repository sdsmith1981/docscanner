<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'documents.view']);
        Permission::firstOrCreate(['name' => 'documents.create']);
        Permission::firstOrCreate(['name' => 'documents.update']);
        Permission::firstOrCreate(['name' => 'documents.delete']);
        Permission::firstOrCreate(['name' => 'documents.process']);
        
        Permission::firstOrCreate(['name' => 'integrations.view']);
        Permission::firstOrCreate(['name' => 'integrations.manage']);
        
        Permission::firstOrCreate(['name' => 'users.view']);
        Permission::firstOrCreate(['name' => 'users.create']);
        Permission::firstOrCreate(['name' => 'users.update']);
        Permission::firstOrCreate(['name' => 'users.delete']);
        
        Permission::firstOrCreate(['name' => 'billing.view']);
        Permission::firstOrCreate(['name' => 'billing.manage']);
        
        Permission::firstOrCreate(['name' => 'settings.view']);
        Permission::firstOrCreate(['name' => 'settings.manage']);
        
        Role::firstOrCreate(['name' => 'super_admin'])->givePermissionTo(Permission::all());
        
        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo([
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.process',
            'integrations.view',
            'integrations.manage',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'billing.view',
            'billing.manage',
            'settings.view',
            'settings.manage',
        ]);
        
        Role::firstOrCreate(['name' => 'manager'])->givePermissionTo([
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
            'documents.process',
            'integrations.view',
            'integrations.manage',
            'users.view',
            'billing.view',
            'settings.view',
        ]);
        
        Role::firstOrCreate(['name' => 'user'])->givePermissionTo([
            'documents.view',
            'documents.create',
            'documents.update',
            'documents.delete',
        ]);
    }
}
