<?php

namespace App\Support;

/**
 * The admin-panel sections that can be granted to a custom role.
 * These keys are seeded into the `permissions` table on migration and
 * power the checkboxes on the Roles & Permissions screen.
 */
class Permissions
{
    /**
     * key => default label
     */
    public const CATALOG = [
        'products' => 'Products & inventory',
        'categories' => 'Categories',
        'orders' => 'Orders & payments',
        'users' => 'Users & customers',
        'messages' => 'Messages',
        'translations' => 'Translations',
        'settings' => 'Shop settings',

        // Custom-order workflow: customer requests and staff conversion to real orders.
        'custom_orders' => 'Custom orders',
        'tailor_dashboard' => 'Tailor dashboard',
    ];

    /**
     * @return list<string> All permission keys that roles can hold.
     */
    public static function keys(): array
    {
        return array_keys(self::CATALOG);
    }

    /**
     * The two permissions that make up the tailor workspace. They get
     * their own section on the Roles & Permissions screen.
     */
    public const TAILOR_KEYS = ['custom_orders', 'tailor_dashboard'];

    /**
     * What each tailor permission unlocks — shown as copy on the Roles
     * screen so admins know exactly what they are granting.
     *
     * @return array<string, string>
     */
    public const TAILOR_NOTES = [
        'tailor_dashboard' => 'admin.roles.tailor_perm_dashboard_desc',
        'custom_orders' => 'admin.roles.tailor_perm_custom_orders_desc',
    ];

    /**
     * General admin sections (everything except the tailor workspace).
     *
     * @return array<string, string>
     */
    public static function general(): array
    {
        return array_diff_key(self::CATALOG, array_flip(self::TAILOR_KEYS));
    }

    /**
     * The tailor workspace section.
     *
     * @return array<string, string>
     */
    public static function tailor(): array
    {
        return array_intersect_key(self::CATALOG, array_flip(self::TAILOR_KEYS));
    }
}
