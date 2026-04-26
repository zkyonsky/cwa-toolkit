<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'view-users', 'guard_name' => 'web'],
            ['name' => 'create-users', 'guard_name' => 'web'],
            ['name' => 'edit-users', 'guard_name' => 'web'],
            ['name' => 'delete-users', 'guard_name' => 'web'],
            ['name' => 'view-roles', 'guard_name' => 'web'],
            ['name' => 'create-roles', 'guard_name' => 'web'],
            ['name' => 'edit-roles', 'guard_name' => 'web'],
            ['name' => 'delete-roles', 'guard_name' => 'web'],
            ['name' => 'view-permissions', 'guard_name' => 'web'],
            ['name' => 'create-permissions', 'guard_name' => 'web'],
            ['name' => 'edit-permissions', 'guard_name' => 'web'],
            ['name' => 'delete-permissions', 'guard_name' => 'web'],
            ['name' => 'view-govs', 'guard_name' => 'web'],
            ['name' => 'create-govs', 'guard_name' => 'web'],
            ['name' => 'edit-govs', 'guard_name' => 'web'],
            ['name' => 'delete-govs', 'guard_name' => 'web'],
            ['name' => 'view-assessments', 'guard_name' => 'web'],
            ['name' => 'create-assessments', 'guard_name' => 'web'],
            ['name' => 'edit-assessments', 'guard_name' => 'web'],
            ['name' => 'delete-assessments', 'guard_name' => 'web'],
            ['name' => 'view-assessees', 'guard_name' => 'web'],
            ['name' => 'create-assessees', 'guard_name' => 'web'],
            ['name' => 'edit-assessees', 'guard_name' => 'web'],
            ['name' => 'delete-assessees', 'guard_name' => 'web'],
            ['name' => 'view-poverties', 'guard_name' => 'web'],
            ['name' => 'create-poverties', 'guard_name' => 'web'],
            ['name' => 'edit-poverties', 'guard_name' => 'web'],
            ['name' => 'delete-poverties', 'guard_name' => 'web'],
            ['name' => 'view-unemployments', 'guard_name' => 'web'],
            ['name' => 'create-unemployments', 'guard_name' => 'web'],
            ['name' => 'edit-unemployments', 'guard_name' => 'web'],
            ['name' => 'delete-unemployments', 'guard_name' => 'web'],
            ['name' => 'view-budget_reals', 'guard_name' => 'web'],
            ['name' => 'create-budget_reals', 'guard_name' => 'web'],
            ['name' => 'edit-budget_reals', 'guard_name' => 'web'],
            ['name' => 'delete-budget_reals', 'guard_name' => 'web'],
            ['name' => 'view-fiscal_capacities', 'guard_name' => 'web'],
            ['name' => 'create-fiscal_capacities', 'guard_name' => 'web'],
            ['name' => 'edit-fiscal_capacities', 'guard_name' => 'web'],
            ['name' => 'delete-fiscal_capacities', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
