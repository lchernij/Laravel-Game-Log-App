<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class MainTest extends TestCase
{
    private User $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_open(): void
    {
        $response = $this->get('/admin/main');

        $response->assertStatus(200);
    }
}
