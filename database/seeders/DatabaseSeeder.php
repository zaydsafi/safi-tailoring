<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Keep the auth seeder: it seeds only the login username/password accounts.
        $this->call(UserSeeder::class);

        // Demo seeder calls are commented out — real data (settings, catalog,
        // translations) lives directly in the database, added via the admin
        // panel or one-off manual runs. Kept here for reference only.

        // $this->call([
        //     SettingSeeder::class,
        //     CatalogSeeder::class,
        //     TranslationsSeeder::class,
        // ]);
    }
}
