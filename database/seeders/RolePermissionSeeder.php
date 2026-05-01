<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view-dashboard',

            'manage-users',
            'manage-settings',
            'manage-files',
            'manage-activity-logs',

            'manage-master-data',
            'manage-kkn',
            'manage-gelombang',
            'verify-pendaftaran',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superadmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'mahasiswa',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'pembimbing',
            'guard_name' => 'web',
        ]);

        $superadmin->syncPermissions(Permission::pluck('name')->all());
    }
}
