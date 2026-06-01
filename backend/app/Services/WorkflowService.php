<?php

namespace App\Services;

use App\Actions\Pedido\AprovarPedidoAction;
use App\Actions\Pedido\DevolverPedidoAction;
use App\Actions\Pedido\RejeitarPedidoAction;
use App\Actions\Pedido\SubmeterPedidoAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class WorkflowService
{
    public function __construct(
        private readonly SubmeterPedidoAction $submeterAction,
        private readonly AprovarPedidoAction  $aprovarAction,
        private readonly RejeitarPedidoAction $rejeitarAction,
        private readonly DevolverPedidoAction $devolverAction,
    ) {}

    public function submeter(Utilizador $user, Pedido $pedido): Pedido
    {
        return $this->submeterAction->execute($user, $pedido);
    }

    public function aprovar(Utilizador $user, Pedido $pedido): Pedido
    {
        return $this->aprovarAction->execute($user, $pedido);
    }

    public function rejeitar(Utilizador $user, Pedido $pedido): Pedido
    {
        return $this->rejeitarAction->execute($user, $pedido);
    }

    public function devolver(Utilizador $user, Pedido $pedido, ?string $comentario): Pedido
    {
        return $this->devolverAction->execute($user, $pedido, $comentario);
    }
}
