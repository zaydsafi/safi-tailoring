<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_page_carries_baseline_security_headers(): void
    {
        $response = $this->get(route('home', ['locale' => 'en']));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_bing_verification_meta_renders_when_configured(): void
    {
        Setting::set('bing_site_verification', '1234567890ABCDEF');

        $response = $this->get(route('home', ['locale' => 'en']));

        $response->assertOk()
            ->assertSee('<meta name="msvalidate.01" content="1234567890ABCDEF">', false);
    }
}
