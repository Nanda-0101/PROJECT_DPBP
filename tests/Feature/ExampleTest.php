<?php

namespace Tests\Feature;

use Tests\CommerceTestCase;

class ExampleTest extends CommerceTestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertStatus(200);
    }
}
