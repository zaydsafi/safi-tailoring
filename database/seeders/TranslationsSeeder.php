<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationsSeeder extends Seeder
{
    /**
     * English values for these keys are edited in shop settings or through
     * admin overrides, so the seeder must never pin an English copy.
     */
    private const EN_FROM_RUNTIME = [
        'common.announcement',
        'footer.tagline',
        'home.hero_title',
        'home.hero_subtitle',
    ];

    public function run(): void
    {
        $en = $this->load('en');
        $ps = $this->load('ps');
        $fa = $this->load('fa');

        $keys = array_values(array_unique(array_merge(array_keys($en), array_keys($ps), array_keys($fa))));

        $created = 0;
        $updated = 0;

        foreach ($keys as $key) {
            $row = Translation::firstOrNew(['key' => $key]);

            if ($row->exists) {
                $updated++;
            } else {
                $row->en = in_array($key, self::EN_FROM_RUNTIME, true) ? null : ($en[$key] ?? null);
                $created++;
            }

            $row->group = str_contains($key, '.') ? explode('.', $key, 2)[0] : 'general';
            $row->ps = $ps[$key] ?? $row->ps;
            $row->fa = $fa[$key] ?? $row->fa;
            $row->save();
        }

        $catalog = $this->seedCatalogContent();

        Translation::flushCache();

        $this->command?->info("Translations: {$created} created, {$updated} updated, {$catalog} catalog entries translated.");
    }

    private function load(string $locale): array
    {
        $path = database_path("data/translations/{$locale}.php");

        return is_file($path) ? (array) require $path : [];
    }

    private function seedCatalogContent(): int
    {
        $fields = 0;

        foreach (['ps', 'fa'] as $locale) {
            $path = database_path("data/translations/content_{$locale}.php");

            if (! is_file($path)) {
                continue;
            }

            $data = (array) require $path;

            foreach ($data['categories'] ?? [] as $slug => $values) {
                $category = Category::where('slug', $slug)->first();

                if (! $category) {
                    continue;
                }

                $names = $category->getRawOriginal('name_translations');
                $descriptions = $category->getRawOriginal('description_translations');
                $names = $names ? json_decode($names, true) : [];
                $descriptions = $descriptions ? json_decode($descriptions, true) : [];

                if (filled($values['name'] ?? null)) {
                    $names[$locale] = $values['name'];
                }
                if (filled($values['description'] ?? null)) {
                    $descriptions[$locale] = $values['description'];
                }

                $category->update([
                    'name_translations' => $names,
                    'description_translations' => $descriptions,
                ]);

                $fields++;
            }

            foreach ($data['products'] ?? [] as $slug => $values) {
                $product = Product::where('slug', $slug)->first();

                if (! $product) {
                    continue;
                }

                $names = json_decode($product->getRawOriginal('name_translations') ?? 'null', true) ?: [];
                $shorts = json_decode($product->getRawOriginal('short_description_translations') ?? 'null', true) ?: [];
                $descriptions = json_decode($product->getRawOriginal('description_translations') ?? 'null', true) ?: [];

                if (filled($values['name'] ?? null)) {
                    $names[$locale] = $values['name'];
                }
                if (filled($values['short_description'] ?? null)) {
                    $shorts[$locale] = $values['short_description'];
                }
                if (filled($values['description'] ?? null)) {
                    $descriptions[$locale] = $values['description'];
                }

                $product->update([
                    'name_translations' => $names,
                    'short_description_translations' => $shorts,
                    'description_translations' => $descriptions,
                ]);

                $fields++;
            }
        }

        return $fields;
    }
}
