<?php

namespace Tests\Browser\Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
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

    #[Group('Orchid')]
    #[Group('10-adicionar-testes-de-frontend-com-laravel-dusk')]
    public function testOpenFormAndValidateFields(): void
    {
        $this->browse(function (Browser $browser) {
            # Form inputs
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

            # Validate empty fields
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

            # Change user name and email
            $browser->visit('/admin/profile')
                ->type('user[name]', 'Admin Atualizado')
                ->type('user[email]', 'admin2@admin2.com.br')
                ->press('Salvar')
                ->waitForText('Perfil atualizado.')
            ;

            $this->assertDatabaseHas('users', [
                'name' => 'Admin Atualizado',
                'email' => 'admin2@admin2.com.br',
            ]);

            # Change password
            $browser->loginAs($this->adminUser)
                ->visit('/admin/profile/changePassword')
                ->type('old_password', 'password')
                ->type('password', 'drowssap')
                ->type('password_confirmation', 'drowssap')
                ->press('Salvar')
                ->waitForText('Perfil atualizado.')
            ;
        });
    }
}
