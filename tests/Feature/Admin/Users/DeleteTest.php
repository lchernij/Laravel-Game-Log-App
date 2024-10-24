<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
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
        $response = $this->post('/admin/users/999999/edit/remove');

        $response->assertStatus(404);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();

        $uri = '/admin/users/' . $user->id . '/edit/remove';
        $response = $this->post($uri);

        $response->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi excluído',
                'toast_notification.level' => 'info'
            ]);

        $user = User::find($user->id);
        $this->assertNull($user);
    }
}
