<?php 

namespace Tests\Feature;

use Tests\TestCase;

class BookshelfTest extends TestCase
{
    public function test_homepage_returns_success()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
