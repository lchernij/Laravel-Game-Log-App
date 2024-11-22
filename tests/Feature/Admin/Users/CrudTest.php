<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class CrudTest extends TestCase
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
    public function testBasicCrud(): void
    {
        # Index with one user
        $this->get('/admin/users')
            ->assertStatus(200)
            ->assertSee('Usuário')
            ->assertSee($this->admin->name);

        # Form create validations
        $this->post('/admin/users/create/save', [])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail é obrigatório.',
            ]);

        $this->post('/admin/users/create/save', [
            'user' => [
                'email' => $this->admin->email
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail já está sendo utilizado.',
            ]);

        # Create user
        $this->post('/admin/users/create/save', [
            'user' => [
                'name' => 'Foo bar',
                'email' => 'foo@bar.com',
                'password' => 'password',
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi salvo.',
                'toast_notification.level' => 'info'
            ]);

        $createdUser = User::where('email', 'foo@bar.com')->first();

        # Show created user
        $this->get('/admin/users/' . $createdUser->id . '/edit')
            ->assertStatus(200)
            ->assertSee('Foo bar');

        # Form edit validations
        $this->post('/admin/users/' . $createdUser->id . '/edit/save', [
            'user' => [
                'email' => $this->admin->email
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail já está sendo utilizado.',
            ]);

        # Edit user
        $this->post('/admin/users/' . $createdUser->id . '/edit/save', [
            'user' => [
                'name' => 'Foo bar editado',
                'email' => $createdUser->email
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi salvo.',
                'toast_notification.level' => 'info'
            ]);

        $createdUser->refresh();
        $this->assertEquals('Foo bar editado', $createdUser->name);

        # Remove user
        $this->post('/admin/users/' . $createdUser->id . '/edit/remove', [])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi excluído',
                'toast_notification.level' => 'info'
            ]);
    }

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testAsyncModal(): void
    {
        $user = User::factory()->create();

        # Show
        $this->post('/admin/async?user=' . $user->id, [
            "_screen" => "eyJpdiI6ImQ3RFlISy9ndFdoR3hGRGIxK3krMFE9PSIsInZhbHVlIjoieE5kZ1Z3MGZ5Nzk2SnhaWUdFYUh5d2swWFgydDNJdFI5QnlBb0RHTEFJOWZWTmxNY0JwcjVDeG1zNmJ1MFRQUCIsIm1hYyI6IjVjMGEzMzkzODhiZWJjNzljNzVjODcxMjViMjJiNjgzMThjYWVmNWU4N2QzOGRlZDc3OTYwNGUzMmUzYzE2YzgiLCJ0YWciOiIifQ==",
            "_call" => "loadUserOnOpenModal",
            "_template" => "editUserModal"
        ])
            ->assertStatus(200);

        # Form edit validations
        $this->post('/admin/users/saveUser?user=' . $user->id, [
            'user' => [
                'name' => 'Foo bar',
                'email' => $this->admin->email
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail já está sendo utilizado.',
            ]);

        # Edit user
        $this->post('/admin/users/saveUser?user=' . $user->id, [
            'user' => [
                'name' => 'Foo bar editado',
                'email' => 'foo@bar.com'
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi salvo.',
                'toast_notification.level' => 'info'
            ]);

        $user->refresh();
        $this->assertEquals('Foo bar editado', $user->name);
    }

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testDeleteFromIndex(): void
    {
        $user = User::factory()->create();

        # Route validation
        $this->post('/admin/users/remove?id=' . $user->id + 1)
            ->assertStatus(404);

        # Remove user
        $this->post('/admin/users/remove?id=' . $user->id)
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'O usuário foi excluído',
                'toast_notification.level' => 'info'
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testLoginAs(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/users/' . $user->id . '/edit/loginAs')
            ->assertStatus(302);

        $this->assertAuthenticatedAs($user);
    }
}
