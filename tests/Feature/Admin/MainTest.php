<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
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

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testOpenMainPage(): void
    {
        $this->get('/admin/main')
            ->assertStatus(200);
    }
}
