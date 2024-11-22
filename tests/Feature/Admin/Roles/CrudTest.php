<?php

namespace Tests\Feature\Admin\Roles;

use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Orchid\Platform\Models\Role;
use Tests\TestCase;

class CrudTest extends TestCase
{
    private User $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testBasicCrud(): void
    {
        # Index with no roles
        $this->get('/admin/roles')
            ->assertStatus(200)
            ->assertSee('Gerenciar funções')
            ->assertSee('Nenhum registro encontrado');

        # Form create validations
        $this->post('/admin/roles/create/save', [])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'role.name' => 'O campo nome é obrigatório.',
                'role.slug' => 'O campo slug é obrigatório.',
            ]);

        # Create role
        $this->post('/admin/roles/create/save', [
            'role' => [
                'slug' => 'sub-admin',
                'name' => 'Sub Admin',
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'A função foi salva',
                'toast_notification.level' => 'info'
            ]);

        # Index with one role
        $this->get('/admin/roles')
            ->assertStatus(200)
            ->assertSee('Gerenciar funções')
            ->assertSee('Sub Admin');

        # Show created role
        $createdRole = Role::where('slug', 'sub-admin')->first();

        $this->get('/admin/roles/' . $createdRole->id . '/edit')
            ->assertStatus(200)
            ->assertSee('Sub Admin');

        # Form edit validations
        $this->post('/admin/roles/' . $createdRole->id . '/edit/save', [])
            ->assertStatus(302)
            ->assertSessionHasErrors([
                'role.name' => 'O campo nome é obrigatório.',
                'role.slug' => 'O campo slug é obrigatório.',
            ]);

        # Edit role
        $this->post('/admin/roles/' . $createdRole->id . '/edit/save', [
            'role' => [
                'slug' => 'sub-admin-editado',
                'name' => 'Sub Admin Editado',
            ]
        ])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'A função foi salva',
                'toast_notification.level' => 'info'
            ]);

        $createdRole->refresh();
        $this->assertEquals('Sub Admin Editado', $createdRole->name);

        # Remove user
        $this->post('/admin/roles/' . $createdRole->id . '/edit/remove', [])
            ->assertStatus(302)
            ->assertSessionHas([
                'toast_notification.message' => 'A função foi excluída',
                'toast_notification.level' => 'info'
            ]);
    }

    #[Group('Orchid')]
    #[Group('3-adicionar-testes-de-api-para-o-orchid')]
    public function testFilter(): void
    {
        (new Role(['name' => 'Foo', 'slug' => 'foo']))->save();
        (new Role(['name' => 'User', 'slug' => 'user']))->save();

        $this->get('admin/roles?filter%5Bname%5D=Foo')
            ->assertStatus(200)
            ->assertSee('Gerenciar funções')
            ->assertSee('Foo')
            ->assertDontSee('User');

        $this->get('admin/roles?filter%5Bslug%5D=user')
            ->assertStatus(200)
            ->assertSee('Gerenciar funções')
            ->assertDontSee('Foo')
            ->assertSee('user');
    }
}
