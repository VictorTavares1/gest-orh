<?php

namespace Database\Seeders;

use App\Models\Organizacao;
use App\Models\Setor;
use App\Models\TipoUtilizador;
use App\Models\Utilizador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilizadorAdminSeeder extends Seeder
{
    /*
     * Credenciais admin:
     *  admin@gestaorh.pt | Admin@2024 | diretora_executiva
     */

    public function run(): void
    {
        $org = Organizacao::firstOrCreate(
            ['nome' => 'Gestão RH'],
            ['ativo' => true]
        );

        $setorAdmin = Setor::firstOrCreate(
            ['id_organizacao' => $org->id_organizacao, 'nome' => 'Administração']
        );

        $tipoExecutiva = TipoUtilizador::where('nome', 'DIRETORA_EXECUTIVA')->firstOrFail();

        $admin = Utilizador::firstOrCreate(
            ['email' => 'admin@gestaorh.pt'],
            [
                'nome'               => 'Administrador',
                'password_hash'      => Hash::make('Admin@2024'),
                'id_setor'           => $setorAdmin->id_setor,
                'id_tipo_utilizador' => $tipoExecutiva->id_tipo_utilizador,
                'ativo'              => true,
            ]
        );
        $admin->syncRoles(['diretora_executiva']);
    }
}
