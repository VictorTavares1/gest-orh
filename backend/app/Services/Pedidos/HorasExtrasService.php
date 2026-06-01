<?php

namespace App\Services\Pedidos;

use App\Actions\Pedido\CriarHorasExtrasAction;
use App\Models\Pedido;
use App\Models\Utilizador;

class HorasExtrasService
{
    public function __construct(private readonly CriarHorasExtrasAction $action) {}

    public function criar(Utilizador $user, array $dados): Pedido
    {
        return $this->action->execute($user, $dados);
    }
}
