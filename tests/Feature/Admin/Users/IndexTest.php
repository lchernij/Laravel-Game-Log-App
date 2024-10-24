<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
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

    public function test_index_with_one_register(): void
    {
        $response = $this->get('/admin/users');

        $response->assertStatus(200)
            ->assertSee('Usuário')
            ->assertSee($this->admin->name);;
    }

    public function test_save_validations(): void
    {
        $user = User::factory()->create();
        $uri = '/admin/users/saveUser?user=' . $user->id;

        $response = $this->post($uri, [
            'user' => [
                'name' => 'Foo bar',
                'email' => $this->admin->email
            ]
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail já está sendo utilizado.',
            ]);
    }

    public function test_save(): void
    {
        $user = User::factory()->create();
        $uri = '/admin/users/saveUser?user=' . $user->id;

        $response = $this->post($uri, [
            'user' => [
                'name' => 'Foo bar',
                'email' => 'foo@bar.com'
            ]
        ]);

        $response->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi salvo.',
                'toast_notification.level' => 'info'
            ]);

        $currentUser = User::where('email', 'foo@bar.com')->first();
        $this->assertEquals($user->id, $currentUser->id);
        $this->assertEquals('Foo bar', $currentUser->name);
    }

    public function test_delete_validations(): void
    {
        $uri = '/admin/users/remove?id=99999999';

        $response = $this->post($uri);

        $response->assertStatus(404);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $uri = '/admin/users/remove?id=' . $user->id;

        $response = $this->post($uri);

        $response->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi excluído',
                'toast_notification.level' => 'info'
            ]);

        $removedUser = User::find($user->id);
        $this->assertNull($removedUser);
    }
}
