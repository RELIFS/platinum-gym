<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<string, list<string>>
     */
    private array $permissionsByRole = [
        'admin' => [
            'manage_members',
            'import_members',
            'manage_packages',
            'manage_classes',
            'manage_bookings',
            'verify_payments',
            'input_cash_payments',
            'scan_qr',
            'manage_trainers',
            'manage_products',
            'manage_content',
            'view_operational_reports',
            'export_operational_reports',
        ],
        'owner' => [
            'view_owner_dashboard',
            'view_financial_reports',
            'export_financial_reports',
        ],
        'member' => [
            'view_own_dashboard',
            'update_own_profile',
            'buy_membership',
            'book_class',
            'cancel_own_booking',
            'view_own_transactions',
            'upload_own_payment_proof',
            'download_own_invoice',
            'view_own_qr',
            'view_own_notifications',
            'use_member_ai_assistant',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissionsByRole as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            foreach ($permissions as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
