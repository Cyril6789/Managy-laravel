<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The public home is now the SaaS marketing landing, not a redirect to login.
     */
    public function test_guests_see_the_landing_page(): void
    {
        $this->get('/')->assertOk()->assertSee('Managy');
    }
}
