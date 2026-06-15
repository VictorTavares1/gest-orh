<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_utilizador')->insertOrIgnore([
            ['nome' => 'FUNCIONARIO'],
            ['nome' => 'DIRETOR_TECNICO'],
            ['nome' => 'DIRETORA_EXECUTIVA'],
            ['nome' => 'SUBSTITUTA'],
        ]);

        DB::table('estado_pedido')->insertOrIgnore([
            ['nome' => 'RASCUNHO'],
            ['nome' => 'PENDENTE'],
            ['nome' => 'EM_APROVACAO_COLEGA'],
            ['nome' => 'EM_APROVACAO_DIRETOR'],
            ['nome' => 'EM_APROVACAO_EXECUTIVA'],
            ['nome' => 'APROVADO'],
            ['nome' => 'REJEITADO'],
            ['nome' => 'CANCELADO'],
        ]);

        DB::table('tipo_pedido')->insertOrIgnore([
            ['nome' => 'Horas Extras',                      'ativo' => 1],
            ['nome' => 'Justificação de Faltas',            'ativo' => 1],
            ['nome' => 'Marcação de Férias',                'ativo' => 1],
            ['nome' => 'Alteração de Férias',               'ativo' => 1],
            ['nome' => 'Troca de Horário',                  'ativo' => 1],
            ['nome' => 'Troca de Folga com Instituição',    'ativo' => 1],
            ['nome' => 'Interrupção de Atividade',          'ativo' => 1],
            ['nome' => 'Folga de Aniversário',              'ativo' => 1],
            ['nome' => 'Assiduidade',                       'ativo' => 1],
            ['nome' => 'Licença de Nojo',                   'ativo' => 1],
            ['nome' => 'Formação',                          'ativo' => 1],
            ['nome' => 'Motivos Académicos',                'ativo' => 1],
            ['nome' => 'Compensação de Entrada Tardia',     'ativo' => 1],
            ['nome' => 'Compensação de Saída Antecipada',   'ativo' => 1],
            ['nome' => 'Outros',                            'ativo' => 1],
        ]);
    }
}
