<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarTrocaHorarioAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class TrocaHorarioService
{
    public function __construct(private readonly CriarTrocaHorarioAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
