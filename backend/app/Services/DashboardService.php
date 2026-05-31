<?php

namespace App\Services;

use App\Enums\EstadoPedidoEnum;
use App\Models\Utilizador;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function resumo(Utilizador $user): array
    {
        if ($user->can('pedidos.ver.todos')) {
            return $this->resumoExecutiva($user);
        }

        if ($user->can('pedidos.ver.setor')) {
            return $this->resumoDiretor($user);
        }

        return $this->resumoFuncionario($user);
    }

    // ─── Resumos por role ─────────────────────────────────────────────────────

    private function resumoFuncionario(Utilizador $user): array
    {
        $porEstado = $this->porEstado(fn ($q) => $q->where('p.id_utilizador', $user->id_utilizador));

        return [
            'tipo'        => 'funcionario',
            'meus_pedidos' => [
                'total'      => array_sum($porEstado),
                'por_estado' => $porEstado,
            ],
            'pendentes_minha_aprovacao' => $this->contarPendentesColega($user),
            'recentes'    => $this->recentes(
                fn ($q) => $q->where('p.id_utilizador', $user->id_utilizador)
            ),
        ];
    }

    private function resumoDiretor(Utilizador $user): array
    {
        $meusPorEstado = $this->porEstado(fn ($q) => $q->where('p.id_utilizador', $user->id_utilizador));

        return [
            'tipo'        => 'diretor_tecnico',
            'meus_pedidos' => [
                'total'      => array_sum($meusPorEstado),
                'por_estado' => $meusPorEstado,
            ],
            'pendentes_minha_aprovacao' => $this->contarPendentesColega($user),
            'recentes'    => $this->recentes(
                fn ($q) => $q->where('p.id_utilizador', $user->id_utilizador)
            ),
            // Pedidos recentes do setor (notificação) — excluindo os próprios
            'pedidos_setor_recentes' => $this->recentes(
                fn ($q) => $q
                    ->where('u.id_setor', $user->id_setor)
                    ->where('p.id_utilizador', '!=', $user->id_utilizador),
                incluirUtilizador: true
            ),
        ];
    }

    private function resumoExecutiva(Utilizador $user): array
    {
        $porEstado = $this->porEstado(fn ($q) => $q);

        return [
            'tipo'   => 'diretora_executiva',
            'global' => [
                'total'      => array_sum($porEstado),
                'por_estado' => $porEstado,
                'por_tipo'   => $this->porTipo(),
            ],
            'aguardam_aprovacao' => $this->contarEstado(EstadoPedidoEnum::EM_APROVACAO_EXECUTIVA),
            'recentes'           => $this->recentes(fn ($q) => $q, incluirUtilizador: true),
        ];
    }

    // ─── Helpers de query ─────────────────────────────────────────────────────

    private function porEstado(\Closure $filtro): array
    {
        $query = DB::table('pedido as p')
            ->join('estado_pedido as ep', 'p.id_estado_pedido', '=', 'ep.id_estado_pedido')
            ->join('utilizador as u', 'p.id_utilizador', '=', 'u.id_utilizador')
            ->select('ep.nome', DB::raw('COUNT(*) as total'))
            ->groupBy('ep.nome');

        $filtro($query);

        $resultado = [];
        foreach ($query->get() as $row) {
            $resultado[$row->nome] = (int) $row->total;
        }

        // Garantir todos os estados presentes com 0
        foreach (EstadoPedidoEnum::cases() as $estado) {
            $resultado[$estado->value] ??= 0;
        }

        return $resultado;
    }

    private function porTipo(): array
    {
        $rows = DB::table('pedido as p')
            ->join('tipo_pedido as tp', 'p.id_tipo_pedido', '=', 'tp.id_tipo_pedido')
            ->select('tp.nome', DB::raw('COUNT(*) as total'))
            ->groupBy('tp.nome')
            ->get();

        $resultado = [];
        foreach ($rows as $row) {
            $resultado[$row->nome] = (int) $row->total;
        }

        return $resultado;
    }

    private function contarPendentesColega(Utilizador $user): int
    {
        return DB::table('pedido as p')
            ->join('estado_pedido as ep', 'p.id_estado_pedido', '=', 'ep.id_estado_pedido')
            ->join('utilizador as u', 'p.id_utilizador', '=', 'u.id_utilizador')
            ->where('ep.nome', EstadoPedidoEnum::EM_APROVACAO_COLEGA->value)
            ->where('u.id_setor', $user->id_setor)
            ->where('p.id_utilizador', '!=', $user->id_utilizador)
            ->count();
    }

    private function contarEstado(EstadoPedidoEnum $estado): int
    {
        return DB::table('pedido as p')
            ->join('estado_pedido as ep', 'p.id_estado_pedido', '=', 'ep.id_estado_pedido')
            ->where('ep.nome', $estado->value)
            ->count();
    }

    private function recentes(\Closure $filtro, bool $incluirUtilizador = false, int $limit = 5): array
    {
        $query = DB::table('pedido as p')
            ->join('estado_pedido as ep', 'p.id_estado_pedido', '=', 'ep.id_estado_pedido')
            ->join('tipo_pedido as tp', 'p.id_tipo_pedido', '=', 'tp.id_tipo_pedido')
            ->join('utilizador as u', 'p.id_utilizador', '=', 'u.id_utilizador')
            ->select('p.id_pedido', 'tp.nome as tipo', 'ep.nome as estado', 'p.data_criacao', 'u.nome as nome_utilizador')
            ->orderByDesc('p.data_criacao')
            ->limit($limit);

        $filtro($query);

        return $query->get()->map(function ($row) use ($incluirUtilizador) {
            $item = [
                'id_pedido'    => $row->id_pedido,
                'tipo'         => $row->tipo,
                'estado'       => $row->estado,
                'data_criacao' => $row->data_criacao,
            ];

            if ($incluirUtilizador) {
                $item['utilizador'] = $row->nome_utilizador;
            }

            return $item;
        })->toArray();
    }
}
