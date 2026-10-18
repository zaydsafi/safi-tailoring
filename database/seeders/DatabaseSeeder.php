<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Keep the auth seeder: it seeds only the login username/password accounts.
        $this->call(UserSeeder::class);

        // Demo seeders fill the shop with real starting data: site settings,
        // the product/category catalog, and all UI translations (en/ps/fa).
        $this->call([
            SettingSeeder::class,
            CatalogSeeder::class,
            TranslationsSeeder::class,
        ]);
    }
}
