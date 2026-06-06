<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimparDadosTeste extends Command
{
    protected $signature = 'app:limpar-dados-teste';
    protected $description = 'Limpa todos os dados de teste, preservando os utilizadores base do seeder.';

    private array $emailsPreservados = [
        'diretora@gestaorh.pt',
        'diretor@gestaorh.pt',
        'funcionario@gestaorh.pt',
    ];

    private array $orgsPreservadas = ['Gestão RH Demo'];

    public function handle(): int
    {
        if (! $this->confirm('Isto vai apagar todos os pedidos, utilizadores de teste, setores e organizações de teste. Confirmas?')) {
            $this->info('Operação cancelada.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            // 1. Apagar todos os pedidos e dados associados
            $this->limparPedidos();

            // 2. Apagar utilizadores de teste (exceto os base)
            $this->limparUtilizadores();

            // 3. Apagar períodos
            $this->limparPeriodos();

            // 4. Apagar setores de teste (não associados a orgs preservadas)
            $this->limparSetores();

            // 5. Apagar organizações de teste
            $this->limparOrganizacoes();
        });

        $this->info('Dados de teste limpos com sucesso.');
        return self::SUCCESS;
    }

    private function limparPedidos(): void
    {
        $ids = DB::table('pedido')->pluck('id_pedido');

        if ($ids->isEmpty()) {
            $this->line('  Sem pedidos para apagar.');
            return;
        }

        // Tabelas de especialização
        $especializacoes = [
            'pedido_horas_extras', 'pedido_justificacao_faltas', 'pedido_marcacao_ferias',
            'pedido_alteracao_ferias', 'pedido_troca_horario', 'pedido_troca_folga_instituicao',
            'pedido_interrupcao_atividade', 'pedido_folga_aniversario', 'pedido_assiduidade',
            'pedido_licenca_nojo', 'pedido_formacao', 'pedido_motivos_academicos',
            'pedido_comp_entrada_tardia', 'pedido_comp_saida_antecipada',
        ];

        foreach ($especializacoes as $tabela) {
            DB::table($tabela)->whereIn('id_pedido', $ids)->delete();
        }

        DB::table('anexo')->whereIn('id_pedido', $ids)->delete();
        DB::table('aprovacao_pedido')->whereIn('id_pedido', $ids)->delete();
        DB::table('historico_pedido')->whereIn('id_pedido', $ids)->delete();
        DB::table('pedido')->whereIn('id_pedido', $ids)->delete();

        $this->line("  {$ids->count()} pedido(s) eliminado(s).");
    }

    private function limparUtilizadores(): void
    {
        $utilizadores = DB::table('utilizador')
            ->whereNotIn('email', $this->emailsPreservados)
            ->get();

        if ($utilizadores->isEmpty()) {
            $this->line('  Sem utilizadores de teste para apagar.');
            return;
        }

        $ids = $utilizadores->pluck('id_utilizador');

        // Revogar tokens e roles
        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\Utilizador')
            ->whereIn('tokenable_id', $ids)
            ->delete();

        DB::table('model_has_roles')
            ->where('model_type', 'App\\Models\\Utilizador')
            ->whereIn('model_id', $ids)
            ->delete();

        DB::table('model_has_permissions')
            ->where('model_type', 'App\\Models\\Utilizador')
            ->whereIn('model_id', $ids)
            ->delete();

        DB::table('utilizador')->whereIn('id_utilizador', $ids)->delete();

        $this->line("  {$utilizadores->count()} utilizador(es) de teste eliminado(s).");
    }

    private function limparPeriodos(): void
    {
        $count = DB::table('periodo')->delete();
        $this->line("  {$count} período(s) eliminado(s).");
    }

    private function limparSetores(): void
    {
        // Preserva setores das organizações base
        $orgIds = DB::table('organizacao')
            ->whereIn('nome', $this->orgsPreservadas)
            ->pluck('id_organizacao');

        // Mover utilizadores preservados para os setores base antes de apagar
        $setorBase = DB::table('setor')
            ->whereIn('id_organizacao', $orgIds)
            ->value('id_setor');

        if ($setorBase) {
            DB::table('utilizador')
                ->whereIn('email', $this->emailsPreservados)
                ->update(['id_setor' => $setorBase]);
        }

        $count = DB::table('setor')
            ->whereNotIn('id_organizacao', $orgIds)
            ->delete();

        $this->line("  {$count} setor(es) de teste eliminado(s). Setores da organização base preservados.");
    }

    private function limparOrganizacoes(): void
    {
        $count = DB::table('organizacao')
            ->whereNotIn('nome', $this->orgsPreservadas)
            ->delete();

        $this->line("  {$count} organização(ões) de teste eliminada(s).");
    }
}
