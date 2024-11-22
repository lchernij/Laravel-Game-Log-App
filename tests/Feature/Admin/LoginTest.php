<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class LoginTest extends TestCase
{
    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testLogin(): void
    {
        # Show login form
        $this->get('/admin/login')
            ->assertStatus(200);

        # Form validations
        $this->post('/admin/login', [
            'email' => '',
            'password' => ''
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'O campo email é obrigatório.',
                'password' => 'O campo senha é obrigatório.'
            ]);

        $this->post('/admin/login', [
            'email' => '123',
            'password' => '123'
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'Os dados que você inseriu não correspondem aos nossos registros. Verifique e tente novamente.'
            ]);

        # User can login with success
        User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('123123')
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@admin.com',
            'password' => '123123'
        ])
            ->assertStatus(302)
            ->assertRedirect('/admin/main');
    }
}
