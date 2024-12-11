<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class LoginTest extends TestCase
{
    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    #[Group('17-adicionar-two-factor-authentication-2fa')]
    public function testLogin(): void
    {
        # Show login form
        $this->get('/login')
            ->assertStatus(200);

        # Form validations
        $this->post('/login', [
            'email' => '',
            'password' => ''
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'O campo email é obrigatório.',
                'password' => 'O campo senha é obrigatório.'
            ]);

        $this->post('/login', [
            'email' => '123',
            'password' => '123'
        ])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'Essas credenciais não foram encontradas em nossos registros.'
            ]);

        # User can login with success
        User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('123123')
        ]);

        $this->post('/login', [
            'email' => 'admin@admin.com',
            'password' => '123123'
        ])
            ->assertStatus(302)
            ->assertRedirect('/admin/main');

        # User two factory challenge too many requests
        for ($i = 0; $i < 5; $i++) {
            $this->post('/two-factor-challenge', []);
        }

        $this->post('/two-factor-challenge', [])
            ->assertStatus(429);
    }
}
