<?php

namespace Tests\Traits;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait SetsUpRolesAndPermissions
{
    /**
     * Create all roles and permissions needed for tests.
     * Call this in setUp() before creating users.
     */
    protected function setUpRolesAndPermissions(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-roles', 'create-roles', 'edit-roles', 'delete-roles',
            'view-permissions',
            'view-govs', 'create-govs', 'edit-govs', 'delete-govs',
            'view-assessees', 'create-assessees', 'edit-assessees', 'delete-assessees',
            'view-assessments', 'create-assessments', 'edit-assessments', 'delete-assessments',
            'delete-budget_reals',
            'delete-economy_indicators',
            'delete-budget_plans',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Admin role — all permissions
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // User role — limited permissions
        $user = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $user->syncPermissions(['view-assessments', 'edit-assessments']);
    }

    /**
     * Create and authenticate an Admin user.
     */
    protected function createAdmin(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('Admin');
        return $user;
    }

    /**
     * Create and authenticate a regular User.
     */
    protected function createUser(): \App\Models\User
    {
        $user = \App\Models\User::factory()->create();
        $user->assignRole('User');
        return $user;
    }
}
