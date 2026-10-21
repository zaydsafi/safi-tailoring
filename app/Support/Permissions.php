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
}
