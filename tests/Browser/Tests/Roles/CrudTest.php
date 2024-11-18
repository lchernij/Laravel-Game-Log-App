<?php

namespace Tests\Browser\Tests\Roles;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
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

    public function testBasicCrud(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser);

            # Index empty
            $browser->visit('/admin/roles')
                ->assertSee('Gerenciar funções:')
                ->assertSee('Direitos de acesso')
                ->assertSee('Adicionar')

                ->assertSee('Nenhum registro encontrado')
                ->assertSee('Importe ou crie registros, ou verifique novamente mais tarde para atualizações.')
            ;

            # Form inputs
            $browser->clickLink('Adicionar')
                ->waitForLocation('/admin/roles/create')
                ->assertSee('Editar função')
                ->assertSee('Modifique os privilégios e permissões associados a uma função específica.')
                ->assertSee('Salvar')

                ->assertSee('Função')
                ->assertSee('Uma função é uma coleção de privilégios (de serviços possivelmente diferentes, como o serviço Usuários, Moderador e assim por diante) que concede aos usuários com essa função a capacidade de executar certas tarefas ou operações.')
                ->assertSee('Nome')
                ->assertInputPresent('role[name]')
                ->assertSee('Slug')
                ->assertInputPresent('role[slug]')

                ->assertSee('Permissão/Privilégio')
                ->assertSee('Um privilégio é necessário para realizar certas tarefas e operações em uma área.')
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
            $browser->type('role[name]', '')
                ->type('role[slug]', '')
                ->press('Salvar')
                ->waitForText('Verifique os dados inseridos.')
            ;

            # Create a role
            $browser->type('role[name]', 'Perfil Administrativo')
                ->type('role[slug]', 'perfil-administrativo')
                ->press('Salvar')
                ->waitForLocation('/admin/roles')
            ;

            $role = Role::where('slug', 'perfil-administrativo')->first();

            # Validate if the role was created
            $browser->assertSee('Nome')
                ->assertSee('Slug')
                ->assertSee('Última alteração')

                ->assertSee('Perfil Administrativo')
                ->assertSee('perfil-administrativo')

                ->assertSee('Configurar colunas')
                ->assertSee('Registros exibidos: 1-1 de 1')
            ;

            # Edit the role
            $browser->clickLink('Perfil Administrativo')
                ->waitForLocation('/admin/roles/' . $role->id . '/edit')
                ->assertInputValue('role[name]', 'Perfil Administrativo')
                ->assertInputValue('role[slug]', 'perfil-administrativo')

                ->type('role[name]', 'Perfil Administrativo Editado')
                ->type('role[slug]', 'perfil-administrativo-editado')
                ->press('Salvar')
                ->waitForLocation('/admin/roles')
            ;

            # Validate if the role was edited
            $browser->assertSee('Perfil Administrativo Editado')
                ->assertSee('perfil-administrativo-editado')
            ;

            # Delete the role
            $browser->clickLink('Perfil Administrativo Editado')
                ->waitForLocation('/admin/roles/' . $role->id . '/edit')
                ->press('Excluir')
                ->waitForText('A função foi excluída')
                ->waitForLocation('/admin/roles')
            ;

            # Index empty again
            $browser->assertSee('Nenhum registro encontrado');
        });
    }
}
