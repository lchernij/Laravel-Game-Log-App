<?php

namespace Tests\Browser\Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\DuskTestCase;

class MainTest extends DuskTestCase
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
    public function testOpenIndex(): void
    {
        $baseUrl = config('app.url');

        $this->browse(function (Browser $browser) use ($baseUrl) {
            $browser->loginAs($this->adminUser)
                ->visit('/admin/main')
                ->assertSee('Principal')
                ->assertSourceHas("{$baseUrl}/admin/profile")
                ->assertSourceHas("{$baseUrl}/admin/users")
            ;
        });
    }
}
