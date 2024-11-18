<?php

namespace Tests\Browser\Tests;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;
    
    public function testOpenFormAndValidateFields(): void
    {
        $this->browse(function (Browser $browser) {
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

            $browser->type('email', '1@1.com')
                ->type('password', '1@1.com')
                ->press('Entrar')
                ->waitFor('.form-control.is-invalid')
            ;
        });
    }

    public function testUserCanLoginWithSuccess(): void
    {
        $this->artisan('orchid:admin admin admin@admin.com password');

        $this->browse(function (Browser $browser) {
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
