<?php

namespace Tests\Browser\Tests\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\DuskTestCase;

class CrudTest extends DuskTestCase
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
    public function testBasicCrud(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser);

            # Index with one user
            $browser->visit('/admin/users')
                ->assertSee('Usuário')
                ->assertSee('Todos os usuários registrados')
                ->assertSee('Adicionar')

                ->assertSee('Filtros')

                ->assertSee('Nome')
                ->assertSee('E-mail')
                ->assertSee('Última alteração')
                ->assertSee('Ações')

                ->assertSee('admin')
                ->assertSee('admin@admin.com')

                ->assertSee('Configurar colunas')
                ->assertSee('Registros exibidos: 1-1 de 1')
            ;

            # Form inputs
            $browser->clickLink('Adicionar')
                ->waitForLocation('/admin/users/create')
                ->assertSee('Criar Usuário')
                ->assertSee('Detalhes como nome, email e senha.')
                ->assertSee('Salvar')

                ->assertSee('Informações do perfil')
                ->assertSee('Atualize as informações do perfil da sua conta e o endereço de e-mail.')
                ->assertSee('Nome')
                ->assertInputPresent('user[name]')
                ->assertSee('E-mail')
                ->assertInputPresent('user[email]')

                ->assertSee('Senha')
                ->assertSee('Certifique-se de que sua conta esteja usando uma senha longa e aleatória para permanecer segura.')
                ->assertInputPresent('user[password]')

                ->assertSee('Funções')
                ->assertSee('Uma função define um conjunto de tarefas que um usuário atribuído à função tem permissão para executar.')
                ->assertSee('Nome da função')
                ->assertInputPresent('user[roles][]')
                ->assertSee('Especifique a quais grupos esta conta pertence:')

                ->assertSee('Permissões')
                ->assertSee('Permitir que o usuário execute algumas ações que não são fornecidas por suas funções')
                ->assertSee('Sistema')
                # Anexo
                ->assertNotChecked('permissions[cGxhdGZvcm0uc3lzdGVtcy5hdHRhY2htZW50]')
                # Funções
                ->assertNotChecked('permissions[cGxhdGZvcm0uc3lzdGVtcy5yb2xlcw==]')
                # Usuários
                ->assertNotChecked('permissions[cGxhdGZvcm0uc3lzdGVtcy51c2Vycw==]')

                ->assertSee('Principal')
                # Principal
                ->assertNotChecked('permissions[cGxhdGZvcm0uaW5kZXg=]')
            ;

            # From validation
            $browser->type('user[name]', '')
                ->type('user[email]', '')
                ->press('Salvar')
                ->waitForText('Verifique os dados inseridos.')
            ;

            $browser->type('user[password]', '')
                ->press('Salvar')
                ->waitForText('Verifique os dados inseridos.')
            ;

            # Create a role
            $browser->type('user[name]', 'Admin Atualizado')
                ->type('user[email]', 'admin2@admin2.com.br')
                ->type('user[password]', '123123')
                ->press('Salvar')
                ->waitForText('O usuário foi salvo.')
                ->waitForLocation('/admin/users')
            ;

            $user = User::where('name', 'Admin Atualizado')->first();

            # Validate if the role was created
            $browser->assertSee('Nome')
                ->assertSee('Admin Atualizado')
                ->assertSee('admin2@admin2.com.br')

                ->assertSee('Configurar colunas')
                ->assertSee('Registros exibidos: 1-2 de 2')
            ;

            # Edit the role
            $browser->clickLink('Admin Atualizado')
                ->waitForLocation('/admin/users/' . $user->id . '/edit')
                ->assertInputValue('user[name]', 'Admin Atualizado')
                ->assertInputValue('user[email]', 'admin2@admin2.com.br')

                ->type('user[name]', 'Admin Editado')
                ->type('user[email]', 'admin2-editado@admin2.com.br')
                ->press('Salvar')
                ->waitForLocation('/admin/users')
            ;

            # Validate if the role was edited
            $browser->assertSee('Admin Editado')
                ->assertSee('admin2-editado@admin2.com.br')
            ;

            # Delete the role
            $warningText = 'Assim que a conta for excluída, todos os seus recursos e dados serão excluídos permanentemente. Antes de excluir esta conta, salve todos os dados ou informações que deseja manter.';
            $browser->clickLink('Admin Editado')
                ->waitForLocation('/admin/users/' . $user->id . '/edit')
                ->press('Excluir')
                ->whenAvailable('.modal', function (Browser $modal) use ($warningText) {
                    $modal->assertSee('Tem certeza?')
                        ->assertSee($warningText)
                        ->press('Excluir');
                })
                ->waitForText('O usuário foi excluído')
                ->waitForLocation('/admin/users')
            ;

            # Index with one user again
            $browser->assertMissing('Admin Atualizado')
                ->assertSee('Registros exibidos: 1-1 de 1');
        });
    }

    #[Group('Orchid')]
    #[Group('10-adicionar-testes-de-frontend-com-laravel-dusk')]
    public function testFilterUser(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($this->adminUser)
                ->visit('/admin/users')
                ->press('#post-form > div.bg-white.rounded.shadow-sm.mb-3.overflow-hidden > div > table > thead > tr > th:nth-child(1) > div > div > button')
                ->waitForText('Aplicar')
                ->type('#field-filtername-53dfb3a580c9a38c3356bcff9fb71c8afc74fdf6', $user->name)
                ->press('Aplicar')
                ->waitForText($user->email)
                ->waitUntilMissingText($this->adminUser->email)
            ;
        });
    }

    #[Group('Orchid')]
    #[Group('10-adicionar-testes-de-frontend-com-laravel-dusk')]
    public function testDeleteUserFromIndex(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $warningText = 'Assim que a conta for excluída, todos os seus recursos e dados serão excluídos permanentemente. Antes de excluir esta conta, salve todos os dados ou informações que deseja manter.';

            # Validate if modal is displayed and cancel the deletion
            $browser->loginAs($this->adminUser)
                ->visit('/admin/users')
                ->press('#field-4ca77d7ebd070b09c9e9f4818a826141d6dbb356')
                ->waitForText($user->email)
                ->press('Excluir')
                ->waitForText('Tem certeza?')
                ->assertSee($warningText)
                ->press('Excluir')
                ->waitUntilMissingText($warningText)
            ;
        });

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
