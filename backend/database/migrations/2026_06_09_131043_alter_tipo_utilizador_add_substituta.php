<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alterar o ENUM para incluir SUBSTITUTA e limpar registos inválidos
        DB::statement("ALTER TABLE tipo_utilizador MODIFY nome ENUM('FUNCIONARIO','DIRETOR_TECNICO','DIRETORA_EXECUTIVA','SUBSTITUTA') NOT NULL");

        // Apagar registos inválidos que ficaram de testes anteriores
        DB::table('tipo_utilizador')
            ->whereNotIn('nome', ['FUNCIONARIO', 'DIRETOR_TECNICO', 'DIRETORA_EXECUTIVA', 'SUBSTITUTA'])
            ->delete();

        // Inserir SUBSTITUTA se não existir
        if (!DB::table('tipo_utilizador')->where('nome', 'SUBSTITUTA')->exists()) {
            DB::table('tipo_utilizador')->insert(['nome' => 'SUBSTITUTA']);
        }
    }

    public function down(): void
    {
        DB::table('tipo_utilizador')->where('nome', 'SUBSTITUTA')->delete();
        DB::statement("ALTER TABLE tipo_utilizador MODIFY nome ENUM('FUNCIONARIO','DIRETOR_TECNICO','DIRETORA_EXECUTIVA') NOT NULL");
    }
};
