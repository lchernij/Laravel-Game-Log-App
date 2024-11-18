<?php

namespace Tests\Browser\Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    use DatabaseTruncation;
    
    private User $adminUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('orchid:admin admin admin@admin.com password');
        $this->adminUser = User::first();
    }

    public function testOpenFormAndValidateFields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/admin/profile')
                ->assertSee('Minha conta')
                ->assertSee('Atualize os detalhes da sua conta, como nome, endereço de e-mail e senha')
                ->assertSee('Informações do perfil')
                ->assertSee('Atualize as informações do perfil da sua conta e o endereço de e-mail.')
                ->assertSee('Nome')
                ->assertInputPresent('user[name]')
                ->assertSee('E-mail')
                ->assertInputPresent('user[email]')
                ->assertSee('Salvar')

                ->assertSee('Atualizar senha')
                ->assertSee('Certifique-se de que sua conta esteja usando uma senha longa e aleatória para permanecer segura.')
                ->assertSee('Senha atual')
                ->assertInputPresent('old_password')
                ->assertSee('Esta é a sua senha definida no momento.')
                ->assertSee('Nova senha')
                ->assertInputPresent('password')
                ->assertSee('Confirme a nova senha')
                ->assertInputPresent('password_confirmation')
                ->assertSee('Uma boa senha tem pelo menos 8 a 15 caracteres, incluindo um número e uma letra minúscula.')
                ->assertSee('Altere sua senha')
            ;

            $browser->type('user[name]', '')
                ->type('user[email]', '')
                ->press('Salvar')
                ->waitForText('Verifique os dados inseridos.')
            ;

            $browser->type('old_password', '')
                ->type('password', '')
                ->type('password_confirmation', '')
                ->press('Altere sua senha')
                ->waitForText('Verifique os dados inseridos.')
            ;
        });
    }

    public function testUserCanChangeProfile(): void
    {
        $user['name'] = 'Admin Atualizado';
        $user['email'] = 'admin2@admin2.com.br';

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($this->adminUser)
                ->visit('/admin/profile')
                ->type('user[name]', $user['name'])
                ->type('user[email]', $user['email'])
                ->press('Salvar')
                ->waitForText('Perfil atualizado.')
            ;
        });

        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }

    public function testUserCanChangePassword(): void
    {
        $password['old'] = 'password';
        $password['new'] = 'drowssap';
        $password['confirm'] = 'drowssap';

        $this->browse(function (Browser $browser) use ($password) {
            $browser->loginAs($this->adminUser)
                ->visit('/admin/profile/changePassword')
                ->type('old_password', $password['old'])
                ->type('password', $password['new'])
                ->type('password_confirmation', $password['confirm'])
                ->press('Salvar')
                ->waitForText('Perfil atualizado.')
            ;
        });
    }
}
