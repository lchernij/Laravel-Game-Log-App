<?php

namespace Tests\Browser\Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;

    #[Group('Orchid')]
    #[Group('10-adicionar-testes-de-frontend-com-laravel-dusk')]
    public function testLogin(): void
    {
        $this->browse(function (Browser $browser) {
            # Form inputs
            $browser->visit('/login')
                ->assertSee('Game Log App Test')
                ->assertSee('Entre na sua conta')
                ->assertSee('Endereço de e-mail')
                ->assertInputPresent('email')
                ->assertSee('Senha')
                ->assertInputPresent('password')
                ->assertSee('Lembrar-me')
                ->assertSee('Entrar')
            ;

            # Login with invalid data
            $browser->type('email', '1@1.com')
                ->type('password', '1@1.com')
                ->press('Entrar')
                ->waitFor('.form-control.is-invalid')
            ;

            # Login with valid data
            $this->artisan('orchid:admin admin admin@admin.com password');

            $browser->visit('/login')
                ->assertSee('Game Log App')
                ->type('email', 'admin@admin.com')
                ->type('password', 'password')
                ->press('Entrar')
                ->waitForLocation('/admin/main')
            ;
        });
    }


    #[Group('17-adicionar-two-factor-authentication-2fa')]
    public function testEnableTwoFactorAuthentication(): void
    {
        # Enable two factor authentication
        $this->artisan('orchid:admin admin admin@admin.com password');
        $adminUser = User::first();

        $this->browse(function (Browser $browser) use ($adminUser) {
            $browser->loginAs($adminUser)
                ->visit('/admin/profile')

                # Show two factor menu actions before enabled
                ->assertSee('Autenticação de dois fatores')
                ->press('Autenticação de dois fatores')

                # Show enable two factor authentication modal
                ->waitForText('Ativar autenticação de dois fatores')
                ->press('Ativar autenticação de dois fatores')

                # Show two factor authentication enabled modal
                ->waitForText('Quando a autenticação de dois fatores é ativada, você será solicitado a inserir um token seguro e aleatório durante a autenticação. Você pode recuperar esse token no aplicativo Google Authenticator do seu telefone.')
                ->press('Ativar autenticação de dois fatores')

                # Show two factor authentication QR code and recovery codes
                ->waitForText('Autenticação de dois fatores')
                ->waitForText('A autenticação de dois fatores está ativada. Escaneie o seguinte código QR usando o aplicativo de autenticação do seu telefone.')
                ->assertSee('Armazene esses códigos de recuperação em um gerenciador de senhas seguro. Eles podem ser usados para recuperar o acesso à sua conta se o dispositivo de autenticação de dois fatores for perdido.')
                ->press('Fechar')

                # Show two factor menu actions after enabled
                ->press('Autenticação de dois fatores')
                ->assertSee('Mostrar códigos de recuperação')
                ->assertSee('Regenerar códigos de recuperação')
                ->assertSee('Desativar autenticação de dois fatores')

                # Show recovery codes
                ->press('Mostrar códigos de recuperação')
                ->whenAvailable('.modal', function (Browser $modal) {
                    $modal->waitForText('Autenticação de dois fatores')
                        ->waitForText('A autenticação de dois fatores está ativada. Escaneie o seguinte código QR usando o aplicativo de autenticação do seu telefone.')
                        ->assertSee('Armazene esses códigos de recuperação em um gerenciador de senhas seguro. Eles podem ser usados para recuperar o acesso à sua conta se o dispositivo de autenticação de dois fatores for perdido.')
                        ->press('Fechar');
                })
            ;
        });

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/login')

                # Login with two factor authentication
                ->waitForLocation('/login')
                ->type('email', 'admin@admin.com')
                ->type('password', 'password')
                ->press('Entrar')

                # Two factor challenge form
                ->waitForLocation('/two-factor-challenge')
                ->assertSee('Autenticação de dois fatores')
                ->assertSee('Confirme o acesso à sua conta inserindo o código de autenticação fornecido pelo aplicativo de autenticação.')
                ->assertSee('Código de autenticação')
                ->assertInputPresent('code')
                ->assertSee('Código de recuperação')
                ->assertInputPresent('recovery_code')
                ->assertSee('Confirme o acesso à sua conta inserindo um de seus códigos de recuperação de emergência.')
                ->assertButtonEnabled('Entrar')

                # Two factor challenge form validations
                ->press('Entrar')
                ->waitFor('.form-control.is-invalid')
                ->waitForText('O código de autenticação de dois fatores fornecido era inválido.')

                ->type('recovery_code', 1)
                ->press('Entrar')
                ->waitFor('.form-control.is-invalid')
                ->waitForText('O código de recuperação de dois fatores fornecido era inválido.')
            ;
        });

        $this->browse(function (Browser $browser) use ($adminUser) {
            $browser->loginAs($adminUser)
                ->visit('/admin/profile')

                # Regenerate recovery codes
                ->press('Autenticação de dois fatores')
                ->press('Regenerar códigos de recuperação')
                ->whenAvailable('.modal', function (Browser $modal) {
                    $modal->waitForText('Autenticação de dois fatores')
                        ->assertSee('Armazene esses códigos de recuperação em um gerenciador de senhas seguro. Eles podem ser usados para recuperar o acesso à sua conta se o dispositivo de autenticação de dois fatores for perdido.')
                        ->press('Fechar');
                })

                # Disable two factor authentication
                ->press('Autenticação de dois fatores')
                ->press('Desativar autenticação de dois fatores')
                ->waitForText('A autenticação de dois fatores foi desativada.')

                # Show two factor menu actions before enabled
                ->assertSee('Autenticação de dois fatores')
                ->press('Autenticação de dois fatores')
                ->waitForText('Ativar autenticação de dois fatores')
            ;
        });
    }
}
