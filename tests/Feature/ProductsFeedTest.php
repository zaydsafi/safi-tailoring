<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_lists_active_products_with_merchant_attributes(): void
    {
        $category = Category::create(['name' => 'Suits', 'slug' => 'suits']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Page Coat',
            'slug' => 'page-coat',
            'type' => 'ready',
            'short_description' => 'Hand-stitched page coat.',
            'price' => 2500,
            'sale_price' => 2000,
            'stock' => 3,
            'track_stock' => true,
            'image' => 'products/page-coat.jpg',
            'is_active' => true,
        ]);

        // Hidden product: must never reach the feed.
        Product::create([
            'category_id' => $category->id,
            'name' => 'Hidden Scarf',
            'slug' => 'hidden-scarf',
            'type' => 'ready',
            'price' => 300,
            'stock' => 1,
            'is_active' => false,
        ]);

        Setting::set('currency_code', 'AFN');

        $response = $this->get(route('products.feed'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = $response->getContent();

        $this->assertStringContainsString('xmlns:g="http://base.google.com/ns/1.0"', $xml);
        $this->assertStringContainsString('<g:id>st-1</g:id>', $xml);
        $this->assertStringContainsString('<g:title>Page Coat</g:title>', $xml);
        $this->assertStringContainsString('<g:description>Hand-stitched page coat.</g:description>', $xml);
        $this->assertStringContainsString(htmlspecialchars(route('shop.show', ['locale' => 'en', 'slug' => 'page-coat']), ENT_XML1), $xml);
        $this->assertStringContainsString('<g:image_link>'.url('storage/products/page-coat.jpg').'</g:image_link>', $xml);
        $this->assertStringContainsString('<g:availability>in_stock</g:availability>', $xml);
        // The sale price wins over the regular one.
        $this->assertStringContainsString('<g:price>2000.00 AFN</g:price>', $xml);
        $this->assertStringNotContainsString('Hidden Scarf', $xml);
    }

    public function test_feed_marks_tracked_out_of_stock_products(): void
    {
        $category = Category::create(['name' => 'Suits', 'slug' => 'suits']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Empty Coat',
            'slug' => 'empty-coat',
            'type' => 'ready',
            'price' => 4000,
            'stock' => 0,
            'track_stock' => true,
            'is_active' => true,
        ]);

        $xml = $this->get(route('products.feed'))->getContent();

        $this->assertStringContainsString('<g:availability>out_of_stock</g:availability>', $xml);
        $this->assertStringContainsString('<g:price>4000.00 AFN</g:price>', $xml);
    }

    public function test_feed_works_for_guests(): void
    {
        $category = Category::create(['name' => 'Suits', 'slug' => 'suits']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Guest Coat',
            'slug' => 'guest-coat',
            'type' => 'ready',
            'price' => 2500,
            'stock' => 2,
            'is_active' => true,
        ]);

        $this->get(route('products.feed'))->assertOk();
    }
}
