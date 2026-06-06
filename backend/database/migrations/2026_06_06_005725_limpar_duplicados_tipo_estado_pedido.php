<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicados em tipo_pedido — mantém o registo com o menor ID para cada nome
        $this->removerDuplicados('tipo_pedido', 'id_tipo_pedido', 'nome');

        // Remove duplicados em estado_pedido — mantém o registo com o menor ID para cada nome
        $this->removerDuplicados('estado_pedido', 'id_estado_pedido', 'nome');

        // Adiciona constraints UNIQUE para evitar duplicados no futuro
        Schema::table('tipo_pedido', function (Blueprint $table) {
            if (!$this->uniqueExists('tipo_pedido', 'tipo_pedido_nome_unique')) {
                $table->unique('nome', 'tipo_pedido_nome_unique');
            }
        });

        Schema::table('estado_pedido', function (Blueprint $table) {
            if (!$this->uniqueExists('estado_pedido', 'estado_pedido_nome_unique')) {
                $table->unique('nome', 'estado_pedido_nome_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tipo_pedido', function (Blueprint $table) {
            $table->dropUnique('tipo_pedido_nome_unique');
        });

        Schema::table('estado_pedido', function (Blueprint $table) {
            $table->dropUnique('estado_pedido_nome_unique');
        });
    }

    private function removerDuplicados(string $tabela, string $pk, string $coluna): void
    {
        $duplicados = DB::table($tabela)
            ->select($coluna, DB::raw("MIN($pk) as id_manter"), DB::raw("GROUP_CONCAT($pk) as todos_ids"))
            ->groupBy($coluna)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $dup) {
            $todosIds = explode(',', $dup->todos_ids);
            $aEliminar = array_filter($todosIds, fn ($id) => (int) $id !== (int) $dup->id_manter);

            if (!empty($aEliminar)) {
                DB::table($tabela)->whereIn($pk, $aEliminar)->delete();
            }
        }
    }

    private function uniqueExists(string $tabela, string $indexName): bool
    {
        $indices = DB::select("SHOW INDEX FROM `$tabela` WHERE Key_name = ?", [$indexName]);
        return !empty($indices);
    }
};
