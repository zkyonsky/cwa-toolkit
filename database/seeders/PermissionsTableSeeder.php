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
            // ['name' => 'view-users', 'guard_name' => 'web'],
            // ['name' => 'create-users', 'guard_name' => 'web'],
            // ['name' => 'edit-users', 'guard_name' => 'web'],
            // ['name' => 'delete-users', 'guard_name' => 'web'],
            // ['name' => 'view-roles', 'guard_name' => 'web'],
            // ['name' => 'create-roles', 'guard_name' => 'web'],
            // ['name' => 'edit-roles', 'guard_name' => 'web'],
            // ['name' => 'delete-roles', 'guard_name' => 'web'],
            // ['name' => 'view-permissions', 'guard_name' => 'web'],
            // ['name' => 'create-permissions', 'guard_name' => 'web'],
            // ['name' => 'edit-permissions', 'guard_name' => 'web'],
            // ['name' => 'delete-permissions', 'guard_name' => 'web'],
            // ['name' => 'view-govs', 'guard_name' => 'web'],
            // ['name' => 'create-govs', 'guard_name' => 'web'],
            // ['name' => 'edit-govs', 'guard_name' => 'web'],
            // ['name' => 'delete-govs', 'guard_name' => 'web'],
            // ['name' => 'view-assessments', 'guard_name' => 'web'],
            // ['name' => 'create-assessments', 'guard_name' => 'web'],
            // ['name' => 'edit-assessments', 'guard_name' => 'web'],
            // ['name' => 'delete-assessments', 'guard_name' => 'web'],
            // ['name' => 'view-assessees', 'guard_name' => 'web'],
            // ['name' => 'create-assessees', 'guard_name' => 'web'],
            // ['name' => 'edit-assessees', 'guard_name' => 'web'],
            // ['name' => 'delete-assessees', 'guard_name' => 'web'],
            ['name' => 'view-budget_plans', 'guard_name' => 'web'],
            ['name' => 'create-budget_plans', 'guard_name' => 'web'],
            ['name' => 'edit-budget_plans', 'guard_name' => 'web'],
            ['name' => 'delete-budget_plans', 'guard_name' => 'web'],
            ['name' => 'view-economy_indicators', 'guard_name' => 'web'],
            ['name' => 'create-economy_indicators', 'guard_name' => 'web'],
            ['name' => 'edit-economy_indicators', 'guard_name' => 'web'],
            ['name' => 'delete-economy_indicators', 'guard_name' => 'web'],
            ['name' => 'view-budget_reals', 'guard_name' => 'web'],
            ['name' => 'create-budget_reals', 'guard_name' => 'web'],
            ['name' => 'edit-budget_reals', 'guard_name' => 'web'],
            ['name' => 'delete-budget_reals', 'guard_name' => 'web'],
            ['name' => 'view-financial_indicators', 'guard_name' => 'web'],
            ['name' => 'create-financial_indicators', 'guard_name' => 'web'],
            ['name' => 'edit-financial_indicators', 'guard_name' => 'web'],
            ['name' => 'delete-financial_indicators', 'guard_name' => 'web'],
            ['name' => 'view-financings', 'guard_name' => 'web'],
            ['name' => 'create-financings', 'guard_name' => 'web'],
            ['name' => 'edit-financings', 'guard_name' => 'web'],
            ['name' => 'delete-financings', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
