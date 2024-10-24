<?php

namespace Tests\Feature\Admin\Roles;

use App\Models\User;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class CreateTest extends TestCase
{
    private User $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_open_form(): void
    {
        $response = $this->get('/admin/roles/create');

        $response->assertStatus(200);
    }

    public function test_form_validations(): void
    {
        $response = $this->post('/admin/roles/create/save', []);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'role.slug' => 'O campo slug é obrigatório.',
            ]);
    }

    public function test_create(): void
    {
        $response = $this->post('/admin/roles/create/save', [
            'role' => [
                'slug' => 'ze',
                'name' => 'Zé',
            ]
        ]);

        $response->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'A função foi salva',
                'toast_notification.level' => 'info'
            ]);

        $role = Role::where('slug', 'ze')->first();
        $this->assertEquals('Zé', $role->name);
        $this->assertNotNull($role->created_at);
        $this->assertNotNull($role->updated_at);
    }
}
