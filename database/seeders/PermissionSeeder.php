<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // إعادة تعيين الـ Cache قبل البدء
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ===================================
        // صلاحيات لوحة التحكم (Admin Dashboard Permissions)
        // ===================================

        $permissions = [
            // --- إدارة المستخدمين ---
            'view users',
            'create users',
            'edit users',
            'delete users',
            'ban users',

            // --- إدارة الباحثين عن عمل ---
            'view candidates',
            'edit candidates',
            'approve candidate profiles',

            // --- إدارة الشركات ---
            'view companies',
            'edit companies',
            'approve companies',

            // --- إدارة المستندات والفيديوهات ---
            'view documents',
            'approve documents',
            'reject documents',
            'delete documents',

            // --- إدارة المهن والدول ---
            'manage professions',
            'manage countries',

            // --- إدارة الأدوار والصلاحيات ---
            'manage roles',
            'manage permissions',

            // --- الإحصائيات ---
            'view dashboard stats',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ===================================
        // أدوار لوحة التحكم (Admin Roles)
        // ===================================

        // --- Role: Admin ---
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'view users',
            'edit users',
            'view candidates',
            'edit candidates',
            'approve candidate profiles',
            'view companies',
            'edit companies',
            'approve companies',
            'view documents',
            'approve documents',
            'reject documents',
            'manage professions',
            'manage countries',
            'view dashboard stats',
        ]);

        // --- Role: Super Admin ---
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // ===================================
        // إنشاء حساب Super Admin الرئيسي
        // ===================================
        $superAdmin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'       => 'مدير النظام (Super Admin)',
                'phone'      => '+201000000000',
                'user_type'  => 'admin',
                'status'     => 'active',
                'password'   => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        $superAdmin->assignRole($superAdminRole);

        $this->command->info('✅ Admin Dashboard Roles & Permissions seeded successfully!');
        $this->command->info('👑 Super Admin Created: admin@example.com | Password: password');
    }
}
