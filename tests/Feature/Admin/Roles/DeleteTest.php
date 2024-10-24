<?php

namespace Tests\Feature\Admin\Roles;

use App\Models\User;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    private User $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    public function test_exist_register_to_delete(): void
    {
        $response = $this->post('/admin/roles/1/edit/remove');

        $response->assertStatus(404);
    }

    public function test_delete(): void
    {
        $role = Role::create([
            'name' => 'Zé',
            'slug' => 'ze'
        ]);

        $uri = '/admin/roles/' . $role->id . '/edit/remove';
        $response = $this->post($uri);

        $response->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'A função foi excluída',
                'toast_notification.level' => 'info'
            ]);

        $role = Role::where('slug', 'ze')->first();
        $this->assertNull($role);
    }
}
