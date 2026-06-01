<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarInterrupcaoAtividadeAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class InterrupcaoAtividadeService
{
    public function __construct(private readonly CriarInterrupcaoAtividadeAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
