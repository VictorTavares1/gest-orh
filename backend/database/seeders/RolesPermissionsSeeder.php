<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    private array $permissions = [
        'pedidos.criar',
        'pedidos.ver.proprios',
        'pedidos.ver.setor',
        'pedidos.ver.todos',
        'pedidos.cancelar.proprio',
        'aprovacoes.colega',
        'aprovacoes.diretor_tecnico',
        'aprovacoes.diretora_executiva',
        'utilizadores.ver',
        'utilizadores.gerir',
        'organizacoes.gerir',
        'setores.gerir',
        'periodos.gerir',
        'relatorios.ver',
        'dashboard.ver',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        $funcionario = Role::firstOrCreate(['name' => 'funcionario', 'guard_name' => 'sanctum']);
        $funcionario->syncPermissions([
            'pedidos.criar', 'pedidos.ver.proprios', 'pedidos.cancelar.proprio',
            'aprovacoes.colega', 'dashboard.ver',
        ]);

        $diretor = Role::firstOrCreate(['name' => 'diretor_tecnico', 'guard_name' => 'sanctum']);
        $diretor->syncPermissions([
            'pedidos.criar', 'pedidos.ver.proprios', 'pedidos.ver.setor',
            'pedidos.cancelar.proprio', 'aprovacoes.colega', 'aprovacoes.diretor_tecnico',
            'utilizadores.ver', 'dashboard.ver',
        ]);

        $executiva = Role::firstOrCreate(['name' => 'diretora_executiva', 'guard_name' => 'sanctum']);
        $executiva->syncPermissions($this->permissions);
    }
}
