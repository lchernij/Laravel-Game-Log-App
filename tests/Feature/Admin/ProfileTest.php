<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class ProfileTest extends TestCase
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
    public function testProfileForm(): void
    {
        # Show profile form
        $this->get('/admin/profile')
            ->assertStatus(200);

        # Form validations
        $this->post('/admin/profile/save')
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'user.name' => 'O campo nome é obrigatório.',
                'user.email' => 'O campo e-mail é obrigatório.'
            ]);

        $otherUser = User::factory()->create();
        $this->post('/admin/profile/save', [
            'user' => [
                'name' => $otherUser->name,
                'email' => $otherUser->email,
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'user.email' => 'O campo e-mail já está sendo utilizado.'
            ]);

        # Save profile
        $this->post('/admin/profile/save', [
            'user' => [
                'name' => 'Foo bar',
                'email' => $this->admin->email
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'Perfil atualizado.',
                'toast_notification.level' => 'info'
            ]);
    }

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testPasswordForm(): void
    {
        # Form validations
        $this->post('admin/profile/changePassword')
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'old_password' => 'O campo senha atual é obrigatório.',
                'password' => 'O campo senha é obrigatório.'
            ]);

        $this->post('admin/profile/changePassword', [
            'old_password' => 'password',
            'password' => 'password',
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'password' => 'O campo senha de confirmação não confere.',
                'password' => 'Os campos senha e senha atual devem ser diferentes.',
            ]);

        # Save password
        $this->post('admin/profile/changePassword', [
            'old_password' => 'password',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'Senha alterada.',
                'toast_notification.level' => 'info'
            ]);
    }
}
