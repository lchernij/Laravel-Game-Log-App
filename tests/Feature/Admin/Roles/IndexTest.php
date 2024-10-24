<?php

namespace Tests\Feature\Admin\Roles;

use App\Models\User;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class IndexTest extends TestCase
{
    private User $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_index_empty(): void
    {
        $response = $this->get('/admin/roles');

        $response->assertStatus(200)
            ->assertSee('Gerenciar funções')
            ->assertSee('Nenhum registro encontrado');
    }

    public function test_index_with_one_register(): void
    {
        Role::create([
            'name' => 'Zé',
            'slug' => 'ze'
        ]);

        $response = $this->get('/admin/roles');

        $response->assertStatus(200)
            ->assertSee('Gerenciar funções')
            ->assertSee('Zé');
    }
}
