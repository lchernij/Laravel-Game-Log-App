<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
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
        $response = $this->get('/admin/users/create');

        $response->assertStatus(200);
    }

    public function test_form_validations(): void
    {
        $response = $this->post('/admin/users/create/save', []);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail é obrigatório.',
            ]);;
    }

    public function test_create(): void
    {
        $response = $this->post('/admin/users/create/save', [
            'user' => [
                'name' => 'Foo bar',
                'email' => 'foo@bar.com',
                'password' => bcrypt('password'),
            ]
        ]);

        $response->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi salvo.',
                'toast_notification.level' => 'info'
            ]);

        $user = User::where('email', 'foo@bar.com')->first();
        $this->assertEquals('Foo bar', $user->name);
        $this->assertNotNull($user->password);
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
    }
}
