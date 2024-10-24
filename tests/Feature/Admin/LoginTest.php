<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_index(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_form_validations(): void
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => ''
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'O campo email é obrigatório.',
                'password' => 'O campo senha é obrigatório.'
            ]);

        $response = $this->post('/admin/login', [
            'email' => '123',
            'password' => '123'
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors([
                'email' => 'Os dados que você inseriu não correspondem aos nossos registros. Verifique e tente novamente.'
            ]);
    }

    public function test_user_can_login_with_success(): void
    {
        User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => bcrypt('123123')
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@admin.com',
            'password' => '123123'
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/admin/main');
    }
}
