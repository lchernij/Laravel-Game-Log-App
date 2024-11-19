<?php

namespace Tests\Browser\Tests;

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
            $browser->visit('/admin/login')
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

            $browser->visit('/admin/login')
                ->assertSee('Game Log App')
                ->type('email', 'admin@admin.com')
                ->type('password', 'password')
                ->press('Entrar')
                ->waitForLocation('/admin/main')
            ;
        });
    }
}
