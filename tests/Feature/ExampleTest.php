<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_redirects_to_a_locale_home(): void
    {
        $this->get('/')->assertRedirect('/en');
    }

    public function test_the_english_home_page_loads(): void
    {
        $this->get('/en')->assertOk();
    }

    public function test_the_pashto_home_page_loads(): void
    {
        $this->get('/ps')->assertOk();
    }

    public function test_the_farsi_home_page_loads(): void
    {
        $this->get('/fa')->assertOk();
    }
}
