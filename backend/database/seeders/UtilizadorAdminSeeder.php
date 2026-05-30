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
     * Credenciais de teste geradas por este seeder:
     *
     *  diretora@gestaorh.pt   | Admin@2024  | diretora_executiva
     *  diretor@gestaorh.pt    | Admin@2024  | diretor_tecnico
     *  funcionario@gestaorh.pt| Admin@2024  | funcionario
     */

    public function run(): void
    {
        $org = Organizacao::firstOrCreate(
            ['nome' => 'Gestão RH Demo'],
            ['ativo' => true]
        );

        $setorAdmin = Setor::firstOrCreate(
            ['id_organizacao' => $org->id_organizacao, 'nome' => 'Administração']
        );

        $setorTI = Setor::firstOrCreate(
            ['id_organizacao' => $org->id_organizacao, 'nome' => 'Tecnologias de Informação']
        );

        $tipoExecutiva = TipoUtilizador::where('nome', 'DIRETORA_EXECUTIVA')->firstOrFail();
        $tipoDiretor   = TipoUtilizador::where('nome', 'DIRETOR_TECNICO')->firstOrFail();
        $tipoFunc      = TipoUtilizador::where('nome', 'FUNCIONARIO')->firstOrFail();

        $diretora = Utilizador::firstOrCreate(
            ['email' => 'diretora@gestaorh.pt'],
            [
                'nome'              => 'Diretora Executiva',
                'password_hash'     => Hash::make('Admin@2024'),
                'id_setor'          => $setorAdmin->id_setor,
                'id_tipo_utilizador'=> $tipoExecutiva->id_tipo_utilizador,
                'ativo'             => true,
            ]
        );
        $diretora->syncRoles(['diretora_executiva']);

        $diretor = Utilizador::firstOrCreate(
            ['email' => 'diretor@gestaorh.pt'],
            [
                'nome'              => 'Diretor Técnico',
                'password_hash'     => Hash::make('Admin@2024'),
                'id_setor'          => $setorTI->id_setor,
                'id_tipo_utilizador'=> $tipoDiretor->id_tipo_utilizador,
                'ativo'             => true,
            ]
        );
        $diretor->syncRoles(['diretor_tecnico']);

        $funcionario = Utilizador::firstOrCreate(
            ['email' => 'funcionario@gestaorh.pt'],
            [
                'nome'              => 'Funcionário Teste',
                'password_hash'     => Hash::make('Admin@2024'),
                'id_setor'          => $setorTI->id_setor,
                'id_tipo_utilizador'=> $tipoFunc->id_tipo_utilizador,
                'ativo'             => true,
            ]
        );
        $funcionario->syncRoles(['funcionario']);
    }
}
