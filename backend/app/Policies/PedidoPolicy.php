<?php

namespace App\Policies;

use App\Enums\EstadoPedidoEnum;
use App\Models\Pedido;
use App\Models\Utilizador;

class PedidoPolicy
{
    public function viewAny(Utilizador $user): bool
    {
        return $user->ativo && $user->hasAnyPermission([
            'pedidos.ver.proprios',
            'pedidos.ver.setor',
            'pedidos.ver.todos',
        ]);
    }

    public function view(Utilizador $user, Pedido $pedido): bool
    {
        if ($user->can('pedidos.ver.todos')) {
            return true;
        }

        if ($user->can('pedidos.ver.setor') && $pedido->utilizador->id_setor === $user->id_setor) {
            return true;
        }

        return $pedido->id_utilizador === $user->id_utilizador
            && $user->can('pedidos.ver.proprios');
    }

    public function create(Utilizador $user): bool
    {
        return $user->ativo && $user->can('pedidos.criar');
    }

    public function update(Utilizador $user, Pedido $pedido): bool
    {
        return $pedido->id_utilizador === $user->id_utilizador
            && $pedido->estadoPedido->nome === EstadoPedidoEnum::RASCUNHO;
    }

    public function delete(Utilizador $user, Pedido $pedido): bool
    {
        return $pedido->id_utilizador === $user->id_utilizador
            && $pedido->estadoPedido->nome === EstadoPedidoEnum::RASCUNHO
            && $user->can('pedidos.cancelar.proprio');
    }

    public function aprovar(Utilizador $user, Pedido $pedido): bool
    {
        return $user->hasAnyPermission([
            'aprovacoes.colega',
            'aprovacoes.diretora_executiva',
        ]);
    }

    public function rejeitar(Utilizador $user, Pedido $pedido): bool
    {
        return $this->aprovar($user, $pedido);
    }

    public function devolver(Utilizador $user, Pedido $pedido): bool
    {
        return $user->can('aprovacoes.diretora_executiva');
    }
}
